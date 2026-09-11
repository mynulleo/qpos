<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Quick Date Presets Bar -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1">
            <i class="fas fa-calendar-alt me-1 text-primary"></i> Quick Date:
          </span>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary"
            :class="{ 'active': activePreset === 'today' }"
            @click="applyDatePreset('today')"
          >
            Today
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary"
            :class="{ 'active': activePreset === 'yesterday' }"
            @click="applyDatePreset('yesterday')"
          >
            Yesterday
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary"
            :class="{ 'active': activePreset === 'last7' }"
            @click="applyDatePreset('last7')"
          >
            Last 7 Days
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary"
            :class="{ 'active': activePreset === 'thisMonth' }"
            @click="applyDatePreset('thisMonth')"
          >
            This Month (চলতি মাস)
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary"
            :class="{ 'active': activePreset === 'lastMonth' }"
            @click="applyDatePreset('lastMonth')"
          >
            Last Month
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-primary"
            :class="{ 'active': activePreset === 'thisYear' }"
            @click="applyDatePreset('thisYear')"
          >
            This Year
          </button>
          <button
            type="button"
            class="btn btn-xs btn-outline-secondary"
            :class="{ 'active': activePreset === 'all' }"
            @click="applyDatePreset('all')"
          >
            All Time
          </button>
        </div>
      </div>

      <!-- Date Pickers -->
      <date-picker
        id="searchfromdate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date (শুরুর তারিখ)"
        placeholder="From Date"
        col="3"
        :req="false"
      />
      <date-picker
        id="searchtodate"
        v-model="search_data.to_date"
        field="search_data.to_date"
        title="To Date (শেষ তারিখ)"
        placeholder="To Date"
        col="3"
        :req="false"
      />

      <!-- Customer / Client -->
      <v-select-container title="Customer (গ্রাহক)" field="search_data.client_id" col="3">
        <v-select
          v-model="search_data.client_id"
          label="name"
          :reduce="obj => obj.id"
          :options="clients"
          placeholder="-- All Customers --"
          :closeOnSelect="true"
        >
          <template #option="option">
            <div>
              <strong>{{ option.name }}</strong>
              <small class="text-muted d-block" v-if="option.mobile">{{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Return Reason Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">
            <i class="fas fa-tag me-1 text-info"></i> Return Reason (ফেরতের কারণ)
          </label>
          <select class="form-select form-select-sm" v-model="search_data.return_reason">
            <option value="all">-- All Reasons (সকল কারণ) --</option>
            <option value="Client request">Client request (ভালো পণ্য - স্টকে জমা)</option>
            <option value="Wastage">Wastage (নষ্ট/ড্যামেজ - ওয়েস্টেজ)</option>
            <option value="Date Expaired">Date Expaired (মেয়াদোত্তীর্ণ - ওয়েস্টেজ)</option>
          </select>
        </div>
      </div>

      <!-- Refund / Payment Method Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">
            <i class="fas fa-wallet me-1 text-success"></i> Refund Method (টাকা ফেরত মাধ্যম)
          </label>
          <select class="form-select form-select-sm" v-model="search_data.payment_method">
            <option value="all">-- All Methods (সকল মাধ্যম) --</option>
            <option value="Cash">Cash (নগদ ফেরত)</option>
            <option value="bKash">bKash (বিকাশ)</option>
            <option value="Nagad">Nagad (নগদ)</option>
            <option value="Rocket">Rocket (রকেট)</option>
            <option value="Bank">Bank Transfer</option>
          </select>
        </div>
      </div>

      <!-- Category Filter -->
      <v-select-container title="Category (ক্যাটাগরি)" field="search_data.category_id" col="3">
        <v-select
          v-model="search_data.category_id"
          label="title"
          :reduce="obj => obj.id"
          :options="categories"
          placeholder="-- All Categories --"
          :closeOnSelect="true"
        />
      </v-select-container>

      <!-- Item Filter -->
      <v-select-container title="Item (পণ্য)" field="search_data.item_id" col="3">
        <v-select
          v-model="search_data.item_id"
          label="title"
          :reduce="obj => obj.id"
          :options="items"
          placeholder="-- All Items --"
          :closeOnSelect="true"
        >
          <template #option="option">
            <div>
              <span>{{ option.title }}</span>
              <small class="text-muted d-block" v-if="option.barcode">Barcode: {{ option.barcode }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Keyword Search -->
      <Input
        v-model="search_data.keyword"
        field="search_data.keyword"
        title="Keyword (চালান/ইনভয়েস/মোবাইল/নোট)"
        placeholder="e.g. RET-..., POS-..., Note text..."
        col="3"
        :req="false"
      />
    </template>

    <!-- 📊 Report Content & Analysis View -->
    <template v-slot:table-list>
      <!-- Top Action Toolbar -->
      <div class="col-md-12 mb-3 d-print-none">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <!-- View Mode Tabs -->
          <ul class="nav nav-pills custom-report-tabs">
            <li class="nav-item">
              <button
                class="nav-link"
                :class="{ 'active': activeTab === 'returns' }"
                @click="activeTab = 'returns'"
              >
                <i class="fas fa-file-invoice-dollar me-1"></i> Return Slips View (রিটার্ন চালান সমূহ) ({{ returns.length }})
              </button>
            </li>
            <li class="nav-item">
              <button
                class="nav-link"
                :class="{ 'active': activeTab === 'items' }"
                @click="activeTab = 'items'"
              >
                <i class="fas fa-boxes me-1"></i> Item Breakdown (পণ্য ভিত্তিক ফেরত) ({{ item_breakdown.length }})
              </button>
            </li>
            <li class="nav-item">
              <button
                class="nav-link"
                :class="{ 'active': activeTab === 'customers' }"
                @click="activeTab = 'customers'"
              >
                <i class="fas fa-users me-1"></i> Customer Breakdown (গ্রাহক ভিত্তিক) ({{ customer_breakdown.length }})
              </button>
            </li>
            <li class="nav-item">
              <button
                class="nav-link"
                :class="{ 'active': activeTab === 'daily' }"
                @click="activeTab = 'daily'"
              >
                <i class="fas fa-calendar-day me-1"></i> Daily Trend (দৈনিক সামারি) ({{ daily_breakdown.length }})
              </button>
            </li>
          </ul>

          <!-- Action Buttons -->
          <div class="d-flex align-items-center gap-2">
            <router-link to="/pos/return" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 shadow-sm">
              <i class="fas fa-plus-circle"></i> New Return (নতুন ফেরত)
            </router-link>

            <!-- Excel Export -->
            <download-excel
              v-if="exportData && exportData.length > 0"
              class="btn btn-sm btn-success cursor-pointer d-flex align-items-center gap-1 shadow-sm"
              :data="exportData"
              :name="exportFileName"
              title="Export to Excel"
            >
              <i class="fas fa-file-excel"></i> Excel Export
            </download-excel>

            <!-- Print Button -->
            <button
              type="button"
              class="btn btn-sm btn-dark d-flex align-items-center gap-1 shadow-sm"
              @click="triggerPrint"
              title="Print Report"
            >
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>
      </div>

      <!-- 📈 5 Operational & Financial KPI Cards -->
      <div class="col-md-12 mb-4">
        <div class="row g-2">
          <!-- Card 1: Total Return Invoices -->
          <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-bold">Total Returns (চালান)</span>
                <div class="kpi-icon-badge bg-primary-subtle text-primary">
                  <i class="fas fa-undo-alt"></i>
                </div>
              </div>
              <h4 class="mb-0 fw-bold font-monospace text-primary">{{ summary.total_returns || 0 }}</h4>
              <small class="text-muted" style="font-size: 11px;">ফেরত লেনদেন সংখ্যা</small>
            </div>
          </div>

          <!-- Card 2: Total Refund Amount -->
          <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100 border-start border-4 border-danger">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-bold">Total Refund (মোট ফেরত টাকা)</span>
                <div class="kpi-icon-badge bg-danger-subtle text-danger">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="mb-0 fw-bold font-monospace text-danger">Tk. {{ formatMoney(summary.total_refund) }}</h4>
              <small class="text-muted" style="font-size: 11px;">গ্রাহকদের মোট ফেরত প্রদান</small>
            </div>
          </div>

          <!-- Card 3: Total Quantity Returned -->
          <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-bold">Total Items (মোট পিস)</span>
                <div class="kpi-icon-badge bg-info-subtle text-info">
                  <i class="fas fa-cubes"></i>
                </div>
              </div>
              <h4 class="mb-0 fw-bold font-monospace text-dark">{{ summary.total_qty || 0 }} pcs</h4>
              <small class="text-muted" style="font-size: 11px;">মোট ফেরত পণ্যের পরিমাণ</small>
            </div>
          </div>

          <!-- Card 4: Restocked / Good Stock -->
          <div class="col-xl-2 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100 border-start border-3 border-success">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i> Restocked (স্টকে)</span>
                <span class="badge bg-success font-monospace">{{ summary.restocked_qty || 0 }} pcs</span>
              </div>
              <h5 class="mb-0 fw-bold font-monospace text-success">Tk. {{ formatMoney(summary.restocked_amount) }}</h5>
              <small class="text-muted" style="font-size: 11px;">ভালো পণ্য পুনরায় স্টকে যুক্ত</small>
            </div>
          </div>

          <!-- Card 5: Wastage & Expired Loss -->
          <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100 border-start border-3 border-warning">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-warning-emphasis small fw-bold"><i class="fas fa-trash-alt me-1"></i> Wastage/Expired (ক্ষতি)</span>
                <span class="badge bg-danger font-monospace">{{ summary.wastage_qty || 0 }} pcs</span>
              </div>
              <h5 class="mb-0 fw-bold font-monospace text-danger">Tk. {{ formatMoney(summary.wastage_amount) }}</h5>
              <small class="text-muted" style="font-size: 11px;">নষ্ট/মেয়াদোত্তীর্ণ - ওয়েস্টেজ এন্ট্রি</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 🏷️ Return Reason Breakdown Summary Banner -->
      <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm bg-light p-2">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
              <span class="small fw-bold text-dark"><i class="fas fa-chart-pie me-1 text-primary"></i> Reasons Breakdown (কারণের বিবরণী):</span>
              <span class="badge bg-success px-2 py-1 shadow-sm">
                <i class="fas fa-user-check me-1"></i> Client Request: {{ summary.reasons ? summary.reasons['Client request'].count : 0 }} slips (Tk. {{ formatMoney(summary.reasons ? summary.reasons['Client request'].amount : 0) }})
              </span>
              <span class="badge bg-danger px-2 py-1 shadow-sm">
                <i class="fas fa-trash-alt me-1"></i> Wastage: {{ summary.reasons ? summary.reasons['Wastage'].count : 0 }} slips (Tk. {{ formatMoney(summary.reasons ? summary.reasons['Wastage'].amount : 0) }})
              </span>
              <span class="badge bg-warning text-dark px-2 py-1 shadow-sm">
                <i class="fas fa-calendar-times me-1"></i> Date Expaired: {{ summary.reasons ? summary.reasons['Date Expaired'].count : 0 }} slips (Tk. {{ formatMoney(summary.reasons ? summary.reasons['Date Expaired'].amount : 0) }})
              </span>
            </div>
            <div class="small text-muted font-monospace">
              <span>Date: {{ dateRangeDisplay }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Content Area -->
      <div class="col-md-12">
        <!-- 📌 VIEW 1: Return Slips / Invoices Table (Detailed View with Reason & Note) -->
        <div v-show="activeTab === 'returns'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0 custom-return-table">
              <thead class="table-dark text-center">
                <tr>
                  <th width="3%">#</th>
                  <th width="12%">Return Slip No & Date</th>
                  <th width="12%">Original Invoice</th>
                  <th width="15%">Customer (গ্রাহক)</th>
                  <th width="12%">Return Reason (কারণ)</th>
                  <th width="16%">Returned Items & Qty</th>
                  <th width="10%" class="text-end">Refund Amount</th>
                  <th width="10%">Refund Method</th>
                  <th width="18%">Return Note / Remarks (মন্তব্য)</th>
                  <th width="8%" class="d-print-none text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="returns.length > 0">
                  <tr v-for="(ret, index) in returns" :key="ret.id">
                    <td class="text-center font-monospace small">{{ index + 1 }}</td>
                    
                    <!-- Return Slip No & Date -->
                    <td>
                      <div class="fw-bold font-monospace text-primary">{{ ret.return_no }}</div>
                      <small class="text-muted d-block font-monospace">
                        <i class="far fa-calendar-alt me-1"></i>{{ ret.return_date }}
                      </small>
                    </td>

                    <!-- Original Invoice -->
                    <td>
                      <div v-if="ret.invoice">
                        <span class="badge bg-light text-dark border font-monospace">{{ ret.invoice.invoice_no }}</span>
                        <small class="text-muted d-block font-monospace" style="font-size: 11px;">
                          Inv Date: {{ ret.invoice.invoice_date }}
                        </small>
                      </div>
                      <span v-else class="text-muted small">N/A</span>
                    </td>

                    <!-- Customer -->
                    <td>
                      <div v-if="ret.client">
                        <div class="fw-bold text-dark">{{ ret.client.name }}</div>
                        <small class="text-muted font-monospace" v-if="ret.client.mobile">
                          <i class="fas fa-phone-alt me-1 text-muted" style="font-size: 10px;"></i>{{ ret.client.mobile }}
                        </small>
                      </div>
                      <div v-else>
                        <span class="text-muted fst-italic">Walk-in Customer</span>
                      </div>
                    </td>

                    <!-- Return Reason Badge -->
                    <td class="text-center">
                      <span v-if="ret.return_reason === 'Client request'" class="badge bg-success px-2 py-1 shadow-sm d-inline-flex align-items-center gap-1">
                        <i class="fas fa-user-check"></i> Client request
                      </span>
                      <span v-else-if="ret.return_reason === 'Wastage'" class="badge bg-danger px-2 py-1 shadow-sm d-inline-flex align-items-center gap-1">
                        <i class="fas fa-trash-alt"></i> Wastage
                      </span>
                      <span v-else-if="ret.return_reason === 'Date Expaired'" class="badge bg-warning text-dark px-2 py-1 shadow-sm d-inline-flex align-items-center gap-1">
                        <i class="fas fa-calendar-times"></i> Date Expaired
                      </span>
                      <span v-else class="badge bg-secondary px-2 py-1">
                        {{ ret.return_reason || 'N/A' }}
                      </span>
                      <small class="d-block text-muted mt-1" style="font-size: 10px;">
                        {{ ret.return_reason === 'Client request' ? 'স্টকে জমা' : 'ওয়েস্টেজ এন্ট্রি' }}
                      </small>
                    </td>

                    <!-- Returned Items & Qty -->
                    <td>
                      <div v-if="ret.details && ret.details.length > 0">
                        <div v-for="(d, dIdx) in ret.details" :key="dIdx" class="mb-1 pb-1 border-bottom border-light">
                          <div class="d-flex justify-content-between align-items-start gap-1">
                            <span class="fw-semibold text-dark small" style="line-height: 1.2;">
                              {{ d.item ? d.item.title : 'Item #' + d.item_id }}
                            </span>
                            <span class="badge bg-dark font-monospace text-nowrap">{{ d.qty }} {{ d.unit ? d.unit.title : 'pcs' }}</span>
                          </div>
                          <div class="d-flex gap-1 flex-wrap mt-1">
                            <span class="badge bg-info-subtle text-dark" v-if="d.color" style="font-size: 10px;">{{ d.color.title }}</span>
                            <span class="badge bg-secondary-subtle text-dark" v-if="d.size" style="font-size: 10px;">{{ d.size.title }}</span>
                            <span class="badge bg-light text-muted font-monospace border" v-if="d.serial_no" style="font-size: 10px;">SN: {{ d.serial_no }}</span>
                          </div>
                        </div>
                      </div>
                      <span v-else class="text-muted small">Total Qty: {{ ret.total_qty }} pcs</span>
                    </td>

                    <!-- Refund Amount -->
                    <td class="text-end font-monospace">
                      <span class="fw-bold text-danger fs-6">Tk. {{ formatMoney(ret.total_refund_amount) }}</span>
                    </td>

                    <!-- Refund Method -->
                    <td>
                      <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                        <i class="fas fa-money-check-alt me-1 text-primary"></i>{{ ret.payment_method || 'Cash' }}
                      </span>
                      <small class="d-block text-muted font-monospace mt-1" v-if="ret.trxid" style="font-size: 11px;">
                        Trx: {{ ret.trxid }}
                      </small>
                    </td>

                    <!-- 🌟 RETURN NOTE / REMARKS (Prominently Highlighted) -->
                    <td>
                      <div v-if="ret.note" class="return-note-box p-2 rounded border bg-light shadow-xs">
                        <i class="fas fa-quote-left text-muted me-1 small"></i>
                        <span class="fw-semibold text-dark small">{{ ret.note }}</span>
                      </div>
                      <span v-else class="text-muted small fst-italic">No note provided</span>
                    </td>

                    <!-- Actions -->
                    <td class="d-print-none text-center">
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary shadow-sm d-inline-flex align-items-center gap-1"
                        @click="openReturnModal(ret)"
                        title="View Full Return Slip Voucher"
                      >
                        <i class="fas fa-eye"></i> View Slip
                      </button>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                      <i class="fas fa-undo-alt fa-3x text-muted mb-2 d-block opacity-50"></i>
                      <h6>কোনো রিটার্ন রেকর্ড পাওয়া যায়নি (No return records found)</h6>
                      <small>তারিখ পরিবর্তন করে অথবা ফিল্টার রিস্টার্ট করে পুনরায় চেষ্টা করুন।</small>
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="returns.length > 0">
                <tr>
                  <td colspan="5" class="text-end">Grand Total (সর্বমোট):</td>
                  <td class="text-center text-info">{{ summary.total_qty }} pcs</td>
                  <td class="text-end text-danger fs-6">Tk. {{ formatMoney(summary.total_refund) }}</td>
                  <td colspan="3"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 2: Item-wise Return Analysis Table -->
        <div v-show="activeTab === 'items'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="24%">Item Title (পণ্যের নাম)</th>
                  <th width="12%">Barcode</th>
                  <th width="12%">Category</th>
                  <th width="10%">Variant (Color/Size)</th>
                  <th width="8%" class="text-center">Total Return</th>
                  <th width="8%" class="text-center">Restocked</th>
                  <th width="8%" class="text-center">Wastage</th>
                  <th width="9%" class="text-end">Avg Refund Rate</th>
                  <th width="11%" class="text-end">Total Refund Value</th>
                  <th width="14%">Return Reasons</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="item_breakdown.length > 0">
                  <tr v-for="(item, index) in item_breakdown" :key="index">
                    <td class="text-center font-monospace small">{{ index + 1 }}</td>
                    <td class="fw-bold text-dark">{{ item.item_title }}</td>
                    <td class="text-center font-monospace">{{ item.barcode }}</td>
                    <td class="text-center">{{ item.category }}</td>
                    <td class="text-center">
                      <span class="badge bg-info text-dark me-1" v-if="item.color">{{ item.color }}</span>
                      <span class="badge bg-secondary" v-if="item.size">{{ item.size }}</span>
                      <span v-if="!item.color && !item.size" class="text-muted small">Standard</span>
                    </td>
                    <td class="text-center font-monospace fw-bold text-primary">
                      {{ item.total_qty }} {{ item.unit }}
                    </td>
                    <td class="text-center font-monospace text-success fw-semibold">
                      {{ item.restocked_qty }}
                    </td>
                    <td class="text-center font-monospace text-danger fw-semibold">
                      {{ item.wastage_qty + item.expired_qty }}
                    </td>
                    <td class="text-end font-monospace">Tk. {{ formatMoney(item.avg_rate) }}</td>
                    <td class="text-end font-monospace fw-bold text-danger">Tk. {{ formatMoney(item.total_refund) }}</td>
                    <td>
                      <small class="fw-semibold text-muted">{{ item.reasons_text || '-' }}</small>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="11" class="text-center py-4 text-muted">
                      No item return records found for the selected period.
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="item_breakdown.length > 0">
                <tr>
                  <td colspan="5" class="text-end">Total:</td>
                  <td class="text-center text-info">{{ summary.total_qty }} pcs</td>
                  <td class="text-center text-success">{{ summary.restocked_qty }} pcs</td>
                  <td class="text-center text-danger">{{ summary.wastage_qty }} pcs</td>
                  <td></td>
                  <td class="text-end text-danger">Tk. {{ formatMoney(summary.total_refund) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 3: Customer Breakdown Table -->
        <div v-show="activeTab === 'customers'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="5%">#</th>
                  <th width="15%">Customer ID</th>
                  <th width="30%">Customer Name</th>
                  <th width="20%">Mobile</th>
                  <th width="12%" class="text-center">Return Slips Count</th>
                  <th width="10%" class="text-center">Total Qty Returned</th>
                  <th width="15%" class="text-end">Total Refunded Amount</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="customer_breakdown.length > 0">
                  <tr v-for="(cust, index) in customer_breakdown" :key="index">
                    <td class="text-center font-monospace">{{ index + 1 }}</td>
                    <td class="text-center font-monospace">{{ cust.clientid }}</td>
                    <td class="fw-bold text-dark">{{ cust.name }}</td>
                    <td class="text-center font-monospace">{{ cust.mobile }}</td>
                    <td class="text-center">
                      <span class="badge bg-secondary font-monospace">{{ cust.returns_count }}</span>
                    </td>
                    <td class="text-center font-monospace fw-bold">{{ cust.total_qty }} pcs</td>
                    <td class="text-end font-monospace fw-bold text-danger">
                      Tk. {{ formatMoney(cust.refund_amount) }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                      No customer return records found.
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="customer_breakdown.length > 0">
                <tr>
                  <td colspan="4" class="text-end">Grand Total:</td>
                  <td class="text-center">{{ summary.total_returns }}</td>
                  <td class="text-center">{{ summary.total_qty }} pcs</td>
                  <td class="text-end text-danger">Tk. {{ formatMoney(summary.total_refund) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 4: Daily Return Trend Table -->
        <div v-show="activeTab === 'daily'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="5%">#</th>
                  <th width="25%">Return Date (তারিখ)</th>
                  <th width="20%" class="text-center">Returns Count (চালান)</th>
                  <th width="20%" class="text-center">Total Qty Returned</th>
                  <th width="30%" class="text-end">Total Refund Amount (টাকা)</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="daily_breakdown.length > 0">
                  <tr v-for="(day, index) in daily_breakdown" :key="index">
                    <td class="text-center font-monospace">{{ index + 1 }}</td>
                    <td class="fw-bold font-monospace">
                      <i class="far fa-calendar-alt me-1 text-primary"></i>{{ day.date }}
                    </td>
                    <td class="text-center font-monospace">
                      <span class="badge bg-secondary">{{ day.returns_count }}</span>
                    </td>
                    <td class="text-center font-monospace fw-bold">{{ day.total_qty }} pcs</td>
                    <td class="text-end font-monospace fw-bold text-danger">
                      Tk. {{ formatMoney(day.refund_amount) }}
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                      No daily records found.
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="daily_breakdown.length > 0">
                <tr>
                  <td colspan="2" class="text-end">Total:</td>
                  <td class="text-center">{{ summary.total_returns }}</td>
                  <td class="text-center">{{ summary.total_qty }} pcs</td>
                  <td class="text-end text-danger">Tk. {{ formatMoney(summary.total_refund) }}</td>
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

      <!-- 🌟 DETAILED RETURN SLIP MODAL -->
      <div
        class="modal fade"
        id="returnSlipModal"
        tabindex="-1"
        aria-labelledby="returnSlipModalLabel"
        aria-hidden="true"
        ref="returnSlipModal"
      >
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg" v-if="selectedReturn">
            <div class="modal-header text-white py-3 px-4" style="background-color: #112C47;">
              <div class="d-flex align-items-center gap-2">
                <i class="fas fa-undo-alt fs-5 text-warning"></i>
                <h5 class="modal-title fw-bold mb-0" id="returnSlipModalLabel">
                  Sales Return Voucher (#{{ selectedReturn.return_no }})
                </h5>
              </div>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" id="printableReturnSlip">
              <!-- Slip Header Info -->
              <div class="row g-3 mb-3 p-3 bg-light rounded border">
                <div class="col-md-6">
                  <div class="small text-muted">Customer Details (গ্রাহক):</div>
                  <h6 class="fw-bold text-dark mb-0">
                    {{ selectedReturn.client ? selectedReturn.client.name : 'Walk-in Customer' }}
                  </h6>
                  <small class="text-muted" v-if="selectedReturn.client && selectedReturn.client.mobile">
                    <i class="fas fa-phone-alt me-1"></i>{{ selectedReturn.client.mobile }}
                  </small>
                </div>
                <div class="col-md-6 text-md-end">
                  <div class="small text-muted">Original Invoice (মূল ইনভয়েস):</div>
                  <strong class="font-monospace text-primary">
                    {{ selectedReturn.invoice ? selectedReturn.invoice.invoice_no : 'N/A' }}
                  </strong>
                  <small class="d-block text-muted">
                    Return Date: {{ selectedReturn.return_date }}
                  </small>
                </div>
              </div>

              <!-- Return Reason & Note Highlight Box -->
              <div class="mb-3 p-3 rounded border" :class="{
                'bg-success-subtle border-success': selectedReturn.return_reason === 'Client request',
                'bg-danger-subtle border-danger': selectedReturn.return_reason === 'Wastage',
                'bg-warning-subtle border-warning': selectedReturn.return_reason === 'Date Expaired',
              }">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="fw-bold small">
                    <i class="fas fa-tag me-1"></i> Return Reason: 
                    <span class="badge" :class="{
                      'bg-success': selectedReturn.return_reason === 'Client request',
                      'bg-danger': selectedReturn.return_reason === 'Wastage',
                      'bg-warning text-dark': selectedReturn.return_reason === 'Date Expaired'
                    }">
                      {{ selectedReturn.return_reason }}
                    </span>
                  </div>
                  <span class="small font-monospace text-muted">
                    Payment Method: <strong>{{ selectedReturn.payment_method }}</strong>
                    <span v-if="selectedReturn.trxid">({{ selectedReturn.trxid }})</span>
                  </span>
                </div>
                <div class="mt-2" v-if="selectedReturn.note">
                  <div class="small fw-bold text-muted mb-1"><i class="fas fa-comment-alt me-1"></i> Return Note / Remarks:</div>
                  <div class="p-2 bg-white rounded border fst-italic text-dark small">
                    "{{ selectedReturn.note }}"
                  </div>
                </div>
              </div>

              <!-- Itemized Table -->
              <div class="table-responsive border rounded mb-3">
                <table class="table table-bordered table-striped align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="5%">#</th>
                      <th>Item Description</th>
                      <th>Variant</th>
                      <th>Serial No</th>
                      <th width="12%" class="text-center">Returned Qty</th>
                      <th width="15%" class="text-end">Unit Rate</th>
                      <th width="18%" class="text-end">Refund Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(d, idx) in selectedReturn.details" :key="idx">
                      <td class="text-center font-monospace">{{ idx + 1 }}</td>
                      <td>
                        <div class="fw-bold text-dark">{{ d.item ? d.item.title : 'Item #' + d.item_id }}</div>
                        <small class="text-muted font-monospace">{{ d.item ? d.item.barcode : '' }}</small>
                      </td>
                      <td>
                        <span class="badge bg-info text-dark me-1" v-if="d.color">{{ d.color.title }}</span>
                        <span class="badge bg-secondary" v-if="d.size">{{ d.size.title }}</span>
                        <span v-if="!d.color && !d.size" class="text-muted small">Standard</span>
                      </td>
                      <td class="font-monospace small">{{ d.serial_no || '-' }}</td>
                      <td class="text-center font-monospace fw-bold">{{ d.qty }} {{ d.unit ? d.unit.title : 'pcs' }}</td>
                      <td class="text-end font-monospace">Tk. {{ formatMoney(d.rate) }}</td>
                      <td class="text-end font-monospace fw-bold text-danger">Tk. {{ formatMoney(d.refund_amount) }}</td>
                    </tr>
                  </tbody>
                  <tfoot class="table-light font-monospace fw-bold">
                    <tr>
                      <td colspan="4" class="text-end">Total Refunded:</td>
                      <td class="text-center">{{ selectedReturn.total_qty }} pcs</td>
                      <td></td>
                      <td class="text-end text-danger fs-6">Tk. {{ formatMoney(selectedReturn.total_refund_amount) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <div class="modal-footer bg-light py-2 px-3 justify-content-between">
              <small class="text-muted">
                Processed By: <strong>{{ selectedReturn.creator ? selectedReturn.creator.name : 'Admin' }}</strong>
              </small>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                  Close
                </button>
                <button type="button" class="btn btn-sm btn-dark d-flex align-items-center gap-1" @click="printModalSlip">
                  <i class="fas fa-print"></i> Print Slip
                </button>
              </div>
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

const model = "salesReturnReport";

export default {
  components: {
    downloadExcel: JsonExcel,
  },
  data() {
    return {
      model: model,
      page_title: "Sales Return Report (পণ্য ফেরত অডিট রিপোর্ট)",
      reportDate: moment().format("D MMMM, YYYY h:mm A"),
      activeTab: "returns",
      activePreset: "thisMonth", // 🌟 Default to current month as requested
      fields_name: {
        default: "All Fields (সকল ফিল্ড)",
        return_no: "Return Slip No (ফেরত চালান নং)",
        note: "Note / Remarks (মন্তব্য)",
      },
      search_data: {
        pagination: 10,
        page: 1,
        field_name: "default",
        value: "",
        from_date: moment().startOf("month").format("YYYY-MM-DD"),
        to_date: moment().endOf("month").format("YYYY-MM-DD"),
        client_id: null,
        return_reason: "all",
        payment_method: "all",
        category_id: null,
        item_id: null,
        keyword: "",
      },
      table: {
        columns: [],
        routes: {},
        datas: [],
        meta: { total: 0 },
        links: [],
      },
      summary: {
        total_returns: 0,
        total_qty: 0,
        total_refund: 0,
        restocked_qty: 0,
        restocked_amount: 0,
        wastage_qty: 0,
        wastage_amount: 0,
        reasons: {
          "Client request": { count: 0, qty: 0, amount: 0 },
          "Wastage": { count: 0, qty: 0, amount: 0 },
          "Date Expaired": { count: 0, qty: 0, amount: 0 },
        },
      },
      returns: [],
      item_breakdown: [],
      customer_breakdown: [],
      daily_breakdown: [],
      clients: [],
      categories: [],
      items: [],
      selectedReturn: null,
    };
  },

  provide() {
    return {
      validate: this.validation,
      model: this.model,
      fields_name: this.fields_name,
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
      } else if (this.search_data.from_date) {
        return "From " + moment(this.search_data.from_date).format("D MMM, YYYY");
      } else if (this.search_data.to_date) {
        return "Until " + moment(this.search_data.to_date).format("D MMM, YYYY");
      }
      return "All Time";
    },

    exportFileName() {
      return `Sales_Return_Report_${this.activeTab}_${moment().format("YYYY-MM-DD")}.xls`;
    },

    exportData() {
      if (this.activeTab === "returns") {
        return this.returns.map((ret, idx) => ({
          "SL": idx + 1,
          "Return Slip No": ret.return_no,
          "Return Date": ret.return_date,
          "Original Invoice No": ret.invoice ? ret.invoice.invoice_no : "N/A",
          "Customer Name": ret.client ? ret.client.name : "Walk-in Customer",
          "Customer Mobile": ret.client ? ret.client.mobile : "N/A",
          "Return Reason": ret.return_reason,
          "Total Qty": ret.total_qty,
          "Refund Amount (Tk)": ret.total_refund_amount,
          "Payment Method": ret.payment_method,
          "TrxID": ret.trxid || "",
          "Return Note": ret.note || "",
          "Handled By": ret.creator ? ret.creator.name : "",
        }));
      } else if (this.activeTab === "items") {
        return this.item_breakdown.map((item, idx) => ({
          "SL": idx + 1,
          "Item Name": item.item_title,
          "Barcode": item.barcode,
          "Category": item.category,
          "Color": item.color || "-",
          "Size": item.size || "-",
          "Total Returned Qty": item.total_qty,
          "Restocked Qty": item.restocked_qty,
          "Wastage Qty": item.wastage_qty + item.expired_qty,
          "Avg Refund Rate": item.avg_rate,
          "Total Refund Value": item.total_refund,
          "Return Reasons": item.reasons_text || "-",
        }));
      } else if (this.activeTab === "customers") {
        return this.customer_breakdown.map((c, idx) => ({
          "SL": idx + 1,
          "Customer ID": c.clientid,
          "Customer Name": c.name,
          "Mobile": c.mobile,
          "Return Slips Count": c.returns_count,
          "Total Returned Qty": c.total_qty,
          "Total Refund Amount": c.refund_amount,
        }));
      } else if (this.activeTab === "daily") {
        return this.daily_breakdown.map((d, idx) => ({
          "SL": idx + 1,
          "Date": d.date,
          "Return Slips Count": d.returns_count,
          "Total Qty Returned": d.total_qty,
          "Refund Amount": d.refund_amount,
        }));
      }
      return [];
    },
  },

  methods: {
    search() {
      this.getSalesReturnReport();
    },

    resetSearchData() {
      this.activePreset = "thisMonth";
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "default";
      this.search_data.value = "";
      this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
      this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
      this.search_data.client_id = null;
      this.search_data.return_reason = "all";
      this.search_data.payment_method = "all";
      this.search_data.category_id = null;
      this.search_data.item_id = null;
      this.search_data.keyword = "";
      this.getSalesReturnReport();
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
        case "all":
          this.search_data.from_date = "";
          this.search_data.to_date = "";
          break;
      }
      this.getSalesReturnReport();
    },

    getSalesReturnReport() {
      this.$root.spinner = true;
      this.reportDate = moment().format("D MMMM, YYYY h:mm A");

      axios
        .get("report/sales-return", { params: this.search_data })
        .then((res) => {
          if (res && res.data) {
            const data = res.data;
            this.returns = data.returns || [];
            this.summary = data.summary || {
              total_returns: 0,
              total_qty: 0,
              total_refund: 0,
              restocked_qty: 0,
              restocked_amount: 0,
              wastage_qty: 0,
              wastage_amount: 0,
              reasons: {
                "Client request": { count: 0, qty: 0, amount: 0 },
                "Wastage": { count: 0, qty: 0, amount: 0 },
                "Date Expaired": { count: 0, qty: 0, amount: 0 },
              },
            };
            this.item_breakdown = data.item_breakdown || [];
            this.customer_breakdown = data.customer_breakdown || [];
            this.daily_breakdown = data.daily_breakdown || [];

            this.table.datas = this.returns;
            this.table.meta = { total: this.returns.length };
          }
        })
        .catch((err) => {
          console.error("Error fetching sales return report:", err);
          if (this.$toast) {
            this.$toast("Failed to load sales return report", "error");
          }
        })
        .finally(() => {
          this.$root.spinner = false;
          this.$root.tableSpinner = false;
        });
    },

    loadFilterDropdowns() {
      // 1. Clients
      axios.get("client?allData=true").then((res) => {
        this.clients = res.data || [];
      });

      // 2. Categories
      axios.get("getcategories/Item").then((res) => {
        this.categories = res.data || [];
      });

      // 3. Items
      axios.get("item?allData=true").then((res) => {
        this.items = res.data || [];
      });
    },

    openReturnModal(ret) {
      this.selectedReturn = ret;
      const modalEl = document.getElementById("returnSlipModal");
      if (modalEl && window.bootstrap) {
        const modal = new window.bootstrap.Modal(modalEl);
        modal.show();
      }
    },

    printModalSlip() {
      window.print();
    },

    triggerPrint() {
      window.print();
    },

    formatMoney(val) {
      if (!val || isNaN(val)) return "0.00";
      return parseFloat(val).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
  },

  mounted() {
    this.getSalesReturnReport();
    this.loadFilterDropdowns();
  },
};
</script>

<style scoped>
.custom-report-tabs .nav-link {
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #495057;
  padding: 6px 14px;
  border: 1px solid #dee2e6;
  background-color: #fff;
  transition: all 0.2s ease;
}

.custom-report-tabs .nav-link:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.custom-report-tabs .nav-link.active {
  background-color: #112C47;
  color: #fff;
  border-color: #112C47;
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}

.kpi-metric-card {
  border-radius: 8px;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.kpi-metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.kpi-icon-badge {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.return-note-box {
  border-left: 3px solid #ffc107 !important;
  background-color: #fffdf5 !important;
}

.btn-xs {
  padding: 2px 8px;
  font-size: 11px;
  border-radius: 4px;
}

.cursor-pointer {
  cursor: pointer;
}

@media print {
  .d-print-none,
  #list_page_wrapper .page_header_card,
  .custom-report-tabs,
  .btn,
  .search-block {
    display: none !important;
  }

  body {
    background-color: #fff !important;
    font-size: 12px !important;
  }

  .card {
    border: none !important;
    box-shadow: none !important;
  }

  .table {
    border: 1px solid #000 !important;
  }

  .table-dark {
    background-color: #f0f0f0 !important;
    color: #000 !important;
  }

  .badge {
    border: 1px solid #666 !important;
    color: #000 !important;
    background: transparent !important;
  }
}
</style>
