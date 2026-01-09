<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySession extends Model
{
    protected $fillable = [
        'date',
        'starting_cash',
        'starting_gcash',
        'notes',
    ];
}