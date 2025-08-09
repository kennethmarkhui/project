<?php

namespace App\Traits;

use App\Enums\RoleType;
use App\Models\User;

trait FeatureTestTrait
{
    protected ?User $currentUser = null;

    /**
     * Create an unauthorized user.
     */
    public function unauthorizedUser(): self
    {
        $this->currentUser = User::factory()->create();
        $this->currentUser->syncRoles(RoleType::USER->value);
        $this->actingAs($this->currentUser);

        return $this;
    }

    /**
     * Create an authorized user.
     */
    public function authorizedUser(bool $super = false): self
    {
        $this->currentUser = User::factory()->create(['name' => 'authorized']);
        $this->currentUser->syncRoles(RoleType::{$super ? 'SUPER_ADMIN' : 'ADMIN'}->value);
        $this->actingAs($this->currentUser);

        return $this;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}
