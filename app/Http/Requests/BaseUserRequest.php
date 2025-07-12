<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseUserRequest extends FormRequest
{
    protected array $parsedIds = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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

    public function isMultiple()
    {
        return count($this->parsedIds) > 1;
    }
}
