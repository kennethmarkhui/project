<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatusType;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Register', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'token' => 'nullable|string',
        ]);

        $invitation = $request->token ? Invitation::query()->where('token', $request->token)->first() : null;

        if ($invitation?->isExpired()) {
            return to_route('register')->with('status', 'Invalid or expired invitation.');
        }

        if ($invitation?->isAccepted()) {
            return to_route('login')->with('status', 'Invation has already been used.');
        }

        if ($invitation && $invitation->email !== $request->email) {
            return to_route('register')->with('status', 'Email must match the invitation.');
        }

        $user = User::query()->create(array_filter([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $invitation ? UserStatusType::APPROVED->value : null
        ]));

        if ($invitation) {
            $invitation->update(['accepted_at' => now()]);

            $user->syncRoles($invitation->role_id);

            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
