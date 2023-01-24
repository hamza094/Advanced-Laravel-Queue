<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConferenceController extends Controller
{
  public function store(Conference $conference)
  {
    $attendee = null;

     DB::transaction(function() use ($conference) {

        $attendee = Attendee::create([
          'conference_id' => $conference->id,
          'name' => request('name'),
          'reference' => $reference = Str::uuid()
        ]);

        $invoice = BillingProvider::invoice([
          'customer_reference' => $reference,
          'card_token' => request('card_token'),
        ]);
    });
         SendTicketInformation::dispatch($attendee);

}

}
