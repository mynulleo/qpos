<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" :showPrintButton="false" :button="false" :showEditRoute="false" printArea="adj_print_area">
    <!-- 💻 Interactive Web Dashboard View -->
    <div class="adj-view-wrapper" v-if="data && data.adjustment_no">
      <!-- 🌟 Top Hero / Adjustment Header Banner -->
      <div class="card border-0 shadow-sm mb-4 adj-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-sliders-h fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">{{ $t('Stock Adjustment Audit') }} #{{ data.adjustment_no }}</h4>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i :class="data.status === 'active' ? 'fas fa-check-circle me-1' : 'fas fa-archive me-1'"></i>
                    {{ data.status === 'active' ? $t('Reconciled & Active') : $t('Archived') }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white">
                    <i class="fas fa-tag me-1"></i> {{ data.adjustment_type || 'Reconciliation' }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>{{ $t('Audit Date') }}: <strong class="text-white">{{ data.adjustment_date }}</strong></span>
                  <span v-if="data.conducted_by"><i class="fas fa-user-check me-1"></i>{{ $t('Conducted By') }}: <strong class="text-white">{{ data.conducted_by.full_name || data.conducted_by.name }}</strong></span>
                  <span v-if="data.branch"><i class="fas fa-code-branch me-1"></i>{{ $t('Branch') }}: <strong class="text-white">{{ data.branch.title }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons (Print Voucher & Quick Back) -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="print('adj_print_area', 'Stock Adjustment - ' + data.adjustment_no)">
                <i class="fas fa-print me-1"></i> {{ $t('Print Voucher') }}
              </button>
              <router-link :to="{ name: model + '.index' }" class="btn btn-outline-light btn-sm fw-bold px-3">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Total Items -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Items') }}</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">
                {{ data.details ? data.details.length : (data.total_items || 0) }} {{ $t('Lines') }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Reconciled product lines') }}</small>
            </div>
          </div>
        </div>

        <!-- 2. Stock In Additions -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Added Stock') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-arrow-up"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-success font-monospace text-nowrap">+{{ totalQtyIn }} {{ $t('Units') }}</h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Inward inventory adjustments') }}</small>
            </div>
          </div>
        </div>

        <!-- 3. Stock Out Deductions -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Deducted Stock') }}</span>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-arrow-down"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-danger font-monospace text-nowrap">-{{ totalQtyOut }} {{ $t('Units') }}</h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Outward inventory adjustments') }}</small>
            </div>
          </div>
        </div>

        <!-- 4. Total Valuation Impact -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Financial Value') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-coins"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">৳ {{ formatNum(data.total_amount) }}</h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Net valuation of audited items') }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Two-Column Metadata Overview -->
      <div class="row g-3 mb-4">
        <!-- Left: Logistics & Custody -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-clipboard-list"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Adjustment Details & Logistics') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Reference, date, personnel and justification') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" width="38%"><i class="fas fa-hashtag me-2 text-muted"></i>{{ $t('Adjustment No') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.adjustment_no }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-calendar-alt me-2 text-muted"></i>{{ $t('Conducted Date') }}</td>
                    <td class="spec-value font-monospace">{{ data.adjustment_date }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-tag me-2 text-muted"></i>{{ $t('Adjustment Type') }}</td>
                    <td class="spec-value"><span class="badge bg-light text-dark border">{{ data.adjustment_type }}</span></td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-user-check me-2 text-muted"></i>{{ $t('Conducted By') }}</td>
                    <td class="spec-value fw-bold text-dark">{{ data.conducted_by ? (data.conducted_by.full_name || data.conducted_by.name) : $t('Store Manager') }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-info-circle me-2 text-muted"></i>{{ $t('Reason') }}</td>
                    <td class="spec-value">{{ data.reason || $t('Routine inventory physical reconciliation') }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-sticky-note me-2 text-muted"></i>{{ $t('Remarks / Note') }}</td>
                    <td class="spec-value fst-italic text-muted">{{ data.remarks || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Reconciliation Summary -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-scale-balanced"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Reconciliation Summary & Impact') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Inventory quantity additions, deductions and status') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" width="38%"><i class="fas fa-circle-check me-2 text-muted"></i>{{ $t('Status') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                        {{ data.status === 'active' ? $t('Active & Applied') : $t('Inactive') }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-arrow-trend-up me-2 text-success"></i>{{ $t('Inward Stock Addition') }}</td>
                    <td class="spec-value font-monospace fw-bold text-success">+{{ totalQtyIn }} {{ $t('Units') }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-arrow-trend-down me-2 text-danger"></i>{{ $t('Outward Stock Deduction') }}</td>
                    <td class="spec-value font-monospace fw-bold text-danger">-{{ totalQtyOut }} {{ $t('Units') }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-boxes-stacked me-2 text-muted"></i>{{ $t('Net Adjusted Quantity') }}</td>
                    <td class="spec-value font-monospace fw-bold">{{ data.total_adjusted_qty || 0 }} {{ $t('Units') }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-sack-dollar me-2 text-muted"></i>{{ $t('Total Financial Value') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark fs-6">৳ {{ formatNum(data.total_amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 📦 Adjusted Products Table -->
      <div class="card border-0 shadow-sm mb-4 table-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-boxes"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Adjusted Product Lines') }}</h6>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Physical count reconciliation and adjustments') }}</small>
            </div>
          </div>
          <span class="badge theme-bg text-white font-monospace">
            {{ data.details ? data.details.length : 0 }} {{ $t('Item(s) Reconciled') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-items-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width: 45px;">#</th>
                  <th>{{ $t('Product Details') }}</th>
                  <th style="width: 120px;" v-if="hasAnyVariants">{{ $t('Variant') }}</th>
                  <th class="text-center" style="width: 130px;" v-if="hasAnySerials">{{ $t('Serial Number') }}</th>
                  <th class="text-center" style="width: 110px;">{{ $t('Current Stock') }}</th>
                  <th class="text-center" style="width: 110px;">{{ $t('Physical Count') }}</th>
                  <th class="text-center" style="width: 120px;">{{ $t('Adjustment Qty') }}</th>
                  <th class="text-end" style="width: 110px;">{{ $t('Unit Cost') }}</th>
                  <th class="text-end" style="width: 130px;">{{ $t('Total Value') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detail, index) in data.details" :key="index">
                  <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>
                  <td>
                    <div class="fw-bold text-dark fs-6">{{ detail.item?.title || $t('Item #') + detail.item_id }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                      <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 11px;">
                        {{ detail.category?.title || detail.item?.category?.title || $t('General') }}
                      </span>
                      <small class="text-muted font-monospace" v-if="detail.item?.barcode" style="font-size: 11px;">
                        <i class="fas fa-barcode me-1"></i>{{ detail.item.barcode }}
                      </small>
                    </div>
                  </td>
                  <td v-if="hasAnyVariants">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                      <span class="badge bg-secondary" v-if="detail.color?.title">{{ detail.color.title }}</span>
                      <span class="badge bg-info text-dark" v-if="detail.size?.title">{{ detail.size.title }}</span>
                      <span class="text-muted small" v-if="!detail.color?.title && !detail.size?.title">-</span>
                    </div>
                  </td>
                  <td class="text-center" v-if="hasAnySerials">
                    <span v-if="detail.serial_no" class="badge bg-light text-primary border font-monospace">
                      {{ detail.serial_no }}
                    </span>
                    <span v-else class="text-muted small">-</span>
                  </td>
                  <td class="text-center font-monospace text-muted">{{ detail.current_stock }}</td>
                  <td class="text-center font-monospace fw-bold text-dark">{{ detail.physical_count }}</td>
                  <td class="text-center font-monospace fw-bold">
                    <span v-if="detail.type === 'addition'" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">
                      <i class="fas fa-plus me-1"></i>{{ detail.adjustment_qty }} {{ detail.unit?.title || 'Pcs' }}
                    </span>
                    <span v-else-if="detail.type === 'subtraction'" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-1 px-2">
                      <i class="fas fa-minus me-1"></i>{{ detail.adjustment_qty }} {{ detail.unit?.title || 'Pcs' }}
                    </span>
                    <span v-else class="badge bg-secondary py-1 px-2">
                      {{ detail.adjustment_qty }} {{ detail.unit?.title || 'Pcs' }}
                    </span>
                  </td>
                  <td class="text-end font-monospace text-muted">৳ {{ formatNum(detail.unit_price) }}</td>
                  <td class="text-end font-monospace fw-bold text-dark">৳ {{ formatNum(detail.total_price) }}</td>
                </tr>
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold fs-6">
                  <td :colspan="3 + (hasAnyVariants ? 1 : 0) + (hasAnySerials ? 1 : 0)" class="text-end text-uppercase">{{ $t('Total Financial Value:') }}</td>
                  <td></td>
                  <td></td>
                  <td class="text-center font-monospace">{{ data.total_adjusted_qty || 0 }}</td>
                  <td></td>
                  <td class="text-end font-monospace fs-5">৳ {{ formatNum(data.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ Official Printable Voucher (#adj_print_area) -->
    <div id="adj_print_area" class="d-none d-print-block print-voucher-page" v-if="data && data.adjustment_no">
      <div class="text-center pb-3 mb-3 border-bottom">
        <h3 class="fw-bold mb-0 text-uppercase">{{ $root.site?.name || 'QPOS Retail & Wholesale' }}</h3>
        <p class="small text-muted mb-1">{{ $root.site?.address || '' }}</p>
        <div class="d-inline-block border border-dark px-3 py-1 fw-bold text-uppercase mt-2" style="font-size: 13px; letter-spacing: 1.5px;">
          {{ $t('STOCK ADJUSTMENT VOUCHER') }}
        </div>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-meta-table">
            <tbody>
              <tr><th width="45%" class="bg-light">{{ $t('Adjustment No') }}:</th><td class="font-monospace fw-bold">{{ data.adjustment_no }}</td></tr>
              <tr><th class="bg-light">{{ $t('Date') }}:</th><td>{{ data.adjustment_date }}</td></tr>
              <tr><th class="bg-light">{{ $t('Adjustment Type') }}:</th><td>{{ data.adjustment_type }}</td></tr>
            </tbody>
          </table>
        </div>
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-meta-table">
            <tbody>
              <tr><th width="45%" class="bg-light">{{ $t('Conducted By') }}:</th><td>{{ data.conducted_by ? (data.conducted_by.full_name || data.conducted_by.name) : 'Store Manager' }}</td></tr>
              <tr><th class="bg-light">{{ $t('Status') }}:</th><td class="fw-bold">{{ data.status === 'active' ? 'ACTIVE & APPLIED' : 'INACTIVE' }}</td></tr>
              <tr><th class="bg-light">{{ $t('Total Items') }}:</th><td class="font-monospace">{{ data.details ? data.details.length : data.total_items }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <table class="table table-bordered align-middle print-items-table mb-3">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 35px;">#</th>
            <th class="text-start">{{ $t('Product Details & Barcode') }}</th>
            <th style="width: 100px;">{{ $t('Current') }}</th>
            <th style="width: 100px;">{{ $t('Physical') }}</th>
            <th style="width: 110px;">{{ $t('Adjustment Qty') }}</th>
            <th class="text-end" style="width: 100px;">{{ $t('Unit Rate') }}</th>
            <th class="text-end" style="width: 110px;">{{ $t('Total Value') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(detail, index) in data.details" :key="index">
            <td class="text-center font-monospace">{{ index + 1 }}</td>
            <td>
              <div class="fw-bold text-dark">{{ detail.item?.title || $t('Item #') + detail.item_id }}</div>
              <small class="text-muted d-block font-monospace" v-if="detail.item?.barcode">
                Barcode: {{ detail.item.barcode }}
              </small>
            </td>
            <td class="text-center font-monospace">{{ detail.current_stock }}</td>
            <td class="text-center font-monospace fw-bold">{{ detail.physical_count }}</td>
            <td class="text-center font-monospace fw-bold">
              {{ detail.type === 'addition' ? '+' : '-' }}{{ detail.adjustment_qty }} {{ detail.unit?.title || 'Pcs' }}
            </td>
            <td class="text-end font-monospace">৳ {{ formatNum(detail.unit_price) }}</td>
            <td class="text-end font-monospace fw-bold">৳ {{ formatNum(detail.total_price) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="fw-bold">
            <td colspan="4" class="text-end text-uppercase">{{ $t('Total Valuation:') }}</td>
            <td class="text-center font-monospace">{{ data.total_adjusted_qty || 0 }}</td>
            <td></td>
            <td class="text-end font-monospace">৳ {{ formatNum(data.total_amount) }}</td>
          </tr>
        </tfoot>
      </table>

      <div class="row pt-5 mt-4 text-center">
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ data.conducted_by ? (data.conducted_by.full_name || data.conducted_by.name) : $t('Conducted By') }}</strong>
            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $t('Audited / Prepared By') }}</small>
          </div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ $t('Store In-Charge') }}</strong>
            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $t('Verified & Checked By') }}</small>
          </div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ $t('Management') }}</strong>
            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $t('Authorized Approval & Seal') }}</small>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "stockAdjustment";

export default {
  name: "StockAdjustmentView",
  data() {
    return {
      page_title: "Stock Adjustment Details",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  computed: {
    hasAnyVariants() {
      return this.data.details?.some(
        (d) => d.color_id || d.size_id || d.color?.title || d.size?.title
      );
    },

    hasAnySerials() {
      return this.data.details?.some(
        (d) => Boolean(d.serial_no && String(d.serial_no).trim())
      );
    },

    totalQtyIn() {
      if (!this.data || !this.data.details) return 0;
      return this.data.details
        .filter((d) => d.type === "addition")
        .reduce((sum, d) => sum + parseFloat(d.adjustment_qty || 0), 0);
    },

    totalQtyOut() {
      if (!this.data || !this.data.details) return 0;
      return this.data.details
        .filter((d) => d.type === "subtraction")
        .reduce((sum, d) => sum + parseFloat(d.adjustment_qty || 0), 0);
    },
  },

  methods: {
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} Details`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.adj-view-wrapper {
  font-family: inherit;
}

.theme-bg {
  background-color: #112C47 !important;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 71, 0.08) !important;
}

.text-theme {
  color: #112C47 !important;
}

.adj-hero-banner {
  background: linear-gradient(135deg, #112C47 0%, #1e4976 100%);
  border-radius: 12px;
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  border-radius: 12px;
}

.stat-card {
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 40px;
  height: 40px;
  font-size: 16px;
}

.section-icon {
  width: 32px;
  height: 32px;
  font-size: 14px;
}

.info-card, .table-card {
  border-radius: 10px;
  overflow: hidden;
}

.custom-spec-table td {
  padding: 10px 16px;
  border-bottom: 1px solid #f1f5f9;
}

.custom-spec-table tr:last-child td {
  border-bottom: none;
}

.spec-label {
  color: #64748b;
  font-weight: 500;
  font-size: 13px;
}

.spec-value {
  color: #1e293b;
  font-size: 13px;
}

.custom-items-table th {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 12px 14px;
  color: #475569;
}

.custom-items-table td {
  padding: 12px 14px;
  font-size: 13px;
}

.print-voucher-page {
  font-family: inherit;
  color: #000;
  background: #fff;
  padding: 20px;
}

.print-meta-table th, .print-meta-table td {
  padding: 6px 10px;
  font-size: 12px;
}

.print-items-table th, .print-items-table td {
  padding: 6px 10px;
  font-size: 12px;
}
</style>
