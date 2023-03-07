<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Messenger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
   public function handle()
   {

    /* We have a Messager job in queue for each customer at all times. We want to find a way to remove this job from the queue in case we decided to deactivate a specific customer's account.
To do that, we can add a flag on the customer model is_active = true and on each run of the Messenger job, we're going to check for this flag:*/

     if (! $this->customer->is_active) {
 return $this->fail(
 new \Exception('Customer account is not active!');
 );
 }

    /* limit due to payload*/

     $messages = Messages::where('customer', $this->customer->id)
                 ->where('status', 'pending')
                 ->orderBy('timestamp')
                  ->limit(10)
                 ->get();

    /*If no messages are available, the job is going to release itself back to the queue to run again after 5 seconds*/

      if (! $messages->count()) {
           return $this->release(5);
 }


   /*When the Messager job runs for a customer, it's going to collect messages for that customer and put them in a chain under the Messenger job.*/

   foreach ($messages as $message) {
    // Put each message in the chain...
    $this->chained[] = $this->serializeJob(
    new ProcessMessage($message)
    );

 }
 /*After adding all messages to the chain, it's going to put a new instance of itself at the end of the chain so the job runs again after processing all jobs.*/
    $this->chained[] = $this->serializeJob(
    new self($this->customer)
   );
}

}
