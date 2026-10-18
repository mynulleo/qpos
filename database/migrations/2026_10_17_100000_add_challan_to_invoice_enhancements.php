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
        // 1. Add show_invoice_terms to site_settings table if not exists
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'show_invoice_terms')) {
                    $table->boolean('show_invoice_terms')->default(1)->nullable()->after('show_pos_terms');
                }
            });
        }

        // 2. Add payment_id and payment_method to invoices table
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'payment_id')) {
                    $table->unsignedBigInteger('payment_id')->nullable()->default(null)->after('paid_amount');
                }
                if (!Schema::hasColumn('invoices', 'payment_method')) {
                    $table->string('payment_method', 50)->nullable()->default(null)->after('payment_id');
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
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (Schema::hasColumn('site_settings', 'show_invoice_terms')) {
                    $table->dropColumn('show_invoice_terms');
                }
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'payment_id')) {
                    $table->dropColumn('payment_id');
                }
                if (Schema::hasColumn('invoices', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
            });
        }
    }
};
