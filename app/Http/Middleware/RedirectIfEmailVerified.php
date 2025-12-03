<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfEmailVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return redirect('/');
        }

        return $next($request);
    }
}
