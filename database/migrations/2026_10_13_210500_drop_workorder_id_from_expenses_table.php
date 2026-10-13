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
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'workorder_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('workorder_id');
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
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'workorder_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->integer('workorder_id')->nullable()->after('expense_date');
            });
        }
    }
};
