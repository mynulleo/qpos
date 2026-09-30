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
        // 1. ALTER GRNS MASTER TABLE (Make purchase_id & supplier_id nullable)
        // -------------------------------------------------------------
        if (Schema::hasTable('grns')) {
            try {
                DB::statement("ALTER TABLE `grns` MODIFY `purchase_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `grns` MODIFY `supplier_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            Schema::table('grns', function (Blueprint $table) {
                if (!Schema::hasColumn('grns', 'grn_type')) {
                    $table->enum('grn_type', ['po', 'supplier', 'direct'])->default('po')->after('grn_date');
                }
                if (!Schema::hasColumn('grns', 'sub_total')) {
                    $table->decimal('sub_total', 12, 2)->default(0.00)->after('challan_date');
                }
                if (!Schema::hasColumn('grns', 'discount')) {
                    $table->decimal('discount', 12, 2)->default(0.00)->after('sub_total');
                }
                if (!Schema::hasColumn('grns', 'paid_amount')) {
                    $table->decimal('paid_amount', 12, 2)->default(0.00)->after('total_amount');
                }
                if (!Schema::hasColumn('grns', 'fund_account_id')) {
                    $table->unsignedBigInteger('fund_account_id')->nullable()->index()->after('paid_amount');
                }
                if (!Schema::hasColumn('grns', 'is_closed')) {
                    $table->tinyInteger('is_closed')->default(0)->after('fund_account_id');
                }
            });
        }

        // -------------------------------------------------------------
        // 2. ALTER GRN_DETAILS TABLE (Make purchase_detail_id nullable & add serials)
        // -------------------------------------------------------------
        if (Schema::hasTable('grn_details')) {
            try {
                DB::statement("ALTER TABLE `grn_details` MODIFY `purchase_detail_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            Schema::table('grn_details', function (Blueprint $table) {
                if (!Schema::hasColumn('grn_details', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->index()->after('purchase_detail_id');
                }
                if (!Schema::hasColumn('grn_details', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->index()->after('item_id');
                }
                if (!Schema::hasColumn('grn_details', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->index()->after('color_id');
                }
                if (!Schema::hasColumn('grn_details', 'unit_id')) {
                    $table->unsignedBigInteger('unit_id')->nullable()->index()->after('size_id');
                }
                if (!Schema::hasColumn('grn_details', 'ordered_qty')) {
                    $table->decimal('ordered_qty', 12, 2)->default(0.00)->after('unit_id');
                }
                if (!Schema::hasColumn('grn_details', 'previously_received_qty')) {
                    $table->decimal('previously_received_qty', 12, 2)->default(0.00)->after('ordered_qty');
                }
                if (!Schema::hasColumn('grn_details', 'unit_price')) {
                    $table->decimal('unit_price', 12, 2)->default(0.00)->after('received_qty');
                }
                if (!Schema::hasColumn('grn_details', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0.00)->after('unit_price');
                }
                if (!Schema::hasColumn('grn_details', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->default(0.00)->after('selling_price');
                }
                if (!Schema::hasColumn('grn_details', 'serial_no')) {
                    $table->text('serial_no')->nullable()->after('total_amount');
                }
            });
        }

        // -------------------------------------------------------------
        // 3. ALTER ITEM_PRICES TABLE (Ensure barcode and pricing columns exist)
        // -------------------------------------------------------------
        if (Schema::hasTable('item_prices')) {
            Schema::table('item_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('item_prices', 'item_id')) {
                    $table->unsignedBigInteger('item_id')->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('item_prices', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->index()->after('item_id');
                }
                if (!Schema::hasColumn('item_prices', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->index()->after('color_id');
                }
                if (!Schema::hasColumn('item_prices', 'barcode')) {
                    $table->string('barcode', 100)->nullable()->index()->after('size_id');
                }
                if (!Schema::hasColumn('item_prices', 'purchase_price')) {
                    $table->decimal('purchase_price', 12, 2)->default(0.00)->after('barcode');
                }
                if (!Schema::hasColumn('item_prices', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0.00)->after('purchase_price');
                }
                if (!Schema::hasColumn('item_prices', 'sales_price')) {
                    $table->decimal('sales_price', 12, 2)->default(0.00)->after('selling_price');
                }
                if (!Schema::hasColumn('item_prices', 'mrp_price')) {
                    $table->decimal('mrp_price', 12, 2)->default(0.00)->after('sales_price');
                }
                if (!Schema::hasColumn('item_prices', 'whole_sale_price')) {
                    $table->decimal('whole_sale_price', 12, 2)->default(0.00)->after('mrp_price');
                }
                if (!Schema::hasColumn('item_prices', 'opening_stock')) {
                    $table->decimal('opening_stock', 12, 2)->default(0.00)->after('whole_sale_price');
                }
            });
        }

        // -------------------------------------------------------------
        // 4. PURCHASES TABLE (Ensure receive_status exists)
        // -------------------------------------------------------------
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'receive_status')) {
                    $table->enum('receive_status', ['Pending', 'Partial', 'Received'])->default('Pending')->after('is_closed');
                }
            });
        }

        // -------------------------------------------------------------
        // 5. PURCHASE_DETAILS TABLE (Ensure selling_price & serial_no exist)
        // -------------------------------------------------------------
        if (Schema::hasTable('purchase_details')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_details', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0.00)->after('price');
                }
                if (!Schema::hasColumn('purchase_details', 'serial_no')) {
                    $table->text('serial_no')->nullable()->after('selling_price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Safe rollback operations
    }
};
