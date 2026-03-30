<?php

namespace Modules\PrSystem\Http\Controllers\Auth;

use Modules\PrSystem\Http\Controllers\Controller;
use Modules\PrSystem\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(RouteServiceProvider::HOME)
                    : view('prsystem::auth.verify-email');
    }
}
