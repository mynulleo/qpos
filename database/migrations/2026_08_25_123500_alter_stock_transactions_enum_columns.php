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
        DB::statement("ALTER TABLE `stock_transactions` MODIFY `transaction_type` VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE `stock_transactions` MODIFY `reference_type` VARCHAR(50) NULL");
        DB::statement("ALTER TABLE `stock_transactions` MODIFY `created_ip` VARCHAR(45) NULL DEFAULT '1'");
        DB::statement("ALTER TABLE `stock_transactions` MODIFY `updated_ip` VARCHAR(45) NULL DEFAULT '1'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `stock_transactions` MODIFY `transaction_type` ENUM('Opening','Purchase','Issue','Adjustment','Wastage') NOT NULL");
    }
};
