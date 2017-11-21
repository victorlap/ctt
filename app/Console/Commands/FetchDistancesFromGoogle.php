<?php

namespace App\Console\Commands;

use App\Helpers\DistanceHelper;
use App\Helpers\TransportElementHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchDistancesFromGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ctt:fetch-distances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all the distances from Google';

    /**
     * The progressbar that is shown
     *
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $elements = app(TransportElementHelper::class)->getMonthsElements();

        $this->info("Trying to find distances for ". count($elements) ." routes.");
        $this->progressBar = $this->output->createProgressBar(count($elements));

        foreach ($elements as $element)
        {
            // We try to find a distanace for every element, this creates them in the database.
            $this->findDistanceAndSave(
                $element->address_from,
                $element->address_to,
                $element->address_from_code,
                $element->address_to_code
            );

            // Also calculate the way back
            $this->findDistanceAndSave(
                $element->address_to,
                $element->address_from,
                $element->address_to_code,
                $element->address_from_code
            );

            $this->progressBar->advance();
        }

        $this->progressBar->finish();
        $this->info("\nFinished.");
    }

    private function findDistanceAndSave($from, $to, $from_code, $to_code)
    {
        $distance = app(DistanceHelper::class)->find($from, $to, $from_code, $to_code);

        if($distance === null) {
            $this->progressBar->clear();
            $this->warn("\nCould not find distance for {$from_code} => {$to_code}");
            $this->progressBar->display();
        }
    }
}
