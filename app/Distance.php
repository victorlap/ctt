<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    protected $table = 'webapp_distances';

    protected $connection = 'oracle';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address_from', 'address_to', 'distance_value', 'distance_text','duration_value', 'duration_text'
    ];
}
