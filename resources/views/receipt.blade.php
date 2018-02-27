<html>
    <head>
        <style type="text/css">
            /* Reset -------------------------------------------------------------------- */
            *    { margin: 0;padding: 0; }
            body { font-size: 14px; }

            /* PS ----------------------------------------------------------------------- */

            h3 {
                margin-bottom: 10px;
                font-size: 15px;
                font-weight: 600;
                text-transform: uppercase;
            }

            .ps {
                width: 496px;
                border-radius: 4px;
                box-sizing: border-box;
                padding: 0 45px;
                margin: 40px auto;
                overflow: hidden;
                border: 1px solid #b0afb5;
                font-family: 'Open Sans', sans-serif;
                color: #4f5365;
            }

            .ps-reminder {
                position: relative;
                top: -1px;
                padding: 9px 0 10px;
                font-size: 11px;
                text-transform: uppercase;
                text-align: center;
                color: #ffffff;
                background: #000000;
            }

            .ps-info {
                margin-top: 26px;
                position: relative;
            }

            .ps-info:after {
                visibility: hidden;
                 display: block;
                 font-size: 0;
                 content: " ";
                 clear: both;
                 height: 0;
            }

            .ps-brand {
                width: 45%;
                float: left;
            }

            .ps-brand img {
                max-width: 150px;
                margin-top: 2px;
            }

            .ps-amount {
                width: 55%;
                float: right;
            }

            .ps-amount h2 {
                font-size: 36px;
                color: #000000;
                line-height: 24px;
                margin-bottom: 15px;
            }

            .ps-amount h2 sup {
                font-size: 16px;
                position: relative;
                top: -2px
            }

            .ps-amount p {
                font-size: 10px;
                line-height: 14px;
            }

            .ps-reference {
                margin-top: 14px;
            }

            h1 {
                font-size: 27px;
                color: #000000;
                text-align: center;
                margin-top: -1px;
                padding: 6px 0 7px;
                border: 1px solid #b0afb5;
                border-radius: 4px;
                background: #f8f9fa;
            }

            .ps-instructions {
                margin: 32px -45px 0;
                padding: 32px 45px 45px;
                border-top: 1px solid #b0afb5;
                background: #f8f9fa;
            }

            ol {
                margin: 17px 0 0 16px;
            }

            li + li {
                margin-top: 10px;
                color: #000000;
            }

            a {
                color: #1475ce;
            }

            .ps-footnote {
                margin-top: 22px;
                padding: 22px 20 24px;
                color: #108f30;
                text-align: center;
                border: 1px solid #108f30;
                border-radius: 4px;
                background: #ffffff;
            }
        </style>
    </head>
    <body>
        <div class="ps">
            <div class="ps-header">
                <div class="ps-reminder">Comprobante de pago: {{ $order->conekta_order }}.</div>
                <div class="ps-info">
                    <div class="ps-brand">
                        <img src="{{ env('APP_URL') . config('services.conekta.logo') }}" alt="{{ env('APP_NAME') }}">
                    </div>
                    <div class="ps-amount">
                        <h3>Monto pagado</h3>
                        <h2>$ {{ money_format('%n', $order->amount / 100) }} <sup>{{ $order->currency }}</sup></h2>
                        <p>Pagados el día: {{ $order->created_at->format('d-m-Y') }}</p>
                    </div>
                </div>
                <div class="ps-reference">
                    {{-- <h3>CLABE</h3> --}}
                    {{-- <h1>{{ $charge->payment_method->receiving_account_number }}</h1> --}}
                </div>
            </div>
            <div class="ps-instructions">
                <h3>{{ env('APP_NAME') }}</h3>
                <ol>
                    @forelse($order->subscriptions as $subscription)
                        <li>{{ $subscription->name }} ({{ $subscription->quantity }}) - ${{ money_format('%n', $subscription->unit_price) }} {{ $order->currency }}</li>
                    @empty
                        <li>Por favor ponte en contacto con el Staff de {{ env('APP_NAME') }}.</li>
                    @endforelse
                </ol>
                <div class="ps-footnote">Conserva este comprobante para cualquiér aclaración. <strong>{{ ENV('APP_NAME') }}</strong>.</div>
            </div>
        </div>
    </body>
</html>