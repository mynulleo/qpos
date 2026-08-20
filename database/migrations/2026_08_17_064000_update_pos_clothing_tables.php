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
        // 1. Add serial_no to invoice_details table
        if (Schema::hasTable('invoice_details') && !Schema::hasColumn('invoice_details', 'serial_no')) {
            Schema::table('invoice_details', function (Blueprint $table) {
                $table->string('serial_no', 255)->nullable()->after('size_id');
            });
        }

        // Add serial_no and selling_price to purchase_details table
        if (Schema::hasTable('purchase_details')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_details', 'serial_no')) {
                    $table->text('serial_no')->nullable()->after('size_id');
                }
                if (!Schema::hasColumn('purchase_details', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0.00)->after('price');
                }
            });
        }

        // 2. Re-create item_stock_summaries view to include color_id and size_id
        DB::statement("DROP VIEW IF EXISTS `item_stock_summaries`");
        DB::statement("
            CREATE VIEW `item_stock_summaries` AS
            SELECT 
                stock_transactions.item_id AS item_id,
                stock_transactions.color_id AS color_id,
                stock_transactions.size_id AS size_id,
                SUM(stock_transactions.qty_in) AS total_qty_in,
                SUM(stock_transactions.qty_out) AS total_qty_out,
                (SUM(stock_transactions.qty_in) - SUM(stock_transactions.qty_out)) AS current_stock
            FROM stock_transactions
            WHERE stock_transactions.status = 'active'
            GROUP BY stock_transactions.item_id, stock_transactions.color_id, stock_transactions.size_id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('invoice_details') && Schema::hasColumn('invoice_details', 'serial_no')) {
            Schema::table('invoice_details', function (Blueprint $table) {
                $table->dropColumn('serial_no');
            });
        }

        DB::statement("DROP VIEW IF EXISTS `item_stock_summaries`");
        DB::statement("
            CREATE VIEW `item_stock_summaries` AS
            SELECT 
                stock_transactions.item_id AS item_id,
                SUM(stock_transactions.qty_in) AS total_qty_in,
                SUM(stock_transactions.qty_out) AS total_qty_out,
                (SUM(stock_transactions.qty_in) - SUM(stock_transactions.qty_out)) AS current_stock
            FROM stock_transactions
            WHERE stock_transactions.status = 'active'
            GROUP BY stock_transactions.item_id
        ");
    }
};
