<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale', session('locale', config('app.locale', 'en')));

        if (in_array($locale, ['en', 'hi'])) {
            App::setLocale($locale);
        } else {
            App::setLocale('en');
        }

        return $next($request);
    }
}
