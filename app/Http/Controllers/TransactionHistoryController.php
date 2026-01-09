<?php

namespace App\Http\Controllers;

use App\Models\GcashAccount;
use App\Models\Transaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('gcashAccount')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Transactions/History', [
            'transactions' => $transactions,
        ]);
    }
    public function history(Request $request)
    {
        $query = Transaction::query()
            ->with('gcashAccount')
            ->orderByDesc('created_at');

        // TYPE FILTER - Fixed: check if not empty string
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // ACCOUNT FILTER
        if ($request->filled('account')) {
            if ($request->account === 'cash') {
                $query->whereNull('gcash_account_id');
            } else {
                $query->where('gcash_account_id', $request->account);
            }
        }

        // DATE RANGE
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // SEARCH REMARKS
        if ($request->filled('search')) {
            $query->where('remarks', 'LIKE', '%' . $request->search . '%');
        }

        // DEBUG - kuha SQL bago mag-paginate
        $debugSql = $query->toSql();
        $debugBindings = $query->getBindings();

        // Execute query with pagination
        $transactions = $query->paginate(10)->withQueryString();

        return Inertia::render('Transactions/History', [
            'transactions' => $transactions,
            'filters' => $request->only([
                'type',
                'account',
                'from',
                'to',
                'search'
            ]),
            'gcashAccounts' => GcashAccount::select('id', 'name')->get(),
            // DEBUG INFO - i-remove mo to pag tapos na
            'debug' => [
                'sql' => $debugSql,
                'bindings' => $debugBindings,
                'request' => $request->all(),
                'total' => $transactions->total()
            ]
        ]);
    }
}
