<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();
        $effectiveRole = $user->role;

        if (empty($effectiveRole)) {
            // Map new UserTypeID to legacy role names
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

        // Treat 'clinic' as equivalent to 'tenant' where used in routes
        $roleAliases = [$effectiveRole];
        if ($effectiveRole === 'tenant') {
            $roleAliases[] = 'clinic';
        }

        $allowed = false;
        foreach ($roles as $required) {
            if (in_array($required, $roleAliases, true)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
