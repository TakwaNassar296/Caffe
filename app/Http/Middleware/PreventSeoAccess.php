<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventSeoAccess
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
     

        return $next($request);
    }
}
