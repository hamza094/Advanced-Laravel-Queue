<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MonitorPendingOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    $private $order;

    public $tries = 4;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      /*we'll check if the order was canceled or confirmed and just return from the handle() method*/
        
      if ($this->order->status == Order::CONFIRMED ||
       $this->order->status == Order::CANCELED) {
      return;
    } 

    /*When the job runs, we want to check if an hour has passed and cancel the order.*/


      if ($this->order->olderThan(59, 'minutes')) {
          $this->order->markAsCanceled();
       return;
    }

     
    /*If we're still within the hour period, then we'll send an SMS reminder and release the job back to the queue with a 15-minute delay.*/

    SMS::send(...);


    /*Using release() inside a job has the same effect as using 
    delay() while dispatching. The job will be released back to the queue and workers will run it again after 15 minutes.*/

    $this->release(now()->addMinutes(15));

    }
}

//Note: To increase the chance of your delayed jobs getting processed on time, you need to make sure you have enough workers to empty the queue as fast as possible. This way, by the time the job becomes available, a worker process will be available to run it