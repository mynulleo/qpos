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
                if (!Schema::hasColumn('invoices', 'payment_id')) {
                    $table->unsignedBigInteger('payment_id')->nullable()->default(null)->after('paid_amount')->index();
                }
                if (!Schema::hasColumn('invoices', 'payment_method')) {
                    $table->string('payment_method', 50)->nullable()->default(null)->after('payment_id');
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
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
                if (Schema::hasColumn('invoices', 'payment_id')) {
                    $table->dropColumn('payment_id');
                }
            });
        }
    }
};
