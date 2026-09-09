<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1">
            <i class="fas fa-calendar-alt me-1 text-primary"></i>Quick Date:
          </span>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'today' }" @click="applyDatePreset('today')">Today</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'yesterday' }" @click="applyDatePreset('yesterday')">Yesterday</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'last7' }" @click="applyDatePreset('last7')">Last 7 Days</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisMonth' }" @click="applyDatePreset('thisMonth')">This Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'lastMonth' }" @click="applyDatePreset('lastMonth')">Last Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisYear' }" @click="applyDatePreset('thisYear')">This Year</button>
          <button type="button" class="btn btn-xs btn-outline-secondary" :class="{ 'active': activePreset === 'all' }" @click="applyDatePreset('all')">All Time</button>
        </div>
      </div>

      <!-- Date Range -->
      <date-picker id="searchfromincomedate" v-model="search_data.from_date" field="search_data.from_date"
        title="From Date" placeholder="From Date" col="3" :req="false"></date-picker>
      <date-picker id="searchtoincomedate" v-model="search_data.to_date" field="search_data.to_date"
        title="To Date" placeholder="To Date" col="3" :req="false"></date-picker>

      <!-- Customer / Client -->
      <v-select-container title="Customer (গ্রাহক)" field="search_data.client_id" col="3">
        <v-select v-model="search_data.client_id" label="name" :reduce="obj => obj.id" :options="clients"
          placeholder="-- All Customers --" :closeOnSelect="true">
          <template #option="option">
            <div>
              <strong>{{ option.name }}</strong>
              <small class="text-muted d-block" v-if="option.mobile">{{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Category Filter -->
      <v-select-container title="Category (ক্যাটাগরি)" field="search_data.category_id" col="3">
        <v-select v-model="search_data.category_id" label="title" :reduce="obj => obj.id" :options="categories"
          placeholder="-- All Categories --" :closeOnSelect="true" />
      </v-select-container>

      <!-- Item Filter -->
      <v-select-container title="Item (পণ্য)" field="search_data.item_id" col="3">
        <v-select v-model="search_data.item_id" label="title" :reduce="obj => obj.id" :options="items"
          placeholder="-- All Items --" :closeOnSelect="true">
          <template #option="option">
            <div>
              <span>{{ option.title }}</span>
              <small class="text-muted d-block" v-if="option.barcode">Barcode: {{ option.barcode }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Invoice No Search -->
      <Input v-model="search_data.invoice_no" field="search_data.invoice_no" title="Invoice No (ইনভয়েস নং)"
        placeholder="e.g. POS-..." col="3" :req="false" />

      <!-- Sale Type Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label">Sale Type (বিক্রয়ের ধরন)</label>
          <select class="form-select form-select-sm" v-model="search_data.sale_type">
            <option value="all">-- All Sales --</option>
            <option value="pos">POS Sales (পিওএস বিক্রয়)</option>
            <option value="general">General Invoices (সাধারণ ইনভয়েস)</option>
          </select>
        </div>
      </div>
    </template>

    <!-- 📊 Report Content & Analysis View -->
    <template v-slot:table-list>
      <!-- Top Action Bar & Tabs -->
      <div class="col-md-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <!-- View Mode Tabs -->
          <ul class="nav nav-pills custom-report-tabs">
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'overview' }" @click="activeTab = 'overview'">
                <i class="fas fa-chart-pie me-1"></i> P&L Overview (আয়-ব্যয় সারাংশ)
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'invoices' }" @click="activeTab = 'invoices'">
                <i class="fas fa-file-invoice me-1"></i> Invoices & Profit (ইনভয়েস সমূহ) ({{ invoices.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'items' }" @click="activeTab = 'items'">
                <i class="fas fa-boxes me-1"></i> Item Profit (পণ্যভিত্তিক লাভ) ({{ item_breakdown.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'expenses' }" @click="activeTab = 'expenses'">
                <i class="fas fa-receipt me-1"></i> Expenses (খরচ) ({{ expenses.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'salaries' }" @click="activeTab = 'salaries'">
                <i class="fas fa-user-tie me-1"></i> Salaries (বেতন) ({{ salaries.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'loans' }" @click="activeTab = 'loans'">
                <i class="fas fa-hand-holding-usd me-1"></i> Loans/Advances (ঋণ) ({{ loans.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'commissions' }" @click="activeTab = 'commissions'">
                <i class="fas fa-percentage me-1"></i> Commissions (কমিশন) ({{ commissions.length }})
              </button>
            </li>
          </ul>

          <!-- Print & Export Buttons -->
          <div class="d-flex align-items-center gap-2">
            <download-excel
              v-if="exportData.length > 0"
              class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
              :data="exportData"
              :name="exportFileName">
              <i class="fas fa-file-excel"></i> Export Excel
            </download-excel>
            <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
              data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Print Report"
              v-x-tooltip @click="print('printArea', model)">
              <i class="fas fa-print"></i> Print Report
            </button>
          </div>
        </div>
      </div>

      <!-- 🌟 Executive Financial KPI Cards (Always Visible) -->
      <div class="col-12 mb-4">
        <div class="row g-3">
          <!-- Card 1: Net Sales Revenue -->
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100 position-relative overflow-hidden">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Net Sales (মোট বিক্রয়)</div>
                  <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(summary.net_sales) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-shopping-cart"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-truncate">
                Gross: ৳{{ formatMoney(summary.gross_sales) }} | Disc: ৳{{ formatMoney(summary.total_discount) }}
              </small>
            </div>
          </div>

          <!-- Card 2: Cost of Goods Sold (Purchase Cost) -->
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-purple text-white h-100 position-relative overflow-hidden">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">COGS (ক্রয় খরচ)</div>
                  <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(summary.total_cogs) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-boxes"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-truncate">
                Qty Sold: {{ Number(summary.total_qty_sold || 0).toLocaleString() }} Pcs
              </small>
            </div>
          </div>

          <!-- Card 3: Gross Profit -->
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-teal text-white h-100 position-relative overflow-hidden">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Gross Profit (মোট লাভ)</div>
                  <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(summary.gross_profit) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-coins"></i></div>
              </div>
              <div class="mt-2 d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark fw-bold">Margin: {{ summary.gross_profit_margin }}%</span>
                <small class="text-white-50 text-truncate">Sales − Purchase Cost</small>
              </div>
            </div>
          </div>

          <!-- Card 4: Operating Deductions & Expenses -->
          <div class="col-xl-2 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-warning text-white h-100 position-relative overflow-hidden">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Expenses (মোট কর্তন)</div>
                  <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(summary.total_deductions) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-wallet"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-truncate" title="Exp + Salary + Loan + Commission">
                Exp: ৳{{ formatMoney(summary.total_expenses) }} | Sal: ৳{{ formatMoney(summary.total_salaries) }}
              </small>
            </div>
          </div>

          <!-- Card 5: Net Profit / Loss -->
          <div class="col-xl-3 col-lg-6 col-md-12 col-sm-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-white h-100 position-relative overflow-hidden"
              :class="summary.is_profitable ? 'bg-gradient-success' : 'bg-gradient-danger'">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">
                    {{ summary.is_profitable ? 'Net Profit (নিট লাভ)' : 'Net Loss (নিট ক্ষতি)' }}
                  </div>
                  <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(summary.net_profit) }}</div>
                </div>
                <div class="metric-icon">
                  <i :class="summary.is_profitable ? 'fas fa-trophy' : 'fas fa-exclamation-triangle'"></i>
                </div>
              </div>
              <div class="mt-2 d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark fw-bold">Net Margin: {{ summary.net_profit_margin }}%</span>
                <small class="text-white-50">Gross Profit − Deductions</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 🖨️ Printable Area & Report Views -->
      <div class="col-12" id="printArea">
        <!-- 🧾 Company Header (Always visible in print) -->
        <div class="text-center mb-3 report-title">
          <h3 class="fw-bold">{{ $root.site?.title || 'QPOS Management System' }}</h3>
          <p class="mb-1 text-muted">{{ $root.site?.address || '' }}</p>
          <p class="text-muted small mb-2">
            Email: {{ $root.site?.contact_email || 'N/A' }} | Phone: {{ $root.site?.mobile1 || 'N/A' }}
          </p>
          <div class="border-top border-bottom py-2 my-2 bg-light">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-file-invoice-dollar me-1"></i>
              Income Statement & Sales Profit (আয়-ব্যয় ও বিক্রয় লাভ প্রতিবেদন)
            </h5>
            <small class="text-muted">
              Period: <strong>{{ dateRangeDisplay }}</strong> | Generated on: <strong>{{ reportDate }}</strong>
            </small>
          </div>
        </div>

        <!-- 📌 TAB 0: P&L Financial Statement Overview -->
        <div v-show="activeTab === 'overview'" class="mb-4">
          <div class="row g-4">
            <!-- Left Side: Formal Income Statement Table -->
            <div class="col-lg-8 col-md-12">
              <div class="card border shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                  <h6 class="mb-0 fw-bold"><i class="fas fa-balance-scale me-2"></i>Income Statement (আয়-ব্যয় বিবরণী)</h6>
                  <span class="badge bg-primary">{{ dateRangeDisplay }}</span>
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0 financial-table">
                    <thead class="table-light">
                      <tr>
                        <th width="60%">Particulars / Heads (বিবরণ)</th>
                        <th width="20%" class="text-end">Amount (টাকা)</th>
                        <th width="20%" class="text-end">Net Total (টাকা)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- 1. Operating Revenue -->
                      <tr class="table-primary fw-bold">
                        <td colspan="3"><i class="fas fa-arrow-circle-right me-1 text-primary"></i> 1. Operating Revenue (বিক্রয় রাজস্ব)</td>
                      </tr>
                      <tr>
                        <td class="ps-4">Gross Sales (মোট বিক্রয় মূল্য)</td>
                        <td class="text-end">৳ {{ formatMoney(summary.gross_sales) }}</td>
                        <td></td>
                      </tr>
                      <tr v-if="summary.total_discount > 0">
                        <td class="ps-4 text-danger">Less: Sales Discounts (বাদ: বিক্রয় ছাড়)</td>
                        <td class="text-end text-danger">(৳ {{ formatMoney(summary.total_discount) }})</td>
                        <td></td>
                      </tr>
                      <tr v-if="summary.total_vat > 0">
                        <td class="ps-4 text-muted">Add: VAT / Tax (যোগ: ভ্যাট)</td>
                        <td class="text-end">৳ {{ formatMoney(summary.total_vat) }}</td>
                        <td></td>
                      </tr>
                      <tr class="fw-bold bg-light">
                        <td class="ps-4">Total Net Sales Revenue (মোট নিট বিক্রয়)</td>
                        <td></td>
                        <td class="text-end text-primary">৳ {{ formatMoney(summary.net_sales) }}</td>
                      </tr>

                      <!-- 2. Cost of Goods Sold (COGS) -->
                      <tr class="table-secondary fw-bold">
                        <td colspan="3"><i class="fas fa-arrow-circle-right me-1 text-secondary"></i> 2. Cost of Goods Sold (পণ্য ক্রয় খরচ / COGS)</td>
                      </tr>
                      <tr>
                        <td class="ps-4">Total Purchase Cost of Sold Inventory (বিক্রিত পণ্যের মোট ক্রয়মূল্য)</td>
                        <td class="text-end text-danger">৳ {{ formatMoney(summary.total_cogs) }}</td>
                        <td></td>
                      </tr>
                      <tr class="fw-bold bg-light">
                        <td class="ps-4">Total COGS</td>
                        <td></td>
                        <td class="text-end text-danger">(৳ {{ formatMoney(summary.total_cogs) }})</td>
                      </tr>

                      <!-- 3. Gross Profit -->
                      <tr class="table-success fw-bold fs-6">
                        <td><i class="fas fa-plus-circle me-1 text-success"></i> 3. Gross Profit (মোট লাভ)</td>
                        <td class="text-center"><span class="badge bg-success">Margin: {{ summary.gross_profit_margin }}%</span></td>
                        <td class="text-end text-success">৳ {{ formatMoney(summary.gross_profit) }}</td>
                      </tr>

                      <!-- 4. Operating Expenses & Deductions -->
                      <tr class="table-warning fw-bold">
                        <td colspan="3"><i class="fas fa-arrow-circle-right me-1 text-warning"></i> 4. Operating Expenses & Deductions (পরিচালন ব্যয় ও কর্তন)</td>
                      </tr>
                      <tr>
                        <td class="ps-4">
                          <a href="javascript:void(0)" @click="activeTab = 'expenses'" class="text-decoration-none text-dark">
                            <i class="fas fa-receipt me-1 text-muted"></i> General & Office Expenses (অফিস ও পরিচালন খরচ)
                          </a>
                        </td>
                        <td class="text-end">৳ {{ formatMoney(summary.total_expenses) }}</td>
                        <td></td>
                      </tr>
                      <tr>
                        <td class="ps-4">
                          <a href="javascript:void(0)" @click="activeTab = 'salaries'" class="text-decoration-none text-dark">
                            <i class="fas fa-user-tie me-1 text-muted"></i> Employee Salary Sheets (কর্মচারীদের বেতন)
                          </a>
                        </td>
                        <td class="text-end">৳ {{ formatMoney(summary.total_salaries) }}</td>
                        <td></td>
                      </tr>
                      <tr>
                        <td class="ps-4">
                          <a href="javascript:void(0)" @click="activeTab = 'loans'" class="text-decoration-none text-dark">
                            <i class="fas fa-hand-holding-usd me-1 text-muted"></i> Disbursed Loans & Advances (কর্মচারী ঋণ ও অগ্রিম)
                          </a>
                        </td>
                        <td class="text-end">৳ {{ formatMoney(summary.total_loans) }}</td>
                        <td></td>
                      </tr>
                      <tr>
                        <td class="ps-4">
                          <a href="javascript:void(0)" @click="activeTab = 'commissions'" class="text-decoration-none text-dark">
                            <i class="fas fa-percentage me-1 text-muted"></i> Agent & Staff Commissions (এজেন্ট ও স্টাফ কমিশন)
                          </a>
                        </td>
                        <td class="text-end">৳ {{ formatMoney(summary.total_commissions) }}</td>
                        <td></td>
                      </tr>
                      <tr class="fw-bold bg-light">
                        <td class="ps-4">Total Operating Deductions (সর্বমোট পরিচালন খরচ)</td>
                        <td></td>
                        <td class="text-end text-warning text-dark">(৳ {{ formatMoney(summary.total_deductions) }})</td>
                      </tr>

                      <!-- 5. Net Profit / (Loss) -->
                      <tr class="fw-bold fs-5" :class="summary.is_profitable ? 'table-success text-success' : 'table-danger text-danger'">
                        <td>
                          <i :class="summary.is_profitable ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger'" class="me-1"></i>
                          5. Net Profit / (Net Loss) (চূড়ান্ত নিট লাভ / ক্ষতি)
                        </td>
                        <td class="text-center">
                          <span class="badge" :class="summary.is_profitable ? 'bg-success' : 'bg-danger'">
                            Net Margin: {{ summary.net_profit_margin }}%
                          </span>
                        </td>
                        <td class="text-end fw-bolder">৳ {{ formatMoney(summary.net_profit) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Right Side: Visual Ratio Breakdown & Fast Stats -->
            <div class="col-lg-4 col-md-12">
              <!-- Deductions Breakdown Card -->
              <div class="card border shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light fw-bold">
                  <i class="fas fa-pie-chart me-1 text-primary"></i> Deductions Breakdown (খরচের বণ্টন)
                </div>
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="fas fa-circle text-primary me-1"></i> General Expenses:</span>
                    <strong class="small">৳ {{ formatMoney(summary.total_expenses) }}</strong>
                  </div>
                  <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-primary" :style="{ width: getDeductionPercent(summary.total_expenses) + '%' }"></div>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="fas fa-circle text-success me-1"></i> Salaries:</span>
                    <strong class="small">৳ {{ formatMoney(summary.total_salaries) }}</strong>
                  </div>
                  <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-success" :style="{ width: getDeductionPercent(summary.total_salaries) + '%' }"></div>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="fas fa-circle text-warning me-1"></i> Loans / Advances:</span>
                    <strong class="small">৳ {{ formatMoney(summary.total_loans) }}</strong>
                  </div>
                  <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar bg-warning" :style="{ width: getDeductionPercent(summary.total_loans) + '%' }"></div>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small"><i class="fas fa-circle text-info me-1"></i> Commissions:</span>
                    <strong class="small">৳ {{ formatMoney(summary.total_commissions) }}</strong>
                  </div>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-info" :style="{ width: getDeductionPercent(summary.total_commissions) + '%' }"></div>
                  </div>
                </div>
              </div>

              <!-- Profitability Health Check Card -->
              <div class="card border shadow-sm rounded-3">
                <div class="card-header bg-light fw-bold">
                  <i class="fas fa-heartbeat me-1 text-danger"></i> Performance Highlights
                </div>
                <div class="card-body p-3">
                  <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                      <span class="text-muted">Invoices Processed:</span>
                      <strong>{{ summary.total_invoices }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                      <span class="text-muted">Total Quantity Sold:</span>
                      <strong>{{ Number(summary.total_qty_sold || 0).toLocaleString() }} Pcs</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                      <span class="text-muted">Cash / Paid Collected:</span>
                      <strong class="text-success">৳ {{ formatMoney(summary.total_paid) }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                      <span class="text-muted">Outstanding Sales Due:</span>
                      <strong class="text-danger">৳ {{ formatMoney(summary.total_due) }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                      <span class="text-muted">Gross Profit Margin:</span>
                      <strong class="text-primary">{{ summary.gross_profit_margin }}%</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                      <span class="text-muted">Net Profit Margin:</span>
                      <strong :class="summary.is_profitable ? 'text-success' : 'text-danger'">
                        {{ summary.net_profit_margin }}%
                      </strong>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 📌 TAB 1: Invoices & Profit per Sale Table -->
        <div v-show="activeTab === 'invoices'">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="fas fa-file-invoice me-1 text-primary"></i>
              Sales Invoices & Per-Sale Profit Breakdown (ইনভয়েস ভিত্তিক লাভ-ক্ষতি)
            </h6>
            <span class="badge bg-secondary">{{ invoices.length }} Invoices Found</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="3%">#</th>
                  <th width="9%">Date</th>
                  <th width="11%">Invoice No</th>
                  <th width="14%">Customer Name</th>
                  <th width="6%">Qty</th>
                  <th width="9%">Gross Sales</th>
                  <th width="7%">Discount</th>
                  <th width="9%">Net Sales</th>
                  <th width="9%">Purchase Cost</th>
                  <th width="9%">Gross Profit</th>
                  <th width="6%">Margin</th>
                  <th width="8%" class="d-print-none">Items</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="invoices.length > 0">
                  <template v-for="(inv, index) in invoices" :key="inv.id">
                    <tr :class="{ 'table-active': expandedInvoices.includes(inv.id) }">
                      <td class="text-center">{{ index + 1 }}</td>
                      <td class="text-center font-monospace">{{ inv.invoice_date }}</td>
                      <td class="font-monospace fw-bold text-primary">{{ inv.invoice_no }}</td>
                      <td>
                        <strong>{{ inv.client_name }}</strong>
                        <small class="text-muted d-block" v-if="inv.client_mobile !== 'N/A'">{{ inv.client_mobile }}</small>
                      </td>
                      <td class="text-center fw-bold">{{ inv.total_qty }}</td>
                      <td class="text-end">৳ {{ formatMoney(inv.gross_amount) }}</td>
                      <td class="text-end text-danger">{{ inv.discount > 0 ? '৳ ' + formatMoney(inv.discount) : '-' }}</td>
                      <td class="text-end fw-bold">৳ {{ formatMoney(inv.net_amount) }}</td>
                      <td class="text-end text-muted">৳ {{ formatMoney(inv.total_cogs) }}</td>
                      <td class="text-end fw-bold" :class="inv.profit >= 0 ? 'text-success' : 'text-danger'">
                        ৳ {{ formatMoney(inv.profit) }}
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="inv.margin_percent >= 0 ? 'bg-success' : 'bg-danger'">
                          {{ inv.margin_percent }}%
                        </span>
                      </td>
                      <td class="text-center d-print-none">
                        <button type="button" class="btn btn-xs" :class="expandedInvoices.includes(inv.id) ? 'btn-secondary' : 'btn-outline-primary'"
                          @click="toggleInvoiceExpand(inv.id)">
                          <i :class="expandedInvoices.includes(inv.id) ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                          {{ expandedInvoices.includes(inv.id) ? 'Hide' : 'View (' + inv.details.length + ')' }}
                        </button>
                      </td>
                    </tr>

                    <!-- Expanded Line Items Table -->
                    <tr v-if="expandedInvoices.includes(inv.id)" class="bg-light d-print-table-row">
                      <td colspan="12" class="p-3">
                        <div class="card border shadow-sm">
                          <div class="card-header bg-white py-1 px-3 fw-bold small text-primary d-flex justify-content-between">
                            <span><i class="fas fa-boxes me-1"></i> Invoice Line Items (পণ্যসমূহ) - {{ inv.invoice_no }}</span>
                            <span class="text-muted">Customer: {{ inv.client_name }}</span>
                          </div>
                          <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 small">
                              <thead class="table-secondary text-center">
                                <tr>
                                  <th width="3%">#</th>
                                  <th width="24%">Item Title (পণ্যের নাম)</th>
                                  <th width="12%">Barcode</th>
                                  <th width="10%">Variant</th>
                                  <th width="6%">Qty</th>
                                  <th width="10%">Unit Purchase</th>
                                  <th width="10%">Unit Sale</th>
                                  <th width="10%">Total Cost</th>
                                  <th width="10%">Total Sale</th>
                                  <th width="10%">Line Profit</th>
                                  <th width="5%">Margin</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(detail, dIdx) in inv.details" :key="dIdx">
                                  <td class="text-center">{{ dIdx + 1 }}</td>
                                  <td>
                                    <strong>{{ detail.item_title }}</strong>
                                    <span v-if="detail.serial_no" class="badge bg-dark ms-1">SN: {{ detail.serial_no }}</span>
                                  </td>
                                  <td class="font-monospace text-center">{{ detail.barcode }}</td>
                                  <td class="text-center">
                                    <span v-if="detail.color_title" class="badge bg-secondary me-1">{{ detail.color_title }}</span>
                                    <span v-if="detail.size_title" class="badge bg-info text-dark">{{ detail.size_title }}</span>
                                    <span v-if="!detail.color_title && !detail.size_title" class="text-muted">-</span>
                                  </td>
                                  <td class="text-center fw-bold">{{ detail.qty }}</td>
                                  <td class="text-end text-muted">৳ {{ formatMoney(detail.unit_purchase_cost) }}</td>
                                  <td class="text-end">৳ {{ formatMoney(detail.unit_sale_price) }}</td>
                                  <td class="text-end text-danger">৳ {{ formatMoney(detail.line_purchase_cost) }}</td>
                                  <td class="text-end text-primary fw-bold">৳ {{ formatMoney(detail.line_sale_amount) }}</td>
                                  <td class="text-end fw-bold" :class="detail.line_profit >= 0 ? 'text-success' : 'text-danger'">
                                    ৳ {{ formatMoney(detail.line_profit) }}
                                  </td>
                                  <td class="text-center">
                                    <span class="badge" :class="detail.line_margin_percent >= 0 ? 'bg-success' : 'bg-danger'">
                                      {{ detail.line_margin_percent }}%
                                    </span>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </template>
                <tr v-else>
                  <td colspan="12" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No sales invoices found for the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="invoices.length > 0">
                <tr>
                  <td colspan="4" class="text-end">Total (সর্বমোট):</td>
                  <td class="text-center">{{ summary.total_qty_sold }}</td>
                  <td class="text-end">৳ {{ formatMoney(summary.gross_sales) }}</td>
                  <td class="text-end text-danger">৳ {{ formatMoney(summary.total_discount) }}</td>
                  <td class="text-end text-primary">৳ {{ formatMoney(summary.net_sales) }}</td>
                  <td class="text-end text-muted">৳ {{ formatMoney(summary.total_cogs) }}</td>
                  <td class="text-end text-success">৳ {{ formatMoney(summary.gross_profit) }}</td>
                  <td class="text-center">{{ summary.gross_profit_margin }}%</td>
                  <td class="d-print-none"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 TAB 2: Item-wise Sales & Profit Breakdown -->
        <div v-show="activeTab === 'items'">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="fas fa-boxes me-1 text-primary"></i>
              Item-wise Sales & Profitability Analysis (পণ্যভিত্তিক বিক্রয় ও লাভ)
            </h6>
            <span class="badge bg-secondary">{{ item_breakdown.length }} Unique Items</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="3%">#</th>
                  <th width="22%">Item Name (পণ্যের নাম)</th>
                  <th width="11%">Barcode</th>
                  <th width="12%">Category</th>
                  <th width="10%">Variant</th>
                  <th width="6%">Qty Sold</th>
                  <th width="9%">Avg Purchase</th>
                  <th width="9%">Avg Sale</th>
                  <th width="9%">Total Cost</th>
                  <th width="9%">Total Sales</th>
                  <th width="9%">Gross Profit</th>
                  <th width="6%">Margin</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="item_breakdown.length > 0">
                  <tr v-for="(item, idx) in item_breakdown" :key="idx">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td><strong>{{ item.item_title }}</strong></td>
                    <td class="font-monospace text-center">{{ item.barcode }}</td>
                    <td>{{ item.category_title }}</td>
                    <td class="text-center">
                      <span v-if="item.color_title" class="badge bg-secondary me-1">{{ item.color_title }}</span>
                      <span v-if="item.size_title" class="badge bg-info text-dark">{{ item.size_title }}</span>
                      <span v-if="!item.color_title && !item.size_title" class="text-muted">-</span>
                    </td>
                    <td class="text-center fw-bold">{{ item.total_qty }} {{ item.unit_title }}</td>
                    <td class="text-end text-muted">৳ {{ formatMoney(item.unit_purchase_cost) }}</td>
                    <td class="text-end">৳ {{ formatMoney(item.avg_sale_price) }}</td>
                    <td class="text-end text-danger">৳ {{ formatMoney(item.total_cost_amount) }}</td>
                    <td class="text-end text-primary fw-bold">৳ {{ formatMoney(item.total_sales_amount) }}</td>
                    <td class="text-end fw-bold" :class="item.total_profit >= 0 ? 'text-success' : 'text-danger'">
                      ৳ {{ formatMoney(item.total_profit) }}
                    </td>
                    <td class="text-center">
                      <span class="badge" :class="item.margin_percent >= 0 ? 'bg-success' : 'bg-danger'">
                        {{ item.margin_percent }}%
                      </span>
                    </td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="12" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No items sold in the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="item_breakdown.length > 0">
                <tr>
                  <td colspan="5" class="text-end">Total (সর্বমোট):</td>
                  <td class="text-center">{{ summary.total_qty_sold }}</td>
                  <td></td>
                  <td></td>
                  <td class="text-end text-danger">৳ {{ formatMoney(summary.total_cogs) }}</td>
                  <td class="text-end text-primary">৳ {{ formatMoney(summary.gross_sales) }}</td>
                  <td class="text-end text-success">৳ {{ formatMoney(summary.gross_profit) }}</td>
                  <td class="text-center">{{ summary.gross_profit_margin }}%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 TAB 3: Operating Expenses Breakdown -->
        <div v-show="activeTab === 'expenses'">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="fas fa-receipt me-1 text-primary"></i>
              Operating & General Expenses Breakdown (অফিস ও পরিচালন খরচ)
            </h6>
            <span class="badge bg-secondary">{{ expenses.length }} Records</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="12%">Expense No</th>
                  <th width="12%">Date</th>
                  <th width="24%">Account Head</th>
                  <th width="18%">Employee / Beneficiary</th>
                  <th width="18%">Narration</th>
                  <th width="12%">Amount</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="expenses.length > 0">
                  <tr v-for="(exp, idx) in expenses" :key="idx">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td class="font-monospace fw-bold text-primary">{{ exp.expense_no }}</td>
                    <td class="text-center font-monospace">{{ exp.date }}</td>
                    <td><strong>{{ exp.account_name }}</strong></td>
                    <td>{{ exp.employee_name }}</td>
                    <td class="text-muted">{{ exp.narration }}</td>
                    <td class="text-end fw-bold text-danger">৳ {{ formatMoney(exp.amount) }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No expenses recorded in the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="expenses.length > 0">
                <tr>
                  <td colspan="6" class="text-end">Total Expenses (সর্বমোট খরচ):</td>
                  <td class="text-end text-danger">৳ {{ formatMoney(summary.total_expenses) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 TAB 4: Employee Salary Sheets Breakdown -->
        <div v-show="activeTab === 'salaries'">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="fas fa-user-tie me-1 text-primary"></i>
              Employee Salary Sheets Breakdown (কর্মচারীদের বেতন শীট)
            </h6>
            <span class="badge bg-secondary">{{ salaries.length }} Records</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="10%">Date</th>
                  <th width="12%">Month / Year</th>
                  <th width="20%">Employee Name</th>
                  <th width="14%">Designation</th>
                  <th width="10%">Basic</th>
                  <th width="10%">Additions</th>
                  <th width="10%">Deductions</th>
                  <th width="10%">Net Salary</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="salaries.length > 0">
                  <tr v-for="(sal, idx) in salaries" :key="idx">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td class="text-center font-monospace">{{ sal.date }}</td>
                    <td class="text-center fw-bold">{{ sal.month }} {{ sal.year }}</td>
                    <td><strong>{{ sal.employee_name }}</strong></td>
                    <td>{{ sal.designation }}</td>
                    <td class="text-end">৳ {{ formatMoney(sal.basic_salary) }}</td>
                    <td class="text-end text-success">+৳ {{ formatMoney(sal.additions) }}</td>
                    <td class="text-end text-danger">-৳ {{ formatMoney(sal.deductions) }}</td>
                    <td class="text-end fw-bold text-danger">৳ {{ formatMoney(sal.amount) }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="9" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No salary records found for the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="salaries.length > 0">
                <tr>
                  <td colspan="8" class="text-end">Total Salaries (সর্বমোট বেতন):</td>
                  <td class="text-end text-danger">৳ {{ formatMoney(summary.total_salaries) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 TAB 5: Employee Loans & Advances Breakdown -->
        <div v-show="activeTab === 'loans'">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="fas fa-hand-holding-usd me-1 text-primary"></i>
              Employee Loans & Advances Disbursed (কর্মচারী ঋণ ও অগ্রিম)
            </h6>
            <span class="badge bg-secondary">{{ loans.length }} Records</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="12%">Trns ID</th>
                  <th width="12%">Date</th>
                  <th width="20%">Employee Name</th>
                  <th width="12%">Type</th>
                  <th width="10%">Installments</th>
                  <th width="12%">Monthly Inst.</th>
                  <th width="14%">Loan Amount</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="loans.length > 0">
                  <tr v-for="(loan, idx) in loans" :key="idx">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td class="font-monospace fw-bold text-primary">{{ loan.trnsid }}</td>
                    <td class="text-center font-monospace">{{ loan.date }}</td>
                    <td><strong>{{ loan.employee_name }}</strong></td>
                    <td class="text-center">
                      <span class="badge" :class="loan.trns_type === 'Advance' ? 'bg-warning text-dark' : 'bg-info text-dark'">
                        {{ loan.trns_type }}
                      </span>
                    </td>
                    <td class="text-center">{{ loan.total_installment }}</td>
                    <td class="text-end">৳ {{ formatMoney(loan.installment_amount) }}</td>
                    <td class="text-end fw-bold text-danger">৳ {{ formatMoney(loan.amount) }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No loans or advances disbursed in the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="loans.length > 0">
                <tr>
                  <td colspan="7" class="text-end">Total Loans / Advances (সর্বমোট ঋণ/অগ্রিম):</td>
                  <td class="text-end text-danger">৳ {{ formatMoney(summary.total_loans) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 TAB 6: Commissions Breakdown -->
        <div v-show="activeTab === 'commissions'">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark">
              <i class="fas fa-percentage me-1 text-primary"></i>
              Agent & Staff Commissions Breakdown (এজেন্ট ও স্টাফ কমিশন)
            </h6>
            <span class="badge bg-secondary">{{ commissions.length }} Records</span>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="12%">Date</th>
                  <th width="24%">Beneficiary (Agent / Staff)</th>
                  <th width="20%">Customer</th>
                  <th width="16%">Workorder / Ref</th>
                  <th width="10%">Percentage</th>
                  <th width="14%">Commission Amount</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="commissions.length > 0">
                  <tr v-for="(comm, idx) in commissions" :key="idx">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td class="text-center font-monospace">{{ comm.date }}</td>
                    <td><strong>{{ comm.beneficiary }}</strong></td>
                    <td>{{ comm.client_name }}</td>
                    <td>{{ comm.workorder_no }}</td>
                    <td class="text-center">{{ comm.percentage ? comm.percentage + '%' : '-' }}</td>
                    <td class="text-end fw-bold text-danger">৳ {{ formatMoney(comm.amount) }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No commissions recorded in the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="commissions.length > 0">
                <tr>
                  <td colspan="6" class="text-end">Total Commissions (সর্বমোট কমিশন):</td>
                  <td class="text-end text-danger">৳ {{ formatMoney(summary.total_commissions) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 Footer Note & Signatures (Visible on Print) -->
        <div class="mt-4 pt-3 border-top d-print-block">
          <div class="row text-center mt-5 pt-4 d-none d-print-flex">
            <div class="col-4">
              <div class="border-top pt-1 fw-bold">Prepared By</div>
            </div>
            <div class="col-4">
              <div class="border-top pt-1 fw-bold">Verified By (Accounts)</div>
            </div>
            <div class="col-4">
              <div class="border-top pt-1 fw-bold">Approved Authority</div>
            </div>
          </div>
          <div class="small text-muted mt-3">
            <p class="mb-0">Generated by: <strong>Admin</strong> | Report generated automatically from QPOS System.</p>
          </div>
        </div>

      </div>
    </template>
  </index-page>
</template>

<script>
import axios from "axios";
import moment from "moment";

const model = "incomeStatement";

export default {
  data() {
    return {
      model: model,
      page_title: "Income Statement & Sales Profit",
      reportDate: moment().format("D MMMM, YYYY, h:mm A"),
      activeTab: "overview", // 'overview' | 'invoices' | 'items' | 'expenses' | 'salaries' | 'loans' | 'commissions'
      activePreset: "thisMonth",
      expandedInvoices: [],

      search_data: {
        from_date: moment().startOf("month").format("YYYY-MM-DD"),
        to_date: moment().endOf("month").format("YYYY-MM-DD"),
        client_id: null,
        category_id: null,
        item_id: null,
        sale_type: "all",
        invoice_no: "",
      },

      summary: {
        total_invoices: 0,
        total_qty_sold: 0,
        gross_sales: 0,
        total_discount: 0,
        total_vat: 0,
        net_sales: 0,
        total_paid: 0,
        total_due: 0,
        total_cogs: 0,
        gross_profit: 0,
        gross_profit_margin: 0,
        total_expenses: 0,
        total_salaries: 0,
        total_loans: 0,
        total_commissions: 0,
        total_deductions: 0,
        net_profit: 0,
        net_profit_margin: 0,
        is_profitable: true,
      },

      invoices: [],
      item_breakdown: [],
      expenses: [],
      salaries: [],
      loans: [],
      commissions: [],

      // Dropdown option data
      clients: [],
      categories: [],
      items: [],
    };
  },

  provide() {
    return {
      validate: this.validation,
      model: this.model,
      search_data: this.search_data,
      table: { columns: [], routes: {}, datas: [] },
      search: this.search,
      resetSearchData: this.resetSearchData,
    };
  },

  computed: {
    dateRangeDisplay() {
      if (this.search_data.from_date && this.search_data.to_date) {
        return (
          moment(this.search_data.from_date).format("D MMM, YYYY") +
          " to " +
          moment(this.search_data.to_date).format("D MMM, YYYY")
        );
      }
      return "All Available Data";
    },

    exportFileName() {
      const from = this.search_data.from_date || "all";
      const to = this.search_data.to_date || "all";
      return `income_statement_${from}_to_${to}.xls`;
    },

    exportData() {
      if (this.activeTab === "invoices") {
        return this.invoices.map((inv, idx) => ({
          "#": idx + 1,
          Date: inv.invoice_date,
          "Invoice No": inv.invoice_no,
          Customer: inv.client_name,
          "Total Qty": inv.total_qty,
          "Gross Amount": inv.gross_amount,
          Discount: inv.discount,
          "Net Amount": inv.net_amount,
          "Purchase Cost": inv.total_cogs,
          "Gross Profit": inv.profit,
          "Margin (%)": inv.margin_percent + "%",
        }));
      } else if (this.activeTab === "items") {
        return this.item_breakdown.map((item, idx) => ({
          "#": idx + 1,
          "Item Name": item.item_title,
          Barcode: item.barcode,
          Category: item.category_title,
          "Qty Sold": item.total_qty,
          "Unit Purchase Cost": item.unit_purchase_cost,
          "Avg Sale Price": item.avg_sale_price,
          "Total Purchase Cost": item.total_cost_amount,
          "Total Sales Revenue": item.total_sales_amount,
          "Gross Profit": item.total_profit,
          "Margin (%)": item.margin_percent + "%",
        }));
      } else if (this.activeTab === "expenses") {
        return this.expenses.map((exp, idx) => ({
          "#": idx + 1,
          "Expense No": exp.expense_no,
          Date: exp.date,
          "Account Head": exp.account_name,
          "Staff / Vendor": exp.employee_name,
          Narration: exp.narration,
          Amount: exp.amount,
        }));
      } else if (this.activeTab === "salaries") {
        return this.salaries.map((sal, idx) => ({
          "#": idx + 1,
          Date: sal.date,
          Month: sal.month + " " + sal.year,
          Employee: sal.employee_name,
          Designation: sal.designation,
          Basic: sal.basic_salary,
          Additions: sal.additions,
          Deductions: sal.deductions,
          "Net Salary": sal.amount,
        }));
      } else if (this.activeTab === "loans") {
        return this.loans.map((loan, idx) => ({
          "#": idx + 1,
          "Trns ID": loan.trnsid,
          Date: loan.date,
          Employee: loan.employee_name,
          Type: loan.trns_type,
          "Total Installments": loan.total_installment,
          "Monthly Amount": loan.installment_amount,
          "Loan Amount": loan.amount,
        }));
      } else if (this.activeTab === "commissions") {
        return this.commissions.map((comm, idx) => ({
          "#": idx + 1,
          Date: comm.date,
          Beneficiary: comm.beneficiary,
          Customer: comm.client_name,
          Workorder: comm.workorder_no,
          Percentage: comm.percentage ? comm.percentage + "%" : "-",
          Amount: comm.amount,
        }));
      }

      // Default Overview Export
      return [
        { Particular: "Gross Sales", Amount: this.summary.gross_sales },
        { Particular: "Sales Discount", Amount: this.summary.total_discount },
        { Particular: "Net Sales Revenue", Amount: this.summary.net_sales },
        { Particular: "Cost of Goods Sold (COGS)", Amount: this.summary.total_cogs },
        { Particular: "Gross Profit", Amount: this.summary.gross_profit },
        { Particular: "Gross Margin (%)", Amount: this.summary.gross_profit_margin + "%" },
        { Particular: "General Expenses", Amount: this.summary.total_expenses },
        { Particular: "Salaries", Amount: this.summary.total_salaries },
        { Particular: "Loans & Advances", Amount: this.summary.total_loans },
        { Particular: "Commissions", Amount: this.summary.total_commissions },
        { Particular: "Total Deductions", Amount: this.summary.total_deductions },
        { Particular: "Net Profit / (Loss)", Amount: this.summary.net_profit },
        { Particular: "Net Margin (%)", Amount: this.summary.net_profit_margin + "%" },
      ];
    },
  },

  methods: {
    formatMoney(val) {
      const num = Number(val || 0);
      return num.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    getDeductionPercent(amount) {
      if (!this.summary.total_deductions || this.summary.total_deductions <= 0) return 0;
      return Math.min(100, Math.round((Number(amount || 0) / this.summary.total_deductions) * 100));
    },

    toggleInvoiceExpand(id) {
      const idx = this.expandedInvoices.indexOf(id);
      if (idx > -1) {
        this.expandedInvoices.splice(idx, 1);
      } else {
        this.expandedInvoices.push(id);
      }
    },

    search() {
      this.getIncomeStatement();
    },

    resetSearchData() {
      this.search_data = {
        from_date: moment().startOf("month").format("YYYY-MM-DD"),
        to_date: moment().endOf("month").format("YYYY-MM-DD"),
        client_id: null,
        category_id: null,
        item_id: null,
        sale_type: "all",
        invoice_no: "",
      };
      this.activePreset = "thisMonth";
      this.getIncomeStatement();
    },

    applyDatePreset(type) {
      this.activePreset = type;
      if (type === "today") {
        this.search_data.from_date = moment().format("YYYY-MM-DD");
        this.search_data.to_date = moment().format("YYYY-MM-DD");
      } else if (type === "yesterday") {
        this.search_data.from_date = moment().subtract(1, "day").format("YYYY-MM-DD");
        this.search_data.to_date = moment().subtract(1, "day").format("YYYY-MM-DD");
      } else if (type === "last7") {
        this.search_data.from_date = moment().subtract(6, "days").format("YYYY-MM-DD");
        this.search_data.to_date = moment().format("YYYY-MM-DD");
      } else if (type === "thisMonth") {
        this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
        this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
      } else if (type === "lastMonth") {
        this.search_data.from_date = moment().subtract(1, "month").startOf("month").format("YYYY-MM-DD");
        this.search_data.to_date = moment().subtract(1, "month").endOf("month").format("YYYY-MM-DD");
      } else if (type === "thisYear") {
        this.search_data.from_date = moment().startOf("year").format("YYYY-MM-DD");
        this.search_data.to_date = moment().endOf("year").format("YYYY-MM-DD");
      } else if (type === "all") {
        this.search_data.from_date = null;
        this.search_data.to_date = null;
      }
      this.getIncomeStatement();
    },

    getIncomeStatement() {
      this.$root.tableSpinner = true;
      axios
        .get("report/incomestatement", { params: this.search_data })
        .then((res) => {
          const data = res.data || {};
          this.summary = data.summary || this.summary;
          this.invoices = data.invoices || [];
          this.item_breakdown = data.item_breakdown || [];
          this.expenses = data.expenses || [];
          this.salaries = data.salaries || [];
          this.loans = data.loans || [];
          this.commissions = data.commissions || [];
          this.reportDate = moment().format("D MMMM, YYYY, h:mm A");
        })
        .catch((err) => {
          console.error("Error fetching income statement:", err);
        })
        .finally(() => {
          this.$root.tableSpinner = false;
        });
    },

    loadFilterOptions() {
      // Clients
      axios.get("clients").then((res) => {
        this.clients = res.data || [];
      }).catch(() => {});

      // Categories
      axios.get("getcategories").then((res) => {
        this.categories = res.data || [];
      }).catch(() => {});

      // Items
      axios.get("getsalesitems").then((res) => {
        this.items = res.data || [];
      }).catch(() => {});
    },
  },

  created() {
    this.getIncomeStatement();
    this.loadFilterOptions();
  },
};
</script>

<style scoped>
.custom-report-tabs {
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
  display: inline-flex;
}
.custom-report-tabs .nav-link {
  color: #475569;
  font-size: 0.84rem;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 6px;
  transition: all 0.2s ease-in-out;
}
.custom-report-tabs .nav-link:hover {
  color: #0f172a;
  background: rgba(255, 255, 255, 0.6);
}
.custom-report-tabs .nav-link.active {
  background: #2563eb;
  color: #ffffff;
  box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
}
.bg-gradient-purple {
  background: linear-gradient(135deg, #5b21b6 0%, #8b5cf6 100%);
}
.bg-gradient-teal {
  background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
}
.bg-gradient-warning {
  background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
}
.bg-gradient-success {
  background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
}
.bg-gradient-danger {
  background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
}

.metric-icon {
  font-size: 1.8rem;
  opacity: 0.35;
}

.financial-table th {
  font-weight: 700;
}
.financial-table td {
  padding: 0.6rem 0.85rem;
}

@media print {
  .custom-report-tabs,
  .d-print-none {
    display: none !important;
  }
  .card {
    border: 1px solid #ddd !important;
    box-shadow: none !important;
  }
}
</style>

