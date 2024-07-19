<?php

namespace FriendsOfBotble\PayTr\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
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
        $orderId = Str::afterLast($oid, '000OR');

        if (! $orderId) {
            echo 'PAYTR notification failed: bad merchant_oid';
            exit;
        }

        $orders = Order::query()->where('id', '=', $orderId)->first();

        if ($orders['payment_id'] === null) {
            if ($request['status'] == 'success') {

                //# BURADA YAPILMASI GEREKENLER
                //# 1) Siparişi onaylayın.
                //# 2) Eğer müşterinize mesaj / SMS / e-posta gibi bilgilendirme yapacaksanız bu aşamada yapmalısınız.
                //# 3) 1. ADIM'da gönderilen payment_amount sipariş tutarı taksitli alışveriş yapılması durumunda
                //# değişebilir. Güncel tutarı $post['total_amount'] değerinden alarak muhasebe işlemlerinizde kullanabilirsiniz.

                do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                    'amount' => $request['payment_amount'] / 100,
                    'currency' => $request['currency'],
                    'charge_id' => $request['merchant_oid'],
                    'payment_channel' => PAYTR_PAYMENT_METHOD_NAME,
                    'status' => PaymentStatusEnum::COMPLETED,
                    'customer_id' => $orders['user_id'],
                    'customer_type' => "Botble\Ecommerce\Models\Customer",
                    'payment_type' => $request['payment_type'],
                    'order_id' => $orders['id'],
                ], $request);

            } else {

                //# BURADA YAPILMASI GEREKENLER
                //# 1) Siparişi iptal edin.
                //# 2) Eğer ödemenin onaylanmama sebebini kayıt edecekseniz aşağıdaki değerleri kullanabilirsiniz.
                //# $post['failed_reason_code'] - başarısız hata kodu
                //# $post['failed_reason_msg'] - başarısız hata mesajı

            }

            //# Bildirimin alındığını PayTR sistemine bildir.
            echo 'OK';
            exit;
        } else {
            echo 'OK';
            exit;
        }
    }
}
