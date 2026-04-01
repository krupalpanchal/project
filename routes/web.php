<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payment', [PaymentController::class, 'index'])->name('payment.form');
Route::post('/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::get('/success', function () {
    return "Payment Successful!";
})->name('payment.success');
Route::get('/cancel', function () {
    return "Payment Canceled!";
})->name('payment.cancel');
Route::post('/checkout', [PaymentController::class, 'checkout']);
Route::post('/webhook', [PaymentController::class, 'webhook']);
Route::get('/refund/{id}', [PaymentController::class, 'refund']);
Route::get('/subscription', [PaymentController::class, 'subscription']);
