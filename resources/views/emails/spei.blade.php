@component('mail::message')
# Hola, {{ $user->name }}

Has recibido este correo por que estás suscrito a Cupongo.

Adjunto en este correo podrás encontar tu ficha para depositar tu mensualidad.

Si tienes problemas abriendo el archivo adjunto puedes encontrar los datos de depósito aquí:

<strong>Cantidad: </strong> $ {{ money_format('%n', $charge->amount / 100) }} {{ $charge->currency }} <br>
<strong>CLABE: </strong> {{ $charge->payment_method->receiving_account_number }} <br>
<strong>Banco: </strong> {{ $charge->payment_method->receiving_account_bank }}

## Instrucciones

* Accede a tu banca en línea.
* Da de alta la CLABE en esta ficha. <strong>El banco deberá de ser {{ $charge->payment_method->receiving_account_bank }}</strong>.
* Realiza la transferencia correspondiente por la cantidad exacta en esta ficha, <strong>de lo contrario se rechazará el cargo</strong>.
* Al confirmar tu pago, el portal de tu banco generará un comprobante digital. <strong>En el podrás verificar que se haya realizado correctamente.</strong> Conserva este comprobante de pago.

Al completar estos pasos recibirás un correo de <strong>{{ ENV('APP_NAME') }}</strong> confirmando tu pago.

Gracias,<br>
{{ config('app.name') }}
@endcomponent