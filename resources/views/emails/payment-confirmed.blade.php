@component('mail::message')
# Hola {{ $user->name }}.

Te informamos que tu pago con número de serie: <br><br>
<strong style="text-align: center;">{{ $order_id }}</strong> <br><br>
ha sido procesado.

Tu subscripción se ha actualizado.

Adjunto en este correo podrás encontrar tu comprobante de pago, es necesario que lo conserves para cualquiér aclaración.

# # Tu nueva fecha de pago es: <strong>{{ $payment_date->format('d-M-Y') }}</strong>

Saludos,<br>
{{ config('app.name') }}
@endcomponent