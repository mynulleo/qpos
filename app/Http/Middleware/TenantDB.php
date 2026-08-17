<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class TenantDB
{
    // app/Http/Middleware/TenantDB.php
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        if ($user) {
            $organization = null;
            if (!empty($user->organization_id)) {
                $organization = \App\Models\Organization::find($user->organization_id);
            }
            if (!$organization && isset($user->organization)) {
                $organization = $user->organization;
            }

            if ($organization && !empty($organization->db_name)) {
                Config::set('database.connections.tenant', [
                    'driver' => 'mysql',
                    'host' => !empty($organization->db_host) ? $organization->db_host : env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => $organization->db_name,
                    'username' => $organization->db_user ?? env('DB_USERNAME', 'root'),
                    'password' => $organization->db_password ?? env('DB_PASSWORD', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => true,
                    'engine' => 'InnoDB',
                ]);
                DB::purge('tenant');
                DB::reconnect('tenant');
                DB::setDefaultConnection('tenant');
            }
        }

        return $next($request);
    }
}
