<?php

use App\Enums\RoleType;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $this->get(route('users'))->assertRedirect(route('login'));
    $this->get(route('users.edit', 1))->assertRedirect(route('login'));
    $this->patch(route('users.update', 1))->assertRedirect(route('login'));
    $this->patch(route('users.bulk_update', 1))->assertRedirect(route('login'));
    $this->delete(route('users.destroy', 1))->assertRedirect(route('login'));
    $this->delete(route('users.bulk_destroy', 1))->assertRedirect(route('login'));
    $this->patch(route('users.restore', 1))->assertRedirect(route('login'));
    $this->patch(route('users.bulk_restore', 1))->assertRedirect(route('login'));
    $this->delete(route('users.force_delete', 1))->assertRedirect(route('login'));
    $this->delete(route('users.bulk_force_delete', 1))->assertRedirect(route('login'));
});

test('authorized user can view users page', function () {
    $response = $this->authorizedUser()->get(route('users'));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 1)
    );
});

test('authorized super admin user can view users with super admin users', function () {
    User::factory()->create();

    $response = $this->authorizedUser(true)->get(route('users'));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 2)
    );
});

test('authorized user can only view non super admin users', function () {
    User::factory()->create()->syncRoles(RoleType::SUPER_ADMIN);

    $response = $this->authorizedUser()->get(route('users'));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 1)
    );
});

test('authorized user can search for users', function () {
    $response = $this->authorizedUser()->get(route('users', ['search' => 'authorized']));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 1)
    );
});

test('authorized user can filter for users', function () {
    $users = User::factory()->count(5)->create();
    $filteredUsers = $users->filter->hasRole(RoleType::USER->value);

    $response = $this->authorizedUser()->get(route('users', ['filters' => json_encode([['id' => 'role', 'value' => [RoleType::USER->value]]])]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', $filteredUsers->count())
            ->where('users.data', function ($responseUsers) use ($filteredUsers) {
                $expectedIds = $filteredUsers->pluck('id')->sort()->values();
                $actualIds = collect($responseUsers)->pluck('id')->sort()->values();

                return $expectedIds->toArray() === $actualIds->toArray();
            })
    );
});

test('authorized user can sort users by name (desc)', function () {
    $users = User::factory()->count(5)->create();
    $allUsers = $users->push($this->authorizedUser()->getCurrentUser());

    $expected = $allUsers->sortByDesc('name')
        ->pluck('name')
        ->values()
        ->all();

    $response = $this->get(route('users', ['sort' => json_encode([['id' => 'name', 'desc' => true]])]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data')
            ->where('users.data', function ($users) use ($expected) {
                $actualNames = collect($users)->pluck('name')->all();

                return $actualNames === $expected;
            })
    );
});

test('authorized user can view users with soft-deleted users', function () {
    User::factory()->deleted()->create();

    $response = $this->authorizedUser()->get(route('users', ['deleted' => 'with']));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 2)
            ->where('users.data.0.deleted_at', fn($deleted_at) => $deleted_at !== null)
            ->where('users.data.1.deleted_at', null)
    );
});

test('authorized user can view only soft-deleted users', function () {
    User::factory()->deleted()->create();

    $response = $this->authorizedUser()->get(route('users', ['deleted' => 'only']));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.deleted_at', fn($deleted_at) => $deleted_at !== null)
    );
});

test('authorized user can paginate to second page', function () {
    User::factory()->count(10)->create();

    $response = $this->authorizedUser()->get(route('users', ['page' => 2]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('users/Index')
            ->has('users.data', 1)
    );
});

test('authorized user can view edit page', function () {
    $user = User::factory()->create();

    $response = $this->authorizedUser()->get(route('users.edit', $user->id));
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('users/Edit')
        ->has('user'));
});

