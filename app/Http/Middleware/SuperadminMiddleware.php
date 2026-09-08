<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperadminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Superadmin access required'], 403);
            }
            abort(403, 'Superadmin access required');
        }

        return $next($request);
    }
}
