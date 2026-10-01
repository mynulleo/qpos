<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1"><i class="fas fa-calendar-alt me-1"></i>Quick Date:</span>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'all' }" @click="applyDatePreset('all')">All Time</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'today' }" @click="applyDatePreset('today')">Today</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'yesterday' }" @click="applyDatePreset('yesterday')">Yesterday</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'last7' }" @click="applyDatePreset('last7')">Last 7 Days</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisMonth' }" @click="applyDatePreset('thisMonth')">This Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'lastMonth' }" @click="applyDatePreset('lastMonth')">Last Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisYear' }" @click="applyDatePreset('thisYear')">This Year</button>
        </div>
      </div>

      <!-- Client Selection -->
      <v-select-container title="Customer (গ্রাহক)" field="search_data.client_id" col="3">
        <v-select
          v-model="search_data.client_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="clients"
          placeholder="-- All Customers (সকল গ্রাহক) --"
          :closeOnSelect="true">
          <template v-slot:option="option">
            <div class="d-flex justify-content-between align-items-center">
              <span>{{ option.name }}</span>
              <small class="text-muted font-monospace">{{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Date Range -->
      <date-picker id="searchfromdate" v-model="search_data.from_date" field="search_data.from_date"
        title="From Date (শুরুর তারিখ)" placeholder="From Date" col="3" :req="false"></date-picker>
      <date-picker id="searchtodate" v-model="search_data.to_date" field="search_data.to_date"
        title="To Date (শেষ তারিখ)" placeholder="To Date" col="3" :req="false"></date-picker>

      <!-- Invoice No Search -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">Invoice No (ইনভয়েস নং)</label>
          <input type="text" class="form-control form-control-sm font-monospace" v-model="search_data.invoice_no" placeholder="e.g. POS-2026...">
        </div>
      </div>

      <!-- Sale Channel Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">Sale Channel (বিক্রয় মাধ্যম)</label>
          <select class="form-select form-select-sm" v-model="search_data.sale_type">
            <option value="all">-- All Channels (সকল মাধ্যম) --</option>
            <option value="pos">POS Terminal (পিওএস)</option>
            <option value="general">General Invoice (সাধারণ ইনভয়েস)</option>
          </select>
        </div>
      </div>
    </template>

    <!-- 📊 Table & Content List -->
    <template v-slot:table-list>
      <!-- Top Action Bar -->
      <div class="col-md-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
              <i class="fas fa-file-invoice-dollar text-primary fs-5"></i>
              <span>VAT / Tax Collection & Audit Report (ভ্যাট ও ট্যাক্স রিপোর্ট)</span>
            </h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace" v-if="siteSetting.vat_no">
              <i class="fas fa-building me-1"></i>BIN / VAT Reg: {{ siteSetting.vat_no }}
            </span>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 font-monospace">
              <i class="fas fa-percent me-1"></i>Default Rate: {{ siteSetting.default_vat }}%
            </span>
          </div>

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
              v-x-tooltip @click="printReport">
              <i class="fas fa-print"></i> Print Report
            </button>
          </div>
        </div>
      </div>

      <!-- 🌟 Metric Summary Cards -->
      <div class="col-12 mb-4">
        <div class="row g-3">
          <!-- Total VAT Collected -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase text-nowrap">Total VAT Collected (মোট ভ্যাট)</div>
                  <div class="fs-4 fw-bold mt-1 font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_vat_collected) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-file-invoice-dollar"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-nowrap">From {{ summaryData.with_vat_invoices }} VAT Invoices</small>
            </div>
          </div>

          <!-- Taxable Sales Base -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-info text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase text-nowrap">Taxable Sales (করযোগ্য বিক্রয়)</div>
                  <div class="fs-4 fw-bold mt-1 font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_taxable_sales) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-chart-line"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-nowrap">Subtotal: <span class="text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_subtotal) }}</span> | Disc: <span class="text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_discount) }}</span></small>
            </div>
          </div>

          <!-- Total Gross Sales (Net + VAT) -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase text-nowrap">Total Gross Invoiced (মোট বিল)</div>
                  <div class="fs-4 fw-bold mt-1 font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_gross_sales) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-cash-register"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block text-nowrap">Paid: <span class="text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_paid) }}</span> | Due: <span class="text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_due) }}</span></small>
            </div>
          </div>

          <!-- Effective VAT Rate -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-warning text-dark h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small fw-bold text-uppercase text-nowrap">Effective VAT Rate (কার্যকর হার)</div>
                  <div class="fs-4 fw-bold mt-1 text-dark font-monospace text-nowrap">{{ summaryData.effective_vat_rate }}%</div>
                </div>
                <div class="metric-icon"><i class="fas fa-percentage text-dark"></i></div>
              </div>
              <small class="text-muted mt-2 d-block text-nowrap">Configured Default Rate: {{ siteSetting.default_vat }}%</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📑 Tab Navigation -->
      <div class="col-12 mb-3">
        <ul class="nav nav-pills custom-report-tabs">
          <li class="nav-item">
            <button class="nav-link text-nowrap" :class="{ 'active': activeTab === 'vat_invoices' }" @click="activeTab = 'vat_invoices'">
              <i class="fas fa-file-invoice-dollar me-1"></i> VAT Invoices (ভ্যাট ইনভয়েস সমূহ) ({{ vatInvoices.length }})
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link text-nowrap" :class="{ 'active': activeTab === 'monthly' }" @click="activeTab = 'monthly'">
              <i class="fas fa-calendar-alt me-1"></i> Monthly Breakdown (মাসিক বিবরণী) ({{ monthlyTrend.length }})
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link text-nowrap" :class="{ 'active': activeTab === 'customers' }" @click="activeTab = 'customers'">
              <i class="fas fa-users me-1"></i> Customer VAT Summary (গ্রাহকভিত্তিক ভ্যাট) ({{ clientVatSummary.length }})
            </button>
          </li>
        </ul>
      </div>

      <!-- 📋 Printable Report Area & Screen Data Table -->
      <div class="col-12" id="printArea">
        <!-- 🧾 Report Header (Visible in Print) -->
        <div class="d-none d-print-block print-header mb-4 text-center">
          <h3 class="fw-bold mb-1">{{ $root.site?.title || 'QPOS Store' }}</h3>
          <p class="mb-1 small text-muted">{{ $root.site?.address || '' }}</p>
          <p class="mb-1 small" v-if="$root.site?.vat_no"><strong>VAT / BIN Registration No:</strong> {{ $root.site?.vat_no }}</p>
          <h5 class="fw-bold mt-2 text-decoration-underline">VAT & TAX COLLECTION STATEMENT</h5>
          <p class="small text-muted mb-0">
            <strong>Statement Period:</strong> {{ search_data.from_date || 'Inception' }} to {{ search_data.to_date || 'Present' }}
            | <strong>Generated:</strong> {{ new Date().toLocaleString() }}
          </p>
        </div>

        <!-- 1. VAT Invoices Tab (Only invoices with VAT > 0) -->
        <div v-show="activeTab === 'vat_invoices'" class="card border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 custom-report-table">
              <thead class="table-dark">
                <tr>
                  <th style="width: 40px;" class="text-center text-nowrap">#</th>
                  <th class="text-nowrap">Invoice Details (ইনভয়েস)</th>
                  <th class="text-nowrap">Customer (গ্রাহক)</th>
                  <th class="text-end text-nowrap">Subtotal (৳)</th>
                  <th class="text-end text-nowrap">Discount (৳)</th>
                  <th class="text-end text-nowrap">Taxable (৳)</th>
                  <th class="text-center text-nowrap">Rate</th>
                  <th class="text-end bg-primary bg-opacity-25 text-white text-nowrap">VAT (৳)</th>
                  <th class="text-end text-nowrap">Total Bill (৳)</th>
                  <th class="text-end text-nowrap">Paid (৳)</th>
                  <th class="text-end text-nowrap">Due (৳)</th>
                  <th class="text-center d-print-none text-nowrap" style="width: 70px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(inv, index) in vatInvoices" :key="inv.id">
                  <td class="text-center text-muted font-monospace small text-nowrap">{{ index + 1 }}</td>
                  <td class="text-nowrap">
                    <div class="d-flex align-items-center gap-1 mb-1">
                      <router-link :to="{ name: 'invoice.show', params: { id: inv.id } }" class="fw-bold font-monospace text-decoration-none text-primary text-nowrap">
                        {{ inv.invoice_no }}
                      </router-link>
                      <span class="badge bg-light text-dark border py-0 px-1 font-monospace" style="font-size: 10px;" v-if="isPosSale(inv)">
                        <i class="fas fa-desktop text-primary me-1"></i>POS
                      </span>
                      <span class="badge bg-light text-dark border py-0 px-1 font-monospace" style="font-size: 10px;" v-else>
                        <i class="fas fa-file-invoice text-secondary me-1"></i>General
                      </span>
                    </div>
                    <small class="text-muted font-monospace d-block" style="font-size: 11px;">
                      <i class="far fa-calendar-alt me-1"></i>{{ formatDate(inv.invoice_date) }}
                    </small>
                  </td>
                  <td class="text-nowrap">
                    <div class="fw-semibold text-dark text-nowrap">{{ inv.client ? inv.client.name : 'Walk-in Customer' }}</div>
                    <small class="text-muted font-monospace text-nowrap" v-if="inv.client?.mobile">{{ inv.client.mobile }}</small>
                  </td>
                  <td class="text-end font-monospace text-nowrap">{{ formatMoney(inv.original_amount) }}</td>
                  <td class="text-end font-monospace text-danger text-nowrap" :class="{ 'text-muted': Number(inv.discount) === 0 }">
                    {{ Number(inv.discount) > 0 ? '-' + formatMoney(inv.discount) : '0.00' }}
                  </td>
                  <td class="text-end font-monospace fw-semibold text-nowrap">
                    {{ formatMoney(Math.max(0, Number(inv.original_amount) - Number(inv.discount))) }}
                  </td>
                  <td class="text-center font-monospace text-nowrap">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-nowrap">
                      {{ getInvoiceVatPercent(inv) }}%
                    </span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-primary bg-primary bg-opacity-10 text-nowrap">
                    {{ formatMoney(inv.vat) }}
                  </td>
                  <td class="text-end font-monospace fw-bold text-dark text-nowrap">{{ formatMoney(inv.amount) }}</td>
                  <td class="text-end font-monospace text-success text-nowrap">{{ formatMoney(inv.paid_amount) }}</td>
                  <td class="text-end font-monospace text-nowrap" :class="Number(inv.amount) - Number(inv.paid_amount) > 0 ? 'text-danger fw-bold' : 'text-muted'">
                    {{ Number(inv.amount) - Number(inv.paid_amount) > 0 ? formatMoney(Number(inv.amount) - Number(inv.paid_amount)) : '0.00' }}
                  </td>
                  <td class="text-center d-print-none text-nowrap">
                    <router-link :to="{ name: 'invoice.show', params: { id: inv.id } }" class="btn btn-xs btn-outline-primary py-0 px-2" title="View Invoice">
                      <i class="fas fa-eye"></i>
                    </router-link>
                  </td>
                </tr>

                <tr v-if="vatInvoices.length === 0">
                  <td colspan="12" class="text-center py-5 text-muted">
                    <i class="fas fa-file-invoice-dollar fs-1 text-muted mb-2 d-block"></i>
                    No VAT invoices found for the selected period.
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-dark fw-bold" v-if="vatInvoices.length > 0">
                <tr>
                  <td colspan="3" class="text-end text-uppercase text-nowrap">Total Summary (সর্বমোট):</td>
                  <td class="text-end font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_subtotal) }}</td>
                  <td class="text-end font-monospace text-danger text-nowrap">-৳&nbsp;{{ formatMoney(summaryData.total_discount) }}</td>
                  <td class="text-end font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_taxable_sales) }}</td>
                  <td class="text-center font-monospace text-white-50 text-nowrap">{{ summaryData.effective_vat_rate }}%</td>
                  <td class="text-end font-monospace bg-primary text-white text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_vat_collected) }}</td>
                  <td class="text-end font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_gross_sales) }}</td>
                  <td class="text-end font-monospace text-success text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_paid) }}</td>
                  <td class="text-end font-monospace text-danger text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_due) }}</td>
                  <td class="d-print-none"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 2. Monthly Trend Tab -->
        <div v-show="activeTab === 'monthly'" class="card border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 custom-report-table">
              <thead class="table-dark">
                <tr>
                  <th style="width: 50px;" class="text-center text-nowrap">#</th>
                  <th class="text-nowrap">Month (মাস)</th>
                  <th class="text-center text-nowrap">Total Invoices</th>
                  <th class="text-center text-nowrap">With VAT Bills</th>
                  <th class="text-end text-nowrap">Taxable Sales (৳)</th>
                  <th class="text-end bg-primary bg-opacity-25 text-white text-nowrap">VAT Collected (৳)</th>
                  <th class="text-end text-nowrap">Gross Sales (৳)</th>
                  <th class="text-end text-nowrap">Effective VAT %</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(m, idx) in monthlyTrend" :key="idx">
                  <td class="text-center text-muted font-monospace text-nowrap">{{ idx + 1 }}</td>
                  <td class="fw-bold text-dark text-nowrap">
                    <i class="far fa-calendar-alt me-1 text-primary"></i> {{ m.month_name }}
                  </td>
                  <td class="text-center font-monospace text-nowrap">{{ m.invoices_count }}</td>
                  <td class="text-center font-monospace text-nowrap">
                    <span class="badge bg-primary text-nowrap">{{ m.with_vat_count }}</span>
                  </td>
                  <td class="text-end font-monospace fw-semibold text-nowrap">{{ formatMoney(m.taxable_amount) }}</td>
                  <td class="text-end font-monospace fw-bold text-primary bg-primary bg-opacity-10 text-nowrap">{{ formatMoney(m.vat_amount) }}</td>
                  <td class="text-end font-monospace fw-bold text-dark text-nowrap">{{ formatMoney(m.gross_amount) }}</td>
                  <td class="text-end font-monospace text-nowrap">
                    {{ m.taxable_amount > 0 ? ((m.vat_amount / m.taxable_amount) * 100).toFixed(2) : '0.00' }}%
                  </td>
                </tr>
                <tr v-if="monthlyTrend.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">No monthly trend data available.</td>
                </tr>
              </tbody>
              <tfoot class="table-dark fw-bold" v-if="monthlyTrend.length > 0">
                <tr>
                  <td colspan="4" class="text-end text-uppercase text-nowrap">Total:</td>
                  <td class="text-end font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_taxable_sales) }}</td>
                  <td class="text-end font-monospace bg-primary text-white text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_vat_collected) }}</td>
                  <td class="text-end font-monospace text-nowrap">৳&nbsp;{{ formatMoney(summaryData.total_gross_sales) }}</td>
                  <td class="text-end font-monospace text-nowrap">{{ summaryData.effective_vat_rate }}%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 3. Customer VAT Summary Tab -->
        <div v-show="activeTab === 'customers'" class="card border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 custom-report-table">
              <thead class="table-dark">
                <tr>
                  <th style="width: 50px;" class="text-center text-nowrap">#</th>
                  <th class="text-nowrap">Customer (গ্রাহক)</th>
                  <th class="text-nowrap">Mobile Number</th>
                  <th class="text-center text-nowrap">VAT Invoices</th>
                  <th class="text-end text-nowrap">Taxable Amount (৳)</th>
                  <th class="text-end bg-primary bg-opacity-25 text-white text-nowrap">VAT Contributed (৳)</th>
                  <th class="text-end text-nowrap">Gross Amount (৳)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(c, idx) in clientVatSummary" :key="idx">
                  <td class="text-center text-muted font-monospace text-nowrap">{{ idx + 1 }}</td>
                  <td class="fw-bold text-dark text-nowrap">
                    <i class="fas fa-user-circle me-1 text-secondary"></i> {{ c.client_name }}
                  </td>
                  <td class="font-monospace text-muted text-nowrap">{{ c.client_mobile }}</td>
                  <td class="text-center font-monospace text-nowrap">
                    <span class="badge bg-info text-dark text-nowrap">{{ c.invoices_count }}</span>
                  </td>
                  <td class="text-end font-monospace fw-semibold text-nowrap">{{ formatMoney(c.taxable_amount) }}</td>
                  <td class="text-end font-monospace fw-bold text-primary bg-primary bg-opacity-10 text-nowrap">{{ formatMoney(c.vat_amount) }}</td>
                  <td class="text-end font-monospace fw-bold text-dark text-nowrap">{{ formatMoney(c.gross_amount) }}</td>
                </tr>
                <tr v-if="clientVatSummary.length === 0">
                  <td colspan="7" class="text-center py-5 text-muted">No client-wise VAT data recorded.</td>
                </tr>
              </tbody>
              <tfoot class="table-dark fw-bold" v-if="clientVatSummary.length > 0">
                <tr>
                  <td colspan="4" class="text-end text-uppercase text-nowrap">Top Customers VAT Total:</td>
                  <td class="text-end font-monospace text-nowrap">
                    ৳&nbsp;{{ formatMoney(clientVatSummary.reduce((s, i) => s + (Number(i.taxable_amount) || 0), 0)) }}
                  </td>
                  <td class="text-end font-monospace bg-primary text-white text-nowrap">
                    ৳&nbsp;{{ formatMoney(clientVatSummary.reduce((s, i) => s + (Number(i.vat_amount) || 0), 0)) }}
                  </td>
                  <td class="text-end font-monospace text-nowrap">
                    ৳&nbsp;{{ formatMoney(clientVatSummary.reduce((s, i) => s + (Number(i.gross_amount) || 0), 0)) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 🖨️ Printable Summary & Signatures -->
        <div class="d-none d-print-block mt-5 pt-4">
          <div class="row g-3">
            <div class="col-4 text-center">
              <div class="border-top border-dark pt-1 mt-5">
                <p class="small mb-0 fw-bold">Prepared By</p>
                <p class="text-muted" style="font-size: 10px;">Accounts / Cashier</p>
              </div>
            </div>
            <div class="col-4 text-center">
              <div class="border-top border-dark pt-1 mt-5">
                <p class="small mb-0 fw-bold">Checked By</p>
                <p class="text-muted" style="font-size: 10px;">Internal Auditor</p>
              </div>
            </div>
            <div class="col-4 text-center">
              <div class="border-top border-dark pt-1 mt-5">
                <p class="small mb-0 fw-bold">Authorized Signature</p>
                <p class="text-muted" style="font-size: 10px;">Managing Director / Management</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </index-page>
</template>

<script>
const model = "report";

export default {
  name: "VatReport",
  data() {
    return {
      page_title: "VAT / Tax Report",
      model: model,
      activePreset: "thisMonth",
      activeTab: "vat_invoices",
      clients: [],
      invoices: [],
      monthlyTrend: [],
      clientVatSummary: [],
      siteSetting: {
        default_vat: 0,
        vat_no: "",
        sale_nature: "both",
      },
      summaryData: {
        total_invoices: 0,
        with_vat_invoices: 0,
        without_vat_invoices: 0,
        total_subtotal: 0,
        total_discount: 0,
        total_taxable_sales: 0,
        total_vat_collected: 0,
        total_gross_sales: 0,
        total_paid: 0,
        total_due: 0,
        effective_vat_rate: 0,
      },
      search_data: {
        from_date: "",
        to_date: "",
        client_id: null,
        invoice_no: "",
        sale_type: "all",
      },
    };
  },

  computed: {
    vatInvoices() {
      return (this.invoices || []).filter((inv) => Number(inv.vat) > 0);
    },

    exportFileName() {
      const from = this.search_data.from_date || "Start";
      const to = this.search_data.to_date || "End";
      let type = "VAT_Invoices_Statement";
      if (this.activeTab === "monthly") type = "Monthly_VAT_Statement";
      else if (this.activeTab === "customers") type = "Customer_VAT_Summary";
      return `${type}_${from}_to_${to}.xls`;
    },

    exportData() {
      if (this.activeTab === "vat_invoices") {
        return this.vatInvoices.map((inv, idx) => {
          const taxable = Math.max(0, (Number(inv.original_amount) || 0) - (Number(inv.discount) || 0));
          const rate = this.getInvoiceVatPercent(inv);
          return {
            "SL": idx + 1,
            "Invoice No": inv.invoice_no,
            "Invoice Date": inv.invoice_date,
            "Customer Name": inv.client ? inv.client.name : "Walk-in Customer",
            "Customer Mobile": inv.client ? inv.client.mobile : "",
            "Sale Channel": this.isPosSale(inv) ? "POS" : "General Invoice",
            "Subtotal (Tk)": Number(inv.original_amount) || 0,
            "Discount (Tk)": Number(inv.discount) || 0,
            "Taxable Base (Tk)": taxable,
            "VAT Rate %": rate + "%",
            "VAT Amount (Tk)": Number(inv.vat) || 0,
            "Total Bill (Tk)": Number(inv.amount) || 0,
            "Paid Amount (Tk)": Number(inv.paid_amount) || 0,
            "Due Amount (Tk)": Math.max(0, (Number(inv.amount) || 0) - (Number(inv.paid_amount) || 0)),
            "Payment Status": (Number(inv.paid_amount) || 0) >= (Number(inv.amount) || 0) ? "Paid" : "Due",
          };
        });
      }

      if (this.activeTab === "customers") {
        return this.clientVatSummary.map((c, idx) => ({
          "SL": idx + 1,
          "Customer Name": c.client_name,
          "Mobile Number": c.client_mobile,
          "VAT Invoices Count": c.invoices_count,
          "Taxable Amount (Tk)": Number(c.taxable_amount) || 0,
          "VAT Contributed (Tk)": Number(c.vat_amount) || 0,
          "Gross Amount (Tk)": Number(c.gross_amount) || 0,
        }));
      }

      return this.monthlyTrend.map((m, idx) => ({
        "SL": idx + 1,
        "Month": m.month_name,
        "Total Invoices": m.invoices_count,
        "With VAT Invoices": m.with_vat_count,
        "Taxable Sales (Tk)": Number(m.taxable_amount) || 0,
        "VAT Collected (Tk)": Number(m.vat_amount) || 0,
        "Gross Amount (Tk)": Number(m.gross_amount) || 0,
        "Effective VAT %": m.taxable_amount > 0 ? ((m.vat_amount / m.taxable_amount) * 100).toFixed(2) : 0,
      }));
    },
  },

  methods: {
    formatMoney(val) {
      return Number(val || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatDate(val) {
      if (!val) return "";
      return val;
    },

    isPosSale(inv) {
      if (!inv.details || !Array.isArray(inv.details)) return false;
      return inv.details.some((d) => d.reference === "POS Sale");
    },

    getInvoiceVatPercent(inv) {
      if (inv.vat_percent !== undefined && inv.vat_percent !== null && Number(inv.vat_percent) > 0) {
        return Number(inv.vat_percent);
      }
      const taxable = Math.max(0, (Number(inv.original_amount) || 0) - (Number(inv.discount) || 0));
      if (taxable > 0 && Number(inv.vat) > 0) {
        return Number(((Number(inv.vat) / taxable) * 100).toFixed(1));
      }
      return Number(this.siteSetting?.default_vat || 0);
    },

    applyDatePreset(preset) {
      this.activePreset = preset;
      const today = new Date();
      const format = (d) => {
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();
        return `${dd}-${mm}-${yyyy}`;
      };

      if (preset === "all") {
        this.search_data.from_date = "";
        this.search_data.to_date = "";
      } else if (preset === "today") {
        this.search_data.from_date = format(today);
        this.search_data.to_date = format(today);
      } else if (preset === "yesterday") {
        const y = new Date(today);
        y.setDate(y.getDate() - 1);
        this.search_data.from_date = format(y);
        this.search_data.to_date = format(y);
      } else if (preset === "last7") {
        const d7 = new Date(today);
        d7.setDate(d7.getDate() - 6);
        this.search_data.from_date = format(d7);
        this.search_data.to_date = format(today);
      } else if (preset === "thisMonth") {
        const startMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        this.search_data.from_date = format(startMonth);
        this.search_data.to_date = format(today);
      } else if (preset === "lastMonth") {
        const startLast = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const endLast = new Date(today.getFullYear(), today.getMonth(), 0);
        this.search_data.from_date = format(startLast);
        this.search_data.to_date = format(endLast);
      } else if (preset === "thisYear") {
        const startYear = new Date(today.getFullYear(), 0, 1);
        this.search_data.from_date = format(startYear);
        this.search_data.to_date = format(today);
      }

      this.getReportData();
    },

    search() {
      this.getReportData();
    },

    clearSearch() {
      this.search_data = {
        from_date: "",
        to_date: "",
        client_id: null,
        invoice_no: "",
        sale_type: "all",
      };
      this.activePreset = "thisMonth";
      this.applyDatePreset("thisMonth");
    },

    getReportData() {
      this.$root.spinner = true;
      const params = { ...this.search_data };

      axios
        .get("report/vat", { params })
        .then((res) => {
          if (res.data) {
            this.invoices = res.data.invoices || [];
            this.summaryData = res.data.summary || this.summaryData;
            this.monthlyTrend = res.data.monthly_trend || [];
            this.clientVatSummary = res.data.client_vat_summary || [];
            if (res.data.site_vat_setting) {
              this.siteSetting = res.data.site_vat_setting;
            }
          }
        })
        .catch((err) => {
          console.error("VAT report fetch error:", err);
          this.$toast("Failed to load VAT report", "error");
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },

    getClients() {
      axios
        .get("client?page=1&per_page=1000")
        .then((res) => {
          if (res.data && res.data.data) {
            this.clients = res.data.data;
          } else if (Array.isArray(res.data)) {
            this.clients = res.data;
          }
        })
        .catch((e) => console.error(e));
    },

    printReport() {
      window.print();
    },
  },

  mounted() {
    this.getClients();
    this.applyDatePreset("thisMonth");
  },
};
</script>

<style scoped>
.custom-report-tabs .nav-link {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  border-radius: 6px;
  padding: 8px 16px;
  transition: all 0.2s ease;
  background-color: #f1f5f9;
  margin-right: 6px;
  white-space: nowrap;
}

.custom-report-tabs .nav-link.active {
  background-color: rgb(17, 44, 70);
  color: #ffffff;
  box-shadow: 0 2px 6px rgba(17, 44, 70, 0.25);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
}

.bg-gradient-success {
  background: linear-gradient(135deg, #065f46 0%, #10b981 100%) !important;
}

.bg-gradient-info {
  background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%) !important;
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%) !important;
}

.metric-icon {
  font-size: 28px;
  opacity: 0.75;
}

.custom-report-table th,
.custom-report-table td {
  white-space: nowrap !important;
}

.custom-report-table th {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  padding: 10px 8px;
}

.custom-report-table td {
  font-size: 12.5px;
  padding: 8px;
}

.text-nowrap {
  white-space: nowrap !important;
}

.btn-xs {
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 4px;
}

.table-opacity-10 {
  background-color: rgba(59, 130, 246, 0.04) !important;
}

@media print {
  body {
    background: #ffffff !important;
    font-size: 11px !important;
    color: #000 !important;
  }

  .nav-pills,
  .btn,
  .d-print-none,
  .site-header,
  .main-sidebar,
  .search-card,
  nav {
    display: none !important;
  }

  .d-print-block {
    display: block !important;
  }

  .card {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
  }

  .custom-report-table th,
  .custom-report-table td {
    padding: 4px 6px !important;
    font-size: 10.5px !important;
    border: 1px solid #333 !important;
    white-space: nowrap !important;
  }

  .custom-report-table thead th {
    background-color: #f1f5f9 !important;
    color: #000 !important;
  }

  .table-dark {
    background-color: #f1f5f9 !important;
    color: #000 !important;
  }
}
</style>
