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
        $allPermIds = [];

        // 1. Warehouse Permissions
        $warehouseParent = DB::table('permissions')->where('name', 'WarehouseController')->first();
        if (!$warehouseParent) {
            $warehouseParentId = DB::table('permissions')->insertGetId([
                'name' => 'WarehouseController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $warehouseParentId = $warehouseParent->id;
        }
        $allPermIds[] = $warehouseParentId;

        $warehouseChildren = [
            ['name' => 'index', 'route' => 'warehouse.index', 'parent_id' => $warehouseParentId],
            ['name' => 'create', 'route' => 'warehouse.create', 'parent_id' => $warehouseParentId],
            ['name' => 'store', 'route' => 'warehouse.store', 'parent_id' => $warehouseParentId],
            ['name' => 'show', 'route' => 'warehouse.show', 'parent_id' => $warehouseParentId],
            ['name' => 'edit', 'route' => 'warehouse.edit', 'parent_id' => $warehouseParentId],
            ['name' => 'update', 'route' => 'warehouse.update', 'parent_id' => $warehouseParentId],
            ['name' => 'destroy', 'route' => 'warehouse.destroy', 'parent_id' => $warehouseParentId],
        ];

        foreach ($warehouseChildren as $cp) {
            $existing = DB::table('permissions')->where('route', $cp['route'])->first();
            if (!$existing) {
                $cid = DB::table('permissions')->insertGetId($cp);
                $allPermIds[] = $cid;
            } else {
                $allPermIds[] = $existing->id;
            }
        }

        // 2. GRN Permissions
        $grnParent = DB::table('permissions')->where('name', 'GrnController')->first();
        if (!$grnParent) {
            $grnParentId = DB::table('permissions')->insertGetId([
                'name' => 'GrnController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $grnParentId = $grnParent->id;
        }
        $allPermIds[] = $grnParentId;

        $grnChildren = [
            ['name' => 'index', 'route' => 'grn.index', 'parent_id' => $grnParentId],
            ['name' => 'create', 'route' => 'grn.create', 'parent_id' => $grnParentId],
            ['name' => 'store', 'route' => 'grn.store', 'parent_id' => $grnParentId],
            ['name' => 'show', 'route' => 'grn.show', 'parent_id' => $grnParentId],
            ['name' => 'edit', 'route' => 'grn.edit', 'parent_id' => $grnParentId],
            ['name' => 'update', 'route' => 'grn.update', 'parent_id' => $grnParentId],
            ['name' => 'destroy', 'route' => 'grn.destroy', 'parent_id' => $grnParentId],
            ['name' => 'pendingPurchases', 'route' => 'grn.pendingPurchases', 'parent_id' => $grnParentId],
            ['name' => 'purchaseItems', 'route' => 'grn.purchaseItems', 'parent_id' => $grnParentId],
        ];

        foreach ($grnChildren as $cp) {
            $existing = DB::table('permissions')->where('route', $cp['route'])->first();
            if (!$existing) {
                $cid = DB::table('permissions')->insertGetId($cp);
                $allPermIds[] = $cid;
            } else {
                $allPermIds[] = $existing->id;
            }
        }

        // 3. Assign permissions to all roles
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

        // 4. Menus
        // Warehouse Menu under Master Setup (parent_id: 3)
        $whMenuExists = DB::table('menus')->where('route_name', 'warehouse.index')->exists();
        if (!$whMenuExists) {
            DB::table('menus')->insert([
                'menu_name' => 'Warehouse',
                'module_name' => '\\App\\Models\\Warehouse',
                'icon' => "<i class='fas fa-warehouse'></i>",
                'route_name' => 'warehouse.index',
                'params' => null,
                'parent_id' => 3,
                'sorting' => 12,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // GRN Menu (top level right next to Purchase)
        $grnMenuExists = DB::table('menus')->where('route_name', 'grn.index')->exists();
        if (!$grnMenuExists) {
            DB::table('menus')->insert([
                'menu_name' => 'Goods Receive (GRN)',
                'module_name' => '\\App\\Models\\Grn',
                'icon' => "<i class='fas fa-clipboard-check'></i>",
                'route_name' => 'grn.index',
                'params' => null,
                'parent_id' => null,
                'sorting' => 13,
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
        DB::table('menus')->whereIn('route_name', ['warehouse.index', 'grn.index'])->delete();

        $controllers = ['WarehouseController', 'GrnController'];
        foreach ($controllers as $ctrl) {
            $perm = DB::table('permissions')->where('name', $ctrl)->first();
            if ($perm) {
                $childIds = DB::table('permissions')->where('parent_id', $perm->id)->pluck('id')->toArray();
                $allIds = array_merge([$perm->id], $childIds);
                DB::table('role_permissions')->whereIn('permission_id', $allIds)->delete();
                DB::table('permissions')->whereIn('id', $allIds)->delete();
            }
        }
    }
};
