<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CashWallet;
use App\Models\FeeSetting;
use App\Models\GcashAccount;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        CashWallet::create(['balance' => 0]);

        FeeSetting::create([]);

        GcashAccount::create([
            // 'name' => 'GCash – Personal',
            // 'balance' => 0,
        ]);
    }
}