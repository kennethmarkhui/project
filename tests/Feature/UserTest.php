<?php

use App\Enums\PermissionType;
use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
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

describe('authorized', function () {
    beforeEach(function () {
        $this->authorizedUser = User::factory()->create();
        foreach (PermissionType::forModel(User::class) as $permission) {
            Permission::findOrCreate($permission->value);
        }
        Role::findOrCreate(RoleType::ADMIN->value)
            ->syncPermissions(PermissionType::forModelValues(User::class));
        $this->authorizedUser->syncRoles(RoleType::ADMIN->value);
        $this->actingAs($this->authorizedUser);
    });

    describe('user list', function () {
        beforeEach(function () {
            Role::findOrCreate(RoleType::EDITOR->value);
            Role::findOrCreate(RoleType::USER->value);
            $this->testUsers = User::factory()->count(11)->sequence(
                fn($sequence) => [
                    'name' => 'User ' . ($sequence->index + 1),
                    'deleted_at' => $sequence->index > 0  ? null : now(),
                ]
            )->create()->each(function ($user, $index) {
                $role = $index > 5 ? RoleType::USER->value : RoleType::EDITOR->value;
                $user->syncRoles($role);
            });
        });

        test('can view', function () {
            $response = $this->get(route('users'));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 10)
                    ->where('users.data', function ($users) {
                        if (
                            $users[0]['id'] !== $this->authorizedUser->id ||
                            $users[0]['name'] !== $this->authorizedUser->name ||
                            $users[0]['email'] !== $this->authorizedUser->email
                        ) {
                            return false;
                        }

                        for ($i = 1; $i < 10; $i++) {
                            $expectedUser = $this->testUsers[$i];
                            if (
                                $users[$i]['id'] !== $expectedUser->id ||
                                $users[$i]['name'] !== $expectedUser->name ||
                                $users[$i]['email'] !== $expectedUser->email
                            ) {
                                return false;
                            }
                        }
                        return true;
                    })
            );
        });

        test('can search', function () {
            $response = $this->get(route('users', ['search' => $this->testUsers->last()->name]));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 1)
                    ->has('users.data.0', fn(AssertableInertia $page) => $page
                        ->where('id', $this->testUsers->last()->id)
                        ->where('name', $this->testUsers->last()->name)
                        ->where('email', $this->testUsers->last()->email)
                        ->where('role', $this->testUsers->last()->getRoleNames()->first())
                        ->where('status', $this->testUsers->last()->status)
                        ->where('deleted_at', $this->testUsers->last()->deleted_at))
            );
        });

        test('can filter', function () {
            $filteredUsers = $this->testUsers->filter->hasRole(RoleType::USER->value);

            $response = $this->get(route('users', ['filters' => json_encode([['id' => 'role', 'value' => [RoleType::USER->value]]])]));
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

        test('can sort by name (desc)', function () {
            $response = $this->get(route('users', ['sort' => json_encode([['id' => 'name', 'desc' => true]])]));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 10)
                    ->where('users.data', function ($users) {
                        $names = collect($users)->pluck('name')->all();
                        $expected = ['User 9', 'User 8', "User 7", 'User 6', 'User 5', 'User 4', 'User 3', 'User 2', 'User 11', 'User 10'];

                        return $names === $expected;
                    })
            );
        });

        test('can view with soft-deleted users', function () {
            $response = $this->get(route('users', ['deleted' => 'with']));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 10)
                    ->where('users.data.1.deleted_at', $this->testUsers[0]->deleted_at->toISOString())
                    ->where('users.data', function ($users) {
                        return collect($users)
                            ->skip(2)
                            ->every(fn($user) => $user['deleted_at'] === null);
                    })

            );
        });

        test('can view only soft-deleted users', function () {
            $response = $this->get(route('users', ['deleted' => 'only']));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 1)
                    ->where('users.data.0.deleted_at', $this->testUsers[0]->deleted_at->toISOString())
            );
        });

        test('can paginate to second page', function () {
            $response = $this->get(route('users', ['page' => 2]));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 1)
                    ->has('users.data.0', fn(AssertableInertia $page) => $page
                        ->where('id', $this->testUsers->last()->id)
                        ->where('name', $this->testUsers->last()->name)
                        ->where('email', $this->testUsers->last()->email)
                        ->where('role', $this->testUsers->last()->getRoleNames()->first())
                        ->where('status', $this->testUsers->last()->status)
                        ->where('deleted_at', $this->testUsers->last()->deleted_at))
            );
        });
    });

    describe('single operations non-deleted', function () {
        beforeEach(function () {
            $this->nonDeletedTestUser = User::factory()->create();
        });

        test('can view edit page', function () {
            $response = $this->get(route('users.edit', $this->nonDeletedTestUser->id));
            $response->assertInertia(fn(AssertableInertia $page) => $page
                ->component('users/Edit')
                ->has('user', fn(AssertableInertia $page) => $page
                    ->where('id', $this->nonDeletedTestUser->id)
                    ->where('name', $this->nonDeletedTestUser->name)
                    ->where('email', $this->nonDeletedTestUser->email)
                    ->where('role', $this->nonDeletedTestUser->getRoleNames()->first())
                    ->where('status', $this->nonDeletedTestUser->status)
                    ->where('deleted_at', $this->nonDeletedTestUser->deleted_at)));
        });

        test('can update', function () {
            $response = $this->patch(route('users.update', $this->nonDeletedTestUser->id), [
                'name' => 'updated',
                'email' => $this->nonDeletedTestUser->email,
                'role' => RoleType::EDITOR->value,
                'status' => 'pending'
            ]);
            $response->assertRedirect(route('users'));

            $oldUserToUpdateEmail = $this->nonDeletedTestUser->email;

            expect($this->nonDeletedTestUser->fresh())
                ->name->toBe('updated')
                ->email->toBe($oldUserToUpdateEmail)
                ->status->toBe('pending')
                ->hasRole(RoleType::EDITOR->value)->toBeTrue();
        });

        test('updating email resets verification', function () {
            $response = $this->patch(route('users.update', $this->nonDeletedTestUser->id), [
                'name' => 'updated',
                'email' => 'update@email.com',
                'role' => RoleType::EDITOR->value,
                'status' => 'pending'
            ]);
            $response->assertRedirect(route('users'));

            expect($this->nonDeletedTestUser->fresh()->email_verfified_at)
                ->toBeNull();
        });

        test('can soft delete', function () {
            $response = $this->delete(route('users.destroy', $this->nonDeletedTestUser->id));
            $response->assertRedirect(route('users'));

            expect($this->nonDeletedTestUser->fresh()->deleted_at)
                ->not->toBeNull();
        });

        test('cannot soft delete current user', function () {
            $response = $this->delete(route('users.destroy', $this->authorizedUser->id));
            $response->assertStatus(403);
        });
    });

    describe('single operations deleted', function () {
        beforeEach(function () {
            $this->deletedTestUser = User::factory()->deleted()->create();
        });

        test('can permanently delete', function () {
            $response = $this->delete(route('users.force_delete', $this->deletedTestUser->id));
            $response->assertRedirect(route('users'));

            expect($this->deletedTestUser->fresh())
                ->toBeNull();
        });


        test('can restore', function () {
            $response = $this->patch(route('users.restore', $this->deletedTestUser->id));
            $response->assertRedirect(route('users'));

            expect($this->deletedTestUser->fresh()->deleted_at)
                ->toBeNull();
        });
    });

    describe('bulk operations non-deleted', function () {
        beforeEach(function () {
            $this->nonDeletedTestUsers =  User::factory()->count(10)->create();
            $this->nonDeletedTestUsersIds = $this->nonDeletedTestUsers->pluck('id')->implode(',');
        });

        test('can bulk update', function () {
            $response = $this->patch(route('users.bulk_update', $this->nonDeletedTestUsersIds), [
                'role' => RoleType::EDITOR->value,
            ]);
            $response->assertRedirect();

            $this->nonDeletedTestUsers->each(function ($user) {
                expect($user->fresh()->hasRole(RoleType::EDITOR->value))
                    ->toBeTrue();
            });
        });

        test('can bulk soft delete', function () {
            $response = $this->delete(route('users.bulk_destroy',  $this->nonDeletedTestUsersIds));
            $response->assertRedirect();

            $this->nonDeletedTestUsers->each(function ($user) {
                expect($user->fresh()->deleted_at)
                    ->not->toBeNull();
            });
        });
    });

    describe('bulk operations deleted', function () {
        beforeEach(function () {
            $this->deletedTestUsers = User::factory()->count(10)->deleted()->create();
            $this->deletedTestUsersIds =  $this->deletedTestUsers->pluck('id')->implode(',');
        });

        test('can bulk permanently delete', function () {
            $response = $this->delete(route('users.bulk_force_delete',  $this->deletedTestUsersIds));
            $response->assertRedirect();

            $this->deletedTestUsers->each(function ($user) {
                expect($user->fresh())
                    ->toBeNull();
            });
        });

        test('can bulk restore', function () {
            $response = $this->patch(route('users.bulk_restore', $this->deletedTestUsersIds));
            $response->assertRedirect();

            $this->deletedTestUsers->each(function ($user) {
                expect($user->fresh()->deleted_at)
                    ->toBeNull();
            });
        });
    });
});

