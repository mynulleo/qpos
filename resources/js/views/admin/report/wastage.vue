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

      <!-- Auditor / Employee Filter -->
      <v-select-container title="Audited By / Auditor (অডিটর)" field="search_data.auditor_id" col="3">
        <v-select
          v-model="search_data.auditor_id"
          label="full_name"
          :reduce="obj => obj.id"
          :options="auditors"
          placeholder="-- All Auditors --"
          :closeOnSelect="true"
        >
          <template #option="option">
            <div>
              <strong>{{ option.full_name }}</strong>
              <small class="text-muted d-block" v-if="option.mobile">{{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Wastage / Disposal Reason Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">
            <i class="fas fa-tag me-1 text-danger"></i> Disposal Reason (ক্ষতির কারণ)
          </label>
          <select class="form-select form-select-sm" v-model="search_data.reason">
            <option value="all">-- All Reasons (সকল কারণ) --</option>
            <option value="Damaged / Broken">Damaged / Broken (ভাঙা/নষ্ট)</option>
            <option value="Date Expaired">Date Expaired (মেয়াদোত্তীর্ণ)</option>
            <option value="Transit Damage / Defective">Transit Damage (পরিবহনে ক্ষতি)</option>
            <option value="Quality Failure">Quality Failure (মান নষ্ট)</option>
            <option value="POS Return">POS Return (ফেরত ক্ষতি)</option>
            <option value="Expired Stock">Expired Stock (মেয়াদোত্তীর্ণ স্টক)</option>
            <option value="Other">Other / Miscellaneous</option>
          </select>
        </div>
      </div>

      <!-- Audit Approval Status Filter -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">
            <i class="fas fa-check-circle me-1 text-success"></i> Audit Status (অডিট অবস্থা)
          </label>
          <select class="form-select form-select-sm" v-model="search_data.status">
            <option value="all">-- All Statuses (সকল স্ট্যাটাস) --</option>
            <option value="approved">Approved (অনুমোদিত রাইট-অফ)</option>
            <option value="pending">Pending (অপেক্ষমান অডিট)</option>
            <option value="rejected">Rejected (বাতিলকৃত)</option>
          </select>
        </div>
      </div>

      <!-- Keyword Search -->
      <Input
        v-model="search_data.keyword"
        field="search_data.keyword"
        title="Keyword (অডিট নং / নোট / বারকোড / সিরিয়াল)"
        placeholder="e.g. WST-..., Defect note..."
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
                :class="{ 'active': activeTab === 'audits' }"
                @click="activeTab = 'audits'"
              >
                <i class="fas fa-clipboard-list me-1"></i> Audit Slips View (অডিট চালান সমূহ) ({{ wastages.length }})
              </button>
            </li>
            <li class="nav-item">
              <button
                class="nav-link"
                :class="{ 'active': activeTab === 'items' }"
                @click="activeTab = 'items'"
              >
                <i class="fas fa-boxes me-1"></i> Item Breakdown (পণ্য ভিত্তিক ক্ষতি) ({{ item_breakdown.length }})
              </button>
            </li>
            <li class="nav-item">
              <button
                class="nav-link"
                :class="{ 'active': activeTab === 'categories' }"
                @click="activeTab = 'categories'"
              >
                <i class="fas fa-chart-pie me-1"></i> Category Share (ক্যাটাগরি ভিত্তিক) ({{ category_breakdown.length }})
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
            <router-link to="/wastage/create" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 shadow-sm">
              <i class="fas fa-plus-circle"></i> New Wastage Audit (নতুন এন্ট্রি)
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
          <!-- Card 1: Total Audit Records -->
          <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-bold">Total Audits (অডিট চালান)</span>
                <div class="kpi-icon-badge bg-primary-subtle text-primary">
                  <i class="fas fa-clipboard-check"></i>
                </div>
              </div>
              <h4 class="mb-0 fw-bold font-monospace text-primary">{{ summary.total_audits || 0 }}</h4>
              <small class="text-muted" style="font-size: 11px;">মোট অডিট রেকর্ড সংখ্যা</small>
            </div>
          </div>

          <!-- Card 2: Total Financial Loss -->
          <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100 border-start border-4 border-danger">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-bold">Total Loss (মোট আর্থিক ক্ষতি)</span>
                <div class="kpi-icon-badge bg-danger-subtle text-danger">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="mb-0 fw-bold font-monospace text-danger">Tk. {{ formatMoney(summary.total_loss) }}</h4>
              <small class="text-muted" style="font-size: 11px;">নষ্ট/মেয়াদোত্তীর্ণ পণ্যের মোট ক্ষতি</small>
            </div>
          </div>

          <!-- Card 3: Total Quantity Disposed -->
          <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-bold">Disposed Qty (মোট পিস)</span>
                <div class="kpi-icon-badge bg-warning-subtle text-warning-emphasis">
                  <i class="fas fa-trash-alt"></i>
                </div>
              </div>
              <h4 class="mb-0 fw-bold font-monospace text-dark">{{ summary.total_qty || 0 }} pcs</h4>
              <small class="text-muted" style="font-size: 11px;">মোট বাতিলকৃত পণ্যের পরিমাণ</small>
            </div>
          </div>

          <!-- Card 4: Approved Write-offs -->
          <div class="col-xl-2 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100 border-start border-3 border-success">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-success small fw-bold"><i class="fas fa-check-double me-1"></i> Approved (অনুমোদিত)</span>
                <span class="badge bg-success font-monospace">{{ summary.approved_qty || 0 }} pcs</span>
              </div>
              <h5 class="mb-0 fw-bold font-monospace text-success">Tk. {{ formatMoney(summary.approved_loss) }}</h5>
              <small class="text-muted" style="font-size: 11px;">অডিট যাচাইকৃত অনুমোদিত ক্ষতি</small>
            </div>
          </div>

          <!-- Card 5: Pending / Draft Audits -->
          <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm kpi-metric-card bg-white p-3 h-100 border-start border-3 border-secondary">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-secondary small fw-bold"><i class="fas fa-hourglass-half me-1"></i> Pending (অপেক্ষমান)</span>
                <span class="badge bg-secondary font-monospace">{{ summary.pending_qty || 0 }} pcs</span>
              </div>
              <h5 class="mb-0 fw-bold font-monospace text-secondary">Tk. {{ formatMoney(summary.pending_loss) }}</h5>
              <small class="text-muted" style="font-size: 11px;">অনুমোদনের অপেক্ষায় থাকা চালান</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 🏷️ Wastage Reason Breakdown Summary Banner -->
      <div class="col-md-12 mb-3" v-if="summary.reasons && summary.reasons.length > 0">
        <div class="card border-0 shadow-sm bg-light p-2">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="small fw-bold text-dark">
                <i class="fas fa-chart-pie me-1 text-danger"></i> Loss Reasons Breakdown (ক্ষতির কারণসমূহ):
              </span>
              <span
                v-for="(r, rIdx) in summary.reasons"
                :key="rIdx"
                class="badge px-2 py-1 shadow-sm"
                :class="getReasonBadgeClass(r.reason)"
              >
                {{ r.reason }}: {{ r.count }} slips ({{ r.qty }} pcs - Tk. {{ formatMoney(r.amount) }})
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
        <!-- 📌 VIEW 1: Audit Slips / Master View (Detailed View) -->
        <div v-show="activeTab === 'audits'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0 custom-wastage-table">
              <thead class="table-dark text-center">
                <tr>
                  <th width="3%">#</th>
                  <th width="14%">Audit No & Date</th>
                  <th width="12%">Branch (শাখা)</th>
                  <th width="16%">Audited By (অডিটর)</th>
                  <th width="20%">Items & Reasons (পণ্য ও কারণ)</th>
                  <th width="8%" class="text-center">Total Qty</th>
                  <th width="11%" class="text-end">Total Loss</th>
                  <th width="8%" class="text-center">Status</th>
                  <th width="16%">Note / Remarks (মন্তব্য)</th>
                  <th width="8%" class="d-print-none text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="wastages.length > 0">
                  <tr v-for="(wst, index) in wastages" :key="wst.id">
                    <td class="text-center font-monospace small">{{ index + 1 }}</td>

                    <!-- Audit No & Date -->
                    <td>
                      <div class="fw-bold font-monospace text-primary">{{ wst.audit_number }}</div>
                      <small class="text-muted d-block font-monospace">
                        <i class="far fa-calendar-alt me-1"></i>{{ wst.audit_date }}
                      </small>
                    </td>

                    <!-- Branch -->
                    <td>
                      <span class="badge bg-light text-dark border">
                        <i class="fas fa-store-alt me-1 text-muted"></i>{{ wst.branch ? wst.branch.branch_name : 'Main Branch' }}
                      </span>
                    </td>

                    <!-- Audited By -->
                    <td>
                      <div class="fw-bold text-dark">
                        {{ wst.auditor ? wst.auditor.full_name : (wst.audited_by || 'Inventory Audit Team') }}
                      </div>
                      <small class="text-muted d-block" v-if="wst.auditor && wst.auditor.empid">
                        ID: {{ wst.auditor.empid }}
                      </small>
                      <small class="text-success d-block" v-if="wst.approved_admin" style="font-size: 10px;">
                        <i class="fas fa-check-circle me-1"></i>Approved by: {{ wst.approved_admin.full_name }}
                      </small>
                    </td>

                    <!-- Items & Reason Badges -->
                    <td>
                      <div v-if="wst.wastage_details && wst.wastage_details.length > 0">
                        <div v-for="(d, dIdx) in wst.wastage_details" :key="dIdx" class="mb-1 pb-1 border-bottom border-light">
                          <div class="d-flex justify-content-between align-items-start gap-1">
                            <span class="fw-semibold text-dark small" style="line-height: 1.2;">
                              {{ d.item ? d.item.title : 'Item #' + d.item_id }}
                            </span>
                            <span class="badge bg-dark font-monospace text-nowrap">{{ d.quantity }} {{ d.unit ? d.unit.title : 'pcs' }}</span>
                          </div>
                          <div class="d-flex gap-1 flex-wrap mt-1">
                            <span class="badge" :class="getReasonBadgeClass(d.reason)" style="font-size: 10px;">
                              {{ d.reason || 'Damage' }}
                            </span>
                            <span class="badge bg-light text-muted border font-monospace" v-if="d.serial_no" style="font-size: 10px;">
                              SN: {{ d.serial_no }}
                            </span>
                          </div>
                        </div>
                      </div>
                      <span v-else class="text-muted small">Total: {{ wst.total_qty }} pcs</span>
                    </td>

                    <!-- Total Qty -->
                    <td class="text-center font-monospace fw-bold">
                      {{ wst.total_qty }} pcs
                    </td>

                    <!-- Total Loss Amount -->
                    <td class="text-end font-monospace">
                      <span class="fw-bold text-danger fs-6">Tk. {{ formatMoney(wst.total_loss_amount) }}</span>
                    </td>

                    <!-- Status -->
                    <td class="text-center">
                      <span
                        class="badge px-2 py-1"
                        :class="{
                          'bg-success': wst.status === 'approved',
                          'bg-warning text-dark': wst.status === 'pending',
                          'bg-danger': wst.status === 'rejected'
                        }"
                      >
                        {{ wst.status ? wst.status.toUpperCase() : 'PENDING' }}
                      </span>
                    </td>

                    <!-- Note / Remarks -->
                    <td>
                      <div class="small text-muted p-1 bg-light rounded" v-if="wst.note" style="max-width: 250px; line-height: 1.3;">
                        <i class="fas fa-comment-alt text-secondary me-1"></i>{{ wst.note }}
                      </div>
                      <span v-else class="text-muted small fst-italic">-</span>
                    </td>

                    <!-- Action -->
                    <td class="d-print-none text-center">
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary shadow-sm d-inline-flex align-items-center gap-1"
                        @click="openWastageModal(wst)"
                        title="View Full Audit Voucher Slip"
                      >
                        <i class="fas fa-eye"></i> View Slip
                      </button>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                      <i class="fas fa-trash-alt fa-3x text-muted mb-2 d-block opacity-50"></i>
                      <h6>কোনো ওয়েস্টেজ বা ডিসপোজাল রেকর্ড পাওয়া যায়নি (No wastage records found)</h6>
                      <small>তারিখ পরিবর্তন করে অথবা ফিল্টার রিসেট করে পুনরায় চেষ্টা করুন।</small>
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="wastages.length > 0">
                <tr>
                  <td colspan="5" class="text-end">Grand Total (সর্বমোট ক্ষতি):</td>
                  <td class="text-center text-warning">{{ summary.total_qty }} pcs</td>
                  <td class="text-end text-danger fs-6">Tk. {{ formatMoney(summary.total_loss) }}</td>
                  <td colspan="3"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 2: Item-wise Loss Analysis Table -->
        <div v-show="activeTab === 'items'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="4%">#</th>
                  <th width="24%">Item Title (পণ্যের নাম)</th>
                  <th width="12%">Barcode</th>
                  <th width="14%">Category</th>
                  <th width="10%">Variant</th>
                  <th width="8%" class="text-center">Audits Count</th>
                  <th width="8%" class="text-center">Disposed Qty</th>
                  <th width="10%" class="text-end">Avg Loss Rate</th>
                  <th width="10%" class="text-end">Total Loss (Tk)</th>
                  <th width="16%">Loss Reasons</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="item_breakdown.length > 0">
                  <tr v-for="(item, idx) in item_breakdown" :key="idx">
                    <td class="text-center font-monospace">{{ idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ item.item_title }}</div>
                    </td>
                    <td class="font-monospace small text-muted">{{ item.barcode }}</td>
                    <td>
                      <span class="badge bg-light text-dark border">{{ item.category }}</span>
                    </td>
                    <td>
                      <span class="badge bg-info text-dark me-1" v-if="item.color">{{ item.color }}</span>
                      <span class="badge bg-secondary" v-if="item.size">{{ item.size }}</span>
                      <span v-if="!item.color && !item.size" class="text-muted small">Standard</span>
                    </td>
                    <td class="text-center font-monospace">{{ item.audits_count }}</td>
                    <td class="text-center font-monospace fw-bold text-warning-emphasis">
                      {{ item.total_qty }} {{ item.unit }}
                    </td>
                    <td class="text-end font-monospace">Tk. {{ formatMoney(item.avg_rate) }}</td>
                    <td class="text-end font-monospace fw-bold text-danger">Tk. {{ formatMoney(item.total_loss) }}</td>
                    <td>
                      <span class="small text-muted">{{ item.reasons_text || '-' }}</span>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="10" class="text-center py-4 text-muted">
                      কোনো পণ্যভিত্তিক ক্ষতির ডাটা পাওয়া যায়নি।
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="item_breakdown.length > 0">
                <tr>
                  <td colspan="6" class="text-end">Total Loss Value (সর্বমোট ক্ষতি):</td>
                  <td class="text-center text-warning">{{ summary.total_qty }} pcs</td>
                  <td></td>
                  <td class="text-end text-danger fs-6">Tk. {{ formatMoney(summary.total_loss) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 3: Category Loss Share Table -->
        <div v-show="activeTab === 'categories'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="5%">#</th>
                  <th width="30%">Category Name (ক্যাটাগরি)</th>
                  <th width="12%" class="text-center">Audits Count</th>
                  <th width="15%" class="text-center">Total Disposed Qty</th>
                  <th width="18%" class="text-end">Total Loss Value (Tk)</th>
                  <th width="20%">Loss Share (% শতকরা হার)</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="category_breakdown.length > 0">
                  <tr v-for="(cat, idx) in category_breakdown" :key="idx">
                    <td class="text-center font-monospace">{{ idx + 1 }}</td>
                    <td>
                      <strong class="text-dark">{{ cat.category_title }}</strong>
                    </td>
                    <td class="text-center font-monospace">{{ cat.audits_count }}</td>
                    <td class="text-center font-monospace fw-bold">{{ cat.total_qty }} pcs</td>
                    <td class="text-end font-monospace fw-bold text-danger">Tk. {{ formatMoney(cat.total_loss) }}</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                          <div
                            class="progress-bar bg-danger"
                            role="progressbar"
                            :style="{ width: cat.loss_percentage + '%' }"
                            :aria-valuenow="cat.loss_percentage"
                            aria-valuemin="0"
                            aria-valuemax="100"
                          ></div>
                        </div>
                        <span class="small fw-bold font-monospace text-dark" style="min-width: 45px;">{{ cat.loss_percentage }}%</span>
                      </div>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                      কোনো ক্যাটাগরিভিত্তিক ক্ষতির ডাটা পাওয়া যায়নি।
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="category_breakdown.length > 0">
                <tr>
                  <td colspan="3" class="text-end">Grand Total:</td>
                  <td class="text-center text-warning">{{ summary.total_qty }} pcs</td>
                  <td class="text-end text-danger fs-6">Tk. {{ formatMoney(summary.total_loss) }}</td>
                  <td>100.0%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 📌 VIEW 4: Daily Loss Trend -->
        <div v-show="activeTab === 'daily'">
          <div class="table-responsive bg-white rounded shadow-sm border">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center">
                <tr>
                  <th width="5%">#</th>
                  <th width="30%">Audit Date (তারিখ)</th>
                  <th width="20%" class="text-center">Audit Slips Count</th>
                  <th width="20%" class="text-center">Disposed Quantity</th>
                  <th width="25%" class="text-end">Total Loss Value (Tk)</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="daily_breakdown.length > 0">
                  <tr v-for="(d, idx) in daily_breakdown" :key="idx">
                    <td class="text-center font-monospace">{{ idx + 1 }}</td>
                    <td class="font-monospace fw-bold text-primary">
                      <i class="far fa-calendar-alt me-1 text-muted"></i>{{ d.formatted_date || d.date }}
                    </td>
                    <td class="text-center font-monospace">{{ d.audits_count }}</td>
                    <td class="text-center font-monospace fw-bold">{{ d.total_qty }} pcs</td>
                    <td class="text-end font-monospace fw-bold text-danger">Tk. {{ formatMoney(d.loss_amount) }}</td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                      কোনো দৈনিক ক্ষতির ডাটা পাওয়া যায়নি।
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-dark font-monospace fw-bold" v-if="daily_breakdown.length > 0">
                <tr>
                  <td colspan="2" class="text-end">Total Period Loss:</td>
                  <td class="text-center">{{ summary.total_audits }} slips</td>
                  <td class="text-center text-warning">{{ summary.total_qty }} pcs</td>
                  <td class="text-end text-danger fs-6">Tk. {{ formatMoney(summary.total_loss) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 🖨️ Wastage / Disposal Audit Certificate Voucher Modal -->
      <div class="modal fade" id="wastageSlipModal" tabindex="-1" aria-labelledby="wastageSlipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg" v-if="selectedWastage">
            <div class="modal-header text-white py-3 px-4" style="background-color: #112C47;">
              <div class="d-flex align-items-center gap-2">
                <i class="fas fa-trash-alt fs-5 text-danger"></i>
                <h5 class="modal-title fw-bold mb-0" id="wastageSlipModalLabel">
                  Wastage & Disposal Audit Voucher (#{{ selectedWastage.audit_number }})
                </h5>
              </div>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" id="printableWastageSlip">
              <!-- Header Info -->
              <div class="row g-3 mb-3 p-3 bg-light rounded border">
                <div class="col-md-6">
                  <div class="small text-muted">Audit Team / Auditor:</div>
                  <h6 class="fw-bold text-dark mb-0">
                    {{ selectedWastage.auditor ? selectedWastage.auditor.full_name : (selectedWastage.audited_by || 'Inventory Audit Team') }}
                  </h6>
                  <small class="text-muted" v-if="selectedWastage.auditor && selectedWastage.auditor.mobile">
                    <i class="fas fa-phone-alt me-1"></i>{{ selectedWastage.auditor.mobile }}
                  </small>
                </div>
                <div class="col-md-6 text-md-end">
                  <div class="small text-muted">Branch & Date:</div>
                  <strong class="text-dark d-block">
                    {{ selectedWastage.branch ? selectedWastage.branch.branch_name : 'Main Store' }}
                  </strong>
                  <small class="text-muted font-monospace">
                    Audit Date: {{ selectedWastage.audit_date }}
                  </small>
                </div>
              </div>

              <!-- Status & Audit Note Box -->
              <div class="mb-3 p-3 rounded border bg-danger-subtle border-danger">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="fw-bold small">
                    <i class="fas fa-shield-alt me-1 text-danger"></i> Audit Status:
                    <span
                      class="badge"
                      :class="{
                        'bg-success': selectedWastage.status === 'approved',
                        'bg-warning text-dark': selectedWastage.status === 'pending',
                        'bg-danger': selectedWastage.status === 'rejected'
                      }"
                    >
                      {{ selectedWastage.status ? selectedWastage.status.toUpperCase() : 'PENDING' }}
                    </span>
                  </div>
                  <span class="small font-monospace text-muted" v-if="selectedWastage.approved_admin">
                    Approved By: <strong>{{ selectedWastage.approved_admin.full_name }}</strong>
                  </span>
                </div>
                <div class="mt-2" v-if="selectedWastage.note">
                  <div class="small fw-bold text-muted mb-1"><i class="fas fa-comment-alt me-1"></i> Audit Remarks / Note:</div>
                  <div class="p-2 bg-white rounded border fst-italic text-dark small">
                    "{{ selectedWastage.note }}"
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
                      <th>Reason / Cause</th>
                      <th>Serial No / Remarks</th>
                      <th width="10%" class="text-center">Qty</th>
                      <th width="14%" class="text-end">Unit Cost</th>
                      <th width="16%" class="text-end">Loss Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(d, idx) in selectedWastage.wastage_details" :key="idx">
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
                      <td>
                        <span class="badge" :class="getReasonBadgeClass(d.reason)">
                          {{ d.reason || 'Damaged' }}
                        </span>
                      </td>
                      <td class="font-monospace small">
                        <div v-if="d.serial_no">SN: {{ d.serial_no }}</div>
                        <div class="text-muted" v-if="d.remarks">{{ d.remarks }}</div>
                        <div v-if="!d.serial_no && !d.remarks">-</div>
                      </td>
                      <td class="text-center font-monospace fw-bold">{{ d.quantity }} {{ d.unit ? d.unit.title : 'pcs' }}</td>
                      <td class="text-end font-monospace">Tk. {{ formatMoney(d.unit_price) }}</td>
                      <td class="text-end font-monospace fw-bold text-danger">Tk. {{ formatMoney(d.total_amount) }}</td>
                    </tr>
                  </tbody>
                  <tfoot class="table-light font-monospace fw-bold">
                    <tr>
                      <td colspan="5" class="text-end">Total Financial Write-off:</td>
                      <td class="text-center">{{ selectedWastage.total_qty }} pcs</td>
                      <td></td>
                      <td class="text-end text-danger fs-6">Tk. {{ formatMoney(selectedWastage.total_loss_amount) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Sign-off Blocks for Print -->
              <div class="row pt-4 mt-2 border-top text-center text-muted small">
                <div class="col-4">
                  <div class="border-top pt-1 mx-2"><strong>Auditor Signature</strong></div>
                </div>
                <div class="col-4">
                  <div class="border-top pt-1 mx-2"><strong>Store Manager</strong></div>
                </div>
                <div class="col-4">
                  <div class="border-top pt-1 mx-2"><strong>Authorized Approval</strong></div>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-2 px-3 justify-content-between">
              <small class="text-muted">
                Created By: <strong>{{ selectedWastage.creator ? selectedWastage.creator.full_name : 'Admin' }}</strong>
              </small>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                  Close
                </button>
                <button type="button" class="btn btn-sm btn-dark d-flex align-items-center gap-1" @click="printModalSlip">
                  <i class="fas fa-print"></i> Print Voucher
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

const model = "wastageReport";

export default {
  components: {
    downloadExcel: JsonExcel,
  },
  data() {
    return {
      model: model,
      page_title: "Wastage & Disposal Audit Report (ক্ষতি ও নষ্ট পণ্য অডিট রিপোর্ট)",
      reportDate: moment().format("D MMMM, YYYY h:mm A"),
      activeTab: "audits",
      activePreset: "thisMonth",
      fields_name: {
        default: "All Fields (সকল ফিল্ড)",
        audit_number: "Audit Slip No (অডিট চালান নং)",
        note: "Note / Remarks (মন্তব্য)",
      },
      search_data: {
        pagination: 10,
        page: 1,
        field_name: "default",
        value: "",
        from_date: moment().startOf("month").format("YYYY-MM-DD"),
        to_date: moment().endOf("month").format("YYYY-MM-DD"),
        category_id: null,
        item_id: null,
        auditor_id: null,
        reason: "all",
        status: "all",
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
        total_audits: 0,
        total_qty: 0,
        total_loss: 0,
        approved_qty: 0,
        approved_loss: 0,
        pending_qty: 0,
        pending_loss: 0,
        reasons: [],
        status_summary: {},
      },
      wastages: [],
      item_breakdown: [],
      category_breakdown: [],
      daily_breakdown: [],
      categories: [],
      items: [],
      auditors: [],
      selectedWastage: null,
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
      return `Wastage_Disposal_Report_${this.activeTab}_${moment().format("YYYY-MM-DD")}.xls`;
    },

    exportData() {
      if (this.activeTab === "audits") {
        return this.wastages.map((wst, idx) => ({
          "SL": idx + 1,
          "Audit Number": wst.audit_number,
          "Audit Date": wst.audit_date,
          "Branch": wst.branch ? wst.branch.branch_name : "Main Store",
          "Audited By": wst.auditor ? wst.auditor.full_name : (wst.audited_by || "Audit Team"),
          "Total Items (Qty)": wst.total_qty,
          "Total Loss Amount (Tk)": wst.total_loss_amount,
          "Status": wst.status ? wst.status.toUpperCase() : "PENDING",
          "Approved By": wst.approved_admin ? wst.approved_admin.full_name : "",
          "Audit Remarks": wst.note || "",
        }));
      } else if (this.activeTab === "items") {
        return this.item_breakdown.map((item, idx) => ({
          "SL": idx + 1,
          "Item Name": item.item_title,
          "Barcode": item.barcode,
          "Category": item.category,
          "Color": item.color || "-",
          "Size": item.size || "-",
          "Total Disposed Qty": item.total_qty,
          "Unit": item.unit,
          "Avg Loss Rate": item.avg_rate,
          "Total Loss Amount (Tk)": item.total_loss,
          "Loss Reasons": item.reasons_text || "-",
        }));
      } else if (this.activeTab === "categories") {
        return this.category_breakdown.map((cat, idx) => ({
          "SL": idx + 1,
          "Category Name": cat.category_title,
          "Audits Count": cat.audits_count,
          "Total Disposed Qty": cat.total_qty,
          "Total Loss Amount (Tk)": cat.total_loss,
          "Loss Share (%)": cat.loss_percentage + "%",
        }));
      } else if (this.activeTab === "daily") {
        return this.daily_breakdown.map((d, idx) => ({
          "SL": idx + 1,
          "Date": d.formatted_date || d.date,
          "Audit Slips Count": d.audits_count,
          "Total Qty Disposed": d.total_qty,
          "Total Loss Amount (Tk)": d.loss_amount,
        }));
      }
      return [];
    },
  },

  methods: {
    search() {
      this.getWastageReport();
    },

    resetSearchData() {
      this.activePreset = "thisMonth";
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "default";
      this.search_data.value = "";
      this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
      this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
      this.search_data.category_id = null;
      this.search_data.item_id = null;
      this.search_data.auditor_id = null;
      this.search_data.reason = "all";
      this.search_data.status = "all";
      this.search_data.keyword = "";
      this.getWastageReport();
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
      this.getWastageReport();
    },

    getWastageReport() {
      this.$root.spinner = true;
      this.reportDate = moment().format("D MMMM, YYYY h:mm A");

      axios
        .get("report/wastage", { params: this.search_data })
        .then((res) => {
          if (res && res.data) {
            const data = res.data;
            this.wastages = data.wastages || [];
            this.summary = data.summary || {
              total_audits: 0,
              total_qty: 0,
              total_loss: 0,
              approved_qty: 0,
              approved_loss: 0,
              pending_qty: 0,
              pending_loss: 0,
              reasons: [],
              status_summary: {},
            };
            this.item_breakdown = data.item_breakdown || [];
            this.category_breakdown = data.category_breakdown || [];
            this.daily_breakdown = data.daily_breakdown || [];

            this.table.datas = this.wastages;
            this.table.meta = { total: this.wastages.length };
          }
        })
        .catch((err) => {
          console.error("Error fetching wastage report:", err);
          if (this.$toast) {
            this.$toast("Failed to load wastage report", "error");
          }
        })
        .finally(() => {
          this.$root.spinner = false;
          this.$root.tableSpinner = false;
        });
    },

    loadFilterDropdowns() {
      // 1. Categories
      axios.get("getcategories/Item").then((res) => {
        this.categories = res.data || [];
      });

      // 2. Items
      axios.get("item?allData=true").then((res) => {
        this.items = res.data || [];
      });

      // 3. Auditors / Employees
      axios.get("employee?allData=true").then((res) => {
        this.auditors = res.data || [];
      });
    },

    getReasonBadgeClass(reason) {
      if (!reason) return "bg-secondary";
      const r = reason.toLowerCase();
      if (r.includes("expair") || r.includes("expire")) return "bg-warning text-dark";
      if (r.includes("transit") || r.includes("transport")) return "bg-info text-dark";
      if (r.includes("damage") || r.includes("broken")) return "bg-danger";
      if (r.includes("quality") || r.includes("defect")) return "bg-dark text-white";
      if (r.includes("return")) return "bg-primary";
      return "bg-danger-subtle text-danger border border-danger";
    },

    openWastageModal(wst) {
      this.selectedWastage = wst;
      const modalEl = document.getElementById("wastageSlipModal");
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
    this.getWastageReport();
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
