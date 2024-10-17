<?php

namespace FriendsOfBotble\PayTr\Services\Gateways;

use FriendsOfBotble\PayTr\Models\Currency;
use FriendsOfBotble\PayTr\Services\Abstracts\PayTrPaymentAbstract;
use Illuminate\Http\Request;

class PayTrPaymentService extends PayTrPaymentAbstract
{
    public function makePayment(Request $request)
    {
    }

    public function afterMakePayment(Request $request)
    {
    }

    public function supportedCurrencyCodes(): array
    {
        return [
            Currency::TL,
            Currency::TRY,
            Currency::EUR,
            Currency::USD,
            Currency::GBP,
            Currency::RUB,
        ];
    }
}
