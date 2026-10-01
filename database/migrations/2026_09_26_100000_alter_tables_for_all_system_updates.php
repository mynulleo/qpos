<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // -------------------------------------------------------------
        // 1. SITE_SETTINGS TABLE ALTERATIONS
        // -------------------------------------------------------------
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('site_settings', 'shop_type')) {
                    $table->string('shop_type', 50)->default('electronics')->after('title');
                }
                if (!Schema::hasColumn('site_settings', 'printer_pos_type')) {
                    $table->string('printer_pos_type', 50)->default('browser')->after('shop_type');
                }
                if (!Schema::hasColumn('site_settings', 'printer_invoice_type')) {
                    $table->string('printer_invoice_type', 50)->default('browser')->after('printer_pos_type');
                }
                if (!Schema::hasColumn('site_settings', 'printer_pos_width')) {
                    $table->string('printer_pos_width', 20)->default('80mm')->after('printer_invoice_type');
                }
                if (!Schema::hasColumn('site_settings', 'printer_pos_paper_type')) {
                    $table->string('printer_pos_paper_type', 50)->default('thermal')->after('printer_pos_width');
                }
                if (!Schema::hasColumn('site_settings', 'printer_barcode_name')) {
                    $table->string('printer_barcode_name', 100)->nullable()->after('printer_pos_paper_type');
                }
                if (!Schema::hasColumn('site_settings', 'label_preset')) {
                    $table->string('label_preset', 50)->default('single_50x25')->after('printer_barcode_name');
                }
                if (!Schema::hasColumn('site_settings', 'coupon_discount_type')) {
                    $table->string('coupon_discount_type', 20)->nullable()->after('label_preset');
                }
            });
        }

        // -------------------------------------------------------------
        // 2. MENUS TABLE ALTERATIONS
        // -------------------------------------------------------------
        if (Schema::hasTable('menus')) {
            Schema::table('menus', function (Blueprint $table) {
                if (!Schema::hasColumn('menus', 'status')) {
                    $table->enum('status', ['active', 'deactive'])->default('active')->after('sorting');
                }
            });
        }

        // -------------------------------------------------------------
        // 3. BRANDS & SERIES TABLES
        // -------------------------------------------------------------
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->text('image')->nullable();
                $table->text('description')->nullable();
                $table->integer('sorting')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('brands', function (Blueprint $table) {
                if (!Schema::hasColumn('brands', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->index()->after('code');
                }
            });
        }

        if (!Schema::hasTable('series')) {
            Schema::create('series', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->unsignedBigInteger('brand_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->integer('sorting')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // -------------------------------------------------------------
        // 4. ITEMS TABLE ALTERATIONS
        // -------------------------------------------------------------
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'brand_id')) {
                    $table->unsignedBigInteger('brand_id')->nullable()->after('category_id')->index();
                }
                if (!Schema::hasColumn('items', 'series_id')) {
                    $table->unsignedBigInteger('series_id')->nullable()->after('brand_id')->index();
                }
                if (!Schema::hasColumn('items', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->after('series_id')->index();
                }
                if (!Schema::hasColumn('items', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->after('color_id')->index();
                }
                if (!Schema::hasColumn('items', 'shop_type')) {
                    $table->string('shop_type', 50)->default('electronics')->after('unit_id');
                }
                if (!Schema::hasColumn('items', 'warranty_type')) {
                    $table->string('warranty_type', 50)->default('none')->after('description');
                }
                if (!Schema::hasColumn('items', 'warranty_period')) {
                    $table->string('warranty_period', 50)->nullable()->after('warranty_type');
                }
            });
        }

        // -------------------------------------------------------------
        // 5. COLORS, SIZES & ITEM_PRICES TABLES
        // -------------------------------------------------------------
        if (!Schema::hasTable('colors')) {
            Schema::create('colors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->integer('sorting')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('sizes')) {
            Schema::create('sizes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->integer('sorting')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('item_prices')) {
            Schema::create('item_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->string('barcode', 100)->nullable()->index();
                $table->decimal('purchase_price', 12, 2)->default(0.00);
                $table->decimal('sales_price', 12, 2)->default(0.00);
                $table->decimal('mrp_price', 12, 2)->default(0.00);
                $table->decimal('whole_sale_price', 12, 2)->default(0.00);
                $table->decimal('opening_stock', 12, 2)->default(0.00);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // -------------------------------------------------------------
        // 6. PURCHASES & PURCHASE_DETAILS TABLE ALTERATIONS
        // -------------------------------------------------------------
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'receive_status')) {
                    $table->enum('receive_status', ['Pending', 'Partial', 'Received'])->default('Pending')->after('is_closed');
                }
            });
        }

        if (Schema::hasTable('purchase_details')) {
            Schema::table('purchase_details', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_details', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->after('item_id')->index();
                }
                if (!Schema::hasColumn('purchase_details', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->after('color_id')->index();
                }
                if (!Schema::hasColumn('purchase_details', 'item_price_id')) {
                    $table->unsignedBigInteger('item_price_id')->nullable()->after('size_id')->index();
                }
                if (!Schema::hasColumn('purchase_details', 'serial_no')) {
                    $table->text('serial_no')->nullable()->after('item_price_id');
                }
                if (!Schema::hasColumn('purchase_details', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0.00)->after('price');
                }
                if (!Schema::hasColumn('purchase_details', 'warranty_type')) {
                    $table->string('warranty_type', 50)->default('none')->after('selling_price');
                }
                if (!Schema::hasColumn('purchase_details', 'warranty_duration')) {
                    $table->integer('warranty_duration')->nullable()->after('warranty_type');
                }
                if (!Schema::hasColumn('purchase_details', 'warranty_expires_at')) {
                    $table->date('warranty_expires_at')->nullable()->after('warranty_duration');
                }
            });
        }

        // -------------------------------------------------------------
        // 7. INVOICES & INVOICE_DETAILS TABLE ALTERATIONS
        // -------------------------------------------------------------
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'coupon_code')) {
                    $table->string('coupon_code', 50)->nullable()->after('discount');
                }
                if (!Schema::hasColumn('invoices', 'coupon_discount')) {
                    $table->decimal('coupon_discount', 12, 2)->default(0.00)->after('coupon_code');
                }
                if (!Schema::hasColumn('invoices', 'redeemed_points')) {
                    $table->integer('redeemed_points')->default(0)->after('coupon_discount');
                }
                if (!Schema::hasColumn('invoices', 'redeemed_point_amount')) {
                    $table->decimal('redeemed_point_amount', 12, 2)->default(0.00)->after('redeemed_points');
                }
                if (!Schema::hasColumn('invoices', 'earned_points')) {
                    $table->integer('earned_points')->default(0)->after('redeemed_point_amount');
                }
            });
        }

        if (Schema::hasTable('invoice_details')) {
            Schema::table('invoice_details', function (Blueprint $table) {
                if (!Schema::hasColumn('invoice_details', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->after('item_id')->index();
                }
                if (!Schema::hasColumn('invoice_details', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->after('color_id')->index();
                }
                if (!Schema::hasColumn('invoice_details', 'item_price_id')) {
                    $table->unsignedBigInteger('item_price_id')->nullable()->after('size_id')->index();
                }
                if (!Schema::hasColumn('invoice_details', 'serial_no')) {
                    $table->string('serial_no', 255)->nullable()->after('item_price_id');
                }
                if (!Schema::hasColumn('invoice_details', 'warranty_type')) {
                    $table->string('warranty_type', 50)->default('none')->after('serial_no');
                }
                if (!Schema::hasColumn('invoice_details', 'warranty_duration')) {
                    $table->integer('warranty_duration')->nullable()->after('warranty_type');
                }
                if (!Schema::hasColumn('invoice_details', 'warranty_expires_at')) {
                    $table->date('warranty_expires_at')->nullable()->after('warranty_duration');
                }
            });
        }

        // -------------------------------------------------------------
        // 8. WAREHOUSES TABLE
        // -------------------------------------------------------------
        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->boolean('is_default')->default(false);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // -------------------------------------------------------------
        // 9. STOCK_TRANSACTIONS TABLE ALTERATIONS & INDEXES
        // -------------------------------------------------------------
        if (Schema::hasTable('stock_transactions')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_transactions', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id')->index();
                }
                if (!Schema::hasColumn('stock_transactions', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->after('item_id')->index();
                }
                if (!Schema::hasColumn('stock_transactions', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->after('color_id')->index();
                }
            });

            try {
                DB::statement("ALTER TABLE `stock_transactions` MODIFY `transaction_type` VARCHAR(50) NOT NULL");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `stock_transactions` MODIFY `reference_type` VARCHAR(50) NULL");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `stock_transactions` MODIFY `created_ip` VARCHAR(45) NULL DEFAULT '1'");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `stock_transactions` MODIFY `updated_ip` VARCHAR(45) NULL DEFAULT '1'");
            } catch (\Throwable $e) {}

            Schema::table('stock_transactions', function (Blueprint $table) {
                $existingIndexes = collect(DB::select("SHOW INDEX FROM stock_transactions"))->pluck('Key_name')->unique()->toArray();
                if (!in_array('idx_st_status_item_color_size', $existingIndexes)) {
                    $table->index(['status', 'item_id', 'color_id', 'size_id'], 'idx_st_status_item_color_size');
                }
                if (!in_array('idx_st_item_status', $existingIndexes)) {
                    $table->index(['item_id', 'status'], 'idx_st_item_status');
                }
            });
        }

        // -------------------------------------------------------------
        // 10. GRNS & GRN_DETAILS TABLES & ALTERATIONS
        // -------------------------------------------------------------
        if (!Schema::hasTable('grns')) {
            Schema::create('grns', function (Blueprint $table) {
                $table->id();
                $table->string('grn_no', 50)->unique();
                $table->date('grn_date')->index();
                $table->enum('grn_type', ['po', 'supplier', 'direct'])->default('po');
                $table->unsignedBigInteger('purchase_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('challan_no', 100)->nullable();
                $table->date('challan_date')->nullable();
                $table->decimal('sub_total', 12, 2)->default(0.00);
                $table->decimal('discount', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->unsignedBigInteger('fund_account_id')->nullable()->index();
                $table->decimal('due_amount', 12, 2)->default(0.00);
                $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            try {
                DB::statement("ALTER TABLE `grns` MODIFY `purchase_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `grns` MODIFY `supplier_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            Schema::table('grns', function (Blueprint $table) {
                if (!Schema::hasColumn('grns', 'grn_type')) {
                    $table->enum('grn_type', ['po', 'supplier', 'direct'])->default('po')->after('grn_date');
                }
                if (!Schema::hasColumn('grns', 'sub_total')) {
                    $table->decimal('sub_total', 12, 2)->default(0.00)->after('challan_date');
                }
                if (!Schema::hasColumn('grns', 'discount')) {
                    $table->decimal('discount', 12, 2)->default(0.00)->after('sub_total');
                }
                if (!Schema::hasColumn('grns', 'fund_account_id')) {
                    $table->unsignedBigInteger('fund_account_id')->nullable()->index()->after('paid_amount');
                }
            });
        }

        if (!Schema::hasTable('grn_details')) {
            Schema::create('grn_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grn_id')->index();
                $table->unsignedBigInteger('purchase_detail_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->unsignedBigInteger('unit_id')->nullable()->index();
                $table->decimal('ordered_qty', 12, 2)->default(0.00);
                $table->decimal('received_qty', 12, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->decimal('selling_price', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->text('serial_no')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            try {
                DB::statement("ALTER TABLE `grn_details` MODIFY `purchase_detail_id` BIGINT UNSIGNED NULL");
            } catch (\Throwable $e) {}

            Schema::table('grn_details', function (Blueprint $table) {
                if (!Schema::hasColumn('grn_details', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->index()->after('purchase_detail_id');
                }
                if (!Schema::hasColumn('grn_details', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->index()->after('item_id');
                }
                if (!Schema::hasColumn('grn_details', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->index()->after('color_id');
                }
                if (!Schema::hasColumn('grn_details', 'unit_id')) {
                    $table->unsignedBigInteger('unit_id')->nullable()->index()->after('size_id');
                }
                if (!Schema::hasColumn('grn_details', 'unit_price')) {
                    $table->decimal('unit_price', 12, 2)->default(0.00)->after('received_qty');
                }
                if (!Schema::hasColumn('grn_details', 'selling_price')) {
                    $table->decimal('selling_price', 12, 2)->default(0.00)->after('unit_price');
                }
                if (!Schema::hasColumn('grn_details', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->default(0.00)->after('selling_price');
                }
                if (!Schema::hasColumn('grn_details', 'serial_no')) {
                    $table->text('serial_no')->nullable()->after('total_amount');
                }
            });
        }

        // -------------------------------------------------------------
        // 11. STOCK_ADJUSTMENTS & STOCK_ADJUSTMENT_DETAILS
        // -------------------------------------------------------------
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->string('adjustment_no', 50)->unique();
                $table->date('adjustment_date')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->enum('adjustment_type', ['Increase', 'Decrease', 'Reconciliation'])->default('Reconciliation');
                $table->enum('reason', ['Damaged', 'Expired', 'Stock In Hand Mismatch', 'Other'])->default('Stock In Hand Mismatch');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('conducted_by')->nullable();
                $table->integer('total_items')->default(0);
                $table->decimal('total_adjusted_qty', 12, 2)->default(0.00);
                $table->decimal('total_amount', 14, 2)->default(0.00);
                $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('stock_adjustments', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_adjustments', 'total_items')) {
                    $table->integer('total_items')->default(0)->after('conducted_by');
                }
                if (!Schema::hasColumn('stock_adjustments', 'total_adjusted_qty')) {
                    $table->decimal('total_adjusted_qty', 12, 2)->default(0)->after('total_items');
                }
                if (!Schema::hasColumn('stock_adjustments', 'total_amount')) {
                    $table->decimal('total_amount', 14, 2)->default(0)->after('total_adjusted_qty');
                }
                if (!Schema::hasColumn('stock_adjustments', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('branch_id')->index();
                }
            });
        }

        if (!Schema::hasTable('stock_adjustment_details')) {
            Schema::create('stock_adjustment_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stock_adjustment_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->decimal('current_stock', 12, 2)->default(0.00);
                $table->decimal('physical_count', 12, 2)->default(0.00);
                $table->decimal('adjusted_qty', 12, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->decimal('total_amount', 14, 2)->default(0.00);
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('stock_adjustment_details', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_adjustment_details', 'color_id')) {
                    $table->unsignedBigInteger('color_id')->nullable()->after('item_id')->index();
                }
                if (!Schema::hasColumn('stock_adjustment_details', 'size_id')) {
                    $table->unsignedBigInteger('size_id')->nullable()->after('color_id')->index();
                }
                if (!Schema::hasColumn('stock_adjustment_details', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('size_id')->index();
                }
            });
        }

        // -------------------------------------------------------------
        // 12. WARRANTY_CLAIMS & WARRANTY_CLAIM_LOGS TABLES & ALTERATIONS
        // -------------------------------------------------------------
        if (!Schema::hasTable('warranty_claims')) {
            Schema::create('warranty_claims', function (Blueprint $table) {
                $table->id();
                $table->string('claim_no', 50)->unique();
                $table->unsignedBigInteger('invoice_id')->nullable()->index();
                $table->unsignedBigInteger('invoice_detail_id')->nullable()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->string('serial_no', 255)->nullable()->index();
                $table->date('claim_date')->index();
                $table->string('product_condition', 100)->nullable();
                $table->text('accessories_received')->nullable();
                $table->text('problem_description');
                $table->enum('status', [
                    'Received',
                    'Sent to Vendor',
                    'Under Repair',
                    'Repaired',
                    'Replaced',
                    'Delivered to Customer',
                    'Rejected'
                ])->default('Received');
                $table->text('vendor_name')->nullable();
                $table->string('vendor_service_ticket_no', 100)->nullable();
                $table->date('sent_to_vendor_date')->nullable();
                $table->date('received_from_vendor_date')->nullable();
                $table->date('delivered_date')->nullable();
                $table->decimal('customer_charge', 12, 2)->default(0.00);
                $table->decimal('vendor_charge', 12, 2)->default(0.00);
                $table->unsignedBigInteger('expense_id')->nullable()->index();
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->text('action_taken')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('warranty_claims', function (Blueprint $table) {
                if (!Schema::hasColumn('warranty_claims', 'expense_id')) {
                    $table->unsignedBigInteger('expense_id')->nullable()->after('customer_charge')->index();
                }
                if (!Schema::hasColumn('warranty_claims', 'payment_id')) {
                    $table->unsignedBigInteger('payment_id')->nullable()->after('expense_id')->index();
                }
                if (!Schema::hasColumn('warranty_claims', 'customer_charge')) {
                    $table->decimal('customer_charge', 12, 2)->default(0.00)->after('delivered_date');
                }
                if (!Schema::hasColumn('warranty_claims', 'vendor_charge')) {
                    $table->decimal('vendor_charge', 12, 2)->default(0.00)->after('customer_charge');
                }
                if (!Schema::hasColumn('warranty_claims', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('client_id')->index();
                }
            });
        }

        if (!Schema::hasTable('warranty_claim_logs')) {
            Schema::create('warranty_claim_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warranty_claim_id')->index();
                $table->string('status_from', 50)->nullable();
                $table->string('status_to', 50);
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // -------------------------------------------------------------
        // 13. WASTAGES & WASTAGE_DETAILS TABLES
        // -------------------------------------------------------------
        if (!Schema::hasTable('wastages')) {
            Schema::create('wastages', function (Blueprint $table) {
                $table->id();
                $table->string('audit_number', 50)->unique();
                $table->date('audit_date');
                $table->unsignedBigInteger('auditor_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->text('reason')->nullable();
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_date')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('wastage_details')) {
            Schema::create('wastage_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('wastage_id')->index();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('color_id')->nullable()->index();
                $table->unsignedBigInteger('size_id')->nullable()->index();
                $table->decimal('qty', 12, 2)->default(1.00);
                $table->decimal('unit_price', 12, 2)->default(0.00);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // -------------------------------------------------------------
        // 14. SALES_RETURNS & SALES_RETURN_DETAILS TABLES
        // -------------------------------------------------------------
        if (!Schema::hasTable('sales_returns')) {
            Schema::create('sales_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_no', 50)->unique();
                $table->unsignedBigInteger('invoice_id')->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->default(1);
                $table->date('return_date')->index();
                $table->string('return_reason', 50)->default('Client request')->index();
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

        // -------------------------------------------------------------
        // 15. QUOTATIONS & QUOTATION_DETAILS TABLES
        // -------------------------------------------------------------
        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('quotation_no', 50)->unique()->index();
                $table->unsignedBigInteger('client_id')->nullable()->index();
                $table->string('client_name')->nullable();
                $table->string('client_phone')->nullable();
                $table->string('client_email')->nullable();
                $table->text('client_address')->nullable();
                $table->date('quotation_date');
                $table->date('validity_date')->nullable();
                $table->string('subject')->nullable();
                $table->string('reference_no')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->decimal('currency_rate', 10, 4)->default(1.0000);
                $table->decimal('sub_total', 14, 2)->default(0.00);
                $table->string('discount_type', 20)->default('fixed');
                $table->decimal('discount', 12, 2)->default(0.00);
                $table->decimal('discount_amount', 14, 2)->default(0.00);
                $table->decimal('tax_percent', 8, 2)->default(0.00);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('shipping_cost', 14, 2)->default(0.00);
                $table->decimal('total_amount', 14, 2)->default(0.00);
                $table->integer('total_items')->default(0);
                $table->decimal('total_qty', 12, 2)->default(0.00);
                $table->string('status', 30)->default('draft');
                $table->text('payment_terms')->nullable();
                $table->text('delivery_terms')->nullable();
                $table->text('warranty_terms')->nullable();
                $table->longText('terms_conditions')->nullable();
                $table->text('note')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('prepared_by')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('quotation_details')) {
            Schema::create('quotation_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('quotation_id')->index();
                $table->string('item_type', 20)->default('product');
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('item_id')->nullable()->index();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->unsignedBigInteger('color_id')->nullable();
                $table->unsignedBigInteger('size_id')->nullable();
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('unit_name', 50)->default('Pcs');
                $table->decimal('qty', 12, 2)->default(1.00);
                $table->decimal('unit_price', 14, 2)->default(0.00);
                $table->decimal('discount_percent', 8, 2)->default(0.00);
                $table->decimal('discount_amount', 14, 2)->default(0.00);
                $table->decimal('total_price', 14, 2)->default(0.00);
                $table->integer('sorting')->default(0);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // -------------------------------------------------------------
        // 16. AGENTS, COMMISSIONS & PAYMENTS TABLE ALTERATIONS
        // -------------------------------------------------------------
        if (!Schema::hasTable('agents')) {
            Schema::create('agents', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('mobile', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->text('address')->nullable();
                $table->decimal('commission_rate', 5, 2)->default(0.00);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('commissions')) {
            Schema::table('commissions', function (Blueprint $table) {
                if (!Schema::hasColumn('commissions', 'agent_id')) {
                    $table->unsignedBigInteger('agent_id')->nullable()->after('employee_id')->index();
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'agent_id')) {
                    $table->unsignedBigInteger('agent_id')->nullable()->after('supplier_id')->index();
                }
            });
        }

        // -------------------------------------------------------------
        // 17. COUPON_SETTINGS & CLIENT_POINTS TABLES
        // -------------------------------------------------------------
        if (!Schema::hasTable('coupon_settings')) {
            Schema::create('coupon_settings', function (Blueprint $table) {
                $table->id();
                $table->string('coupon_code', 50)->unique();
                $table->string('coupon_name', 100);
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 12, 2)->default(0.00);
                $table->decimal('min_order_amount', 12, 2)->default(0.00);
                $table->decimal('max_discount_amount', 12, 2)->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->enum('status', ['active', 'deactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('client_points')) {
            Schema::create('client_points', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->unique()->index();
                $table->integer('total_points')->default(0);
                $table->integer('redeemed_points')->default(0);
                $table->integer('available_points')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('client_point_transactions')) {
            Schema::create('client_point_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->index();
                $table->unsignedBigInteger('invoice_id')->nullable()->index();
                $table->enum('type', ['earned', 'redeemed', 'adjusted'])->default('earned');
                $table->integer('points')->default(0);
                $table->decimal('equivalent_amount', 12, 2)->default(0.00);
                $table->string('note', 255)->nullable();
                $table->timestamps();
            });
        }

        // -------------------------------------------------------------
        // 18. ITEM_STOCK_SUMMARIES VIEW RECREATION
        // -------------------------------------------------------------
        try {
            DB::statement("DROP VIEW IF EXISTS `item_stock_summaries`");
            DB::statement("
                CREATE VIEW `item_stock_summaries` AS
                SELECT 
                    stock_transactions.item_id AS item_id,
                    stock_transactions.color_id AS color_id,
                    stock_transactions.size_id AS size_id,
                    SUM(stock_transactions.qty_in) AS total_qty_in,
                    SUM(stock_transactions.qty_out) AS total_qty_out,
                    (SUM(stock_transactions.qty_in) - SUM(stock_transactions.qty_out)) AS current_stock
                FROM stock_transactions
                WHERE stock_transactions.status = 'active'
                GROUP BY stock_transactions.item_id, stock_transactions.color_id, stock_transactions.size_id
            ");
        } catch (\Throwable $e) {}

        // -------------------------------------------------------------
        // 19. ENSURE 'SALES RETURN' ACCOUNT HEAD IN ACCOUNTS TABLE
        // -------------------------------------------------------------
        if (Schema::hasTable('accounts')) {
            try {
                $salesReturn = Account::where('system_key_name', 'sales-return')->first();
                if (!$salesReturn) {
                    $incomeParent = Account::where('system_key_name', 'sales-revenue')->first();
                    $parentId = $incomeParent ? $incomeParent->parent_id : 39;

                    Account::create([
                        'parent_id'         => $parentId,
                        'account_code'      => 4150,
                        'account_name'      => 'Sales Return',
                        'account_type'      => 'Income',
                        'default_type'      => 'System',
                        'system_key_name'   => 'sales-return',
                        'balance_type'      => 'Debit',
                        'status'            => 'active',
                    ]);
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reversible actions if required
    }
};
