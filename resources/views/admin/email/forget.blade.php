@component('mail::message')
# Dear {{ $data['name'] }},

You have requested to reset your password. Please click the button below to set a new password:

@component('mail::button', ['url' => $data['reset_url']])
Reset Password
@endcomponent

If the button above does not work, copy and paste the following URL into your browser:
[{{ $data['reset_url'] }}]({{ $data['reset_url'] }})

> **Notice:** This password reset link is valid for **10 minutes** only. After 10 minutes, this link will expire and will no longer work.

If you did not request a password reset, please ignore this email. No changes will be made to your account.

Best regards,  
**{{ config('app.name') }}**
@endcomponent
