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
        // 1. Make workorder_id nullable in challans table
        try {
            DB::statement("ALTER TABLE `challans` MODIFY `workorder_id` INT NULL DEFAULT NULL;");
        } catch (\Throwable $e) {}

        // 2. Add invoice tracking and acceptance fields to challans table
        Schema::table('challans', function (Blueprint $table) {
            if (!Schema::hasColumn('challans', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->default(null)->after('client_id');
            }
            if (!Schema::hasColumn('challans', 'is_invoiced')) {
                $table->boolean('is_invoiced')->default(0)->after('invoice_id');
            }
            if (!Schema::hasColumn('challans', 'receive_status')) {
                $table->string('receive_status', 50)->default('pending')->after('is_invoiced');
            }
            if (!Schema::hasColumn('challans', 'accepted_at')) {
                $table->dateTime('accepted_at')->nullable()->after('receive_status');
            }
            if (!Schema::hasColumn('challans', 'accepted_by')) {
                $table->unsignedBigInteger('accepted_by')->nullable()->after('accepted_at');
            }
        });

        // 3. Make workorder_detail_id nullable in challan_details table
        try {
            DB::statement("ALTER TABLE `challan_details` MODIFY `workorder_detail_id` VARCHAR(225) NULL DEFAULT NULL;");
        } catch (\Throwable $e) {}

        // 4. Add challan_id to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'challan_id')) {
                $table->unsignedBigInteger('challan_id')->nullable()->default(null)->after('client_id');
            }
        });

        // 5. Add challan_detail_id to invoice_details table
        Schema::table('invoice_details', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_details', 'challan_detail_id')) {
                $table->unsignedBigInteger('challan_detail_id')->nullable()->default(null)->after('workorder_id');
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
        Schema::table('challans', function (Blueprint $table) {
            if (Schema::hasColumn('challans', 'invoice_id')) {
                $table->dropColumn(['invoice_id', 'is_invoiced', 'receive_status', 'accepted_at', 'accepted_by']);
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'challan_id')) {
                $table->dropColumn('challan_id');
            }
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_details', 'challan_detail_id')) {
                $table->dropColumn('challan_detail_id');
            }
        });
    }
};
