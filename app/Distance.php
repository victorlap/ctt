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
        'from', 'to', 'distance_value', 'distance_text','duration_value', 'duration_text'
    ];

    protected $hidden = [
    	'id'
    ];
}
