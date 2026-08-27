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
        if (!Schema::hasTable('wastages')) {
            Schema::create('wastages', function (Blueprint $table) {
                $table->id();
                $table->string('audit_number', 64)->unique();
                $table->date('audit_date');
                $table->string('audited_by', 255)->nullable();
                $table->unsignedBigInteger('auditor_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->decimal('total_qty', 12, 2)->default(0);
                $table->decimal('total_loss_amount', 12, 2)->default(0);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable()->index();
                $table->dateTime('approved_date')->nullable();
                
                // System tracking & Soft deletes
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->string('created_ip', 45)->nullable();
                $table->string('updated_ip', 45)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wastage_details')) {
            Schema::create('wastage_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('wastage_id')->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->decimal('quantity', 12, 2)->default(0);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('reason', 255)->nullable();
                $table->date('expired_date')->nullable();
                $table->text('serial_no')->nullable();
                $table->text('remarks')->nullable();
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('wastage_id')
                    ->references('id')
                    ->on('wastages')
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
        Schema::dropIfExists('wastage_details');
        Schema::dropIfExists('wastages');
    }
};
