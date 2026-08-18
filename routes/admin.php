<?php

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RobotController;
use App\Http\Controllers\Admin\Support\SupportController;
use App\Http\Controllers\Admin\System\ModuleController;
use Illuminate\Support\Facades\Route;

// Backend Route...
Route::group(['prefix' => 'backend', 'as' => 'backend.'], function () {

    // Auth Route...
    Route::post('forget', [App\Http\Controllers\Auth\AdminLoginController::class, 'forget'])->name('forget');
    Route::any('password-reset', [App\Http\Controllers\Auth\AdminLoginController::class, 'reset'])->name('reset');
});

// Authorized Route...
Route::middleware(['auth:admin', 'tenantDB'])->group(function () {

    // Support Route...
    Route::get('support/countries', [SupportController::class, 'countries']);
    Route::get('support/page', [SupportController::class, 'page']);
    Route::get('get-modules', [ModuleController::class, 'getModules']);

    Route::get('/frontend-routes', function () {
        return response()->json(GlobalHelper::getAllRouteNames());
    });

    // Custom Pagination Example Route...
    Route::get('custom/pagination', [ProfileController::class, 'news']);

    // Sitemap Route...
    Route::get('sitemap-data', [App\Http\Controllers\Admin\System\ActivityLogController::class, 'sitemapData'])->name('sitemapData');

    // Auth Route...
    Route::post('logout', [App\Http\Controllers\Auth\AdminLoginController::class, 'logout'])->name('logout');

    // Systems Route...
    Route::get('systems-update', [App\Http\Controllers\Admin\System\RoleController::class, 'systemsRoleUpdate']);
    Route::get('get-permissions', [App\Http\Controllers\Admin\System\RoleController::class, 'getPermissions']);
    Route::get('get-menus/{any?}', [App\Http\Controllers\Admin\System\MenuController::class, 'menus']);
    Route::get('initialize-systems', [App\Http\Controllers\Admin\System\LibController::class, 'systems']);

    // Category Route...
    Route::get('get-category/{type}', [App\Http\Controllers\Admin\CategoryController::class, 'getCategory']);
    Route::get('paynow/{invoiceid}', [App\Http\Controllers\Admin\InvoiceController::class, 'paynow']);

    // Admin Route...
    Route::post('check-old-password', [App\Http\Controllers\Admin\AdminController::class, 'checkOldPassword']);
    Route::post('change-password', [App\Http\Controllers\Admin\AdminController::class, 'passwordChange']);
    Route::get('get-dashboard-menus', [App\Http\Controllers\Admin\System\MenuController::class, 'dashboardMenu']);

    // Module Route...
    Route::view('module', 'admin.layouts.admin_app')->name('module.index');
    Route::get('module/check-model', [App\Http\Controllers\Admin\System\ModuleController::class, 'checkModel']);

    // Table Route...
    Route::get('table-sorting', [App\Http\Controllers\Base\SortingController::class, 'sorting']);
    Route::get('get-last-sorting', [App\Http\Controllers\Base\SortingController::class, 'lastSorting']);

    // Menu Route...
    Route::get('module-delete', [App\Http\Controllers\Admin\System\ModuleController::class, 'moduleDelete'])->name('module.delete');


    Route::get('backend-parent-menus', [App\Http\Controllers\Admin\System\MenuController::class, 'getParentMenu'])->name('backendParentMenu');
    Route::get('getcategories/{modulename?}', [App\Http\Controllers\Admin\System\LibController::class, 'getcategories'])->name('getcategories');
    Route::get('getunits/{modulename?}', [App\Http\Controllers\Admin\System\LibController::class, 'getunits'])->name('getunits');
    Route::get('getpackages/{serviceid?}', [App\Http\Controllers\Admin\System\LibController::class, 'getPackages'])->name('getpackages');
    Route::get('getpackagebyid/{packageid}', [App\Http\Controllers\Admin\System\LibController::class, 'getPackageByID'])->name('getpackagebyid');
    Route::get('getareas/{districtid?}', [App\Http\Controllers\Admin\System\LibController::class, 'getAreas'])->name('getAreas');
    Route::get('clientinfo', [App\Http\Controllers\Admin\ClientController::class, 'getClientInfo'])->name('getClientInfo');
    Route::get('employeeinfo/{employeeid?}', [App\Http\Controllers\Admin\EmployeeController::class, 'getEmployeeInfo'])->name('getEmployeeInfo');
    Route::get('voucherref/{reftype?}', [App\Http\Controllers\Admin\VoucherController::class, 'getVoucherRef'])->name('getVoucherRef');
    Route::get('clients/{service_id?}', [App\Http\Controllers\Admin\ClientController::class, 'getClients'])->name('getClients');
    Route::get('accountsbytype/{accounttype?}', [App\Http\Controllers\Admin\System\LibController::class, 'getAccountByType'])->name('getAccountByType');
    Route::get('getitemsbycategory/{categoryid?}', [App\Http\Controllers\Admin\System\LibController::class, 'getItemsByCategory'])->name('getItemsByCategory');
    Route::get('getitemstock/{itemid}', [App\Http\Controllers\Admin\ItemController::class, 'getItemStock'])->name('getItemStock');
    Route::get('getsalarysheetdata', [App\Http\Controllers\Admin\SalarySheetController::class, 'getsalarysheetdata']);
    Route::get('getclientdueinvoices/{clientid?}', [App\Http\Controllers\Admin\PaymentController::class, 'clientDueInvoices'])->name('Payment.clientDueInvoices');
    Route::get('getclientbyid/{clientid?}', [App\Http\Controllers\Admin\ClientController::class, 'getClientByID'])->name('Client.getClientByID');
    Route::get('getsupplierbyid/{supid?}', [App\Http\Controllers\Admin\SupplierController::class, 'getSupplierByID'])->name('Supplier.getSupplierByID');
    Route::get('getpurchaseinvoices/{supid?}', [App\Http\Controllers\Admin\PaymentController::class, 'supplierPurchaseInvoices'])->name('Payment.supplierPurchaseInvoices');
    Route::get('getulpbandwidths/{ulpid?}', [App\Http\Controllers\Admin\PaymentController::class, 'ULPBandwidth'])->name('Payment.ULPBandwidth');
    Route::get('getemppayabledatas/{empid?}', [App\Http\Controllers\Admin\PaymentController::class, 'getEmpPayableDatas'])->name('Payment.getEmpPayableDatas');
    Route::get('getagentpayabledatas/{agentid?}', [App\Http\Controllers\Admin\PaymentController::class, 'agentPayableDatas'])->name('Payment.agentPayableDatas');
    Route::get('getofficeexpenses', [App\Http\Controllers\Admin\PaymentController::class, 'getOfficeExpenses'])->name('Payment.getOfficeExpenses');
    Route::get('getloanhistories/{empid?}', [App\Http\Controllers\Admin\LoanInfoController::class, 'getLoanHistories'])->name('Payment.getLoanHistories');
    Route::get('getsalaryparameters', [App\Http\Controllers\Admin\SalaryParameterController::class, 'getSalaryParameters'])->name('Payment.getSalaryParameters');
    Route::get('clientsforcommissions/{serviceid?}', [App\Http\Controllers\Admin\CommissionController::class, 'getClientsForCommission'])->name('Commission.clientsforcommissions');
    Route::get('/invoice/{invoice}/months', [App\Http\Controllers\Admin\InvoiceController::class, 'months']);
    Route::get('getbanks', [App\Http\Controllers\Admin\BankController::class, 'getBanks']);
    // Route::get('getservices', [App\Http\Controllers\Admin\ServiceController::class, 'getServices']);
    Route::get('suppliers', [App\Http\Controllers\Admin\SupplierController::class, 'getSuppliers']);
    Route::get('employees', [App\Http\Controllers\Admin\EmployeeController::class, 'getEmployees']);
    // Route::get('getserviceinfo/{serviceid?}', [App\Http\Controllers\Admin\ServiceController::class, 'getServiceInfo']);
    Route::get('getfunds/{fund_account_id?}', [App\Http\Controllers\Admin\PaymentController::class, 'getFundData']);
    Route::get('getfundaccounts', [App\Http\Controllers\Admin\AccountController::class, 'getFundAccounts']);
    Route::get('getfundbalance/{account_id?}', [App\Http\Controllers\Admin\AccountController::class, 'getFundBalance']);
    Route::get('getcurrencies', [App\Http\Controllers\Admin\CurrencyController::class, 'getCurrencies']);
    Route::get('getsalesitems', [App\Http\Controllers\Admin\ItemController::class, 'getSalesItems']);
    Route::post('import-workorder-excel', [App\Http\Controllers\Admin\WorkorderController::class, 'importWorkorderExcel']);

    Route::get('getemploaninstallment/{empid?}', [App\Http\Controllers\Admin\PaymentController::class, 'EmployeeLoanInstallment']);
    Route::get('getworkorders', [App\Http\Controllers\Admin\WorkorderController::class, 'getWorkorders']);
    Route::get('getworkorderinfo/{workorderid}', [App\Http\Controllers\Admin\WorkorderController::class, 'getWorkorderInfo']);
    Route::get('getagentinfo/{id?}', [App\Http\Controllers\Admin\AgentController::class, 'getAgentInfo']);
    Route::get('getagents', [App\Http\Controllers\Admin\AgentController::class, 'getAgents']);

    // POS Terminal API Endpoints
    Route::get('pos/search-items', [App\Http\Controllers\Admin\PosController::class, 'searchItems']);
    Route::get('pos/search-customer', [App\Http\Controllers\Admin\PosController::class, 'searchCustomer']);
    Route::get('pos/validate-serial', [App\Http\Controllers\Admin\PosController::class, 'validateSerial']);
    Route::post('pos/quick-customer', [App\Http\Controllers\Admin\PosController::class, 'quickCustomer']);
    Route::post('pos/checkout', [App\Http\Controllers\Admin\PosController::class, 'checkout']);
    Route::get('pos/search-invoices-return', [App\Http\Controllers\Admin\PosController::class, 'searchInvoicesForReturn']);
    Route::post('pos/process-return', [App\Http\Controllers\Admin\PosController::class, 'processReturn']);
    Route::post('pos/convert-points', [App\Http\Controllers\Admin\PosController::class, 'convertPoints']);
    Route::get('report/coupon', [App\Http\Controllers\Admin\ReportController::class, 'coupon']);

    // Label Print & Barcode Utility Routes
    Route::get('generate-item-barcode', [App\Http\Controllers\Admin\ItemController::class, 'getGeneratedBarcode']);

    // User Permission Based Routing...
    Route::middleware('auth.access')->group(function () {
        // Dashboard Route...
        Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard.index');

        // Profile route
        Route::get('profile', [AdminController::class, 'profileDetails'])->name('profile.profileDetails');
        Route::put('profile', [AdminController::class, 'updateProfile'])->name('profile.updateProfile');

        // Module Route...
        Route::match(['get', 'post'], 'module/create', [App\Http\Controllers\Admin\System\ModuleController::class, 'create'])->name('module.create');

        // Activity Log Route...
        Route::get('activityLog', [App\Http\Controllers\Admin\System\ActivityLogController::class, 'index'])->name('activityLog.index');
        Route::get('activityLog/{id}', [App\Http\Controllers\Admin\System\ActivityLogController::class, 'show'])->name('activityLog.show');
        Route::get('allRead', [App\Http\Controllers\Admin\System\ActivityLogController::class, 'allRead'])->name('activityLog.allRead');
        Route::delete('activityLog/{id}', [App\Http\Controllers\Admin\System\ActivityLogController::class, 'destroy'])->name('activityLog.destroy');
        Route::post('activityLog/{activity}/restore', [App\Http\Controllers\Admin\System\ActivityLogController::class, 'restore'])->name('activityLog.restore');

        // Admin Route...
        Route::resource('role', App\Http\Controllers\Admin\System\RoleController::class);
        Route::resource('menu', App\Http\Controllers\Admin\System\MenuController::class);

        Route::get('siteSetting', [App\Http\Controllers\Admin\System\SiteSettingController::class, 'show'])->name('siteSetting.show');
        Route::post('siteSetting', [App\Http\Controllers\Admin\System\SiteSettingController::class, 'store'])->name('siteSetting.store');
        Route::get('siteSetting/edit', [App\Http\Controllers\Admin\System\SiteSettingController::class, 'edit'])->name('siteSetting.store');

        Route::resource('admin', App\Http\Controllers\Admin\AdminController::class);
        // Route::resource('plugin', App\Http\Controllers\Admin\PluginController::class);
        // robot txt
        Route::get('/robots', [App\Http\Controllers\Admin\RobotController::class, 'index'])->name('robots.index');
        Route::get('robots-file', [App\Http\Controllers\Admin\RobotController::class, 'getfilecontent'])->name('robots.getfilecontent');
        Route::post('robots-file', [RobotController::class, 'savefilecontent'])->name('robots.savefilecontent');

        // for xml
        Route::get('/searchData', [App\Http\Controllers\Admin\XMLController::class, 'index'])->name('searchData.index');

        Route::get('/generate-xml', [App\Http\Controllers\Admin\XMLController::class, 'generateXmlFile'])->name('searchData.generateXmlFile');
        Route::get('/xml-file', [App\Http\Controllers\Admin\XMLController::class, 'getXmlFile'])->name('searchData.getXmlFile');
        Route::get('/get-broken-url', [App\Http\Controllers\Admin\XMLController::class, 'getBrokenUrl'])->name('searchData.getBrokenUrl');

        Route::get('bulkdataimport/employee', [App\Http\Controllers\Admin\BulkDataImportController::class, 'employee'])->name('bulkdataimport.employee');
        Route::get('bulkdataimport/client', [App\Http\Controllers\Admin\BulkDataImportController::class, 'client'])->name('bulkdataimport.client');

        Route::post('bulkdataimport/empimport', [App\Http\Controllers\Admin\BulkDataImportController::class, 'empimport'])->name('bulkdataimport.empimport');
        Route::post('bulkdataimport/clientimport', [App\Http\Controllers\Admin\BulkDataImportController::class, 'clientimport'])->name('bulkdataimport.clientimport');

        // invoice extra action
        Route::get('invoice/bill/{id}', [App\Http\Controllers\Admin\InvoiceController::class, 'bill'])->name('invoice.bill');
        Route::get('invoice/moneyreceipt/{id}', [App\Http\Controllers\Admin\InvoiceController::class, 'moneyreceipt'])->name('invoice.moneyreceipt');
        
        Route::resource('category', App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('mediaValidator', App\Http\Controllers\Admin\MediaValidatorController::class);
        Route::resource('helpInfo', App\Http\Controllers\Admin\HelpInfoController::class);
        // Route::resource('service', App\Http\Controllers\Admin\ServiceController::class);
        Route::resource('invoice', App\Http\Controllers\Admin\InvoiceController::class);
        Route::resource('unit', App\Http\Controllers\Admin\UnitController::class);
        Route::resource('client', App\Http\Controllers\Admin\ClientController::class);
        Route::resource('district', App\Http\Controllers\Admin\DistrictController::class);
        Route::resource('area', App\Http\Controllers\Admin\AreaController::class);
        Route::resource('branch', App\Http\Controllers\Admin\BranchController::class);
        Route::resource('designation', App\Http\Controllers\Admin\DesignationController::class);
        Route::resource('employee', App\Http\Controllers\Admin\EmployeeController::class);
        Route::resource('account', App\Http\Controllers\Admin\AccountController::class);
        Route::resource('supplier', App\Http\Controllers\Admin\SupplierController::class);
        Route::resource('financialYear', App\Http\Controllers\Admin\FinancialYearController::class);
        Route::resource('voucher', App\Http\Controllers\Admin\VoucherController::class);
        Route::resource('expense', App\Http\Controllers\Admin\ExpenseController::class);
        Route::resource('purchase', App\Http\Controllers\Admin\PurchaseController::class);
        Route::resource('item', App\Http\Controllers\Admin\ItemController::class);
        // Route::resource('issue', App\Http\Controllers\Admin\IssueController::class);
        Route::resource('loanInfo', App\Http\Controllers\Admin\LoanInfoController::class);
        Route::resource('salarySheet', App\Http\Controllers\Admin\SalarySheetController::class);
        Route::resource('payment', App\Http\Controllers\Admin\PaymentController::class);
        Route::resource('salaryParameter', App\Http\Controllers\Admin\SalaryParameterController::class);
        Route::resource('agent', App\Http\Controllers\Admin\AgentController::class);
        Route::resource('commission', App\Http\Controllers\Admin\CommissionController::class);
        Route::resource('bank', App\Http\Controllers\Admin\BankController::class);
        Route::resource('fundTransfer', App\Http\Controllers\Admin\FundTransferController::class);
        Route::resource('withdrawl', App\Http\Controllers\Admin\WithdrawlController::class);
        Route::resource('workorder', App\Http\Controllers\Admin\WorkorderController::class);
        Route::resource('currency', App\Http\Controllers\Admin\CurrencyController::class);
        Route::resource('challan', App\Http\Controllers\Admin\ChallanController::class);
        Route::resource('color', App\Http\Controllers\Admin\ColorController::class);
        Route::resource('size', App\Http\Controllers\Admin\SizeController::class);
        Route::get('warrantyClaim/check-serial', [App\Http\Controllers\Admin\WarrantyClaimController::class, 'checkSerial'])->name('warrantyClaim.checkSerial');
        Route::post('warrantyClaim/{id}/add-log', [App\Http\Controllers\Admin\WarrantyClaimController::class, 'addTrackingLog'])->name('warrantyClaim.addLog');
        Route::resource('warrantyClaim', App\Http\Controllers\Admin\WarrantyClaimController::class);

        // POS Terminal View Routes
        Route::get('pos', [App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos.index');
        Route::get('pos/return', [App\Http\Controllers\Admin\PosController::class, 'return'])->name('pos.return');
        Route::get('pos/labelprint', [App\Http\Controllers\Admin\PosController::class, 'labelprint'])->name('pos.labelprint');
        
        //Report
        Route::get('report/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('report.sales');
        Route::get('report/itemladger', [App\Http\Controllers\Admin\ReportController::class, 'itemladger'])->name('report.itemladger');
        Route::get('report/availablestock', [App\Http\Controllers\Admin\ReportController::class, 'availablestock'])->name('report.availablestock');
        Route::get('report/incomestatement', [App\Http\Controllers\Admin\ReportController::class, 'incomestatement'])->name('report.incomestatement');
        Route::get('report/expensestatement', [App\Http\Controllers\Admin\ReportController::class, 'expensestatement'])->name('report.expensestatement');
        Route::get('report/cashbook', [App\Http\Controllers\Admin\ReportController::class, 'cashbook'])->name('report.cashbook');
        Route::get('report/ledger', [App\Http\Controllers\Admin\ReportController::class, 'ledger'])->name('report.ledger');
        Route::get('report/trialbalance', [App\Http\Controllers\Admin\ReportController::class, 'trialbalance'])->name('report.trialbalance');
        Route::get('report/receivable', [App\Http\Controllers\Admin\ReportController::class, 'receivable'])->name('report.receivable');
        Route::get('report/payable', [App\Http\Controllers\Admin\ReportController::class, 'payable'])->name('report.payable');
        Route::get('report/balancesheet', [App\Http\Controllers\Admin\ReportController::class, 'balancesheet'])->name('report.balancesheet');
        Route::get('report/clientledger', [App\Http\Controllers\Admin\ReportController::class, 'clientLedger'])->name('report.clientledger');
        Route::get('report/supplierledger', [App\Http\Controllers\Admin\ReportController::class, 'supplierledger'])->name('report.supplierledger');
        Route::get('report/agentledger', [App\Http\Controllers\Admin\ReportController::class, 'agentledger'])->name('report.agentledger');
        Route::get('report/employeeledger', [App\Http\Controllers\Admin\ReportController::class, 'employeeledger'])->name('report.employeeledger');
        Route::get('report/funds', [App\Http\Controllers\Admin\ReportController::class, 'funds'])->name('report.funds');
        Route::get('report/coupon', [App\Http\Controllers\Admin\ReportController::class, 'coupon'])->name('report.coupon');
        Route::get('report/serial', [App\Http\Controllers\Admin\ReportController::class, 'serial'])->name('report.serial');
        Route::get('report/warrantyclaim', [App\Http\Controllers\Admin\ReportController::class, 'warrantyclaim'])->name('report.warrantyclaim');
        Route::get('report/warrantyClaim', [App\Http\Controllers\Admin\ReportController::class, 'warrantyclaim'])->name('report.warrantyClaim');

        Route::post('expense/approved', [App\Http\Controllers\Admin\ExpenseController::class, 'approved'])->name('expense.approved');
        Route::post('expense/approvalcancel', [App\Http\Controllers\Admin\ExpenseController::class, 'approvalcancel'])->name('expense.approvalcancel');

        Route::post('salarySheet/approved', [App\Http\Controllers\Admin\SalarySheetController::class, 'approved'])->name('salarySheet.approved');
        Route::post('salarySheet/approvalcancel', [App\Http\Controllers\Admin\SalarySheetController::class, 'approvalcancel'])->name('salarySheet.approvalcancel');
        Route::post('salarySheet/paynow', [App\Http\Controllers\Admin\SalarySheetController::class, 'paynow'])->name('salarySheet.paynow');
        Route::post('salarySheet/paynow', [App\Http\Controllers\Admin\SalarySheetController::class, 'paynow'])->name('salarySheet.paynow');

        Route::post('commission/approved', [App\Http\Controllers\Admin\CommissionController::class, 'approved'])->name('commission.approved');
        Route::post('commission/approvalcancel', [App\Http\Controllers\Admin\CommissionController::class, 'approvalcancel'])->name('commission.approvalcancel');

        Route::post('loanInfo/approved', [App\Http\Controllers\Admin\LoanInfoController::class, 'approved'])->name('loanInfo.approved');
        Route::post('loanInfo/approvalcancel', [App\Http\Controllers\Admin\LoanInfoController::class, 'approvalcancel'])->name('loanInfo.approvalcancel');

        // Resident Approval & Actions Start

    });
    Route::get('mediaValidator/{modelName}/getFields', [App\Http\Controllers\Admin\MediaValidatorController::class, 'getFields'])->name('mediaValidator.getFields');
    Route::get('helpInfo/{modelName}/{pageType}/', [App\Http\Controllers\Admin\HelpInfoController::class, 'getHelpInfo'])->name('get.HelpInfo');
});
