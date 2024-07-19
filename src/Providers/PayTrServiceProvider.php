<?php

namespace FriendsOfBotble\PayTr\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class PayTrServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this->setNamespace('plugins/paytr')
            ->loadHelpers()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadAndPublishTranslations()
            ->loadRoutes();

        $this->app->register(HookServiceProvider::class);
    }
}
