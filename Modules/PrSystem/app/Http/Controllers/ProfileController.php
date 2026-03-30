<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('prsystem::profile.edit', [
            'user' => $request->user(),
            'sites' => \Modules\PrSystem\Models\Site::all(),
            'departments' => \Modules\PrSystem\Models\Department::all(),
        ]);
    }

    /**
     * Update the user's employment information.
     */
    public function updateEmployment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return Redirect::route('profile.edit')->with('status', 'employment-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Upload user signature image.
     */
    public function uploadSignature(Request $request): RedirectResponse
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();

        // Delete old signature if exists
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // Store new signature
        $path = $request->file('signature')->store('signatures', 'public');

        // Update user
        $user->update(['signature_path' => $path]);

        return Redirect::route('profile.edit')->with('status', 'signature-uploaded');
    }

    /**
     * Delete user signature image.
     */
    public function deleteSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
            $user->update(['signature_path' => null]);
        }

        return Redirect::route('profile.edit')->with('status', 'signature-deleted');
    }
}
