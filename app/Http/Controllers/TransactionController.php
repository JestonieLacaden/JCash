<?php

namespace App\Http\Controllers;

use App\Models\FeeSetting;
use Illuminate\Http\Request;
use App\Services\TransactionService;
use Inertia\Inertia;
use App\Models\GcashAccount;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()
            ->with(['fromAccount', 'toAccount', 'gcashAccount'])
            ->orderByDesc('claimed_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('claimed_at', $request->date);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                    ->orWhere('receiver_name', 'like', "%{$request->search}%")
                    ->orWhere('remarks', 'like', "%{$request->search}%");
            });
        }

        return Inertia::render('Transactions/Index', [
            'transactions' => $query->paginate(20),
            'filters' => $request->only(['type', 'date', 'search']),
        ]);
    }

    public function store(Request $request, TransactionService $service)
    {
        $data = $request->validate([
            'type' => 'required|in:cash_in,cash_out,capital_move',
            'gcash_account_id' => 'nullable|exists:gcash_accounts,id',
            'from' => 'nullable',
            'to' => 'nullable',
            'amount' => 'required|numeric|min:1',
            'fee' => 'nullable|numeric|min:0',
            'discounted' => 'boolean',
            'reference' => 'nullable|string',
            'receiver_name' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);


        match ($data['type']) {
            'cash_in' => $service->cashIn($data),
            'cash_out' => $service->cashOut($data),
            'capital_move' => $service->moveCapital([
                'from' => $data['from'],
                'to' => $data['to'],
                'from_account_id' => is_numeric($data['from']) ? $data['from'] : null,
                'to_account_id' => is_numeric($data['to']) ? $data['to'] : null,
                'amount' => $data['amount'],
                'reference' => $data['reference'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]),
        };

        return redirect()->route('dashboard')->with('success', 'Transaction saved successfully!');
    }

    public function create()
    {
        return Inertia::render('Transactions/Create', [
            'gcashAccounts' => GcashAccount::where('is_active', true)->get(),
            'feeSettings' => FeeSetting::first(),
        ]);
    }

    public function adjustment()
    {
        return Inertia::render('Transactions/Adjustment', [
            'gcashAccounts' => GcashAccount::where('is_active', true)->get(),
        ]);
    }

    public function storeAdjustment(Request $request, TransactionService $service)
    {
        $data = $request->validate([
            'target' => 'required|in:cash,gcash',
            'direction' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:1',
            'remarks' => 'required|string|max:255',
            'gcash_account_id' => 'nullable|required_if:target,gcash',
        ]);

        $service->adjust($data);

        return redirect()->route('dashboard')->with('success', 'Adjustment saved');
    }

    public function history(Request $request)
    {
        $query = Transaction::query()
            ->with(['gcashAccount', 'fromAccount', 'toAccount'])
            ->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by account
        if ($request->filled('account')) {
            if ($request->account === 'cash') {
                $query->whereNull('gcash_account_id');
            } else {
                $query->where('gcash_account_id', $request->account);
            }
        }

        // Date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Search remarks
        if ($request->filled('search')) {
            $query->where('remarks', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Transactions/History', [
            'transactions' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['type', 'account', 'from', 'to', 'search']),
            'gcashAccounts' => GcashAccount::select('id', 'name')->get(),
        ]);
    }
}
