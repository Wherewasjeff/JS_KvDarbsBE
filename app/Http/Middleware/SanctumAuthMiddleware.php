<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanctumAuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (!auth()->guard('sanctum')->check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
