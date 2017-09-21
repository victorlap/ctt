<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistanceRequest;
use App\Distance;
use GuzzleHttp\Client;

class DistanceController extends Controller
{

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(DistanceRequest $request)
    {
        $distance = Distance::firstOrNew([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);

        if(! $distance->exists) {
            $distance = $this->findGoogle($distance);
        }

        return response()->json($distance);
    }

    public function findGoogle(Distance $distance)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?&origins=". $distance->from ."&destinations=". $distance->to ."&key=". config('services.google.key');

        $res = $this->client->get($url);
        $body = \GuzzleHttp\json_decode($res->getBody());
        if(is_null($body)) {
            throw new \Exception("Could not read from Google");
        }

        $distance->distance_value = $body->rows[0]->elements[0]->distance->value;
        $distance->distance_text = $body->rows[0]->elements[0]->distance->text;
        $distance->duration_value = $body->rows[0]->elements[0]->duration->value;
        $distance->duration_text = $body->rows[0]->elements[0]->duration->text;

        $distance->save();

        $distance->fetched_from_google = true;

        return $distance;
    }
}
