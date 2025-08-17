<?php

namespace App\Traits\Http\Resources;

use Illuminate\Http\Request;

trait Authorizable
{
    public function withPermissions(Request $request, array $data = [])
    {
        return array_merge($data, [
            'can' => $this->getPermissions($request),
        ]);
    }

    protected function getPermissions(Request $request)
    {
        $defaults = [
            'read' => $request->user()?->can('view', $this->resource),
            'update' => $request->user()?->can('update', $this->resource),
            'delete' => $request->user()?->can('delete', $this->resource),
        ];

        if (method_exists($this, 'additionalPermissions')) {
            return array_merge($defaults, $this->additionalPermissions($request));
        }

        return $defaults;
    }
}
