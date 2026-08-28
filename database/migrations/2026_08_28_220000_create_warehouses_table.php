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
        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('code', 64)->nullable()->unique();
                $table->string('phone', 32)->nullable();
                $table->string('email', 128)->nullable();
                $table->text('address')->nullable();
                $table->string('contact_person', 255)->nullable();
                $table->string('contact_person_phone', 32)->nullable();
                $table->string('capacity', 255)->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->integer('sorting')->default(0);
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
