<?php

namespace App;

class Distance
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from', 'to', 'distance_value', 'distance_text','duration_value', 'duration_text'
    ];
}
