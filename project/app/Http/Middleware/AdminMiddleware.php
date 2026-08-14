<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Your account has been deactivated. Please contact administration.');
        }

        return $next($request);
    }
}
