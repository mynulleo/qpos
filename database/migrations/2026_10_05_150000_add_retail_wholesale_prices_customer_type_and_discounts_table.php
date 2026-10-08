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
        // -------------------------------------------------------------
        // 1. ITEMS TABLE: Add retail_price and wholesale_price
        // -------------------------------------------------------------
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'retail_price')) {
                    $table->decimal('retail_price', 15, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('items', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 15, 2)->default(0.00)->nullable();
                }
            });

            // Backfill retail_price and wholesale_price from existing rate/price columns if available
            try {
                if (Schema::hasColumn('items', 'sale_price')) {
                    DB::statement("UPDATE `items` SET `retail_price` = `sale_price` WHERE (`retail_price` = 0 OR `retail_price` IS NULL) AND `sale_price` > 0");
                }
                if (Schema::hasColumn('items', 'selling_price')) {
                    DB::statement("UPDATE `items` SET `retail_price` = `selling_price` WHERE (`retail_price` = 0 OR `retail_price` IS NULL) AND `selling_price` > 0");
                }
                if (Schema::hasColumn('items', 'opening_rate')) {
                    DB::statement("UPDATE `items` SET `retail_price` = `opening_rate` WHERE (`retail_price` = 0 OR `retail_price` IS NULL) AND `opening_rate` > 0");
                }
                DB::statement("UPDATE `items` SET `wholesale_price` = `retail_price` WHERE (`wholesale_price` = 0 OR `wholesale_price` IS NULL) AND `retail_price` > 0");
            } catch (\Throwable $e) {}
        }

        // -------------------------------------------------------------
        // 2. ITEM_PRICES TABLE: Add retail_price and wholesale_price
        // -------------------------------------------------------------
        if (Schema::hasTable('item_prices')) {
            Schema::table('item_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('item_prices', 'retail_price')) {
                    $table->decimal('retail_price', 15, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('item_prices', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 15, 2)->default(0.00)->nullable();
                }
            });

            try {
                if (Schema::hasColumn('item_prices', 'selling_price')) {
                    DB::statement("UPDATE `item_prices` SET `retail_price` = `selling_price` WHERE (`retail_price` = 0 OR `retail_price` IS NULL) AND `selling_price` > 0");
                }
                if (Schema::hasColumn('item_prices', 'whole_sale_price')) {
                    DB::statement("UPDATE `item_prices` SET `wholesale_price` = `whole_sale_price` WHERE (`wholesale_price` = 0 OR `wholesale_price` IS NULL) AND `whole_sale_price` > 0");
                }
                DB::statement("UPDATE `item_prices` SET `wholesale_price` = `retail_price` WHERE (`wholesale_price` = 0 OR `wholesale_price` IS NULL) AND `retail_price` > 0");
            } catch (\Throwable $e) {}
        }

        // -------------------------------------------------------------
        // 3. GRN_DETAILS TABLE: Add retail_price and wholesale_price
        // -------------------------------------------------------------
        if (Schema::hasTable('grn_details')) {
            Schema::table('grn_details', function (Blueprint $table) {
                if (!Schema::hasColumn('grn_details', 'retail_price')) {
                    $table->decimal('retail_price', 15, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('grn_details', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 15, 2)->default(0.00)->nullable();
                }
            });

            try {
                if (Schema::hasColumn('grn_details', 'selling_price')) {
                    DB::statement("UPDATE `grn_details` SET `retail_price` = `selling_price` WHERE (`retail_price` = 0 OR `retail_price` IS NULL) AND `selling_price` > 0");
                    DB::statement("UPDATE `grn_details` SET `wholesale_price` = `selling_price` WHERE (`wholesale_price` = 0 OR `wholesale_price` IS NULL) AND `selling_price` > 0");
                }
            } catch (\Throwable $e) {}
        }

        // -------------------------------------------------------------
        // 4. PURCHASE_DETAILS TABLE: Add retail_price and wholesale_price
        // -------------------------------------------------------------
        if (Schema::hasTable('purchase_details')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_details', 'retail_price')) {
                    $table->decimal('retail_price', 15, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('purchase_details', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 15, 2)->default(0.00)->nullable();
                }
            });

            try {
                if (Schema::hasColumn('purchase_details', 'selling_price')) {
                    DB::statement("UPDATE `purchase_details` SET `retail_price` = `selling_price` WHERE (`retail_price` = 0 OR `retail_price` IS NULL) AND `selling_price` > 0");
                    DB::statement("UPDATE `purchase_details` SET `wholesale_price` = `selling_price` WHERE (`wholesale_price` = 0 OR `wholesale_price` IS NULL) AND `selling_price` > 0");
                }
            } catch (\Throwable $e) {}
        }

        // -------------------------------------------------------------
        // 5. CLIENTS TABLE: Add customer_type ('retail', 'wholesale')
        // -------------------------------------------------------------
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (!Schema::hasColumn('clients', 'customer_type')) {
                    $table->string('customer_type', 30)->default('retail')->after('name')->comment('retail, wholesale');
                }
            });
        }

        // -------------------------------------------------------------
        // 6. DISCOUNTS TABLE: Create discount management table
        // -------------------------------------------------------------
        if (!Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->id();
                $table->string('title', 191);
                $table->string('scope', 30)->default('category')->comment('category, item');
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->string('discount_type', 30)->default('percentage')->comment('percentage, fixed');
                $table->decimal('discount_value', 12, 2)->default(0.00);
                $table->string('applicable_on', 30)->default('both')->comment('retail, wholesale, both');
                $table->date('valid_from')->nullable()->index();
                $table->date('valid_to')->nullable()->index();
                $table->tinyInteger('status')->default(1)->index()->comment('1: active, 0: inactive');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // -------------------------------------------------------------
        // 7. REGISTER DISCOUNT MENU & PERMISSIONS
        // -------------------------------------------------------------
        $posParent = DB::table('menus')->where('route_name', 'pos.index')->value('parent_id')
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'POS')->value('id')
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Stock & Inventory')->value('id');

        if ($posParent) {
            $existingMenu = DB::table('menus')->where('route_name', 'discount.index')->first();
            if (!$existingMenu) {
                $maxSorting = DB::table('menus')->where('parent_id', $posParent)->max('sorting') ?? 10;
                DB::table('menus')->insert([
                    'parent_id'     => $posParent,
                    'menu_name'     => 'Discounts',
                    'icon'          => "<i class='fas fa-tags'></i>",
                    'route_name'    => 'discount.index',
                    'params'        => null,
                    'sorting'       => $maxSorting + 1,
                    'show_dasboard' => 0,
                    'show_profile'  => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // Permissions
        $existingPerm = DB::table('permissions')->where('route', 'discount.index')->first();
        $permId = null;

        if (!$existingPerm) {
            $parentPermId = DB::table('permissions')->where('route', 'pos.index')->value('parent_id')
                ?? DB::table('permissions')->where('route', 'item.index')->value('parent_id')
                ?? null;

            $permId = DB::table('permissions')->insertGetId([
                'name'      => 'Discount Management',
                'route'     => 'discount.index',
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
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasColumn('items', 'retail_price')) {
                    $table->dropColumn('retail_price');
                }
                if (Schema::hasColumn('items', 'wholesale_price')) {
                    $table->dropColumn('wholesale_price');
                }
            });
        }

        if (Schema::hasTable('item_prices')) {
            Schema::table('item_prices', function (Blueprint $table) {
                if (Schema::hasColumn('item_prices', 'retail_price')) {
                    $table->dropColumn('retail_price');
                }
                if (Schema::hasColumn('item_prices', 'wholesale_price')) {
                    $table->dropColumn('wholesale_price');
                }
            });
        }

        if (Schema::hasTable('grn_details')) {
            Schema::table('grn_details', function (Blueprint $table) {
                if (Schema::hasColumn('grn_details', 'retail_price')) {
                    $table->dropColumn('retail_price');
                }
                if (Schema::hasColumn('grn_details', 'wholesale_price')) {
                    $table->dropColumn('wholesale_price');
                }
            });
        }

        if (Schema::hasTable('purchase_details')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_details', 'retail_price')) {
                    $table->dropColumn('retail_price');
                }
                if (Schema::hasColumn('purchase_details', 'wholesale_price')) {
                    $table->dropColumn('wholesale_price');
                }
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (Schema::hasColumn('clients', 'customer_type')) {
                    $table->dropColumn('customer_type');
                }
            });
        }

        Schema::dropIfExists('discounts');

        DB::table('menus')->where('route_name', 'discount.index')->delete();
        DB::table('permissions')->where('route', 'discount.index')->delete();
    }
};
