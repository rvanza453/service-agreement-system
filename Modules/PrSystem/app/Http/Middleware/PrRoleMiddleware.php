<?php

namespace Modules\PrSystem\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrRoleMiddleware
{
    /**
     * Restrict access to users who have one of the allowed PR module roles.
     *
     * Usage in routes: middleware('pr.role:Admin,Approver')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $prRole = $user->moduleRole('pr');

        // Only check module-specific role to strictly enforce 'Role Per Module'


        if (!$prRole) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses ke modul PR.');
            }

            return redirect()->route('modules.index')
                ->with('error', 'Anda tidak memiliki akses ke modul PR. Hubungi administrator untuk mendapatkan role yang sesuai.');
        }

        if (!empty($allowedRoles) && !in_array($prRole, $allowedRoles, true)) {
            abort(403, 'Role Anda (' . $prRole . ') tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
