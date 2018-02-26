<?php

namespace UvealSnow\ConektaCashier\Controllers;

use PDF;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use UvealSnow\ConektaCashier\Mail\ConektaCharge;

class WebhookController extends Controller
{
    public function orderPaid(Request $request)
    {
        DB::table('tests')->insert([
            'data' => json_encode($request->all())
        ]);
        //
        return response('ok');
    }

    public function test()
    {
        $user = User::find(1);
        $sub = $user->subscriptions[0];

        $user->initConekta();

        $order = $user->createConektaOrder([$sub->asLineItem()]);

        $charge = $user->chargeOrder($order, [
            'type' => 'spei',
        ]);

        $temp = public_path() . "/tmp/$charge->id.pdf";

        PDF::loadView('cashier::stub', [
            'charge' => $charge,
        ])->save($temp)->stream('download.pdf');

        return new ConektaCharge($user, $charge, $temp);
    }
}
