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
        if (!Schema::hasTable('warranty_claims')) {
            Schema::create('warranty_claims', function (Blueprint $table) {
                $table->id();
                $table->string('claim_no', 64)->unique();
                $table->string('serial_no', 191)->nullable()->index();
                $table->unsignedBigInteger('invoice_id')->nullable()->index();
                $table->unsignedBigInteger('invoice_detail_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->unsignedBigInteger('color_id')->nullable();
                $table->unsignedBigInteger('size_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                
                // Customer Information Snapshot
                $table->string('customer_name', 191)->nullable();
                $table->string('customer_mobile', 50)->nullable();
                $table->text('customer_address')->nullable();
                
                // Policy & Dates
                $table->string('warranty_type', 50)->default('warranty');
                $table->string('warranty_period', 100)->nullable();
                $table->date('sale_date')->nullable();
                $table->date('claim_date')->nullable();
                $table->date('warranty_expiry_date')->nullable();
                
                // Issue & Status
                $table->text('problem_description')->nullable();
                $table->text('accessories_received')->nullable();
                $table->string('current_status', 50)->default('received');
                $table->date('expected_delivery_date')->nullable();
                $table->date('delivered_date')->nullable();
                
                // Financials
                $table->decimal('service_cost', 12, 2)->default(0);
                $table->decimal('customer_charge', 12, 2)->default(0);
                $table->text('remarks')->nullable();
                
                // Standard System Fields
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->string('created_ip', 45)->nullable();
                $table->string('updated_ip', 45)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warranty_claim_logs')) {
            Schema::create('warranty_claim_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warranty_claim_id')->index();
                $table->string('status', 50);
                $table->text('remarks')->nullable();
                $table->date('reminder_date')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('created_ip', 45)->nullable();
                $table->timestamps();

                $table->foreign('warranty_claim_id')
                    ->references('id')
                    ->on('warranty_claims')
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
        Schema::dropIfExists('warranty_claim_logs');
        Schema::dropIfExists('warranty_claims');
    }
};
