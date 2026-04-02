<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Stripe\EphemeralKey;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;

class StripeService
{
    protected StripeClient $stripe;
    protected string $secretKey;
    protected string $webhookSecret;

    public function __construct()
    {
        // TODO : Make config file
        $this->secretKey     = config('services.stripe.secret',);
        $this->webhookSecret = config('services.stripe.webhook_secret',git );

        \Stripe\Stripe::setApiKey($this->secretKey);
        $this->stripe = new StripeClient($this->secretKey);
    }

   
    public function createCustomer(User $user): string
    {
        $customer = \Stripe\Customer::create([
            'name'  => $user->name,
            'phone' => $user->phone,
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    
    public function ensureCustomer(User $user): string
    {
        if (! $user->stripe_customer_id) {
            return $this->createCustomer($user);
        }

        return $user->stripe_customer_id;
    }

    
   /* public function createPaymentIntent(User $user, float $amount, string $paymentMethod, int $orderId,  int $points = 0, string $type = null ): array
    {
        $customerId = $this->ensureCustomer($user);

        $ephemeralKey = EphemeralKey::create(
            ['customer' => $customerId],
            ['stripe_version' => '2026-01-28.clover']
        );

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount'               => (int) round($amount * 100), // convert to cents
            'currency'             => 'usd',
            'payment_method_types' => ['card'],
            'customer'             => $customerId,
            'payment_method'       => $paymentMethod,
            'off_session'          => false,
            'confirm'              => false,
            'setup_future_usage'   => 'off_session',
            'metadata'             => [
                'order_id' => $orderId,
                'user_id'  => $user->id,
                'type'     => $type,
                'points'   => $points,
            ],
        ]);

        return [
            'payment_intent' => $paymentIntent,
            'ephemeral_key'  => $ephemeralKey,
        ];
    }*/


    public function createPaymentIntent(User $user, float $amount, ?string $paymentMethod = null, int $orderId, int $points = 0, string $type = null): array
    {
        $customerId = $this->ensureCustomer($user);

        $ephemeralKey = EphemeralKey::create(
            ['customer' => $customerId],
            ['stripe_version' => '2026-01-28.clover'] 
        );

        $params = [
            'amount'               => (int) round($amount * 100),
            'currency'             => 'usd',
            'customer'             => $customerId,
            'setup_future_usage'   => 'off_session',
            'metadata'             => [
                'order_id' => $orderId,
                'user_id'  => $user->id,
                'type'     => $type,
                'points'   => $points,
            ],

            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];
        
        if ($paymentMethod) {
            $params['payment_method'] = $paymentMethod;
        }

        $paymentIntent = \Stripe\PaymentIntent::create($params);

        return [
            'payment_intent' => $paymentIntent,
            'ephemeral_key'  => $ephemeralKey,
        ];
    }



    public function charge(User $user, float $salary, int $points, string $paymentMethod): array
    {
        $customerId = $this->ensureCustomer($user);

        $ephemeralKey = EphemeralKey::create(
            ['customer' => $customerId],
            ['stripe_version' => '2026-01-28.clover']
        );

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount'               => (int) round($salary * 100), // convert to cents
            'currency'             => 'usd',
            'payment_method_types' => ['card'],
            'customer'             => $customerId,
            'payment_method'       => $paymentMethod,
            'off_session'          => false,
            'confirm'              => false,
            'metadata'             => [
                'user_id'  => $user->id,
                'type'     => 'points',
                'points'   => $points,
            ],
        ]);

        return [
            'payment_intent' => $paymentIntent,
            'ephemeral_key'  => $ephemeralKey,
            'stripe_customer_id' => $user?->stripe_customer_id ?? null,
        ];
    }

    //  Webhook
    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return \Stripe\Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }

   
    //callack
    public function handlePaymentSucceeded(\Stripe\Event $event): void
    {
        $paymentIntent = $event->data->object;

        $userId  = $paymentIntent->metadata->user_id  ?? null;
        $orderId = $paymentIntent->metadata->order_id ?? null;

        $user  = $userId  ? User::find($userId)  : null;
        $order = $orderId ? Order::find($orderId) : null;

        if ($user && $paymentIntent->payment_method) {
            $user->update(['stripe_payment_method' => $paymentIntent->payment_method]);

            $this->stripe->paymentMethods->attach(
                $paymentIntent->payment_method,
                ['customer' => $user->stripe_customer_id]
            );
        }

        if ($order) {
            $order->update(['status' => 'paid']);
            $order->update(['points_increase_user' => $paymentIntent->metadata->points]);

            DB::transaction(function () use ($order) {

                foreach ($order->items as $item) {

                    $product = Product::lockForUpdate()->find($item->product_id);


                    $product->decrement('remaining_quantity', $item->quantity);
                    $product->decrement('total_sales', $item->quantity);
                    $product->save();
                }
            });


            if ($paymentIntent->metadata->type === 'scheduled') {
                $order->update(['status' => 'scheduled']);

                $order->update(['points_increase_user' => $paymentIntent->metadata->points]);

            DB::transaction(function () use ($order) {

                foreach ($order->items as $item) {

                    $product = Product::lockForUpdate()->find($item->product_id);


                    $product->decrement('remaining_quantity', $item->quantity);
                    $product->decrement('total_sales', $item->quantity);
                    $product->save();
                }
            });
            }


            if ($paymentIntent->metadata->points) {

                $user->total_points += $paymentIntent->metadata->points;
                $user->save();
            }

        }


        if ($paymentIntent->metadata->type === 'points') {
            $user->total_points += $paymentIntent->metadata->points;
            $user->save();
        }
    }

    public function handlePaymentFailed(\Stripe\Event $event): void
    {
        $paymentIntent = $event->data->object;

        $userId  = $paymentIntent->metadata->user_id  ?? null;
        $orderId = $paymentIntent->metadata->order_id ?? null;

        $user  = $userId  ? User::find($userId)  : null;
        $order = $orderId ? Order::find($orderId) : null;

        if ($order) {
            $order->update(['status' => 'canceled']);
        }

        if ($user && isset($paymentIntent->last_payment_error->payment_method->id)) {
            $user->update([
                'stripe_payment_method' => $paymentIntent->last_payment_error->payment_method->id,
            ]);
        }
    }

    //  CRUD Payment Methods
    public function attachPaymentMethod(User $user, string $paymentMethodId): \Stripe\PaymentMethod
    {
        $this->ensureCustomer($user);

        return $this->stripe->paymentMethods->attach(
            $paymentMethodId,
            ['customer' => $user->stripe_customer_id]
        );
    }

    public function retrievePaymentMethod(User $user, string $paymentMethodId): \Stripe\PaymentMethod
    {
        return $this->stripe->customers->retrievePaymentMethod(
            $user->stripe_customer_id,
            $paymentMethodId,
            []
        );
    }

    public function listPaymentMethods(User $user): \Stripe\Collection
    {
        $customerId = $this->ensureCustomer($user);

        return $this->stripe->customers->allPaymentMethods(
            $customerId,
            ['limit' => 10]
        );
    }

    public function detachPaymentMethod(string $paymentMethodId): \Stripe\PaymentMethod
    {
        return $this->stripe->paymentMethods->detach($paymentMethodId, []);
    }
}
