<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class BulkBaseUserRequest extends FormRequest
{
    protected array $parsedIds = [];

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->parsedIds = explode(',', $this->route('id'));
        $this->merge([
            '_ids' => $this->parsedIds
        ]);
    }

    public function ids()
    {
        return $this->parsedIds;
    }
}
