<?php

namespace App\Providers;

use App\Models\FeeSetting;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        FeeSetting::firstOrCreate([]);
        Inertia::share([
            'auth' => fn() => [
                'user' => auth()->user()
                    ? [
                        'id' => auth()->user()->id,
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                        'role' => auth()->user()->role,
                    ]
                    : null,
            ],
        ]);
    }
}