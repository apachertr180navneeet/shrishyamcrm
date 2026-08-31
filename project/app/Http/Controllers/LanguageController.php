<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (in_array($locale, ['en', 'hi'])) {
            Session::put('locale', $locale);
            session(['locale' => $locale]);
            session()->save();
            App::setLocale($locale);
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            // Only allow redirecting back to the same host (prevents open-redirect)
            $refererHost = parse_url($referer, PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            if ($refererHost && $appHost && $refererHost === $appHost) {
                return redirect()->to($referer);
            }
        }

        return redirect()->back();
    }
}
