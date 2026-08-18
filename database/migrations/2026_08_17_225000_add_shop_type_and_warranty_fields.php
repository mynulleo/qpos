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
                if (!Schema::hasColumn('site_settings', 'shop_type')) {
                    $table->string('shop_type')->default('electronics')->after('title');
                }
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'warranty_type')) {
                    $table->string('warranty_type')->default('none')->after('description');
                }
                if (!Schema::hasColumn('items', 'warranty_period')) {
                    $table->string('warranty_period')->nullable()->after('warranty_type');
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
                if (Schema::hasColumn('site_settings', 'shop_type')) {
                    $table->dropColumn('shop_type');
                }
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasColumn('items', 'warranty_period')) {
                    $table->dropColumn('warranty_period');
                }
                if (Schema::hasColumn('items', 'warranty_type')) {
                    $table->dropColumn('warranty_type');
                }
            });
        }
    }
};
