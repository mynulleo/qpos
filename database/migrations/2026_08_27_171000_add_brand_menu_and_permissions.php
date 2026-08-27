<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Insert Permissions for Brand
        $parentPerm = DB::table('permissions')->where('name', 'BrandController')->first();
        if (!$parentPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name' => 'BrandController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $parentPermId = $parentPerm->id;
        }

        $childPermissions = [
            ['name' => 'index', 'route' => 'brand.index', 'parent_id' => $parentPermId],
            ['name' => 'create', 'route' => 'brand.create', 'parent_id' => $parentPermId],
            ['name' => 'store', 'route' => 'brand.store', 'parent_id' => $parentPermId],
            ['name' => 'show', 'route' => 'brand.show', 'parent_id' => $parentPermId],
            ['name' => 'edit', 'route' => 'brand.edit', 'parent_id' => $parentPermId],
            ['name' => 'update', 'route' => 'brand.update', 'parent_id' => $parentPermId],
            ['name' => 'destroy', 'route' => 'brand.destroy', 'parent_id' => $parentPermId],
        ];

        $allPermIds = [$parentPermId];

        foreach ($childPermissions as $cp) {
            $existing = DB::table('permissions')->where('route', $cp['route'])->first();
            if (!$existing) {
                $cid = DB::table('permissions')->insertGetId($cp);
                $allPermIds[] = $cid;
            } else {
                $allPermIds[] = $existing->id;
            }
        }

        // 2. Assign all permissions to all roles in role_permissions
        $roleIds = DB::table('roles')->pluck('id');
        foreach ($roleIds as $rId) {
            foreach ($allPermIds as $pId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $rId)
                    ->where('permission_id', $pId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $rId,
                        'permission_id' => $pId,
                    ]);
                }
            }
        }

        // 3. Add to menus table (under Master Setup, parent_id: 3)
        $menuExists = DB::table('menus')->where('route_name', 'brand.index')->exists();
        if (!$menuExists) {
            DB::table('menus')->insert([
                'menu_name' => 'Brand',
                'module_name' => '\\App\\Models\\Brand',
                'icon' => "<i class='fas fa-copyright'></i>",
                'route_name' => 'brand.index',
                'params' => null,
                'parent_id' => 3,
                'sorting' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menus')->where('route_name', 'brand.index')->delete();
        $perm = DB::table('permissions')->where('name', 'BrandController')->first();
        if ($perm) {
            $childIds = DB::table('permissions')->where('parent_id', $perm->id)->pluck('id')->toArray();
            $allIds = array_merge([$perm->id], $childIds);
            DB::table('role_permissions')->whereIn('permission_id', $allIds)->delete();
            DB::table('permissions')->whereIn('id', $allIds)->delete();
        }
    }
};
