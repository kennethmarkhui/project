<?php

use App\Enums\PermissionType;
use App\Enums\RoleType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('permissions'));
    $response->assertRedirect(route('login'));
});

describe('authorized', function () {
    beforeEach(function () {
        $this->authorizedUser = User::factory()->create();
        foreach (PermissionType::forModel(Permission::class) as $permission) {
            Permission::findOrCreate($permission->value);
        }
        Role::findOrCreate(RoleType::ADMIN->value)
            ->syncPermissions(PermissionType::forModelValues(Permission::class));
        $this->authorizedUser->syncRoles(RoleType::ADMIN->value);
        $this->actingAs($this->authorizedUser);
    });

    test('can view the permissions page', function () {
        $response = $this->get(route('permissions'));
        $response->assertInertia(fn(AssertableInertia $page) => $page
            ->component('permissions/Index')
            ->has('permissions'));
    });
});

describe('unauthorized', function () {
    beforeEach(function () {
        $this->unauthorizedUser = User::factory()->create();
        Role::findOrCreate(RoleType::USER->value);
        $this->unauthorizedUser->syncRoles(RoleType::USER->value);
        $this->actingAs($this->unauthorizedUser);
    });

    test('cannot view the permissions page', function () {
        $response = $this->get(route('permissions'));
        $response->assertStatus(403);
    });
});
