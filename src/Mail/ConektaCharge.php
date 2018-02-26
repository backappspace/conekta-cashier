<?php

namespace UvealSnow\ConektaCashier\Mail;

use App\User;
use Conekta\Charge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConektaCharge extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $concept;
    public $charge;
    public $file;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Charge $charge, string $file, $concept = null)
    {
        $this->user = $user;
        $this->concept = $concept;
        $this->charge = $charge;
        $this->file = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('cashier::emails.spei')
            ->from(env('MAIL_FROM_ADDRESS', 'no-reply@uvealsnow.com'))
            ->subject('Ficha de pago SPEI '. now()->format('d-m-Y'))
            ->attach($this->file, [
                'as' => 'spei_'. now()->format('d-m-Y') . '.pdf',
                'mime' => 'application/pdf'
            ]);
    }
}
