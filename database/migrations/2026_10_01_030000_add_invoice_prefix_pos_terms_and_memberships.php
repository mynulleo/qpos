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
        // 1. Add invoice_prefix, show_pos_terms, memberships to site_settings table
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'invoice_prefix')) {
                    $table->string('invoice_prefix', 50)->default('POS')->nullable();
                }
                if (!Schema::hasColumn('site_settings', 'show_pos_terms')) {
                    $table->boolean('show_pos_terms')->default(1)->nullable();
                }
                if (!Schema::hasColumn('site_settings', 'memberships')) {
                    $table->longText('memberships')->nullable();
                }
            });
        }

        // 2. Add terms_conditions column to invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'terms_conditions')) {
                    $table->longText('terms_conditions')->nullable();
                }
            });
        }

        // 3. Create terms_conditions table
        if (!Schema::hasTable('terms_conditions')) {
            Schema::create('terms_conditions', function (Blueprint $table) {
                $table->id();
                $table->string('module_name', 100)->default('Invoice'); // Invoice, Purchase Order, Warranty, Quotation
                $table->text('condition_text');
                $table->integer('sorting')->default(0);
                $table->boolean('is_default')->default(1);
                $table->string('status', 20)->default('active');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 4. Create organization_memberships table
        if (!Schema::hasTable('organization_memberships')) {
            Schema::create('organization_memberships', function (Blueprint $table) {
                $table->id();
                $table->string('org_name', 255);
                $table->text('logo')->nullable();
                $table->boolean('show_in_invoice')->default(1);
                $table->integer('sorting')->default(0);
                $table->string('status', 20)->default('active');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 5. Seed default terms and conditions
        $defaultTerms = [
            // Invoice Terms
            [
                'module_name' => 'Invoice',
                'condition_text' => 'Please preserve this invoice for any warranty claims and exchange within 7 days.',
                'sorting' => 1,
                'is_default' => 1,
                'status' => 'active',
            ],
            [
                'module_name' => 'Invoice',
                'condition_text' => 'Warranty does not cover physical damage, burn, liquid ingress, or broken warranty seals.',
                'sorting' => 2,
                'is_default' => 1,
                'status' => 'active',
            ],
            [
                'module_name' => 'Invoice',
                'condition_text' => 'Disputed items will be inspected according to company service policy.',
                'sorting' => 3,
                'is_default' => 1,
                'status' => 'active',
            ],
            // Purchase Order Terms
            [
                'module_name' => 'Purchase Order',
                'condition_text' => 'All products must match specifications and serial requirements upon delivery.',
                'sorting' => 1,
                'is_default' => 1,
                'status' => 'active',
            ],
            [
                'module_name' => 'Purchase Order',
                'condition_text' => 'Defective or substandard goods will be rejected and returned immediately.',
                'sorting' => 2,
                'is_default' => 1,
                'status' => 'active',
            ],
            [
                'module_name' => 'Purchase Order',
                'condition_text' => 'Payment will be processed according to agreed credit terms after inspection.',
                'sorting' => 3,
                'is_default' => 1,
                'status' => 'active',
            ],
            // Warranty Terms
            [
                'module_name' => 'Warranty',
                'condition_text' => 'Warranty service covers manufacturer defects during the valid warranty period only.',
                'sorting' => 1,
                'is_default' => 1,
                'status' => 'active',
            ],
            [
                'module_name' => 'Warranty',
                'condition_text' => 'Physical damage, tampering, unauthorized repair, or liquid damage will void warranty.',
                'sorting' => 2,
                'is_default' => 1,
                'status' => 'active',
            ],
            [
                'module_name' => 'Warranty',
                'condition_text' => 'Minimum 7-15 business days required for warranty claim inspection and resolution.',
                'sorting' => 3,
                'is_default' => 1,
                'status' => 'active',
            ],
        ];

        foreach ($defaultTerms as $term) {
            $exists = DB::table('terms_conditions')
                ->where('module_name', $term['module_name'])
                ->where('condition_text', $term['condition_text'])
                ->first();
            if (!$exists) {
                DB::table('terms_conditions')->insert(array_merge($term, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 6. Register Permissions for TermsConditionController
        $parentPerm = DB::table('permissions')->where('name', 'TermsConditionController')->first();
        if (!$parentPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name' => 'TermsConditionController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $parentPermId = $parentPerm->id;
        }

        $childPermissions = [
            ['name' => 'index', 'route' => 'termsCondition.index', 'parent_id' => $parentPermId],
            ['name' => 'create', 'route' => 'termsCondition.create', 'parent_id' => $parentPermId],
            ['name' => 'store', 'route' => 'termsCondition.store', 'parent_id' => $parentPermId],
            ['name' => 'show', 'route' => 'termsCondition.show', 'parent_id' => $parentPermId],
            ['name' => 'edit', 'route' => 'termsCondition.edit', 'parent_id' => $parentPermId],
            ['name' => 'update', 'route' => 'termsCondition.update', 'parent_id' => $parentPermId],
            ['name' => 'destroy', 'route' => 'termsCondition.destroy', 'parent_id' => $parentPermId],
            ['name' => 'byModule', 'route' => 'termsCondition.byModule', 'parent_id' => $parentPermId],
            ['name' => 'toggleStatus', 'route' => 'termsCondition.toggleStatus', 'parent_id' => $parentPermId],
            ['name' => 'toggleDefault', 'route' => 'termsCondition.toggleDefault', 'parent_id' => $parentPermId],
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

        // 7. Seed Menu Item for Terms & Conditions under System / Settings parent
        $existingMenu = DB::table('menus')->where('route_name', 'termsCondition.index')->first();
        if (!$existingMenu) {
            // Find System / Settings Parent
            $settingsParent = DB::table('menus')->where('menu_name', 'like', '%Setting%')->whereNull('parent_id')->first()
                ?? DB::table('menus')->where('menu_name', 'like', '%System%')->whereNull('parent_id')->first()
                ?? DB::table('menus')->where('menu_name', 'like', '%POS%')->whereNull('parent_id')->first();

            $parentId = $settingsParent ? $settingsParent->id : null;

            DB::table('menus')->insert([
                'menu_name' => 'Terms & Conditions',
                'module_name' => '\\App\\Models\\TermsCondition',
                'route_name' => 'termsCondition.index',
                'parent_id' => $parentId,
                'icon' => '<i class="fas fa-file-contract"></i>',
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
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'invoice_prefix')) {
                    $table->dropColumn('invoice_prefix');
                }
                if (Schema::hasColumn('site_settings', 'show_pos_terms')) {
                    $table->dropColumn('show_pos_terms');
                }
                if (Schema::hasColumn('site_settings', 'memberships')) {
                    $table->dropColumn('memberships');
                }
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'terms_conditions')) {
                    $table->dropColumn('terms_conditions');
                }
            });
        }

        Schema::dropIfExists('terms_conditions');
        Schema::dropIfExists('organization_memberships');
    }
};
