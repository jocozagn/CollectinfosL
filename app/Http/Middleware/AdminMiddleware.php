<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Accès réservé aux administrateurs.');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Veuillez vous connecter en tant qu\'administrateur.');
        }

        return $next($request);
    }
}
