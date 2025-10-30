<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;

class EmailVerificationNotificationSentResponse implements EmailVerificationNotificationSentResponseContract
{
    public function toResponse($request)
    {
        return back()->with('success', 'Verification link sent');
    }
}
