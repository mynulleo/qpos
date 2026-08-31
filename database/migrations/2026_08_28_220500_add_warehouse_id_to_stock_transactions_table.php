<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('stock_transactions') && !Schema::hasColumn('stock_transactions', 'warehouse_id')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('item_id')->index();
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
        if (Schema::hasTable('stock_transactions') && Schema::hasColumn('stock_transactions', 'warehouse_id')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
