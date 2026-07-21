<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || (int) $user->RoleID !== 3) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}