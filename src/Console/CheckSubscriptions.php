<?php

namespace UvealSnow\ConektaCashier\Console;

use PDF;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use UvealSnow\ConektaCashier\Order;
use Illuminate\Support\Facades\Mail;
use UvealSnow\ConektaCashier\Mail\ConektaCharge;

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
                    $attachedSubscriptions = [];

                    if ($subscription->shouldBeCharged()) {
                        $lineItems[] = $subscription->asLineItem();
                        $attachedSubscriptions[] = $subscription->id;
                    }
                }

                if (count($lineItems) > 0) {
                    // init conekta
                    $user->initConekta();

                    // Create Conekta Order
                    $order = $user->createConektaOrder($lineItems);

                    // Retrieve the created charge object
                    $charge = $user->chargeOrder($order, [
                        'type' => 'spei'
                    ]);

                    // Add order to DB
                    $db_order = new Order();

                    $db_order->fillOrder($order);
                    $user->orders()->save($db_order);

                    foreach ($lineItems as $i => $item) {
                        $db_order->subscriptions()->attach($attachedSubscriptions[$i], [
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'details' => $item['name'],
                        ]);
                    }

                    // Update subscriptions
                    DB::table('subscriptions')
                        ->whereIn('id', $attachedSubscriptions)
                        ->update(['conekta_order_id' => $order->id]);

                    // make temporary path
                    $temp = public_path() . "/tmp/$charge->id.pdf";

                    // make and save charge pdf
                    PDF::loadView('cashier::stub', [
                        'charge' => $charge,
                    ])->save($temp);

                    // create and send email to user
                    Mail::to($user)->queue(new ConektaCharge(
                        $user,
                        $charge,
                        $temp,
                        'te has suscrito a ' . env('APP_NAME', 'Laravel')
                    ));

                    unlink($temp);
                }
            }
        });
    }
}
