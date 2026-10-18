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
        Schema::table('challan_details', function (Blueprint $table) {
            if (!Schema::hasColumn('challan_details', 'color_id')) {
                $table->unsignedBigInteger('color_id')->nullable()->after('item_id');
            }
            if (!Schema::hasColumn('challan_details', 'size_id')) {
                $table->unsignedBigInteger('size_id')->nullable()->after('color_id');
            }
            if (!Schema::hasColumn('challan_details', 'serial_no')) {
                $table->text('serial_no')->nullable()->after('size_id');
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
        Schema::table('challan_details', function (Blueprint $table) {
            if (Schema::hasColumn('challan_details', 'serial_no')) {
                $table->dropColumn('serial_no');
            }
            if (Schema::hasColumn('challan_details', 'size_id')) {
                $table->dropColumn('size_id');
            }
            if (Schema::hasColumn('challan_details', 'color_id')) {
                $table->dropColumn('color_id');
            }
        });
    }
};
