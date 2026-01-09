<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\CashWallet;
use App\Models\GcashAccount;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        // Ensure cash wallet exists
        $cashWallet = CashWallet::firstOrCreate([], [
            'balance' => 0,
        ]);

        // Current balances
        $totalGcash = GcashAccount::where('is_active', true)->sum('balance');
        $cashOnHand = $cashWallet->balance;
        $totalCapital = $totalGcash + $cashOnHand;

        // Tubo today
        $tuboToday = Transaction::whereDate('claimed_at', today())
            ->whereIn('type', ['cash_in', 'cash_out'])
            ->sum('fee');

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalGcash' => $totalGcash,
                'cashOnHand' => $cashOnHand,
                'totalCapital' => $totalCapital,
                'tuboToday' => $tuboToday,
            ],
            'gcashAccounts' => GcashAccount::orderBy('name')->get(),
        ]);
    }
}
