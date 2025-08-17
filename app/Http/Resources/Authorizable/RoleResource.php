<?php

namespace App\Http\Resources\Authorizable;

use App\Http\Resources\RoleResource as BaseRoleResource;
use App\Traits\Http\Resources\Authorizable;
use Illuminate\Http\Request;

class RoleResource extends BaseRoleResource
{
    use Authorizable;

    /**
     * Transform the resource into an array with authorization.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->withPermissions($request, parent::toArray($request));
    }
}
