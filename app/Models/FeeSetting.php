<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeSetting extends Model
{
    protected $fillable = [
        'below_500_fee',
        'five_hundred_to_999_fee',
        'per_1000_fee',
        'discounted_per_1000_fee',
    ];
}