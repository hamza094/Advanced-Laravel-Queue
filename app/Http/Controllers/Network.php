<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Network extends Controller
{
 /*Unlike batches, jobs inside a chain are processed in sequence. If a job didn't report completion, it'll be retried until it either succeeds and the next job in the chain is dispatched or fails and the whole chain is deleted.*/
    Bus::chain(
 new EnsureANetworkExists(),
 new EnsureNetworkHasInternetAccess(),
 new CreateDatabase($database)
)->catch(function() {
 /*First, we check if the network isn't attached to any other resources, that means it's safe to delete it so it doesn't cost us while not being used.However, if it is used by other resources but they don't need internet access—no active databases—we can only remove internet access while keeping the network.*/   
 $network = Network::first();
 if (! $network->usedByOtherResources()) {
 $network->delete();
 return;
 }
 if (! $network->activeDatabases()->count()) {
 $network->removeInternetAccess();
 }
})->dispatch()
}
