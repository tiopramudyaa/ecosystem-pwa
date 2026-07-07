<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureLiteApiAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('lite_api_token')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
