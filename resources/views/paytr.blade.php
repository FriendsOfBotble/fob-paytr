<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('PayTR') }}</title>
    <style>
        body{
            padding: 0;
            margin:0;
        }
        .header{
            display: flex;
            padding: 20px;
            margin-bottom: -15px;
            position: relative;
            z-index: 99;
            pointer-events: none;
        } 
        .custom-bg-green {
            background-color: #5db634;
        }

        .custom-text-white {
            color: white;
        }

        .custom-padding {
            padding: 0.5rem;
        }

        .custom-text-center {
            text-align: center;
        }

        .custom-font-size-13 {
            font-size: 13px;
            font-family: Verdana, Geneva, Tahoma, sans-serif
        }

        .custom-flex {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-position-absolute {
            position: absolute;
        }

        .custom-checkout-logo {
            display: inline-block;
            margin-left: auto;
            margin-right: auto;
        }

        .custom-iframe {
            width: 100%;
        }

    </style>
   
</head>
<body>

    <div class="custom-bg-green custom-text-white custom-padding custom-flex">
        <p class="mb-0 custom-text-center custom-font-size-13">
            {{ __('All purchases you make on this site are protected with 256 bit SSL..') }}
        </p>
    </div>
    <div id="main-checkout-product-info"> 
        <div>
            <div class=" header">
                @php
                    $logo = theme_option('logo_in_the_checkout_page') ?: theme_option('logo');
                @endphp
    
                @if ($logo)
                    <div class="custom-checkout-logo"> 
                        <img
                            src="{{ RvMedia::getImageUrl($logo) }}"
                            alt="{{ theme_option('site_title') }}"
                        /> 
                    </div> 
                @endif
    
            </div>
            <script>
                $(document).ready(function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });
            </script>
            <div>
                <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                <iframe src="https://www.paytr.com/odeme/guvenli/{{ $token }}" id="paytriframe" frameborder="0" scrolling="no" class="custom-iframe"></iframe>
                <script>iFrameResize({},'#paytriframe');</script>
            </div>
        </div>
    </div>

    
     

<script type="text/javascript" src="{{ asset('vendor/core/core/js-validation/js/js-validation.js') }}"></script>

</body>
</html>

