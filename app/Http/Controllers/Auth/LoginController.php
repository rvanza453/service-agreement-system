<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $user = Auth::user();
        $hasGlobalRole = $user && method_exists($user, 'roles') && $user->roles()->exists();
        $hasModuleRole = $user && method_exists($user, 'moduleRoles') && $user->moduleRoles()->exists();

        if (!$hasGlobalRole && !$hasModuleRole) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda belum memiliki role. Hubungi administrator untuk mendapatkan akses.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('modules.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
