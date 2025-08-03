<?php

namespace App\Http\Requests\User;

class BulkDestroyUserRequest extends BulkBaseUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
