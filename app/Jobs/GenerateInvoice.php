<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$month,$exchangeRate)
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Amount due in USD
       $amount = UsageMeter::calculate($this->user, $this->month);

       $amountInLocalCurrency = $amount * $this->exchangeRate;

    $taxInLocalCurrency = ($amount * 14 / 100) * $this->exchangeRate;

    $total = $amountInLocalCurrency + $taxInLocalCurrency;

    Mail::to($this->user)->send("Your usage last month was
                           {$total}{$this->user->currency}");
    }
}
