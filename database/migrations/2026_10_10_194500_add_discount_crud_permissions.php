<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Parent Controller Permission
        $parentPerm = DB::table('permissions')->where('name', 'DiscountController')->first();
        if (!$parentPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name'      => 'DiscountController',
                'route'     => null,
                'parent_id' => null,
            ]);
        } else {
            $parentPermId = $parentPerm->id;
        }

        // 2. Child Permissions
        $childPermissions = [
            ['name' => 'index',        'route' => 'discount.index',        'parent_id' => $parentPermId],
            ['name' => 'create',       'route' => 'discount.create',       'parent_id' => $parentPermId],
            ['name' => 'store',        'route' => 'discount.store',        'parent_id' => $parentPermId],
            ['name' => 'show',         'route' => 'discount.show',         'parent_id' => $parentPermId],
            ['name' => 'edit',         'route' => 'discount.edit',         'parent_id' => $parentPermId],
            ['name' => 'update',       'route' => 'discount.update',       'parent_id' => $parentPermId],
            ['name' => 'destroy',      'route' => 'discount.destroy',      'parent_id' => $parentPermId],
            ['name' => 'toggleStatus', 'route' => 'discount.toggleStatus', 'parent_id' => $parentPermId],
        ];

        $allPermIds = [$parentPermId];

        foreach ($childPermissions as $cp) {
            $existing = DB::table('permissions')->where('route', $cp['route'])->first();
            if (!$existing) {
                $cid = DB::table('permissions')->insertGetId($cp);
                $allPermIds[] = $cid;
            } else {
                DB::table('permissions')->where('id', $existing->id)->update([
                    'parent_id' => $parentPermId,
                    'name'      => $cp['name'],
                ]);
                $allPermIds[] = $existing->id;
            }
        }

        // 3. Assign permissions to all existing roles
        $roleIds = DB::table('roles')->pluck('id');
        foreach ($roleIds as $rId) {
            foreach ($allPermIds as $pId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $rId)
                    ->where('permission_id', $pId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'role_id'       => $rId,
                        'permission_id' => $pId,
                    ]);
                }
            }
        }

        Cache::forget('role_pemission_cache');
        Cache::forget('side_menu_cache');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $parentPerm = DB::table('permissions')->where('name', 'DiscountController')->first();
        if ($parentPerm) {
            $childIds = DB::table('permissions')->where('parent_id', $parentPerm->id)->pluck('id');
            DB::table('role_permissions')->whereIn('permission_id', $childIds)->delete();
            DB::table('role_permissions')->where('permission_id', $parentPerm->id)->delete();
            DB::table('permissions')->where('parent_id', $parentPerm->id)->delete();
            DB::table('permissions')->where('id', $parentPerm->id)->delete();
        }

        Cache::forget('role_pemission_cache');
        Cache::forget('side_menu_cache');
    }
};
