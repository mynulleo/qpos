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
                if (!Schema::hasColumn('warranty_claims', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('client_id')->index();
                }
                if (!Schema::hasColumn('warranty_claims', 'supplier_name')) {
                    $table->string('supplier_name', 191)->nullable()->after('supplier_id');
                }
                if (!Schema::hasColumn('warranty_claims', 'purchase_id')) {
                    $table->unsignedBigInteger('purchase_id')->nullable()->after('invoice_detail_id')->index();
                }
                if (!Schema::hasColumn('warranty_claims', 'purchase_no')) {
                    $table->string('purchase_no', 100)->nullable()->after('purchase_id');
                }
                if (!Schema::hasColumn('warranty_claims', 'purchase_date')) {
                    $table->date('purchase_date')->nullable()->after('purchase_no');
                }
                if (!Schema::hasColumn('warranty_claims', 'supplier_warranty_period')) {
                    $table->string('supplier_warranty_period', 100)->nullable()->after('warranty_period');
                }
                if (!Schema::hasColumn('warranty_claims', 'supplier_warranty_expiry_date')) {
                    $table->date('supplier_warranty_expiry_date')->nullable()->after('warranty_expiry_date');
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
                $columns = [
                    'supplier_id',
                    'supplier_name',
                    'purchase_id',
                    'purchase_no',
                    'purchase_date',
                    'supplier_warranty_period',
                    'supplier_warranty_expiry_date',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('warranty_claims', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
