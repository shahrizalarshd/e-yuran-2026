<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', __('Akaun anda tidak aktif.'));
        }

        if (!$request->user()->isAdmin()) {
            abort(403, __('Anda tidak dibenarkan mengakses halaman ini.'));
        }

        return $next($request);
    }
}

