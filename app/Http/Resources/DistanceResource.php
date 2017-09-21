<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class DistanceResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'distance_value' => $this->distance_value,
            'distance_text' => $this->distance_text,
            'duration_value' => $this->duration_value,
            'duration_text' => $this->duration_text,
            'status' => $this->status ?? 'OK',
        ];
    }
}
