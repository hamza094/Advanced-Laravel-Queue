<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $server;
  private $payload;

  public function __construct(Server $server, $payload)
  {
     $this->server = $server;
     $this->payload = $payload;
  }

   public $tries = 20;
   public $maxExceptions = 3;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$this->server->forge_server_id){
            $response = Http::timeout(5)->post(
            '.../servers', $this->payload
             )->throw()->json();

          $this->server->update([
         'forge_server_id' => $response['id']
          ]);

           return $this->release(120);
        }
          
        if ($this->server->stillProvisioning($this->server)) {
         return $this->release(60);
        }  

         $this->server->update([
            'is_ready' => true,
          ]);
    }

    public function failed(Exception $e)
    {
         Alert::create([
        // ...
        'message' => "Provisioning failed!",
       ]);
         
      $this->server->delete();

    }
}
