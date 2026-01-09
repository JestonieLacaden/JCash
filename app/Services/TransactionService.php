<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\CashWallet;
use App\Models\GcashAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\FeeSetting;

class TransactionService
{
    private function computeFee(float $amount, bool $discounted = false): float
    {
        $fee = 0;
        $settings = FeeSetting::first();

        if ($amount < 500) {
            $fee = $settings->below_500_fee;
        } elseif ($amount < 1000) {
            $fee = $settings->five_to_999_fee;
        } else {
            $perThousand = floor($amount / 1000);
            $rate = $discounted
                ? $settings->discounted_per_1000_fee
                : $settings->per_1000_fee;

            $fee = $perThousand * $rate;
        }

        return $fee;
    }

    public function cashIn(array $data)
    {
        return DB::transaction(function () use ($data) {

            $gcash = GcashAccount::findOrFail($data['gcash_account_id']);
            $wallet = CashWallet::firstOrFail();

            // Store previous balance
            $previousBalance = $gcash->balance;

            // CASH IN = you SEND money
            $gcash->decrement('balance', $data['amount']);
            $wallet->increment('balance', $data['amount']);

            $fee = $this->computeFee(
                $data['amount'],
                $data['discounted'] ?? false
            );

            return Transaction::create([
                'type' => 'cash_in',
                'gcash_account_id' => $gcash->id,
                'amount' => $data['amount'],
                'previous_balance' => $previousBalance,
                'fee' => $fee,
                'discounted' => $data['discounted'] ?? false,
                'remarks' => $data['remarks'] ?? null,
                'reference' => $data['reference'] ?? null,
                'receiver_name' => $data['receiver_name'] ?? null,
                'status' => 'claimed',
                'claimed_at' => now(),
            ]);
        });
    }


    public function cashOut(array $data)
    {
        return DB::transaction(function () use ($data) {

            $gcash = GcashAccount::findOrFail($data['gcash_account_id']);
            $wallet = CashWallet::firstOrFail();

            // Store previous balance
            $previousBalance = $gcash->balance;

            // CASH OUT = you RECEIVE money
            $gcash->increment('balance', $data['amount']);
            $wallet->decrement('balance', $data['amount']);

            $fee = $this->computeFee(
                $data['amount'],
                $data['discounted'] ?? false
            );

            return Transaction::create([
                'type' => 'cash_out',
                'gcash_account_id' => $gcash->id,
                'amount' => $data['amount'],
                'previous_balance' => $previousBalance,
                'fee' => $fee,
                'discounted' => $data['discounted'] ?? false,
                'remarks' => $data['remarks'] ?? null,
                'reference' => $data['reference'] ?? null,
                'receiver_name' => $data['receiver_name'] ?? null,
                'status' => 'claimed',
                'claimed_at' => now(),
            ]);
        });
    }


    public function moveCapital(array $data)
    {
        return DB::transaction(function () use ($data) {

            $from = GcashAccount::find($data['from_account_id']);
            $to   = GcashAccount::find($data['to_account_id']);
            $wallet = CashWallet::first();

            if ($from) $from->decrement('balance', $data['amount']);
            if ($to)   $to->increment('balance', $data['amount']);

            if ($data['from'] === 'cash') $wallet->decrement('balance', $data['amount']);
            if ($data['to'] === 'cash')   $wallet->increment('balance', $data['amount']);

            return Transaction::create([
                'type' => 'capital_move',
                'from_account_id' => $data['from_account_id'] ?? null,
                'to_account_id' => $data['to_account_id'] ?? null,
                'amount' => $data['amount'],
                'reference' => $data['reference'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'claimed',
                'claimed_at' => Carbon::now(),
            ]);
        });
    }

    public function adjust(array $data)
    {
        return DB::transaction(function () use ($data) {

            $wallet = CashWallet::firstOrCreate([], ['balance' => 0]);

            if ($data['target'] === 'cash') {
                if ($data['direction'] === 'add') {
                    $wallet->increment('balance', $data['amount']);
                } else {
                    $wallet->decrement('balance', $data['amount']);
                }
            }

            if ($data['target'] === 'gcash') {
                $gcash = GcashAccount::findOrFail($data['gcash_account_id']);

                if ($data['direction'] === 'add') {
                    $gcash->increment('balance', $data['amount']);
                } else {
                    $gcash->decrement('balance', $data['amount']);
                }
            }

            return Transaction::create([
                'type' => 'adjustment',
                'amount' => $data['amount'],
                'gcash_account_id' => $data['gcash_account_id'] ?? null,
                'remarks' => $data['remarks'],
                'status' => 'claimed',
                'claimed_at' => now(),
            ]);
        });
    }
}


//     public function adjust(array $data)
//     {
        
// }