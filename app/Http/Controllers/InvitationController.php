<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvitationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvitationRequest $request)
    {
        Gate::authorize('create', Invitation::class);

        $invitation = Invitation::query()->create([
            'email' => $request->safe()->email,
            'role_id' => Role::findByName($request->safe()->role)->id,
            'token' => Str::random(60),
            'invited_by' => $request->user()->id,
            'expires_at' => Carbon::now()->addDay()
        ]);

        Mail::to($request->safe()->email)->send(new UserInvitation($invitation));

        return back()->with('success', 'Invitation has been sent');
    }

    public function accept(Request $request)
    {
        $invitation = Invitation::query()->where('token', $request->route('token'))->firstOrFail();

        if ($invitation->isExpired()) {
            return to_route('register')->with('status', 'Invalid or expired invitation.');
        }

        if ($invitation->isAccepted()) {
            return to_route('login')->with('status', 'Invation has already been used.');
        }

        return Inertia::render('auth/Register', [
            'email' => $invitation->email,
            'token' => $request->route('token')
        ]);
    }
}
