<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAssignedRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $hasGlobalRole = method_exists($user, 'roles') && $user->roles()->exists();
        $hasModuleRole = method_exists($user, 'moduleRoles') && $user->moduleRoles()->exists();

        if (!$hasGlobalRole && !$hasModuleRole) {
            if ($request->expectsJson()) {
                abort(403, 'Akun Anda belum memiliki role. Hubungi administrator.');
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Akun Anda belum memiliki role. Hubungi administrator untuk mendapatkan akses.',
                ]);
        }

        return $next($request);
    }
}
