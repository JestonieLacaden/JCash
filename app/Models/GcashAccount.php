<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GcashAccount extends Model
{
    protected $fillable = [
        'name',
        'balance',
        'is_active',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'gcash_account_id');
    }
}
