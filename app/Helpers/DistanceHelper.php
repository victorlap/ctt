<?php
/**
 * Created by PhpStorm.
 * User: victo
 * Date: 26/09/2017
 * Time: 13:37
 */

namespace App\Helpers;


use App\Distance;
use Carbon\Carbon;
use GuzzleHttp\Client;

class DistanceHelper
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function find($from, $to, $from_code, $to_code)
    {
        $distance = Distance::firstOrNew([
            'address_from' => $from,
            'address_to' => $to,
            'address_from_code' => $from_code,
            'address_to_code' => $to_code,
        ]);

        if (!$distance->exists) {
           $distance = $this->fromGoogle($distance);

           if($distance->distance_value === null) {
               return null;
           }

           $distance->save();
        }

        if ($distance->tries < 5 && $distance->updated_at->diffInHours(Carbon::now()) > 16) {
            $google = $this->fromGoogle($distance);
            if ($google->duration_value !== $distance->duration_value) {
                $distance->duration_value = ($google['duration_value'] + $distance->duration_value) / 2;
            }
            $distance->tries += 1;
            $distance->save();
        }

        return $distance;
    }

    public function fromGoogle($distance)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?&origins=" . $distance->address_from . "&destinations=" . $distance->address_to . "&key=" . config('services.google.key');

        $res = $this->client->get($url);
        $body = \GuzzleHttp\json_decode($res->getBody());

        if (is_null($body) || $body->rows[0]->elements[0]->status == 'NOT_FOUND') {
            return $distance;
        }

        return $distance->fill([
            'distance_value' => $body->rows[0]->elements[0]->distance->value,
            'distance_text' => $body->rows[0]->elements[0]->distance->text,
            'duration_value' => $body->rows[0]->elements[0]->duration->value,
            'duration_text' => $body->rows[0]->elements[0]->duration->text,
        ]);
    }
}