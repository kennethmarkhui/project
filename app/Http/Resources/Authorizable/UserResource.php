<?php

namespace App\Http\Resources\Authorizable;

use App\Http\Resources\UserResource as BaseUserResource;
use App\Traits\Http\Resources\Authorizable;
use Illuminate\Http\Request;

class UserResource extends BaseUserResource
{
    use Authorizable;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->withPermissions($request, parent::toArray($request));
    }

    protected function additionalPermissions(Request $request): array
    {
        return [
            'restore' => $request->user()?->can('restore', $this->resource),
            'force_delete' => $request->user()?->can('forceDelete', $this->resource),
        ];
    }
}
