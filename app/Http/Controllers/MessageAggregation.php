<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageAggregation extends Controller
{
   /* To do that, we're going to push a Messenger Job once a customer account is activated. This job is going to keep an eye on the messages database table and pushes the jobs in the chain under its control:*/

    Messenger::dispatch($customer);
}
