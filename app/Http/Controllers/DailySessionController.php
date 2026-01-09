<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailySession;
use App\Models\CashWallet;
use App\Models\GcashAccount;

class DailySessionController extends Controller
{
    public function continue()
    {
        DailySession::firstOrCreate(
            ['date' => today()],
            [
                'starting_cash' => CashWallet::first()->balance,
                'starting_gcash' => GcashAccount::sum('balance'),
                'notes' => 'Continued previous day',
            ]
        );

        return redirect()->route('dashboard');
    }

    public function start()
    {
        // Use updateOrCreate to avoid duplicate entry errors
        DailySession::updateOrCreate(
            ['date' => today()],
            [
                'starting_cash' => 0, // Reset to 0 for new day
                'starting_gcash' => 0, // Reset to 0 for new day
                'notes' => 'Started fresh new day',
            ]
        );

        return redirect()->route('dashboard');
    }


    public function reset(Request $request)
    {
        $data = $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DailySession::updateOrCreate(
            ['date' => today()],
            [
                'starting_cash' => $data['starting_cash'],
                'starting_gcash' => GcashAccount::sum('balance'),
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with('success', 'New day started');
    }
}
