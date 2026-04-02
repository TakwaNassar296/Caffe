<?php

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Middleware\ApiLang;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\GeneralConrtoller;
use App\Http\Controllers\Api\KeywordController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\OrderUserController;
use App\Http\Controllers\Api\BranchUserController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\UserPaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserLocationController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\BranchProductController;
use App\Http\Controllers\Api\OrderSecheualController;
use App\Http\Controllers\Api\BranchCategoryController;


Route::get('/unauthorized', function () {
    return response()->json([
        'message' => 'Unauthorized'
    ], 401);
})->name('login');




Route::middleware(ApiLang::class)->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
        Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
    });


    /***************************** Authentication  ***************************/
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('logout', 'logout')->middleware('auth:user');
        Route::post('verify-otp', 'verifyOtp');
        Route::post('resend-otp', 'resendOtp');
        Route::post('forget-password', 'forgetPassword');
        Route::post('reset-password', 'resetPassword');
        Route::post('refresh-token', 'refreshToken');
        Route::post('logout', 'logout')->middleware('auth:user');
        Route::post('delete-account', 'deleteAccount')->middleware('auth:user');
    });

    /*****************************  Profile  ***************************/

    Route::middleware('auth:user')->controller(ProfileController::class)->group(function () {
        Route::get('profile', 'show');
        Route::post('profile/update', 'updateProfile');
    });

    /***************************** User Location ***************************/

    Route::middleware('auth:user')->group(function () {
        Route::get('user-locations', [UserLocationController::class, 'index']);
        Route::post('user-locations', [UserLocationController::class, 'store']);
        Route::post('user-locations/{id}/edit', [UserLocationController::class, 'update']);
        Route::delete('user-locations/{id}', [UserLocationController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->prefix('ranks')->controller(RankController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/user', 'userRankStats');
        Route::get('/{id}', 'show');
    });
    Route::middleware('auth:user')->controller(RankController::class)->group(function () {
        Route::get('/my-points', 'userPoints');
    });


    /************});
     ***************** site-settings ***************************/
    Route::get('site-settings', [SiteSettingController::class, 'index']);

    Route::controller(CategoryController::class)->group(function () {
        Route::get('categories', 'index');
    });

    Route::controller(ProductController::class)->group(function () {
        Route::get('products', 'index');
        Route::get('product/{id}', 'show');
        Route::get('product-in-cart', 'appereInCart');
        Route::get('related-products', 'relatedProducts')->middleware('auth:user');
    });


    /***************************** Branch User ***************************/
    Route::controller(BranchUserController::class)->middleware('auth:user')->group(function () {
        Route::get('branch-user', 'index');
        Route::post('branch-user', 'follow');
        Route::delete('branch-user/{branchId}', 'delete');
        Route::get('get-branchs', 'getBranches');
    });

    /***************************** Branch Product ***************************/
    Route::get('/branch/{branchId}/products', [BranchProductController::class, 'baseProducts']);

    Route::get('/branch/{branchId}/products/filter', [BranchProductController::class, 'filterProducts']);

    Route::get('/branch/{branchId}/products/price-range', [BranchProductController::class, 'priceRange']);




    /***************************** Branch Categories ***************************/
    Route::get('branch/categories/{branchId}', [BranchCategoryController::class, 'index']);


    /***************************** CART ***************************/
    Route::middleware('auth:user')->prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::post('/update-quantity/{itemId}', [CartController::class, 'updateQuantity']);
        Route::delete('/remove-item/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clearCart']);
    });


    /***************************** User Review ***************************/
    Route::middleware('auth:user')->group(function () {
        Route::post('orders/{orderId}/review/driver', [ReviewController::class, 'driverReview']);
        Route::post('orders/{orderId}/review/branch', [ReviewController::class, 'branchReview']);
        Route::post('orders/{orderId}/review/products', [ReviewController::class, 'productReview']);

        //delete 
        Route::post('orders/complete/{orderId}', [ReviewController::class, 'completeOrder']);   //for api mobile

    });

    /***************************** Order ***************************/
    Route::middleware('auth:user')->group(function () {
        Route::post('order/summary', [OrderController::class, 'getOrderSummary']);
        Route::post('order/checkout', [OrderController::class, 'checkout']);
        Route::post('order/pay-with-points', [OrderController::class, 'payWithPoints']);
        Route::post('order/reorder/{orderId}', [OrderController::class, 'reorder']);
        Route::post('orders/cancel/{orderId}', [OrderController::class, 'cancelOrder']);
    });

    Route::prefix('user/orders')->middleware('auth:user')->group(function () {
        Route::get('/status/{status}', [OrderUserController::class, 'getOrdersByStatus'])->whereIn('status', ['pending', 'completed', 'canceled']);
        Route::get('/{id}', [OrderUserController::class, 'show']);
        // Route::post('/{orderId}/cancel', [OrderUserController::class, 'cancelOrder']);
    });



    /***************************** Verify  Coupon ***************************/
    Route::middleware('auth:user')->group(function () {
        Route::post('verify-coupon', [CouponController::class, 'verify']);
    });

    /***************************** User Payment ***************************/

    Route::middleware('auth:user')->group(function () {
        Route::get('user-payment', [UserPaymentController::class, 'index']);
        Route::post('user-payment', [UserPaymentController::class, 'store']);
    });



    /***************************** Advertisement Payment ***************************/

    // Route::middleware('auth:user')->group(function () {
    Route::get('advertisement-text', [AdvertisementController::class, 'advertisementText']);
    Route::get('advertisement-image', [AdvertisementController::class, 'advertisementImage']);
    Route::get('home', [AdvertisementController::class, 'home']);
