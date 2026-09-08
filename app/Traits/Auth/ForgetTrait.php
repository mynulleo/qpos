<?php

namespace App\Traits\Auth;

use App\Mail\Admin\ForgetMail;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

trait ForgetTrait
{
    /**
     * Forget password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forget(Request $request)
    {
        // 1. Extract email from either 'email' or 'data.email' or nested payloads
        $email = trim($request->input('email') ?? $request->input('data.email') ?? '');

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email address is required.',
            ], 200);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please provide a valid email address.',
            ], 200);
        }

        $emailLower = strtolower($email);

        // 2. Verify email in accessdb connection's organization_users (case-insensitive)
        $orgUser = OrganizationUser::whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])->first();
        $userName = 'User';

        if (!empty($orgUser)) {
            if ($orgUser->block == 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account has been blocked. Please contact your administrator for assistance.',
                ], 200);
            }
            $email = $orgUser->email; // Use canonical database email
            $userName = $orgUser->full_name ?: ($orgUser->name ?: 'User');
        } else {
            // Check accessdb admins table as fallback
            $adminUser = DB::connection('accessdb')->table('admins')->whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])->first();
            if ($adminUser) {
                if ($adminUser->status === 'deactive' || $adminUser->status === 'inactive') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Your account is inactive. Please contact your administrator.',
                    ], 200);
                }
                $email = $adminUser->email;
                $userName = $adminUser->name ?: 'Admin';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The provided email does not match our records.',
                ], 200);
            }
        }

        // 3. Generate secure token
        $token = Str::random(64);

        // Delete any existing reset tokens for this email in accessdb
        DB::connection('accessdb')->table('password_resets')->whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])->delete();

        // Store token with created_at timestamp
        DB::connection('accessdb')->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Build password reset link
        $resetUrl = url("/password-reset?token={$token}&email=" . urlencode($email));

        $mailData = [
            'name' => $userName,
            'email' => $email,
            'token' => $token,
            'reset_url' => $resetUrl,
            'expire_minutes' => 10,
        ];

        try {
            Mail::to($email)->send(new ForgetMail($mailData));

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset email sent. Please check your inbox and follow the instructions. (Link expires in 10 minutes)',
            ], 200);
        } catch (Throwable $exception) {
            Log::error('Password reset email failed: ' . $exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to send email at this moment. Error: ' . $exception->getMessage(),
            ], 200);
        }
    }
}
