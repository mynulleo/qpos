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
        // 1. Insert Permissions
        $parentPermId = DB::table('permissions')->insertGetId([
            'name' => 'Warranty Claim',
            'route' => 'warrantyClaim.index',
            'parent_id' => null,
        ]);

        $childPermissions = [
            ['name' => 'Create', 'route' => 'warrantyClaim.create', 'parent_id' => $parentPermId],
            ['name' => 'View', 'route' => 'warrantyClaim.show', 'parent_id' => $parentPermId],
            ['name' => 'Edit', 'route' => 'warrantyClaim.edit', 'parent_id' => $parentPermId],
            ['name' => 'Delete', 'route' => 'warrantyClaim.destroy', 'parent_id' => $parentPermId],
            ['name' => 'Check Serial', 'route' => 'warrantyClaim.checkSerial', 'parent_id' => $parentPermId],
            ['name' => 'Add Tracking Log', 'route' => 'warrantyClaim.addLog', 'parent_id' => $parentPermId],
        ];

        $allPermIds = [$parentPermId];

        foreach ($childPermissions as $cp) {
            $cid = DB::table('permissions')->insertGetId($cp);
            $allPermIds[] = $cid;
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

        // 3. Add to menus table (as a sub-menu under POS menu or as a main menu item)
        $posMenu = DB::table('menus')->where('route_name', 'pos.index')->orWhere('menu_name', 'POS')->first();
        $parentId = $posMenu ? ($posMenu->parent_id ?: $posMenu->id) : null;

        $menuExists = DB::table('menus')->where('route_name', 'warrantyClaim.index')->exists();
        if (!$menuExists) {
            DB::table('menus')->insert([
                'menu_name' => 'Warranty Claim',
                'icon' => "<i class='fas fa-shield-alt'></i>",
                'route_name' => 'warrantyClaim.index',
                'params' => null,
                'parent_id' => $parentId,
                'sorting' => 95,
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
        DB::table('menus')->where('route_name', 'warrantyClaim.index')->delete();
        $perm = DB::table('permissions')->where('route', 'warrantyClaim.index')->first();
        if ($perm) {
            $childIds = DB::table('permissions')->where('parent_id', $perm->id)->pluck('id')->toArray();
            $allIds = array_merge([$perm->id], $childIds);
            DB::table('role_permissions')->whereIn('permission_id', $allIds)->delete();
            DB::table('permissions')->whereIn('id', $allIds)->delete();
        }
    }
};
