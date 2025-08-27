<?php

use App\Enums\UserStatusType;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh())
        ->hasVerifiedEmail()->toBeTrue()
        ->status->toBe(UserStatusType::APPROVED->value);
    $response->assertRedirect(route('dashboard', absolute: false) . '?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('email is not verified with invalid user id', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => 123, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl);

    $this->assertFalse($user->fresh()->hasVerifiedEmail());
});

test('verified user is redirected to dashboard from verification prompt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('already verified user visiting verification link is redirected without firing event again', function () {
    $user = User::factory()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false) . '?verified=1');

    $this->assertTrue($user->fresh()->hasVerifiedEmail());
    Event::assertNotDispatched(Verified::class);
});
