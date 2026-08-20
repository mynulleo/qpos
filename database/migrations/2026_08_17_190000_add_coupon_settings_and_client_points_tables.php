<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponSettingsAndClientPointsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add coupon settings to site_settings table
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'coupon_enabled')) {
                $table->tinyInteger('coupon_enabled')->default(0)->after('system_mode');
            }
            if (!Schema::hasColumn('site_settings', 'point_earn_rate')) {
                $table->decimal('point_earn_rate', 10, 4)->default(1.0000)->after('coupon_enabled')->comment('How many points earned per 1 Tk spent');
            }
            if (!Schema::hasColumn('site_settings', 'point_redeem_rate')) {
                $table->decimal('point_redeem_rate', 10, 4)->default(10.0000)->after('point_earn_rate')->comment('How many points needed to redeem 1 Tk discount');
            }
            if (!Schema::hasColumn('site_settings', 'min_points_to_redeem')) {
                $table->integer('min_points_to_redeem')->default(10)->after('point_redeem_rate')->comment('Minimum points threshold to redeem');
            }
        });

        // 2. Add points_balance to clients table
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'points_balance')) {
                $table->decimal('points_balance', 12, 2)->default(0.00)->after('previous_due');
            }
        });

        // 3. Create client_point_transactions table for ledger history
        if (!Schema::hasTable('client_point_transactions')) {
            Schema::create('client_point_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->index();
                $table->unsignedBigInteger('invoice_id')->nullable()->index();
                $table->string('type', 30)->default('Earn')->comment('Earn, Redeem, Convert_To_Cash, Adjustment, Refund');
                $table->decimal('points_in', 10, 2)->default(0.00);
                $table->decimal('points_out', 10, 2)->default(0.00);
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->decimal('rate', 10, 4)->default(1.0000);
                $table->decimal('amount_equivalent', 12, 2)->default(0.00)->comment('Monetary value in Tk');
                $table->string('description', 255)->nullable();
                $table->date('transaction_date')->nullable();
                $table->string('status', 20)->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->string('created_ip', 50)->nullable();
                $table->string('updated_ip', 50)->nullable();
                $table->softDeletes();
                $table->timestamps();
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
        Schema::dropIfExists('client_point_transactions');

        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'points_balance')) {
                $table->dropColumn('points_balance');
            }
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $columns = ['coupon_enabled', 'point_earn_rate', 'point_redeem_rate', 'min_points_to_redeem'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
