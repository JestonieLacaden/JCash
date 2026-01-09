<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\DailySession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->date ?? today()->toDateString();

        $transactions = Transaction::whereDate('created_at', $date);

        $summary = [
            'cash_in' => (clone $transactions)->where('type', 'cash_in')->sum('amount'),
            'cash_out' => (clone $transactions)->where('type', 'cash_out')->sum('amount'),
            'adjustment_add' => (clone $transactions)
                ->where('type', 'adjustment')
                ->where('amount', '>', 0)
                ->sum('amount'),
            'adjustment_deduct' => (clone $transactions)
                ->where('type', 'adjustment')
                ->where('amount', '<', 0)
                ->sum(DB::raw('ABS(amount)')),
            'fees' => (clone $transactions)->sum('fee'),
        ];

        $session = DailySession::where('date', $date)->first();

        return Inertia::render('Reports/Daily', [
            'date' => $date,
            'summary' => $summary,
            'session' => $session,
        ]);
    }
}