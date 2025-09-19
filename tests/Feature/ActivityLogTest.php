<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('activity.logs'));
    $response->assertRedirect(route('login'));
});

test('authorized user can view the activity log page', function () {
    $response = $this->authorizedUser()->get(route('activity.logs'));
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('activity-logs/Index')
        ->has('activity_logs.data'));
});

test('unauthorized user cannot view the activity log page', function () {
    $response = $this->unauthorizedUser()->get(route('activity.logs'));
    $response->assertForbidden();
});

test('authorized user cannot view super admin user causer for activities', function () {
    $this->authorizedUser(true);
    $activity = User::factory()->create();

    $superUserResponse = $this->get(route('activity.logs'));
    $superUserResponse->assertInertia(fn(AssertableInertia $page) => $page
        ->component('activity-logs/Index')
        ->has('activity_logs.data')
        ->where('activity_logs.data', function (Collection $data) use ($activity) {
            $targetActivity = $data->first(function ($item) use ($activity) {
                return $item['subject_type'] === $activity::class && $item['subject_id'] == $activity->id;
            });
            return $targetActivity['causer']['id'] === $this->getCurrentUser()->id;
        }));

    $response = $this->authorizedUser()->get(route('activity.logs'));
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('activity-logs/Index')
        ->has('activity_logs.data')
        ->where('activity_logs.data', function (Collection $data) use ($activity) {
            $targetActivity = $data->first(function ($item) use ($activity) {
                return $item['subject_type'] === $activity::class && $item['subject_id'] == $activity->id;
            });
            return $targetActivity['causer'] !== $this->getCurrentUser()->id;
        }));
});

test('authorized user can filter activity log by event', function () {
    $response = $this->authorizedUser()->get(route('activity.logs', [
        'filters' => json_encode([[
            'id' => 'event',
            'value' => ['created']
        ]])
    ]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('activity-logs/Index')
            ->has(
                'activity_logs.data',
                fn(AssertableInertia $data) => $data->each(
                    fn(AssertableInertia $item) => $item
                        ->where('event', 'created')
                        ->etc()
                )
            )
    );
});

test('authorized user can filter activity log by date range', function () {
    $knownDate = now()->subWeek();
    $this->travelTo($knownDate);
    User::factory()->create();
    $this->travelBack();

    $startDate = $knownDate->copy()->subDay()->format('Y-m-d');
    $endDate = $knownDate->copy()->addDay()->format('Y-m-d');

    $response = $this->authorizedUser()->get(route('activity.logs', [
        'filters' => json_encode([[
            'id' => 'created_at',
            'value' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]])
    ]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('activity-logs/Index')
            ->has('activity_logs.data', 1)
    );
});

test('authorized user can sort activity log by subject id (desc)', function () {
    $response = $this->authorizedUser()->get(route('activity.logs', ['sort' => json_encode([['id' => 'subject_id', 'desc' => true]])]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('activity-logs/Index')
            ->has('activity_logs.data')
            ->where('activity_logs.data', function (Collection $data) {
                $actualSubjectIds = collect($data)->pluck('subject_id')->all();
                $expected = $actualSubjectIds;
                rsort($expected);

                return $actualSubjectIds === $expected;
            })
    );
});

test('authorized user can paginate to second page', function () {
    User::factory()->count(10)->create();

    $response = $this->authorizedUser()->get(route('activity.logs', ['page' => 2]));
    $response->assertInertia(
        fn(AssertableInertia $page) => $page
            ->component('activity-logs/Index')
            ->has('activity_logs.data')
    );
    expect($response->inertiaProps('activity_logs.data'))->not->toBeEmpty();
});
