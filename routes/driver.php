<?php

use App\Http\Middleware\ApiLang;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\Driver\OrderController;
use App\Http\Controllers\Api\Driver\ReviewController;
use App\Http\Controllers\Api\Driver\FinanceController;
use App\Http\Controllers\Api\Driver\ProfileController;
use App\Http\Controllers\Api\Driver\AuthenticationController;
use App\Http\Controllers\Api\Driver\DriverRegisterController;


Route::get('/unauthorized', function () {
    return response()->json([
        'message' => 'Unauthorized'
    ], 401);    

})->name('login');


Route::prefix('driver/') ->middleware(ApiLang::class)->group(function () {


    Route::prefix('auth/')->controller(AuthenticationController::class)->group(function () {
        Route::prefix('register/')->group(function () {
            Route::post('step1', [DriverRegisterController::class, 'registerStep1']);
            Route::post('step2', [DriverRegisterController::class, 'registerStep2']);
            Route::post('step3', [DriverRegisterController::class, 'registerStep3']);
            Route::post('step4', [DriverRegisterController::class, 'registerStep4']);
        });
        Route::post('login', 'login');
        Route::post('logout', 'logout')->middleware('auth:driver');
        Route::post('verify-otp', 'verifyOtp');
        Route::post('resend-otp', 'resendOtp');
        Route::post('forget-password', 'forgetPassword');
        Route::post('reset-password', 'resetPassword');
        Route::get('vehicle-type', 'vehicleType');
    });


    Route::middleware('auth:driver')->controller(ProfileController::class)->group(function () {
        Route::get('profile', 'show');
        Route::post('profile/update', 'updateProfile');
        Route::post('logout', 'logout');
    });

    Route::middleware('auth:driver')->controller(ReviewController::class)->group(function () {
        Route::get('reviews', 'index');
    });


    Route::prefix('orders/')->middleware('auth:driver')->controller(OrderController::class)->group(function () {
        Route::get('new', 'newOrder');
        Route::post('book-order/{orderID}', 'bookOrder');
        Route::post('receive-order-branch/{orderID}', 'receiveOrderBranch');
        Route::get('on-delivery', 'onDelivery');
        Route::get('delivered', 'delivered');
        Route::get('cancelled', 'cancelled');
        // Route::post('/verify', 'verify')->name('orders.verify');
    });

    Route::get('terms-conditions', [PageController::class, 'termsConditionsDriver']);

    Route::middleware('auth:driver')->group(function () {
        Route::get('finance/completed-orders', [FinanceController::class, 'completedOrders']);
        Route::get('finance/cancelled-orders', [FinanceController::class, 'cancelledOrders']);
        Route::get('finance/history-orders',   [FinanceController::class, 'historyOrders']);
    });
});
