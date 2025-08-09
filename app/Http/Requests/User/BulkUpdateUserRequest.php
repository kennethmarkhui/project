<?php

namespace App\Http\Requests\User;

use App\Models\Role;
use App\Models\User;
use App\Rules\ExistsWithGlobalScope;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BulkUpdateUserRequest extends BulkBaseUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'sometimes',
                'string',
                'lowercase',
                'max:255',
                new ExistsWithGlobalScope(Role::class, 'name'),
                Rule::requiredIf(!$this->filled('status')),
                Rule::excludeIf($this->filled('status'))
            ],
            'status' => [
                'sometimes',
                'string',
                'lowercase',
                'max:255',
                Rule::in(User::STATUS),
                Rule::requiredIf(!$this->filled('role')),
                Rule::excludeIf($this->filled('role'))
            ],
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if (!$this->hasAny(['role', 'status'])) {
            throw ValidationException::withMessages([
                'update' => 'role or status must be provided for bulk update.'
            ]);
        };
    }
}
