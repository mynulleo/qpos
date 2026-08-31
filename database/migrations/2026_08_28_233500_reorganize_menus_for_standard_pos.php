<?php

use Illuminate\Database\Migrations\Migration;
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
        // 1. Delete requested excluded menus: Commission, Loan Info, Agent (all occurrences), Workorder
        DB::table('menus')->whereIn('route_name', [
            'commission.index',
            'loanInfo.index',
            'agent.index',
            'report.agentledger',
            'workorder.index'
        ])->delete();

        // Also delete by menu_name for any stray entries
        DB::table('menus')->whereIn('menu_name', [
            'Commission',
            'Loan Info',
            'Agent',
            'Agents',
            'Agent Ledger',
            'Workorder'
        ])->delete();

        // 2. Create or find top-level 'Purchase' parent menu
        $purchaseParent = DB::table('menus')
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('menu_name', 'Purchase')
                  ->orWhere('menu_name', 'Purchase & Procurement');
            })->first();

        if (!$purchaseParent) {
            $purchaseParentId = DB::table('menus')->insertGetId([
                'menu_name' => 'Purchase',
                'module_name' => '\\App\\Models\\Purchase',
                'icon' => "<i class='fas fa-shopping-cart'></i>",
                'route_name' => null,
                'params' => null,
                'parent_id' => null,
                'sorting' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $purchaseParentId = $purchaseParent->id;
            DB::table('menus')->where('id', $purchaseParentId)->update([
                'menu_name' => 'Purchase',
                'sorting' => 3,
                'route_name' => null,
                'icon' => "<i class='fas fa-shopping-cart'></i>"
            ]);
        }

        // 3. Move Purchase Order, GRN, and Suppliers under Purchase Parent
        // Purchase Order
        DB::table('menus')->where('route_name', 'purchase.index')->update([
            'parent_id' => $purchaseParentId,
            'menu_name' => 'Purchase Orders',
            'sorting' => 1
        ]);

        // Goods Receive (GRN)
        DB::table('menus')->where('route_name', 'grn.index')->update([
            'parent_id' => $purchaseParentId,
            'menu_name' => 'Goods Receive (GRN)',
            'sorting' => 2
        ]);

        // Suppliers
        DB::table('menus')->where('route_name', 'supplier.index')->update([
            'parent_id' => $purchaseParentId,
            'menu_name' => 'Suppliers',
            'sorting' => 3
        ]);

        // 4. Organize POS Menu (Parent ID 142 or by name 'POS')
        $posParent = DB::table('menus')->where('id', 142)->first() 
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'POS')->first();

        if ($posParent) {
            DB::table('menus')->where('id', $posParent->id)->update([
                'sorting' => 2,
                'icon' => "<i class='fas fa-cash-register'></i>"
            ]);

            // Children under POS
            DB::table('menus')->where('route_name', 'pos.index')->update(['parent_id' => $posParent->id, 'menu_name' => 'POS Terminal', 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'invoice.index')->update(['parent_id' => $posParent->id, 'menu_name' => 'Sales Invoices', 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'pos.return')->update(['parent_id' => $posParent->id, 'menu_name' => 'Sales Return', 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'client.index')->update(['parent_id' => $posParent->id, 'menu_name' => 'Customers', 'sorting' => 4]);
            DB::table('menus')->where('route_name', 'pos.labelprint')->update(['parent_id' => $posParent->id, 'menu_name' => 'Label Print', 'sorting' => 5]);
            DB::table('menus')->where('route_name', 'challan.index')->update(['parent_id' => $posParent->id, 'menu_name' => 'Delivery Challan', 'sorting' => 6]);
            DB::table('menus')->where('route_name', 'warrantyClaim.index')->update(['parent_id' => $posParent->id, 'menu_name' => 'Warranty Claim', 'sorting' => 7]);
        }

        // 5. Organize Stock & Inventory (Parent ID 141)
        $stockParent = DB::table('menus')->where('id', 141)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Stock & Inventory')->first();

        if ($stockParent) {
            DB::table('menus')->where('id', $stockParent->id)->update([
                'sorting' => 4,
                'icon' => "<i class='fas fa-boxes'></i>"
            ]);

            DB::table('menus')->where('route_name', 'item.index')->update(['parent_id' => $stockParent->id, 'menu_name' => 'Products / Items', 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'bulkdataimport.item')->update(['parent_id' => $stockParent->id, 'menu_name' => 'Item Import', 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'report.availablestock')->update(['parent_id' => $stockParent->id, 'menu_name' => 'Available Stock', 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'report.itemladger')->update(['parent_id' => $stockParent->id, 'menu_name' => 'Item Ledger', 'sorting' => 4]);
            DB::table('menus')->where('route_name', 'wastage.index')->update(['parent_id' => $stockParent->id, 'menu_name' => 'Wastage / Damage', 'sorting' => 5]);
        }

        // 6. Delete empty 'Advance' parent menu (ID 143)
        $advanceParent = DB::table('menus')->where('id', 143)->orWhere('menu_name', 'Advance')->first();
        if ($advanceParent) {
            // Any remaining children reassign or delete
            DB::table('menus')->where('parent_id', $advanceParent->id)->whereNull('route_name')->delete();
            DB::table('menus')->where('id', $advanceParent->id)->delete();
        }

        // 7. Organize Accounts (Parent ID 100)
        $accParent = DB::table('menus')->where('id', 100)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Accounts')->first();

        if ($accParent) {
            DB::table('menus')->where('id', $accParent->id)->update([
                'sorting' => 5,
                'icon' => "<i class='fas fa-money-check-alt'></i>"
            ]);

            DB::table('menus')->where('route_name', 'account.index')->update(['parent_id' => $accParent->id, 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'payment.index')->update(['parent_id' => $accParent->id, 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'voucher.index')->update(['parent_id' => $accParent->id, 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'expense.index')->update(['parent_id' => $accParent->id, 'sorting' => 4]);
            DB::table('menus')->where('route_name', 'fundTransfer.index')->update(['parent_id' => $accParent->id, 'sorting' => 5]);
            DB::table('menus')->where('route_name', 'withdrawl.index')->update(['parent_id' => $accParent->id, 'sorting' => 6]);
        }

        // 8. Organize Ledgers (Parent ID 117)
        $ledgerParent = DB::table('menus')->where('id', 117)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Individual Ledger')->first();

        if ($ledgerParent) {
            DB::table('menus')->where('id', $ledgerParent->id)->update([
                'menu_name' => 'Ledgers',
                'sorting' => 6,
                'icon' => "<i class='fas fa-book'></i>"
            ]);

            DB::table('menus')->where('route_name', 'report.clientledger')->update(['parent_id' => $ledgerParent->id, 'menu_name' => 'Customer Ledger', 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'report.supplierledger')->update(['parent_id' => $ledgerParent->id, 'menu_name' => 'Supplier Ledger', 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'report.employeeledger')->update(['parent_id' => $ledgerParent->id, 'menu_name' => 'Employee Ledger', 'sorting' => 3]);
        }

        // 9. Organize Reports (Parent ID 94)
        $reportParent = DB::table('menus')->where('id', 94)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Reports')->first();

        if ($reportParent) {
            DB::table('menus')->where('id', $reportParent->id)->update([
                'sorting' => 7,
                'icon' => "<i class='fas fa-chart-line'></i>"
            ]);

            DB::table('menus')->where('route_name', 'report.sales')->update(['parent_id' => $reportParent->id, 'menu_name' => 'Sales Report', 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'report.incomestatement')->update(['parent_id' => $reportParent->id, 'menu_name' => 'Profit & Loss', 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'report.cashbook')->update(['parent_id' => $reportParent->id, 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'report.ledger')->update(['parent_id' => $reportParent->id, 'sorting' => 4]);
            DB::table('menus')->where('route_name', 'report.trialbalance')->update(['parent_id' => $reportParent->id, 'sorting' => 5]);
            DB::table('menus')->where('route_name', 'report.receivable')->update(['parent_id' => $reportParent->id, 'sorting' => 6]);
            DB::table('menus')->where('route_name', 'report.payable')->update(['parent_id' => $reportParent->id, 'sorting' => 7]);
            DB::table('menus')->where('route_name', 'report.balancesheet')->update(['parent_id' => $reportParent->id, 'sorting' => 8]);
            DB::table('menus')->where('route_name', 'report.expensestatement')->update(['parent_id' => $reportParent->id, 'sorting' => 9]);
            DB::table('menus')->where('route_name', 'report.serial')->update(['parent_id' => $reportParent->id, 'sorting' => 10]);
            DB::table('menus')->where('route_name', 'report.warrantyclaim')->update(['parent_id' => $reportParent->id, 'sorting' => 11]);
            DB::table('menus')->where('route_name', 'report.funds')->update(['parent_id' => $reportParent->id, 'sorting' => 12]);
            DB::table('menus')->where('route_name', 'report.coupon')->update(['parent_id' => $reportParent->id, 'sorting' => 13]);
        }

        // 10. Organize HR (Parent ID 138)
        $hrParent = DB::table('menus')->where('id', 138)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'HR')->first();

        if ($hrParent) {
            DB::table('menus')->where('id', $hrParent->id)->update([
                'sorting' => 8,
                'icon' => "<i class='fas fa-user-tie'></i>"
            ]);

            DB::table('menus')->where('route_name', 'employee.index')->update(['parent_id' => $hrParent->id, 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'designation.index')->update(['parent_id' => $hrParent->id, 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'salarySheet.index')->update(['parent_id' => $hrParent->id, 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'salaryParameter.index')->update(['parent_id' => $hrParent->id, 'sorting' => 4]);
        }

        // 11. Organize Master Setup (Parent ID 3)
        $masterParent = DB::table('menus')->where('id', 3)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'Master Setup')->first();

        if ($masterParent) {
            DB::table('menus')->where('id', $masterParent->id)->update([
                'sorting' => 9,
                'icon' => "<i class='fas fa-cogs'></i>"
            ]);

            DB::table('menus')->where('route_name', 'warehouse.index')->update(['parent_id' => $masterParent->id, 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'category.index')->update(['parent_id' => $masterParent->id, 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'brand.index')->update(['parent_id' => $masterParent->id, 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'unit.index')->update(['parent_id' => $masterParent->id, 'sorting' => 4]);
            DB::table('menus')->where('route_name', 'color.index')->update(['parent_id' => $masterParent->id, 'sorting' => 5]);
            DB::table('menus')->where('route_name', 'size.index')->update(['parent_id' => $masterParent->id, 'sorting' => 6]);
            DB::table('menus')->where('route_name', 'financialYear.index')->update(['parent_id' => $masterParent->id, 'sorting' => 7]);
            DB::table('menus')->where('route_name', 'bank.index')->update(['parent_id' => $masterParent->id, 'sorting' => 8]);
            DB::table('menus')->where('route_name', 'district.index')->update(['parent_id' => $masterParent->id, 'sorting' => 9]);
            DB::table('menus')->where('route_name', 'area.index')->update(['parent_id' => $masterParent->id, 'sorting' => 10]);
            DB::table('menus')->where('route_name', 'currency.index')->update(['parent_id' => $masterParent->id, 'sorting' => 11]);
            DB::table('menus')->where('route_name', 'mediaValidator.index')->update(['parent_id' => $masterParent->id, 'sorting' => 12]);
            DB::table('menus')->where('route_name', 'helpInfo.index')->update(['parent_id' => $masterParent->id, 'sorting' => 13]);
        }

        // 12. Organize System Settings (Parent ID 19)
        $sysParent = DB::table('menus')->where('id', 19)->first()
            ?? DB::table('menus')->whereNull('parent_id')->where('menu_name', 'System Settings')->first();

        if ($sysParent) {
            DB::table('menus')->where('id', $sysParent->id)->update([
                'sorting' => 10,
                'icon' => "<i class='fas fa-sliders-h'></i>"
            ]);

            DB::table('menus')->where('route_name', 'role.index')->update(['parent_id' => $sysParent->id, 'sorting' => 1]);
            DB::table('menus')->where('route_name', 'menu.index')->update(['parent_id' => $sysParent->id, 'sorting' => 2]);
            DB::table('menus')->where('route_name', 'siteSetting.show')->update(['parent_id' => $sysParent->id, 'sorting' => 3]);
            DB::table('menus')->where('route_name', 'module.create')->update(['parent_id' => $sysParent->id, 'sorting' => 4]);
        }

        // 13. Dashboard sorting
        DB::table('menus')->where('route_name', 'dashboard.index')->update(['sorting' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No-op rollback
    }
};
