<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1"><i class="fas fa-calendar-alt me-1"></i>Quick Date:</span>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'today' }" @click="applyDatePreset('today')">Today</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'yesterday' }" @click="applyDatePreset('yesterday')">Yesterday</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'last7' }" @click="applyDatePreset('last7')">Last 7 Days</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisMonth' }" @click="applyDatePreset('thisMonth')">This Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'lastMonth' }" @click="applyDatePreset('lastMonth')">Last Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisYear' }" @click="applyDatePreset('thisYear')">This Year</button>
        </div>
      </div>

      <!-- Date Range -->
      <date-picker id="searchfromdate" v-model="search_data.from_date" field="search_data.from_date"
        title="From Date" placeholder="From Date" col="3" :req="false"></date-picker>
      <date-picker id="searchtodate" v-model="search_data.to_date" field="search_data.to_date"
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
        placeholder="e.g. POS-2026..." col="3" :req="false" />

      <!-- Payment Status Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label">Payment Status (পরিশোধের অবস্থা)</label>
          <select class="form-select form-select-sm" v-model="search_data.payment_status">
            <option value="all">-- All Statuses --</option>
            <option value="paid">Paid (পরিশোধিত)</option>
            <option value="due">Due / Partial (বকেয়া / আংশিক)</option>
          </select>
        </div>
      </div>

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
      <!-- Top Action Bar -->
      <div class="col-md-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <!-- View Mode Tabs -->
          <ul class="nav nav-pills custom-report-tabs">
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'invoices' }" @click="activeTab = 'invoices'">
                <i class="fas fa-file-invoice me-1"></i> Invoices View (ইনভয়েস সমূহ) ({{ invoices.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'items' }" @click="activeTab = 'items'">
                <i class="fas fa-boxes me-1"></i> Item Breakdown (আইটেম ভিত্তিক) ({{ item_breakdown.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'customers' }" @click="activeTab = 'customers'">
                <i class="fas fa-users me-1"></i> Customer Breakdown (গ্রাহক ভিত্তিক) ({{ customer_breakdown.length }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'daily' }" @click="activeTab = 'daily'">
                <i class="fas fa-chart-line me-1"></i> Daily Trend (দৈনিক বিক্রয়) ({{ daily_breakdown.length }})
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

      <!-- 🌟 Metric Summary Cards (Always Visible) -->
      <div class="col-12 mb-4">
        <div class="row g-3">
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Total Invoices</div>
                  <div class="fs-4 fw-bold mt-1">{{ summary.total_invoices || 0 }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-receipt"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Period Sales Count</small>
            </div>
          </div>

          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-info text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Qty Sold (পরিমাণ)</div>
                  <div class="fs-4 fw-bold mt-1">{{ Number(summary.total_qty || 0).toLocaleString() }} <span class="fs-6">Pcs</span></div>
                </div>
                <div class="metric-icon"><i class="fas fa-box-open"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Total Items Sold</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Net Sales (মোট বিক্রয়)</div>
                  <div class="fs-4 fw-bold mt-1">Tk. {{ formatMoney(summary.net_sales) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-dollar-sign"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Discount: Tk. {{ formatMoney(summary.total_discount) }} | VAT: Tk. {{ formatMoney(summary.total_vat) }}</small>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-teal text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Collected (আদায়)</div>
                  <div class="fs-4 fw-bold mt-1 text-white">Tk. {{ formatMoney(summary.total_paid) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Total Received</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-12 col-sm-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-danger text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Outstanding Due (বকেয়া)</div>
                  <div class="fs-4 fw-bold mt-1 text-white">Tk. {{ formatMoney(summary.total_due) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-exclamation-circle"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Total Unpaid Amount</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 🖨️ Printable Area & Report Views -->
      <div class="col-12" id="printArea">
        <!-- 🧾 Company Header (Always visible in print) -->
        <div class="text-center mb-3 report-title">
          <h3 class="fw-bold">{{ $root.site.title || 'QPOS' }}</h3>
          <p class="mb-1 text-muted">{{ $root.site.address || 'Dhaka, Bangladesh' }}</p>
          <p class="text-muted small">
            Email: {{ $root.site.contact_email || 'N/A' }} | Phone: {{ $root.site.mobile1 || 'N/A' }}
          </p>
          <div class="border-top border-bottom py-2 my-2 bg-light">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-chart-bar me-1"></i>
              Sales Report (বিক্রয় প্রতিবেদন) - {{ getTabTitle(activeTab) }}
            </h5>
            <small class="text-muted">
              Period: <strong>{{ dateRangeDisplay }}</strong> | Generated on: <strong>{{ reportDate }}</strong>
            </small>
          </div>
        </div>

        <!-- 📌 VIEW 1: Invoices Breakdown Table -->
        <div v-show="activeTab === 'invoices'">
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="10%">Date</th>
                  <th width="12%">Invoice No</th>
                  <th width="16%">Customer Name</th>
                  <th width="8%">Items/Qty</th>
                  <th width="10%">Subtotal</th>
                  <th width="8%">Discount</th>
                  <th width="10%">Net Total</th>
                  <th width="10%">Paid</th>
                  <th width="10%">Due</th>
                  <th width="6%">Status</th>
                  <th width="6%" class="d-print-none">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="invoices.length > 0">
                  <template v-for="(inv, index) in invoices" :key="inv.id">
                    <tr :class="{ 'table-active': expandedInvoices.includes(inv.id) }">
                      <td class="text-center">{{ index + 1 }}</td>
                      <td class="text-center font-monospace">{{ inv.invoice_date }}</td>
                      <td class="font-monospace fw-bold text-primary">
                        {{ inv.invoice_no }}
                      </td>
                      <td>
                        <div class="fw-bold text-dark">{{ inv.client ? inv.client.name : 'Walk-in Customer' }}</div>
                        <small class="text-muted" v-if="inv.client && inv.client.mobile">{{ inv.client.mobile }}</small>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-secondary font-monospace">{{ inv.invoice_details ? inv.invoice_details.length : 0 }} items</span>
                      </td>
                      <td class="text-end font-monospace">{{ formatMoney(inv.original_amount) }}</td>
                      <td class="text-end font-monospace text-danger">{{ formatMoney(inv.discount) }}</td>
                      <td class="text-end font-monospace fw-bold text-dark">{{ formatMoney(inv.amount) }}</td>
                      <td class="text-end font-monospace text-success">{{ formatMoney(inv.paid_amount) }}</td>
                      <td class="text-end font-monospace fw-bold" :class="inv.computed_due > 0 ? 'text-danger' : 'text-muted'">
                        {{ formatMoney(inv.computed_due) }}
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="{
                          'bg-success': inv.computed_status === 'Paid',
                          'bg-warning text-dark': inv.computed_status === 'Partial',
                          'bg-danger': inv.computed_status === 'Due'
                        }">
                          {{ inv.computed_status }}
                        </span>
                      </td>
                      <td class="text-center d-print-none">
                        <button type="button" class="btn btn-xs btn-outline-info"
                          @click="toggleInvoiceExpand(inv.id)"
                          :title="expandedInvoices.includes(inv.id) ? 'Collapse Items' : 'View Items'">
                          <i class="fas" :class="expandedInvoices.includes(inv.id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                      </td>
                    </tr>
                    <!-- Expandable Line Items Row -->
                    <tr v-if="expandedInvoices.includes(inv.id)" class="bg-light d-print-table-row">
                      <td colspan="12" class="p-3">
                        <div class="border rounded p-2 bg-white shadow-sm">
                          <small class="fw-bold text-muted text-uppercase mb-2 d-block">
                            <i class="fas fa-list me-1"></i> Invoice Items Breakdown (ইনভয়েস আইটেম বিস্তারিত):
                          </small>
                          <table class="table table-sm table-bordered mb-0">
                            <thead class="table-secondary">
                              <tr class="text-center small">
                                <th>Item Title (পণ্যের নাম)</th>
                                <th>Barcode</th>
                                <th>Category</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Serial No</th>
                                <th>Qty</th>
                                <th>Rate (মূল্য)</th>
                                <th>Total (মোট)</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(item, itemIdx) in inv.invoice_details" :key="itemIdx" class="small">
                                <td>{{ item.item ? item.item.title : (item.description || 'N/A') }}</td>
                                <td class="text-center font-monospace">{{ item.item ? item.item.barcode : 'N/A' }}</td>
                                <td class="text-center">{{ item.item && item.item.category ? item.item.category.title : 'N/A' }}</td>
                                <td class="text-center">{{ item.color ? item.color.title : '-' }}</td>
                                <td class="text-center">{{ item.size ? item.size.title : '-' }}</td>
                                <td class="text-center font-monospace">{{ item.serial_no || '-' }}</td>
                                <td class="text-center fw-bold">{{ item.qty }} {{ item.item && item.item.unit ? item.item.unit.title : '' }}</td>
                                <td class="text-end font-monospace">{{ formatMoney(item.amount) }}</td>
                                <td class="text-end font-monospace fw-bold">{{ formatMoney(item.total_amount) }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </td>
                    </tr>
                  </template>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="12" class="text-center py-4 text-muted">
                      <i class="fas fa-search me-1"></i> No sales invoices found matching the selected search criteria.
                    </td>
                  </tr>
                </template>
              </tbody>
              <!-- Invoices Table Footer Totals -->
              <tfoot class="table-dark font-monospace fw-bold" v-if="invoices.length > 0">
                <tr>
                  <td colspan="4" class="text-end">Total Summary (মোট সর্বমোট):</td>
                  <td class="text-center">{{ summary.total_qty }} pcs</td>
                  <td class="text-end">{{ formatMoney(summary.gross_amount) }}</td>
                  <td class="text-end text-warning">{{ formatMoney(summary.total_discount) }}</td>
                  <td class="text-end text-info">{{ formatMoney(summary.net_sales) }}</td>
                  <td class="text-end text-success">{{ formatMoney(summary.total_paid) }}</td>
                  <td class="text-end text-danger">{{ formatMoney(summary.total_due) }}</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 2: Item Breakdown Table -->
        <div v-show="activeTab === 'items'">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="24%">Item Title (পণ্যের নাম)</th>
                  <th width="12%">Barcode</th>
                  <th width="14%">Category</th>
                  <th width="10%">Color</th>
                  <th width="8%">Size</th>
                  <th width="10%">Sold Qty</th>
                  <th width="10%">Avg Selling Rate</th>
                  <th width="12%">Total Sales Amount</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="item_breakdown.length > 0">
                  <tr v-for="(item, index) in item_breakdown" :key="index">
                    <td class="text-center">{{ index + 1 }}</td>
                    <td class="fw-bold text-dark">{{ item.item_title }}</td>
                    <td class="text-center font-monospace">{{ item.barcode }}</td>
                    <td class="text-center">{{ item.category }}</td>
                    <td class="text-center">{{ item.color || '-' }}</td>
                    <td class="text-center">{{ item.size || '-' }}</td>
                    <td class="text-center fw-bold font-monospace">{{ item.total_qty }} {{ item.unit }}</td>
                    <td class="text-end font-monospace">Tk. {{ formatMoney(item.avg_rate) }}</td>
                    <td class="text-end font-monospace fw-bold text-primary">Tk. {{ formatMoney(item.total_amount) }}</td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                      No item sales records found for the selected period.
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="item_breakdown.length > 0">
                <tr>
                  <td colspan="6" class="text-end">Total Items Sold:</td>
                  <td class="text-center">{{ summary.total_qty }} pcs</td>
                  <td class="text-end">Total Amount:</td>
                  <td class="text-end text-info">Tk. {{ formatMoney(summary.net_sales) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 3: Customer Breakdown Table -->
        <div v-show="activeTab === 'customers'">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="5%">#</th>
                  <th width="12%">Customer ID</th>
                  <th width="25%">Customer Name</th>
                  <th width="15%">Mobile</th>
                  <th width="10%">Invoices Count</th>
                  <th width="13%">Total Purchased</th>
                  <th width="10%">Total Paid</th>
                  <th width="10%">Total Due</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="customer_breakdown.length > 0">
                  <tr v-for="(cust, index) in customer_breakdown" :key="index">
                    <td class="text-center">{{ index + 1 }}</td>
                    <td class="text-center font-monospace">{{ cust.clientid }}</td>
                    <td class="fw-bold text-dark">{{ cust.name }}</td>
                    <td class="text-center font-monospace">{{ cust.mobile }}</td>
                    <td class="text-center"><span class="badge bg-secondary font-monospace">{{ cust.invoices_count }}</span></td>
                    <td class="text-end font-monospace fw-bold">Tk. {{ formatMoney(cust.total_purchased) }}</td>
                    <td class="text-end font-monospace text-success">Tk. {{ formatMoney(cust.total_paid) }}</td>
                    <td class="text-end font-monospace fw-bold" :class="cust.total_due > 0 ? 'text-danger' : 'text-muted'">
                      Tk. {{ formatMoney(cust.total_due) }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                      No customer sales records found.
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="customer_breakdown.length > 0">
                <tr>
                  <td colspan="4" class="text-end">Grand Total:</td>
                  <td class="text-center">{{ summary.total_invoices }}</td>
                  <td class="text-end text-info">Tk. {{ formatMoney(summary.net_sales) }}</td>
                  <td class="text-end text-success">Tk. {{ formatMoney(summary.total_paid) }}</td>
                  <td class="text-end text-danger">Tk. {{ formatMoney(summary.total_due) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 4: Daily Sales Trend Table -->
        <div v-show="activeTab === 'daily'">
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="5%">#</th>
                  <th width="15%">Date</th>
                  <th width="10%">Invoices Count</th>
                  <th width="10%">Total Qty Sold</th>
                  <th width="15%">Gross Amount</th>
                  <th width="10%">Discount</th>
                  <th width="15%">Net Sales</th>
                  <th width="12%">Paid</th>
                  <th width="12%">Due</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="daily_breakdown.length > 0">
                  <tr v-for="(day, index) in daily_breakdown" :key="index">
                    <td class="text-center">{{ index + 1 }}</td>
                    <td class="text-center font-monospace fw-bold text-dark">{{ day.formatted_date }}</td>
                    <td class="text-center"><span class="badge bg-secondary font-monospace">{{ day.invoices_count }}</span></td>
                    <td class="text-center font-monospace fw-bold">{{ day.total_qty }} pcs</td>
                    <td class="text-end font-monospace">Tk. {{ formatMoney(day.gross_amount) }}</td>
                    <td class="text-end font-monospace text-danger">Tk. {{ formatMoney(day.discount) }}</td>
                    <td class="text-end font-monospace fw-bold text-primary">Tk. {{ formatMoney(day.net_sales) }}</td>
                    <td class="text-end font-monospace text-success">Tk. {{ formatMoney(day.paid_amount) }}</td>
                    <td class="text-end font-monospace fw-bold" :class="day.due_amount > 0 ? 'text-danger' : 'text-muted'">
                      Tk. {{ formatMoney(day.due_amount) }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                      No daily sales records found for this period.
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="daily_breakdown.length > 0">
                <tr>
                  <td colspan="2" class="text-end">Total Summary:</td>
                  <td class="text-center">{{ summary.total_invoices }}</td>
                  <td class="text-center">{{ summary.total_qty }} pcs</td>
                  <td class="text-end">Tk. {{ formatMoney(summary.gross_amount) }}</td>
                  <td class="text-end text-warning">Tk. {{ formatMoney(summary.total_discount) }}</td>
                  <td class="text-end text-info">Tk. {{ formatMoney(summary.net_sales) }}</td>
                  <td class="text-end text-success">Tk. {{ formatMoney(summary.total_paid) }}</td>
                  <td class="text-end text-danger">Tk. {{ formatMoney(summary.total_due) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 Footer Note for Print -->
        <div class="mt-4 small text-muted d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-0">Report Generated By: <strong>{{ $root.admin ? $root.admin.name : 'System Admin' }}</strong></p>
            <small>This report is automatically generated by QPOS ERP and is authentic without signature.</small>
          </div>
          <div class="text-end">
            <div class="border-top pt-1 mt-4 px-4 d-inline-block text-center">
              <strong>Authorized Signature</strong>
            </div>
          </div>
        </div>

      </div>
    </template>
  </index-page>
</template>

<script>
import axios from "axios";
import moment from "moment";
import JsonExcel from "vue-json-excel3";

const model = "salesReport";

export default {
  components: {
    downloadExcel: JsonExcel,
  },
  data() {
    return {
      model: model,
      page_title: "Sales Report",
      reportDate: moment().format("D MMMM, YYYY h:mm A"),
      activeTab: "invoices",
      activePreset: "thisMonth",
      search_data: {
        from_date: moment().startOf("month").format("YYYY-MM-DD"),
        to_date: moment().endOf("month").format("YYYY-MM-DD"),
        client_id: null,
        category_id: null,
        item_id: null,
        invoice_no: "",
        payment_status: "all",
        sale_type: "all",
      },
      table: {
        columns: [],
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      summary: {
        total_invoices: 0,
        total_qty: 0,
        gross_amount: 0,
        total_discount: 0,
        total_vat: 0,
        net_sales: 0,
        total_paid: 0,
        total_due: 0,
      },
      invoices: [],
      item_breakdown: [],
      customer_breakdown: [],
      daily_breakdown: [],
      expandedInvoices: [],
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
      table: this.table,
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
      return "All Time";
    },

    exportFileName() {
      return `Sales_Report_${this.activeTab}_${moment().format("YYYY-MM-DD")}.xls`;
    },

    exportData() {
      if (this.activeTab === "invoices") {
        return this.invoices.map((inv, idx) => ({
          "SL": idx + 1,
          "Invoice Date": inv.invoice_date,
          "Invoice No": inv.invoice_no,
          "Customer Name": inv.client ? inv.client.name : "Walk-in Customer",
          "Customer Mobile": inv.client ? inv.client.mobile : "N/A",
          "Subtotal Amount": inv.original_amount,
          "Discount": inv.discount,
          "VAT": inv.vat,
          "Net Amount": inv.amount,
          "Paid Amount": inv.paid_amount,
          "Due Amount": inv.computed_due,
          "Status": inv.computed_status,
        }));
      } else if (this.activeTab === "items") {
        return this.item_breakdown.map((item, idx) => ({
          "SL": idx + 1,
          "Item Name": item.item_title,
          "Barcode": item.barcode,
          "Category": item.category,
          "Color": item.color || "-",
          "Size": item.size || "-",
          "Sold Quantity": item.total_qty,
          "Unit": item.unit,
          "Avg Selling Rate": item.avg_rate,
          "Total Sales Amount": item.total_amount,
        }));
      } else if (this.activeTab === "customers") {
        return this.customer_breakdown.map((c, idx) => ({
          "SL": idx + 1,
          "Customer ID": c.clientid,
          "Customer Name": c.name,
          "Mobile": c.mobile,
          "Invoices Count": c.invoices_count,
          "Total Purchased": c.total_purchased,
          "Total Paid": c.total_paid,
          "Total Due": c.total_due,
        }));
      } else if (this.activeTab === "daily") {
        return this.daily_breakdown.map((d, idx) => ({
          "SL": idx + 1,
          "Date": d.formatted_date,
          "Invoices Count": d.invoices_count,
          "Total Qty Sold": d.total_qty,
          "Gross Amount": d.gross_amount,
          "Discount": d.discount,
          "VAT": d.vat,
          "Net Sales": d.net_sales,
          "Paid Amount": d.paid_amount,
          "Due Amount": d.due_amount,
        }));
      }
      return [];
    },
  },

  methods: {
    search() {
      this.getSalesReport();
    },

    resetSearchData() {
      this.activePreset = "thisMonth";
      this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
      this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
      this.search_data.client_id = null;
      this.search_data.category_id = null;
      this.search_data.item_id = null;
      this.search_data.invoice_no = "";
      this.search_data.payment_status = "all";
      this.search_data.sale_type = "all";
      this.getSalesReport();
    },

    applyDatePreset(preset) {
      this.activePreset = preset;
      const today = moment();

      switch (preset) {
        case "today":
          this.search_data.from_date = today.format("YYYY-MM-DD");
          this.search_data.to_date = today.format("YYYY-MM-DD");
          break;
        case "yesterday":
          const yest = moment().subtract(1, "days");
          this.search_data.from_date = yest.format("YYYY-MM-DD");
          this.search_data.to_date = yest.format("YYYY-MM-DD");
          break;
        case "last7":
          this.search_data.from_date = moment().subtract(6, "days").format("YYYY-MM-DD");
          this.search_data.to_date = today.format("YYYY-MM-DD");
          break;
        case "thisMonth":
          this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
          this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
          break;
        case "lastMonth":
          const lastMonth = moment().subtract(1, "months");
          this.search_data.from_date = lastMonth.startOf("month").format("YYYY-MM-DD");
          this.search_data.to_date = lastMonth.endOf("month").format("YYYY-MM-DD");
          break;
        case "thisYear":
          this.search_data.from_date = moment().startOf("year").format("YYYY-MM-DD");
          this.search_data.to_date = moment().endOf("year").format("YYYY-MM-DD");
          break;
      }
      this.getSalesReport();
    },

    getSalesReport() {
      this.$root.spinner = true;
      this.reportDate = moment().format("D MMMM, YYYY h:mm A");

      axios
        .get("report/sales", { params: this.search_data })
        .then((res) => {
          if (res.data) {
            this.summary = res.data.summary || {};
            this.invoices = res.data.invoices || [];
            this.item_breakdown = res.data.item_breakdown || [];
            this.customer_breakdown = res.data.customer_breakdown || [];
            this.daily_breakdown = res.data.daily_breakdown || [];
          }
        })
        .catch((err) => {
          console.error("Sales report load error:", err);
          this.$toast("Failed to load sales report data", "error");
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },

    toggleInvoiceExpand(id) {
      const idx = this.expandedInvoices.indexOf(id);
      if (idx > -1) {
        this.expandedInvoices.splice(idx, 1);
      } else {
        this.expandedInvoices.push(id);
      }
    },

    getTabTitle(tab) {
      switch (tab) {
        case "invoices":
          return "Invoice-wise Breakdown (ইনভয়েস ভিত্তিক)";
        case "items":
          return "Item-wise Breakdown (পণ্য ভিত্তিক)";
        case "customers":
          return "Customer-wise Breakdown (গ্রাহক ভিত্তিক)";
        case "daily":
          return "Daily Sales Trend (দৈনিক বিক্রয়)";
        default:
          return "";
      }
    },

    formatMoney(val) {
      const num = Number(val || 0);
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    fetchFilterOptions() {
      // 1. Categories
      axios.get("getcategories/Item").then((res) => {
        this.categories = res.data || [];
      });

      // 2. Clients
      axios.get("client?allData=true").then((res) => {
        this.clients = res.data || [];
      });

      // 3. Items
      axios.get("item?allData=true").then((res) => {
        this.items = res.data || [];
      });
    },
  },

  created() {
    this.page_title = "Sales Report";
    this.fetchFilterOptions();
    this.getSalesReport();
  },

  validators: {},
};
</script>

<style scoped>
.custom-report-tabs .nav-link {
  border-radius: 6px;
  padding: 6px 14px;
  font-weight: 600;
  font-size: 13px;
  color: #495057;
  border: 1px solid #dee2e6;
  background: #f8f9fa;
  transition: all 0.2s ease;
}

.custom-report-tabs .nav-link:hover {
  background: #e9ecef;
  color: #0d6efd;
}

.custom-report-tabs .nav-link.active {
  background: #0d6efd;
  color: #ffffff;
  border-color: #0d6efd;
  box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25);
}

.btn-xs {
  padding: 2px 8px;
  font-size: 11px;
  font-weight: 500;
  border-radius: 4px;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
}

.bg-gradient-info {
  background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
}

.bg-gradient-success {
  background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
}

.bg-gradient-teal {
  background: linear-gradient(135deg, #20c997 0%, #0f7a5b 100%) !important;
}

.bg-gradient-danger {
  background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
}

.metric-icon {
  font-size: 24px;
  opacity: 0.35;
}

@media print {
  .d-print-none {
    display: none !important;
  }
  .d-print-table-row {
    display: table-row !important;
  }
  .custom-report-tabs,
  .p_btn {
    display: none !important;
  }
}
</style>
