<?php

use App\Enums\UserStatusType;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    Event::fake();

    $response = $this->post(route('register.store'), [
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
