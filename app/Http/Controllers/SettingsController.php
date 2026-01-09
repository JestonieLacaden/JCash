<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeSetting;
use Inertia\Inertia;
use App\Models\GcashAccount;
use App\Models\User;


class SettingsController extends Controller
{
    public function gcash()
    {
        return Inertia::render('settings/GcashAccounts', [
            'accounts' => GcashAccount::orderBy('created_at')->get(),
        ]);
    }

    public function storeGcash(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        GcashAccount::create([
            'name' => $data['name'],
            'balance' => 0,
            'is_active' => true,
        ]);

        return back();
    }

    public function updateGcash(Request $request, GcashAccount $gcash)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $gcash->update($data);

        return back();
    }

    public function deleteGcash(GcashAccount $gcash)
    {
        // Check if account has transactions
        if ($gcash->transactions()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Cannot delete account with existing transactions. Set to inactive instead.'
            ]);
        }

        $gcash->delete();

        return back()->with('success', 'GCash account deleted successfully');
    }


    public function fees()
    {
        $gcashAccounts = GcashAccount::orderBy('created_at')
            ->withCount('transactions')
            ->get();

        return Inertia::render('settings/FeeSettings', [
            'fee' => FeeSetting::first(),
            'users' => User::orderBy('name')->get(),
            'gcashAccounts' => $gcashAccounts,
        ]);
    }

    public function updateFees(Request $request)
    {
        $data = $request->validate([
            'below_500_fee' => 'required|numeric|min:0',
            'five_to_999_fee' => 'required|numeric|min:0',
            'per_1000_fee' => 'required|numeric|min:0',
            'discount_per_1000_fee' => 'required|numeric|min:0',
        ]);

        FeeSetting::first()->update($data);

        return back()->with('success', 'Fee settings updated');
    }
}
