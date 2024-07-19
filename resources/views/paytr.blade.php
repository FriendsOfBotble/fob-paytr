@extends('plugins/ecommerce::orders.master')

@section('title', __('Checkout'))

@section('content')
    @include('plugins/payment::partials.header')

    <div class="container" id="main-checkout-product-info">
        <div class="row">
            <div class="col-12 left" style="border-right: 0;padding-right:0;padding-bottom:0;padding-left:0;">
                <div class="d-block" style="text-align: center;margin-bottom:-16px;">
                    @include('plugins/ecommerce::orders.partials.logo')
                </div>
                <div style="background:#5db634;margin-bottom:15px;" class="text-white ps-3 pe-3 pt-1 pb-1 d-flex align-items-center justify-content-center">
                    <p class="mb-0">
                        <i class="fas fa-lock mr-1"></i>&nbsp;Bu sitede yapacağınız tüm alışverişler 256 bit SSL ile korunmaktadır. <i class="fas fa-question-circle ml-1" data-toggle="tooltip" data-placement="top" title="Bilgileriniz orijinal haliyle değil SSL ile şifrelenerek güvenli bir şekilde gönderilir."></i>
                    </p>
                </div>
                <script>
                    $(document).ready(function(){
                        $('[data-toggle="tooltip"]').tooltip();
                    });
                </script>
                <div class="col-12">
                    <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                    <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
                    <script>iFrameResize({},'#paytriframe');</script>
                </div>
            </div>
        </div>
    </div>
@stop

@push('footer')
    <script type="text/javascript" src="{{ asset('vendor/core/core/js-validation/js/js-validation.js') }}"></script>

    {!! JsValidator::formRequest(
        Botble\Ecommerce\Http\Requests\SaveCheckoutInformationRequest::class,
        '#checkout-form',
    ) !!}
@endpush
