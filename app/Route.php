<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seq', 'start', 'end'
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
