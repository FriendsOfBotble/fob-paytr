@if ($payment)
    @if ($payment->amount_refunded)
        <h6 class="alert-heading mt-4">{{ trans('plugins/payment::payment.amount_refunded') }}:
            {{ $payment->amount_refunded / 100 }} {{ $payment->currency }}
        </h6>
    @endif

    @if ($refunds = Arr::get($paymentModel->metadata, 'refunds', []))
        @foreach ($refunds as $refund)
            <div id="{{ Arr::get($refund, 'data.id') }}" class="mt-4">
                @include('plugins/paytr::refund-detail')
            </div>
        @endforeach
    @endif


    <div class="mt-4">
        @include('plugins/payment::partials.view-payment-source')
    </div>
@endif
