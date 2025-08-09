<?php

use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('permissions'));
    $response->assertRedirect(route('login'));
});

test('authorized user can view the permissions page', function () {
    $response = $this->authorizedUser()->get(route('permissions'));
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('permissions/Index')
        ->has('permissions'));
});

test('unauthorized user cannot view the permissions page', function () {
    $response = $this->unauthorizedUser()->get(route('permissions'));
    $response->assertForbidden();
});
