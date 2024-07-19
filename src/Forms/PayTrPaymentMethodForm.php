<?php

namespace FriendsOfBotble\PayTr\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Forms\PaymentMethodForm;

class PayTrPaymentMethodForm extends PaymentMethodForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(PAYTR_PAYMENT_METHOD_NAME)
            ->paymentName('PayTR')
            ->paymentDescription(__('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'PayTR']))
            ->paymentLogo(url('vendor/core/plugins/paytr/images/paytr.jpg'))
            ->paymentUrl('https://www.paytr.com/')
            ->paymentInstructions(view('plugins/paytr::instructions')->render())
            ->add(
                get_payment_setting_key('merchant_id', PAYTR_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/paytr::paytr.merchant_id'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_id', PAYTR_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                get_payment_setting_key('merchant_key', PAYTR_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/paytr::paytr.merchant_key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_key', PAYTR_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                get_payment_setting_key('merchant_salt', PAYTR_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/paytr::paytr.merchant_salt'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_salt', PAYTR_PAYMENT_METHOD_NAME))
                    ->toArray()
            );
    }
}
