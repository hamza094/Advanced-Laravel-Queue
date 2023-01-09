<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /*remember, only one attempt is allowed by default when you start a worker. In our case, we want this job to retry for an unlimited number of times since we already have an expiration in place. To do this, we need to set the $tries public property to 0:*/

     public $tries = 0;


    private $integration;
    private $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Integration $integration,Event $event)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
      /*We configure a 5-second timeout on the client. This will
      prevent the job from getting stuck waiting indefinitely for a response.Always set a reasonable timeout when making requests from your code.*/

        $response = Http::timeout(5)->post(
            $this->integration->url,
            $this->event->data
        );

        if ($response->failed()) {
            $this->release(
           now()->addMinutes(15 * $this->attempts())
           );
        }

        /* The attempts() method returns the number of times the job has been attempted. If it's the first run, attempts() will return 1.*/
    }

    public function retryUntil()
    {
       return now()->addDay();
    }


    /* When a job exceeds the assigned number of attempts or reaches the expiration time, the worker is going to call this failed() method and pass a MaxAttemptsExceededException exception.In other words, failed() is called when the worker is done retrying this job.*/

    public function failed(Exception $e)
    {
        Mail::to(
        $this->integration->developer_email
        )->send(...);
    }


}
