<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserRequest extends BaseUserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        if ($this->isMultiple()) {
            return [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'lowercase', 'max:255'],
                'role' => ['sometimes', 'string', 'lowercase', 'max:255', Rule::in(User::ROLES)],
                'status' => ['sometimes', 'string', 'lowercase', 'max:255', Rule::in(User::STATUS)],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                ],
                'role' => ['required', 'string', 'lowercase', 'max:255', Rule::in(User::ROLES)],
                'status' => ['required', 'string', 'lowercase', 'max:255', Rule::in(User::STATUS)],
            ];
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if ($this->isMultiple()) {
            if (!$this->hasAny(['role', 'status'])) {
                throw ValidationException::withMessages([
                    'update' => 'role or status must be provided for bulk update.'
                ]);
            };
        }
    }
}
