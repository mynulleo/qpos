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
        if (Schema::hasTable('warranty_claims')) {
            Schema::table('warranty_claims', function (Blueprint $table) {
                if (!Schema::hasColumn('warranty_claims', 'expense_id')) {
                    $table->unsignedBigInteger('expense_id')->nullable()->after('customer_charge')->index();
                }
                if (!Schema::hasColumn('warranty_claims', 'payment_id')) {
                    $table->unsignedBigInteger('payment_id')->nullable()->after('expense_id')->index();
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
        if (Schema::hasTable('warranty_claims')) {
            Schema::table('warranty_claims', function (Blueprint $table) {
                if (Schema::hasColumn('warranty_claims', 'payment_id')) {
                    $table->dropColumn('payment_id');
                }
                if (Schema::hasColumn('warranty_claims', 'expense_id')) {
                    $table->dropColumn('expense_id');
                }
            });
        }
    }
};
