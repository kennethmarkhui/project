<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatusType;
use App\Models\Role;
use App\Rules\ExistsWithGlobalScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
            ],
            'role' => ['required', 'string', 'lowercase', 'max:255', new ExistsWithGlobalScope(Role::class, 'name')],
            'status' => ['required', 'string', 'lowercase', 'max:255', Rule::enum(UserStatusType::class)],
        ];
    }
}
