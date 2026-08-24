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
            return redirect()->to($referer);
        }

        return redirect()->back();
    }
}
