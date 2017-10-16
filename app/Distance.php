<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address_from', 'address_to', 'address_from_code', 'address_to_code', 'distance_value', 'distance_text','duration_value', 'duration_text'
    ];
}
