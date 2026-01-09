<?php

use App\Http\Controllers\DailySessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\UserManagementController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// 📖 READ-ONLY ROUTES (All authenticated users can view)
Route::middleware(['auth'])->group(function () {
    Route::get('/transactions/history', [TransactionHistoryController::class, 'history'])
        ->name('transactions.history');
    Route::get('/reports/daily', [ReportController::class, 'daily'])
        ->name('reports.daily');
});

// 📝 STAFF ROUTES (Encoder: can create cash_in and cash_out only)
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/transactions/create', [TransactionController::class, 'create'])
        ->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');
});

// 🔒 ADMIN-ONLY ROUTES (Full control)
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Adjustments & Capital Moves
    Route::get('/transactions/adjustment', [TransactionController::class, 'adjustment'])
        ->name('transactions.adjustment');
    Route::post('/transactions/adjustment', [TransactionController::class, 'storeAdjustment'])
        ->name('transactions.adjustment.store');

    // Daily Session operations
    Route::post('/daily-session/continue', [DailySessionController::class, 'continue'])
        ->name('daily-session.continue');
    Route::post('/daily-session/start', [DailySessionController::class, 'start'])
        ->name('daily-session.start');

    // Settings (View & Edit)
    Route::get('/settings/fees', [SettingsController::class, 'fees'])->name('settings.fees');
    Route::put('/settings/fees', [SettingsController::class, 'updateFees'])
        ->name('settings.fees.update');
    Route::get('/settings/gcash', [SettingsController::class, 'gcash'])
        ->name('settings.gcash');
    Route::post('/settings/gcash', [SettingsController::class, 'storeGcash'])
        ->name('settings.gcash.store');
    Route::put('/settings/gcash/{gcash}', [SettingsController::class, 'updateGcash'])
        ->name('settings.gcash.update');
    Route::delete('/settings/gcash/{gcash}', [SettingsController::class, 'deleteGcash'])
        ->name('settings.gcash.delete');

    // Exports (Admin only)
    Route::get('/exports/transactions', [ExportController::class, 'transactions'])
        ->name('exports.transactions');
    Route::get('/exports/daily', [ExportController::class, 'daily'])
        ->name('exports.daily');

    // Pin verification (Admin only for sensitive operations)
    Route::post('/pin/verify', [PinController::class, 'verify'])
        ->name('pin.verify');

    // 👥 USER MANAGEMENT (Admin only)
    Route::resource('users', UserManagementController::class);
});


require __DIR__ . '/auth.php';