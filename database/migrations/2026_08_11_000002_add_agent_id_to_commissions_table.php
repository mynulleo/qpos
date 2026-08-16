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
        if (Schema::hasTable('commissions') && !Schema::hasColumn('commissions', 'agent_id')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('employee_id');
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
        if (Schema::hasTable('commissions') && Schema::hasColumn('commissions', 'agent_id')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->dropColumn('agent_id');
            });
        }
    }
};
