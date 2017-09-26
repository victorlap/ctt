<?php
/**
 * Created by PhpStorm.
 * User: victo
 * Date: 26/09/2017
 * Time: 13:37
 */

namespace App\Helpers;


use App\Distance;
use GuzzleHttp\Client;

class DistanceHelper
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function find($from, $to) {
        $distance = Distance::firstOrNew([
            'address_from' => $from,
            'address_to' => $to,
        ]);

        if(! $distance->exists) {
            $distance = $this->fromGoogle($from, $to);
        }

        return $distance;
    }

    public function fromGoogle($from, $to)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?&origins=". $from ."&destinations=". $to ."&key=". config('services.google.key');

        $res = $this->client->get($url);
        $body = \GuzzleHttp\json_decode($res->getBody());

        if(is_null($body) || $body->rows[0]->elements[0]->status == 'NOT_FOUND') {
            return null;
        }

        $distance = Distance::create([
            'address_from' => $from,
            'address_to' => $to,
            'distance_value' => $body->rows[0]->elements[0]->distance->value,
            'distance_text'  => $body->rows[0]->elements[0]->distance->text,
            'duration_value' => $body->rows[0]->elements[0]->duration->value,
            'duration_text'  => $body->rows[0]->elements[0]->duration->text,
        ]);

        return $distance;
    }
}