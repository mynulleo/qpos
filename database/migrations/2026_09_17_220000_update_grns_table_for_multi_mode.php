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
        // 1. Update purchases table to ensure receive_status exists
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'receive_status')) {
                    $table->enum('receive_status', ['Pending', 'Partial', 'Received'])->default('Pending')->after('is_closed');
                }
            });
        }

        // 2. Update grns master table for multi-mode (PO, Supplier, Direct)
        if (Schema::hasTable('grns')) {
            try {
                DB::statement("ALTER TABLE `grns` MODIFY `purchase_id` BIGINT UNSIGNED NULL");
            } catch (\Exception $e) {}

            try {
                DB::statement("ALTER TABLE `grns` MODIFY `supplier_id` BIGINT UNSIGNED NULL");
            } catch (\Exception $e) {}

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
                if (!Schema::hasColumn('grns', 'fund_account_id')) {
                    $table->unsignedBigInteger('fund_account_id')->nullable()->index()->after('paid_amount');
                }
            });
        }

        // 3. Update grn_details table for standalone item lines & serials
        if (Schema::hasTable('grn_details')) {
            try {
                DB::statement("ALTER TABLE `grn_details` MODIFY `purchase_detail_id` BIGINT UNSIGNED NULL");
            } catch (\Exception $e) {}

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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('grn_details')) {
            Schema::table('grn_details', function (Blueprint $table) {
                if (Schema::hasColumn('grn_details', 'serial_no')) {
                    $table->dropColumn('serial_no');
                }
                if (Schema::hasColumn('grn_details', 'total_amount')) {
                    $table->dropColumn('total_amount');
                }
                if (Schema::hasColumn('grn_details', 'selling_price')) {
                    $table->dropColumn('selling_price');
                }
                if (Schema::hasColumn('grn_details', 'unit_price')) {
                    $table->dropColumn('unit_price');
                }
            });
        }

        if (Schema::hasTable('grns')) {
            Schema::table('grns', function (Blueprint $table) {
                if (Schema::hasColumn('grns', 'fund_account_id')) {
                    $table->dropColumn('fund_account_id');
                }
                if (Schema::hasColumn('grns', 'discount')) {
                    $table->dropColumn('discount');
                }
                if (Schema::hasColumn('grns', 'sub_total')) {
                    $table->dropColumn('sub_total');
                }
                if (Schema::hasColumn('grns', 'grn_type')) {
                    $table->dropColumn('grn_type');
                }
            });
        }
    }
};
