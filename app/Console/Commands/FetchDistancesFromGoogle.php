<?php

namespace App\Console\Commands;

use App\Helpers\DistanceHelper;
use Illuminate\Console\Command;

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
        $elements = $this->getElements();

        $this->info("Trying to find distances for ". count($elements) ." distances.");

        foreach ($elements as $element)
        {
            // We try to find a distanace for every element, this creates them in the database.
            $distance = app(DistanceHelper::class)->find($element->address_from, $element->address_to);

            if($distance === null) {
                $this->warn("Could not find distance for {$element->address_from_code} => {$element->address_to_code}");
                continue;
            }

            $distance->address_from_code = $element->address_from_code;
            $distance->address_to_code = $element->address_to_code;
            $distance->save();
        }

        $this->info("Finished.");
    }

    private function getElements()
    {
        return \DB::connection('oracle')->select("
SELECT
  ADDRESS_TO.ZIPCODE || ' ' || ADDRESS_TO.CITY || ' ' || ADDRESS_TO.COUNTRY1 \"ADDRESS_TO\",
  ADDRESS_FROM.ZIPCODE || ' ' || ADDRESS_FROM.CITY || ' ' || ADDRESS_FROM.COUNTRY1 \"ADDRESS_FROM\",
  TRANSPORTELEMENT.ADDRESS_FROM \"ADDRESS_FROM_CODE\",
  COALESCE(TRANSPORTELEMENT.ADDRESS_STOP1, TRANSPORTELEMENT.ADDRESS_TO,
           TRANSPORTELEMENT.ADDRESS_STOPSHOW) \"ADDRESS_TO_CODE\",
  TO_DATE(COALESCE(TRANSPORTELEMENT.DATEPLANNED1, TRANSPORTELEMENT.AFKOPPELDATE, TRANSPORTELEMENT.SHOWDATEPLANNED)
          || ' '
          || COALESCE(TRANSPORTELEMENT.TIMEPLANNED1, TRANSPORTELEMENT.AFKOPPELTIME, TRANSPORTELEMENT.SHOWTIMEPLANNED),
          'YYYY-MM-DD \"00:00:00\" HH24MI') \"DATETIME_TO\"

FROM CTT2.TRANSPORTELEMENT
  LEFT OUTER JOIN CTT2.ADDRESS \"ADDRESS_TO\"
    ON ADDRESS_TO.CODE = COALESCE(TRANSPORTELEMENT.ADDRESS_STOP1, TRANSPORTELEMENT.ADDRESS_TO,
                                  TRANSPORTELEMENT.ADDRESS_STOPSHOW)
  LEFT OUTER JOIN CTT2.ADDRESS  \"ADDRESS_FROM\"
    ON ADDRESS_FROM.CODE = TRANSPORTELEMENT.ADDRESS_FROM

WHERE TRANSPORTELEMENT.IE = 'T'
      AND
      COALESCE(TRANSPORTELEMENT.DATEPLANNED1, TRANSPORTELEMENT.AFKOPPELDATE, TRANSPORTELEMENT.SHOWDATEPLANNED) >= Trunc(
          sysdate, 'MONTH')

ORDER BY DATETIME_TO ASC
        ");
    }
}
