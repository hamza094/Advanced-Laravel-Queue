<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

/* Here we set $tries = 0 so the worker never marks the job as failed.*/

    public $tries = 0;

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

    /* This job has to be fault-tolerant; if the job is marked as failed, next jobs in the chain will never run and the customer will stop receiving new messages. For that reason, we need to let the job retry indefinitely and handle the failure ourselves:
  */

public function handle()
{
  /*At the
same time, we check if the job has been attempted more than 5 times,
report the incident, skip the job, and run the next job in the chain.*/   
 if ($this->attemps() > 5) {
 // Log the failure so we can investigate
 Log::error(...);
 // Return immediately so the next job in the chain is run.
 return;
 }
 Intercom::send($this->message);
 $this->message->update([
 'status' => 'sent'
 ]);
}
}
