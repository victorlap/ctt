<?php

namespace App\Console\Commands;

use App\Helpers\DistanceHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $this->info("Trying to find distances for ". count($elements) ." routes.");

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
        }

        $this->info("Finished.");
    }

    private function findDistanceAndSave($from, $to, $from_code, $to_code)
    {
        $distance = app(DistanceHelper::class)->find($from, $to, $from_code, $to_code);

        if($distance === null) {
            $this->warn("Could not find distance for {$from_code} => {$to_code}");
        }
    }

    private function getElements()
    {
        return DB::connection('oracle')->select("
SELECT DISTINCT
       ADDRESS_TO.ZIPCODE || ' ' || ADDRESS_TO.CITY || ' ' || ADDRESS_TO.COUNTRY1 \"ADDRESS_TO\",
       ADDRESS_FROM.ZIPCODE || ' ' || ADDRESS_FROM.CITY || ' ' || ADDRESS_FROM.COUNTRY1 \"ADDRESS_FROM\",
       ADDRESS_FROM_CODE,
       ADDRESS_TO_CODE
FROM
  (SELECT ADDRESS_FROM \"ADDRESS_FROM_CODE\",
          COALESCE(ADDRESS_STOP1,ADDRESS_TO, ADDRESS_STOPSHOW) \"ADDRESS_TO_CODE\"
   FROM CTT2.TRANSPORTELEMENT
   WHERE ADDRESS_FROM = 'CTT'
     AND IE = 'T'
     AND COALESCE(TRANSPORTELEMENT.DATEPLANNED1, TRANSPORTELEMENT.AFKOPPELDATE, TRANSPORTELEMENT.SHOWDATEPLANNED) >= TRUNC(SYSDATE, 'MONTH')
  
   UNION SELECT ADDRESS_STOP1 \"ADDRESS_FROM_CODE\",
                COALESCE(ADDRESS_STOP2, ADDRESS_TO, ADDRESS_STOPSHOW) \"ADDRESS_TO_CODE\"
   FROM CTT2.TRANSPORTELEMENT
   WHERE ADDRESS_STOP1 IS NOT NULL
     AND ADDRESS_FROM = 'CTT'
     AND IE = 'T'
     AND COALESCE(TRANSPORTELEMENT.DATEPLANNED1, TRANSPORTELEMENT.AFKOPPELDATE, TRANSPORTELEMENT.SHOWDATEPLANNED) >= TRUNC(SYSDATE, 'MONTH')
  
   UNION SELECT ADDRESS_STOP2 \"ADDRESS_FROM_CODE\",
                COALESCE(ADDRESS_STOP3, ADDRESS_TO, ADDRESS_STOPSHOW) \"ADDRESS_TO_CODE\"
   FROM CTT2.TRANSPORTELEMENT
   WHERE ADDRESS_STOP2 IS NOT NULL
     AND ADDRESS_FROM = 'CTT'
     AND IE = 'T'
     AND COALESCE(TRANSPORTELEMENT.DATEPLANNED1, TRANSPORTELEMENT.AFKOPPELDATE, TRANSPORTELEMENT.SHOWDATEPLANNED) >= TRUNC(SYSDATE, 'MONTH')
   
   UNION SELECT ADDRESS_STOP3 \"ADDRESS_FROM_CODE\",
                COALESCE(ADDRESS_TO, ADDRESS_STOPSHOW) \"ADDRESS_TO_CODE\"
   FROM CTT2.TRANSPORTELEMENT
   WHERE ADDRESS_STOP3 IS NOT NULL
     AND ADDRESS_FROM = 'CTT'
     AND IE = 'T'
     AND COALESCE(TRANSPORTELEMENT.DATEPLANNED1, TRANSPORTELEMENT.AFKOPPELDATE, TRANSPORTELEMENT.SHOWDATEPLANNED) >= TRUNC(SYSDATE, 'MONTH') )
     
LEFT OUTER JOIN CTT2.ADDRESS \"ADDRESS_TO\" 
  ON ADDRESS_TO.CODE = ADDRESS_TO_CODE
  
LEFT OUTER JOIN CTT2.ADDRESS \"ADDRESS_FROM\" 
  ON ADDRESS_FROM.CODE = ADDRESS_FROM_CODE
        ");
    }
}
