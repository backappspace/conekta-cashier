@component('mail::message')
# Hola {{ $user->name }}.

Te informamos que tu pago con número de serie {{ $order_id }} ha sido procesado.

Tu subscripción se ha actualizado.

Adjunto en este correo podrás encontrar tu comprobante de pago, es necesario que lo conserves para cualquiér aclaración.

# # Tu nueva fecha de pago es {{ $payment_date }}

Saludos,<br>
{{ config('app.name') }}
@endcomponent