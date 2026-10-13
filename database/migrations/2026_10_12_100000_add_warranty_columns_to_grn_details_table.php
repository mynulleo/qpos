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
        if (Schema::hasTable('grn_details')) {
            Schema::table('grn_details', function (Blueprint $table) {
                if (!Schema::hasColumn('grn_details', 'warranty_type')) {
                    $table->string('warranty_type', 50)->default('none')->nullable()->after('serial_no');
                }
                if (!Schema::hasColumn('grn_details', 'warranty_duration')) {
                    $table->integer('warranty_duration')->nullable()->after('warranty_type');
                }
                if (!Schema::hasColumn('grn_details', 'warranty_period')) {
                    $table->string('warranty_period', 191)->nullable()->after('warranty_duration');
                }
                if (!Schema::hasColumn('grn_details', 'warranty_expires_at')) {
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
        if (Schema::hasTable('grn_details')) {
            Schema::table('grn_details', function (Blueprint $table) {
                if (Schema::hasColumn('grn_details', 'warranty_expires_at')) {
                    $table->dropColumn('warranty_expires_at');
                }
                if (Schema::hasColumn('grn_details', 'warranty_period')) {
                    $table->dropColumn('warranty_period');
                }
                if (Schema::hasColumn('grn_details', 'warranty_duration')) {
                    $table->dropColumn('warranty_duration');
                }
                if (Schema::hasColumn('grn_details', 'warranty_type')) {
                    $table->dropColumn('warranty_type');
                }
            });
        }
    }
};
