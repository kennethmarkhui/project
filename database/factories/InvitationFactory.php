<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'role_id' => Role::factory(),
            'invited_by' => User::factory(),
            'expires_at' => Carbon::now()->addDay(),
            'accepted_at' => null,
        ];
    }

    /**
     * Indicate that the invitation is expired
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => Carbon::now()->subDay(),
        ]);
    }

    /**
     * Indicate that the invitation is accepted
     */
    public function accepted(): static
    {
        return $this->state(fn(array $attributes) => [
            'accepted_at' => Carbon::now()->subHour(),
        ]);
    }
}
