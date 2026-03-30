<?php

namespace Modules\ServiceAgreementSystem\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SasRoleMiddleware
{
    /**
     * Restrict access to users who have one of the allowed SAS module roles.
     *
     * Usage in routes: middleware('sas.role:Admin,Approver')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $sasRole = $user->moduleRole('sas');

        if (!$sasRole) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses ke modul Service Agreement System.');
            }

            return redirect()->route('modules.index')
                ->with('error', 'Anda tidak memiliki akses ke modul Service Agreement System. Hubungi administrator untuk mendapatkan role yang sesuai.');
        }

        if (!empty($allowedRoles) && !in_array($sasRole, $allowedRoles, true)) {
            abort(403, 'Role Anda (' . $sasRole . ') tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
