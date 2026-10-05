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
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'note')) {
                    $table->text('note')->nullable()->after('total_amount');
                }
                if (!Schema::hasColumn('purchases', 'terms_conditions')) {
                    $table->longText('terms_conditions')->nullable()->after('total_amount');
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
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (Schema::hasColumn('purchases', 'terms_conditions')) {
                    $table->dropColumn('terms_conditions');
                }
                if (Schema::hasColumn('purchases', 'note')) {
                    $table->dropColumn('note');
                }
            });
        }
    }
};
