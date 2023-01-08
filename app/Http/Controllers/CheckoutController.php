<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    publin function store(){
        $order=Order::create([
            'status'=>Order::Pending,
        ]);

       /*delay of 3600 seconds (1 hour); workers will not process this job before the hour passes*/

       //MonitorPendingOrder::dispatch($order)->deleay(3600);


      /*15 minutes until the user completes the checkout or we cancel the order after 1 hour.*/

      MonitorPendingOrder::dispatch($order)
        ->deleay(now()->addMinutes(15));

    }
}
