<x-mail::message>
# You're Invited!

You have been invited to join {{ config('app.name') }}.

<x-mail::button :url="$url">
Accept Invitation
</x-mail::button>

This invitation link will expire in 1 day. If you did not expect to receive this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
