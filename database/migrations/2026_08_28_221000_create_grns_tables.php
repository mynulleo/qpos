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
        // 1. Add receive_status to purchases table if not exists
        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'receive_status')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->enum('receive_status', ['Pending', 'Partial', 'Received'])->default('Pending')->after('is_closed');
            });
        }

        // 2. Create grns master table
        if (!Schema::hasTable('grns')) {
            Schema::create('grns', function (Blueprint $table) {
                $table->id();
                $table->string('grn_no', 64)->unique();
                $table->date('grn_date');
                $table->unsignedBigInteger('purchase_id')->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->string('challan_no', 100)->nullable();
                $table->date('challan_date')->nullable();
                $table->decimal('total_qty', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->tinyInteger('is_closed')->default(0);
                $table->string('received_by', 255)->nullable();
                $table->text('note')->nullable();
                $table->enum('status', ['active', 'deactive'])->default('active');

                // Tracking & soft deletes
                $table->unsignedBigInteger('created_by')->nullable()->default(1);
                $table->unsignedBigInteger('updated_by')->nullable()->default(1);
                $table->string('created_ip', 45)->nullable()->default('1');
                $table->string('updated_ip', 45)->nullable()->default('1');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 3. Create grn_details table
        if (!Schema::hasTable('grn_details')) {
            Schema::create('grn_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grn_id')->index();
                $table->unsignedBigInteger('purchase_detail_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->decimal('ordered_qty', 12, 2)->default(0.00);
                $table->decimal('previously_received_qty', 12, 2)->default(0.00);
                $table->decimal('received_qty', 12, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->decimal('selling_price', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->text('serial_no')->nullable();
                $table->text('note')->nullable();
                $table->enum('status', ['active', 'deactive'])->default('active');

                $table->softDeletes();
                $table->timestamps();

                $table->foreign('grn_id')
                    ->references('id')
                    ->on('grns')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('grn_details');
        Schema::dropIfExists('grns');

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'receive_status')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('receive_status');
            });
        }
    }
};
