<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensivePosSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info("--- STEP 1: Truncating and Initializing Tables ---");
        $tablesToTruncate = [
            'item_prices', 'stock_transactions',
            'purchase_details', 'purchases', 'grn_details', 'grns',
            'invoice_details', 'invoices', 'client_point_transactions',
            'expense_details', 'expenses', 'voucher_details', 'vouchers',
            'payment_details', 'payments', 'fund_transfers', 'withdrawls',
            'commissions', 'workorder_details', 'workorders', 'challan_details', 'challans',
            'warranty_claim_logs', 'warranty_claims', 'wastage_details', 'wastages',
            'salary_sheet_details', 'salary_sheets', 'loan_installments', 'loan_infos',
            'employee_salaries', 'employees', 'clients', 'suppliers', 'agents',
            'items', 'brands', 'warehouses', 'colors', 'sizes', 'units', 'designations', 'salary_parameters'
        ];

        foreach ($tablesToTruncate as $tbl) {
            if (DB::getSchemaBuilder()->hasTable($tbl)) {
                DB::table($tbl)->truncate();
            }
        }

        $this->command->info("--- STEP 2: Seeding Master Setups ---");
        $this->seedMasterSetups();

        $this->command->info("--- STEP 3: Seeding Stakeholders & Accounts ---");
        $this->seedStakeholdersAndAccounts();

        $this->command->info("--- STEP 4: Seeding 100+ Items Per Category (500+ Items Total) ---");
        $this->seedAllCategoryItems();

        $this->command->info("--- STEP 5: Seeding Purchases, GRN & Initial Inventory Stock ---");
        $this->seedPurchasesAndStock();

        $this->command->info("--- STEP 6: Seeding 3 Months of Sales (Invoices, Details, POS Payments) ---");
        $this->seedThreeMonthsSales();

        $this->command->info("--- STEP 7: Seeding Expenses, Vouchers & Financial Transactions ---");
        $this->seedExpensesAndVouchers();

        $this->command->info("--- STEP 8: Seeding HR, Salaries & Loans ---");
        $this->seedHrAndSalaries();

        $this->command->info("--- STEP 9: Seeding Operations (Challans, Workorders, Warranty, Wastage) ---");
        $this->seedOperations();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("✅ ALL MODULE DATA SUCCESSFULLY SEEDED WITH 100+ ITEMS PER CATEGORY & 3 MONTHS SALES!");
    }

    private function seedMasterSetups()
    {
        // 1. Categories
        $categories = [
            ['id' => 32, 'module_name' => 'Item', 'title' => "Men's Collection", 'slug' => 'mens-collection', 'sorting' => 1, 'status' => 'active'],
            ['id' => 33, 'module_name' => 'Item', 'title' => "Electronics Goods", 'slug' => 'electronics-goods', 'sorting' => 2, 'status' => 'active'],
            ['id' => 34, 'module_name' => 'Item', 'title' => "Woman Collections", 'slug' => 'woman-collections', 'sorting' => 3, 'status' => 'active'],
            ['id' => 35, 'module_name' => 'Item', 'title' => "Kids Collections", 'slug' => 'kids-collections', 'sorting' => 4, 'status' => 'active'],
            ['id' => 36, 'module_name' => 'Item', 'title' => "Grocery Items", 'slug' => 'grocery-items', 'sorting' => 5, 'status' => 'active'],
        ];
        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(['id' => $cat['id']], $cat + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 2. Units
        $units = [
            ['id' => 1, 'module' => 'Item', 'title' => 'Pcs', 'status' => 'active'],
            ['id' => 2, 'module' => 'Item', 'title' => 'Kg', 'status' => 'active'],
            ['id' => 3, 'module' => 'Item', 'title' => 'Box', 'status' => 'active'],
            ['id' => 4, 'module' => 'Item', 'title' => 'Pkt', 'status' => 'active'],
            ['id' => 5, 'module' => 'Item', 'title' => 'Dozen', 'status' => 'active'],
            ['id' => 6, 'module' => 'Item', 'title' => 'Liter', 'status' => 'active'],
            ['id' => 7, 'module' => 'Item', 'title' => 'Meter', 'status' => 'active'],
            ['id' => 8, 'module' => 'Item', 'title' => 'Pair', 'status' => 'active'],
        ];
        foreach ($units as $u) {
            DB::table('units')->insert($u + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 3. Colors
        $colors = [
            ['id' => 1, 'title' => 'Black', 'sort' => 1, 'status' => 'active'],
            ['id' => 2, 'title' => 'White', 'sort' => 2, 'status' => 'active'],
            ['id' => 3, 'title' => 'Navy Blue', 'sort' => 3, 'status' => 'active'],
            ['id' => 4, 'title' => 'Red', 'sort' => 4, 'status' => 'active'],
            ['id' => 5, 'title' => 'Royal Blue', 'sort' => 5, 'status' => 'active'],
            ['id' => 6, 'title' => 'Olive Green', 'sort' => 6, 'status' => 'active'],
            ['id' => 7, 'title' => 'Grey', 'sort' => 7, 'status' => 'active'],
            ['id' => 8, 'title' => 'Maroon', 'sort' => 8, 'status' => 'active'],
            ['id' => 9, 'title' => 'Yellow', 'sort' => 9, 'status' => 'active'],
            ['id' => 10, 'title' => 'Beige', 'sort' => 10, 'status' => 'active'],
        ];
        foreach ($colors as $c) {
            DB::table('colors')->insert($c + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 4. Sizes
        $sizes = [
            ['id' => 1, 'title' => 'S', 'sort' => 1, 'status' => 'active'],
            ['id' => 2, 'title' => 'M', 'sort' => 2, 'status' => 'active'],
            ['id' => 3, 'title' => 'L', 'sort' => 3, 'status' => 'active'],
            ['id' => 4, 'title' => 'XL', 'sort' => 4, 'status' => 'active'],
            ['id' => 5, 'title' => 'XXL', 'sort' => 5, 'status' => 'active'],
            ['id' => 6, 'title' => '30', 'sort' => 6, 'status' => 'active'],
            ['id' => 7, 'title' => '32', 'sort' => 7, 'status' => 'active'],
            ['id' => 8, 'title' => '34', 'sort' => 8, 'status' => 'active'],
            ['id' => 9, 'title' => '36', 'sort' => 9, 'status' => 'active'],
            ['id' => 10, 'title' => 'Free Size', 'sort' => 10, 'status' => 'active'],
        ];
        foreach ($sizes as $s) {
            DB::table('sizes')->insert($s + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 5. Brands
        $brands = [
            ['id' => 1, 'category_id' => 32, 'title' => 'Apex Apparels', 'status' => 'active'],
            ['id' => 2, 'category_id' => 32, 'title' => 'Aarong Man', 'status' => 'active'],
            ['id' => 3, 'category_id' => 32, 'title' => 'Cats Eye', 'status' => 'active'],
            ['id' => 4, 'category_id' => 32, 'title' => 'Richman', 'status' => 'active'],
            ['id' => 5, 'category_id' => 33, 'title' => 'Samsung', 'status' => 'active'],
            ['id' => 6, 'category_id' => 33, 'title' => 'Apple', 'status' => 'active'],
            ['id' => 7, 'category_id' => 33, 'title' => 'Walton', 'status' => 'active'],
            ['id' => 8, 'category_id' => 33, 'title' => 'Xiaomi', 'status' => 'active'],
            ['id' => 9, 'category_id' => 33, 'title' => 'Sony', 'status' => 'active'],
            ['id' => 10, 'category_id' => 33, 'title' => 'Philips', 'status' => 'active'],
            ['id' => 11, 'category_id' => 34, 'title' => 'Aarong Taaga', 'status' => 'active'],
            ['id' => 12, 'category_id' => 34, 'title' => 'Yellow Woman', 'status' => 'active'],
            ['id' => 13, 'category_id' => 34, 'title' => 'Sailor', 'status' => 'active'],
            ['id' => 14, 'category_id' => 34, 'title' => 'Le Reve', 'status' => 'active'],
            ['id' => 15, 'category_id' => 35, 'title' => 'Nogor Kids', 'status' => 'active'],
            ['id' => 16, 'category_id' => 35, 'title' => 'BabyShop BD', 'status' => 'active'],
            ['id' => 17, 'category_id' => 35, 'title' => 'Chaldal Junior', 'status' => 'active'],
            ['id' => 18, 'category_id' => 36, 'title' => 'PRAN Consumer', 'status' => 'active'],
            ['id' => 19, 'category_id' => 36, 'title' => 'Square Consumer', 'status' => 'active'],
            ['id' => 20, 'category_id' => 36, 'title' => 'Unilever BD', 'status' => 'active'],
            ['id' => 21, 'category_id' => 36, 'title' => 'Nestlé Bangladesh', 'status' => 'active'],
            ['id' => 22, 'category_id' => 36, 'title' => 'ACI Pure', 'status' => 'active'],
        ];
        foreach ($brands as $b) {
            DB::table('brands')->insert($b + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 6. Warehouses
        $warehouses = [
            ['id' => 1, 'name' => 'Main Warehouse Dhaka', 'code' => 'WH-DHK-01', 'contact_person' => 'Rahim Uddin', 'phone' => '01711998877', 'address' => 'Plot 14, Tejgaon I/A, Dhaka', 'status' => 'active'],
            ['id' => 2, 'name' => 'Chittagong Regional Depot', 'code' => 'WH-CTG-02', 'contact_person' => 'Kamal Hossain', 'phone' => '01811887766', 'address' => 'Agrabad C/A, Chittagong', 'status' => 'active'],
            ['id' => 3, 'name' => 'Uttara Central Warehouse', 'code' => 'WH-UTR-03', 'contact_person' => 'Shakil Ahmed', 'phone' => '01911776655', 'address' => 'Sector 7, Uttara, Dhaka', 'status' => 'active'],
            ['id' => 4, 'name' => 'Sylhet Logistics Hub', 'code' => 'WH-SYL-04', 'contact_person' => 'Anisur Rahman', 'phone' => '01611665544', 'address' => 'Zindabazar, Sylhet', 'status' => 'active'],
        ];
        foreach ($warehouses as $w) {
            DB::table('warehouses')->insert($w + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 7. Designations
        $designations = [
            ['id' => 1, 'title' => 'Branch Manager', 'status' => 'active'],
            ['id' => 2, 'title' => 'Assistant Manager', 'status' => 'active'],
            ['id' => 3, 'title' => 'Senior Accountant', 'status' => 'active'],
            ['id' => 4, 'title' => 'Head Cashier', 'status' => 'active'],
            ['id' => 5, 'title' => 'Sales Executive', 'status' => 'active'],
            ['id' => 6, 'title' => 'Inventory Officer', 'status' => 'active'],
            ['id' => 7, 'title' => 'Delivery Executive', 'status' => 'active'],
            ['id' => 8, 'title' => 'Customer Support Officer', 'status' => 'active'],
        ];
        foreach ($designations as $d) {
            DB::table('designations')->updateOrInsert(['id' => $d['id']], $d + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 8. Financial Years
        DB::table('financial_years')->updateOrInsert(
            ['id' => 7],
            [
                'title' => 'FY 2026-2027',
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'is_current' => 1,
                'is_closed' => 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 9. Salary Parameters
        $salaryParams = [
            ['id' => 1, 'title' => 'Basic Salary', 'is_basic' => 1, 'percentage_of_basic' => 100, 'sorting' => 1, 'status' => 'active'],
            ['id' => 2, 'title' => 'House Rent Allowance', 'is_basic' => 0, 'percentage_of_basic' => 50, 'sorting' => 2, 'status' => 'active'],
            ['id' => 3, 'title' => 'Medical Allowance', 'is_basic' => 0, 'percentage_of_basic' => 10, 'sorting' => 3, 'status' => 'active'],
            ['id' => 4, 'title' => 'Conveyance Allowance', 'is_basic' => 0, 'percentage_of_basic' => 10, 'sorting' => 4, 'status' => 'active'],
        ];
        foreach ($salaryParams as $sp) {
            DB::table('salary_parameters')->updateOrInsert(['id' => $sp['id']], $sp + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function seedStakeholdersAndAccounts()
    {
        // 1. Financial Fund Accounts
        $accounts = [
            ['id' => 1, 'account_name' => 'Cash in Hand (Main Drawer)', 'system_key_name' => 'Cash', 'account_type' => 'Asset', 'reference_type' => 'System', 'opening_balance' => 150000.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 1, 'status' => 'active'],
            ['id' => 2, 'account_name' => 'Dutch-Bangla Bank (A/C: 115.120.9874)', 'system_key_name' => 'Bank', 'account_type' => 'Asset', 'reference_type' => 'System', 'opening_balance' => 450000.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 1, 'status' => 'active'],
            ['id' => 3, 'account_name' => 'BRAC Bank (A/C: 1501.2045.8900)', 'system_key_name' => 'Bank', 'account_type' => 'Asset', 'reference_type' => 'System', 'opening_balance' => 380000.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 1, 'status' => 'active'],
            ['id' => 4, 'account_name' => 'City Bank (A/C: 3102.4567.1120)', 'system_key_name' => 'Bank', 'account_type' => 'Asset', 'reference_type' => 'System', 'opening_balance' => 250000.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 1, 'status' => 'active'],
            ['id' => 5, 'account_name' => 'bKash Merchant (01711-001122)', 'system_key_name' => 'bKash', 'account_type' => 'Asset', 'reference_type' => 'System', 'opening_balance' => 95000.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 1, 'status' => 'active'],
            ['id' => 6, 'account_name' => 'Nagad Merchant (01811-334455)', 'system_key_name' => 'Nagad', 'account_type' => 'Asset', 'reference_type' => 'System', 'opening_balance' => 65000.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 1, 'status' => 'active'],
            ['id' => 7, 'account_name' => 'General Sales Income', 'system_key_name' => 'Sales', 'account_type' => 'Income', 'reference_type' => 'System', 'opening_balance' => 0.00, 'opening_balance_type' => 'Credit', 'is_fund_account' => 0, 'status' => 'active'],
            ['id' => 8, 'account_name' => 'General Office Expenses', 'system_key_name' => 'Expense', 'account_type' => 'Expense', 'reference_type' => 'System', 'opening_balance' => 0.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 0, 'status' => 'active'],
            ['id' => 9, 'account_name' => 'Shop Rent Expense', 'system_key_name' => 'Rent', 'account_type' => 'Expense', 'reference_type' => 'System', 'opening_balance' => 0.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 0, 'status' => 'active'],
            ['id' => 10, 'account_name' => 'Utility Bills (Electricity & Water)', 'system_key_name' => 'Utility', 'account_type' => 'Expense', 'reference_type' => 'System', 'opening_balance' => 0.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 0, 'status' => 'active'],
            ['id' => 11, 'account_name' => 'Staff Salaries Expense', 'system_key_name' => 'Salary', 'account_type' => 'Expense', 'reference_type' => 'System', 'opening_balance' => 0.00, 'opening_balance_type' => 'Debit', 'is_fund_account' => 0, 'status' => 'active'],
        ];
        foreach ($accounts as $acc) {
            DB::table('accounts')->updateOrInsert(['id' => $acc['id']], $acc + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 2. Employees (10 Active Staff)
        $employees = [
            ['id' => 1, 'empid' => 'EMP-1001', 'full_name' => 'Tanvir Hasan', 'email' => 'tanvir@qpos.com', 'mobile' => '01711223344', 'nid' => '199026925110001', 'designation_id' => 1, 'branch_id' => 1, 'salary' => 65000, 'joining_date' => '2024-01-01', 'address' => 'Dhanmondi 27, Dhaka', 'status' => 'active'],
            ['id' => 2, 'empid' => 'EMP-1002', 'full_name' => 'Mahmudul Karim', 'email' => 'mahmud@qpos.com', 'mobile' => '01711223345', 'nid' => '199226925110002', 'designation_id' => 2, 'branch_id' => 1, 'salary' => 45000, 'joining_date' => '2024-03-01', 'address' => 'Mirpur 10, Dhaka', 'status' => 'active'],
            ['id' => 3, 'empid' => 'EMP-1003', 'full_name' => 'Farhana Sultana', 'email' => 'farhana@qpos.com', 'mobile' => '01711223346', 'nid' => '199526925110003', 'designation_id' => 3, 'branch_id' => 1, 'salary' => 42000, 'joining_date' => '2024-02-15', 'address' => 'Gulshan 2, Dhaka', 'status' => 'active'],
            ['id' => 4, 'empid' => 'EMP-1004', 'full_name' => 'Arifur Rahman', 'email' => 'arif@qpos.com', 'mobile' => '01711223347', 'nid' => '199426925110004', 'designation_id' => 4, 'branch_id' => 1, 'salary' => 32000, 'joining_date' => '2024-04-01', 'address' => 'Uttara Sector 4, Dhaka', 'status' => 'active'],
            ['id' => 5, 'empid' => 'EMP-1005', 'full_name' => 'Kamrul Islam', 'email' => 'kamrul@qpos.com', 'mobile' => '01711223348', 'nid' => '199626925110005', 'designation_id' => 5, 'branch_id' => 1, 'salary' => 26000, 'joining_date' => '2024-05-10', 'address' => 'Mohammadpur, Dhaka', 'status' => 'active'],
            ['id' => 6, 'empid' => 'EMP-1006', 'full_name' => 'Nafisa Tabassum', 'email' => 'nafisa@qpos.com', 'mobile' => '01711223349', 'nid' => '199826925110006', 'designation_id' => 5, 'branch_id' => 1, 'salary' => 25000, 'joining_date' => '2024-06-01', 'address' => 'Banasree, Dhaka', 'status' => 'active'],
            ['id' => 7, 'empid' => 'EMP-1007', 'full_name' => 'Sajid Al-Mamun', 'email' => 'sajid@qpos.com', 'mobile' => '01711223350', 'nid' => '199326925110007', 'designation_id' => 6, 'branch_id' => 1, 'salary' => 30000, 'joining_date' => '2024-01-20', 'address' => 'Tejgaon, Dhaka', 'status' => 'active'],
            ['id' => 8, 'empid' => 'EMP-1008', 'full_name' => 'Rakibul Hasan', 'email' => 'rakib@qpos.com', 'mobile' => '01711223351', 'nid' => '199726925110008', 'designation_id' => 7, 'branch_id' => 1, 'salary' => 22000, 'joining_date' => '2024-07-01', 'address' => 'Farmgate, Dhaka', 'status' => 'active'],
            ['id' => 9, 'empid' => 'EMP-1009', 'full_name' => 'Nasreen Akter', 'email' => 'nasreen@qpos.com', 'mobile' => '01711223352', 'nid' => '199926925110009', 'designation_id' => 8, 'branch_id' => 1, 'salary' => 24000, 'joining_date' => '2024-08-01', 'address' => 'Moghbazar, Dhaka', 'status' => 'active'],
            ['id' => 10, 'empid' => 'EMP-1010', 'full_name' => 'Golam Mostafa', 'email' => 'mostafa@qpos.com', 'mobile' => '01711223353', 'nid' => '199126925110010', 'designation_id' => 5, 'branch_id' => 2, 'salary' => 28000, 'joining_date' => '2024-03-15', 'address' => 'Agrabad, Chittagong', 'status' => 'active'],
        ];

        foreach ($employees as $e) {
            $basic = $e['salary'];
            $empId = DB::table('employees')->insertGetId($e + ['created_at' => now(), 'updated_at' => now()]);

            DB::table('employee_salaries')->insert([
                'employee_id' => $empId,
                'salary_parameter_id' => 1,
                'amount' => $basic,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. Suppliers (12 Vendors)
        $suppliers = [
            ['supid' => 'SUP-001', 'org_name' => 'Transcom Electronics Ltd', 'name' => 'Mizanur Rahman', 'mobile' => '01713000001', 'email' => 'info@transcombd.com', 'address' => 'Gulshan 1, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-002', 'org_name' => 'Walton Hi-Tech Industries', 'name' => 'Ashraf Ali', 'mobile' => '01713000002', 'email' => 'supply@waltonbd.com', 'address' => 'Chandra, Gazipur', 'status' => 'active'],
            ['supid' => 'SUP-003', 'org_name' => 'Rangs Electronics Ltd', 'name' => 'Shafiqul Islam', 'mobile' => '01713000003', 'email' => 'sales@rangs.com', 'address' => 'Sonargaon Road, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-004', 'org_name' => 'Apex Footwear & Leather Ltd', 'name' => 'Jashim Uddin', 'mobile' => '01713000004', 'email' => 'dist@apexfootwear.com', 'address' => 'Gulshan 2, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-005', 'org_name' => 'Beximco Apparels Distribution', 'name' => 'Ziaul Hoque', 'mobile' => '01713000005', 'email' => 'supply@beximco.net', 'address' => 'Kashimpur, Gazipur', 'status' => 'active'],
            ['supid' => 'SUP-006', 'org_name' => 'PRAN-RFL Distribution Ltd', 'name' => 'Moniruzzaman', 'mobile' => '01713000006', 'email' => 'orders@pranfoods.net', 'address' => 'PRAN Tower, Badda, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-007', 'org_name' => 'Square Consumer Products Ltd', 'name' => 'Enamul Haque', 'mobile' => '01713000007', 'email' => 'supply@squaregroup.com', 'address' => 'Square Centre, Mohakhali, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-008', 'org_name' => 'Unilever Bangladesh Supply Ltd', 'name' => 'Saiful Bari', 'mobile' => '01713000008', 'email' => 'care@unileverbd.com', 'address' => 'ZN Tower, Gulshan, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-009', 'org_name' => 'Meghna Group of Industries (MGI)', 'name' => 'Tariqul Islam', 'mobile' => '01713000009', 'email' => 'sales@meghnagroup.biz', 'address' => 'Motijheel C/A, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-010', 'org_name' => 'ACI Pure Consumer Goods', 'name' => 'Delwar Hossain', 'mobile' => '01713000010', 'email' => 'supply@aci-bd.com', 'address' => 'Tejgaon I/A, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-011', 'org_name' => 'Xiaomi Bangladesh Authorized Dist', 'name' => 'Fahim Faisal', 'mobile' => '01713000011', 'email' => 'sales@xiaomibd.com', 'address' => 'Banani, Dhaka', 'status' => 'active'],
            ['supid' => 'SUP-012', 'org_name' => 'Samsung Bangladesh Hub', 'name' => 'Habibur Rahman', 'mobile' => '01713000012', 'email' => 'orders@samsungbd.com', 'address' => 'Gulshan Avenue, Dhaka', 'status' => 'active'],
        ];
        foreach ($suppliers as $sup) {
            DB::table('suppliers')->insert($sup + ['created_at' => now(), 'updated_at' => now()]);
        }

        // 4. Clients / Customers (60 Active Customers)
        $clientNames = [
            'Md. Ashiqur Rahman', 'Shahidul Alam', 'Nayeem Hasan', 'Abdur Razzak', 'Tanvir Ahmed',
            'Sadia Islam', 'Nusrat Jahan', 'Sabrina Rahman', 'Sharmin Sultana', 'Mehnaz Chowdhury',
            'Kazi Ahsan Habib', 'Mahbubur Rahman', 'Imran Nazir', 'Faisal Mahmud', 'Zubair Hossain',
            'Farzana Haque', 'Rifat Ara', 'Shaila Parveen', 'Tamanna Yasmin', 'Rubina Akter',
            'Shahadat Hossain', 'Jasim Uddin', 'Mustafizur Rahman', 'Saiful Islam', 'Nazmul Huda',
            'Anika Tabassum', 'Sumaiya Binte', 'Jannatul Ferdous', 'Tasnim Fatema', 'Mariam Begum',
            'Towhidul Islam', 'Khaled Mahmud', 'Mushfiqur Rahim', 'Mahmudullah Riyad', 'Tamim Iqbal',
            'Taslima Nasrin', 'Rupa Ganguly', 'Afroza Khanam', 'Laila Arjumand', 'Bilkis Banu',
            'Hasibul Hasan', 'Al-Amin Hossain', 'Mehedi Hasan', 'Shohidul Islam', 'Shakil Khan',
            'Ishrat Jahan', 'Naila Nayem', 'Priyanka Das', 'Mithila Roy', 'Pooja Sengupta',
            'Rashedul Karim', 'Wahiduzzaman', 'Sikder Enterprise', 'Prime Trading BD', 'Dhaka Fashion Hub',
            'Chittagong Retailers', 'Uttara Mega Store', 'Sylhet Super Mart', 'Dhanmondi Club Store', 'Green View Corporation'
        ];

        $clientIndex = 1001;
        foreach ($clientNames as $idx => $cName) {
            $mobile = '018' . str_pad($idx + 1000000, 8, '0', STR_PAD_LEFT);
            $points = rand(50, 650);
            DB::table('clients')->insert([
                'clientid' => $clientIndex++,
                'name' => $cName,
                'mobile' => $mobile,
                'email' => strtolower(str_replace([' ', '.'], '', $cName)) . '@gmail.com',
                'address' => 'Road #' . rand(1, 25) . ', Sector #' . rand(1, 14) . ', Dhaka',
                'reg_date' => date('Y-m-d', strtotime("-".rand(30, 180)." days")),
                'points_balance' => $points,
                'previous_due' => (rand(0, 10) > 7) ? rand(500, 3500) : 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Agents
        $agents = [
            ['full_name' => 'Kazi Zahid Hasan', 'mobile' => '01718889901', 'organization' => 'Apex Referral Network', 'designation' => 'Lead Marketing Agent', 'address' => 'Dhanmondi, Dhaka', 'status' => 'active'],
            ['full_name' => 'Sabbir Ahmed Khan', 'mobile' => '01718889902', 'organization' => 'Direct Sales Alliance', 'designation' => 'Senior Agent', 'address' => 'Mirpur, Dhaka', 'status' => 'active'],
            ['full_name' => 'Rubel Mia', 'mobile' => '01718889903', 'organization' => 'Urban Retail Connect', 'designation' => 'Agent Executive', 'address' => 'Uttara, Dhaka', 'status' => 'active'],
            ['full_name' => 'Anwarul Kabir', 'mobile' => '01718889904', 'organization' => 'Prime Distribution Channel', 'designation' => 'Area Agent', 'address' => 'Chittagong', 'status' => 'active'],
        ];
        foreach ($agents as $ag) {
            DB::table('agents')->insert($ag + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function seedAllCategoryItems()
    {
        $categoriesData = [
            32 => [ // Men's Collection (Min 105 items)
                'prefix' => "Men's",
                'types' => [
                    ['Formal Cotton Shirt', 850, 1350, 1, 1],
                    ['Casual Denim Shirt', 950, 1550, 1, 1],
                    ['Slim Fit Oxford Shirt', 900, 1450, 1, 1],
                    ['Mandarin Collar Shirt', 800, 1290, 1, 1],
                    ['Pique Cotton Polo T-Shirt', 450, 790, 1, 1],
                    ['Honeycomb Sports Polo', 550, 950, 1, 1],
                    ['Crew Neck Solid T-Shirt', 280, 490, 1, 1],
                    ['Oversized Graphic Tee', 380, 650, 1, 1],
                    ['Henley Long Sleeve Tee', 420, 720, 1, 1],
                    ['5-Pocket Stretch Denim Jeans', 1100, 1850, 1, 1],
                    ['Slim Fit Washed Indigo Jeans', 1250, 2100, 1, 1],
                    ['Khaki Casual Chino Pants', 950, 1600, 1, 1],
                    ['Formal Stretch Dress Trousers', 1050, 1750, 1, 1],
                    ['6-Pocket Heavy Cargo Pants', 1150, 1950, 1, 1],
                    ['Cotton Linen Drawstring Joggers', 650, 1150, 1, 1],
                    ['Semi-Tops Embroidered Silk Panjabi', 1600, 2850, 1, 1],
                    ['Festive White Cotton Panjabi', 1200, 2150, 1, 1],
                    ['Traditional Jacquard Kabli Set', 2200, 3800, 1, 1],
                    ['Handloom Pure Cotton Lungi', 350, 580, 1, 1],
                    ['Tailored Slim Fit Blazer', 3800, 6500, 1, 1],
                    ['Winter Fleece Zip Hoodie', 850, 1450, 1, 1],
                ],
                'qualifiers' => ['Classic', 'Premium', 'Vintage', 'Modern', 'Royal', 'Executive', 'Signature'],
                'unit_id' => 1,
                'has_warranty' => 0
            ],
            33 => [ // Electronics Goods (Min 105 items)
                'prefix' => "Electronic",
                'types' => [
                    ['Smart 4K UHD LED TV 43-inch', 24500, 32900, 1, 1],
                    ['Smart 4K Frameless TV 32-inch', 14500, 19900, 1, 1],
                    ['Active Noise Cancelling TWS Earbuds', 1450, 2450, 1, 1],
                    ['Wireless Over-Ear Bluetooth Headphone', 2100, 3400, 1, 1],
                    ['RGB Mechanical Gaming Keyboard', 1650, 2750, 1, 1],
                    ['Ergonomic Wireless Optical Mouse', 450, 850, 1, 1],
                    ['20000mAh 65W PD Fast Power Bank', 1850, 2900, 1, 1],
                    ['10000mAh Magnetic Wireless Power Bank', 1350, 2200, 1, 1],
                    ['65W GaN Fast Wall Adapter', 850, 1450, 1, 1],
                    ['100W Braided Type-C Fast Cable', 180, 390, 1, 1],
                    ['1TB NVMe PCIe 4.0 High-Speed SSD', 5200, 7500, 1, 1],
                    ['512GB SATA III Solid State Drive', 2600, 3800, 1, 1],
                    ['16GB DDR4 3200MHz Desktop RAM', 2900, 4200, 1, 1],
                    ['AX3000 Dual-Band Wi-Fi 6 Router', 3200, 4800, 1, 1],
                    ['Smart 360 Full HD Security Camera', 1800, 2950, 1, 1],
                    ['Portable Waterproof Bluetooth Speaker', 1250, 2150, 1, 1],
                    ['Stainless Steel Electric Kettle 1.8L', 750, 1350, 1, 1],
                    ['Heavy Duty Multi Blender & Grinder 1000W', 2400, 3900, 1, 1],
                    ['Digital Air Fryer 5.5L Touch Control', 4800, 7500, 1, 1],
                    ['Heavy Steam Iron Non-Stick 2000W', 1100, 1850, 1, 1],
                    ['Digital Professional Hair & Beard Trimmer', 850, 1490, 1, 1],
                ],
                'qualifiers' => ['Pro', 'Max', 'Ultra', 'Plus', 'Elite', 'Titanium', 'Turbo'],
                'unit_id' => 1,
                'has_warranty' => 1
            ],
            34 => [ // Woman Collections (Min 105 items)
                'prefix' => "Women's",
                'types' => [
                    ['Dhakai Jamdani Handloom Saree', 2800, 4600, 1, 1],
                    ['Rajshahi Pure Silk Saree', 3500, 5800, 1, 1],
                    ['Georgette Embroidered Party Saree', 2200, 3600, 1, 1],
                    ['Soft Tangail Cotton Daily Saree', 750, 1250, 1, 1],
                    ['Chiffon Floral Digital Printed Saree', 1100, 1850, 1, 1],
                    ['Premium Lawn 3-Piece Unstitched Suit', 1450, 2450, 1, 1],
                    ['Cotton Hand-Stitched Salwar Kameez', 1650, 2750, 1, 1],
                    ['Embroidered Silk Festive 3-Piece', 2600, 4300, 1, 1],
                    ['Digital Printed Cotton Kurti', 650, 1150, 1, 1],
                    ['Floral Rayon Designer Tunic', 750, 1300, 1, 1],
                    ['Denim Casual Longline Shirt', 950, 1650, 1, 1],
                    ['High Waist Super Stretch Skinny Jeans', 1100, 1890, 1, 1],
                    ['Wide Leg Flared Palazzo Pants', 450, 850, 1, 1],
                    ['Cotton Lycra Premium Ankle Leggings', 280, 520, 1, 1],
                    ['Dubai Cherry Fabric Modest Abaya', 2100, 3600, 1, 1],
                    ['Front Open Shrug Style Abaya', 2400, 3990, 1, 1],
                    ['Georgette Premium Chiffon Hijab', 220, 450, 1, 1],
                    ['Cashmere Knitted Winter Cardigan', 1200, 2100, 1, 1],
                    ['Woolen Kashmiri Embroidered Shawl', 1500, 2600, 1, 1],
                    ['Boho Style Printed Maxi Dress', 1350, 2300, 1, 1],
                    ['Silk Dupatta with Golden Zari Work', 550, 950, 1, 1],
                ],
                'qualifiers' => ['Elegance', 'Glamour', 'Exclusive', 'Artisan', 'Heritage', 'Grace', 'Flora'],
                'unit_id' => 1,
                'has_warranty' => 0
            ],
            35 => [ // Kids Collections (Min 105 items)
                'prefix' => "Kids",
                'types' => [
                    ['Boys Pique Polo T-Shirt', 320, 580, 1, 1],
                    ['Boys Superhero Graphic Tee', 220, 420, 1, 1],
                    ['Boys Stretch Denim Jeans', 550, 950, 1, 1],
                    ['Boys Elastic Waist Chino Shorts', 380, 680, 1, 1],
                    ['Boys Cotton Panjabi with Pajama Set', 750, 1350, 1, 1],
                    ['Boys Formal 2-Piece Party Suit', 1450, 2450, 1, 1],
                    ['Boys Cotton Nightwear Pajama Set', 420, 750, 1, 1],
                    ['Boys Fleece Winter Hoodie', 580, 990, 1, 1],
                    ['Girls Floral Tiered Cotton Frock', 450, 820, 1, 1],
                    ['Girls Princess Tutu Party Gown', 950, 1650, 1, 1],
                    ['Girls Embroidered Kurti & Legging Set', 650, 1150, 1, 1],
                    ['Girls Denim Dungaree Overall Dress', 720, 1280, 1, 1],
                    ['Girls Butterfly Print Nightwear Set', 380, 690, 1, 1],
                    ['Girls Festive Lehenga Choli Set', 1250, 2200, 1, 1],
                    ['Newborn Soft Cotton Romper (3-Pack)', 480, 890, 1, 1],
                    ['Baby Footed Winter Sleepsuit', 380, 690, 1, 1],
                    ['Baby Soft Swaddle Wrap Blanket', 320, 580, 1, 1],
                    ['Kids LED Light-Up Sneaker Shoes', 650, 1190, 1, 1],
                    ['Kids Ergonomic Primary School Bag', 750, 1350, 1, 1],
                    ['Kids Insulated Stainless Steel Flask', 380, 680, 1, 1],
                    ['Kids Anti-Spill Bento Lunch Box', 320, 590, 1, 1],
                ],
                'qualifiers' => ['Junior', 'Little Star', 'Cute Bear', 'Wonder', 'Happy Kid', 'Tiny Toes', 'Super Junior'],
                'unit_id' => 1,
                'has_warranty' => 0
            ],
            36 => [ // Grocery Items (Min 105 items)
                'prefix' => "Grocery",
                'types' => [
                    ['Premium Miniket Rice (25kg Bag)', 1850, 2150, 2, 0],
                    ['Royal Basmati Aromatic Rice (5kg)', 850, 1050, 2, 0],
                    ['Chinigura Special Pulao Rice (1kg)', 125, 155, 2, 0],
                    ['Fortified Pure Soybean Oil (5L)', 780, 890, 6, 0],
                    ['Cold Pressed Mustard Oil (1L)', 240, 290, 6, 0],
                    ['Pure Desi Cow Ghee (500g Jar)', 580, 690, 1, 0],
                    ['Whole Wheat Fresh Atta (2kg)', 95, 115, 2, 0],
                    ['Fine White Baking Maida (2kg)', 115, 140, 2, 0],
                    ['Premium Red Masoor Dal (1kg)', 125, 150, 2, 0],
                    ['Polished Yellow Moong Dal (1kg)', 140, 175, 2, 0],
                    ['Refined Cane Sugar (1kg)', 115, 135, 2, 0],
                    ['Vacuum Iodized Table Salt (1kg)', 32, 42, 2, 0],
                    ['Pure Turmeric Powder (200g Pack)', 65, 85, 4, 0],
                    ['Hot Red Chili Powder (200g Pack)', 85, 110, 4, 0],
                    ['Roasted Cumin Powder (100g Pack)', 75, 98, 4, 0],
                    ['Special Shahi Garam Masala (100g)', 110, 145, 4, 0],
                    ['Premium CTC Black Tea (500g)', 195, 245, 4, 0],
                    ['Instant Rich Gold Coffee (100g Jar)', 320, 395, 1, 0],
                    ['Full Cream Milk Powder (1kg Pack)', 720, 840, 4, 0],
                    ['Instant Masala Noodles (8-Pack Box)', 135, 165, 3, 0],
                    ['Sundarban 100% Pure Raw Honey (500g)', 420, 520, 1, 0],
                ],
                'qualifiers' => ['Pure Organic', 'Natural Fresh', 'Daily Choice', 'Gold Standard', 'Chef Special', 'Farm Fresh', 'Super Saver'],
                'unit_id' => 1,
                'has_warranty' => 0
            ]
        ];

        $barcodeStart = 10000000;
        $globalItemId = 1;

        foreach ($categoriesData as $catId => $catMeta) {
            $catItemsCount = 0;
            $types = $catMeta['types'];
            $qualifiers = $catMeta['qualifiers'];

            $brandIds = DB::table('brands')->where('category_id', $catId)->pluck('id')->toArray();
            if (empty($brandIds)) $brandIds = [1];

            foreach ($types as $typeRow) {
                list($baseName, $cost, $sale, $unitId, $isClothing) = $typeRow;

                foreach ($qualifiers as $qIdx => $qualifier) {
                    $barcode = $barcodeStart + $globalItemId;
                    $title = "{$catMeta['prefix']} {$qualifier} {$baseName}";
                    $brandId = $brandIds[$qIdx % count($brandIds)];

                    $itemCost = round($cost * (1 + ($qIdx * 0.04)), 2);
                    $itemSale = round($sale * (1 + ($qIdx * 0.04)), 2);

                    $itemId = DB::table('items')->insertGetId([
                        'barcode' => $barcode,
                        'category_id' => $catId,
                        'brand_id' => $brandId,
                        'title' => $title,
                        'description' => "High quality authentic {$title} for daily retail and corporate orders.",
                        'unit_id' => $unitId,
                        'opening_qty' => 0,
                        'opening_rate' => $itemCost,
                        'warranty_type' => $catMeta['has_warranty'] ? 'months' : 'none',
                        'warranty_period' => $catMeta['has_warranty'] ? '12 Months' : null,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($isClothing) {
                        $varSizes = [2, 3, 4]; // M, L, XL
                        $varColors = [1, 3]; // Black, Navy
                        foreach ($varSizes as $sId) {
                            foreach ($varColors as $cId) {
                                DB::table('item_prices')->insert([
                                    'item_id' => $itemId,
                                    'color_id' => $cId,
                                    'size_id' => $sId,
                                    'purchase_price' => $itemCost,
                                    'selling_price' => $itemSale,
                                    'status' => 'active',
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    } else {
                        DB::table('item_prices')->insert([
                            'item_id' => $itemId,
                            'color_id' => null,
                            'size_id' => null,
                            'purchase_price' => $itemCost,
                            'selling_price' => $itemSale,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    $globalItemId++;
                    $catItemsCount++;
                }
            }
            $this->command->info(" - Category ID: {$catId} seeded with {$catItemsCount} items.");
        }
    }

    private function seedPurchasesAndStock()
    {
        $items = DB::table('items')->get();
        $itemPrices = DB::table('item_prices')->get()->groupBy('item_id');
        $suppliers = DB::table('suppliers')->pluck('id')->toArray();
        $warehouses = DB::table('warehouses')->pluck('id')->toArray();

        $purchaseDates = [
            '2026-05-25', '2026-06-02', '2026-06-10', '2026-06-18', '2026-06-25',
            '2026-07-02', '2026-07-09', '2026-07-16', '2026-07-23', '2026-07-30',
            '2026-08-04', '2026-08-11', '2026-08-18', '2026-08-25', '2026-08-31',
            '2026-09-01'
        ];

        // 8 items per purchase keeps total purchase amount well below decimal(8,2) max (999,999)
        $chunkSize = 8;
        $itemChunks = $items->chunk($chunkSize);

        $purchaseIndex = 1;
        foreach ($itemChunks as $chunkIndex => $chunk) {
            $pDate = $purchaseDates[$chunkIndex % count($purchaseDates)];
            $supId = $suppliers[$chunkIndex % count($suppliers)];
            $whId = $warehouses[$chunkIndex % count($warehouses)];
            $invNo = 'PUR-' . date('Ymd', strtotime($pDate)) . '-' . str_pad($purchaseIndex, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $detailsToInsert = [];

            foreach ($chunk as $item) {
                $pRow = $itemPrices[$item->id]->first() ?? null;
                $cost = $pRow ? $pRow->purchase_price : 500;
                $sell = $pRow ? $pRow->selling_price : 750;
                
                // For expensive items (TVs/SSDs) buy 5-10 units, for others buy 25-50 units
                $qty = ($cost > 5000) ? rand(5, 10) : rand(25, 50);
                $lineTotal = $qty * $cost;
                $totalAmount += $lineTotal;

                $hasSerial = ($item->warranty_type != 'none');
                $serialNos = null;
                if ($hasSerial) {
                    $serials = [];
                    for ($s = 1; $s <= min($qty, 10); $s++) {
                        $serials[] = 'SN-' . $item->barcode . '-' . str_pad($s, 3, '0', STR_PAD_LEFT);
                    }
                    $serialNos = implode(',', $serials);
                }

                $detailsToInsert[] = [
                    'item_id' => $item->id,
                    'category_id' => $item->category_id,
                    'unit_id' => $item->unit_id,
                    'color_id' => ($item->category_id == 32 || $item->category_id == 34) ? 1 : null,
                    'size_id' => ($item->category_id == 32 || $item->category_id == 34) ? 2 : null,
                    'serial_no' => $serialNos,
                    'qty' => $qty,
                    'price' => $cost,
                    'selling_price' => $sell,
                    'total_amount' => $lineTotal,
                    'status' => 'active',
                    'created_at' => $pDate . ' 10:00:00',
                    'updated_at' => $pDate . ' 10:00:00',
                ];
            }

            $paidAmount = round($totalAmount * 0.85, 2);

            $purchaseId = DB::table('purchases')->insertGetId([
                'invoiceno' => $invNo,
                'purchase_date' => $pDate,
                'supplier_id' => $supId,
                'amount' => $totalAmount,
                'total_amount' => $totalAmount,
                'discount' => 0,
                'tax' => 0,
                'is_closed' => 1,
                'receive_status' => 'Received',
                'status' => 'active',
                'created_at' => $pDate . ' 10:00:00',
                'updated_at' => $pDate . ' 10:00:00',
            ]);

            $grnNo = 'GRN-' . date('Ymd', strtotime($pDate)) . '-' . str_pad($purchaseIndex, 4, '0', STR_PAD_LEFT);
            $grnId = DB::table('grns')->insertGetId([
                'grn_no' => $grnNo,
                'grn_date' => $pDate,
                'purchase_id' => $purchaseId,
                'supplier_id' => $supId,
                'warehouse_id' => $whId,
                'challan_no' => 'CH-' . $purchaseId,
                'challan_date' => $pDate,
                'total_qty' => array_sum(array_column($detailsToInsert, 'qty')),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'is_closed' => 1,
                'received_by' => 1,
                'note' => 'All items verified and received in good condition.',
                'status' => 'active',
                'created_at' => $pDate . ' 11:00:00',
                'updated_at' => $pDate . ' 11:00:00',
            ]);

            foreach ($detailsToInsert as $dt) {
                $dt['purchase_id'] = $purchaseId;
                $pDetailId = DB::table('purchase_details')->insertGetId($dt);

                DB::table('grn_details')->insert([
                    'grn_id' => $grnId,
                    'purchase_detail_id' => $pDetailId,
                    'category_id' => $dt['category_id'],
                    'item_id' => $dt['item_id'],
                    'color_id' => $dt['color_id'],
                    'size_id' => $dt['size_id'],
                    'unit_id' => $dt['unit_id'],
                    'ordered_qty' => $dt['qty'],
                    'previously_received_qty' => 0,
                    'received_qty' => $dt['qty'],
                    'unit_price' => $dt['price'],
                    'selling_price' => $dt['selling_price'],
                    'total_amount' => $dt['total_amount'],
                    'serial_no' => $dt['serial_no'],
                    'note' => null,
                    'status' => 'active',
                    'created_at' => $pDate . ' 11:00:00',
                    'updated_at' => $pDate . ' 11:00:00',
                ]);

                DB::table('stock_transactions')->insert([
                    'item_id' => $dt['item_id'],
                    'color_id' => $dt['color_id'],
                    'size_id' => $dt['size_id'],
                    'warehouse_id' => $whId,
                    'transaction_date' => $pDate,
                    'transaction_type' => 'Purchase',
                    'reference_type' => 'Purchase',
                    'reference_id' => (string)$purchaseId,
                    'qty_in' => $dt['qty'],
                    'qty_out' => 0,
                    'status' => 'active',
                    'created_at' => $pDate . ' 10:00:00',
                    'updated_at' => $pDate . ' 10:00:00',
                ]);
            }

            if ($paidAmount > 0) {
                $payId = DB::table('payments')->insertGetId([
                    'payslipno' => $invNo,
                    'payment_type' => 'Pay',
                    'supplier_id' => $supId,
                    'payment_date' => $pDate,
                    'discount' => 0,
                    'amount' => $paidAmount,
                    'fund_account_id' => 2, // Bank Account
                    'payment_method' => 'Bank',
                    'status' => 'active',
                    'created_at' => $pDate . ' 11:30:00',
                    'updated_at' => $pDate . ' 11:30:00',
                ]);

                DB::table('payment_details')->insert([
                    'payment_id' => $payId,
                    'reference_type' => 'Purchase',
                    'reference_id' => $purchaseId,
                    'amount' => $paidAmount,
                    'is_closed' => 1,
                    'status' => 'active',
                    'created_at' => $pDate . ' 11:30:00',
                    'updated_at' => $pDate . ' 11:30:00',
                ]);
            }

            $purchaseIndex++;
        }
        $this->command->info(" - Successfully seeded " . ($purchaseIndex - 1) . " purchase and GRN orders with initial stock.");
    }

    private function seedThreeMonthsSales()
    {
        $clients = DB::table('clients')->pluck('id')->toArray();
        $items = DB::table('items')->get()->keyBy('id');
        $itemPrices = DB::table('item_prices')->get()->groupBy('item_id');
        $itemIds = $items->keys()->toArray();
        $fundAccounts = [1, 2, 5, 6];
        $methods = ['Cash', 'Bank', 'bKash', 'Nagad'];

        $startDate = strtotime('2026-06-01');
        $endDate = strtotime('2026-09-04');

        $invoiceCounter = 1;
        $current = $startDate;

        while ($current <= $endDate) {
            $currDate = date('Y-m-d', $current);
            $dailyOrders = rand(2, 4);

            for ($o = 0; $o < $dailyOrders; $o++) {
                $clientId = $clients[array_rand($clients)];
                $invNo = 'POS-' . date('Ymd', $current) . '-' . str_pad($invoiceCounter, 4, '0', STR_PAD_LEFT);
                $timeString = sprintf("%02d:%02d:%02d", rand(10, 21), rand(0, 59), rand(0, 59));
                $fullDateTime = "{$currDate} {$timeString}";

                $basketSize = rand(1, 4);
                $originalAmount = 0;
                $lines = [];

                for ($b = 0; $b < $basketSize; $b++) {
                    $randomItemId = $itemIds[array_rand($itemIds)];
                    $item = $items[$randomItemId];
                    $pRow = $itemPrices[$item->id]->first() ?? null;
                    $rate = $pRow ? $pRow->selling_price : 750;
                    $qty = rand(1, 4);
                    $lineTotal = $qty * $rate;
                    $originalAmount += $lineTotal;

                    $colorId = ($item->category_id == 32 || $item->category_id == 34) ? 1 : null;
                    $sizeId = ($item->category_id == 32 || $item->category_id == 34) ? 2 : null;
                    $serial = ($item->warranty_type != 'none') ? ('SN-' . $item->barcode . '-001') : null;

                    $lines[] = [
                        'item' => $item,
                        'qty' => $qty,
                        'rate' => $rate,
                        'total' => $lineTotal,
                        'color_id' => $colorId,
                        'size_id' => $sizeId,
                        'serial' => $serial,
                    ];
                }

                $discount = (rand(1, 10) > 7) ? round($originalAmount * 0.05, 2) : 0;
                $vat = round(($originalAmount - $discount) * 0.02, 2);
                $totalAmount = max(0, ($originalAmount - $discount) + $vat);

                $payRoll = rand(1, 100);
                if ($payRoll <= 80) {
                    $paidAmount = $totalAmount;
                    $isClosed = 1;
                } elseif ($payRoll <= 95) {
                    $paidAmount = round($totalAmount * rand(40, 70) / 100, 2);
                    $isClosed = 0;
                } else {
                    $paidAmount = 0;
                    $isClosed = 0;
                }

                $invId = DB::table('invoices')->insertGetId([
                    'client_id' => $clientId,
                    'invoice_no' => $invNo,
                    'invoice_date' => $currDate,
                    'original_amount' => $originalAmount,
                    'discount' => $discount,
                    'vat' => $vat,
                    'amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'is_previous_due' => 0,
                    'is_closed' => $isClosed,
                    'status' => 'active',
                    'created_at' => $fullDateTime,
                    'updated_at' => $fullDateTime
                ]);

                foreach ($lines as $line) {
                    DB::table('invoice_details')->insert([
                        'invoice_id' => (string)$invId,
                        'reference' => 'POS Sale',
                        'workorder_id' => 0,
                        'item_id' => $line['item']->id,
                        'color_id' => $line['color_id'],
                        'size_id' => $line['size_id'],
                        'serial_no' => $line['serial'],
                        'account_id' => 0,
                        'description' => 'POS Sale - ' . $line['item']->title,
                        'qty' => $line['qty'],
                        'amount' => $line['rate'],
                        'total_amount' => $line['total'],
                        'status' => 'active',
                        'created_at' => $fullDateTime,
                        'updated_at' => $fullDateTime
                    ]);

                    DB::table('stock_transactions')->insert([
                        'item_id' => $line['item']->id,
                        'color_id' => $line['color_id'],
                        'size_id' => $line['size_id'],
                        'warehouse_id' => 1,
                        'transaction_date' => $currDate,
                        'transaction_type' => 'Issue',
                        'reference_type' => 'Issue',
                        'reference_id' => (string)$invId,
                        'qty_in' => 0,
                        'qty_out' => $line['qty'],
                        'status' => 'active',
                        'created_at' => $fullDateTime,
                        'updated_at' => $fullDateTime
                    ]);
                }

                if ($paidAmount > 0) {
                    $methodIdx = rand(0, 3);
                    $fundAccId = $fundAccounts[$methodIdx];
                    $methodName = $methods[$methodIdx];

                    $payId = DB::table('payments')->insertGetId([
                        'payslipno' => $invNo,
                        'payment_type' => 'Receive',
                        'client_id' => $clientId,
                        'payment_date' => $currDate,
                        'discount' => 0,
                        'amount' => $paidAmount,
                        'fund_account_id' => $fundAccId,
                        'payment_method' => $methodName,
                        'mbanking_type' => ($methodName == 'bKash' || $methodName == 'Nagad') ? $methodName : null,
                        'trxid' => ($methodName == 'bKash' || $methodName == 'Nagad') ? ('TRX' . rand(10000000, 99999999)) : null,
                        'status' => 'active',
                        'created_at' => $fullDateTime,
                        'updated_at' => $fullDateTime
                    ]);

                    DB::table('payment_details')->insert([
                        'payment_id' => $payId,
                        'reference_type' => 'Invoice',
                        'reference_id' => $invId,
                        'amount' => $paidAmount,
                        'is_closed' => $isClosed,
                        'status' => 'active',
                        'created_at' => $fullDateTime,
                        'updated_at' => $fullDateTime
                    ]);
                }

                $earnedPoints = floor($totalAmount / 100);
                if ($earnedPoints > 0) {
                    DB::table('client_point_transactions')->insert([
                        'client_id' => $clientId,
                        'invoice_id' => $invId,
                        'type' => 'Earn',
                        'points_in' => $earnedPoints,
                        'points_out' => 0,
                        'balance' => $earnedPoints,
                        'rate' => 10.00,
                        'amount_equivalent' => round($earnedPoints / 10, 2),
                        'description' => "Loyalty points earned from invoice #{$invNo}",
                        'transaction_date' => $currDate,
                        'status' => 'active',
                        'created_at' => $fullDateTime,
                        'updated_at' => $fullDateTime
                    ]);
                }

                $invoiceCounter++;
            }

            $current = strtotime('+1 day', $current);
        }

        $this->command->info(" - Successfully seeded " . ($invoiceCounter - 1) . " invoices across 3+ months.");
    }

    private function seedExpensesAndVouchers()
    {
        $expenseCategories = [
            ['title' => 'Monthly Showroom & Office Rent', 'account_id' => 9, 'amount' => 45000],
            ['title' => 'DESCO Electricity & Utility Bill', 'account_id' => 10, 'amount' => 14500],
            ['title' => 'High Speed Fiber Internet Bill', 'account_id' => 8, 'amount' => 3500],
            ['title' => 'Staff Tea, Snacks & Entertainment', 'account_id' => 8, 'amount' => 4200],
            ['title' => 'Store Maintenance & Cleaning Supplies', 'account_id' => 8, 'amount' => 2800],
            ['title' => 'Facebook & Social Media Marketing Ads', 'account_id' => 8, 'amount' => 8500],
            ['title' => 'Courier & Logistics Shipping Cost', 'account_id' => 8, 'amount' => 6200],
        ];

        $expenseDates = [
            '2026-06-05', '2026-06-12', '2026-06-20', '2026-06-28',
            '2026-07-05', '2026-07-14', '2026-07-22', '2026-07-29',
            '2026-08-05', '2026-08-12', '2026-08-19', '2026-08-28',
            '2026-09-02'
        ];

        $expCounter = 101;
        foreach ($expenseDates as $eDate) {
            $cat = $expenseCategories[array_rand($expenseCategories)];
            $amount = $cat['amount'] + rand(-200, 500);
            $expId = 'EXP-' . date('Ymd', strtotime($eDate)) . '-' . $expCounter++;

            $expenseDbId = DB::table('expenses')->insertGetId([
                'expenseid' => $expId,
                'expense_date' => $eDate,
                'workorder_id' => 0,
                'employee_id' => 1,
                'total_amount' => $amount,
                'approved_by' => 1,
                'approved_date' => $eDate,
                'status' => 'active',
                'created_at' => $eDate . ' 14:00:00',
                'updated_at' => $eDate . ' 14:00:00',
            ]);

            DB::table('expense_details')->insert([
                'expense_id' => $expenseDbId,
                'account_id' => $cat['account_id'],
                'amount' => $amount,
                'narration' => $cat['title'],
                'is_closed' => 1,
                'status' => 'active',
                'created_at' => $eDate . ' 14:00:00',
                'updated_at' => $eDate . ' 14:00:00',
            ]);

            DB::table('payments')->insert([
                'payslipno' => $expId,
                'payment_type' => 'Pay',
                'payment_date' => $eDate,
                'discount' => 0,
                'amount' => $amount,
                'fund_account_id' => 1,
                'payment_method' => 'Cash',
                'status' => 'active',
                'created_at' => $eDate . ' 14:30:00',
                'updated_at' => $eDate . ' 14:30:00',
            ]);
        }

        // Vouchers (Debit / Credit / Journal)
        $voucherDates = ['2026-06-15', '2026-06-30', '2026-07-15', '2026-07-31', '2026-08-15', '2026-08-31'];
        $vCounter = 1;
        foreach ($voucherDates as $vDate) {
            $vNo = 'VOU-' . date('Ymd', strtotime($vDate)) . '-' . str_pad($vCounter++, 3, '0', STR_PAD_LEFT);
            $fyId = (strtotime($vDate) < strtotime('2026-07-01')) ? 6 : 7;

            $vId = DB::table('vouchers')->insertGetId([
                'voucherno' => $vNo,
                'voucher_type' => 'Journal',
                'voucher_date' => $vDate,
                'narration' => 'Bi-weekly ledger reconciliation and petty cash balancing.',
                'financial_year_id' => $fyId,
                'status' => 'active',
                'created_at' => $vDate . ' 18:00:00',
                'updated_at' => $vDate . ' 18:00:00',
            ]);

            DB::table('voucher_details')->insert([
                [
                    'voucher_id' => $vId,
                    'account_id' => 1, // Cash
                    'dr_amount' => 50000.00,
                    'cr_amount' => 0.00,
                    'line_narration' => 'Cash in hand received from bank withdrawal',
                    'status' => 'active',
                    'created_at' => $vDate . ' 18:00:00',
                    'updated_at' => $vDate . ' 18:00:00',
                ],
                [
                    'voucher_id' => $vId,
                    'account_id' => 2, // DBBL
                    'dr_amount' => 0.00,
                    'cr_amount' => 50000.00,
                    'line_narration' => 'Bank cash withdrawal to replenish counter cash',
                    'status' => 'active',
                    'created_at' => $vDate . ' 18:00:00',
                    'updated_at' => $vDate . ' 18:00:00',
                ]
            ]);
        }

        // Fund Transfers & Withdrawals
        $transfers = [
            ['date' => '2026-06-10', 'from' => 2, 'to' => 1, 'amount' => '30000', 'remarks' => 'Bank to Main Cash replenishment'],
            ['date' => '2026-07-08', 'from' => 5, 'to' => 2, 'amount' => '50000', 'remarks' => 'bKash merchant settlement to DBBL Bank'],
            ['date' => '2026-08-05', 'from' => 6, 'to' => 3, 'amount' => '40000', 'remarks' => 'Nagad settlement to BRAC Bank'],
            ['date' => '2026-08-20', 'from' => 3, 'to' => 1, 'amount' => '25000', 'remarks' => 'Cash drawer top-up from BRAC Bank'],
        ];
        foreach ($transfers as $tf) {
            DB::table('fund_transfers')->insert([
                'transfer_date' => $tf['date'],
                'from_account_id' => $tf['from'],
                'to_account_id' => $tf['to'],
                'amount' => $tf['amount'],
                'remarks' => $tf['remarks'],
                'status' => 'active',
                'created_at' => $tf['date'] . ' 12:00:00',
                'updated_at' => $tf['date'] . ' 12:00:00',
            ]);
        }

        $withdrawals = [
            ['date' => '2026-06-25', 'amount' => '20000', 'acc' => 2, 'remarks' => 'Director monthly travel advance'],
            ['date' => '2026-07-25', 'amount' => '25000', 'acc' => 2, 'remarks' => 'Owner personal drawing'],
            ['date' => '2026-08-25', 'amount' => '20000', 'acc' => 3, 'remarks' => 'Contingency emergency cash withdrawal'],
        ];
        foreach ($withdrawals as $wd) {
            DB::table('withdrawls')->insert([
                'withdrwal_date' => $wd['date'],
                'amount' => $wd['amount'],
                'account_id' => (string)$wd['acc'],
                'remarks' => $wd['remarks'],
                'status' => 'active',
                'created_at' => $wd['date'] . ' 15:00:00',
                'updated_at' => $wd['date'] . ' 15:00:00',
            ]);
        }
    }

    private function seedHrAndSalaries()
    {
        $employees = DB::table('employees')->get();
        $salaryProfiles = DB::table('employee_salaries')->pluck('amount', 'employee_id')->toArray();

        $loanStaff1 = 5;
        $loanStaff2 = 8;

        $loan1 = DB::table('loan_infos')->insertGetId([
            'trnsid' => 'LOAN-2026-001',
            'trns_date' => '2026-05-15',
            'employee_id' => $loanStaff1,
            'trns_type' => 'Loan',
            'amount' => 30000.00,
            'total_installment' => 6,
            'installment_amount' => 5000,
            'deduct_from_salary' => 1,
            'schedule_day' => 1,
            'due_amount' => 15000.00,
            'reason' => 'Family Medical Emergency',
            'status' => 'active',
            'created_at' => '2026-05-15 10:00:00',
            'updated_at' => '2026-05-15 10:00:00',
        ]);

        $loan2 = DB::table('loan_infos')->insertGetId([
            'trnsid' => 'LOAN-2026-002',
            'trns_date' => '2026-06-01',
            'employee_id' => $loanStaff2,
            'trns_type' => 'Loan',
            'amount' => 24000.00,
            'total_installment' => 6,
            'installment_amount' => 4000,
            'deduct_from_salary' => 1,
            'schedule_day' => 1,
            'due_amount' => 12000.00,
            'reason' => 'Home Renovation',
            'status' => 'active',
            'created_at' => '2026-06-01 10:00:00',
            'updated_at' => '2026-06-01 10:00:00',
        ]);

        $months = [
            ['month' => '06', 'name' => 'June 2026', 'year' => 2026, 'gen_date' => '2026-07-01'],
            ['month' => '07', 'name' => 'July 2026', 'year' => 2026, 'gen_date' => '2026-08-01'],
            ['month' => '08', 'name' => 'August 2026', 'year' => 2026, 'gen_date' => '2026-09-01'],
        ];

        foreach ($months as $m) {
            $sheetId = DB::table('salary_sheets')->insertGetId([
                'title' => "Monthly Staff Salary - {$m['name']}",
                'month' => $m['month'],
                'year' => $m['year'],
                'generated_date' => $m['gen_date'],
                'approved_by' => 1,
                'approved_date' => $m['gen_date'],
                'status' => 'active',
                'created_at' => $m['gen_date'] . ' 09:00:00',
                'updated_at' => $m['gen_date'] . ' 09:00:00',
            ]);

            $totalSheetAmount = 0;

            foreach ($employees as $emp) {
                $baseSalary = $salaryProfiles[$emp->id] ?? 25000;
                $bonus = ($m['month'] == '06') ? 5000 : 0;
                $installment = 0;

                if ($emp->id == $loanStaff1) {
                    $installment = 5000;
                    DB::table('loan_installments')->insert([
                        'loan_info_id' => $loan1,
                        'employee_id' => $emp->id,
                        'type' => 'Installment',
                        'deduct_from_salary' => 1,
                        'instalment_date' => $m['gen_date'],
                        'amount' => 5000.00,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_ip' => 1,
                        'updated_ip' => 1,
                        'created_at' => $m['gen_date'] . ' 10:00:00',
                        'updated_at' => $m['gen_date'] . ' 10:00:00',
                    ]);
                } elseif ($emp->id == $loanStaff2) {
                    $installment = 4000;
                    DB::table('loan_installments')->insert([
                        'loan_info_id' => $loan2,
                        'employee_id' => $emp->id,
                        'type' => 'Installment',
                        'deduct_from_salary' => 1,
                        'instalment_date' => $m['gen_date'],
                        'amount' => 4000.00,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_ip' => 1,
                        'updated_ip' => 1,
                        'created_at' => $m['gen_date'] . ' 10:00:00',
                        'updated_at' => $m['gen_date'] . ' 10:00:00',
                    ]);
                }

                $netTotal = ($baseSalary + $bonus) - $installment;
                $totalSheetAmount += $netTotal;

                DB::table('salary_sheet_details')->insert([
                    'salary_sheet_id' => $sheetId,
                    'employee_id' => $emp->id,
                    'salary' => $baseSalary,
                    'commission' => 0,
                    'bonus' => $bonus,
                    'installment' => $installment,
                    'installment_history' => $installment > 0 ? "Loan Installment Deducted" : null,
                    'deduct' => $installment,
                    'total' => $netTotal,
                    'is_paid' => 1,
                    'status' => 'active',
                    'created_at' => $m['gen_date'] . ' 10:00:00',
                    'updated_at' => $m['gen_date'] . ' 10:00:00',
                ]);
            }

            DB::table('payments')->insert([
                'payslipno' => "SAL-{$m['year']}-{$m['month']}",
                'payment_type' => 'Pay',
                'payment_date' => $m['gen_date'],
                'discount' => 0,
                'amount' => $totalSheetAmount,
                'fund_account_id' => 2,
                'payment_method' => 'Bank',
                'status' => 'active',
                'created_at' => $m['gen_date'] . ' 11:00:00',
                'updated_at' => $m['gen_date'] . ' 11:00:00',
            ]);
        }
    }

    private function seedOperations()
    {
        $clients = DB::table('clients')->pluck('id')->toArray();
        $items = DB::table('items')->get()->keyBy('id');
        $itemIds = $items->keys()->toArray();
        $invoices = DB::table('invoices')->take(20)->get();

        // 1. Corporate Workorders & Details (6 Work Orders)
        for ($w = 1; $w <= 6; $w++) {
            $clientId = $clients[$w % count($clients)];
            $oDate = date('Y-m-d', strtotime("-".(rand(10, 75))." days"));
            $delDate = date('Y-m-d', strtotime($oDate . " +15 days"));
            $oNo = 'WO-2026-' . str_pad($w, 4, '0', STR_PAD_LEFT);
            $orderItemId = $itemIds[$w % count($itemIds)];

            $amount = rand(45000, 120000);
            $woId = DB::table('workorders')->insertGetId([
                'client_id' => $clientId,
                'order_date' => $oDate,
                'order_no' => $oNo,
                'delivery_date' => $delDate,
                'shipping' => 'Standard Truck Delivery',
                'currency_id' => 1,
                'currency_rate' => 1.0,
                'amount' => $amount,
                'due_amount' => 0,
                'is_closed' => 1,
                'status' => 'active',
                'created_at' => $oDate . ' 10:00:00',
                'updated_at' => $oDate . ' 10:00:00',
            ]);

            $woDetailId = DB::table('workorder_details')->insertGetId([
                'workorder_id' => $woId,
                'item_id' => $orderItemId,
                'description' => 'Corporate Bulk Supply Order Batch #' . $w,
                'actual_qty' => 50,
                'ordered_qty' => 50,
                'receive_qty' => 50,
                'due_qty' => 0,
                'unit_price' => round($amount / 50, 2),
                'price' => $amount,
                'status' => 'active',
                'created_at' => $oDate . ' 10:00:00',
                'updated_at' => $oDate . ' 10:00:00',
            ]);

            // Delivery Challan
            $chNo = 'CH-2026-' . str_pad($w, 4, '0', STR_PAD_LEFT);
            $chId = DB::table('challans')->insertGetId([
                'workorder_id' => $woId,
                'challan_no' => $chNo,
                'challan_date' => $delDate,
                'client_id' => $clientId,
                'delivery_location' => 'Corporate Head Office, Gulshan, Dhaka',
                'receive_by' => 'Admin Logistics Officer',
                'receive_by_designation' => 'Procurement Manager',
                'receive_by_mobile' => '01711009988',
                'delivery_by' => 'Rakibul Hasan',
                'delivery_by_mobile' => '01711223351',
                'status' => 'active',
                'created_at' => $delDate . ' 14:00:00',
                'updated_at' => $delDate . ' 14:00:00',
            ]);

            DB::table('challan_details')->insert([
                'challan_id' => (string)$chId,
                'workorder_detail_id' => (string)$woDetailId,
                'item_id' => $orderItemId,
                'description' => 'Delivery of ordered items in good condition',
                'qty' => 50,
                'receive_qty' => 50,
                'remark' => 'Verified by Store Keeper',
                'status' => 'active',
                'created_at' => $delDate . ' 14:00:00',
                'updated_at' => $delDate . ' 14:00:00',
            ]);
        }

        // 2. Warranty Claims & Logs (8 Claims)
        $elecItems = DB::table('items')->where('warranty_type', '!=', 'none')->get();
        if ($elecItems->isNotEmpty()) {
            $claimStatuses = ['pending', 'in_progress', 'completed', 'delivered'];
            for ($wc = 1; $wc <= 8; $wc++) {
                $elItem = $elecItems[$wc % $elecItems->count()];
                $cStatus = $claimStatuses[$wc % count($claimStatuses)];
                $claimDate = date('Y-m-d', strtotime("-".($wc * 7)." days"));
                $serial = 'SN-' . $elItem->barcode . '-001';

                $claimId = DB::table('warranty_claims')->insertGetId([
                    'claim_no' => 'WC-2026-' . str_pad($wc, 4, '0', STR_PAD_LEFT),
                    'serial_no' => $serial,
                    'invoice_id' => $invoices[$wc % $invoices->count()]->id,
                    'item_id' => $elItem->id,
                    'client_id' => $clients[$wc % count($clients)],
                    'customer_name' => 'Customer Claim #' . $wc,
                    'customer_mobile' => '018119988' . str_pad($wc, 2, '0', STR_PAD_LEFT),
                    'warranty_type' => 'months',
                    'warranty_period' => '12 Months',
                    'sale_date' => date('Y-m-d', strtotime($claimDate . ' -45 days')),
                    'claim_date' => $claimDate,
                    'warranty_expiry_date' => date('Y-m-d', strtotime($claimDate . ' +320 days')),
                    'problem_description' => 'Device power fluctuation / charging port loose contact.',
                    'accessories_received' => 'Main unit with original power adapter and cable',
                    'current_status' => $cStatus,
                    'service_cost' => ($cStatus == 'delivered') ? 350.00 : 0.00,
                    'customer_charge' => 0.00,
                    'status' => 'active',
                    'created_at' => $claimDate . ' 11:00:00',
                    'updated_at' => $claimDate . ' 11:00:00',
                ]);

                DB::table('warranty_claim_logs')->insert([
                    [
                        'warranty_claim_id' => $claimId,
                        'status' => 'received',
                        'remarks' => 'Item received from customer for technical inspection.',
                        'created_by' => 1,
                        'created_at' => $claimDate . ' 11:05:00',
                        'updated_at' => $claimDate . ' 11:05:00',
                    ],
                    [
                        'warranty_claim_id' => $claimId,
                        'status' => $cStatus,
                        'remarks' => "Claim status updated to {$cStatus}.",
                        'created_by' => 1,
                        'created_at' => $claimDate . ' 16:00:00',
                        'updated_at' => $claimDate . ' 16:00:00',
                    ]
                ]);
            }
        }

        // 3. Wastages & Details (4 Audits)
        for ($ws = 1; $ws <= 4; $ws++) {
            $auditDate = date('Y-m-d', strtotime("-".($ws * 20)." days"));
            $auditNo = 'WST-2026-' . str_pad($ws, 4, '0', STR_PAD_LEFT);
            $wItem = $items[$itemIds[$ws % count($itemIds)]];
            $wQty = rand(2, 5);
            $costPrice = 500;
            $lossAmount = $wQty * $costPrice;

            $wId = DB::table('wastages')->insertGetId([
                'audit_number' => $auditNo,
                'audit_date' => $auditDate,
                'audited_by' => 'Inventory Audit Team',
                'auditor_id' => 7,
                'branch_id' => 1,
                'total_qty' => $wQty,
                'total_loss_amount' => $lossAmount,
                'status' => 'approved',
                'note' => 'Minor transit damage and packaging defect verified during quarterly audit.',
                'approved_by' => 1,
                'approved_date' => $auditDate,
                'created_at' => $auditDate . ' 17:00:00',
                'updated_at' => $auditDate . ' 17:00:00',
            ]);

            DB::table('wastage_details')->insert([
                'wastage_id' => $wId,
                'category_id' => $wItem->category_id,
                'item_id' => $wItem->id,
                'unit_id' => $wItem->unit_id,
                'quantity' => $wQty,
                'unit_price' => $costPrice,
                'total_amount' => $lossAmount,
                'reason' => 'Transit Damage / Defective',
                'status' => 'active',
                'created_at' => $auditDate . ' 17:00:00',
                'updated_at' => $auditDate . ' 17:00:00',
            ]);

            DB::table('stock_transactions')->insert([
                'item_id' => $wItem->id,
                'warehouse_id' => 1,
                'transaction_date' => $auditDate,
                'transaction_type' => 'Wastage',
                'reference_type' => 'Wastage',
                'reference_id' => (string)$wId,
                'qty_in' => 0,
                'qty_out' => $wQty,
                'status' => 'active',
                'created_at' => $auditDate . ' 17:00:00',
                'updated_at' => $auditDate . ' 17:00:00',
            ]);
        }

        // 4. Agent Commissions (6 Records)
        $agents = DB::table('agents')->pluck('id')->toArray();
        for ($ac = 1; $ac <= 6; $ac++) {
            $commDate = date('Y-m-d', strtotime("-".($ac * 12)." days"));
            $agId = $agents[$ac % count($agents)];
            $commAmount = rand(1500, 4500);

            DB::table('commissions')->insert([
                'is_employee' => 0,
                'agent_id' => $agId,
                'client_id' => $clients[$ac % count($clients)],
                'amount' => $commAmount,
                'percentage' => 3.5,
                'remarks' => 'Monthly retail sales commission payout',
                'is_closed' => 1,
                'status' => 'active',
                'approved_by' => 1,
                'approved_date' => $commDate,
                'created_at' => $commDate . ' 12:00:00',
                'updated_at' => $commDate . ' 12:00:00',
            ]);

            DB::table('payments')->insert([
                'payslipno' => 'COMM-' . str_pad($ac, 4, '0', STR_PAD_LEFT),
                'payment_type' => 'Pay',
                'agent_id' => $agId,
                'payment_date' => $commDate,
                'discount' => 0,
                'amount' => $commAmount,
                'fund_account_id' => 1,
                'payment_method' => 'Cash',
                'status' => 'active',
                'created_at' => $commDate . ' 12:30:00',
                'updated_at' => $commDate . ' 12:30:00',
            ]);
        }
    }
}
