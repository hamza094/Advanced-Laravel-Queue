<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function invoice(){
      foreach (User::all() as $user) {
        //GenerateInvoice::dispatch($user, $month);


    /*It's always a good practice to ensure queued jobs are self-contained; that each job has everything it needs to run.
So, while dispatching the jobs, we're going to get a fresh exchange rate and then pass it to each job:*/

      $exchangeRate = Currency::exchangeRateFor($user->currency);
      
      GenerateInvoice::dispatch($user, $month, $exchangeRate);

    }



    }
}
