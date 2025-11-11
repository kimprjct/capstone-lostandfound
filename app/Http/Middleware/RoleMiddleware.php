<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
public function handle($request, Closure $next, $role)
{
    $user = auth()->user();
    if (!$user) {
        abort(403, 'Unauthorized');
    }

    $effectiveRole = $user->role;
    if (empty($effectiveRole)) {
        if (isset($user->UserTypeID)) {
            if ($user->UserTypeID === 1) {
                $effectiveRole = 'admin';
            } elseif ($user->UserTypeID === 2) {
                $effectiveRole = 'tenant';
            } elseif ($user->UserTypeID === 3) {
                $effectiveRole = 'user';
            }
        }
    }

    // 'clinic' routes should accept tenant users
    if ($role === 'clinic' && $effectiveRole === 'tenant') {
        return $next($request);
    }

    if ($effectiveRole === $role) {
        return $next($request);
    }

    abort(403, 'Unauthorized');
}
}
