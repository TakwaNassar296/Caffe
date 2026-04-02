<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Category;
use App\Models\UserLocation;
use App\Models\UserPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PayWithPointsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $branch;
    protected $product;
    protected $cart;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with points
        $this->user = User::factory()->create([
            'total_points' => 1000,
            'total_stars' => 50,
        ]);

        // Create category
        $category = Category::factory()->create();

        // Create branch
        $this->branch = Branch::factory()->create([
            'name' => 'Test Branch',
            'opening_time' => '08:00:00',
            'close_date' => '22:00:00',
        ]);

        // Create product with points price
        $this->product = Product::factory()->create([
            'name' => 'Test Coffee',
            'price' => 50.00,
            'price_with_points' => 100,
            'points' => 10,
            'stars' => 2,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        // Attach product to branch
        $this->branch->products()->attach($this->product->id, ['status' => 1]);

        // Create user location
        UserLocation::factory()->create([
            'user_id' => $this->user->id,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);

        // Create user payment method
        UserPayment::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function test_get_order_summary_returns_points_cost()
    {
        // Create cart with items
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total_price' => 100.00,
            'original_price' => 50.00,
            'discount_price' => 0,
        ]);

        $response = $this->actingAs($this->user, 'user')
            ->getJson('/api/order/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'subtotal',
                    'discount',
                    'delivery_charge',
                    'tax',
                    'total',
                    'total_price_with_points',
                    'user_total_points',
                    'description_delivery',
                ]
            ])
            ->assertJson([
                'data' => [
                    'total_price_with_points' => 200, // 2 items * 100 points
                    'user_total_points' => 1000,
                ]
            ]);
    }

    /** @test */
    public function test_checkout_with_points_success()
    {
        // Create cart with items
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total_price' => 100.00,
            'original_price' => 50.00,
            'discount_price' => 0,
        ]);

        $userLocation = $this->user->locations()->first();
        $userPayment = $this->user->payments()->first();

        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/checkout', [
                'type' => 'delivery',
                'user_location_id' => $userLocation->id,
                'user_payment_id' => $userPayment->id,
                'pay_with' => 'points',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order created successfully with 200 points',
            ]);

        // Verify points were deducted
        $this->user->refresh();
        $this->assertEquals(800, $this->user->total_points); // 1000 - 200

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'pay_with' => 'points',
            'payment_status' => 'paid',
            'total_price' => 0, // Points payment has no money cost
        ]);
    }

    /** @test */
    public function test_checkout_with_points_insufficient_balance()
    {
        // Update user to have insufficient points
        $this->user->update(['total_points' => 50]);

        // Create cart with items
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total_price' => 100.00,
            'original_price' => 50.00,
            'discount_price' => 0,
        ]);

        $userLocation = $this->user->locations()->first();
        $userPayment = $this->user->payments()->first();

        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/checkout', [
                'type' => 'delivery',
                'user_location_id' => $userLocation->id,
                'user_payment_id' => $userPayment->id,
                'pay_with' => 'points',
            ]);

        $response->assertStatus(500)
            ->assertJsonFragment([
                'message' => 'Checkout failed',
            ]);

        // Verify points were NOT deducted
        $this->user->refresh();
        $this->assertEquals(50, $this->user->total_points);
    }

    /** @test */
    public function test_schedule_order_with_points_success()
    {
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/scheduled-orders', [
                'order_type' => 'pick_up',
                'branch_id' => $this->branch->id,
                'schedual_date' => now()->addDay()->setTime(14, 0, 0)->format('Y-m-d H:i:s'),
                'products' => [
                    [
                        'id' => $this->product->id,
                        'quantity' => 3,
                    ]
                ],
                'pay_with' => 'points',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order scheduled successfully with 300 points',
            ]);

        // Verify points were deducted
        $this->user->refresh();
        $this->assertEquals(700, $this->user->total_points); // 1000 - 300

        // Verify scheduled order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'scheduled',
            'pay_with' => 'points',
            'payment_status' => 'paid',
        ]);
    }

    /** @test */
    public function test_get_scheduled_order_summary_returns_points_cost()
    {
        // Create scheduled order
        $order = $this->user->orders()->create([
            'branch_id' => $this->branch->id,
            'sub_total' => 0,
            'total_price' => 0,
            'status' => 'scheduled',
            'payment_status' => 'pending',
            'pay_with' => 'money',
            'type' => 'pick_up',
            'schedual_date' => now()->addDay(),
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total_price' => 100.00,
        ]);

        $response = $this->actingAs($this->user, 'user')
            ->getJson("/api/scheduled-orders/{$order->id}/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'subtotal',
                    'total_price_with_points',
                    'user_total_points',
                ]
            ])
            ->assertJson([
                'data' => [
                    'total_price_with_points' => 200, // 2 * 100
                    'user_total_points' => 1000,
                ]
            ]);
    }

    /** @test */
    public function test_checkout_scheduled_order_with_points()
    {
        // Create scheduled order
        $order = $this->user->orders()->create([
            'branch_id' => $this->branch->id,
            'sub_total' => 0,
            'total_price' => 0,
            'status' => 'scheduled',
            'payment_status' => 'pending',
            'pay_with' => 'money',
            'type' => 'pick_up',
            'schedual_date' => now()->addDay(),
        ]);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'total_price' => 250.00,
        ]);

        $response = $this->actingAs($this->user, 'user')
            ->postJson("/api/scheduled-orders/{$order->id}/checkout", [
                'pay_with' => 'points',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment completed successfully with 500 points',
            ]);

        // Verify points were deducted
        $this->user->refresh();
        $this->assertEquals(500, $this->user->total_points); // 1000 - 500

        // Verify order payment status updated
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'pay_with' => 'points',
        ]);
    }

    /** @test */
    public function test_product_without_price_with_points_throws_error()
    {
        // Create product without points price
        $productWithoutPoints = Product::factory()->create([
            'name' => 'No Points Product',
            'price' => 30.00,
            'price_with_points' => null,
            'category_id' => $this->product->category_id,
        ]);

        $this->branch->products()->attach($productWithoutPoints->id, ['status' => 1]);

        // Create cart with this product
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $cart->items()->create([
            'product_id' => $productWithoutPoints->id,
            'quantity' => 1,
            'total_price' => 30.00,
            'original_price' => 30.00,
            'discount_price' => 0,
        ]);

        $userLocation = $this->user->locations()->first();
        $userPayment = $this->user->payments()->first();

        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/checkout', [
                'type' => 'delivery',
                'user_location_id' => $userLocation->id,
                'user_payment_id' => $userPayment->id,
                'pay_with' => 'points',
            ]);

        $response->assertStatus(500)
            ->assertJsonFragment([
                'message' => 'Checkout failed',
            ]);
    }

    /** @test */
    public function test_checkout_with_money_still_works()
    {
        // Create cart with items
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'total_price' => 100.00,
            'original_price' => 50.00,
            'discount_price' => 0,
        ]);

        $userLocation = $this->user->locations()->first();
        $userPayment = $this->user->payments()->first();

        $initialPoints = $this->user->total_points;

        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/checkout', [
                'type' => 'delivery',
                'user_location_id' => $userLocation->id,
                'user_payment_id' => $userPayment->id,
                'pay_with' => 'money',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order created successfully',
            ]);

        // Verify points were NOT deducted for money payment
        $this->user->refresh();
        // Points might increase due to rewards, but shouldn't decrease
        $this->assertGreaterThanOrEqual($initialPoints, $this->user->total_points);

        // Verify order was created with money
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'pay_with' => 'money',
            'payment_status' => 'paid',
        ]);
    }
}


