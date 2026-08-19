<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\Organization;
use App\Models\Residence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        if ($user) {
            $expiredDate = null;
            if (!empty($user->organization_id)) {
                try {
                    $org = Organization::select('id', 'expired_date', 'status', 'block')->find($user->organization_id);
                    if ($org) {
                        $expiredDate = $org->expired_date;
                    }
                } catch (\Exception $e) {}
            }

            if (!$expiredDate && isset($user->residence)) {
                $expiredDate = $user->residence->expired_date ?? null;
            }

            if (!empty($expiredDate)) {
                try {
                    $isExpired = Carbon::today()->gt(Carbon::parse($expiredDate)->startOfDay());
                } catch (\Exception $e) {
                    $isExpired = false;
                }

                if ($isExpired) {
                    // Allowed essential routes for initial page load, payment renewal, and logout
                    $allowedRoutes = [
                        'admin/initialize-systems',
                        'initialize-systems',
                        'admin/subscription/initiate-payment',
                        'subscription/initiate-payment',
                        'subscription/payment/*',
                        'admin/logout',
                        'logout',
                        'admin/qlogout',
                        'qlogin',
                        'admin/loginCheck',
                        'loginCheck',
                    ];

                    $isAllowed = false;
                    foreach ($allowedRoutes as $route) {
                        if ($request->is($route) || $request->is("*/{$route}")) {
                            $isAllowed = true;
                            break;
                        }
                    }

                    // HTML views (GET) can load so Vue mounts and displays the software lock screen
                    if ($request->format() == 'html' && $request->isMethod('get')) {
                        $isAllowed = true;
                    }

                    if (!$isAllowed) {
                        return response()->json([
                            'success' => false,
                            'is_expired' => true,
                            'expired_date' => $expiredDate,
                            'message' => 'Your software subscription has expired. Access is locked.',
                            'error' => 'SUBSCRIPTION_EXPIRED'
                        ], 403);
                    }
                }
            }
        }

        return $next($request);
    }
}
