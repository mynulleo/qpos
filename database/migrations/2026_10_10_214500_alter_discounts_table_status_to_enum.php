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
        if (Schema::hasTable('discounts')) {
            // Convert any existing integer status values if any exist
            DB::statement("UPDATE `discounts` SET `status` = 'active' WHERE `status` = 1 OR `status` = '1' OR `status` IS NULL");
            DB::statement("UPDATE `discounts` SET `status` = 'deactive' WHERE `status` = 0 OR `status` = '0'");

            // Modify discounts.status to ENUM('active', 'deactive') to match other modules in the application
            DB::statement("ALTER TABLE `discounts` MODIFY `status` ENUM('active', 'deactive') NOT NULL DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('discounts')) {
            DB::statement("ALTER TABLE `discounts` MODIFY `status` TINYINT NOT NULL DEFAULT 1");
        }
    }
};
