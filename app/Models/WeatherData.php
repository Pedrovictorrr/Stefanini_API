<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherData extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'data',
        'temperature',
        'conditions'
    ];

    protected $casts = [
        'data' => 'json',
    ];
}
