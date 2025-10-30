<?php

namespace App\Http\Responses;

use App\Enums\UserStatusType;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request)
    {
        $request->user()->status = UserStatusType::APPROVED->value;
        $request->user()->save();

        return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
    }
}
