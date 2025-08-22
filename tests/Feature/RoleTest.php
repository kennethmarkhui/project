<?php

use App\Enums\PermissionType;
use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $this->get(route('roles'))->assertRedirect(route('login'));
    $this->get(route('roles.create', 1))->assertRedirect(route('login'));
    $this->post(route('roles.store', 1))->assertRedirect(route('login'));
    $this->get(route('roles.edit', 1))->assertRedirect(route('login'));
    $this->patch(route('roles.update', 1))->assertRedirect(route('login'));
    $this->delete(route('roles.destroy', 1))->assertRedirect(route('login'));
});

test('authorized user can view the roles page', function () {
    $response = $this->authorizedUser()->get(route('roles'));
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('roles/Index')
        ->has('roles'));
});

test('authorized super admin user can view a system role page', function () {
    $this->authorizedUser(true)->get(route('roles.show', 1))->assertOK();
    $this->authorizedUser(true)->get(route('roles.show', 2))->assertOK();
    $this->authorizedUser(true)->get(route('roles.show', 3))->assertOK();
    $this->authorizedUser(true)->get(route('roles.show', 4))->assertOK();
});

test('authorized user can view a system role page except for super admin', function () {
    $this->authorizedUser()->get(route('roles.show', 1))->assertNotFound();
    $this->authorizedUser()->get(route('roles.show', 2))->assertOK();
    $this->authorizedUser()->get(route('roles.show', 3))->assertOK();
    $this->authorizedUser()->get(route('roles.show', 4))->assertOK();
});

test('authorized user can view create page', function () {
    $response = $this->authorizedUser()->get(route('roles.create'));
    $response->assertOk();
});

test('authorized user can store', function () {
    $expectedPermission = PermissionType::USER_READ->value;
    $permission = Permission::findOrCreate($expectedPermission);

    $response = $this->authorizedUser()->post(route('roles.store'), [
        'name' => 'new role',
        'permissions' => [$permission->id]
    ]);
    $response->assertRedirect(route('roles'));

    $role = Role::with('permissions')->where('name', 'new role')->first();
    expect($role)->not->toBeNull()
        ->and($role->permissions)->toHaveCount(1)
        ->and($role->permissions->first()->name)->toBe($expectedPermission);
});

test('authorized user can view edit page', function () {
    $role = Role::factory()->create();

    $response = $this->authorizedUser()->get(route('roles.edit', $role->id));
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('roles/Edit')
        ->has('role')
        ->has('permissions'));
});

test('authorized user cannot view system role edit page', function () {
    $this->authorizedUser()->get(route('roles.edit', 1))->assertNotFound();
    $this->authorizedUser()->get(route('roles.edit', 2))->assertForbidden();
    $this->authorizedUser()->get(route('roles.edit', 3))->assertForbidden();
    $this->authorizedUser()->get(route('roles.edit', 4))->assertForbidden();
});

test('authorized super admin user cannot view system role edit page', function () {
    $this->authorizedUser(true)->get(route('roles.edit', 1))->assertForbidden();
    $this->authorizedUser(true)->get(route('roles.edit', 2))->assertForbidden();
    $this->authorizedUser(true)->get(route('roles.edit', 3))->assertForbidden();
    $this->authorizedUser(true)->get(route('roles.edit', 4))->assertForbidden();
});

test('authorized user can update a role', function () {
    $expectedPermission = PermissionType::USER_READ->value;
    $permission = Permission::findOrCreate($expectedPermission);
    $role = Role::factory()->create();

    $response = $this->authorizedUser()->patch(route('roles.update', $role->id), [
        'name' => 'updated',
        'permissions' => [$permission->id]
    ]);
    $response->assertRedirect();

    expect($role->fresh()->load('permissions'))
        ->name->toBe('updated')
        ->and($role->permissions)->toHaveCount(1)
        ->and($role->permissions->first()->name)->toBe($expectedPermission);;
});

test('authorized user can delete a role', function () {
    $role = Role::factory()->create();

    $response = $this->authorizedUser()->delete(route('roles.destroy', $role->id));
    $response->assertRedirect(route('roles'));

    expect($role->fresh())
        ->toBeNull();
});

test('authorized user cannot delete a system role', function () {
    $response = $this->authorizedUser()->delete(route('roles.destroy', 2));
    $response->assertForbidden();
});

test('authorized super admin user cannot delete a system role', function () {
    $this->authorizedUser(true)->delete(route('roles.destroy', 1))->assertForbidden();
    $this->authorizedUser(true)->delete(route('roles.destroy', 2))->assertForbidden();
    $this->authorizedUser(true)->delete(route('roles.destroy', 3))->assertForbidden();
    $this->authorizedUser(true)->delete(route('roles.destroy', 4))->assertForbidden();
});

test('unauthorized user cannot view the roles page', function () {
    $response = $this->unauthorizedUser()->get(route('roles'));
    $response->assertForbidden();
});

test('unauthorized user cannot view create page', function () {
    $response = $this->unauthorizedUser()->get(route('roles.create'));
    $response->assertForbidden();
});

test('unauthorized user cannot store', function () {
    $response = $this->unauthorizedUser()->post(route('roles.store'), [
        'name' => 'new role',
        'permissions' => []
    ]);
    $response->assertForbidden();
});

test('unauthorized user cannot view edit page', function () {
    $role = Role::findOrCreate(RoleType::USER->value);

    $response = $this->unauthorizedUser()->get(route('roles.edit', $role->id));
    $response->assertForbidden();
});

test('unauthorized user cannot update', function () {
    $role = Role::findOrCreate(RoleType::USER->value);

    $response = $this->unauthorizedUser()->patch(route('roles.update', $role->id), [
        'name' => 'updated',
        'permissions' => []
    ]);
    $response->assertForbidden();
});

test('unauthorized user cannot delete', function () {
    $role = Role::findOrCreate(RoleType::USER->value);

    $response = $this->unauthorizedUser()->delete(route('roles.destroy', $role->id));
    $response->assertForbidden();
});
