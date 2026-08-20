<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AdminLoginController;

// Home Route...
Route::get('/', [AdminLoginController::class, 'login'])->name('qpanel');

// Admin Route...
Route::group(['prefix' => '', 'as' => 'admin.'], function () {
    Route::get('/qpanel', [AdminLoginController::class, 'login'])->name('qpanel');
    Route::post('/qlogin', [AdminLoginController::class, 'login']);
    Route::get('/login-check', [AdminLoginController::class, 'loginCheck'])->name('loginCheck');
    Route::any('/logout', [AdminLoginController::class, 'logout'])->name('logout');
});


// CKEditor files management only for authenticated user
Route::view('ckfinder/ckfinder', 'ckfinder.ckfinder')->name('ckfinder.ckfinder')->middleware('auth:admin');

// SSLCommerz Subscription Payment Callback Routes
use App\Http\Controllers\SubscriptionPaymentController;

Route::match(['get', 'post'], 'success-from-ssl', [SubscriptionPaymentController::class, 'success'])->name('subscription.payment.success');
Route::match(['get', 'post'], 'failed-from-ssl', [SubscriptionPaymentController::class, 'fail'])->name('subscription.payment.fail');
Route::match(['get', 'post'], 'cancel-from-ssl', [SubscriptionPaymentController::class, 'cancel'])->name('subscription.payment.cancel');
Route::post('ipn', [SubscriptionPaymentController::class, 'ipn'])->name('subscription.payment.ipn');
