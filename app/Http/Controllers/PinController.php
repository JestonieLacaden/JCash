<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PinSetting;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $setting = PinSetting::first();

        if (!$setting) {
            return response()->json([
                'message' => 'PIN not set'
            ], 500);
        }

        if (!Hash::check($request->pin, $setting->pin_hash)) {
            return response()->json([
                'message' => 'Invalid PIN'
            ], 403);
        }

        // ✅ Mark PIN as verified for this session
        session(['pin_verified' => true]);

        return response()->json([
            'success' => true
        ]);
    }
}