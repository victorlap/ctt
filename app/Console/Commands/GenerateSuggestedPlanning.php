<?php

namespace App\Console\Commands;

use App\Distance;
use App\Helpers\TransportElementHelper;
use App\Route;
use App\Truck;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSuggestedPlanning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ctt:generate-planning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Suggested Planning';

    private $transportElementHelper;

    private $trucksOnRoute = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransportElementHelper $transportElementHelper)
    {
        parent::__construct();

        $this->transportElementHelper = $transportElementHelper;

        // Prepare trucks on route array
        foreach (Truck::all() as $truck) {
            $this->trucksOnRoute[$truck->id] = [];
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->deleteOldRoutes();

        $elements = app(TransportElementHelper::class)->getTodaysElements();

        $routes = collect();

        foreach ($elements as $element) {
            $duration = $this->getDuration($element);

            $start = Carbon::parse($element->start);
            $end = Carbon::parse($element->start)->addMinutes($duration);

            $truck = $this->getFreeTruck($start, $end);
            $this->addDuration($truck, $start, $end);

            $route = new Route([
                'seq' => $element->seq,
                'start' => $start,
                'end' => $end
            ]);
            $route->truck()->associate($truck);
            $route->save();

            $routes->push($route);
        }
    }

    public function getFreeTruck()
    {
        foreach ($this->trucksOnRoute as $truckId => $blocks) {
            if(empty($blocks)) {
                return $truckId;
            }


        }
    }

    public function addDuration(Truck $truck, $start, $end)
    {
        $this->trucksOnRoute[$truck->id][] = [
            'start' => $start,
            'end' => $end,
        ];
    }

    public function getDuration($element)
    {
        $stops = collect([
            $element->ADDRESS_FROM,
            $element->ADDRESS_STOP1,
            $element->ADDRESS_STOP2,
            $element->ADDRESS_STOP3,
            $element->ADDRESS_TO
        ])->filter();

        $duration = 0;

        for($i = 0; $i < $stops -1; $i++) {
            $distance = Distance::where([
                'address_from_code' => $stops[$i],
                'address_to_code' => $stops[$i+1]
            ])->first();

            // Get Duration to seconds and add a stop time
            $duration += ($distance->duration_value * 1.3 / 60) + 30;
        }

        return $duration;
    }

    public function deleteOldRoutes() {
        DB::table('routes')->delete();
    }
}
