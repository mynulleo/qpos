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
        // 1. Invoices Table Enhancements (Delivery destination, Vehicle info, VAT %)
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'vat_percent')) {
                    $table->decimal('vat_percent', 8, 2)->default(0.00)->nullable()->comment('VAT % applied on invoice');
                }
                if (!Schema::hasColumn('invoices', 'delivery_address')) {
                    $table->text('delivery_address')->nullable()->comment('Delivery destination for Mushak 6.3');
                }
                if (!Schema::hasColumn('invoices', 'vehicle_info')) {
                    $table->string('vehicle_info', 255)->nullable()->comment('Vehicle nature and number for Mushak 6.3');
                }
            });
        }

        // 2. Site Settings Table (Default VAT %, Sale Nature, Printer Presets)
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'default_vat')) {
                    $table->decimal('default_vat', 8, 2)->default(0.00)->nullable()->comment('Default VAT % for POS sales');
                }
                if (!Schema::hasColumn('site_settings', 'sale_nature')) {
                    $table->string('sale_nature', 50)->default('both')->nullable()->comment('retail, wholesale, both');
                }
                if (!Schema::hasColumn('site_settings', 'normal_paper_size')) {
                    $table->string('normal_paper_size', 50)->default('a4')->nullable();
                }
                if (!Schema::hasColumn('site_settings', 'thermal_paper_size')) {
                    $table->string('thermal_paper_size', 50)->default('80mm')->nullable();
                }
                if (!Schema::hasColumn('site_settings', 'label_preset')) {
                    $table->string('label_preset', 50)->default('4x2')->nullable();
                }
            });
        }

        // 3. Register 'invoice.mushak' and 'report.vat' Permissions & Assign to All Roles
        $permissionsToAdd = [
            [
                'name'        => 'Mushak 6.3 Challan',
                'route'       => 'invoice.mushak',
                'parent_hint' => 'invoice',
            ],
            [
                'name'        => 'VAT / Tax Report',
                'route'       => 'report.vat',
                'parent_hint' => 'report',
            ],
        ];

        $roles = DB::table('roles')->pluck('id');

        foreach ($permissionsToAdd as $permData) {
            $existingPerm = DB::table('permissions')->where('route', $permData['route'])->first();
            $permId = null;

            if (!$existingPerm) {
                $parentPermId = DB::table('permissions')
                    ->where('route', 'like', '%' . $permData['parent_hint'] . '%')
                    ->orWhere('name', 'like', '%' . $permData['parent_hint'] . '%')
                    ->value('id');

                $permId = DB::table('permissions')->insertGetId([
                    'name'       => $permData['name'],
                    'route'      => $permData['route'],
                    'parent_id'  => $parentPermId,
                ]);
            } else {
                $permId = $existingPerm->id;
            }

            if ($permId && count($roles) > 0) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'vehicle_info')) {
                    $table->dropColumn('vehicle_info');
                }
                if (Schema::hasColumn('invoices', 'delivery_address')) {
                    $table->dropColumn('delivery_address');
                }
                if (Schema::hasColumn('invoices', 'vat_percent')) {
                    $table->dropColumn('vat_percent');
                }
            });
        }

        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'default_vat')) {
                    $table->dropColumn('default_vat');
                }
                if (Schema::hasColumn('site_settings', 'sale_nature')) {
                    $table->dropColumn('sale_nature');
                }
                if (Schema::hasColumn('site_settings', 'normal_paper_size')) {
                    $table->dropColumn('normal_paper_size');
                }
                if (Schema::hasColumn('site_settings', 'thermal_paper_size')) {
                    $table->dropColumn('thermal_paper_size');
                }
                if (Schema::hasColumn('site_settings', 'label_preset')) {
                    $table->dropColumn('label_preset');
                }
            });
        }

        DB::table('permissions')->whereIn('route', ['invoice.mushak', 'report.vat'])->delete();
    }
};
