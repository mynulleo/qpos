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
                if (!Schema::hasColumn('site_settings', 'show_pos_item_popup')) {
                    $table->boolean('show_pos_item_popup')->default(1)->nullable()->after('show_invoice_terms')->comment('1 = Show item modal popup on POS select, 0 = Directly add to cart');
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
                if (Schema::hasColumn('site_settings', 'show_pos_item_popup')) {
                    $table->dropColumn('show_pos_item_popup');
                }
            });
        }
    }
};

