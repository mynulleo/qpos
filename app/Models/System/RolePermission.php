<?php

/**
 * @Quill Information Technology
 */

namespace App\Models\System;

use App\Models\Base\BaseModel;
use Illuminate\Support\Facades\Auth;

class RolePermission extends BaseModel
{
    protected $table = 'role_permissions';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $logName = 'Role Permission';

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public static function permissions()
    {
        try {
            return RolePermission::select('permission_id', 'role_id')
                ->with(['permission' => function ($q) {
                    $q->select('id', 'name', 'route', 'parent_id');
                }])->get()->groupBy('role_id');
        } catch (\Exception $e) {
            return collect();
        }
    }

    public static function permissionProcess($obj)
    {
        $routes = [];
        $user = Auth::guard('admin')->user() ?? Auth::user();
        if (!$user || !isset($user->role_id)) {
            return $routes;
        }

        $rolePermissions = $obj->get($user->role_id);
        if ($rolePermissions) {
            foreach ($rolePermissions->toArray() as $value) {
                if (! empty($value['permission']['parent_id']) && ! empty($value['permission']['route'])) {
                    $routes[] = $value['permission']['route'];
                }
            }
        }

        return array_values(array_unique($routes));
    }
}
