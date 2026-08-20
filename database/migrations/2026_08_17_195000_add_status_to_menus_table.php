<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('menus') && !Schema::hasColumn('menus', 'status')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->enum('status', ['active', 'deactive'])->default('active')->after('show_profile');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menus') && Schema::hasColumn('menus', 'status')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
