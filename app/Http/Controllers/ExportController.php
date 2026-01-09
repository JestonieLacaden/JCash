<?php

namespace App\Http\Controllers;

use App\Models\CashWallet;
use App\Models\Transaction;
use App\Models\DailySession;
use App\Models\GcashAccount;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function transactions(Request $request)
    {
        $query = Transaction::with('gcashAccount');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->account) {
            if ($request->account === 'cash') {
                $query->whereNull('gcash_account_id');
            } else {
                $query->where('gcash_account_id', $request->account);
            }
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->search) {
            $query->where('remarks', 'like', "%{$request->search}%");
        }

        return new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'Date',
                'Type',
                'Account',
                'Amount',
                'Fee',
                'Remarks',
            ]);

            $query->orderBy('created_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $t) {
                    fputcsv($handle, [
                        $t->created_at,
                        $t->type,
                        $t->gcashAccount?->name ?? 'Cash Wallet',
                        $t->amount,
                        $t->fee,
                        $t->remarks,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
        ]);
    }

    public function daily(Request $request)
    {
        $date = now()->toDateString();

        $transactions = Transaction::whereDate('created_at', $date)->get();

        return response()->streamDownload(function () use ($transactions, $date) {
            $out = fopen('php://output', 'w');

            // Title
            fputcsv($out, ['Daily Report']);
            fputcsv($out, ['Date', $date]);
            fputcsv($out, []);

            // Table Header
            fputcsv($out, ['Date', 'Type', 'Account', 'Amount', 'Fee', 'Remarks']);

            foreach ($transactions as $t) {
                fputcsv($out, [
                    $t->created_at,
                    $t->type,
                    $t->gcash_account_id
                        ? optional($t->gcashAccount)->name
                        : 'Cash Wallet',
                    $t->amount,
                    $t->fee ?? 0,
                    $t->remarks,
                ]);
            }

            fputcsv($out, []);

            // Summary
            fputcsv($out, ['Summary']);
            fputcsv($out, ['Cash In Total', $transactions->where('type', 'cash_in')->sum('amount')]);
            fputcsv($out, ['Cash Out Total', $transactions->where('type', 'cash_out')->sum('amount')]);
            fputcsv($out, ['Total Fees', $transactions->sum('fee')]);

            fclose($out);
        }, "daily-report-{$date}.csv");
    }
}