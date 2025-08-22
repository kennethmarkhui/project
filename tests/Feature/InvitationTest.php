<?php

use App\Enums\RoleType;
use App\Mail\UserInvitation;
use App\Models\Invitation;
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

test('user with valid invite url is redirected to register', function () {
    $invitation = Invitation::factory()->create();

    $response = $this->get(route('invitation.accept', $invitation->token));

    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('auth/Register')
        ->where('email', $invitation->email)
        ->where('token', $invitation->token));
});

test('user with invalid token is redirected to 404', function () {

    $response = $this->get(route('invitation.accept', 'invalid token'));

    $response->assertNotFound();
});

test('user with expired invite url is redirected to register with error', function () {
    $invitation = Invitation::factory()->expired()->create();

    $response = $this->get(route('invitation.accept', $invitation->token));

    $response->assertRedirect(route('register'));
});

test('user with used invite url is redirected to login with error', function () {
    $invitation = Invitation::factory()->accepted()->create();

    $response = $this->get(route('invitation.accept', $invitation->token));

    $response->assertRedirect(route('login'));
});
