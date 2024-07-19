<?php

namespace FriendsOfBotble\PayTr;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'payment_paytr_name',
            'payment_paytr_description',
            'payment_paytr_merchant_id',
            'payment_paytr_merchant_key',
            'payment_paytr_merchant_salt',
            'payment_paytr_status',
        ]);
    }
}
