<?php

use App\Enums\RoleType;
use App\Enums\UserStatusType;
use App\Mail\UserInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Inertia\Testing\AssertableInertia;

test('authorized user can send a user invitation', function () {
    Mail::fake();

    $email = 'test@example.com';
    $response = $this->authorizedUser()->post(route('invitation.store'), [
        'email' => $email,
        'role' => RoleType::USER->value
    ]);

    $response->assertValid()->assertRedirect();

    Mail::assertSent(UserInvitation::class, $email);

    $invitation = Invitation::query()->where('email', $email)->first();
    expect($invitation)->not->toBeNull();
});

test('authorized super user can send a user invitation with super admin role', function () {
    Mail::fake();

    $email = 'test@example.com';
    $response = $this->authorizedUser(true)->post(route('invitation.store'), [
        'email' => $email,
        'role' => RoleType::SUPER_ADMIN->value
    ]);

    $response->assertValid()->assertRedirect();

    Mail::assertSent(UserInvitation::class, $email);

    $invitation = Invitation::query()->where('email', $email)->first();
    expect($invitation)->not->toBeNull();
});

test('authorized user cannot send a user invitation with super admin role', function () {
    Mail::fake();

    $email = 'test@example.com';
    $response = $this->authorizedUser()->post(route('invitation.store'), [
        'email' => $email,
        'role' => RoleType::SUPER_ADMIN->value
    ]);

    $response->assertInvalid('role');

    Mail::assertNothingSent();

    $invitation = Invitation::query()->where('email', $email)->first();
    expect($invitation)->toBeNull();
});

test('unauthorized user cannot send a user invitation', function () {
    Mail::fake();

    $email = 'test@example.com';
    $response = $this->unauthorizedUser()->post(route('invitation.store'), [
        'email' => $email,
        'role' => RoleType::USER->value
    ]);

    $response->assertForbidden();

    Mail::assertNothingSent();

    $invitation = Invitation::query()->where('email', $email)->first();
    expect($invitation)->toBeNull();
});

test('user with valid invite url is redirected to accept invite page', function () {
    $invitation = Invitation::factory()->create();

    $response = $this->get(route('invitation.show', $invitation->token));

    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('auth/AcceptInvite')
        ->where('email', $invitation->email)
        ->where('token', $invitation->token));
});

test('user with invalid invite url token is redirected to 404', function () {
    $response = $this->get(route('invitation.show', 'invalid token'));

    $response->assertNotFound();
});

test('user with expired invite url is redirected to register with error', function () {
    $invitation = Invitation::factory()->expired()->create();

    $response = $this->get(route('invitation.show', $invitation->token));

    $response->assertRedirect(route('register'));
});

test('user with used invite url is redirected to login with error', function () {
    $invitation = Invitation::factory()->accepted()->create();

    $response = $this->get(route('invitation.show', $invitation->token));

    $response->assertRedirect(route('login'));
});

test('user with valid invitation can create an account', function () {
    Event::fake();

    $invitation = Invitation::factory()->create();

    $response = $this->post(route('invitation.accept'), [
        'name' => 'Invited User',
        'email' => $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    expect($invitation->fresh()->accepted_at)->not->toBeNull();

    $user = User::where('email', $invitation->email)->first();
    expect($user)
        ->status->toBe(UserStatusType::APPROVED->value)
        ->hasVerifiedEmail()->toBeTrue();

    Event::assertDispatched(Verified::class);
    Event::assertDispatched(Registered::class);
});

test('user with invalid invitation token cannot create an account', function () {
    $invitation = Invitation::factory()->create();

    $response = $this->post(route('invitation.accept'), [
        'name' => 'Invited User',
        'email' => $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => 'invalid token',
    ]);

    $response->assertNotFound();
});

test('user with expired invitation cannot create an account', function () {
    $invitation = Invitation::factory()->expired()->create();

    $response = $this->post(route('invitation.accept'), [
        'name' => 'Invited User',
        'email' => $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('register'));
});

test('user with used invitation cannot create an account', function () {
    $invitation = Invitation::factory()->accepted()->create();

    $response = $this->post(route('invitation.accept'), [
        'name' => 'Invited User',
        'email' => $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('user with non-matching email in invitation cannot create an account', function () {
    $invitation = Invitation::factory()->create();

    $response = $this->post(route('invitation.accept'), [
        'name' => 'Invited User',
        'email' => 'non-matching' . $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('register'));
});
