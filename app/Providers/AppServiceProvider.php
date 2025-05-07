<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use App\Models\Worker;
use Illuminate\Auth\RequestGuard;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::resolved(function ($auth) {
            $auth->shouldUse(request()->expectsJson() && request()->bearerToken()
                ? 'sanctum'
                : config('auth.defaults.guard', 'web'));
        });
    }

    protected function createGuard($name, array $config)
    {
        return new RequestGuard(function ($request) use ($config) {
            return Sanctum::guard($config['provider'] ?? 'workers')($request);
        }, app('request'));
    }
}

