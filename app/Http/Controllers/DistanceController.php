<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistanceRequest;
use App\Distance;
use App\Http\Resources\DistanceResource;
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

        return response()->json(new DistanceResource($distance));
    }

    public function findGoogle(Distance $distance)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?&origins=". $distance->from ."&destinations=". $distance->to ."&key=". config('services.google.key');

        $res = $this->client->get($url);
        $body = \GuzzleHttp\json_decode($res->getBody());

        if(is_null($body) || $body->rows[0]->elements[0]->status == 'NOT_FOUND') {
            $distance->status = "ERROR";
            return $distance;
        }

        $distance->distance_value = $body->rows[0]->elements[0]->distance->value;
        $distance->distance_text = $body->rows[0]->elements[0]->distance->text;
        $distance->duration_value = $body->rows[0]->elements[0]->duration->value;
        $distance->duration_text = $body->rows[0]->elements[0]->duration->text;

        $distance->save();

        return $distance;
    }
}
