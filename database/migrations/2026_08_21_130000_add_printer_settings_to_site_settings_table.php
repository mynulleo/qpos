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
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'printer_type')) {
                    $table->string('printer_type', 50)->default('thermal')->after('shop_type')->comment('thermal, normal');
                }
                if (!Schema::hasColumn('site_settings', 'normal_paper_size')) {
                    $table->string('normal_paper_size', 50)->default('A4')->after('printer_type')->comment('A4, A5');
                }
                if (!Schema::hasColumn('site_settings', 'thermal_paper_size')) {
                    $table->string('thermal_paper_size', 50)->default('80mm')->after('normal_paper_size')->comment('80mm, 60mm');
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
                if (Schema::hasColumn('site_settings', 'thermal_paper_size')) {
                    $table->dropColumn('thermal_paper_size');
                }
                if (Schema::hasColumn('site_settings', 'normal_paper_size')) {
                    $table->dropColumn('normal_paper_size');
                }
                if (Schema::hasColumn('site_settings', 'printer_type')) {
                    $table->dropColumn('printer_type');
                }
            });
        }
    }
};