// });



    /************************************General Api[Cities] *****************/

    Route::get('cities', [GeneralConrtoller::class, 'cities']);

    /***************************** KeyWords ***************************/
    Route::get('keywords', [KeywordController::class, 'index']);
    Route::get('user/terms-conditions', [PageController::class, 'termsConditionsUser']);
    Route::get('coupons', [PageController::class, 'coupon']);
    Route::get('support', [PageController::class, 'support']);




    Route::middleware('auth:user')->group(function () {
        Route::get('user-payment', [UserPaymentController::class, 'index']);
        Route::post('user-payment', [UserPaymentController::class, 'store']);
    });




    Route::middleware('auth:sanctum')->controller(FavouriteController::class)->group(function () {
        Route::get('/favorites',  'index');
        Route::post('/favorites',  'store');
        Route::delete('/favorites/{id}',  'destroy');
        Route::delete('/favorites',  'clearAll');
    });


// Route::get('coupon/{slug}', [PageController::class, 'show']);


    /***************************** Order Schedule ***************************/
    Route::middleware('auth:user')->group(function () {
        Route::get('orders/schedule', [OrderSecheualController::class, 'index']);
        Route::post('orders/schedule', [OrderSecheualController::class, 'scheduleOrder']);
        Route::get('orders/schedule/{orderId}', [OrderSecheualController::class, 'readScheduledOrder']);
        Route::delete('orders/schedule/{orderId}', [OrderSecheualController::class, 'deleteScheduledOrder']);
        Route::get('orders/schedule/{orderId}/summary', [OrderSecheualController::class, 'getScheduledOrdersSummary']);
        Route::post('orders/schedule/{orderId}/checkout', [OrderSecheualController::class, 'checkout']);

    });


    Route::prefix('orders/')->controller(OrderController::class)->group(function () {

        Route::post('/verify', 'verify')->name('orders.verify');
    });


});

Route::post('buy-points', [OrderController::class, 'buyPoints']);
// STRIPE
Route::post('stripe/webhook', [OrderController::class, 'stripePaymentWebhook']);
Route::middleware('auth:user')->group(function () {
    Route::get('stripe/payment-method', [OrderController::class, 'stripGetPaymentMethod']);
    Route::get('stripe/payment-method/{paymentMethodId}', [OrderController::class, 'stripShowPaymentMethod']);
    Route::post('stripe/payment-method/attach', [OrderController::class, 'stripeAttachPaymentMethod']);
    Route::delete('stripe/payment-method/detach', [OrderController::class, 'stripDetachPaymentMethod']);
    

});
