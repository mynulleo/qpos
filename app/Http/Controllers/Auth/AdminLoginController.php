<?php

/**
 * Dev: @Quill Information Technology
 */

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Traits\Auth\LoginTrait;
use App\Traits\Auth\ResetTrait;
use App\Models\OrganizationUser;
use App\Traits\Auth\ForgetTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

class AdminLoginController extends Controller
{
    use ForgetTrait, LoginTrait, ResetTrait;

    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout', 'loginCheck');
    }

    /**
     * Process the user login
     *
     * @return response
     */
    public function login(Request $request)
    {
        if (app()->environment('production')) {
            $rules['g-recaptcha-response'] = 'required';
        }

        if ($request->isMethod('post')) {
            if ($this->validateCheck($request)) {
                $email = $request->email;
                $arr = [
                    'email' => $email,
                    'password' => $request->password,
                    'status' => 'active',
                ];

                $organization = OrganizationUser::with('organization:id,organization_name,block,expired_date,subscription_fee,status')->where('email', $email)->first();

                if (!empty($organization)) {
                    if ($organization->block == 0) {

                        if (Auth::guard('admin')->attempt($arr, $request->remember)) {

                            Session::forget($email);
                            Artisan::call('cache:clear');

                            // Store the login details.
                            $userId = Auth::guard('admin')->user()->id;
                            $this->storeLoginDetails($userId, 'admin');

                            $org = $organization->organization ?? null;
                            $expiredDate = $org ? $org->expired_date : (Auth::guard('admin')->user()->residence->expired_date ?? null);
                            $isExpired = false;
                            if (!empty($expiredDate)) {
                                try {
                                    $isExpired = \Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($expiredDate)->startOfDay());
                                } catch (\Exception $e) {
                                    $isExpired = false;
                                }
                            }

                            return response([
                                // 'message' => 'Logged in successfully',
                                'user' => Auth::guard('admin')->user(),
                                'role' => Auth::guard('admin')->user()->role->name ?? '',
                                'organization_id' => Auth::guard('admin')->user()->organization_id ?? ($organization->organization_id ?? ''),
                                'organization_name' => $org ? $org->organization_name : 'My Organization',
                                'expired_date' => $expiredDate ?? '',
                                'subscription_fee' => $org ? ($org->subscription_fee ?? 1000) : 1000,
                                'is_expired' => $isExpired,
                                'redirect_url' => $request->session()->pull('url.intended', route('dashboard.index')),
                            ], 200);
                        } else {
                            $attempt = $this->attemptAdmin($organization->id, $email);
                            if ($attempt <= 3 && $attempt != 0) {
                                $message = "You have {$attempt} login attempts remaining";
                            } elseif ($attempt == 0) {
                                $message = 'Your account has been blocked. Please contact your administrator for assistance';
                            } else {
                                $message = 'The login information provided is incorrect';
                            }

                            return response([
                                'message' => $message,
                            ], 201);
                        }
                    } else {
                        Session::forget($email);

                        return response([
                            'message' => 'Your account has been blocked. Please contact your administrator for assistance',
                        ], 201);
                    }
                } else {
                    return response([
                        'message' => 'The provided email does not match our records',
                    ], 201);
                }
            }
        } elseif ($request->isMethod('get')) {
            return view('admin.layouts.login_app', ['token' => $request->query('token'), 'email' => $request->query('email')]);
        } else {
            return view('admin.layouts.login_app', ['token' => $request->query('token'), 'email' => $request->query('email')]);
        }
    }

    /**
     * Attempt to login as admin
     *
     * @param [type] $id
     * @param [type] $email
     * @return response
     */
    private function attemptAdmin($id, $email)
    {
        $count = Session::get($email) ?? 0;
        $inc = $count + 1;
        Session::put($email, $inc);

        $attempt = 6 - $inc;
        if ($attempt == 0) {
            Session::forget($email);
            Admin::where('id', $id)->update(['block' => 1]);
        }

        return $attempt;
    }

    /**
     * Logout the authenticated user
     *
     * @return response
     */
    public function logout(Request $request = null)
    {
        try {
            if (Auth::guard('admin')->check()) {
                Auth::guard('admin')->logout();
            }
            if (Auth::check()) {
                Auth::logout();
            }
            if ($request && $request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        } catch (\Exception $e) {}

        if ($request && $request->expectsJson()) {
            return response([
                'success' => true,
                'message' => 'You have been successfully logged out',
                'redirect_url' => url('/'),
            ], 200);
        }

        return redirect('/');
    }

    /**
     * Check auth
     *
     * @return response
     */
    public function loginCheck()
    {
        if (Auth::guard('admin')->user()) {
            return response([
                'message' => 'Login Successfully',
                'user' => Auth::guard('admin')->user(),
                'role' => Auth::guard('admin')->user()->role->name ?? '',
            ], 200);
        }

        return response(['message' => 'Unauthorized'], 201);
    }

    /**
     * Validation on login request
     *
     * @param [type] $request
     * @return bool
     */
    public function validateCheck($request)
    {
        return $request->validate([
            'email' => 'required|email:rfc,|max:100',
            'password' => 'required|min:3',
            'g-recaptcha-response' => 'captcha'
        ], [], [
            'g-recaptcha-response' => 'captcha'
        ]);
    }
}