test('authorized user can update a user', function () {
    $user = User::factory()->create();

    $response = $this->authorizedUser()->patch(route('users.update', $user->id), [
        'name' => 'updated',
        'email' => $user->email,
        'role' => RoleType::EDITOR->value,
        'status' => $user->status
    ]);
    $response->assertRedirect(route('users'));

    expect($user->fresh())
        ->name->toBe('updated')
        ->email->toBe($user->email)
        ->status->toBe($user->status)
        ->hasRole(RoleType::EDITOR->value)->toBeTrue();
});

test('authorized super admin user can update a users role to super admin', function () {
    $user = User::factory()->create();

    $response = $this->authorizedUser(true)->patch(route('users.update', $user->id), [
        'name' => 'updated',
        'email' => $user->email,
        'role' => RoleType::SUPER_ADMIN->value,
        'status' => $user->status
    ]);
    $response->assertRedirect();

    expect($user->fresh())
        ->hasRole(RoleType::SUPER_ADMIN->value)->toBeTrue();;
});

test('authorized non super admin user cannot update a users role to super admin', function () {
    $user = User::factory()->create();

    $response = $this->authorizedUser()->patch(route('users.update', $user->id), [
        'name' => 'updated',
        'email' => $user->email,
        'role' => RoleType::SUPER_ADMIN->value,
        'status' => $user->status
    ]);
    $response->assertInvalid('role');

    expect($user->fresh())
        ->hasRole(RoleType::SUPER_ADMIN->value)->toBeFalse();;
});

test('updating email resets verification', function () {
    $user = User::factory()->create();

    $response = $this->authorizedUser()->patch(route('users.update', $user->id), [
        'name' => $user->name,
        'email' => 'update@email.com',
        'role' => RoleType::EDITOR->value,
        'status' => $user->status
    ]);
    $response->assertRedirect(route('users'));

    expect($user->fresh()->email_verfified_at)
        ->toBeNull();
});

test('authorized user can soft delete a user', function () {
    $user = User::factory()->create();

    $response = $this->authorizedUser()->delete(route('users.destroy', $user->id));
    $response->assertRedirect(route('users'));

    expect($user->fresh()->deleted_at)
        ->not->toBeNull();
});

test('authorized user cannot soft delete current user from the list', function () {
    $response = $this->authorizedUser()->delete(route('users.destroy', $this->getCurrentUser()->id));
    $response->assertForbidden();
});

test('authorized super admin user cannot soft delete current user from the list', function () {
    $response = $this->authorizedUser(true)->delete(route('users.destroy', $this->getCurrentUser()->id));
    $response->assertForbidden();
});

test('authorized user can permanently delete a user', function () {
    $user = User::factory()->deleted()->create();

    $response = $this->authorizedUser()->delete(route('users.force_delete', $user->id));
    $response->assertRedirect(route('users'));

    expect($user->fresh())
        ->toBeNull();
});

test('authorized user can restore a soft deleted user', function () {
    $user = User::factory()->deleted()->create();

    $response = $this->authorizedUser()->patch(route('users.restore', $user->id));
    $response->assertRedirect(route('users'));

    expect($user->fresh()->deleted_at)
        ->toBeNull();
});

test('authorized user can update users in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->patch(route('users.bulk_update', $ids), [
        'role' => RoleType::EDITOR->value,
    ]);
    $response->assertRedirect();

    $users->each(function ($user) {
        expect($user->fresh()->hasRole(RoleType::EDITOR->value))
            ->toBeTrue();
    });
});

test('authorized user cannot update users role to super admin in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->patch(route('users.bulk_update', $ids), [
        'role' => RoleType::SUPER_ADMIN->value,
    ]);
    $response->assertInvalid('role');
});

test('authorized super admin user can update users role to super admin in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser(true)->patch(route('users.bulk_update', $ids), [
        'role' => RoleType::SUPER_ADMIN->value,
    ]);
    $response->assertRedirect();

    $users->each(function ($user) {
        expect($user->fresh()->hasRole(RoleType::SUPER_ADMIN->value))
            ->toBeTrue();
    });
});

test('authorized user cannot soft update users with current user in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->patch(route('users.bulk_update', "{$ids},{$this->getCurrentUser()->id}"), [
        'role' => RoleType::EDITOR->value,
    ]);
    $response->assertForbidden();
});

