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
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->integer('sorting')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'brand_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('category_id');
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
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'brand_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('brand_id');
            });
        }

        Schema::dropIfExists('brands');
    }
};
