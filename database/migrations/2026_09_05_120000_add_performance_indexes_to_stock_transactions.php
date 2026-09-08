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
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Check and add composite index on stock_transactions for ultra fast stock view aggregation
            $existingIndexes = collect(DB::select("SHOW INDEX FROM stock_transactions"))->pluck('Key_name')->unique()->toArray();
            
            if (!in_array('idx_st_status_item_color_size', $existingIndexes)) {
                $table->index(['status', 'item_id', 'color_id', 'size_id'], 'idx_st_status_item_color_size');
            }
            if (!in_array('idx_st_item_status', $existingIndexes)) {
                $table->index(['item_id', 'status'], 'idx_st_item_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM stock_transactions"))->pluck('Key_name')->unique()->toArray();
            
            if (in_array('idx_st_status_item_color_size', $existingIndexes)) {
                $table->dropIndex('idx_st_status_item_color_size');
            }
            if (in_array('idx_st_item_status', $existingIndexes)) {
                $table->dropIndex('idx_st_item_status');
            }
        });
    }
};
