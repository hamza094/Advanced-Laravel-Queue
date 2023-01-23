<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RefundInvoiceController extends Controller
{
    public function invoice(Conference $conference){

      $jobs = $conference->attendees
              ->map(function($attendee) {
                return new RefundAttendee($attendee)
            });


    /* Using batch() instructs the bus to collect and store all the jobs in a single insert query.*/

    /*To force the batch to continue even if some of the jobs fail, we may use the allowFailures() method:*/

    /*If all the jobs in the batch finish successfully, Laravel then()*/

    /* Similar to then(), using catch() allows us to execute a callback on the first failure.*/

    /*Callbacks registered using the finally() method will be called after all the batch jobs are done. Some of these jobs might have completed successfully while others might have failed.*/ 

      $batch=Bus::batch($jobs)
            ->allowFailures()
            ->then(function ($batch) {
             $conference = Conference::firstWhere(
              'refunds_batch', '=', $batch->id
              );
               Mail::to($conference->organizer)->send(
              'All attendees were refunded successfully!'
            );
          })
            ->catch(function ($batch, $e) {
           $conference = Conference::firstWhere(
           'refunds_batch', '=', $batch->id
            );
             Mail::to($conference->organizer)->send(
             'We failed to refund some of the attendees!'
            );
          })
            ->finally(function ($batch) {
             $conference = Conference::firstWhere(
              'refunds_batch', '=', $batch->id
             );
             Mail::to($conference->organizer)->send(
              'Refunding attendees completed!'
            );
          })
            ->dispatch();

        /*We can extract the batch ID from this instance and
          store it in the conference model for future reference*/

          $conference->update([
           'refunds_batch' => $batch->id
         ]);
    
    }

    public function check(Conference $conference){

    /* Using a reference to the batch, we can show the conference organizer information about the progress of the refund process:*/

       $batch = Bus::findBatch(
      $conference->refunds_batch
      );
         
      return [
           'progress' => $batch->progress().'%',
           'remaining_refunds' => $batch->pendingJobs,
           'has_failures' => $batch->hasFailures(),
           'is_cancelled' => $batch->canceled()
          ];
    }
}
