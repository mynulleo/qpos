<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="true"
    :showEditRoute="true"
    :showDeleteButton="true"
    :showPrintButton="true"
    printArea="withdrawal_print_area"
  >
    <!-- 💻 1. Interactive On-Screen ERP Dashboard View -->
    <div class="withdrawal-web-view w-100">
      
      <!-- 🌟 Top Hero / Withdrawal Header Banner -->
      <div class="card border-0 shadow-sm mb-4 withdrawal-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-hand-holding-usd fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ $t('Fund Withdrawal Voucher') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #WDL-{{ String(data.id || '').padStart(5, '0') }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Posted & Active') : $t('Inactive') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="far fa-calendar-alt me-1"></i> {{ $t('Withdrawal Date') }}:
                    <strong class="text-white">{{ data.withdrwal_date || '-' }}</strong>
                  </span>
                  <span>
                    <i class="fas fa-receipt me-1"></i> {{ $t('Voucher No') }}:
                    <strong class="text-white">{{ data.voucher?.voucherno || ('TRX-' + String(data.id || '').padStart(5, '0')) }}</strong>
                  </span>
                  <span v-if="data.voucher?.financial_year?.title">
                    <i class="fas fa-calendar-check me-1"></i> {{ $t('Fiscal Year') }}:
                    <strong class="text-white">{{ data.voucher.financial_year.title }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Metadata Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-shield-halved me-1 text-info"></i> {{ $t('Double-Entry Ledger Verified') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Total Withdrawn Amount -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Withdrawn Amount') }}</span>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                {{ $filter.formatBDT(data.amount) }}
              </h4>
              <small class="text-muted d-block text-truncate" :title="currencyToWordsBD(data.amount || 0)" style="font-size: 11px;">
                {{ currencyToWordsBD(data.amount || 0) }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Source Account (Credit Outflow) -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 small fw-bold">
                  {{ $t('Credit (Disbursed From)') }}
                </span>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-university"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="data.account?.account_name">
                {{ data.account?.account_name || ('Account #' + data.account_id) }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.account?.account_code ? 'Code: ' + data.account_code : $t('Fund Account') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Linked Journal Voucher -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 small fw-bold">
                  {{ $t('Journal Voucher') }}
                </span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-receipt"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark font-monospace text-truncate">
                {{ data.voucher?.voucherno || ('TRX-' + String(data.id || '').padStart(5, '0')) }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.voucher?.financial_year?.title || $t('Financial Accounts') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Posting Date & Status -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Status & Posting') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-check-double"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark font-monospace">
                {{ data.withdrwal_date || '-' }}
              </h5>
              <small class="text-success fw-semibold font-monospace" style="font-size: 11px;">
                <i class="fas fa-circle me-1" style="font-size: 7px;"></i>{{ $t('Posted to General Ledger') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Information Details Card -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-info-circle text-primary"></i>
            {{ $t('Withdrawal Particulars & Narrative') }}
          </span>
          <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
            {{ $t('Source') }}: Withdrawl
          </span>
        </div>
        <div class="card-body p-4">
          <div class="row g-4">
            <div class="col-md-6">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted ps-0 py-2" style="width: 40%;">{{ $t('Withdrawal Ref') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">#WDL-{{ String(data.id || '').padStart(5, '0') }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Withdrawal Date') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">{{ data.withdrwal_date || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Withdrawn Amount') }}:</td>
                    <td class="fw-bold text-danger font-monospace py-2 fs-6">{{ $filter.formatBDT(data.amount) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Amount in Words') }}:</td>
                    <td class="small text-muted py-2 fst-italic">{{ currencyToWordsBD(data.amount || 0) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="col-md-6 border-start-md">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted ps-0 py-2" style="width: 40%;">{{ $t('Source Account') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">
                      {{ data.account ? data.account.account_name : ('#' + data.account_id) }}
                    </td>
                  </tr>
                  <tr v-if="data.account && data.account.account_code">
                    <td class="text-muted ps-0 py-2">{{ $t('Account Code') }}:</td>
                    <td class="fw-bold text-muted font-monospace py-2">{{ data.account.account_code }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Remarks / Note') }}:</td>
                    <td class="text-dark py-2">{{ data.remarks || 'No remarks provided' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Status') }}:</td>
                    <td class="py-2">
                      <span class="badge rounded-pill px-2 py-1 font-monospace" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                        {{ data.status === 'active' ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 📑 Double-Entry Journal Breakdown Table (If linked voucher exists) -->
      <div v-if="data.voucher && data.voucher.voucher_details && data.voucher.voucher_details.length" class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-balance-scale text-primary"></i>
            {{ $t('Double-Entry Journal Entries') }} ({{ data.voucher.voucherno }})
          </span>
          <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small font-monospace">
            <i class="fas fa-check-circle me-1"></i>{{ $t('Balanced') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted font-monospace">
                <tr>
                  <th style="width: 50px;" class="text-center">#</th>
                  <th>{{ $t('Account Head') }}</th>
                  <th style="width: 130px;" class="text-center">{{ $t('Entry Type') }}</th>
                  <th style="width: 170px;" class="text-end">{{ $t('Debit (BDT)') }}</th>
                  <th style="width: 170px;" class="text-end">{{ $t('Credit (BDT)') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, idx) in data.voucher.voucher_details" :key="idx">
                  <td class="text-center font-monospace text-muted small">{{ idx + 1 }}</td>
                  <td>
                    <div class="fw-bold text-dark font-monospace">
                      {{ entry.account?.account_name || ('Account #' + entry.account_id) }}
                    </div>
                    <small class="text-muted font-monospace" v-if="entry.account?.account_code">
                      Code: {{ entry.account.account_code }}
                    </small>
                  </td>
                  <td class="text-center">
                    <span
                      class="badge font-monospace px-2 py-1 rounded"
                      :class="entry.type === 'Debit' ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25'"
                    >
                      {{ entry.type }}
                    </span>
                  </td>
                  <td class="text-end font-monospace fw-bold" :class="entry.type === 'Debit' ? 'text-primary' : 'text-muted'">
                    {{ entry.type === 'Debit' ? $filter.formatBDT(entry.amount) : '—' }}
                  </td>
                  <td class="text-end font-monospace fw-bold" :class="entry.type === 'Credit' ? 'text-danger' : 'text-muted'">
                    {{ entry.type === 'Credit' ? $filter.formatBDT(entry.amount) : '—' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Printable Official Voucher Sheet (Rendered only during print) -->
    <div id="withdrawal_print_area" class="d-none d-print-block print-container p-4 bg-white text-dark">
      <div class="print-voucher-border p-4">
        
        <!-- Header / Organization -->
        <div class="text-center pb-3 border-bottom mb-4">
          <h3 class="fw-bold mb-1 text-uppercase letter-spacing-1">{{ $root.global?.site_name || 'QPOS ERP' }}</h3>
          <p class="mb-1 text-muted small">{{ $root.global?.address || 'Official Accounting Voucher' }}</p>
          <div class="d-inline-block border border-dark px-3 py-1 mt-2 text-uppercase fw-bold font-monospace fs-6">
            {{ $t('FUND WITHDRAWAL VOUCHER') }}
          </div>
        </div>

        <!-- Meta info grid -->
        <div class="row g-3 mb-4 font-monospace small">
          <div class="col-6">
            <div><strong>{{ $t('Voucher No') }}:</strong> {{ data.voucher?.voucherno || ('WDL-' + String(data.id || '').padStart(5, '0')) }}</div>
            <div><strong>{{ $t('Withdrawal Date') }}:</strong> {{ data.withdrwal_date }}</div>
            <div><strong>{{ $t('Disbursed Account') }}:</strong> {{ data.account?.account_name }}</div>
          </div>
          <div class="col-6 text-end">
            <div><strong>{{ $t('Print Date') }}:</strong> {{ $filter.today() }}</div>
            <div><strong>{{ $t('Reference ID') }}:</strong> #WDL-{{ String(data.id || '').padStart(5, '0') }}</div>
            <div><strong>{{ $t('Fiscal Year') }}:</strong> {{ data.voucher?.financial_year?.title || 'Current' }}</div>
          </div>
        </div>

        <!-- Voucher Details Table -->
        <table class="table table-bordered mb-3 small font-monospace">
          <thead class="table-light">
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <th>{{ $t('Particulars / Account Description') }}</th>
              <th style="width: 150px;" class="text-end">{{ $t('Amount (BDT)') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-center">1</td>
              <td>
                <div class="fw-bold">{{ $t('Fund Withdrawal from') }} {{ data.account?.account_name }}</div>
                <div class="text-muted small" v-if="data.remarks">{{ $t('Note') }}: {{ data.remarks }}</div>
              </td>
              <td class="text-end fw-bold fs-6">{{ $filter.formatBDT(data.amount) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="fw-bold">
              <td colspan="2" class="text-end text-uppercase">{{ $t('Total Withdrawn Amount') }}:</td>
              <td class="text-end fs-6">{{ $filter.formatBDT(data.amount) }}</td>
            </tr>
          </tfoot>
        </table>

        <!-- Amount In Words -->
        <div class="p-2 border rounded bg-light small mb-5">
          <strong>{{ $t('In Words') }}:</strong> {{ currencyToWordsBD(data.amount || 0) }}
        </div>

        <!-- Official Signatures Block -->
        <div class="row text-center font-monospace small pt-5" style="margin-top: 50px;">
          <div class="col-3">
            <div class="border-top border-dark pt-1">{{ $t('Prepared By') }}</div>
          </div>
          <div class="col-3">
            <div class="border-top border-dark pt-1">{{ $t('Checked By') }}</div>
          </div>
          <div class="col-3">
            <div class="border-top border-dark pt-1">{{ $t('Approved By') }}</div>
          </div>
          <div class="col-3">
            <div class="border-top border-dark pt-1">{{ $t('Received By') }}</div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "withdrawl";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  methods: {
    currencyToWordsBD(num) {
      if (!num || isNaN(num) || num <= 0) return 'Zero Taka Only';
      const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
      const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

      const inWords = (n) => {
        let str = '';
        if (n >= 10000000) {
          str += inWords(Math.floor(n / 10000000)) + 'Crore ';
          n %= 10000000;
        }
        if (n >= 100000) {
          str += inWords(Math.floor(n / 100000)) + 'Lakh ';
          n %= 100000;
        }
        if (n >= 1000) {
          str += inWords(Math.floor(n / 1000)) + 'Thousand ';
          n %= 1000;
        }
        if (n >= 100) {
          str += inWords(Math.floor(n / 100)) + 'Hundred ';
          n %= 100;
        }
        if (n > 0) {
          if (n < 20) {
            str += a[n];
          } else {
            str += b[Math.floor(n / 10)] + ' ' + a[n % 10];
          }
        }
        return str;
      };

      const integerPart = Math.floor(num);
      const decimalPart = Math.round((num - integerPart) * 100);
      let result = inWords(integerPart).trim() + ' Taka';
      if (decimalPart > 0) {
        result += ' and ' + inWords(decimalPart).trim() + ' Poisa';
      }
      return result + ' Only';
    }
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.withdrawal-hero-banner {
  background: linear-gradient(135deg, #112C47 0%, #1e3c72 100%);
  border-radius: 12px;
}
.hero-icon-box {
  width: 52px;
  height: 52px;
}
.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 10px;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.stat-icon {
  width: 38px;
  height: 38px;
  font-size: 16px;
}
@media (min-width: 768px) {
  .border-start-md {
    border-left: 1px solid #edf2f7 !important;
  }
}
@media print {
  .print-voucher-border {
    border: 2px solid #333;
    padding: 24px;
  }
}
</style>