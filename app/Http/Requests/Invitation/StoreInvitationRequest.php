<?php

namespace App\Http\Requests\Invitation;

use App\Models\Invitation;
use App\Models\Role;
use App\Rules\ExistsWithGlobalScope;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'role' => ['required', 'string', 'lowercase', 'max:255', new ExistsWithGlobalScope(Role::class, 'name')],
        ];
    }

    /**
     * Invitation is not expired.
     * 
     * @return null|\App\Models\Invitation
     */
    public function isInvitationNotExpired(): ?Invitation
    {
        $existingInvitation = Invitation::query()
            ->where('email', $this->email)
            ->first();

        return $existingInvitation?->isExpired()
            ? $existingInvitation
            : null;
    }
}
