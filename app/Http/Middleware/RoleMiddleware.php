<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (empty($roles) || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Akses ditolak untuk peran Anda.'], 403);
        }

        abort(403, 'Akses tidak diizinkan untuk peran ' . ($user->role ?? 'tamu') . '.');
    }
}
