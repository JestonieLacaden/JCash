<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'type',
        'gcash_account_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'fee',
        'discounted',
        'status',
        'reference',
        'remarks',
        'claimed_at',
        'receiver_name',
    ];

    protected $casts = [
        'discounted' => 'boolean',
        'claimed_at' => 'datetime',
    ];

    public function gcashAccount()
    {
        return $this->belongsTo(GcashAccount::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(GcashAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(GcashAccount::class, 'to_account_id');
    }
}