<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('locales.default', 'fr'));

        if (! in_array($locale, config('locales.supported', ['fr']), true)) {
            $locale = config('locales.default', 'fr');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
