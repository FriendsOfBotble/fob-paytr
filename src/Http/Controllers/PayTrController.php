<?php

namespace FriendsOfBotble\PayTr\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Order as EcommerceOrder;
use Botble\Hotel\Models\Booking;
use Botble\JobBoard\Models\Transaction as TransactionJobBoard;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Events\PaymentWebhookReceived;
use Botble\RealEstate\Models\Transaction as TransactionRealEstate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayTrController extends BaseController
{
    public function webhook(Request $request)
    {
        $merchant_key = get_payment_setting('merchant_key', PAYTR_PAYMENT_METHOD_NAME);
        $merchant_salt = get_payment_setting('merchant_salt', PAYTR_PAYMENT_METHOD_NAME);

        $hash = base64_encode(hash_hmac('sha256', $request['merchant_oid'] . $merchant_salt . $request['status'] . $request['total_amount'], $merchant_key, true));

        if ($hash != $request['hash']) {
            exit('PAYTR notification failed: bad hash');
        }
        $oid = $request['merchant_oid'] ?? '';
        $orderId = Str::of($oid)->after('000OR')->before('CUSID')->toString();
        $customerId = Str::afterLast($oid, 'CUSID');

        if (! $orderId) {
            echo 'PAYTR notification failed: bad merchant_oid';
            exit;
        }

        $orderModel = match (true) {
            is_plugin_active('ecommerce') => EcommerceOrder::class,
            is_plugin_active('job-board') => TransactionJobBoard::class,
            is_plugin_active('hotel') => Booking::class,
            is_plugin_active('real-estate') => TransactionRealEstate::class,
            default => null,
        };

        $customerType = match (true) {
            is_plugin_active('ecommerce') => "Botble\Ecommerce\Models\Customer",
            is_plugin_active('job-board') => "Botble\JobBoard\Models\Account",
            is_plugin_active('hotel') => "Botble\Hotel\Models\Customer",
            is_plugin_active('real-estate') => "Botble\RealEstate\Models\Account",
            default => null,
        };

        $order = $orderModel::query()->where('id', $orderId)->first();

        if ($order && $order->payment_id) {
            echo 'OK';
            exit;
        }

        if ($request['status'] == 'success') {

            //# BURADA YAPILMASI GEREKENLER
            //# 1) Siparişi onaylayın.
            //# 2) Eğer müşterinize mesaj / SMS / e-posta gibi bilgilendirme yapacaksanız bu aşamada yapmalısınız.
            //# 3) 1. ADIM'da gönderilen payment_amount sipariş tutarı taksitli alışveriş yapılması durumunda
            //# değişebilir. Güncel tutarı $post['total_amount'] değerinden alarak muhasebe işlemlerinizde kullanabilirsiniz.

            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'amount' => $request['total_amount'] / 100,
                'currency' => $request['currency'],
                'charge_id' => $chargeId = $request['merchant_oid'],
                'payment_channel' => PAYTR_PAYMENT_METHOD_NAME,
                'status' => PaymentStatusEnum::COMPLETED,
                'customer_id' => $customerId,
                'customer_type' => $customerType,
                'payment_type' => $request['payment_type'],
                'order_id' => [$orderId],
            ], $request);

            PaymentWebhookReceived::dispatch($chargeId);
        } else {

            //# BURADA YAPILMASI GEREKENLER
            //# 1) Siparişi iptal edin.
            //# 2) Eğer ödemenin onaylanmama sebebini kayıt edecekseniz aşağıdaki değerleri kullanabilirsiniz.
            //# $post['failed_reason_code'] - başarısız hata kodu
            //# $post['failed_reason_msg'] - başarısız hata mesajı

        }

        echo 'OK';
        exit;
    }
}
