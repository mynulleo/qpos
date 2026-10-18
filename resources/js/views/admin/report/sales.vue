<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1"><i class="fas fa-calendar-alt me-1 text-primary"></i>{{ $t("Quick Date:") }}</span>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'today' }" @click="applyDatePreset('today')">{{ $t("Today") }}</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'yesterday' }" @click="applyDatePreset('yesterday')">{{ $t("Yesterday") }}</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'last7' }" @click="applyDatePreset('last7')">{{ $t("Last 7 Days") }}</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisMonth' }" @click="applyDatePreset('thisMonth')">{{ $t("This Month") }}</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'lastMonth' }" @click="applyDatePreset('lastMonth')">{{ $t("Last Month") }}</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisYear' }" @click="applyDatePreset('thisYear')">{{ $t("This Year") }}</button>
        </div>
      </div>

      <!-- Date Range -->
      <date-picker id="searchfromdate" v-model="search_data.from_date" field="search_data.from_date"
        :title="$t('From Date')" :placeholder="$t('From Date')" col="3" :req="false"></date-picker>
      <date-picker id="searchtodate" v-model="search_data.to_date" field="search_data.to_date"
        :title="$t('To Date')" :placeholder="$t('To Date')" col="3" :req="false"></date-picker>

      <!-- Customer / Client -->
      <v-select-container :title="$t('Customer')" field="search_data.client_id" col="3">
        <v-select v-model="search_data.client_id" label="name" :reduce="obj => obj.id" :options="clients"
          :placeholder="$t('-- All Customers --')" :closeOnSelect="true">
          <template #option="option">
            <div>
              <strong>{{ option.name }}</strong>
              <small class="text-muted d-block" v-if="option.mobile">{{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Category Filter -->
      <v-select-container :title="$t('Category')" field="search_data.category_id" col="3">
        <v-select v-model="search_data.category_id" label="title" :reduce="obj => obj.id" :options="categories"
          :placeholder="$t('-- All Categories --')" :closeOnSelect="true" />
      </v-select-container>

      <!-- Item Filter -->
      <v-select-container :title="$t('Item')" field="search_data.item_id" col="3">
        <v-select v-model="search_data.item_id" label="title" :reduce="obj => obj.id" :options="items"
          :placeholder="$t('-- All Items --')" :closeOnSelect="true">
          <template #option="option">
            <div>
              <span>{{ option.title }}</span>
              <small class="text-muted d-block" v-if="option.barcode">{{ $t('Barcode') }}: {{ option.barcode }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Invoice No Search -->
      <Input v-model="search_data.invoice_no" field="search_data.invoice_no" :title="$t('Invoice No')"
        :placeholder="$t('e.g. POS-2026...')" col="3" :req="false" />

      <!-- Payment Status Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label">{{ $t('Payment Status') }}</label>
          <select class="form-select form-select-sm" v-model="search_data.payment_status">
            <option value="all">{{ $t("-- All Statuses --") }}</option>
            <option value="paid">{{ $t('Paid') }}</option>
            <option value="due">{{ $t('Due / Partial') }}</option>
          </select>
        </div>
      </div>

      <!-- Sale Type Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label">{{ $t('Sale Type') }}</label>
          <select class="form-select form-select-sm" v-model="search_data.sale_type">
            <option value="all">{{ $t("-- All Sales --") }}</option>
            <option value="pos">{{ $t('POS Sales') }}</option>
            <option value="general">{{ $t('General Invoices') }}</option>
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
                <i class="fas fa-file-invoice me-1"></i> {{ $t("Invoices View") }} ({{ $bnNum(invoices.length) }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'items' }" @click="activeTab = 'items'">
                <i class="fas fa-boxes me-1"></i> {{ $t("Item Breakdown") }} ({{ $bnNum(item_breakdown.length) }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'customers' }" @click="activeTab = 'customers'">
                <i class="fas fa-users me-1"></i> {{ $t("Customer Breakdown") }} ({{ $bnNum(customer_breakdown.length) }})
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ 'active': activeTab === 'daily' }" @click="activeTab = 'daily'">
                <i class="fas fa-chart-line me-1"></i> {{ $t("Daily Trend") }} ({{ $bnNum(daily_breakdown.length) }})
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
              <i class="fas fa-file-excel"></i> {{ $t("Export Excel") }}
            </download-excel>
            <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
              :data-bs-title="$t('Print Report')"
              v-x-tooltip @click="print('printArea', model)">
              <i class="fas fa-print"></i> {{ $t("Print Report") }}
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
                  <div class="text-white-50 small fw-bold text-uppercase">{{ $t("Total Invoices") }}</div>
                  <div class="fs-4 fw-bold mt-1">{{ $bnNum(summary.total_invoices || 0) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-receipt"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">{{ $t("Period Sales Count") }}</small>
            </div>
          </div>

          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-info text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Qty Sold') }}</div>
                  <div class="fs-4 fw-bold mt-1">{{ $bnNum(Number(summary.total_qty || 0).toLocaleString()) }} <span class="fs-6">{{ $t('Pcs') }}</span></div>
                </div>
                <div class="metric-icon"><i class="fas fa-box-open"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">{{ $t("Total Items Sold") }}</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Net Sales') }}</div>
                  <div class="fs-4 fw-bold mt-1">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.net_sales)) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-dollar-sign"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">{{ $t('Discount') }}: {{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_discount)) }} | {{ $t('VAT') }}: {{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_vat)) }}</small>
            </div>
          </div>

          <div class="col-xl-2 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-teal text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Collected') }}</div>
                  <div class="fs-4 fw-bold mt-1 text-white">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_paid)) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">{{ $t("Total Received") }}</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-12 col-sm-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-danger text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Outstanding Due') }}</div>
                  <div class="fs-4 fw-bold mt-1 text-white">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_due)) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-exclamation-circle"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">{{ $t("Total Unpaid Amount") }}</small>
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
            {{ $t('Email') }}: {{ $root.site.contact_email || $t('N/A') }} | {{ $t('Phone') }}: {{ $root.site.mobile1 || $t('N/A') }}
          </p>
          <div class="border-top border-bottom py-2 my-2 bg-light">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-chart-bar me-1"></i>
              {{ $t('Sales Report') }} - {{ getTabTitle(activeTab) }}
            </h5>
            <small class="text-muted">
              {{ $t('Period') }}: <strong>{{ dateRangeDisplay }}</strong> | {{ $t('Generated on') }}: <strong>{{ reportDateDisplay }}</strong>
            </small>
          </div>
        </div>

        <!-- 📌 VIEW 1: Invoices Breakdown Table -->
        <div v-show="activeTab === 'invoices'">
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">{{ $t('#') }}</th>
                  <th width="10%">{{ $t('Date') }}</th>
                  <th width="12%">{{ $t('Invoice No') }}</th>
                  <th width="16%">{{ $t('Customer Name') }}</th>
                  <th width="8%">{{ $t('Items/Qty') }}</th>
                  <th width="10%">{{ $t('Subtotal') }}</th>
                  <th width="8%">{{ $t('Discount') }}</th>
                  <th width="10%">{{ $t('Net Total') }}</th>
                  <th width="10%">{{ $t('Paid') }}</th>
                  <th width="10%">{{ $t('Due') }}</th>
                  <th width="6%">{{ $t('Status') }}</th>
                  <th width="6%" class="d-print-none">{{ $t('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="invoices.length > 0">
                  <template v-for="(inv, index) in invoices" :key="inv.id">
                    <tr :class="{ 'table-active': expandedInvoices.includes(inv.id) }">
                      <td class="text-center">{{ $bnNum(index + 1) }}</td>
                      <td class="text-center font-monospace">{{ $bnNum(inv.invoice_date) }}</td>
                      <td class="font-monospace fw-bold text-primary">
                        {{ inv.invoice_no }}
                      </td>
                      <td>
                        <div class="fw-bold text-dark">{{ inv.client ? inv.client.name : $t('Walk-in Customer') }}</div>
                        <small class="text-muted" v-if="inv.client && inv.client.mobile">{{ $bnNum(inv.client.mobile) }}</small>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-secondary font-monospace">{{ $bnNum(inv.invoice_details ? inv.invoice_details.length : 0) }} {{ $t('items') }}</span>
                      </td>
                      <td class="text-end font-monospace">{{ $bnNum(formatMoney(inv.original_amount)) }}</td>
                      <td class="text-end font-monospace text-danger">{{ $bnNum(formatMoney(inv.discount)) }}</td>
                      <td class="text-end font-monospace fw-bold text-dark">{{ $bnNum(formatMoney(inv.amount)) }}</td>
                      <td class="text-end font-monospace text-success">{{ $bnNum(formatMoney(inv.paid_amount)) }}</td>
                      <td class="text-end font-monospace fw-bold" :class="inv.computed_due > 0 ? 'text-danger' : 'text-muted'">
                        {{ $bnNum(formatMoney(inv.computed_due)) }}
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="{
                          'bg-success': inv.computed_status === 'Paid',
                          'bg-warning text-dark': inv.computed_status === 'Partial',
                          'bg-danger': inv.computed_status === 'Due'
                        }">
                          {{ $t(inv.computed_status) }}
                        </span>
                      </td>
                      <td class="text-center d-print-none">
                        <button type="button" class="btn btn-xs btn-outline-info"
                          @click="toggleInvoiceExpand(inv.id)"
                          :title="expandedInvoices.includes(inv.id) ? $t('Collapse Items') : $t('View Items')">
                          <i class="fas" :class="expandedInvoices.includes(inv.id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                      </td>
                    </tr>
                    <!-- Expandable Line Items Row -->
                    <tr v-if="expandedInvoices.includes(inv.id)" class="bg-light d-print-table-row">
                      <td colspan="12" class="p-3">
                        <div class="border rounded p-2 bg-white shadow-sm">
                          <small class="fw-bold text-muted text-uppercase mb-2 d-block">
                            <i class="fas fa-list me-1"></i> {{ $t('Invoice Items Breakdown') }}:
                          </small>
                          <table class="table table-sm table-bordered mb-0">
                            <thead class="table-secondary">
                              <tr class="text-center small">
                                <th>{{ $t('Item Title') }}</th>
                                <th>{{ $t('Barcode') }}</th>
                                <th>{{ $t('Category') }}</th>
                                <th>{{ $t('Color') }}</th>
                                <th>{{ $t('Size') }}</th>
                                <th>{{ $t('Serial No') }}</th>
                                <th>{{ $t('Qty') }}</th>
                                <th>{{ $t('Rate') }}</th>
                                <th>{{ $t('Total') }}</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(item, itemIdx) in inv.invoice_details" :key="itemIdx" class="small">
                                <td>{{ item.item ? item.item.title : (item.description || $t('N/A')) }}</td>
                                <td class="text-center font-monospace">{{ item.item ? item.item.barcode : $t('N/A') }}</td>
                                <td class="text-center">{{ item.item && item.item.category ? item.item.category.title : $t('N/A') }}</td>
                                <td class="text-center">{{ item.color ? item.color.title : '-' }}</td>
                                <td class="text-center">{{ item.size ? item.size.title : '-' }}</td>
                                <td class="text-center font-monospace">{{ item.serial_no || '-' }}</td>
                                <td class="text-center fw-bold">{{ $bnNum(item.qty) }} {{ item.item && item.item.unit ? item.item.unit.title : '' }}</td>
                                <td class="text-end font-monospace">{{ $bnNum(formatMoney(item.amount)) }}</td>
                                <td class="text-end font-monospace fw-bold">{{ $bnNum(formatMoney(item.total_amount)) }}</td>
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
                      <i class="fas fa-search me-1"></i> {{ $t('No sales invoices found matching the selected search criteria.') }}
                    </td>
                  </tr>
                </template>
              </tbody>
              <!-- Invoices Table Footer Totals -->
              <tfoot class="table-dark font-monospace fw-bold" v-if="invoices.length > 0">
                <tr>
                  <td colspan="4" class="text-end">{{ $t('Total Summary') }}:</td>
                  <td class="text-center">{{ $bnNum(summary.total_qty) }} {{ $t('pcs') }}</td>
                  <td class="text-end">{{ $bnNum(formatMoney(summary.gross_amount)) }}</td>
                  <td class="text-end text-warning">{{ $bnNum(formatMoney(summary.total_discount)) }}</td>
                  <td class="text-end text-info">{{ $bnNum(formatMoney(summary.net_sales)) }}</td>
                  <td class="text-end text-success">{{ $bnNum(formatMoney(summary.total_paid)) }}</td>
                  <td class="text-end text-danger">{{ $bnNum(formatMoney(summary.total_due)) }}</td>
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
                  <th width="4%">{{ $t('#') }}</th>
                  <th width="24%">{{ $t('Item Title') }}</th>
                  <th width="12%">{{ $t('Barcode') }}</th>
                  <th width="14%">{{ $t('Category') }}</th>
                  <th width="10%">{{ $t('Color') }}</th>
                  <th width="8%">{{ $t('Size') }}</th>
                  <th width="10%">{{ $t('Sold Qty') }}</th>
                  <th width="10%">{{ $t('Avg Selling Rate') }}</th>
                  <th width="12%">{{ $t('Total Sales Amount') }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="item_breakdown.length > 0">
                  <tr v-for="(item, index) in item_breakdown" :key="index">
                    <td class="text-center">{{ $bnNum(index + 1) }}</td>
                    <td class="fw-bold text-dark">{{ item.item_title }}</td>
                    <td class="text-center font-monospace">{{ item.barcode }}</td>
                    <td class="text-center">{{ item.category }}</td>
                    <td class="text-center">{{ item.color || '-' }}</td>
                    <td class="text-center">{{ item.size || '-' }}</td>
                    <td class="text-center fw-bold font-monospace">{{ $bnNum(item.total_qty) }} {{ item.unit }}</td>
                    <td class="text-end font-monospace">{{ $t('Tk.') }} {{ $bnNum(formatMoney(item.avg_rate)) }}</td>
                    <td class="text-end font-monospace fw-bold text-primary">{{ $t('Tk.') }} {{ $bnNum(formatMoney(item.total_amount)) }}</td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                      {{ $t('No item sales records found for the selected period.') }}
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="item_breakdown.length > 0">
                <tr>
                  <td colspan="6" class="text-end">{{ $t('Total Items Sold') }}:</td>
                  <td class="text-center">{{ $bnNum(summary.total_qty) }} {{ $t('pcs') }}</td>
                  <td class="text-end">{{ $t('Total Amount') }}:</td>
                  <td class="text-end text-info">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.net_sales)) }}</td>
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
                  <th width="5%">{{ $t('#') }}</th>
                  <th width="12%">{{ $t('Customer ID') }}</th>
                  <th width="25%">{{ $t('Customer Name') }}</th>
                  <th width="15%">{{ $t('Mobile') }}</th>
                  <th width="10%">{{ $t('Invoices Count') }}</th>
                  <th width="13%">{{ $t('Total Purchased') }}</th>
                  <th width="10%">{{ $t('Total Paid') }}</th>
                  <th width="10%">{{ $t('Total Due') }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="customer_breakdown.length > 0">
                  <tr v-for="(cust, index) in customer_breakdown" :key="index">
                    <td class="text-center">{{ $bnNum(index + 1) }}</td>
                    <td class="text-center font-monospace">{{ cust.clientid }}</td>
                    <td class="fw-bold text-dark">{{ cust.name }}</td>
                    <td class="text-center font-monospace">{{ $bnNum(cust.mobile) }}</td>
                    <td class="text-center"><span class="badge bg-secondary font-monospace">{{ $bnNum(cust.invoices_count) }}</span></td>
                    <td class="text-end font-monospace fw-bold">{{ $t('Tk.') }} {{ $bnNum(formatMoney(cust.total_purchased)) }}</td>
                    <td class="text-end font-monospace text-success">{{ $t('Tk.') }} {{ $bnNum(formatMoney(cust.total_paid)) }}</td>
                    <td class="text-end font-monospace fw-bold" :class="cust.total_due > 0 ? 'text-danger' : 'text-muted'">
                      {{ $t('Tk.') }} {{ $bnNum(formatMoney(cust.total_due)) }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                      {{ $t('No customer sales records found.') }}
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="customer_breakdown.length > 0">
                <tr>
                  <td colspan="4" class="text-end">{{ $t('Grand Total') }}:</td>
                  <td class="text-center">{{ $bnNum(summary.total_invoices) }}</td>
                  <td class="text-end text-info">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.net_sales)) }}</td>
                  <td class="text-end text-success">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_paid)) }}</td>
                  <td class="text-end text-danger">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_due)) }}</td>
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
                  <th width="5%">{{ $t('#') }}</th>
                  <th width="15%">{{ $t('Date') }}</th>
                  <th width="10%">{{ $t('Invoices Count') }}</th>
                  <th width="10%">{{ $t('Total Qty Sold') }}</th>
                  <th width="15%">{{ $t('Gross Amount') }}</th>
                  <th width="10%">{{ $t('Discount') }}</th>
                  <th width="15%">{{ $t('Net Sales') }}</th>
                  <th width="12%">{{ $t('Paid') }}</th>
                  <th width="12%">{{ $t('Due') }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="daily_breakdown.length > 0">
                  <tr v-for="(day, index) in daily_breakdown" :key="index">
                    <td class="text-center">{{ $bnNum(index + 1) }}</td>
                    <td class="text-center font-monospace fw-bold text-dark">{{ $bnNum(day.formatted_date) }}</td>
                    <td class="text-center"><span class="badge bg-secondary font-monospace">{{ $bnNum(day.invoices_count) }}</span></td>
                    <td class="text-center font-monospace fw-bold">{{ $bnNum(day.total_qty) }} {{ $t('pcs') }}</td>
                    <td class="text-end font-monospace">{{ $t('Tk.') }} {{ $bnNum(formatMoney(day.gross_amount)) }}</td>
                    <td class="text-end font-monospace text-danger">{{ $t('Tk.') }} {{ $bnNum(formatMoney(day.discount)) }}</td>
                    <td class="text-end font-monospace fw-bold text-primary">{{ $t('Tk.') }} {{ $bnNum(formatMoney(day.net_sales)) }}</td>
                    <td class="text-end font-monospace text-success">{{ $t('Tk.') }} {{ $bnNum(formatMoney(day.paid_amount)) }}</td>
                    <td class="text-end font-monospace fw-bold" :class="day.due_amount > 0 ? 'text-danger' : 'text-muted'">
                      {{ $t('Tk.') }} {{ $bnNum(formatMoney(day.due_amount)) }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                      {{ $t('No daily sales records found for this period.') }}
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="daily_breakdown.length > 0">
                <tr>
                  <td colspan="2" class="text-end">{{ $t('Total Summary') }}:</td>
                  <td class="text-center">{{ $bnNum(summary.total_invoices) }}</td>
                  <td class="text-center">{{ $bnNum(summary.total_qty) }} {{ $t('pcs') }}</td>
                  <td class="text-end">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.gross_amount)) }}</td>
                  <td class="text-end text-warning">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_discount)) }}</td>
                  <td class="text-end text-info">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.net_sales)) }}</td>
                  <td class="text-end text-success">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_paid)) }}</td>
                  <td class="text-end text-danger">{{ $t('Tk.') }} {{ $bnNum(formatMoney(summary.total_due)) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 Footer Note for Print -->
        <div class="mt-4 small text-muted d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-0">{{ $t('Report Generated By') }}: <strong>{{ $root.admin ? $root.admin.name : $t('System Admin') }}</strong></p>
            <small>{{ $t('This report is automatically generated by QPOS ERP and is authentic without signature.') }}</small>
          </div>
          <div class="text-end">
            <div class="border-top pt-1 mt-4 px-4 d-inline-block text-center">
              <strong>{{ $t('Authorized Signature') }}</strong>
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
        const from = moment(this.search_data.from_date).format("D MMM, YYYY");
        const to = moment(this.search_data.to_date).format("D MMM, YYYY");
        return (
          this.$bnNum(from) +
          " " +
          this.$t("to") +
          " " +
          this.$bnNum(to)
        );
      }
      return this.$t("All Time");
    },

    reportDateDisplay() {
      return this.$bnNum(this.reportDate);
    },

    exportFileName() {
      return `Sales_Report_${this.activeTab}_${moment().format("YYYY-MM-DD")}.xls`;
    },

    exportData() {
      if (this.activeTab === "invoices") {
        return this.invoices.map((inv, idx) => ({
          [this.$t("SL")]: idx + 1,
          [this.$t("Invoice Date")]: inv.invoice_date,
          [this.$t("Invoice No")]: inv.invoice_no,
          [this.$t("Customer Name")]: inv.client ? inv.client.name : this.$t("Walk-in Customer"),
          [this.$t("Customer Mobile")]: inv.client ? inv.client.mobile : this.$t("N/A"),
          [this.$t("Subtotal")]: inv.original_amount,
          [this.$t("Discount")]: inv.discount,
          [this.$t("VAT")]: inv.vat,
          [this.$t("Net Total")]: inv.amount,
          [this.$t("Paid Amount")]: inv.paid_amount,
          [this.$t("Due Amount")]: inv.computed_due,
          [this.$t("Status")]: this.$t(inv.computed_status),
        }));
      } else if (this.activeTab === "items") {
        return this.item_breakdown.map((item, idx) => ({
          [this.$t("SL")]: idx + 1,
          [this.$t("Item Title")]: item.item_title,
          [this.$t("Barcode")]: item.barcode,
          [this.$t("Category")]: item.category,
          [this.$t("Color")]: item.color || "-",
          [this.$t("Size")]: item.size || "-",
          [this.$t("Sold Qty")]: item.total_qty,
          [this.$t("Unit")]: item.unit,
          [this.$t("Avg Selling Rate")]: item.avg_rate,
          [this.$t("Total Sales Amount")]: item.total_amount,
        }));
      } else if (this.activeTab === "customers") {
        return this.customer_breakdown.map((c, idx) => ({
          [this.$t("SL")]: idx + 1,
          [this.$t("Customer ID")]: c.clientid,
          [this.$t("Customer Name")]: c.name,
          [this.$t("Mobile")]: c.mobile,
          [this.$t("Invoices Count")]: c.invoices_count,
          [this.$t("Total Purchased")]: c.total_purchased,
          [this.$t("Total Paid")]: c.total_paid,
          [this.$t("Total Due")]: c.total_due,
        }));
      } else if (this.activeTab === "daily") {
        return this.daily_breakdown.map((d, idx) => ({
          [this.$t("SL")]: idx + 1,
          [this.$t("Date")]: d.formatted_date,
          [this.$t("Invoices Count")]: d.invoices_count,
          [this.$t("Total Qty Sold")]: d.total_qty,
          [this.$t("Gross Amount")]: d.gross_amount,
          [this.$t("Discount")]: d.discount,
          [this.$t("VAT")]: d.vat,
          [this.$t("Net Sales")]: d.net_sales,
          [this.$t("Paid Amount")]: d.paid_amount,
          [this.$t("Due Amount")]: d.due_amount,
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
          this.$toast(this.$t("Failed to load sales report data"), "error");
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
          return this.$t("Invoice-wise Breakdown");
        case "items":
          return this.$t("Item-wise Breakdown");
        case "customers":
          return this.$t("Customer-wise Breakdown");
        case "daily":
          return this.$t("Daily Sales Trend");
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
    this.page_title = this.$t("Sales Report");
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
