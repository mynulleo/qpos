<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" :showPrintButton="false" :button="false" :showEditRoute="false" printArea="account_print_area">
    <div class="account-view-wrapper" v-if="data && data.id">
      <!-- 🌟 Top Hero / Account Header Banner -->
      <div class="card border-0 shadow-sm mb-4 account-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i :class="data.is_group ? 'fas fa-layer-group fs-3' : 'fas fa-book-bookmark fs-3'"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">{{ data.account_name }}</h4>
                  <span class="badge" :class="data.status === 'active' || data.status == 1 ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i :class="data.status === 'active' || data.status == 1 ? 'fas fa-check-circle me-1' : 'fas fa-ban me-1'"></i>
                    {{ data.status === 'active' || data.status == 1 ? $t('Active') : $t('Deactive') }}
                  </span>
                  <span class="badge" :class="getAccountTypeBadge(data.account_type)">
                    {{ data.account_type ? data.account_type.toUpperCase() : 'GENERAL' }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="data.is_group">
                    <i class="fas fa-folder me-1"></i> {{ $t('Group Head') }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="data.is_fund_account">
                    <i class="fas fa-wallet me-1"></i> {{ $t('Fund Account') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>Code: <strong class="text-white">{{ data.account_code || ('ACC-' + data.id) }}</strong></span>
                  <span v-if="data.parent"><i class="fas fa-folder-tree me-1"></i>Parent: <strong class="text-white">{{ data.parent.account_name }}</strong></span>
                  <span v-if="data.reference_type"><i class="fas fa-link me-1"></i>Reference: <strong class="text-white">{{ data.reference_type }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons (Print & Back) -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="print('account_print_area', 'Account - ' + data.account_name)">
                <i class="fas fa-print me-1"></i> {{ $t('Print Summary') }}
              </button>
              <router-link :to="{ name: model + '.index' }" class="btn btn-outline-light btn-sm fw-bold px-3">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Two-Column Metadata Overview -->
      <div class="row g-3 mb-4">
        <!-- Left: Account Details -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-info-circle"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Account Structure & Identification') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Account code, name, parent head and classification') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" width="38%"><i class="fas fa-barcode me-2 text-muted"></i>{{ $t('Account Code') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.account_code || ('ACC-' + data.id) }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-heading me-2 text-muted"></i>{{ $t('Account Name') }}</td>
                    <td class="spec-value fw-bold text-dark">{{ data.account_name }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-folder-tree me-2 text-muted"></i>{{ $t('Parent Group Head') }}</td>
                    <td class="spec-value fw-semibold text-dark">{{ data.parent?.account_name || $t('Top Level Primary Head') }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-tags me-2 text-muted"></i>{{ $t('Account Classification') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="getAccountTypeBadge(data.account_type)">
                        {{ data.account_type ? data.account_type.toUpperCase() : 'GENERAL' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-layer-group me-2 text-muted"></i>{{ $t('Account Mode') }}</td>
                    <td class="spec-value">
                      <span v-if="data.is_group" class="badge bg-warning bg-opacity-10 text-dark border">
                        <i class="fas fa-folder me-1 text-warning"></i>{{ $t('Group / Parent Account') }}
                      </span>
                      <span v-else class="badge bg-light text-dark border">
                        <i class="fas fa-file-invoice me-1 text-primary"></i>{{ $t('General Ledger Account') }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="data.system_key_name">
                    <td class="spec-label"><i class="fas fa-key me-2 text-muted"></i>{{ $t('System Key') }}</td>
                    <td class="spec-value font-monospace text-muted">{{ data.system_key_name }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Balances & Reference -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-coins"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Financial Balances & Reference Entity') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Opening balances, fund status and link references') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" width="38%"><i class="fas fa-wallet me-2 text-muted"></i>{{ $t('Opening Balance') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark fs-6">৳ {{ formatNum(data.opening_balance || 0) }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-balance-scale me-2 text-muted"></i>{{ $t('Balance Type') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.opening_balance_type === 'debit' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success'">
                        {{ data.opening_balance_type ? data.opening_balance_type.toUpperCase() : 'DEBIT' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-link me-2 text-muted"></i>{{ $t('Reference Entity') }}</td>
                    <td class="spec-value font-monospace">{{ data.reference_type || 'General Accounting' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-circle-check me-2 text-muted"></i>{{ $t('Operational Status') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.status === 'active' || data.status == 1 ? 'bg-success text-white' : 'bg-secondary text-white'">
                        {{ data.status === 'active' || data.status == 1 ? $t('Active & Postable') : $t('Deactive') }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-calendar-alt me-2 text-muted"></i>{{ $t('Registered Date') }}</td>
                    <td class="spec-value font-monospace">{{ data.created_at ? data.created_at.substring(0, 10) : 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ Printable Summary Sheet (#account_print_area) -->
    <div id="account_print_area" class="d-none d-print-block print-voucher-page" v-if="data && data.id">
      <div class="text-center pb-3 mb-3 border-bottom">
        <h3 class="fw-bold mb-0 text-uppercase">{{ $root.site?.name || 'QPOS Retail & Wholesale' }}</h3>
        <p class="small text-muted mb-1">{{ $root.site?.address || '' }}</p>
        <div class="d-inline-block border border-dark px-3 py-1 fw-bold text-uppercase mt-2" style="font-size: 13px; letter-spacing: 1.5px;">
          {{ $t('ACCOUNT PROFILE SUMMARY') }}
        </div>
      </div>

      <table class="table table-bordered mb-4">
        <tbody>
          <tr><th width="30%" class="bg-light">{{ $t('Account Code') }}:</th><td class="font-monospace fw-bold">{{ data.account_code || ('ACC-' + data.id) }}</td></tr>
          <tr><th class="bg-light">{{ $t('Account Name') }}:</th><td class="fw-bold fs-6">{{ data.account_name }}</td></tr>
          <tr><th class="bg-light">{{ $t('Parent Group Head') }}:</th><td>{{ data.parent?.account_name || 'Top Level Primary Head' }}</td></tr>
          <tr><th class="bg-light">{{ $t('Account Classification') }}:</th><td class="fw-bold">{{ data.account_type ? data.account_type.toUpperCase() : 'GENERAL' }}</td></tr>
          <tr><th class="bg-light">{{ $t('Opening Balance') }}:</th><td class="font-monospace fw-bold">৳ {{ formatNum(data.opening_balance || 0) }} ({{ (data.opening_balance_type || 'DEBIT').toUpperCase() }})</td></tr>
          <tr><th class="bg-light">{{ $t('Reference Entity') }}:</th><td>{{ data.reference_type || 'General Accounting' }}</td></tr>
          <tr><th class="bg-light">{{ $t('Status') }}:</th><td>{{ (data.status === 'active' || data.status == 1) ? 'ACTIVE' : 'DEACTIVE' }}</td></tr>
        </tbody>
      </table>

      <div class="row pt-5 mt-4 text-center">
        <div class="col-6">
          <div class="border-top pt-2 mx-5">
            <strong class="d-block text-dark">{{ $t('Accountant / Prepared By') }}</strong>
          </div>
        </div>
        <div class="col-6">
          <div class="border-top pt-2 mx-5">
            <strong class="d-block text-dark">{{ $t('Authorized Approval & Seal') }}</strong>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "account";

export default {
  name: "AccountView",
  data() {
    return {
      page_title: "Account Details",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  methods: {
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    getAccountTypeBadge(type) {
      if (!type) return "bg-secondary text-white";
      const t = type.toLowerCase();
      if (t === "asset") return "bg-success text-white";
      if (t === "liability") return "bg-warning text-dark";
      if (t === "equity") return "bg-purple text-white";
      if (t === "revenue") return "bg-info text-dark";
      if (t === "expense") return "bg-danger text-white";
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
.account-view-wrapper {
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

.bg-purple {
  background-color: #8b5cf6 !important;
}

.account-hero-banner {
  background: linear-gradient(135deg, #112C47 0%, #1e4976 100%);
  border-radius: 12px;
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  border-radius: 12px;
}

.section-icon {
  width: 32px;
  height: 32px;
  font-size: 14px;
}

.info-card {
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

.print-voucher-page {
  font-family: inherit;
  color: #000;
  background: #fff;
  padding: 20px;
}
</style>