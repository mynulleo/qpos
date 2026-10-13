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
        // 1. Add customer_type to quotations table
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                if (!Schema::hasColumn('quotations', 'customer_type')) {
                    $table->string('customer_type', 30)->default('retail')->after('client_id')->comment('retail, wholesale');
                }
            });
        }

        // 2. Insert default terms and conditions for Quotation module if not present
        if (Schema::hasTable('terms_conditions')) {
            $existing = DB::table('terms_conditions')->where('module_name', 'Quotation')->count();
            if ($existing == 0) {
                $now = now();
                DB::table('terms_conditions')->insert([
                    [
                        'module_name'    => 'Quotation',
                        'condition_text' => 'Quotation is valid for 15 days from the date of issue.',
                        'sorting'        => 1,
                        'is_default'     => 1,
                        'status'         => 'active',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                    [
                        'module_name'    => 'Quotation',
                        'condition_text' => 'Prices are subject to revision after the validity period expires.',
                        'sorting'        => 2,
                        'is_default'     => 1,
                        'status'         => 'active',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                    [
                        'module_name'    => 'Quotation',
                        'condition_text' => 'Delivery will be made within agreed working days after receipt of confirmed work order.',
                        'sorting'        => 3,
                        'is_default'     => 1,
                        'status'         => 'active',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                    [
                        'module_name'    => 'Quotation',
                        'condition_text' => 'Physical signature or official work order is required upon quotation acceptance.',
                        'sorting'        => 4,
                        'is_default'     => 1,
                        'status'         => 'active',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                    [
                        'module_name'    => 'Quotation',
                        'condition_text' => 'Prices are inclusive of applicable VAT & Taxes unless specified otherwise.',
                        'sorting'        => 5,
                        'is_default'     => 0,
                        'status'         => 'active',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ],
                ]);
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
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                if (Schema::hasColumn('quotations', 'customer_type')) {
                    $table->dropColumn('customer_type');
                }
            });
        }
    }
};
