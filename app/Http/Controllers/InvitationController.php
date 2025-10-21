<?php

namespace App\Http\Controllers;

use App\Enums\UserStatusType;
use App\Http\Requests\Invitation\AcceptInvitationRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class InvitationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvitationRequest $request)
    {
        Gate::authorize('create', Invitation::class);

        $request->isInvitationNotExpired()?->delete();

        $invitation = Invitation::query()->create([
            'email' => $request->safe()->email,
            'role_id' => Role::findByName($request->safe()->role)->id,
            'invited_by' => $request->user()->id,
            'expires_at' => Carbon::now()->addDay()
        ]);

        Mail::to($request->safe()->email)->send(new UserInvitation($invitation));

        return back()->with('success', 'Invitation has been sent');
    }

    /**
     * Show the invitation form page.
     */
    public function show(Request $request, Invitation $invitation)
    {
        if ($invitation->isAccepted()) {
            return to_route('login')->with('error', 'Invation has already been used');
        }

        return Inertia::render('auth/AcceptInvite', [
            'email' => $invitation->email
        ]);
    }

    /**
     * Handle registration through invitation.
     */
    public function accept(AcceptInvitationRequest $request)
    {
        $invitation = Invitation::query()->where('email', $request->safe()->email)->firstOrFail();

        if ($invitation->isExpired()) {
            return to_route('register')->with('error', 'Invalid or expired invitation');
        }

        if ($invitation->isAccepted()) {
            return to_route('login')->with('error', 'Invation has already been used');
        }

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'status' => UserStatusType::APPROVED->value
        ]);

        $invitation->update(['accepted_at' => Carbon::now()]);

        $user->syncRoles($invitation->role_id);

        $user->markEmailAsVerified();
        event(new Verified($user));

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return to_route('dashboard');
    }
}
