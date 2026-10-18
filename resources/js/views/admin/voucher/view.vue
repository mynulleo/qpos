<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" :showPrintButton="false" :button="false" :showEditRoute="false" printArea="voucher_print_area">
    <div class="voucher-view-wrapper" v-if="data && data.voucherno">
      <!-- 🌟 Top Hero / Voucher Header Banner -->
      <div class="card border-0 shadow-sm mb-4 voucher-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-file-invoice-dollar fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">{{ $t('Voucher') }} #{{ data.voucherno }}</h4>
                  <span class="badge" :class="getVoucherTypeBadge(data.voucher_type)">
                    {{ data.voucher_type ? data.voucher_type.toUpperCase() : 'JOURNAL' }}
                  </span>
                  <span class="badge" :class="data.status === 'active' || data.status == 1 ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i :class="data.status === 'active' || data.status == 1 ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                    {{ data.status === 'active' || data.status == 1 ? $t('Posted & Active') : $t('Pending') }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="data.financial_year">
                    <i class="fas fa-calendar-alt me-1"></i> {{ data.financial_year.title }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>Date: <strong class="text-white">{{ data.voucher_date }}</strong></span>
                  <span v-if="data.payment"><i class="fas fa-receipt me-1"></i>Payslip: <strong class="text-white">{{ data.payment.payslipno }}</strong></span>
                  <span v-if="data.approved_by"><i class="fas fa-user-check me-1"></i>Approved By: <strong class="text-white">{{ data.approved_by }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons (Print & Back) -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="print('voucher_print_area', 'Accounting Voucher - ' + data.voucherno)">
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
        <!-- 1. Total Journal Entries -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Journal Entries') }}</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-list-ol"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">
                {{ data.voucher_details ? data.voucher_details.length : 0 }} {{ $t('Lines') }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Double-entry line items') }}</small>
            </div>
          </div>
        </div>

        <!-- 2. Total Debit Amount -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Debit (Dr)') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-arrow-down-left"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-primary font-monospace text-nowrap">৳ {{ formatNum(totalDr) }}</h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Total debited valuation') }}</small>
            </div>
          </div>
        </div>

        <!-- 3. Total Credit Amount -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Credit (Cr)') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-arrow-up-right"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-success font-monospace text-nowrap">৳ {{ formatNum(totalCr) }}</h4>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Total credited valuation') }}</small>
            </div>
          </div>
        </div>

        <!-- 4. Trial Balance Match Status -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Balance Status') }}</span>
                <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" :class="isBalanced ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'">
                  <i :class="isBalanced ? 'fas fa-check-double' : 'fas fa-triangle-exclamation'"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 font-monospace text-nowrap" :class="isBalanced ? 'text-success' : 'text-danger'">
                {{ isBalanced ? $t('Balanced (Dr = Cr)') : ('Diff: ৳ ' + formatNum(Math.abs(totalDr - totalCr))) }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">{{ isBalanced ? $t('Double-entry ledger verified') : $t('Mismatch detected') }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Two-Column Metadata Overview -->
      <div class="row g-3 mb-4">
        <!-- Left: Logistics -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-file-invoice"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Voucher Identification') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Reference numbers, dates and financial year') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" width="38%"><i class="fas fa-hashtag me-2 text-muted"></i>{{ $t('Voucher No') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.voucherno }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-calendar-alt me-2 text-muted"></i>{{ $t('Voucher Date') }}</td>
                    <td class="spec-value font-monospace">{{ data.voucher_date }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-tag me-2 text-muted"></i>{{ $t('Voucher Type') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="getVoucherTypeBadge(data.voucher_type)">
                        {{ data.voucher_type ? data.voucher_type.toUpperCase() : 'JOURNAL' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-calendar-check me-2 text-muted"></i>{{ $t('Financial Year') }}</td>
                    <td class="spec-value fw-semibold text-dark">{{ data.financial_year?.title || '-' }}</td>
                  </tr>
                  <tr v-if="data.payment?.payslipno">
                    <td class="spec-label"><i class="fas fa-receipt me-2 text-muted"></i>{{ $t('Payslip Ref') }}</td>
                    <td class="spec-value font-monospace text-primary fw-bold">{{ data.payment.payslipno }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Narration & Approval -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-comment-dollar"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Narration & Posting Status') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Accounting explanation and sign-off') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" width="38%"><i class="fas fa-circle-check me-2 text-muted"></i>{{ $t('Status') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.status === 'active' || data.status == 1 ? 'bg-success text-white' : 'bg-secondary text-white'">
                        {{ data.status === 'active' || data.status == 1 ? $t('Posted & Active') : $t('Pending') }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-user-check me-2 text-muted"></i>{{ $t('Approved By') }}</td>
                    <td class="spec-value fw-bold text-dark">{{ data.approved_by || 'System Administrator' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-clock me-2 text-muted"></i>{{ $t('Approved At') }}</td>
                    <td class="spec-value font-monospace">{{ data.approved_at || data.created_at || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-comment-alt me-2 text-muted"></i>{{ $t('General Narration') }}</td>
                    <td class="spec-value fst-italic text-dark">{{ data.narration || $t('Routine financial transaction entry.') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 📦 Double Entry Journal Table -->
      <div class="card border-0 shadow-sm mb-4 table-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-book-open"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Journal Transactions') }}</h6>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Double-entry ledger postings with debits and credits') }}</small>
            </div>
          </div>
          <span class="badge theme-bg text-white font-monospace">
            {{ data.voucher_details ? data.voucher_details.length : 0 }} {{ $t('Line(s)') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-items-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width: 45px;">#</th>
                  <th>{{ $t('Account Name & Code') }}</th>
                  <th style="width: 140px;">{{ $t('Account Type') }}</th>
                  <th class="text-end" style="width: 140px;">{{ $t('Debit (Dr.)') }}</th>
                  <th class="text-end" style="width: 140px;">{{ $t('Credit (Cr.)') }}</th>
                  <th>{{ $t('Line Narration / Ref') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.voucher_details" :key="index">
                  <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>
                  <td>
                    <div class="fw-bold text-dark fs-6">{{ item.account?.account_name || ('Account #' + item.account_id) }}</div>
                    <div class="small text-muted font-monospace mt-0.5" v-if="item.account?.account_code">
                      Code: {{ item.account.account_code }}
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border">
                      {{ item.account_type || item.account?.account_type || 'General' }}
                    </span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-primary fs-6">
                    {{ parseFloat(item.dr_amount) > 0 ? ('৳ ' + formatNum(item.dr_amount)) : '-' }}
                  </td>
                  <td class="text-end font-monospace fw-bold text-success fs-6">
                    {{ parseFloat(item.cr_amount) > 0 ? ('৳ ' + formatNum(item.cr_amount)) : '-' }}
                  </td>
                  <td class="small">
                    <span v-if="item.line_narration" class="text-dark">{{ item.line_narration }}</span>
                    <span v-else class="text-muted fst-italic">{{ data.narration || '-' }}</span>
                    <div v-if="item.reference_type" class="text-muted font-monospace mt-0.5" style="font-size: 11px;">
                      Ref: {{ item.reference_type }} <span v-if="item.reference_id">#{{ item.reference_id }}</span>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold fs-6">
                  <td colspan="3" class="text-end text-uppercase">{{ $t('Total Debit & Credit:') }}</td>
                  <td class="text-end font-monospace text-primary fs-5">৳ {{ formatNum(totalDr) }}</td>
                  <td class="text-end font-monospace text-success fs-5">৳ {{ formatNum(totalCr) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ Printable Voucher (#voucher_print_area) -->
    <div id="voucher_print_area" class="d-none d-print-block print-voucher-page" v-if="data && data.voucherno">
      <div class="text-center pb-3 mb-3 border-bottom">
        <h3 class="fw-bold mb-0 text-uppercase">{{ $root.site?.name || 'QPOS Retail & Wholesale' }}</h3>
        <p class="small text-muted mb-1">{{ $root.site?.address || '' }}</p>
        <div class="d-inline-block border border-dark px-3 py-1 fw-bold text-uppercase mt-2" style="font-size: 13px; letter-spacing: 1.5px;">
          {{ (data.voucher_type ? data.voucher_type.toUpperCase() : 'JOURNAL') + ' VOUCHER' }}
        </div>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-meta-table">
            <tbody>
              <tr><th width="45%" class="bg-light">{{ $t('Voucher No') }}:</th><td class="font-monospace fw-bold">{{ data.voucherno }}</td></tr>
              <tr><th class="bg-light">{{ $t('Date') }}:</th><td>{{ data.voucher_date }}</td></tr>
              <tr><th class="bg-light">{{ $t('Financial Year') }}:</th><td>{{ data.financial_year?.title || '-' }}</td></tr>
            </tbody>
          </table>
        </div>
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-meta-table">
            <tbody>
              <tr><th width="45%" class="bg-light">{{ $t('Voucher Type') }}:</th><td class="fw-bold">{{ data.voucher_type }}</td></tr>
              <tr><th class="bg-light">{{ $t('Status') }}:</th><td>{{ (data.status === 'active' || data.status == 1) ? 'POSTED' : 'PENDING' }}</td></tr>
              <tr><th class="bg-light">{{ $t('Approved By') }}:</th><td>{{ data.approved_by || 'Admin' }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <table class="table table-bordered align-middle print-items-table mb-3">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 35px;">#</th>
            <th class="text-start">{{ $t('Account Details & Code') }}</th>
            <th class="text-end" style="width: 120px;">{{ $t('Debit (Dr.)') }}</th>
            <th class="text-end" style="width: 120px;">{{ $t('Credit (Cr.)') }}</th>
            <th class="text-start">{{ $t('Narration') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data.voucher_details" :key="index">
            <td class="text-center font-monospace">{{ index + 1 }}</td>
            <td>
              <div class="fw-bold">{{ item.account?.account_name }}</div>
              <small class="text-muted font-monospace" v-if="item.account?.account_code">Code: {{ item.account.account_code }}</small>
            </td>
            <td class="text-end font-monospace">{{ parseFloat(item.dr_amount) > 0 ? ('৳ ' + formatNum(item.dr_amount)) : '-' }}</td>
            <td class="text-end font-monospace">{{ parseFloat(item.cr_amount) > 0 ? ('৳ ' + formatNum(item.cr_amount)) : '-' }}</td>
            <td class="small">{{ item.line_narration || data.narration || '-' }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="fw-bold">
            <td colspan="2" class="text-end text-uppercase">{{ $t('Total:') }}</td>
            <td class="text-end font-monospace">৳ {{ formatNum(totalDr) }}</td>
            <td class="text-end font-monospace">৳ {{ formatNum(totalCr) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      <div class="p-2 border bg-light mb-4 small" v-if="data.narration">
        <strong>{{ $t('General Narration:') }}</strong> {{ data.narration }}
      </div>

      <div class="row pt-5 mt-4 text-center">
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ $t('Accountant / Prepared By') }}</strong>
          </div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ $t('Checked & Verified By') }}</strong>
          </div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ data.approved_by || $t('Authorized Approval') }}</strong>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "voucher";

export default {
  name: "VoucherView",
  data() {
    return {
      page_title: "Voucher Details",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  computed: {
    totalDr() {
      if (!this.data || !this.data.voucher_details) return 0;
      return this.data.voucher_details.reduce((sum, item) => sum + parseFloat(item.dr_amount || 0), 0);
    },

    totalCr() {
      if (!this.data || !this.data.voucher_details) return 0;
      return this.data.voucher_details.reduce((sum, item) => sum + parseFloat(item.cr_amount || 0), 0);
    },

    isBalanced() {
      return Math.abs(this.totalDr - this.totalCr) < 0.01;
    },
  },

  methods: {
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    getVoucherTypeBadge(type) {
      if (!type) return "bg-secondary text-white";
      const t = type.toLowerCase();
      if (t === "credit") return "bg-success text-white";
      if (t === "debit") return "bg-danger text-white";
      if (t === "journal") return "bg-primary text-white";
      if (t === "contra") return "bg-warning text-dark";
      return "bg-secondary text-white";
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} Details`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.voucher-view-wrapper {
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

.voucher-hero-banner {
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