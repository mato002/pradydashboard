<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcedPasswordChangeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->mustChangePassword()) {
            return redirect()->route('dashboard');
        }

        return view('auth.password-expired', [
            'expiryDays' => config('auth.password_expiry_days', 28),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user?->mustChangePassword()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $user->markPasswordChanged();

        return redirect()
            ->route('dashboard')
            ->with('status', __('Password updated. You can continue using the dashboard.'));
    }
}
