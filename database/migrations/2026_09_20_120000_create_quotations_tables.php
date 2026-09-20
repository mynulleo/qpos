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
        // 1. Create quotations table
        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('quotation_no', 50)->unique()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('client_name')->nullable();
                $table->string('client_phone')->nullable();
                $table->string('client_email')->nullable();
                $table->text('client_address')->nullable();
                $table->date('quotation_date');
                $table->date('validity_date')->nullable();
                $table->string('subject')->nullable();
                $table->string('reference_no')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->decimal('currency_rate', 10, 4)->default(1.0000);
                $table->decimal('sub_total', 14, 2)->default(0.00);
                $table->string('discount_type', 20)->default('fixed'); // fixed / percentage
                $table->decimal('discount', 12, 2)->default(0.00);
                $table->decimal('discount_amount', 14, 2)->default(0.00);
                $table->decimal('tax_percent', 8, 2)->default(0.00);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('shipping_cost', 14, 2)->default(0.00);
                $table->decimal('total_amount', 14, 2)->default(0.00);
                $table->integer('total_items')->default(0);
                $table->decimal('total_qty', 12, 2)->default(0.00);
                $table->string('status', 30)->default('draft'); // draft, sent, accepted, declined, converted, expired
                $table->text('payment_terms')->nullable();
                $table->text('delivery_terms')->nullable();
                $table->text('warranty_terms')->nullable();
                $table->longText('terms_conditions')->nullable();
                $table->text('note')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('prepared_by')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Create quotation_details table
        if (!Schema::hasTable('quotation_details')) {
            Schema::create('quotation_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('quotation_id')->index();
                $table->string('item_type', 20)->default('product'); // product, custom, service
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->unsignedBigInteger('color_id')->nullable();
                $table->unsignedBigInteger('size_id')->nullable();
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('unit_name', 50)->default('Pcs');
                $table->decimal('qty', 12, 2)->default(1.00);
                $table->decimal('unit_price', 14, 2)->default(0.00);
                $table->decimal('discount_percent', 8, 2)->default(0.00);
                $table->decimal('discount_amount', 14, 2)->default(0.00);
                $table->decimal('total_price', 14, 2)->default(0.00);
                $table->integer('sorting')->default(0);
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            });
        }

        // 3. Register Permissions for QuotationController
        $parentPerm = DB::table('permissions')->where('name', 'QuotationController')->first();
        if (!$parentPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name' => 'QuotationController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $parentPermId = $parentPerm->id;
        }

        $childPermissions = [
            ['name' => 'index', 'route' => 'quotation.index', 'parent_id' => $parentPermId],
            ['name' => 'create', 'route' => 'quotation.create', 'parent_id' => $parentPermId],
            ['name' => 'store', 'route' => 'quotation.store', 'parent_id' => $parentPermId],
            ['name' => 'show', 'route' => 'quotation.show', 'parent_id' => $parentPermId],
            ['name' => 'edit', 'route' => 'quotation.edit', 'parent_id' => $parentPermId],
            ['name' => 'update', 'route' => 'quotation.update', 'parent_id' => $parentPermId],
            ['name' => 'destroy', 'route' => 'quotation.destroy', 'parent_id' => $parentPermId],
            ['name' => 'changeStatus', 'route' => 'quotation.changeStatus', 'parent_id' => $parentPermId],
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

        // 4. Seed Menu Item for Quotations
        $existingMenu = DB::table('menus')->where('route_name', 'quotation.index')->first();
        if (!$existingMenu) {
            // Find POS Parent (ID 142 or by name 'POS')
            $posParent = DB::table('menus')->where('id', 142)->first()
                ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'POS')->first();

            $parentId = $posParent ? $posParent->id : null;

            DB::table('menus')->insert([
                'menu_name' => 'Quotations',
                'module_name' => '\\App\\Models\\Quotation',
                'route_name' => 'quotation.index',
                'parent_id' => $parentId,
                'icon' => '<i class="fas fa-file-invoice-dollar"></i>',
                'sorting' => 4,
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
        Schema::dropIfExists('quotation_details');
        Schema::dropIfExists('quotations');
    }
};
