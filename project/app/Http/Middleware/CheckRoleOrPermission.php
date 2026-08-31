<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    public function handle(Request $request, Closure $next, string ...$rolesOrPermissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        // Super Admin has unrestricted access
        if ($user->isSuperAdmin() || $user->role === 'super_admin' || $user->role === 'admin' || ($user->roleModel && $user->roleModel->name === 'super_admin')) {
            return $next($request);
        }

        // Deny if no roles/permissions were specified — fail closed rather than open.
        if (empty($rolesOrPermissions)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized. You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access this module.');
        }

        foreach ($rolesOrPermissions as $item) {
            if ($user->hasRole($item) || $user->hasPermission($item)) {
                return $next($request);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to perform this action.'], 403);
        }

        return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access this module.');
    }
}