test('authorized super admin user cannot soft update users with current user in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser(true)->patch(route('users.bulk_update', "{$ids},{$this->getCurrentUser()->id}"), [
        'role' => RoleType::EDITOR->value,
    ]);
    $response->assertForbidden();
});

test('authorized user can soft delete users in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->delete(route('users.bulk_destroy',  $ids));
    $response->assertRedirect();

    $users->each(function ($user) {
        expect($user->fresh()->deleted_at)
            ->not->toBeNull();
    });
});

test('authorized user cannot soft delete users with current user in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->delete(route('users.bulk_destroy', "{$ids},{$this->getCurrentUser()->id}"));
    $response->assertForbidden();
});

test('authorized super admin user cannot soft delete users with current user in bulk', function () {
    $users = User::factory()->count(5)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser(true)->delete(route('users.bulk_destroy', "{$ids},{$this->getCurrentUser()->id}"));
    $response->assertForbidden();
});

test('authorized user can permanently delete users in bulk', function () {
    $users = User::factory()->count(5)->deleted()->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->delete(route('users.bulk_force_delete',  $ids));
    $response->assertRedirect();

    $users->each(function ($user) {
        expect($user->fresh())
            ->toBeNull();
    });
});

test('authorized user can restore users in bulk', function () {
    $users = User::factory()->count(5)->deleted()->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->authorizedUser()->patch(route('users.bulk_restore', $ids));
    $response->assertRedirect();

    $users->each(function ($user) {
        expect($user->fresh()->deleted_at)
            ->toBeNull();
    });
});

test('unauthorized user cannot view the users page', function () {
    $response = $this->unauthorizedUser()->get(route('users'));
    $response->assertForbidden();
});

test('unauthorized user cannot view edit page', function () {
    $user = User::factory()->create();

    $response = $this->unauthorizedUser()->get(route('users.edit', $user->id));
    $response->assertForbidden();
});

test('unauthorized user cannot update a user', function () {
    $user = User::factory()->create();

    $response = $this->unauthorizedUser()->patch(route('users.update', $user->id), [
        'name' => 'updated',
        'email' => $user->email,
        'role' => RoleType::EDITOR->value,
        'status' => $user->status
    ]);
    $response->assertForbidden();
});

test('unauthorized user cannot soft delete a user', function () {
    $user = User::factory()->create();

    $response = $this->unauthorizedUser()->delete(route('users.destroy', $user->id));
    $response->assertForbidden();
});

test('unauthorized user cannot permanently delete a user', function () {
    $user = User::factory()->deleted()->create();

    $response = $this->unauthorizedUser()->delete(route('users.force_delete', $user->id));
    $response->assertForbidden();
});

test('unauthorized user cannot restore a user', function () {
    $user = User::factory()->deleted()->create();

    $response = $this->unauthorizedUser()->patch(route('users.restore', $user->id));
    $response->assertForbidden();
});

test('unauthorized user cannot update users in bulk', function () {
    $users =  User::factory()->count(2)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->unauthorizedUser()->patch(route('users.bulk_update', $ids), [
        'role' => RoleType::EDITOR->value,
    ]);
    $response->assertForbidden();
});

test('unauthorized user cannot soft delete users in bulk', function () {
    $users =  User::factory()->count(2)->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->unauthorizedUser()->delete(route('users.bulk_destroy',  $ids));
    $response->assertForbidden();
});

test('unauthorized user cannot permanently delete users in bulk', function () {
    $users =  User::factory()->count(2)->deleted()->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->unauthorizedUser()->delete(route('users.bulk_force_delete',  $ids));
    $response->assertForbidden();
});

test('unauthorized user cannot restore users in bulk', function () {
    $users =  User::factory()->count(2)->deleted()->create();
    $ids = $users->pluck('id')->implode(',');

    $response = $this->unauthorizedUser()->patch(route('users.bulk_restore', $ids));
    $response->assertForbidden();
});
