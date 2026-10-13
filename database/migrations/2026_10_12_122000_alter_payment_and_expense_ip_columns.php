<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        $tables = [
            'payments',
            'payment_details',
            'expenses',
            'expense_details',
            'invoices',
            'invoice_details',
            'purchases',
            'purchase_details',
            'vouchers',
            'voucher_details',
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                if (Schema::hasColumn($tbl, 'created_ip')) {
                    try {
                        DB::statement("ALTER TABLE `{$tbl}` MODIFY `created_ip` VARCHAR(45) NULL DEFAULT '1'");
                    } catch (\Throwable $e) {}
                }
                if (Schema::hasColumn($tbl, 'updated_ip')) {
                    try {
                        DB::statement("ALTER TABLE `{$tbl}` MODIFY `updated_ip` VARCHAR(45) NULL DEFAULT '1'");
                    } catch (\Throwable $e) {}
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
        // No downgrade needed for VARCHAR(45) IP columns
    }
};
