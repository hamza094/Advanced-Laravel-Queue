<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RateLimit implements ShouldQueue
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

    public $tries = 0;
    public $maxExceptions = 3;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($timestamp = Cache::get('api-limit')) {

          return $this->release($timestamp - time());
    }

       $response = Http::acceptJson()
                        ->timeout(10)
                        ->withToken('...')
                        ->get('https://...');

        if ($response->failed() && $response->status() == 429) {
            $secondsRemaining = $response->header('Retry-After');

           Cache::put(
            'api-limit',
             now()->addSeconds($secondsRemaining)->timestamp,
             $secondsRemaining
            );

            return $this->release($secondsRemaining)
    }

    public function retryUntil()
    {
       return now()->addHours(12);
    }
}
