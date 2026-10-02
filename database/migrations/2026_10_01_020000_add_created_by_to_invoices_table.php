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
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->default(1)->after('status')->index();
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
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'created_by')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
