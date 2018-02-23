<?php

namespace UvealSnow\ConektaCashier\Console;

use App\User;
use Illuminate\Console\Command;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conekta:subscriptions
            {--no-charge: Returns a list of subscriptions that should be charged, but does not charge them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if subscriptions should be paid';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        User::has('subscriptions')->chunk(50, function ($users) {
            foreach ($users as $user) {
                echo "$user->name \n";

                foreach ($user->subscriptions as $subscription) {
                    $lineItems = [];

                    if ($subscription->shouldBeCharged()) {
                        $lineItems[] = $subscription->asLineItem();
                    }

                    if (count($lineItems) > 0) {
                        $order = $user->createConektaOrder($lineItems);

                        $charge = $user->chargeOrder($order, [
                            'type' => 'spei'
                        ]);

                        // make temporary path
                        // $temp = "$charge->id.pdf";

                        // make and save charge pdf
                        // PDF::loadView('cashier.stub', [
                            // 'charge' => $charge
                        // ])->save($temp);

                        // create and send email to user
                        // Mail::to($user)->queue(new ConektaCharge($user, $charge, $file));
                    }
                }
            }
        });
    }
}
