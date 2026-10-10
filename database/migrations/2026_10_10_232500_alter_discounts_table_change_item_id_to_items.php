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
        if (Schema::hasTable('discounts')) {
            // 1. Add items column as JSON if not already present
            if (!Schema::hasColumn('discounts', 'items')) {
                Schema::table('discounts', function (Blueprint $table) {
                    $table->json('items')->nullable()->after('category_id');
                });
            }

            // 2. Migrate existing item_id values into items JSON array
            if (Schema::hasColumn('discounts', 'item_id')) {
                DB::statement("UPDATE `discounts` SET `items` = JSON_ARRAY(item_id) WHERE `item_id` IS NOT NULL AND (`items` IS NULL OR JSON_LENGTH(`items`) = 0)");

                // Drop index on item_id if exists
                $indexes = DB::select("SHOW INDEX FROM `discounts` WHERE Key_name = 'discounts_item_id_index'");
                if (!empty($indexes)) {
                    DB::statement("ALTER TABLE `discounts` DROP INDEX `discounts_item_id_index`");
                }

                // Drop the old item_id column
                Schema::table('discounts', function (Blueprint $table) {
                    $table->dropColumn('item_id');
                });
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
        if (Schema::hasTable('discounts')) {
            if (!Schema::hasColumn('discounts', 'item_id')) {
                Schema::table('discounts', function (Blueprint $table) {
                    $table->unsignedBigInteger('item_id')->nullable()->index()->after('category_id');
                });
            }

            if (Schema::hasColumn('discounts', 'items')) {
                Schema::table('discounts', function (Blueprint $table) {
                    $table->dropColumn('items');
                });
            }
        }
    }
};
