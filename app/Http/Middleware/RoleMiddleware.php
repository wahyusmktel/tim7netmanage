<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (!$request->user()->hasAnyRole($roles)) {
            abort(403, 'Akses Ditolak.');
        }

        return $next($request);
    }
}
