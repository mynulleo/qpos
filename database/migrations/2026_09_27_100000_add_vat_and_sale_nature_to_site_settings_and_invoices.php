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
        // 1. Add default_vat and sale_nature to site_settings table
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'default_vat')) {
                    $table->decimal('default_vat', 8, 2)->default(0.00)->nullable()->after('default_currency_id')->comment('Default VAT % for POS sales');
                }
                if (!Schema::hasColumn('site_settings', 'sale_nature')) {
                    $table->string('sale_nature', 50)->default('both')->nullable()->after('shop_type')->comment('retail, wholesale, both');
                }
            });
        }

        // 2. Add vat_percent to invoices table if not exists
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'vat_percent')) {
                    $table->decimal('vat_percent', 8, 2)->default(0.00)->nullable()->after('vat')->comment('VAT percentage applied on invoice');
                }
            });
        }

        // 3. Register Menu entry under Reports
        $reportParent = DB::table('menus')->where('route_name', 'report.sales')->value('parent_id')
            ?? DB::table('menus')->where('menu_name', 'Reports')->value('id')
            ?? 94;

        if ($reportParent) {
            $existingMenu = DB::table('menus')->where('route_name', 'report.vat')->first();
            if (!$existingMenu) {
                $maxSorting = DB::table('menus')->where('parent_id', $reportParent)->max('sorting') ?? 15;
                DB::table('menus')->insert([
                    'parent_id'     => $reportParent,
                    'menu_name'     => 'VAT / Tax Report',
                    'icon'          => "<i class='fas fa-file-invoice-dollar'></i>",
                    'route_name'    => 'report.vat',
                    'params'        => null,
                    'sorting'       => $maxSorting + 1,
                    'show_dasboard' => 0,
                    'show_profile'  => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // 4. Register Permissions & Assign to all roles
        $existingPerm = DB::table('permissions')->where('route', 'report.vat')->first();
        $permId = null;

        if (!$existingPerm) {
            $parentPermId = DB::table('permissions')->where('route', 'report.sales')->value('parent_id')
                ?? DB::table('permissions')->where('name', 'like', '%report%')->value('id');

            $permId = DB::table('permissions')->insertGetId([
                'name'      => 'VAT Report',
                'route'     => 'report.vat',
                'parent_id' => $parentPermId,
            ]);
        } else {
            $permId = $existingPerm->id;
        }

        if ($permId) {
            $roles = DB::table('roles')->pluck('id');
            foreach ($roles as $roleId) {
                $hasRolePerm = DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permId)
                    ->exists();

                if (!$hasRolePerm) {
                    DB::table('role_permissions')->insert([
                        'role_id'       => $roleId,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'default_vat')) {
                    $table->dropColumn('default_vat');
                }
                if (Schema::hasColumn('site_settings', 'sale_nature')) {
                    $table->dropColumn('sale_nature');
                }
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'vat_percent')) {
                    $table->dropColumn('vat_percent');
                }
            });
        }

        DB::table('menus')->where('route_name', 'report.vat')->delete();
        DB::table('permissions')->where('route', 'report.vat')->delete();
    }
};
