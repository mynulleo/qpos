<?php

namespace App\Traits\Auth;

use App\Models\OrganizationUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

trait ResetTrait
{
    /**
     * Handle password reset GET and POST requests
     *
     * @param Request $request
     * @return mixed
     */
    public function reset(Request $request)
    {
        // 1. If GET request, render the login_app view with token and email
        if ($request->isMethod('get') || (!$request->ajax() && !$request->expectsJson())) {
            $token = $request->query('token');
            $email = $request->query('email');

            return view('admin.layouts.login_app', compact('token', 'email'));
        }

        // 2. Flexible parameter extraction
        $email = trim($request->input('email') ?? $request->input('data.email') ?? '');
        $token = trim($request->input('token') ?? $request->input('data.token') ?? '');
        $password = $request->input('password') ?? $request->input('data.password');
        $passwordConfirmation = $request->input('password_confirmation') ?? $request->input('data.password_confirmation') ?? $request->input('reset_password') ?? $request->input('data.reset_password');

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email address is required.',
            ], 200);
        }

        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reset token is required.',
            ], 200);
        }

        if (empty($password) || strlen($password) < 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password must be at least 6 characters.',
            ], 200);
        }

        if ($password !== $passwordConfirmation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Oops, the password confirmation does not match. Please try again!',
            ], 200);
        }

        $emailLower = strtolower($email);

        // 3. Verify token and email in accessdb password_resets
        $resetRecord = DB::connection('accessdb')->table('password_resets')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])
            ->where('token', $token)
            ->first();

        if (empty($resetRecord)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid password reset link or email. Please request a new link.',
            ], 200);
        }

        // 4. Check 10-minute expiration
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(10)->isPast()) {
            // Delete expired token
            DB::connection('accessdb')->table('password_resets')
                ->whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])
                ->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'This password reset link has expired (10 minutes limit). Please request a new one.',
            ], 200);
        }

        // 5. Find user in accessdb organization_users
        $user = OrganizationUser::whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])->first();

        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
        } else {
            // Check in accessdb admins table
            $adminCount = DB::connection('accessdb')->table('admins')->whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])->update([
                'password' => Hash::make($password),
            ]);

            if (!$adminCount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account not found with this email address.',
                ], 200);
            }
        }

        // 6. Remove the used token
        DB::connection('accessdb')->table('password_resets')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$emailLower])
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Your password has been changed successfully! You can now login with your new password.',
        ], 200);
    }
}
