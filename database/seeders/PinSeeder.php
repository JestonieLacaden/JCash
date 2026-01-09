<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PinSetting;
use Illuminate\Support\Facades\Hash;

class PinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PinSetting::firstOrCreate([], [
            'pin_hash' => Hash::make('1234'),
        ]);
    }
}