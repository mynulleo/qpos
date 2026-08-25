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
        $existingPerm = DB::table('permissions')->where('route', 'wastage.index')->first();
        if (!$existingPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name' => 'Wastage',
                'route' => 'wastage.index',
                'parent_id' => null,
            ]);

            $childPermissions = [
                ['name' => 'Create', 'route' => 'wastage.create', 'parent_id' => $parentPermId],
                ['name' => 'View', 'route' => 'wastage.show', 'parent_id' => $parentPermId],
                ['name' => 'Edit', 'route' => 'wastage.edit', 'parent_id' => $parentPermId],
                ['name' => 'Delete', 'route' => 'wastage.destroy', 'parent_id' => $parentPermId],
                ['name' => 'Approve', 'route' => 'wastage.approved', 'parent_id' => $parentPermId],
                ['name' => 'Cancel Approval', 'route' => 'wastage.approvalcancel', 'parent_id' => $parentPermId],
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
        }

        // 3. Add to menus table (as a main menu or under inventory/items)
        $menuExists = DB::table('menus')->where('route_name', 'wastage.index')->exists();
        if (!$menuExists) {
            $parentMenu = DB::table('menus')->where('route_name', 'purchase.index')->orWhere('menu_name', 'Purchase')->first();
            $parentId = $parentMenu ? $parentMenu->parent_id : null;

            DB::table('menus')->insert([
                'menu_name' => 'Wastage',
                'icon' => "<i class='fas fa-dumpster'></i>",
                'route_name' => 'wastage.index',
                'params' => null,
                'parent_id' => $parentId,
                'sorting' => 96,
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
        DB::table('menus')->where('route_name', 'wastage.index')->delete();
        $perm = DB::table('permissions')->where('route', 'wastage.index')->first();
        if ($perm) {
            $childIds = DB::table('permissions')->where('parent_id', $perm->id)->pluck('id')->toArray();
            $allIds = array_merge([$perm->id], $childIds);
            DB::table('role_permissions')->whereIn('permission_id', $allIds)->delete();
            DB::table('permissions')->whereIn('id', $allIds)->delete();
        }
    }
};
