<?php

namespace App\Http\Controllers;

use App\Helpers\DistanceHelper;
use App\Http\Requests\DistanceRequest;
use App\Http\Resources\DistanceResource;

class DistanceController extends Controller
{

    public function __invoke(DistanceRequest $request)
    {
        $distance = app(DistanceHelper::class)->find(
            $request->input('from'),
            $request->input('to')
        );

        return response()->json(new DistanceResource($distance));
    }

}
