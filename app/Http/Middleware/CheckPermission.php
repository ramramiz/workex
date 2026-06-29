<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super admin bypasses all permission checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $permissions = explode('|', $permission);
        $hasAny = false;
        foreach ($permissions as $perm) {
            if ($user->hasPermission(trim($perm))) {
                $hasAny = true;
                break;
            }
        }

        if (!$hasAny) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Access Denied. You do not have permission: ' . $permission);
        }

        return $next($request);
    }
}
