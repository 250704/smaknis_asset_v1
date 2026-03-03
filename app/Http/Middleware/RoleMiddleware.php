<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login');
        }

        if (!$user->hasRole($role)) {
            abort(403, 'AKSES DITOLAK');
        }

        return $next($request);
    }
}
