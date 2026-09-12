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
        // 1. Create sales_returns table
        if (!Schema::hasTable('sales_returns')) {
            Schema::create('sales_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_no', 50)->unique();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->default(1);
                $table->date('return_date')->index();
                $table->string('return_reason', 50)->default('Client request')->index(); // 'Client request', 'Wastage', 'Date Expaired'
                $table->text('note')->nullable();
                $table->string('payment_method', 50)->default('Cash')->index();
                $table->string('mbanking_type', 50)->nullable();
                $table->string('trxid', 100)->nullable();
                $table->decimal('total_qty', 12, 2)->default(0);
                $table->decimal('total_refund_amount', 12, 2)->default(0);
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->unsignedBigInteger('wastage_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->default(1);
                $table->unsignedBigInteger('updated_by')->nullable()->default(1);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Create sales_return_details table
        if (!Schema::hasTable('sales_return_details')) {
            Schema::create('sales_return_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_return_id')->index();
                $table->unsignedBigInteger('invoice_detail_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->decimal('qty', 12, 2)->default(0);
                $table->decimal('rate', 12, 2)->default(0);
                $table->decimal('refund_amount', 12, 2)->default(0);
                $table->string('return_reason', 50)->nullable();
                $table->string('serial_no', 100)->nullable();
                $table->text('note')->nullable();
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Add Menu entry under Reports (Parent ID 94)
        $reportParent = DB::table('menus')->where('id', 94)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Reports')->first();

        if ($reportParent) {
            $existingMenu = DB::table('menus')->where('route_name', 'report.salesReturn')->first();
            if (!$existingMenu) {
                $maxSorting = DB::table('menus')->where('parent_id', $reportParent->id)->max('sorting') ?? 13;
                DB::table('menus')->insert([
                    'parent_id'     => $reportParent->id,
                    'menu_name'     => 'Sales Return Report',
                    'icon'          => "<i class='fas fa-undo-alt'></i>",
                    'route_name'    => 'report.salesReturn',
                    'params'        => null,
                    'sorting'       => $maxSorting + 1,
                    'show_dasboard' => 0,
                    'show_profile'  => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // 4. Add Permissions
        $existingPerm = DB::table('permissions')->where('route', 'report.salesReturn')->first();
        if (!$existingPerm) {
            $permissionParentId = 459; // Report parent permission group ID if exists
            $parentGroup = DB::table('permissions')->where('id', 459)->first()
                ?? DB::table('permissions')->where('name', 'like', '%report%')->first();
            $pId = $parentGroup ? $parentGroup->id : null;

            DB::table('permissions')->insert([
                'name'        => 'salesReturn',
                'route'       => 'report.salesReturn',
                'parent_id'   => $pId,
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
        Schema::dropIfExists('sales_return_details');
        Schema::dropIfExists('sales_returns');
        DB::table('menus')->where('route_name', 'report.salesReturn')->delete();
        DB::table('permissions')->where('route_name', 'report.salesReturn')->delete();
    }
};
