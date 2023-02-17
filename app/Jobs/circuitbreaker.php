<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class circuitbreaker implements ShouldQueue
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

/* What we want to implement here is called "The Circuit Breaker" pattern. It's a way to prevent failure from constantly recurring. Once we notice a certain service is failing multiple consecutive times, we're going to stop sending requests to it for a while and give it some time to recover.*/

/* To implement this pattern, we need to count the number of consecutive failures during a period of time. We're going to store this count in a cache key:*/


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    /*If the circuit is open, we're going to release the job back to the queue with a
delay varying between 1 and 120 seconds after the circuit closes back again. The reason for the random delay is to prevent flooding the service with too many requests once the circuit closes again.*/

       if ($lastFailureTimestamp = Cache::get('circuit:open')) {
 if (time() - $lastFailureTimestamp < 8 * 60) {
 return $this->release(
 $lastFailureTimestamp + 600 + rand(1, 120)
 );
 } else {
 $halfOpen = true;
 }
 }

Redis::funnel('slow_service')
 ->limit(5)
 ->then(function () {
        $response = Http::acceptJson()
                   ->timeout(10)
                   ->get('...');

        if ($response->serverError()) {

        if (! Cache::get('failures')) {
            Cache::put('failures', 1, 60);
          } else {
            Cache::increment('failures');
        }

 /*Here if the number of consecutive failures in a one-minute window crosses 10, we're going to store a lock in the cache that holds the current timestamp and expires after 10 minutes.*/

         if (Cache::get('failures') > 10) {
          Cache::put('circuit:open', time(), 600);
        }

          return $this->release(600);
        }
    }
    }
}
