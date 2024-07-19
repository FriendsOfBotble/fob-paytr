@if (get_payment_setting('status', PAYTR_PAYMENT_METHOD_NAME) == 1)
    <x-plugins-payment::payment-method
        :name="PAYTR_PAYMENT_METHOD_NAME"
        paymentName="PayTR"
    />
@endif

