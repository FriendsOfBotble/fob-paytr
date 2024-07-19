<?php

namespace FriendsOfBotble\PayTr\Services\Abstracts;

use Botble\Payment\Models\Payment;
use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\Support\Services\ProduceServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

abstract class PayTrPaymentAbstract implements ProduceServiceInterface
{
    use PaymentErrorTrait;

    protected bool $supportRefundOnline;

    public function __construct()
    {
        $this->supportRefundOnline = true;
    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }

    public function getPaymentDetails($paymentId)
    {
        try {
            $response = Payment::query()->where('charge_id', '=', $paymentId)->first();
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }

        return $response;
    }

    public function refundOrder($paymentId, $amount, array $options = []): array
    {
        try {
            $merchantId = get_payment_setting('merchant_id', PAYTR_PAYMENT_METHOD_NAME);
            $merchantKey = get_payment_setting('merchant_key', PAYTR_PAYMENT_METHOD_NAME);
            $merchantSalt = get_payment_setting('merchant_salt', PAYTR_PAYMENT_METHOD_NAME);

            $merchantOrderId = $options['order_id'];

            $token = base64_encode(hash_hmac('sha256', $merchantId . $merchantOrderId . $amount . $merchantSalt, $merchantKey, true));

            $response = Http::asForm()->post('https://www.paytr.com/odeme/iade', [
                'merchant_id' => $merchantId,
                'merchant_oid' => $merchantOrderId,
                'return_amount' => $amount,
                'paytr_token' => $token,
            ])->json();

            if ($response['status'] == 'success') {
                $response = array_merge($response, ['_refund_id' => Arr::get($response, 'merchant_oid')]);

                return [
                    'error' => false,
                    'message' => $response['status'],
                    'data' => $response,
                ];
            } else {
                return [
                    'error' => true,
                    'message' => $response['err_no'] . ' - ' . $response['err_msg'],
                ];
            }
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function getRefundDetails($refundId): void
    {
    }

    public function execute(Request $request): bool
    {
        try {
            return $this->makePayment($request);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }
    }

    abstract public function makePayment(Request $request);

    abstract public function afterMakePayment(Request $request);
}
