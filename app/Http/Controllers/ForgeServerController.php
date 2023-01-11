<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ForgeServerController extends Controller
{
    $server = Server::create([
     'is_ready' => false,
    'forge_server_id' => null
    ]);

   ProvisionServer::dispatch($server, request('server_payload'));

}
