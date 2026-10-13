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
        if (Schema::hasTable('invoice_details')) {
            Schema::table('invoice_details', function (Blueprint $table) {
                if (!Schema::hasColumn('invoice_details', 'warranty_type')) {
                    $table->string('warranty_type', 50)->default('none')->nullable()->after('serial_no');
                }
                if (!Schema::hasColumn('invoice_details', 'warranty_duration')) {
                    $table->integer('warranty_duration')->nullable()->after('warranty_type');
                }
                if (!Schema::hasColumn('invoice_details', 'warranty_period')) {
                    $table->string('warranty_period', 100)->nullable()->after('warranty_duration');
                }
                if (!Schema::hasColumn('invoice_details', 'warranty_expires_at')) {
                    $table->date('warranty_expires_at')->nullable()->after('warranty_period');
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
        if (Schema::hasTable('invoice_details')) {
            Schema::table('invoice_details', function (Blueprint $table) {
                if (Schema::hasColumn('invoice_details', 'warranty_period')) {
                    $table->dropColumn('warranty_period');
                }
            });
        }
    }
};
