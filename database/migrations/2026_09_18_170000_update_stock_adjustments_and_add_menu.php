<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Update stock_adjustments table
        if (Schema::hasTable('stock_adjustments')) {
            Schema::table('stock_adjustments', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_adjustments', 'total_items')) {
                    $table->integer('total_items')->default(0)->after('conducted_by');
                }
                if (!Schema::hasColumn('stock_adjustments', 'total_adjusted_qty')) {
                    $table->decimal('total_adjusted_qty', 12, 2)->default(0)->after('total_items');
                }
                if (!Schema::hasColumn('stock_adjustments', 'total_amount')) {
                    $table->decimal('total_amount', 14, 2)->default(0)->after('total_adjusted_qty');
                }
                if (!Schema::hasColumn('stock_adjustments', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id')->index();
                }
            });
        }

        // 2. Update stock_adjustment_details table
        if (Schema::hasTable('stock_adjustment_details')) {
            Schema::table('stock_adjustment_details', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_adjustment_details', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->after('item_id')->index();
                }
                if (!Schema::hasColumn('stock_adjustment_details', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->after('color_id')->index();
                }
                if (!Schema::hasColumn('stock_adjustment_details', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('size_id')->index();
                }
            });
        }

        // 3. Insert Permissions for StockAdjustmentController
        $parentPerm = DB::table('permissions')->where('name', 'StockAdjustmentController')->first();
        if (!$parentPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name' => 'StockAdjustmentController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $parentPermId = $parentPerm->id;
        }

        $childPermissions = [
            ['name' => 'index', 'route' => 'stockAdjustment.index', 'parent_id' => $parentPermId],
            ['name' => 'create', 'route' => 'stockAdjustment.create', 'parent_id' => $parentPermId],
            ['name' => 'store', 'route' => 'stockAdjustment.store', 'parent_id' => $parentPermId],
            ['name' => 'show', 'route' => 'stockAdjustment.show', 'parent_id' => $parentPermId],
            ['name' => 'edit', 'route' => 'stockAdjustment.edit', 'parent_id' => $parentPermId],
            ['name' => 'update', 'route' => 'stockAdjustment.update', 'parent_id' => $parentPermId],
            ['name' => 'destroy', 'route' => 'stockAdjustment.destroy', 'parent_id' => $parentPermId],
        ];

        foreach ($childPermissions as $perm) {
            $exists = DB::table('permissions')->where('route', $perm['route'])->first();
            if (!$exists) {
                $permId = DB::table('permissions')->insertGetId($perm);
            } else {
                $permId = $exists->id;
            }

            // Assign to all existing roles
            $roles = DB::table('roles')->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }

        // 4. Seed Menu item under Stock & Inventory (parent_id: 141)
        $existingMenu = DB::table('menus')->where('route_name', 'stockAdjustment.index')->first();
        if (!$existingMenu) {
            $parentStock = DB::table('menus')->where('id', 141)->first();
            $parentId = $parentStock ? 141 : null;

            DB::table('menus')->insert([
                'menu_name' => 'Stock Adjustment',
                'route_name' => 'stockAdjustment.index',
                'parent_id' => $parentId,
                'icon' => '<i class="fas fa-sliders-h"></i>',
                'sorting' => 6,
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
    }
};