describe('unauthorized', function () {
    beforeEach(function () {
        $this->unauthorizedUser = User::factory()->create();
        Role::findOrCreate(RoleType::USER->value);
        $this->unauthorizedUser->syncRoles(RoleType::USER->value);
        $this->actingAs($this->unauthorizedUser);
    });

    test('cannot view the users page', function () {

        $response = $this->get(route('users'));
        $response->assertStatus(403);
    });

    describe('single operations non-deleted', function () {
        beforeEach(function () {
            $this->nonDeletedTestUser = User::factory()->create();
        });

        test('cannot view edit page', function () {
            $response = $this->get(route('users.edit', $this->nonDeletedTestUser->id));
            $response->assertStatus(403);
        });

        test('cannot update', function () {
            $response = $this->patch(route('users.update', $this->nonDeletedTestUser->id), [
                'name' => 'updated',
                'email' => $this->nonDeletedTestUser->email,
                'role' => RoleType::EDITOR->value,
                'status' => 'pending'
            ]);
            $response->assertStatus(403);
        });

        test('cannot soft delete', function () {
            $response = $this->delete(route('users.destroy', $this->nonDeletedTestUser->id));
            $response->assertStatus(403);
        });
    });

    describe('single operations deleted', function () {
        beforeEach(function () {
            $this->deletedTestUser = User::factory()->deleted()->create();
        });

        test('cannot permanently delete', function () {
            $response = $this->delete(route('users.force_delete', $this->deletedTestUser->id));
            $response->assertStatus(403);
        });


        test('cannot restore', function () {
            $response = $this->patch(route('users.restore', $this->deletedTestUser->id));
            $response->assertStatus(403);
        });
    });

    describe('bulk operations non-deleted', function () {
        beforeEach(function () {
            $this->nonDeletedTestUsers =  User::factory()->count(10)->create();
            $this->nonDeletedTestUsersIds = $this->nonDeletedTestUsers->pluck('id')->implode(',');
        });

        test('cannot bulk update', function () {
            $response = $this->patch(route('users.bulk_update', $this->nonDeletedTestUsersIds), [
                'role' => RoleType::EDITOR->value,
            ]);
            $response->assertStatus(403);
        });

        test('cannot bulk soft delete', function () {
            $response = $this->delete(route('users.bulk_destroy',  $this->nonDeletedTestUsersIds));
            $response->assertStatus(403);
        });
    });

    describe('bulk operations deleted', function () {
        beforeEach(function () {
            $this->deletedTestUsers = User::factory()->count(10)->deleted()->create();
            $this->deletedTestUsersIds =  $this->deletedTestUsers->pluck('id')->implode(',');
        });

        test('cannot bulk permanently delete', function () {
            $response = $this->delete(route('users.bulk_force_delete',  $this->deletedTestUsersIds));
            $response->assertStatus(403);
        });

        test('cannot bulk restore', function () {
            $response = $this->patch(route('users.bulk_restore', $this->deletedTestUsersIds));
            $response->assertStatus(403);
        });
    });
});
