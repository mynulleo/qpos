<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" printArea="moneyreceipt_print">
    <!-- 💻 Interactive Executive Dashboard View (Screen) -->
    <div class="payment-view-wrapper d-print-none">

      <!-- 🌟 1. Top Executive Payment Hero Banner -->
      <div class="card border-0 shadow-sm mb-4 payment-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i :class="data.payment_type === 'Receive' ? 'fas fa-arrow-circle-down fs-2 text-success-light' : 'fas fa-arrow-circle-up fs-2 text-info-light'"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white font-monospace">#{{ data.payslipno || data.id }}</h4>
                  <span
                    class="badge px-3 py-1 font-monospace"
                    :class="data.payment_type === 'Receive' ? 'bg-success text-white' : 'bg-primary text-white'"
                  >
                    <i :class="data.payment_type === 'Receive' ? 'fas fa-arrow-down me-1' : 'fas fa-arrow-up me-1'"></i>
                    {{ data.payment_type === 'Receive' ? $t('Money Received (Inflow)') : $t('Payment Disbursed (Outflow)') }}
                  </span>
                  <span class="badge bg-light text-dark fw-bold">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    {{ data.status ? $t(data.status) : $t('Success') }}
                  </span>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace">
                    <i class="fas fa-wallet me-1"></i> {{ data.payment_method ? $t(data.payment_method) : $t('Cash') }}
                  </span>
                  <span v-if="data.can_edit === false" class="badge bg-warning text-dark">
                    <i class="fas fa-lock me-1"></i> {{ data.lock_reason ? $t(data.lock_reason) : $t('System Linked') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>{{ $t('Date') }}: <strong class="text-white">{{ data.payment_date || $t('N/A') }}</strong></span>
                  <span><i class="fas fa-user-tag me-1"></i>{{ partyCategory }}: <strong class="text-white">{{ partyDisplayName }}</strong></span>
                  <span v-if="data.fund_account"><i class="fas fa-university me-1"></i>{{ $t('Fund') }}: <strong class="text-white">{{ data.fund_account.account_name }} ({{ data.fund_account.account_code }})</strong></span>
                  <span v-if="data.payment_details && data.payment_details.length"><i class="fas fa-layer-group me-1"></i>{{ $t('Settled References') }}: <strong class="text-white">{{ data.payment_details.length }}</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 2. Top Metric KPI Summary Cards -->
      <div class="row g-3 mb-4">
        <!-- Card 1: Payment Amount -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Payment Amount') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-coins"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-success font-monospace">৳ {{ formatPrice(data.amount) }}</h4>
              <small class="text-muted" v-if="parseFloat(data.discount || 0) > 0">
                <i class="fas fa-tag me-1 text-warning"></i>{{ $t('Discount') }}: ৳ {{ formatPrice(data.discount) }}
              </small>
            </div>
          </div>
        </div>

        <!-- Card 2: Total Reference Bill Amount -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important;">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Invoiced / Bill') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-file-invoice-dollar"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-primary font-monospace">৳ {{ formatPrice(totalBillAmount) }}</h4>
              <small class="text-muted">
                {{ (data.payment_details && data.payment_details.length) || 0 }} {{ $t('reference line(s)') }}
              </small>
            </div>
          </div>
        </div>

        <!-- Card 3: Total Settled / Adjusted -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid #6366f1 !important;">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Settled') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-hand-holding-usd"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace">৳ {{ formatPrice(totalSettledAmount) }}</h4>
              <small class="text-muted">
                {{ $t('Applied to bill ledger') }}
              </small>
            </div>
          </div>
        </div>

        <!-- Card 4: Remaining Outstanding Due -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100" :style="{ borderLeft: (totalDueAmount > 0 ? '4px solid #ef4444 !important' : '4px solid #10b981 !important') }">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Remaining Due') }}</span>
                <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" :class="totalDueAmount > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success'">
                  <i :class="totalDueAmount > 0 ? 'fas fa-exclamation-triangle' : 'fas fa-check-double'"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 font-monospace" :class="totalDueAmount > 0 ? 'text-danger' : 'text-success'">
                ৳ {{ formatPrice(totalDueAmount) }}
              </h4>
              <small :class="totalDueAmount > 0 ? 'text-danger' : 'text-success'">
                {{ totalDueAmount > 0 ? $t('Outstanding Balance') : $t('Fully Settled') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📑 3. Settlement & Linked Documents Table Card (Full Width) -->
      <div class="card border-0 shadow-sm mb-4 info-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-layer-group"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Settlement & Reference Documents') }}</h6>
              <small class="text-muted" style="font-size: 11.5px;">
                {{ $t('Inspect line items, prices or open full document.') }}
              </small>
            </div>
          </div>
          <span class="badge bg-secondary font-monospace" v-if="data.payment_details">
            {{ data.payment_details.length }} {{ $t('Linked Record(s)') }}
          </span>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-settlement-table">
              <thead class="table-light">
                <tr>
                  <th style="width: 45px;" class="text-center">#</th>
                  <th>{{ $t('Reference Type') }}</th>
                  <th>{{ $t('Document No / Ref') }}</th>
                  <th>{{ $t('Date') }}</th>
                  <th>{{ $t('Account / Head') }}</th>
                  <th class="text-end" style="width: 140px;">{{ $t('Bill Amount (৳)') }}</th>
                  <th class="text-end" style="width: 150px;">{{ $t('Paid / Settled (৳)') }}</th>
                  <th class="text-end" style="width: 140px;">{{ $t('Remaining Due (৳)') }}</th>
                  <th class="text-center" style="width: 110px;">{{ $t('Status') }}</th>
                  <th class="text-center" style="width: 160px;">{{ $t('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!data.payment_details || data.payment_details.length === 0">
                  <td colspan="10" class="text-center py-4 text-muted">
                    <i class="fas fa-folder-open fs-3 d-block mb-2 text-muted"></i>
                    {{ $t('No specific reference bills linked to this payment.') }}
                  </td>
                </tr>
                <tr v-for="(pd, index) in data.payment_details" :key="index">
                  <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>
                  <td>
                    <span class="badge px-2 py-1" :class="refBadgeClass(pd.reference_type)">
                      <i :class="refIconClass(pd.reference_type)" class="me-1"></i>
                      {{ $t(pd.reference_type) }}
                    </span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="fw-bold font-monospace text-dark">
                        {{ pd.ref_doc_no || (pd.reference_info || ('#' + pd.reference_id)) }}
                      </span>
                      <a
                        v-if="pd.ref_url"
                        :href="pd.ref_url"
                        target="_blank"
                        class="text-primary text-decoration-none small"
                        :title="$t('Open document in new tab')"
                      >
                        <i class="fas fa-external-link-alt" style="font-size: 11px;"></i>
                      </a>
                    </div>
                    <small class="text-muted d-block" v-if="pd.reference_info && pd.ref_doc_no && pd.reference_info !== pd.ref_doc_no">
                      {{ pd.reference_info }}
                    </small>
                  </td>
                  <td class="font-monospace small text-muted">
                    {{ pd.ref_date || '-' }}
                  </td>
                  <td>
                    <div v-if="pd.account">
                      <span class="fw-medium text-dark small">{{ pd.account.account_name }}</span>
                      <small class="text-muted font-monospace d-block">({{ pd.account.account_code }})</small>
                    </div>
                    <span class="text-muted small" v-else>-</span>
                  </td>
                  <!-- Numbers formatted cleanly without ৳ prefix as requested -->
                  <td class="text-end font-monospace text-dark">
                    {{ formatPrice(pd.amount) }}
                  </td>
                  <td class="text-end font-monospace fw-bold text-success">
                    {{ formatPrice(pd.adjust_amount || pd.paid_amount || pd.amount) }}
                  </td>
                  <td class="text-end font-monospace" :class="parseFloat(pd.due_amount) > 0 ? 'text-danger fw-semibold' : 'text-muted'">
                    {{ formatPrice(pd.due_amount) }}
                  </td>
                  <td class="text-center">
                    <span class="badge" :class="pd.is_closed ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'">
                      <i :class="pd.is_closed ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                      {{ pd.is_closed ? $t('Settled') : $t('Partial') }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <!-- Quick Inspect Button -->
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1 px-2 py-1"
                        @click="inspectReference(pd)"
                        :title="$t('Inspect items & details')"
                      >
                        <i class="fas fa-eye"></i>
                        <span>{{ $t('Details') }}</span>
                      </button>

                      <!-- Direct Link Button -->
                      <a
                        v-if="pd.ref_url"
                        :href="pd.ref_url"
                        target="_blank"
                        class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center px-2 py-1"
                        :title="$t('Open full page')"
                      >
                        <i class="fas fa-external-link-alt"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold" v-if="data.payment_details && data.payment_details.length > 0">
                <tr>
                  <td colspan="5" class="text-end text-uppercase small">{{ $t('Total (৳)') }}:</td>
                  <td class="text-end font-monospace text-dark">{{ formatPrice(totalBillAmount) }}</td>
                  <td class="text-end font-monospace text-success">{{ formatPrice(totalSettledAmount) }}</td>
                  <td class="text-end font-monospace" :class="totalDueAmount > 0 ? 'text-danger' : 'text-success'">
                    {{ formatPrice(totalDueAmount) }}
                  </td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 🏛️ 4. Two Columns Layout: Left (Party & Financial Ledger Info) | Right (Money Receipt Preview) -->
      <div class="row g-4">
        <!-- Left Column: Details & Beneficiary (5-col) -->
        <div class="col-xl-5 col-lg-5">
          <!-- Beneficiary / Party Profile Card -->
          <div class="card border-0 shadow-sm mb-4 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-user-circle"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Party & Beneficiary Details') }}</h6>
                <small class="text-muted" style="font-size: 11.5px;">{{ partyCategory }}</small>
              </div>
            </div>
            <div class="card-body p-3">
              <div class="d-flex align-items-start gap-3 mb-3 p-3 rounded bg-light border">
                <div class="avatar-box rounded-circle theme-bg text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px; min-width: 48px;">
                  {{ partyInitial }}
                </div>
                <div>
                  <h6 class="fw-bold text-dark mb-1">{{ partyDisplayName }}</h6>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1">
                    {{ partyCategory }}
                  </span>
                  <div class="text-muted small font-monospace" v-if="data.payto?.mobile">
                    <i class="fas fa-phone-alt me-1 text-muted"></i>
                    <a :href="'tel:' + data.payto.mobile" class="text-decoration-none text-dark">{{ data.payto.mobile }}</a>
                  </div>
                </div>
              </div>

              <table class="table table-sm table-borderless align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label" style="width: 120px;"><i class="fas fa-map-marker-alt me-2 text-muted"></i>{{ $t('Address') }}</td>
                    <td class="spec-value text-dark">{{ data.payto?.address || $t('N/A') }}</td>
                  </tr>
                  <tr v-if="data.payto?.info">
                    <td class="spec-label"><i class="fas fa-info-circle me-2 text-muted"></i>{{ $t('Other Info') }}</td>
                    <td class="spec-value text-muted">{{ data.payto.info }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Payment Channel & Ledger Card -->
          <div class="card border-0 shadow-sm mb-4 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-university"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Payment Mode & Channel') }}</h6>
                <small class="text-muted" style="font-size: 11.5px;">{{ $t('Banking and fund account ledger specifications') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label"><i class="far fa-calendar-alt me-2 text-muted"></i>{{ $t('Payment Date') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.payment_date }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-exchange-alt me-2 text-muted"></i>{{ $t('Transaction Type') }}</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.payment_type === 'Receive' ? 'bg-success' : 'bg-primary'">
                        {{ data.payment_type ? $t(data.payment_type) : '' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-money-check-alt me-2 text-muted"></i>{{ $t('Payment Method') }}</td>
                    <td class="spec-value fw-bold text-dark">{{ data.payment_method ? $t(data.payment_method) : $t('Cash') }}</td>
                  </tr>
                  <tr v-if="data.mbanking_type">
                    <td class="spec-label"><i class="fas fa-mobile-alt me-2 text-muted"></i>{{ $t('MFS / Mobile Type') }}</td>
                    <td class="spec-value font-monospace fw-bold text-primary">{{ data.mbanking_type }}</td>
                  </tr>
                  <tr v-if="data.bank">
                    <td class="spec-label"><i class="fas fa-university me-2 text-muted"></i>{{ $t('Bank Name') }}</td>
                    <td class="spec-value fw-semibold">{{ data.bank.bank_name }}</td>
                  </tr>
                  <tr v-if="data.branch">
                    <td class="spec-label"><i class="fas fa-code-branch me-2 text-muted"></i>{{ $t('Branch') }}</td>
                    <td class="spec-value">{{ data.branch }}</td>
                  </tr>
                  <tr v-if="data.accountno">
                    <td class="spec-label"><i class="fas fa-credit-card me-2 text-muted"></i>{{ $t('Account No') }}</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.accountno }}</td>
                  </tr>
                  <tr v-if="data.chequeno">
                    <td class="spec-label"><i class="fas fa-money-check me-2 text-muted"></i>{{ $t('Cheque No') }}</td>
                    <td class="spec-value font-monospace">{{ data.chequeno }}</td>
                  </tr>
                  <tr v-if="data.trxid">
                    <td class="spec-label"><i class="fas fa-receipt me-2 text-muted"></i>{{ $t('Transaction ID') }}</td>
                    <td class="spec-value font-monospace text-primary fw-bold">{{ data.trxid }}</td>
                  </tr>
                  <tr v-if="data.fund_account">
                    <td class="spec-label"><i class="fas fa-wallet me-2 text-muted"></i>{{ $t('Fund Account') }}</td>
                    <td class="spec-value">
                      <span class="fw-bold text-dark">{{ data.fund_account.account_name }}</span>
                      <small class="text-muted font-monospace ms-1">({{ data.fund_account.account_code }})</small>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-tag me-2 text-muted"></i>{{ $t('Discount') }}</td>
                    <td class="spec-value font-monospace text-warning fw-bold">৳ {{ formatPrice(data.discount || 0) }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-coins me-2 text-muted"></i>{{ $t('Net Amount') }}</td>
                    <td class="spec-value font-monospace fs-6 fw-bold text-success">৳ {{ formatPrice(data.amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right Column: Official Voucher / Receipt Preview (7-col) -->
        <div class="col-xl-7 col-lg-7">
          <div class="card border-0 shadow-sm info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                  <i class="fas fa-receipt"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">
                    {{ data.payment_type === 'Receive' ? $t('Money Receipt') : $t('Payment Voucher') }}
                  </h6>
                  <small class="text-muted" style="font-size: 11.5px;">{{ $t('Official printable document') }}</small>
                </div>
              </div>
              <button
                type="button"
                class="btn btn-sm btn-dark shadow-sm d-flex align-items-center gap-1"
                @click="printReceipt"
                :title="$t('Print this receipt')"
              >
                <i class="fas fa-print"></i>
                <span>{{ $t('Print') }}</span>
              </button>
            </div>

            <div class="card-body p-3 bg-light bg-opacity-50">
              <!-- Official Printable Receipt Container -->
              <div class="receipt-container" id="moneyreceipt_print">
                <!-- Embedded isolated print styles so popup print window has 100% style fidelity -->
                <component is="style">
                  @page {
                    size: A4 portrait;
                    margin: 8mm 10mm;
                  }
                  * {
                    box-sizing: border-box !important;
                  }
                  body {
                    margin: 0 !important;
                    padding: 10px !important;
                    background: #ffffff !important;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                    color: #1e293b !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                  }
                  .receipt-content {
                    width: 100% !important;
                    max-width: 820px !important;
                    margin: 0 auto !important;
                    background: #ffffff !important;
                    padding: 24px 28px !important;
                    border: 2px solid #112C47 !important;
                    border-radius: 6px !important;
                    box-shadow: none !important;
                  }
                  .receipt-header {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    border-bottom: 2px solid #112C47 !important;
                    padding-bottom: 14px !important;
                    margin-bottom: 12px !important;
                  }
                  .logo-section {
                    width: 25% !important;
                    text-align: left !important;
                  }
                  .logo-img {
                    max-height: 65px !important;
                    max-width: 170px !important;
                    object-fit: contain !important;
                  }
                  .title-section {
                    width: 40% !important;
                    text-align: center !important;
                  }
                  .receipt-badge {
                    display: inline-block !important;
                    background-color: #112C47 !important;
                    color: #ffffff !important;
                    font-weight: 800 !important;
                    font-size: 16px !important;
                    letter-spacing: 1.5px !important;
                    padding: 5px 20px !important;
                    border-radius: 4px !important;
                    text-transform: uppercase !important;
                  }
                  .receipt-copy-subtitle {
                    font-size: 11px !important;
                    margin-top: 4px !important;
                    color: #64748b !important;
                    font-weight: 600 !important;
                  }
                  .info-section {
                    width: 35% !important;
                    text-align: right !important;
                  }
                  .company-name {
                    font-size: 16px !important;
                    font-weight: bold !important;
                    color: #0f172a !important;
                    margin: 0 !important;
                  }
                  .company-sub {
                    font-size: 11px !important;
                    line-height: 1.35 !important;
                    color: #475569 !important;
                    margin: 2px 0 0 0 !important;
                  }
                  .receipt-meta-row {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    background: #f8fafc !important;
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 4px !important;
                    padding: 6px 14px !important;
                    margin-bottom: 16px !important;
                    font-size: 12px !important;
                  }
                  .receipt-field-row {
                    display: flex !important;
                    flex-direction: row !important;
                    align-items: baseline !important;
                    margin-bottom: 12px !important;
                    width: 100% !important;
                  }
                  .field-label {
                    width: 210px !important;
                    min-width: 190px !important;
                    font-size: 13px !important;
                    font-weight: 700 !important;
                    color: #334155 !important;
                    white-space: nowrap !important;
                  }
                  .field-line {
                    flex: 1 !important;
                    border-bottom: 1px dashed #64748b !important;
                    padding-bottom: 2px !important;
                    min-height: 22px !important;
                    display: flex !important;
                    align-items: baseline !important;
                    flex-wrap: wrap !important;
                  }
                  .field-value {
                    font-size: 13.5px !important;
                    color: #0f172a !important;
                  }
                  .receipt-mini-table {
                    font-size: 11px !important;
                    width: 100% !important;
                    border-collapse: collapse !important;
                    margin-top: 14px !important;
                    margin-bottom: 6px !important;
                  }
                  .receipt-mini-table th {
                    background: #f1f5f9 !important;
                    font-weight: 700 !important;
                    color: #334155 !important;
                    border: 1px solid #cbd5e1 !important;
                    padding: 5px 8px !important;
                  }
                  .receipt-mini-table td {
                    border: 1px solid #cbd5e1 !important;
                    padding: 5px 8px !important;
                  }
                  .receipt-signature-section {
                    display: flex !important;
                    flex-direction: row !important;
                    justify-content: space-between !important;
                    align-items: flex-end !important;
                    margin-top: 45px !important;
                    padding-top: 10px !important;
                    width: 100% !important;
                  }
                  .sig-box {
                    width: 28% !important;
                    text-align: center !important;
                  }
                  .sig-line {
                    border-top: 1px solid #334155 !important;
                    margin-bottom: 5px !important;
                    width: 100% !important;
                  }
                  .sig-title {
                    font-size: 11.5px !important;
                    font-weight: 700 !important;
                    color: #1e293b !important;
                  }
                  .sig-img-wrap {
                    height: 48px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    margin-bottom: 3px !important;
                  }
                  .sig-img {
                    max-height: 44px !important;
                    max-width: 130px !important;
                    object-fit: contain !important;
                  }
                  .receipt-footer {
                    margin-top: 16px !important;
                    padding-top: 8px !important;
                    text-align: center !important;
                    border-top: 1px dashed #cbd5e1 !important;
                    font-size: 10.5px !important;
                    color: #64748b !important;
                  }
                </component>

                <div class="receipt-content mx-auto">
                  <!-- Header with Logo, Title Badge and Contact Info -->
                  <div class="receipt-header">
                    <div class="logo-section">
                      <img :src="$root.site?.logo_two || $root.site?.logo || '/images/logo.png'" alt="Logo" class="logo-img" />
                    </div>
                    <div class="title-section">
                      <div class="receipt-badge">
                        <span v-if="data.payment_type === 'Receive'">{{ $t('MONEY RECEIPT') }}</span>
                        <span v-else>{{ $t('PAYMENT VOUCHER') }}</span>
                      </div>
                      <div class="receipt-copy-subtitle">
                        <span>{{ data.payment_type === 'Receive' ? $t('Customer / Office Copy') : $t('Payee / Office Copy') }}</span>
                      </div>
                    </div>
                    <div class="info-section">
                      <h4 class="company-name">{{ $root.site?.title || $root.site?.name || 'QPOS STORE' }}</h4>
                      <p class="company-sub" v-if="$root.site?.address">{{ $root.site?.address }}</p>
                      <p class="company-sub" v-if="$root.site?.mobile1 || $root.site?.contact_number">
                        <strong>{{ $t('Phone:') }}</strong> {{ $root.site?.mobile1 || $root.site?.contact_number }}
                      </p>
                      <p class="company-sub" v-if="$root.site?.contact_email">
                        <strong>{{ $t('Email:') }}</strong> {{ $root.site?.contact_email }}
                      </p>
                    </div>
                  </div>

                  <!-- Receipt Meta Bar -->
                  <div class="receipt-meta-row">
                    <div>
                      <span class="text-muted fw-bold">{{ $t('Receipt No:') }}</span>
                      <span class="font-monospace fw-bold text-dark fs-6 ms-1">#{{ data.payslipno || data.id }}</span>
                    </div>
                    <div>
                      <span class="badge px-3 py-1 font-monospace" :class="data.payment_type === 'Receive' ? 'bg-success text-white' : 'bg-primary text-white'">
                        {{ data.payment_type === 'Receive' ? $t('Money Received') : $t('Payment Disbursed') }}
                      </span>
                    </div>
                    <div>
                      <span class="text-muted fw-bold">{{ $t('Date:') }}</span>
                      <span class="font-monospace fw-bold text-dark fs-6 ms-1">{{ data.payment_date }}</span>
                    </div>
                  </div>

                  <!-- Receipt Body Rows -->
                  <div class="receipt-body">
                    <!-- 1. Received From / Paid To -->
                    <div class="receipt-field-row">
                      <span class="field-label">{{ data.payment_type === 'Receive' ? $t('Received with thanks from:') : $t('Paid to:') }}</span>
                      <div class="field-line">
                        <span class="field-value fw-bold text-dark">
                          {{ partyDisplayName }}
                        </span>
                        <span class="field-value text-muted fw-normal ms-2" v-if="data.payto?.mobile">
                          ({{ data.payto.mobile }})
                        </span>
                        <span class="field-value text-muted fw-normal ms-2" v-if="data.payto?.address">
                          - {{ data.payto.address }}
                        </span>
                      </div>
                    </div>

                    <!-- 2. The Sum of Taka (Amount) -->
                    <div class="receipt-field-row">
                      <span class="field-label">{{ $t('Amount (in Figures):') }}</span>
                      <div class="field-line">
                        <span class="field-value font-monospace fw-bold text-dark fs-5 text-success">
                          ৳ {{ formatPrice(data.amount) }}
                        </span>
                        <span class="badge bg-light text-dark border ms-2" v-if="parseFloat(data.discount || 0) > 0">
                          {{ $t('Discount:') }} ৳ {{ formatPrice(data.discount) }}
                        </span>
                      </div>
                    </div>

                    <!-- 3. In Words -->
                    <div class="receipt-field-row">
                      <span class="field-label">{{ $t('Amount (in Words):') }}</span>
                      <div class="field-line">
                        <span class="field-value fst-italic text-dark fw-bold">
                          {{ currencyToWordsBD(data.amount || 0) }} {{ $t('Taka Only') }}.
                        </span>
                      </div>
                    </div>

                    <!-- 4. Payment Method & Details -->
                    <div class="receipt-field-row">
                      <span class="field-label">{{ $t('Payment Mode:') }}</span>
                      <div class="field-line">
                        <span class="field-value fw-semibold text-dark">
                          {{ data.payment_method ? $t(data.payment_method) : $t('Cash') }}
                        </span>
                        <span class="field-value text-muted font-monospace ms-2" v-if="paymentChannelInfo">
                          ({{ paymentChannelInfo }})
                        </span>
                      </div>
                    </div>

                    <!-- 5. On Account Of / Purpose -->
                    <div class="receipt-field-row">
                      <span class="field-label">{{ $t('On Account Of / For:') }}</span>
                      <div class="field-line">
                        <span class="field-value text-dark fw-semibold">
                          {{ paymentPurposeText }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Itemized Settlement Details Table -->
                  <!-- USER SPEC: Bill Amount (৳), Paid / Settled (৳), Remaining Due (৳) in header, NO (৳) in rows! -->
                  <div class="mt-3" v-if="data.payment_details && data.payment_details.length > 0">
                    <table class="table table-sm table-bordered receipt-mini-table mb-1">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 35px;" class="text-center">#</th>
                          <th>{{ $t('Reference / Description') }}</th>
                          <th class="text-end" style="width: 125px;">{{ $t('Bill Amount (৳)') }}</th>
                          <th class="text-end" style="width: 135px;">{{ $t('Paid / Settled (৳)') }}</th>
                          <th class="text-end" style="width: 125px;">{{ $t('Remaining Due (৳)') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(pd, pIdx) in data.payment_details" :key="pIdx">
                          <td class="text-center font-monospace">{{ pIdx + 1 }}</td>
                          <td>
                            <span class="fw-bold">{{ $t(pd.reference_type) }}</span>
                            <span class="font-monospace text-secondary ms-1" v-if="pd.ref_doc_no || pd.reference_info">
                              ({{ pd.ref_doc_no || pd.reference_info }})
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" v-if="pd.is_closed">{{ $t('Settled') }}</span>
                          </td>
                          <!-- (৳) symbol removed from all numbers in rows as requested -->
                          <td class="text-end font-monospace">{{ formatPrice(pd.amount) }}</td>
                          <td class="text-end font-monospace fw-bold text-success">{{ formatPrice(pd.adjust_amount || pd.paid_amount || pd.amount) }}</td>
                          <td class="text-end font-monospace text-danger">{{ formatPrice(pd.due_amount) }}</td>
                        </tr>
                      </tbody>
                      <tfoot class="table-light fw-bold" v-if="data.payment_details.length > 1">
                        <tr>
                          <td colspan="2" class="text-end text-uppercase small">{{ $t('Total (৳)') }}:</td>
                          <td class="text-end font-monospace">{{ formatPrice(totalBillAmount) }}</td>
                          <td class="text-end font-monospace text-success">{{ formatPrice(totalSettledAmount) }}</td>
                          <td class="text-end font-monospace text-danger">{{ formatPrice(totalDueAmount) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <!-- Signatures Section -->
                  <div class="receipt-signature-section">
                    <div class="sig-box">
                      <div class="sig-line"></div>
                      <div class="sig-title">{{ data.payment_type === 'Receive' ? $t('Customer / Received By') : $t('Payee / Received By') }}</div>
                    </div>
                    <div class="sig-box">
                      <div class="sig-line"></div>
                      <div class="sig-title">{{ $t('Prepared By') }}</div>
                    </div>
                    <div class="sig-box">
                      <div class="sig-img-wrap" v-if="$root.site?.authorized_signature_two || $root.site?.authorized_signature">
                        <img :src="$root.site?.authorized_signature_two || $root.site?.authorized_signature" alt="Authorized Seal" class="sig-img" />
                      </div>
                      <div class="sig-line"></div>
                      <div class="sig-title">{{ $t('Authorized Signature') }}</div>
                    </div>
                  </div>

                  <!-- Receipt Footer -->
                  <div class="receipt-footer">
                    <span>* {{ $t('Thank you for your business. This is a computer generated official money receipt.') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔍 5. Interactive Reference Quick-Inspect Modal -->
    <div
      v-if="showRefModal"
      class="modal fade show d-block ref-inspect-modal"
      tabindex="-1"
      style="background: rgba(15, 23, 42, 0.7); z-index: 1060;"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px; overflow: hidden;">
          <!-- Modal Header -->
          <div class="modal-header py-3 px-4 theme-bg text-white border-0">
            <div class="d-flex align-items-center gap-2">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i :class="refIconClass(activeRefType)" class="fs-5"></i>
              </div>
              <div>
                <h6 class="modal-title fw-bold mb-0 text-white font-monospace">
                  [{{ $t(activeRefType) }}] {{ activeRefDocNo }}
                </h6>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ $t('Linked Document Specification & Itemized Breakdown') }}
                </small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="closeRefModal"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 bg-light bg-opacity-25">
            <!-- Loading state -->
            <div v-if="loadingRefModal" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2 text-muted small">{{ $t('Fetching reference document details from database...') }}</p>
            </div>

            <!-- Loaded Document Details -->
            <div v-else-if="activeRefData">
              <!-- Document Metadata Grid -->
              <div class="row g-2 mb-3">
                <div class="col-md-4 col-6">
                  <div class="p-2 rounded bg-white border">
                    <small class="text-muted d-block" style="font-size: 11px;">{{ $t('Document No') }}</small>
                    <span class="fw-bold font-monospace text-dark">{{ activeRefData.doc_no || $t('N/A') }}</span>
                  </div>
                </div>
                <div class="col-md-4 col-6">
                  <div class="p-2 rounded bg-white border">
                    <small class="text-muted d-block" style="font-size: 11px;">{{ $t('Date') }}</small>
                    <span class="fw-semibold text-dark font-monospace">{{ activeRefData.date || $t('N/A') }}</span>
                  </div>
                </div>
                <div class="col-md-4 col-12">
                  <div class="p-2 rounded bg-white border">
                    <small class="text-muted d-block" style="font-size: 11px;">{{ $t('Status') }}</small>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                      {{ activeRefData.status ? $t(activeRefData.status) : $t('Active') }}
                    </span>
                  </div>
                </div>
                <div class="col-md-6 col-12" v-if="activeRefData.party_name">
                  <div class="p-2 rounded bg-white border">
                    <small class="text-muted d-block" style="font-size: 11px;">{{ $t('Client / Supplier / Employee') }}</small>
                    <span class="fw-bold text-dark">{{ activeRefData.party_name }}</span>
                    <small class="text-muted font-monospace ms-2" v-if="activeRefData.party_mobile">({{ activeRefData.party_mobile }})</small>
                  </div>
                </div>
                <div class="col-md-6 col-12">
                  <div class="p-2 rounded bg-white border d-flex justify-content-between align-items-center">
                    <div>
                      <small class="text-muted d-block" style="font-size: 11px;">{{ $t('Total Document Amount') }}</small>
                      <span class="fw-bold text-dark font-monospace fs-6">৳ {{ formatPrice(activeRefData.total_amount) }}</span>
                    </div>
                    <div class="text-end" v-if="activeRefData.due_amount !== undefined">
                      <small class="text-muted d-block" style="font-size: 11px;">{{ $t('Remaining Due') }}</small>
                      <span class="fw-bold font-monospace" :class="parseFloat(activeRefData.due_amount) > 0 ? 'text-danger' : 'text-success'">
                        ৳ {{ formatPrice(activeRefData.due_amount) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Line Items Table (Products / Services / Detail breakdown) -->
              <div class="card border-0 shadow-sm mb-3" v-if="activeRefData.items && activeRefData.items.length > 0">
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                  <span class="fw-bold small text-dark"><i class="fas fa-boxes me-1 text-primary"></i> {{ $t('Itemized Product / Service List') }}</span>
                  <span class="badge bg-secondary font-monospace">{{ activeRefData.items.length }} {{ $t('Items') }}</span>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 35px;" class="text-center">#</th>
                          <th>{{ $t('Item / Description') }}</th>
                          <th>{{ $t('Barcode / Code') }}</th>
                          <th class="text-center" style="width: 80px;">{{ $t('Qty') }}</th>
                          <th class="text-end" style="width: 110px;">{{ $t('Unit Price (৳)') }}</th>
                          <th class="text-end" style="width: 120px;">{{ $t('Total (৳)') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(it, iIdx) in activeRefData.items" :key="iIdx">
                          <td class="text-center font-monospace text-muted">{{ iIdx + 1 }}</td>
                          <td class="fw-semibold text-dark">{{ it.title }}</td>
                          <td class="font-monospace text-muted">{{ it.code || '-' }}</td>
                          <td class="text-center font-monospace fw-bold">{{ it.qty }} <small class="text-muted fw-normal">{{ it.unit ? $t(it.unit) : '' }}</small></td>
                          <td class="text-end font-monospace">{{ formatPrice(it.unit_price) }}</td>
                          <td class="text-end font-monospace fw-bold text-dark">{{ formatPrice(it.total_price) }}</td>
                        </tr>
                      </tbody>
                      <tfoot class="table-light fw-bold" v-if="activeRefData.items.length > 1">
                        <tr>
                          <td colspan="3" class="text-end text-uppercase small">{{ $t('Total') }}:</td>
                          <td class="text-center font-monospace">{{ modalTotalQty }}</td>
                          <td></td>
                          <td class="text-end font-monospace text-dark">৳ {{ formatPrice(modalTotalSum) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Note / Remarks Box -->
              <div class="alert alert-light border mb-0 py-2 px-3 small text-muted d-flex align-items-center gap-2" v-if="activeRefData.note">
                <i class="fas fa-info-circle text-primary fs-5"></i>
                <div>
                  <strong>{{ $t('Note / Memo') }}:</strong> {{ activeRefData.note }}
                </div>
              </div>
            </div>

            <!-- Error or Empty state -->
            <div v-else class="text-center py-4 text-muted">
              <i class="fas fa-exclamation-circle text-warning fs-3 mb-2"></i>
              <p class="mb-0">{{ $t('Could not load details for this reference.') }}</p>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center">
            <div>
              <a
                v-if="activeRefData && activeRefData.url"
                :href="activeRefData.url"
                target="_blank"
                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm"
              >
                <i class="fas fa-external-link-alt"></i>
                <span>{{ $t('Open Full Document Page') }}</span>
              </a>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" @click="closeRefModal">
              {{ $t('Close') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "payment";

export default {
  name: "PaymentView",
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],

      // Modal state for reference inspection
      showRefModal: false,
      loadingRefModal: false,
      activeRefType: "",
      activeRefId: null,
      activeRefDocNo: "",
      activeRefData: null,
    };
  },
  computed: {
    partyDisplayName() {
      return (
        this.data.payto?.name ||
        this.data.supplier?.org_name ||
        this.data.supplier?.name ||
        this.data.client?.name ||
        this.data.employee?.full_name ||
        this.data.agent?.full_name ||
        this.$t('Walk-in Customer / Client')
      );
    },
    partyCategory() {
      if (this.data.supplier_id || this.data.supplier) return this.$t('Supplier / Vendor');
      if (this.data.client_id || this.data.client) return this.$t('Customer / Client');
      if (this.data.employee_id || this.data.employee) return this.$t('Employee / Staff');
      if (this.data.agent_id || this.data.agent) return this.$t('Agent / Partner');
      return this.$t('Receive From / Pay To');
    },
    partyInitial() {
      const name = this.partyDisplayName;
      return name ? name.trim().charAt(0).toUpperCase() : 'P';
    },
    totalBillAmount() {
      if (!this.data.payment_details || !this.data.payment_details.length) {
        return parseFloat(this.data.amount) || 0;
      }
      return this.data.payment_details.reduce(
        (sum, d) => sum + (parseFloat(d.amount) || 0),
        0
      );
    },
    totalSettledAmount() {
      if (!this.data.payment_details || !this.data.payment_details.length) {
        return parseFloat(this.data.amount) || 0;
      }
      return this.data.payment_details.reduce(
        (sum, d) => sum + (parseFloat(d.adjust_amount || d.paid_amount || d.amount) || 0),
        0
      );
    },
    totalDueAmount() {
      if (!this.data.payment_details || !this.data.payment_details.length) {
        return 0;
      }
      return this.data.payment_details.reduce(
        (sum, d) => sum + (parseFloat(d.due_amount) || 0),
        0
      );
    },
    modalTotalQty() {
      if (!this.activeRefData || !this.activeRefData.items) return 0;
      return this.activeRefData.items.reduce(
        (sum, it) => sum + (parseFloat(it.qty) || 0),
        0
      );
    },
    modalTotalSum() {
      if (!this.activeRefData || !this.activeRefData.items) return 0;
      return this.activeRefData.items.reduce(
        (sum, it) => sum + (parseFloat(it.total_price) || 0),
        0
      );
    },
    paymentChannelInfo() {
      const parts = [];
      if (this.data.mbanking_type) parts.push(this.data.mbanking_type);
      if (this.data.bank_id && this.data.bank?.bank_name) parts.push(this.data.bank.bank_name);
      if (this.data.branch) parts.push(this.data.branch);
      if (this.data.accountno) parts.push(`${this.$t('A/C')}: ${this.data.accountno}`);
      if (this.data.chequeno) parts.push(`${this.$t('Cheque')}: ${this.data.chequeno}`);
      if (this.data.trxid) parts.push(`${this.$t('TrxID')}: ${this.data.trxid}`);
      return parts.length ? parts.join(' | ') : null;
    },
    paymentPurposeText() {
      if (this.data.invoice_numbers) {
        return `${this.$t('Invoice(s)')}: ${this.data.invoice_numbers}`;
      }
      if (this.data.payment_details && this.data.payment_details.length > 0) {
        const refs = this.data.payment_details
          .map(
            (pd) =>
              pd.ref_doc_no ||
              pd.reference_info ||
              (pd.reference_type ? `${this.$t(pd.reference_type)}${pd.reference_id ? ' #' + pd.reference_id : ''}` : '')
          )
          .filter(Boolean);
        if (refs.length > 0) {
          return refs.join(', ');
        }
      }
      if (this.data.payto?.info) {
        return this.data.payto.info;
      }
      return this.data.payment_type === 'Receive'
        ? this.$t('Sales / Due Settlement')
        : this.$t('Supplier Bill / Expense Payment');
    },
  },
  methods: {
    printReceipt() {
      this.print(
        'moneyreceipt_print',
        this.data.payment_type === 'Receive' ? this.$t('Money Receipt') : this.$t('Payment Voucher')
      );
    },
    formatPrice(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return '0.00';
      return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
    refBadgeClass(type) {
      switch (type) {
        case 'Invoice':
          return 'bg-primary text-white';
        case 'GRN':
          return 'bg-info text-dark';
        case 'Purchase':
          return 'bg-success text-white';
        case 'WarrantyClaim':
          return 'bg-warning text-dark';
        case 'Expense':
        case 'OfficeExpense':
          return 'bg-danger text-white';
        case 'Loan':
        case 'LoanInfo':
          return 'bg-secondary text-white';
        case 'Salary':
        case 'SalarySheet':
          return 'bg-dark text-white';
        case 'SalesReturn':
          return 'bg-danger text-white';
        default:
          return 'bg-secondary text-white';
      }
    },
    refIconClass(type) {
      switch (type) {
        case 'Invoice':
          return 'fas fa-file-invoice-dollar';
        case 'GRN':
          return 'fas fa-boxes';
        case 'Purchase':
          return 'fas fa-truck-loading';
        case 'WarrantyClaim':
          return 'fas fa-shield-alt';
        case 'Expense':
        case 'OfficeExpense':
          return 'fas fa-receipt';
        case 'Loan':
        case 'LoanInfo':
          return 'fas fa-hand-holding-usd';
        case 'Salary':
        case 'SalarySheet':
          return 'fas fa-money-check-alt';
        case 'SalesReturn':
          return 'fas fa-undo-alt';
        default:
          return 'fas fa-file-alt';
      }
    },
    inspectReference(pd) {
      if (!pd || !pd.reference_type || !pd.reference_id) {
        if (pd && pd.ref_url) {
          window.open(pd.ref_url, '_blank');
        }
        return;
      }

      this.activeRefType = pd.reference_type;
      this.activeRefId = pd.reference_id;
      this.activeRefDocNo = pd.ref_doc_no || `${this.$t(pd.reference_type)} #${pd.reference_id}`;
      this.showRefModal = true;
      this.loadingRefModal = true;
      this.activeRefData = null;

      axios
        .get(`getreferencedetail/${pd.reference_type}/${pd.reference_id}`)
        .then((res) => {
          this.activeRefData = res.data;
        })
        .catch((err) => {
          console.error('Failed to load reference detail:', err);
          if (this.$toast) {
            this.$toast.error(this.$t('Failed to load reference details'));
          }
        })
        .finally(() => {
          this.loadingRefModal = false;
        });
    },
    closeRefModal() {
      this.showRefModal = false;
      this.activeRefData = null;
      this.loadingRefModal = false;
    },
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
/* Theme & Hero Banner Styles */
.theme-bg {
  background: #112C47 !important;
}

.theme-bg-soft {
  background: rgba(17, 44, 71, 0.1) !important;
}

.text-theme {
  color: #112C47 !important;
}

.text-success-light {
  color: #6ee7b7 !important;
}

.text-info-light {
  color: #93c5fd !important;
}

.payment-hero-banner {
  background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%) !important;
  border-radius: 8px;
}

.hero-icon-box {
  width: 54px;
  height: 54px;
  min-width: 54px;
}

.stat-icon {
  width: 38px;
  height: 38px;
}

.section-icon {
  width: 32px;
  height: 32px;
}

.info-card {
  border-radius: 8px;
  overflow: hidden;
}

.custom-spec-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
}

.custom-spec-table tr:last-child td {
  border-bottom: none;
}

.spec-label {
  width: 160px;
  color: #64748b;
  font-weight: 600;
}

.spec-value {
  color: #1e293b;
}

.custom-settlement-table th {
  font-weight: 700;
  color: #334155;
  font-size: 12.5px;
  padding: 10px 12px;
}

.custom-settlement-table td {
  padding: 10px 12px;
  font-size: 13px;
}

.btn-xs {
  font-size: 11px;
  line-height: 1.3;
  border-radius: 4px;
}

/* Receipt Display on Screen */
.receipt-container {
  padding: 5px;
}

.receipt-content {
  width: 100%;
  max-width: 780px;
  margin: 0 auto;
  background: #ffffff;
  padding: 24px 28px;
  border: 2px solid #112C47;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #1e293b;
  position: relative;
}

.receipt-header {
  display: flex !important;
  flex-direction: row !important;
  justify-content: space-between !important;
  align-items: center !important;
  gap: 15px;
  border-bottom: 2px solid #112C47;
  padding-bottom: 14px;
  margin-bottom: 12px;
}

.logo-section {
  width: 25%;
  min-width: 120px;
}

.logo-img {
  max-height: 65px;
  max-width: 170px;
  object-fit: contain;
}

.title-section {
  width: 40%;
  text-align: center;
}

.receipt-badge {
  display: inline-block;
  background-color: #112C47;
  color: #ffffff !important;
  font-weight: 800;
  font-size: 16px;
  letter-spacing: 1.5px;
  padding: 5px 20px;
  border-radius: 4px;
  text-transform: uppercase;
}

.receipt-copy-subtitle {
  font-size: 11px;
  margin-top: 4px;
  color: #64748b;
  font-weight: 600;
}

.info-section {
  width: 35%;
  text-align: right;
}

.company-name {
  font-size: 16px;
  font-weight: bold;
  color: #0f172a;
  margin: 0;
}

.company-sub {
  font-size: 11px;
  line-height: 1.35;
  color: #475569;
  margin: 2px 0 0 0;
}

.receipt-meta-row {
  display: flex !important;
  flex-direction: row !important;
  justify-content: space-between !important;
  align-items: center !important;
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 6px 14px;
  margin-bottom: 16px;
  font-size: 12px;
}

.receipt-field-row {
  display: flex !important;
  flex-direction: row !important;
  align-items: baseline !important;
  margin-bottom: 12px;
  width: 100%;
}

.field-label {
  width: 210px;
  min-width: 190px;
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  white-space: nowrap;
}

.field-line {
  flex: 1;
  border-bottom: 1px dashed #64748b;
  padding-bottom: 2px;
  min-height: 22px;
  display: flex;
  align-items: baseline;
  flex-wrap: wrap;
}

.field-value {
  font-size: 13.5px;
  color: #0f172a;
}

.receipt-mini-table {
  font-size: 11px;
  width: 100%;
}

.receipt-mini-table th {
  background: #f1f5f9;
  font-weight: 700;
  color: #334155;
}

.receipt-signature-section {
  display: flex !important;
  flex-direction: row !important;
  justify-content: space-between !important;
  align-items: flex-end !important;
  margin-top: 45px;
  padding-top: 10px;
  width: 100%;
}

.sig-box {
  width: 28%;
  text-align: center;
}

.sig-line {
  border-top: 1px solid #334155;
  margin-bottom: 5px;
  width: 100%;
}

.sig-title {
  font-size: 11.5px;
  font-weight: 700;
  color: #1e293b;
}

.sig-img-wrap {
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 3px;
}

.sig-img {
  max-height: 44px;
  max-width: 130px;
  object-fit: contain;
}

.receipt-footer {
  margin-top: 16px;
  padding-top: 8px;
  text-align: center;
  border-top: 1px dashed #cbd5e1;
  font-size: 10.5px;
  color: #64748b;
}

/* Modal Styling */
.ref-inspect-modal .modal-content {
  border-radius: 10px;
}

/* Print Overrides */
@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 10mm;
  }
  html, body {
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  .payment-view-wrapper {
    display: none !important;
  }
  .receipt-content {
    max-width: 100% !important;
    width: 100% !important;
    border: 2px solid #112C47 !important;
    box-shadow: none !important;
    padding: 18px 20px !important;
    margin: 0 auto !important;
  }
}

@media screen and (max-width: 768px) {
  .receipt-content {
    padding: 14px;
  }
  .receipt-header {
    flex-direction: column !important;
    text-align: center;
  }
  .logo-section, .title-section, .info-section {
    width: 100% !important;
    text-align: center !important;
  }
  .receipt-meta-row {
    flex-direction: column !important;
    gap: 6px;
    text-align: center;
  }
  .receipt-field-row {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
  .field-label {
    width: 100% !important;
    margin-bottom: 2px;
  }
  .field-line {
    width: 100% !important;
  }
}
</style>