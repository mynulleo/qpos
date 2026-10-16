<template>
  <create-form @onSubmit="submit">
    <!-- Top Hero / Banner Section -->
    <div class="col-12 mb-3">
      <div class="fund-transfer-hero p-3 p-md-4 rounded-4 shadow-sm border bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="hero-icon-box shadow-sm">
              <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0">
                  {{ $t($route.params.id ? 'Edit Fund Transfer' : 'Fund Transfer') }}
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill small">
                  <i class="fas fa-university me-1"></i>{{ $t('Inter-Account Transfer') }}
                </span>
                <span v-if="$route.params.id" class="badge bg-secondary text-white rounded-pill px-2 py-1 small">
                  #{{ $route.params.id }}
                </span>
              </div>
              <p class="text-muted small mb-0 mt-1">
                {{ $t('Safely transfer money between cash and bank accounts with real-time balance ledger simulation.') }}
              </p>
            </div>
          </div>

          <!-- Dynamic Status Indicator Pill -->
          <div class="transfer-status-indicator">
            <div v-if="isSameAccount" class="status-pill status-pill-warning">
              <i class="fas fa-exclamation-triangle"></i>
              <span>{{ $t('Accounts Cannot Be Same') }}</span>
            </div>
            <div v-else-if="isInsufficientBalance" class="status-pill status-pill-danger">
              <i class="fas fa-times-circle"></i>
              <span>{{ $t('Insufficient Source Balance') }}</span>
            </div>
            <div v-else-if="isTransferValid" class="status-pill status-pill-success">
              <span class="status-pulse"></span>
              <span>{{ $t('Ready to Transfer') }}: <strong>{{ formatCurrency(numericAmount) }}</strong></span>
            </div>
            <div v-else class="status-pill status-pill-neutral">
              <i class="fas fa-info-circle"></i>
              <span>{{ $t('Complete Transfer Details') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Left Side: 8 Columns -->
    <div class="col-lg-8">
      <div class="d-flex flex-column gap-3">

        <!-- 1. Interactive Account Flow Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-white" style="overflow: visible !important;">
          <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="fas fa-route text-primary"></i>
              {{ $t('Account Routing & Direction') }}
            </span>
            <button
              type="button"
              class="btn btn-sm btn-outline-primary rounded-pill px-3 d-flex align-items-center gap-2 swap-btn"
              @click="swapAccounts"
              :disabled="!data.from_account_id && !data.to_account_id"
              :title="$t('Swap Source and Destination Accounts')"
            >
              <i class="fas fa-arrows-alt-h"></i>
              <span>{{ $t('Swap Accounts') }}</span>
            </button>
          </div>

          <div class="card-body p-3 p-md-4" style="overflow: visible !important;">
            <div class="row g-3 align-items-stretch" style="overflow: visible !important;">
              <!-- From Account Column -->
              <div class="col-md-5" style="overflow: visible !important;">
                <div class="account-card from-card p-3 rounded-3 h-100 border" style="overflow: visible !important;">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 small fw-bold">
                      <i class="fas fa-arrow-up me-1"></i> {{ $t('FROM (Source)') }}
                    </span>
                    <span v-if="loadingFromFund" class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                  </div>

                  <Select
                    title='From Account'
                    col="12"
                    v-model='data.from_account_id'
                    field='data.from_account_id'
                    label='name'
                    :reduce='(obj) => obj.id'
                    :options='fundaccounts'
                    placeholder='-- Select Source Account --'
                    :closeOnSelect='true'
                    :appendToBody='true'
                    :required='true'
                  />

                  <!-- Live Balance Display -->
                  <div class="balance-meta-box mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">
                        <i class="fas fa-wallet me-1 text-secondary"></i> {{ $t('Current Balance') }}:
                      </span>
                      <span class="fw-bold font-monospace" :class="fromCurrentBalance >= 0 ? 'text-dark' : 'text-danger'">
                        {{ fromFund ? formatCurrency(fromCurrentBalance) : '—' }}
                      </span>
                    </div>

                    <!-- Projected After Transfer -->
                    <div v-if="fromFund && numericAmount > 0" class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-dashed">
                      <span class="small" :class="isInsufficientBalance ? 'text-danger fw-semibold' : 'text-muted'">
                        <i class="fas fa-calculator me-1"></i> {{ $t('After Transfer') }}:
                      </span>
                      <span class="fw-bold small font-monospace" :class="fromProjectedBalance >= 0 ? 'text-muted' : 'text-danger'">
                        {{ formatCurrency(fromProjectedBalance) }}
                      </span>
                    </div>

                    <!-- Warning message if insufficient -->
                    <div v-if="isInsufficientBalance" class="mt-2 text-danger small d-flex align-items-center gap-1">
                      <i class="fas fa-exclamation-circle"></i>
                      <span>{{ $t('Transfer exceeds available fund balance!') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Center Flow Connector -->
              <div class="col-md-2 d-flex flex-column align-items-center justify-content-center py-2">
                <div class="transfer-connector-icon shadow-sm" :class="{ 'active': isTransferValid }">
                  <i class="fas fa-arrow-right d-none d-md-inline"></i>
                  <i class="fas fa-arrow-down d-inline d-md-none"></i>
                </div>
                <div v-if="numericAmount > 0" class="badge bg-primary text-white rounded-pill px-2 py-1 mt-2 text-nowrap small shadow-sm font-monospace">
                  {{ formatCurrency(numericAmount) }}
                </div>
                <span v-else class="text-muted small mt-2" style="font-size: 11px;">
                  {{ $t('Direct Flow') }}
                </span>
              </div>

              <!-- To Account Column -->
              <div class="col-md-5" style="overflow: visible !important;">
                <div class="account-card to-card p-3 rounded-3 h-100 border" style="overflow: visible !important;">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 small fw-bold">
                      <i class="fas fa-arrow-down me-1"></i> {{ $t('TO (Destination)') }}
                    </span>
                    <span v-if="loadingToFund" class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                  </div>

                  <Select
                    title='To Account'
                    col="12"
                    v-model='data.to_account_id'
                    field='data.to_account_id'
                    label='name'
                    :reduce='(obj) => obj.id'
                    :options='fundaccounts'
                    placeholder='-- Select Destination Account --'
                    :closeOnSelect='true'
                    :appendToBody='true'
                    :required='true'
                  />

                  <!-- Live Balance Display -->
                  <div class="balance-meta-box mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">
                        <i class="fas fa-vault me-1 text-secondary"></i> {{ $t('Current Balance') }}:
                      </span>
                      <span class="fw-bold font-monospace" :class="toCurrentBalance >= 0 ? 'text-dark' : 'text-danger'">
                        {{ toFund ? formatCurrency(toCurrentBalance) : '—' }}
                      </span>
                    </div>

                    <!-- Projected After Transfer -->
                    <div v-if="toFund && numericAmount > 0" class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-dashed">
                      <span class="text-success small fw-semibold">
                        <i class="fas fa-plus-circle me-1"></i> {{ $t('After Transfer') }}:
                      </span>
                      <span class="fw-bold small text-success font-monospace">
                        {{ formatCurrency(toProjectedBalance) }}
                      </span>
                    </div>

                    <!-- Warning if same account -->
                    <div v-if="isSameAccount" class="mt-2 text-warning small d-flex align-items-center gap-1">
                      <i class="fas fa-exclamation-triangle"></i>
                      <span>{{ $t('Choose a different destination') }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Transfer Amount & Metadata Card -->
        <div class="card shadow-sm border-0 rounded-4 bg-white" style="overflow: visible !important;">
          <div class="card-header bg-white border-bottom py-3 px-4">
            <span class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="fas fa-money-bill-wave text-warning"></i>
              {{ $t('Transfer Amount & Parameters') }}
            </span>
          </div>

          <div class="card-body p-3 p-md-4">
            <div class="row g-3">
              <!-- Transfer Date -->
              <date-picker
                id='date0'
                v-model='data.transfer_date'
                field='data.transfer_date'
                title='Transfer Date'
                placeholder='Transfer Date'
                col='6'
                :req='true'
              />

              <!-- Status Switch -->
              <Switch
                v-model='data.status'
                field='data.status'
                title='Status'
                on-label='Active'
                off-label='Deactive'
                col='6'
                :req='true'
              />

              <!-- Transfer Amount Hero Input Box -->
              <div class="col-12 mt-2">
                <div class="amount-input-box p-3 p-md-4 rounded-3 border">
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <label class="form-label fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                      <i class="fas fa-coins text-warning"></i>
                      {{ $t('Transfer Amount') }}
                      <span class="text-danger">*</span>
                    </label>

                    <!-- Quick Action Buttons: Max & Clear -->
                    <div class="d-flex align-items-center gap-1">
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-success py-1 px-2 rounded-pill small fw-semibold"
                        @click="setMaxBalance"
                        :disabled="fromCurrentBalance <= 0"
                        title="Set amount to full available source balance"
                      >
                        <i class="fas fa-wallet me-1"></i> {{ $t('Max Balance') }}
                      </button>
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-secondary py-1 px-2 rounded-pill small"
                        @click="clearAmount"
                        :disabled="!data.amount || data.amount == 0"
                        title="Clear amount"
                      >
                        <i class="fas fa-undo me-1"></i> {{ $t('Clear') }}
                      </button>
                    </div>
                  </div>

                  <!-- Amount Input Field -->
                  <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white fw-bold text-primary px-3 fs-3 border-end-0">৳</span>
                    <input
                      type="number"
                      step="any"
                      min="0"
                      class="form-control form-control-lg border-start-0 fw-bold fs-3 text-dark font-monospace"
                      :class="{ 'is-invalid': isInsufficientBalance }"
                      v-model="data.amount"
                      placeholder="0.00"
                      autocomplete="off"
                    />
                  </div>

                  <!-- In-Words Display -->
                  <div v-if="numericAmount > 0" class="in-words-preview mt-2 small d-flex align-items-center gap-2">
                    <i class="fas fa-quote-left text-muted opacity-75"></i>
                    <span class="fw-semibold text-primary">{{ amountInWords(numericAmount) }}</span>
                  </div>

                  <!-- Quick Preset Amount Chips -->
                  <div class="preset-chips mt-3 d-flex align-items-center flex-wrap gap-2 pt-2 border-top">
                    <span class="text-muted small fw-semibold me-1">
                      <i class="fas fa-bolt text-warning me-1"></i>{{ $t('Quick Add') }}:
                    </span>
                    <button
                      type="button"
                      v-for="preset in [500, 1000, 2000, 5000, 10000, 20000, 50000]"
                      :key="preset"
                      class="btn btn-sm btn-white border rounded-pill px-3 py-1 small preset-btn"
                      @click="addAmount(preset)"
                    >
                      +৳{{ preset.toLocaleString('en-BD') }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Remarks / Note Field with Preset Reason Chips -->
              <div class="col-12 mt-3">
                <label class="form-label fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                  <span>
                    <i class="fas fa-comment-alt text-secondary me-1"></i>
                    {{ $t('Remarks / Narration') }}
                    <span class="text-muted fw-normal small">({{ $t('Optional') }})</span>
                  </span>
                  <span class="text-muted small" v-if="data.remarks">
                    {{ data.remarks.length }} {{ $t('chars') }}
                  </span>
                </label>
                <textarea
                  v-model="data.remarks"
                  class="form-control rounded-3"
                  rows="2"
                  :placeholder="$t('e.g., Petty cash replenishment for store operations, cash withdrawal from bank...')"
                ></textarea>

                <!-- Remark Preset Tags -->
                <div class="remark-tags mt-2 d-flex align-items-center flex-wrap gap-1">
                  <span class="text-muted small me-1">
                    <i class="fas fa-tags me-1 text-muted"></i>{{ $t('Suggestions') }}:
                  </span>
                  <button
                    type="button"
                    v-for="tag in remarkSuggestions"
                    :key="tag"
                    class="badge bg-light text-dark border text-decoration-none fw-normal py-1 px-2 rounded-pill preset-tag-btn"
                    @click="applyRemarkTag(tag)"
                  >
                    {{ tag }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Right Column: 4 Columns Live Ledger & Voucher Simulation (Sticky) -->
    <div class="col-lg-4">
      <div class="sticky-top" style="top: 20px; z-index: 10;">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden voucher-preview-card bg-white">
          <!-- Card Header -->
          <div class="card-header bg-gradient-navy text-white py-3 px-4 d-flex align-items-center justify-content-between">
            <span class="fw-bold d-flex align-items-center gap-2">
              <i class="fas fa-receipt text-warning"></i>
              {{ $t('Ledger Voucher Preview') }}
            </span>
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2 py-1 small">
              {{ $t('Journal') }}
            </span>
          </div>

          <div class="card-body p-3 p-md-4">
            <!-- Voucher Hero Amount Box -->
            <div class="voucher-amount-hero p-3 rounded-3 text-center mb-3">
              <span class="text-muted small text-uppercase tracking-wider d-block mb-1 fw-semibold">
                {{ $t('Total Transfer Amount') }}
              </span>
              <h2 class="fw-bold mb-0 font-monospace" :class="numericAmount > 0 ? 'text-primary' : 'text-muted'">
                {{ formatCurrency(numericAmount) }}
              </h2>
              <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                <span class="badge bg-white text-secondary border small">
                  <i class="far fa-calendar-alt me-1"></i> {{ data.transfer_date || $t('No date') }}
                </span>
                <span class="badge rounded-pill small" :class="data.status === 'active' ? 'bg-success-subtle text-success border border-success' : 'bg-secondary-subtle text-secondary border'">
                  {{ data.status === 'active' ? $t('Active') : $t('Deactive') }}
                </span>
              </div>
            </div>

            <!-- Double-Entry Journal Breakdown -->
            <div class="double-entry-box mb-3">
              <span class="small fw-bold text-uppercase text-muted d-block mb-2">
                <i class="fas fa-balance-scale me-1 text-primary"></i> {{ $t('Double-Entry Ledger Impact') }}
              </span>

              <!-- 1. Outgoing Entry (Credit From-Account) -->
              <div class="ledger-row p-2 rounded-3 border mb-2" :class="{ 'border-danger bg-danger-subtle bg-opacity-25': isInsufficientBalance }">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 65%;">
                    <span class="badge bg-danger text-white rounded px-2 py-1 small font-monospace">Cr</span>
                    <span class="fw-semibold small text-truncate" :title="fromAccountName">
                      {{ fromAccountName || $t('Select Source Account') }}
                    </span>
                  </div>
                  <span class="text-danger fw-bold small font-monospace text-nowrap">
                    -{{ formatCurrency(numericAmount) }}
                  </span>
                </div>
                <div v-if="fromFund" class="mt-1 d-flex justify-content-between align-items-center small text-muted font-monospace" style="font-size: 11px;">
                  <span>{{ $t('Current') }}: {{ formatCurrency(fromCurrentBalance) }}</span>
                  <span :class="fromProjectedBalance >= 0 ? 'text-muted' : 'text-danger fw-bold'">
                    {{ $t('After') }}: {{ formatCurrency(fromProjectedBalance) }}
                  </span>
                </div>
              </div>

              <!-- 2. Incoming Entry (Debit To-Account) -->
              <div class="ledger-row p-2 rounded-3 border mb-2">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 65%;">
                    <span class="badge bg-success text-white rounded px-2 py-1 small font-monospace">Dr</span>
                    <span class="fw-semibold small text-truncate" :title="toAccountName">
                      {{ toAccountName || $t('Select Destination Account') }}
                    </span>
                  </div>
                  <span class="text-success fw-bold small font-monospace text-nowrap">
                    +{{ formatCurrency(numericAmount) }}
                  </span>
                </div>
                <div v-if="toFund" class="mt-1 d-flex justify-content-between align-items-center small text-muted font-monospace" style="font-size: 11px;">
                  <span>{{ $t('Current') }}: {{ formatCurrency(toCurrentBalance) }}</span>
                  <span class="text-success fw-bold">
                    {{ $t('After') }}: {{ formatCurrency(toProjectedBalance) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Transfer Source Allocation Bar -->
            <div v-if="fromFund && fromCurrentBalance > 0 && numericAmount > 0" class="allocation-meter mb-3 p-2 bg-light rounded-3 border">
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="text-muted" style="font-size: 11px;">{{ $t('Source Balance Allocation') }}:</span>
                <span class="fw-bold" :class="isInsufficientBalance ? 'text-danger' : 'text-primary'" style="font-size: 11px;">
                  {{ transferPercentage }}%
                </span>
              </div>
              <div class="progress" style="height: 6px;">
                <div
                  class="progress-bar"
                  :class="isInsufficientBalance ? 'bg-danger' : (transferPercentage > 80 ? 'bg-warning' : 'bg-primary')"
                  role="progressbar"
                  :style="{ width: Math.min(transferPercentage, 100) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Pre-Flight Checklist -->
            <div class="checklist-box p-3 rounded-3 bg-light border mb-3">
              <span class="small fw-bold text-uppercase text-muted d-block mb-2">
                <i class="fas fa-clipboard-check me-1"></i> {{ $t('Transfer Checklist') }}
              </span>
              <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                <li class="d-flex align-items-center gap-2">
                  <i class="fas" :class="data.transfer_date ? 'fa-check-circle text-success' : 'fa-circle text-muted opacity-50'"></i>
                  <span :class="{ 'text-muted': !data.transfer_date }">{{ $t('Transfer Date specified') }}</span>
                </li>
                <li class="d-flex align-items-center gap-2">
                  <i class="fas" :class="data.from_account_id ? 'fa-check-circle text-success' : 'fa-circle text-muted opacity-50'"></i>
                  <span :class="{ 'text-muted': !data.from_account_id }">{{ $t('Source account chosen') }}</span>
                </li>
                <li class="d-flex align-items-center gap-2">
                  <i class="fas" :class="data.to_account_id && !isSameAccount ? 'fa-check-circle text-success' : (isSameAccount ? 'fa-times-circle text-danger' : 'fa-circle text-muted opacity-50')"></i>
                  <span :class="{ 'text-muted': !data.to_account_id, 'text-danger fw-semibold': isSameAccount }">
                    {{ isSameAccount ? $t('Accounts must be different') : $t('Destination account chosen') }}
                  </span>
                </li>
                <li class="d-flex align-items-center gap-2">
                  <i class="fas" :class="numericAmount > 0 && !isInsufficientBalance ? 'fa-check-circle text-success' : (isInsufficientBalance ? 'fa-times-circle text-danger' : 'fa-circle text-muted opacity-50')"></i>
                  <span :class="{ 'text-muted': numericAmount <= 0, 'text-danger fw-semibold': isInsufficientBalance }">
                    {{ isInsufficientBalance ? $t('Insufficient balance in source') : $t('Valid transfer amount') }}
                  </span>
                </li>
              </ul>
            </div>

            <!-- Accounting Information Alert -->
            <div class="alert alert-info py-2 px-3 mb-0 small rounded-3 border-0 bg-info-subtle text-info-emphasis d-flex align-items-start gap-2">
              <i class="fas fa-shield-alt mt-1 flex-shrink-0 text-primary"></i>
              <span style="font-size: 11.5px; line-height: 1.4;">
                {{ $t('A balanced double-entry Journal Voucher (Debit To-Account, Credit From-Account) is automatically posted to the general ledger upon submit.') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Prominent Action Footer Slot -->
    <template #form_footer>
      <div class="col-12 mt-3">
        <div class="action-footer-card p-3 bg-white border rounded-4 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-2">
            <router-link :to="{ name: model + '.index' }" class="btn btn-outline-secondary px-4 rounded-pill fw-semibold">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
            </router-link>
            <button
              type="button"
              class="btn btn-outline-danger px-3 rounded-pill fw-semibold"
              @click="resetForm"
              :title="$t('Reset form fields')"
            >
              <i class="fas fa-redo-alt me-1"></i> {{ $t('Reset') }}
            </button>
          </div>

          <button
            type="submit"
            class="btn btn-primary px-5 py-2 rounded-pill fw-bold d-flex align-items-center gap-2 shadow execute-btn"
            :disabled="$root.submit || !isTransferValid"
          >
            <template v-if="$root.submit">
              <i class="fa fa-spinner fa-spin"></i> {{ $t('Processing...') }}
            </template>
            <template v-else>
              <i :class="$route.params.id ? 'fas fa-save' : 'fas fa-paper-plane'"></i>
              <span>{{ $t($route.params.id ? 'Update Transfer' : 'Execute Fund Transfer') }}</span>
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = 'fundTransfer';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        transfer_date: this.$filter.today(),
        from_account_id: '',
        to_account_id: '',
        amount: null,
        remarks: '',
        status: 'active'
      },
      fromFund: null,
      toFund: null,
      fund: {}, // preserved for legacy validator compatibility
      fundaccounts: [],
      loadingFromFund: false,
      loadingToFund: false,
      remarkSuggestions: [
        'Petty Cash Replenishment',
        'Bank Deposit',
        'Cash Withdrawal',
        'Inter-Branch Transfer',
        'Emergency Fund Allocation'
      ]
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  computed: {
    numericAmount() {
      const val = parseFloat(this.data.amount);
      return isNaN(val) ? 0 : val;
    },
    fromCurrentBalance() {
      if (!this.fromFund || this.fromFund.current_balance === undefined) return 0;
      return parseFloat(this.fromFund.current_balance) || 0;
    },
    toCurrentBalance() {
      if (!this.toFund || this.toFund.current_balance === undefined) return 0;
      return parseFloat(this.toFund.current_balance) || 0;
    },
    fromProjectedBalance() {
      return this.fromCurrentBalance - this.numericAmount;
    },
    toProjectedBalance() {
      return this.toCurrentBalance + this.numericAmount;
    },
    isSameAccount() {
      return Boolean(
        this.data.from_account_id &&
        this.data.to_account_id &&
        this.data.from_account_id == this.data.to_account_id
      );
    },
    isInsufficientBalance() {
      if (!this.fromFund || this.fromFund.current_balance === undefined) return false;
      return this.numericAmount > this.fromCurrentBalance;
    },
    isTransferValid() {
      return Boolean(
        this.data.transfer_date &&
        this.data.from_account_id &&
        this.data.to_account_id &&
        !this.isSameAccount &&
        this.numericAmount > 0 &&
        !this.isInsufficientBalance
      );
    },
    transferPercentage() {
      if (!this.fromCurrentBalance || this.fromCurrentBalance <= 0) return 0;
      const pct = (this.numericAmount / this.fromCurrentBalance) * 100;
      return Math.round(pct);
    },
    fromAccountName() {
      if (!this.data.from_account_id) return '';
      const acc = this.fundaccounts.find((a) => a.id == this.data.from_account_id);
      if (!acc) return '';
      return acc.name || acc.account_name || (acc.account_code ? `${acc.account_code} - ${acc.account_name}` : '');
    },
    toAccountName() {
      if (!this.data.to_account_id) return '';
      const acc = this.fundaccounts.find((a) => a.id == this.data.to_account_id);
      if (!acc) return '';
      return acc.name || acc.account_name || (acc.account_code ? `${acc.account_code} - ${acc.account_name}` : '');
    }
  },

  watch: {
    'data.from_account_id': {
      handler(newVal) {
        if (newVal) {
          this.fetchFromBalance(newVal);
        } else {
          this.fromFund = null;
          this.fund = {};
        }
      },
      deep: true
    },
    'data.to_account_id': {
      handler(newVal) {
        if (newVal) {
          this.fetchToBalance(newVal);
        } else {
          this.toFund = null;
        }
      },
      deep: true
    }
  },

  methods: {
    formatCurrency(amount) {
      if (amount === null || amount === undefined || isNaN(amount)) return '৳ 0.00';
      const num = Number(amount);
      const isNegative = num < 0;
      const formatted = Math.abs(num).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      return (isNegative ? '-৳ ' : '৳ ') + formatted;
    },

    amountInWords(num) {
      if (!num || num <= 0) return '';
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
    },

    fetchFromBalance(accountId) {
      this.loadingFromFund = true;
      axios
        .get(`/getfundbalance/${accountId}`)
        .then((response) => {
          this.fromFund = response.data;
          this.fund = response.data; // keep legacy reference
        })
        .catch((error) => {
          console.error('Error fetching source account balance:', error);
        })
        .finally(() => {
          this.loadingFromFund = false;
        });
    },

    fetchToBalance(accountId) {
      this.loadingToFund = true;
      axios
        .get(`/getfundbalance/${accountId}`)
        .then((response) => {
          this.toFund = response.data;
        })
        .catch((error) => {
          console.error('Error fetching destination account balance:', error);
        })
        .finally(() => {
          this.loadingToFund = false;
        });
    },

    getFundAccounts() {
      axios
        .get('/getfundaccounts')
        .then((response) => {
          const list = Array.isArray(response.data) ? response.data : [];
          this.fundaccounts = list.map((item) => {
            const formattedName = item.name && item.name.trim() !== ''
              ? item.name
              : (item.account_code ? `${item.account_code} - ${item.account_name}` : item.account_name);
            return {
              ...item,
              name: formattedName || `Account #${item.id}`
            };
          });
        })
        .catch((error) => {
          console.error('Error fetching fund accounts:', error);
        });
    },

    swapAccounts() {
      const fromId = this.data.from_account_id;
      const toId = this.data.to_account_id;
      this.data.from_account_id = toId;
      this.data.to_account_id = fromId;
    },

    setMaxBalance() {
      if (this.fromCurrentBalance > 0) {
        this.data.amount = this.fromCurrentBalance;
      }
    },

    clearAmount() {
      this.data.amount = null;
    },

    addAmount(val) {
      const current = this.numericAmount;
      this.data.amount = parseFloat((current + val).toFixed(2));
    },

    applyRemarkTag(tag) {
      if (!this.data.remarks) {
        this.data.remarks = tag;
      } else if (!this.data.remarks.includes(tag)) {
        this.data.remarks = `${this.data.remarks}, ${tag}`;
      }
    },

    resetForm() {
      this.data = {
        transfer_date: this.$filter.today(),
        from_account_id: '',
        to_account_id: '',
        amount: null,
        remarks: '',
        status: 'active'
      };
      this.fromFund = null;
      this.toFund = null;
      this.fund = {};
    },

    submit() {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();
        if (error > 0) {
          this.$toast(
            `You need to fill ${error} more empty mandatory fields`,
            'warning'
          );
          return false;
        }

        if (this.isSameAccount) {
          this.$toast('Source and Destination accounts cannot be the same', 'warning');
          return false;
        }

        if (this.isInsufficientBalance) {
          this.$toast('Insufficient funds in source account', 'error');
          return false;
        }

        if (res) {
          const form = document.getElementById('form');
          const formData = new FormData(form);
          formData.append('transfer_date', this.data.transfer_date);
          formData.append('from_account_id', this.data.from_account_id);
          formData.append('to_account_id', this.data.to_account_id);
          formData.append('amount', this.data.amount);
          formData.append('remarks', this.data.remarks || '');
          formData.append('status', this.data.status || 'active');

          if (this.data.id) {
            this.update(this.model, formData, this.data.id, true);
          } else {
            this.store(this.model, formData);
          }
        }
      });
    }
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`).then(() => {
        if (this.data.from_account_id) this.fetchFromBalance(this.data.from_account_id);
        if (this.data.to_account_id) this.fetchToBalance(this.data.to_account_id);
      });
    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }
    this.getFundAccounts();
  },

  validators: {
    'data.transfer_date': function (value = null) {
      return Validator.value(value).required('Transfer Date is required');
    },
    'data.from_account_id': function (value = null) {
      return Validator.value(value).required('From Account is required');
    },
    'data.to_account_id': function (value = null) {
      const vm = this;
      return Validator.value(value)
        .required('To Account is required')
        .custom(function () {
          if (vm.data.from_account_id && value && vm.data.from_account_id == value) {
            return 'Select another fund account';
          }
        });
    },
    'data.amount': function (value = null) {
      const vm = this;
      return Validator.value(value)
        .required('Amount is required')
        .custom(function () {
          const amount = parseFloat(value);
          if (isNaN(amount) || amount <= 0) {
            return 'Amount must be greater than 0';
          }
          if (vm.fromFund && vm.fromFund.current_balance !== undefined) {
            const balance = parseFloat(vm.fromFund.current_balance);
            if (balance < amount) {
              return 'Transaction failed due to insufficient fund balance.';
            }
          }
        });
    }
  }
};
</script>

<style scoped>
/* Hero Header */
.fund-transfer-hero {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-color: #e2e8f0 !important;
}

.hero-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #da511f 0%, #ff7a45 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}

/* Status indicator pills */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  border: 1px solid transparent;
  transition: all 0.2s ease;
}

.status-pill-success {
  background-color: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-color: rgba(16, 185, 129, 0.3);
}

.status-pill-danger {
  background-color: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.3);
}

.status-pill-warning {
  background-color: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.3);
}

.status-pill-neutral {
  background-color: rgba(100, 116, 139, 0.1);
  color: #475569;
  border-color: rgba(100, 116, 139, 0.2);
}

.status-pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #10b981;
  box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  animation: pulse-green 1.8s infinite;
}

@keyframes pulse-green {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}

/* Account Cards */
.account-card {
  background: #fbfcfe;
  transition: all 0.2s ease;
  overflow: visible !important;
  position: relative;
}

.account-card:hover {
  background: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.from-card {
  border-top: 3px solid #dc3545 !important;
  border-color: #e2e8f0;
}

.to-card {
  border-top: 3px solid #10b981 !important;
  border-color: #e2e8f0;
}

/* Global vs dropdown styling so that floating dropdown is always on top */
:global(.vs__dropdown-menu) {
  z-index: 999999 !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
  border-radius: 8px !important;
  border: 1px solid #cbd5e1 !important;
  max-height: 260px !important;
  background-color: #ffffff !important;
}

:global(.vs__dropdown-option) {
  padding: 8px 14px !important;
  font-size: 13.5px !important;
  color: #1e293b !important;
}

:global(.vs__dropdown-option--highlight) {
  background-color: #da511f !important;
  color: #ffffff !important;
}

.swap-btn {
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.swap-btn:hover {
  transform: translateY(-1px);
}

/* Transfer Connector Icon */
.transfer-connector-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #ffffff;
  border: 2px dashed #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: #64748b;
  transition: all 0.3s ease;
}

.transfer-connector-icon.active {
  border: 2px solid #da511f;
  background: #da511f;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(218, 81, 31, 0.25);
}

/* Amount Input Box */
.amount-input-box {
  background: #f8fafc;
  border-color: #e2e8f0 !important;
}

.amount-input-box input {
  font-size: 1.75rem !important;
  letter-spacing: -0.5px;
}

.amount-input-box input:focus {
  border-color: #da511f;
  box-shadow: 0 0 0 3px rgba(218, 81, 31, 0.15);
}

.preset-btn {
  background: #ffffff;
  color: #334155;
  font-weight: 600;
  font-size: 12px;
  transition: all 0.15s ease;
  border-color: #cbd5e1 !important;
}

.preset-btn:hover {
  background: #da511f;
  color: #ffffff;
  border-color: #da511f !important;
  transform: translateY(-1px);
}

.preset-tag-btn {
  font-size: 11.5px;
  cursor: pointer;
  transition: all 0.15s ease;
  border-color: #e2e8f0 !important;
}

.preset-tag-btn:hover {
  background-color: #112c47 !important;
  color: #ffffff !important;
  border-color: #112c47 !important;
}

/* Voucher Preview Card (Sidebar) */
.voucher-preview-card {
  border-color: #e2e8f0 !important;
}

.bg-gradient-navy {
  background: linear-gradient(135deg, #112c47 0%, #1e4265 100%);
}

.voucher-amount-hero {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
}

.ledger-row {
  background: #ffffff;
  border-color: #e2e8f0 !important;
  transition: all 0.2s ease;
}

.ledger-row:hover {
  border-color: #cbd5e1 !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

/* Action Footer */
.action-footer-card {
  border-color: #e2e8f0 !important;
}

.execute-btn {
  background: linear-gradient(135deg, #da511f 0%, #ff6b35 100%);
  border: none;
  letter-spacing: 0.2px;
  transition: all 0.2s ease;
}

.execute-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #c44516 0%, #e85822 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(218, 81, 31, 0.35) !important;
}

.execute-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-white {
  background: #ffffff;
}

.btn-xs {
  font-size: 11px;
}

.border-dashed {
  border-style: dashed !important;
}
</style>