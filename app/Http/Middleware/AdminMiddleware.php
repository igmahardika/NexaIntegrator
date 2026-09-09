<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $allowedRoles = ['superadmin', 'site_admin', 'operator', 'cashier', 'advertiser'];
        if (!in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Akses dashboard tidak diizinkan untuk peran ini.');
        }

        return $next($request);
    }
}
