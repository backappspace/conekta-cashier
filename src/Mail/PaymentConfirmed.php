<?php

namespace UvealSnow\ConektaCashier\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $file;
    public $user;
    public $order_id;
    public $payment_date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $order_id, $payment_date, $file)
    {
        $this->file = $file;
        $this->user = $user;
        $this->order_id = $order_id;
        $this->payment_date = $payment_date;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('cashier::emails.payment-confirmed')
            ->from(env('MAIL_FROM_ADDRESS', 'no-reply@uvealsnow.com'))
            ->subject(env('APP_NAME', 'ConektaCashier') . ": Recivo $this->order_id")
            ->attach($this->file, [
                'as' => "recivo-$this->order_id.pdf",
                'mime' => 'application/pdf'
            ]);
    }
}
