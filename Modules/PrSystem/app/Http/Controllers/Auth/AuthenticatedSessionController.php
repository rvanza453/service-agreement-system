<?php

namespace Modules\PrSystem\Http\Controllers\Auth;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Http\Requests\Auth\LoginRequest;
use Modules\PrSystem\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('prsystem::auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        \Modules\PrSystem\Helpers\ActivityLogger::log('login', 'User logged in');

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        \Modules\PrSystem\Helpers\ActivityLogger::log('logout', 'User logged out');
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
