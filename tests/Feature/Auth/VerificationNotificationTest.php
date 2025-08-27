<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;

test('sends verification notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect('/');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('does not send verification notification if email is verified', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();
});
