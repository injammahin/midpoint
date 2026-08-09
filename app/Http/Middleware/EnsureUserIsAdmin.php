<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {

            return redirect()
                ->route('login');
        }


        if (
            Auth::user()->role !== 'admin'
            ||
            !Auth::user()->status
        ) {

            abort(403, 'You do not have permission to access the administration panel.');
        }


        return $next($request);
    }
}