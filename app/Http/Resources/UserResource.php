<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->getRoleNames()->first(),
            'status' => $this->status,
            'deleted_at' => $this->deleted_at,
            'can' => [
                'read' => Gate::allows('view', $this->resource),
                'update' => Gate::allows('update', $this->resource),
                'delete' => Gate::allows('delete', $this->resource),
                'restore' => Gate::allows('restore', $this->resource),
                'force_delete' => Gate::allows('forceDelete', $this->resource),
            ],
        ];
    }
}
