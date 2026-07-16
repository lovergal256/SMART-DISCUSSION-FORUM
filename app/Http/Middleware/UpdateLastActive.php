<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            Auth::user()->update(['LastActiveDate' => now()]);
        }

        return $next($request);
    }
}