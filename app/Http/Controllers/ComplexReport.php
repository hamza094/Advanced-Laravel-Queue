<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComplexReport extends Controller
{
    public function report(){

        $report = Report::create();

        Bus::chain([
          new ExtractData($report),
           function () use ($report) {
            $jobs = $report->chunks->mapInto(
                   TransformChunk::class
            );

            Bus::batch($jobs)->then(function() use ($report) {

            Bus::chain([
               new StoreData($report),
               new GenerateSummary($report)
                 ])->dispatch();
                 })->dispatch();
            }
              ])->dispatch();
    }

    /*This code creates a new Report object using the create() method on the Report model. It then uses Laravel's Bus system to chain together a series of operations that will be executed one after the other.

The first operation in the chain is an instance of the ExtractData job class, which takes the $report object as a constructor argument. The second operation is an anonymous function that takes $report as a parameter. Inside this function, the code retrieves a collection of "chunks" from the $report object, and maps these chunks to an array of TransformChunk job objects.

The third operation is another call to the Bus system, which creates a new batch of jobs using the batch() method. The batch is initialized with the array of TransformChunk job objects, and a callback function is passed to the then() method. This callback function also takes $report as a parameter, and creates a new chain of operations using the chain() method on the Bus system.*/

/*This chain contains two more job classes: StoreData and GenerateSummary. Both of these job classes also take $report as a constructor argument. Finally, the chain is dispatched using the dispatch() method.*/

/*more on notes 122 page
move into seprate class
into seprate function
catch on failure*/
}
