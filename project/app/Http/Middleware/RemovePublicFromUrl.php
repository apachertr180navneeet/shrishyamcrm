<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemovePublicFromUrl
{
    /**
     * Handle an incoming request and redirect any URLs containing '/public' to clean URLs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->getRequestUri();

        if (preg_match('#/public(/|(?:\?.*)?$)#', $uri)) {
            $cleanUrl = preg_replace('#/public(/|(?:\?.*)?$)#', '$1', $request->fullUrl());
            return redirect()->away($cleanUrl, 301);
        }

        return $next($request);
    }
}
