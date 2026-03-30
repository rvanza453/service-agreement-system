<?php

namespace Modules\QcComplaintSystem\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QcRoleMiddleware
{
    /**
     * Restrict access to users who have one of the allowed QC module roles.
     *
     * Usage in routes: middleware('qc.role:QC Admin,QC Officer')
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $qcRole = $user->moduleRole('qc');

        if (!$qcRole) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses ke modul QC.');
            }

            return redirect()->route('modules.index')
                ->with('error', 'Anda tidak memiliki akses ke modul QC. Hubungi administrator untuk mendapatkan role yang sesuai.');
        }

        if (!empty($allowedRoles) && !in_array($qcRole, $allowedRoles, true)) {
            abort(403, 'Role Anda (' . $qcRole . ') tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
