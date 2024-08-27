<?php

namespace FriendsOfBotble\PayTr\Providers;

use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Payment\Supports\PaymentHelper;
use FriendsOfBotble\PayTr\Forms\PayTrPaymentMethodForm;
use FriendsOfBotble\PayTr\Services\Gateways\PayTrPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, function (?string $html, array $data) {
            PaymentMethods::method(PAYTR_PAYMENT_METHOD_NAME, [
                'html' => view('plugins/paytr::methods', $data)->render(),
            ]);

            return $html;
        }, 12, 2);

        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, function (array $data, Request $request) {
            if ($data['type'] != PAYTR_PAYMENT_METHOD_NAME) {
                return $data;
            }

            $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

            if (empty($paymentData['address']['email'])) {
                return [
                    ...$data,
                    'error' => true,
                    'message' => __('Please enter your email address.'),
                ];
            }

            try {
                $merchantId = get_payment_setting('merchant_id', PAYTR_PAYMENT_METHOD_NAME);
                $merchantKey = get_payment_setting('merchant_key', PAYTR_PAYMENT_METHOD_NAME);
                $merchantSalt = get_payment_setting('merchant_salt', PAYTR_PAYMENT_METHOD_NAME);

                $amount = $paymentData['amount'] * 100;
                $merchantOrderId = sprintf(
                    'PAYTR%s000OR%sCUSID%s',
                    Str::upper(Str::random(6)),
                    $paymentData['order_id'][0],
                    $paymentData['customer_id'] ?? 0,
                );

                $products = [];

                foreach ($paymentData['products'] as $product) {
                    $name = $product['name'];
                    $price = $product['price'] * 1.20;
                    $quantity = $product['qty'];
                    $products[] = [$name, $price, $quantity];
                }

                $basket = base64_encode(json_encode($products));

                $testMode = 1;
                $noInstallment = 0;
                $maxInstallment = 0;
                $currency = 'TL';

                $hash = sprintf(
                    '%s%s%s%s%d%s%d%d%s%d',
                    $merchantId,
                    $request->ip(),
                    $merchantOrderId,
                    $paymentData['address']['email'],
                    $amount,
                    $basket,
                    $noInstallment,
                    $maxInstallment,
                    $currency,
                    $testMode
                );

                $token = base64_encode(hash_hmac('sha256', sprintf('%s%s', $hash, $merchantSalt), $merchantKey, true));

                $response = Http::asForm()->post('https://www.paytr.com/odeme/api/get-token', [
                    'merchant_id' => $merchantId,
                    'user_ip' => $request->ip(),
                    'merchant_oid' => $merchantOrderId,
                    'email' => $paymentData['address']['email'],
                    'payment_amount' => $amount,
                    'paytr_token' => $token,
                    'user_basket' => $basket,
                    'debug_on' => 1,
                    'no_installment' => $noInstallment,
                    'max_installment' => $maxInstallment,
                    'user_name' => $paymentData['address']['name'],
                    'user_address' => sprintf('%s %s', $paymentData['address']['address'] ?: 'none', $paymentData['address']['city'] ?: 'none'),
                    'user_phone' => $paymentData['address']['phone'],
                    'merchant_ok_url' => route('payments.paytr.callback'),
                    'merchant_fail_url' => $paymentData['return_url'] ?? PaymentHelper::getCancelURL(),
                    'timeout_limit' => 30,
                    'currency' => $currency,
                    'test_mode' => $testMode,
                ])->json();

                if ($response['status'] !== 'success') {
                    $data['error'] = true;
                    $data['message'] = $response['reason'];

                    return $data;
                }

                echo view('plugins/paytr::paytr', [
                    'token' => $response['token'],
                ]);

                exit;
            } catch (Throwable $exception) {
                $data['error'] = true;
                $data['message'] = json_encode($exception->getMessage());
            }

            return $data;
        }, 12, 2);

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, function (?string $html) {
            return $html . PayTrPaymentMethodForm::create()->renderForm();
        }, 92);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class === PaymentMethodEnum::class) {
                $values['PAYTR'] = PAYTR_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 19, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYTR_PAYMENT_METHOD_NAME) {
                $value = 'PayTR';
            }

            return $value;
        }, 19, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYTR_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )->toHtml();
            }

            return $value;
        }, 19, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == PAYTR_PAYMENT_METHOD_NAME) {
                $data = PayTrPaymentService::class;
            }

            return $data;
        }, 19, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == PAYTR_PAYMENT_METHOD_NAME) {
                $paymentService = new PayTrPaymentService();
                $paymentDetail = $paymentService->getPaymentDetails($payment->charge_id);

                if ($paymentDetail) {
                    $data = view('plugins/paytr::detail', ['payment' => $paymentDetail, 'paymentModel' => $payment])->render();
                }
            }

            return $data;
        }, 19, 2);

        add_filter(PAYMENT_FILTER_GET_REFUND_DETAIL, function ($data, $payment, $refundId) {
            return $data;
        }, 19, 3);
    }
}
