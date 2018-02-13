<?php

namespace UvealSnow\ConektaCashier\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function test()
    {
        echo "loquilla";
    }

    public function orderPaid(Request $request)
    {
        return true;
    }
}
