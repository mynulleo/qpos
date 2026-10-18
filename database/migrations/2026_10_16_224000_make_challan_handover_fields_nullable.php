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
        // 1. Make delivery and receiver handover fields nullable in challans table
        DB::statement("ALTER TABLE `challans` 
            MODIFY `delivery_location` VARCHAR(225) NULL DEFAULT NULL,
            MODIFY `receive_by` VARCHAR(225) NULL DEFAULT NULL,
            MODIFY `receive_by_designation` VARCHAR(225) NULL DEFAULT NULL,
            MODIFY `receive_by_mobile` VARCHAR(225) NULL DEFAULT NULL,
            MODIFY `delivery_by` VARCHAR(225) NULL DEFAULT NULL,
            MODIFY `delivery_by_mobile` VARCHAR(225) NULL DEFAULT NULL;");

        // 2. Make description nullable in challan_details
        DB::statement("ALTER TABLE `challan_details` 
            MODIFY `description` TEXT NULL DEFAULT NULL,
            MODIFY `receive_qty` DECIMAL(8,2) NOT NULL DEFAULT '0.00';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to revert to strict not null
    }
};
