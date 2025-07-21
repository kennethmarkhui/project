<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;


test('guests are redirected to the login page', function () {
    $response = $this->get(route('users'));
    $response->assertRedirect(route('login'));
});

describe('authenticated', function () {
    beforeEach(function () {
        $this->authenticatedUser =  User::factory()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin']);
        $this->actingAs($this->authenticatedUser);
    });

    test('can visit the users page', function () {

        $response = $this->get(route('users'));
        $response->assertStatus(200);
    });

    describe('user list', function () {
        beforeEach(function () {
            $this->testUsers = User::factory()->count(11)->sequence(
                fn($sequence) => [
                    'name' => 'User ' . ($sequence->index + 1),
                    'deleted_at' => $sequence->index > 0  ? null : now(),
                    'role' => $sequence->index > 5 ? 'basic' : 'staff'
                ]
            )->create();
        });

        test('can view', function () {
            $response = $this->get(route('users'));
            $response->assertInertia(
                fn(AssertableInertia $page) => $page
                    ->component('users/Index')
                    ->has('users.data', 10)
                    ->where('users.data', function ($users) {
                        if (
                            $users[0]['id'] !== $this->authenticatedUser->id ||
                            $users[0]['name'] !== $this->authenticatedUser->name ||
                            $users[0]['email'] !== $this->authenticatedUser->email
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
                        ->where('role', $this->testUsers->last()->role)
                        ->where('status', $this->testUsers->last()->status)
                        ->where('deleted_at', $this->testUsers->last()->deleted_at))
            );
        });

        test('can filter', function () {
            $filteredUsers = $this->testUsers->whereIn('role', 'basic');

            $response = $this->get(route('users', ['filters' => json_encode([['id' => 'role', 'value' => ['basic']]])]));
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
                        ->where('role', $this->testUsers->last()->role)
                        ->where('status', $this->testUsers->last()->status)
                        ->where('deleted_at', $this->testUsers->last()->deleted_at))
            );
        });
    });

    describe('single operations', function () {
        describe('non-deleted users', function () {
            beforeEach(function () {
                $this->nonDeletedTestUser = User::factory()->create();
            });

            test('can view edit page', function () {
                $response = $this->get(route('users.edit', $this->nonDeletedTestUser->id));
                $response->assertInertia(fn(AssertableInertia $page) => $page
                    ->component('users/Edit')
                    ->has('user.data', fn(AssertableInertia $page) => $page
                        ->where('id', $this->nonDeletedTestUser->id)
                        ->where('name', $this->nonDeletedTestUser->name)
                        ->where('email', $this->nonDeletedTestUser->email)
                        ->where('role', $this->nonDeletedTestUser->role)
                        ->where('status', $this->nonDeletedTestUser->status)
                        ->where('deleted_at', $this->nonDeletedTestUser->deleted_at)));
            });

            test('can update', function () {
                $response = $this->patch(route('users.update', $this->nonDeletedTestUser->id), [
                    'name' => 'updated',
                    'email' => $this->nonDeletedTestUser->email,
                    'role' => 'staff',
                    'status' => 'pending'
                ]);
                $response->assertRedirect(route('users'));


                $oldUserToUpdateEmail = $this->nonDeletedTestUser->email;

                expect($this->nonDeletedTestUser->fresh())
                    ->name->toBe('updated')
                    ->email->toBe($oldUserToUpdateEmail)
                    ->role->toBe('staff')
                    ->status->toBe('pending');
            });

            test('updating email resets verification', function () {
                $response = $this->patch(route('users.update', $this->nonDeletedTestUser->id), [
                    'name' => 'updated',
                    'email' => 'update@email.com',
                    'role' => 'staff',
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
        });

        describe('deleted users', function () {
            beforeEach(function () {
                $this->deletedTestUser = User::factory()->deleted()->create();
            });

            test('can permanently delete', function () {
                $response = $this->delete(route('users.destroy', $this->deletedTestUser->id));
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
    });

    describe('bulk operations', function () {
        describe('non-deleted users', function () {
            beforeEach(function () {
                $this->nonDeletedTestUsers =  User::factory()->count(10)->create();
                $this->nonDeletedTestUsersIds = $this->nonDeletedTestUsers->pluck('id')->implode(',');
            });

            test('can bulk update', function () {
                $response = $this->patch(route('users.update', $this->nonDeletedTestUsersIds), [
                    'role' => 'staff',
                ]);
                $response->assertRedirect();

                $this->nonDeletedTestUsers->each(function ($user) {
                    expect($user->fresh()->role)
                        ->toBe('staff');
                });
            });

            test('can bulk soft delete', function () {
                $response = $this->delete(route('users.destroy',  $this->nonDeletedTestUsersIds));
                $response->assertRedirect();

                $this->nonDeletedTestUsers->each(function ($user) {
                    expect($user->fresh()->deleted_at)
                        ->not->toBeNull();
                });
            });
        });

        describe('deleted users', function () {
            beforeEach(function () {
                $this->deletedTestUsers = User::factory()->count(10)->deleted()->create();
                $this->deletedTestUsersIds =  $this->deletedTestUsers->pluck('id')->implode(',');
            });

            test('can bulk permanently delete', function () {
                $response = $this->delete(route('users.destroy',  $this->deletedTestUsersIds));
                $response->assertRedirect();

                $this->deletedTestUsers->each(function ($user) {
                    expect($user->fresh())
                        ->toBeNull();
                });
            });

            test('can bulk restore', function () {
                $response = $this->patch(route('users.restore', $this->deletedTestUsersIds));
                $response->assertRedirect();

                $this->deletedTestUsers->each(function ($user) {
                    expect($user->fresh()->deleted_at)
                        ->toBeNull();
                });
            });
        });
    });
});
