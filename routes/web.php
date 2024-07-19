<?php

use FriendsOfBotble\PayTr\Http\Controllers\PayTrController;
use Illuminate\Support\Facades\Route;

Route::middleware('core')->group(function () {
    Route::post('payment/paytr/webhook', [PayTrController::class, 'webhook'])
        ->name('payments.paytr.webhook');
});
