<?php

use App\Enums\UserStatusType;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    Event::fake();

    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->status->toBe(UserStatusType::PENDING->value);

    Event::assertNotDispatched(Verified::class);
    Event::assertDispatched(Registered::class);
});

test('user with valid invitation can register', function () {
    Event::fake();

    $invitation = Invitation::factory()->create();

    $response = $this->post(route('register'), [
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

test('user with expired invitation cannot register', function () {
    $invitation = Invitation::factory()->expired()->create();

    $response = $this->post(route('register'), [
        'name' => 'Invited User',
        'email' => $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('register'));
});

test('user with used invitation cannot register', function () {
    $invitation = Invitation::factory()->accepted()->create();

    $response = $this->post(route('register'), [
        'name' => 'Invited User',
        'email' => $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('user with non-matching email in invitation cannot register', function () {
    $invitation = Invitation::factory()->create();

    $response = $this->post(route('register'), [
        'name' => 'Invited User',
        'email' => 'non-matching' . $invitation->email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => $invitation->token,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('register'));
});
