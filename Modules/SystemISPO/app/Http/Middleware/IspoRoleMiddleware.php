<?php

namespace Modules\SystemISPO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IspoRoleMiddleware
{
    /**
     * Restrict access to users who have one of the allowed ISPO module roles.
     *
     * Usage in routes: middleware('ispo.role:ISPO Admin,ISPO Auditor')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $ispoRole = $user->moduleRole('ispo');

        if (!$ispoRole) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses ke modul System ISPO.');
            }

            return redirect()->route('modules.index')
                ->with('error', 'Anda tidak memiliki akses ke modul System ISPO. Hubungi administrator untuk mendapatkan role yang sesuai.');
        }

        if (!empty($allowedRoles) && !in_array($ispoRole, $allowedRoles, true)) {
            abort(403, 'Role Anda (' . $ispoRole . ') tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
