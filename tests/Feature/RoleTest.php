<?php

use App\Enums\PermissionType;
use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $this->get(route('roles'))->assertRedirect(route('login'));
    $this->get(route('roles.create', 1))->assertRedirect(route('login'));
    $this->post(route('roles.store', 1))->assertRedirect(route('login'));
    $this->get(route('roles.edit', 1))->assertRedirect(route('login'));
    $this->patch(route('roles.update', 1))->assertRedirect(route('login'));
    $this->delete(route('roles.destroy', 1))->assertRedirect(route('login'));
});

describe('authorized', function () {
    beforeEach(function () {
        $this->authorizedUser = User::factory()->create();
        foreach (PermissionType::forModel(Role::class) as $permission) {
            Permission::findOrCreate($permission->value);
        }
        Role::findOrCreate(RoleType::ADMIN->value)
            ->syncPermissions(PermissionType::forModelValues(Role::class));
        $this->authorizedUser->syncRoles(RoleType::ADMIN->value);
        $this->actingAs($this->authorizedUser);
    });

    test('can view the roles page', function () {
        $response = $this->get(route('roles'));
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('roles/Index')
            ->has('roles'));
    });

    test('can view create page', function () {
        $response = $this->get(route('roles.create'));
        $response->assertStatus(200);
    });

    test('can store', function () {
        $expectedPermission = PermissionType::USER_READ->value;
        $permission = Permission::findOrCreate($expectedPermission);

        $response = $this->post(route('roles.store'), [
            'name' => 'new role',
            'permissions' => [$permission->id]
        ]);
        $response->assertRedirect(route('roles'));

        $role = Role::with('permissions')->where('name', 'new role')->first();
        expect($role)->not->toBeNull()
            ->and($role->permissions)->toHaveCount(1)
            ->and($role->permissions->first()->name)->toBe($expectedPermission);
    });

    test('can view edit page', function () {
        $testRole = Role::findOrCreate('test');

        $response = $this->get(route('roles.edit', $testRole->id));
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('roles/Edit')
            ->has('roles')
            ->has('role')
            ->has('permissions'));
    });

    test('can update', function () {
        $expectedPermission = PermissionType::USER_READ->value;
        $permission = Permission::findOrCreate($expectedPermission);
        $testRole = Role::findOrCreate('test');

        $response = $this->patch(route('roles.update', $testRole->id), [
            'name' => 'updated',
            'permissions' => [$permission->id]
        ]);
        $response->assertRedirect();

        expect($testRole->fresh()->load('permissions'))
            ->name->toBe('updated')
            ->and($testRole->permissions)->toHaveCount(1)
            ->and($testRole->permissions->first()->name)->toBe($expectedPermission);;
    });

    test('can delete', function () {
        $testRole = Role::findOrCreate('test');

        $response = $this->delete(route('roles.destroy', $testRole->id));
        $response->assertRedirect(route('roles'));

        expect($testRole->fresh())
            ->toBeNull();
    });
});

describe('unauthorized', function () {
    beforeEach(function () {
        $this->unauthorizedUser = User::factory()->create();
        Role::findOrCreate(RoleType::USER->value);
        $this->unauthorizedUser->syncRoles(RoleType::USER->value);
        $this->actingAs($this->unauthorizedUser);
    });

    test('cannot view the roles page', function () {
        $response = $this->get(route('roles'));
        $response->assertStatus(403);
    });

    test('cannot view create page', function () {
        $response = $this->get(route('roles.create'));
        $response->assertStatus(403);
    });

    test('cannot store', function () {
        $response = $this->post(route('roles.store'), [
            'name' => 'new role',
            'permissions' => []
        ]);
        $response->assertStatus(403);
    });

    test('cannot view edit page', function () {
        $testRole = Role::findOrCreate('test');

        $response = $this->get(route('roles.edit', $testRole->id));
        $response->assertStatus(403);
    });

    test('cannot update', function () {
        $testRole = Role::findOrCreate('test');

        $response = $this->patch(route('roles.update', $testRole->id), [
            'name' => 'updated',
            'permissions' => []
        ]);
        $response->assertStatus(403);
    });

    test('cannot delete', function () {
        $testRole = Role::findOrCreate('test');

        $response = $this->delete(route('roles.destroy', $testRole->id));
        $response->assertStatus(403);
    });
});
