<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReportGenerate implements ShouldQueue
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

    public $tries = 10;
    public $maxExceptions = 2

    //We want to only allow 5 jobs belonging to a single customer to be running at the same time. Any more report generation requests from the same customer shall be delayed until 1 of the 5 slots is freed.
    //In our challenge, we're going to use the concurrency limiter:

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       Redis::funnel($this->report->customer_id)
       /*Now each slot will be freed once a report generation is completed, or if 5 minutes have passed.*/
        ->releaseAfter(5 * 60)
        /*By default, the limiter will wait 3 seconds before it gives up and releases the job. You can control the wait time by using the block method:*/
         ->block(5)
         /*limited the execution of the report generation to only 5 concurrent executions.*/
         ->limit(5)
         ->then(function () {
            $results = ReportGenerator::generate($this->report);
            $this->report->update([
               'status' => 'done',
                'results' => $results
               ]);
                }, function () {
                return $this->release(10);
            });
      }

    //In the previous challenge, we had to limit the concurrency of a report generation job to 5. In this example, we'll explore how we can limit generating reports to only 5 reports per hour

    //Instead of using Redis::funnel(), we're going to use another built-in limiter called Redis::throttle():

    public function handle()
    {
 
 Redis::throttle($this->report->customer_id)
             ->allow(5)
             ->every(60 * 60)
             ->then(function () {
            $results = ReportGenerator::generate($this->report);
            $this->report->update([
             'status' => 'done',
             'results' => $results
            ]);
            }, function () {
             return $this->release(60 * 10);
          });

/*In the example above, we're allowing 5 reports to be generated per hour. If all slots were occupied, we're going to release the job to be retried after 10 minutes.*/
    }



}
