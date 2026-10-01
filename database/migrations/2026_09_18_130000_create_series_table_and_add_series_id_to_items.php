<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        // 1. Create series table
        if (!Schema::hasTable('series')) {
            Schema::create('series', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('brand_id')->nullable()->index();
                $table->string('title');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->integer('sorting')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Add series_id and ensure model_no exists on items table
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'series_id')) {
                    $table->unsignedBigInteger('series_id')->nullable()->after('brand_id')->index();
                }
            });
        }

        // 3. Insert Permissions for SeriesController
        $parentPerm = DB::table('permissions')->where('name', 'SeriesController')->first();
        if (!$parentPerm) {
            $parentPermId = DB::table('permissions')->insertGetId([
                'name' => 'SeriesController',
                'route' => null,
                'parent_id' => null,
            ]);
        } else {
            $parentPermId = $parentPerm->id;
        }

        $childPermissions = [
            ['name' => 'index', 'route' => 'series.index', 'parent_id' => $parentPermId],
            ['name' => 'create', 'route' => 'series.create', 'parent_id' => $parentPermId],
            ['name' => 'store', 'route' => 'series.store', 'parent_id' => $parentPermId],
            ['name' => 'show', 'route' => 'series.show', 'parent_id' => $parentPermId],
            ['name' => 'edit', 'route' => 'series.edit', 'parent_id' => $parentPermId],
            ['name' => 'update', 'route' => 'series.update', 'parent_id' => $parentPermId],
            ['name' => 'destroy', 'route' => 'series.destroy', 'parent_id' => $parentPermId],
        ];

        $allPermIds = [$parentPermId];

        foreach ($childPermissions as $cp) {
            $existing = DB::table('permissions')->where('route', $cp['route'])->first();
            if (!$existing) {
                $cid = DB::table('permissions')->insertGetId($cp);
                $allPermIds[] = $cid;
            } else {
                $allPermIds[] = $existing->id;
            }
        }

        // 4. Assign permissions to all roles
        $roleIds = DB::table('roles')->pluck('id');
        foreach ($roleIds as $rId) {
            foreach ($allPermIds as $pId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $rId)
                    ->where('permission_id', $pId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $rId,
                        'permission_id' => $pId,
                    ]);
                }
            }
        }

        // 5. Add to menus table (under Master Setup, parent_id: 3)
        $menuExists = DB::table('menus')->where('route_name', 'series.index')->exists();
        if (!$menuExists) {
            DB::table('menus')->insert([
                'menu_name' => 'Series',
                'module_name' => '\\App\\Models\\Series',
                'icon' => "<i class='fas fa-stream'></i>",
                'route_name' => 'series.index',
                'params' => null,
                'parent_id' => 3,
                'sorting' => 4,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menus')->where('route_name', 'series.index')->delete();
        $perm = DB::table('permissions')->where('name', 'SeriesController')->first();
        if ($perm) {
            $childIds = DB::table('permissions')->where('parent_id', $perm->id)->pluck('id')->toArray();
            $allIds = array_merge([$perm->id], $childIds);
            DB::table('role_permissions')->whereIn('permission_id', $allIds)->delete();
            DB::table('permissions')->whereIn('id', $allIds)->delete();
        }

        if (Schema::hasTable('items') && Schema::hasColumn('items', 'series_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('series_id');
            });
        }

        Schema::dropIfExists('series');
    }
};
