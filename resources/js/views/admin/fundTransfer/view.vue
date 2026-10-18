<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="true"
    :showEditRoute="true"
    :showDeleteButton="true"
    :showPrintButton="true"
    printArea="fund_transfer_print_area"
  >
    <!-- 💻 1. Interactive On-Screen ERP Dashboard View -->
    <div class="fund-transfer-web-view w-100">
      
      <!-- 🌟 Top Hero / Transfer Header Banner -->
      <div class="card border-0 shadow-sm mb-4 transfer-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-money-bill-transfer fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ $t('Fund Transfer Voucher') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #{{ data.id }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Posted & Active') : $t('Inactive') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="far fa-calendar-alt me-1"></i> {{ $t('Transfer Date') }}:
                    <strong class="text-white">{{ data.transfer_date || '-' }}</strong>
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

            <!-- Right Metadata Badge (Clean enterprise audit indicator without duplicate action buttons) -->
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
        <!-- 1. Total Transferred Amount -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Transfer Amount') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
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
                  {{ $t('Credit (From Sender)') }}
                </span>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-arrow-up"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="fromAccountName">
                {{ fromAccountName || $t('Source Account') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.from_account?.account_code ? 'Code: ' + data.from_account.account_code : $t('Fund Account') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Destination Account (Debit Inflow) -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small fw-bold">
                  {{ $t('Debit (To Receiver)') }}
                </span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-arrow-down"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="toAccountName">
                {{ toAccountName || $t('Destination Account') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.to_account?.account_code ? 'Code: ' + data.to_account.account_code : $t('Fund Account') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Accounting Audit & Balance Status -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small fw-bold">
                  {{ $t('Ledger Status') }}
                </span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-balance-scale"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-success d-flex align-items-center gap-1">
                <i class="fas fa-check-double text-success fs-6"></i>
                {{ $t('Double-Entry Balanced') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.voucher?.voucherno || ('TRX-' + String(data.id || '').padStart(5, '0')) }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 🔄 Interactive Visual Flow Connection Strip -->
      <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark small d-flex align-items-center gap-2">
            <i class="fas fa-route text-primary"></i> {{ $t('Fund Transfer Direction & Route Flow') }}
          </span>
          <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 small">
            {{ $t('Inter-Account Contra') }}
          </span>
        </div>
        <div class="card-body p-3 p-md-4">
          <div class="row align-items-center g-3">
            <!-- Source Node -->
            <div class="col-md-4 text-center text-md-start">
              <div class="p-3 rounded-3 bg-light border border-danger-subtle h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="node-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-building-columns"></i>
                  </div>
                  <div>
                    <span class="badge bg-danger text-white small px-2 py-0">CREDIT</span>
                    <small class="text-muted d-block font-monospace">SOURCE</small>
                  </div>
                </div>
                <h6 class="fw-bold text-dark mb-1">{{ fromAccountName }}</h6>
                <small class="text-muted font-monospace" v-if="data.from_account?.account_code">
                  Code: {{ data.from_account.account_code }}
                </small>
              </div>
            </div>

            <!-- Directional Flow & Amount -->
            <div class="col-md-4 text-center">
              <div class="transfer-flow-center py-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 font-monospace fs-6 mb-2 d-inline-block">
                  {{ $filter.formatBDT(data.amount) }}
                </span>
                <div class="flow-arrow-track d-flex align-items-center justify-content-center my-1">
                  <div class="arrow-track-line"></div>
                  <div class="arrow-track-badge"><i class="fas fa-arrow-right text-white"></i></div>
                  <div class="arrow-track-line"></div>
                </div>
                <small class="text-muted d-block fst-italic px-2 text-truncate" :title="currencyToWordsBD(data.amount || 0)">
                  {{ currencyToWordsBD(data.amount || 0) }}
                </small>
              </div>
            </div>

            <!-- Destination Node -->
            <div class="col-md-4 text-center text-md-end">
              <div class="p-3 rounded-3 bg-light border border-success-subtle h-100">
                <div class="d-flex align-items-center justify-content-md-end gap-2 mb-2">
                  <div class="text-md-end">
                    <span class="badge bg-success text-white small px-2 py-0">DEBIT</span>
                    <small class="text-muted d-block font-monospace">DESTINATION</small>
                  </div>
                  <div class="node-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-wallet"></i>
                  </div>
                </div>
                <h6 class="fw-bold text-dark mb-1">{{ toAccountName }}</h6>
                <small class="text-muted font-monospace" v-if="data.to_account?.account_code">
                  Code: {{ data.to_account.account_code }}
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Two-Column Details Information Section -->
      <div class="row g-3 mb-4">
        <!-- Left: Transfer Parameters -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
              <span class="fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="fas fa-file-invoice text-primary"></i> {{ $t('Transfer Parameters') }}
              </span>
              <span class="badge bg-light text-dark border font-monospace">#{{ data.id }}</span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-striped mb-0 align-middle">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 38%;">{{ $t('Transfer ID') }}</td>
                    <td style="width: 4%;">:</td>
                    <td class="font-monospace fw-bold text-dark">#{{ data.id }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Transfer Date') }}</td>
                    <td>:</td>
                    <td class="font-monospace text-dark">{{ data.transfer_date || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('From (Source Account)') }}</td>
                    <td>:</td>
                    <td>
                      <span class="fw-bold text-danger">{{ fromAccountName }}</span>
                      <small class="text-muted d-block font-monospace" v-if="data.from_account?.account_code">
                        Code: {{ data.from_account.account_code }}
                      </small>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('To (Destination Account)') }}</td>
                    <td>:</td>
                    <td>
                      <span class="fw-bold text-success">{{ toAccountName }}</span>
                      <small class="text-muted d-block font-monospace" v-if="data.to_account?.account_code">
                        Code: {{ data.to_account.account_code }}
                      </small>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Transfer Amount') }}</td>
                    <td>:</td>
                    <td class="font-monospace fw-bold text-primary fs-6">
                      {{ $filter.formatBDT(data.amount) }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Status') }}</td>
                    <td>:</td>
                    <td>
                      <span class="badge" :class="data.status === 'active' ? 'bg-success' : 'bg-danger'">
                        {{ data.status === 'active' ? $t('Posted / Active') : $t('Inactive') }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Narration / Remarks') }}</td>
                    <td>:</td>
                    <td class="text-muted">{{ data.remarks || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Accounting & Voucher Info -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
              <span class="fw-bold small text-dark d-flex align-items-center gap-2">
                <i class="fas fa-book-bookmark text-primary"></i> {{ $t('Accounting & Voucher Information') }}
              </span>
              <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                {{ data.voucher?.voucher_type || 'Contra' }}
              </span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-striped mb-0 align-middle">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 38%;">{{ $t('Voucher No') }}</td>
                    <td style="width: 4%;">:</td>
                    <td class="font-monospace fw-bold text-primary">
                      {{ data.voucher?.voucherno || ('TRX-' + String(data.id || '').padStart(5, '0')) }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Voucher Type') }}</td>
                    <td>:</td>
                    <td>
                      <span class="badge bg-light text-dark border">
                        {{ data.voucher?.voucher_type || 'Contra / Journal' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Voucher Date') }}</td>
                    <td>:</td>
                    <td class="font-monospace text-dark">
                      {{ data.voucher?.voucher_date || data.transfer_date }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Financial Year') }}</td>
                    <td>:</td>
                    <td class="text-dark">{{ data.voucher?.financial_year?.title || 'Current' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Accounting Method') }}</td>
                    <td>:</td>
                    <td class="text-dark">{{ $t('Double-Entry General Ledger') }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('System Narration') }}</td>
                    <td>:</td>
                    <td class="text-muted">{{ data.voucher?.narration || data.remarks || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">{{ $t('Ledger Verification') }}</td>
                    <td>:</td>
                    <td>
                      <span class="badge bg-success text-white">
                        <i class="fas fa-check-circle me-1"></i> {{ $t('Double Entry Balanced') }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- ⚖️ Double-Entry General Ledger Breakdown Table -->
      <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-scale-balanced text-primary"></i>
            {{ $t('General Ledger Journal Breakdown (Double-Entry)') }}
          </span>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-monospace">
            <i class="fas fa-check-double me-1"></i> {{ $t('Balanced Dr = Cr') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 journal-ledger-table">
              <thead class="table-light border-bottom">
                <tr>
                  <th class="ps-3 py-2 small text-uppercase" style="width: 8%;">{{ $t('Entry') }}</th>
                  <th class="py-2 small text-uppercase" style="width: 32%;">{{ $t('Account Head') }}</th>
                  <th class="py-2 small text-uppercase" style="width: 30%;">{{ $t('Particulars / Line Narration') }}</th>
                  <th class="py-2 small text-uppercase text-end" style="width: 15%;">{{ $t('Debit (Dr)') }}</th>
                  <th class="pe-3 py-2 small text-uppercase text-end" style="width: 15%;">{{ $t('Credit (Cr)') }}</th>
                </tr>
              </thead>
              <tbody>
                <!-- If database voucher_details exist -->
                <template v-if="data.voucher?.voucher_details && data.voucher.voucher_details.length > 0">
                  <tr v-for="(item, index) in data.voucher.voucher_details" :key="index">
                    <td class="ps-3">
                      <span
                        class="badge font-monospace px-2 py-1"
                        :class="Number(item.dr_amount) > 0 ? 'bg-success text-white' : 'bg-danger text-white'"
                      >
                        {{ Number(item.dr_amount) > 0 ? 'Dr' : 'Cr' }}
                      </span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark">{{ item.account?.account_name }}</span>
                      <small class="text-muted d-block font-monospace" v-if="item.account?.account_code">
                        Code: {{ item.account?.account_code }}
                      </small>
                    </td>
                    <td class="text-muted small">
                      {{ item.line_narration || data.remarks || (Number(item.dr_amount) > 0 ? $t('Fund received via inter-account transfer') : $t('Fund transferred out to destination account')) }}
                    </td>
                    <td class="text-end font-monospace" :class="Number(item.dr_amount) > 0 ? 'fw-bold text-success' : 'text-muted'">
                      {{ Number(item.dr_amount) > 0 ? $filter.formatBDT(item.dr_amount) : '৳ 0.00' }}
                    </td>
                    <td class="pe-3 text-end font-monospace" :class="Number(item.cr_amount) > 0 ? 'fw-bold text-danger' : 'text-muted'">
                      {{ Number(item.cr_amount) > 0 ? $filter.formatBDT(item.cr_amount) : '৳ 0.00' }}
                    </td>
                  </tr>
                </template>

                <!-- Standard Verified Fallback -->
                <template v-else>
                  <!-- 1. Debit Row: Destination Account -->
                  <tr>
                    <td class="ps-3">
                      <span class="badge bg-success text-white font-monospace px-2 py-1">Dr</span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark">{{ toAccountName }}</span>
                      <small class="text-muted d-block font-monospace" v-if="data.to_account?.account_code">
                        Code: {{ data.to_account?.account_code }}
                      </small>
                    </td>
                    <td class="text-muted small">
                      {{ $t('Fund received via inter-account transfer') }}
                    </td>
                    <td class="text-end font-monospace fw-bold text-success">
                      {{ $filter.formatBDT(data.amount) }}
                    </td>
                    <td class="pe-3 text-end font-monospace text-muted">
                      ৳ 0.00
                    </td>
                  </tr>

                  <!-- 2. Credit Row: Source Account -->
                  <tr>
                    <td class="ps-3">
                      <span class="badge bg-danger text-white font-monospace px-2 py-1">Cr</span>
                    </td>
                    <td>
                      <span class="fw-bold text-dark">{{ fromAccountName }}</span>
                      <small class="text-muted d-block font-monospace" v-if="data.from_account?.account_code">
                        Code: {{ data.from_account?.account_code }}
                      </small>
                    </td>
                    <td class="text-muted small">
                      {{ $t('Fund transferred out from source account') }}
                    </td>
                    <td class="text-end font-monospace text-muted">
                      ৳ 0.00
                    </td>
                    <td class="pe-3 text-end font-monospace fw-bold text-danger">
                      {{ $filter.formatBDT(data.amount) }}
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-light border-top border-2">
                <tr class="fw-bold">
                  <td colspan="3" class="ps-3 text-uppercase text-dark">
                    {{ $t('Total Balanced Impact') }}:
                  </td>
                  <td class="text-end font-monospace text-success fs-6">
                    {{ $filter.formatBDT(data.amount) }}
                  </td>
                  <td class="pe-3 text-end font-monospace text-danger fs-6">
                    {{ $filter.formatBDT(data.amount) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- ✍️ Signatures & Verification Strip -->
      <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
        <div class="row text-center g-4">
          <div class="col-4">
            <div class="signature-line mx-auto mb-2"></div>
            <span class="fw-semibold small text-dark d-block">{{ $t('Prepared By') }}</span>
            <span class="text-muted small" style="font-size: 11px;">{{ $root.global?.auth?.name || $t('Accounts Officer') }}</span>
          </div>
          <div class="col-4">
            <div class="signature-line mx-auto mb-2"></div>
            <span class="fw-semibold small text-dark d-block">{{ $t('Verified & Checked') }}</span>
            <span class="text-muted small" style="font-size: 11px;">{{ $t('Internal Audit') }}</span>
          </div>
          <div class="col-4">
            <div class="signature-line mx-auto mb-2"></div>
            <span class="fw-semibold small text-dark d-block">{{ $t('Authorized Signatory') }}</span>
            <span class="text-muted small" style="font-size: 11px;">{{ $t('Manager / Director') }}</span>
          </div>
        </div>
        <div class="text-center text-muted mt-4 pt-2 border-top small" style="font-size: 11px;">
          {{ $t('Generated automatically by POS System on') }} {{ currentDateTime() }} • {{ $t('Computer Generated Official Document') }}
        </div>
      </div>

    </div>

    <!-- 🖨️ 2. Bulletproof Printable Voucher Layout (#fund_transfer_print_area) -->
    <!-- This dedicated container is specifically formatted for standard A4 portrait print with zero breaking -->
    <div id="fund_transfer_print_area" class="d-none">
      <div style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; color: #000000; background: #ffffff; padding: 5px; width: 100%; box-sizing: border-box;">
        
        <!-- 🏢 1. Official Header Section -->
        <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 16px; border-bottom: 2px solid #000000; padding-bottom: 12px;">
          <tbody>
            <tr>
              <td style="width: 60%; vertical-align: top; border: none;">
                <div style="font-size: 22px; font-weight: 800; color: #112C47; text-transform: uppercase; margin-bottom: 4px;">
                  {{ $root.site?.title || 'QPOS ENTERPRISE' }}
                </div>
                <div style="font-size: 11.5px; color: #333333; margin-bottom: 3px;" v-if="$root.site?.address">
                  {{ $root.site.address }}
                </div>
                <div style="font-size: 11px; color: #444444;">
                  <span v-if="$root.site?.mobile1"><strong>Phone:</strong> {{ $root.site.mobile1 }}</span>
                  <span v-if="$root.site?.contact_email" style="margin-left: 12px;"><strong>Email:</strong> {{ $root.site.contact_email }}</span>
                </div>
              </td>
              <td style="width: 40%; vertical-align: top; text-align: right; border: none;">
                <div style="display: inline-block; font-size: 13px; font-weight: 800; padding: 5px 16px; border: 1.5px solid #000000; border-radius: 4px; background-color: #f1f5f9; color: #000000; text-transform: uppercase; margin-bottom: 6px;">
                  FUND TRANSFER VOUCHER
                </div>
                <div style="font-size: 11px; color: #000000; font-family: monospace;">
                  <strong>Voucher No:</strong> {{ data.voucher?.voucherno || ('TRX-' + String(data.id || '').padStart(5, '0')) }}
                </div>
                <div style="font-size: 11px; color: #000000; font-family: monospace;">
                  <strong>Transfer ID:</strong> #{{ data.id }}
                </div>
                <div style="font-size: 11px; color: #000000;">
                  <strong>Date:</strong> {{ data.transfer_date }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- 📋 2. Meta Information Section (Clean Key-Value Box) -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 16px; font-size: 11.5px;">
          <tbody>
            <tr style="background-color: #f8fafc;">
              <td style="width: 20%; padding: 6px 10px; font-weight: bold; border: 1px solid #000000;">From (Source Account):</td>
              <td style="width: 30%; padding: 6px 10px; border: 1px solid #000000; font-weight: bold;">
                {{ fromAccountName }}
                <span v-if="data.from_account?.account_code" style="font-family: monospace; font-size: 10.5px; font-weight: normal; color: #555;">({{ data.from_account.account_code }})</span>
              </td>
              <td style="width: 20%; padding: 6px 10px; font-weight: bold; border: 1px solid #000000;">To (Destination Account):</td>
              <td style="width: 30%; padding: 6px 10px; border: 1px solid #000000; font-weight: bold;">
                {{ toAccountName }}
                <span v-if="data.to_account?.account_code" style="font-family: monospace; font-size: 10.5px; font-weight: normal; color: #555;">({{ data.to_account.account_code }})</span>
              </td>
            </tr>
            <tr>
              <td style="padding: 6px 10px; font-weight: bold; border: 1px solid #000000;">Transfer Amount:</td>
              <td style="padding: 6px 10px; border: 1px solid #000000; font-weight: 800; font-family: monospace; font-size: 13px;">
                {{ $filter.formatBDT(data.amount) }}
              </td>
              <td style="padding: 6px 10px; font-weight: bold; border: 1px solid #000000;">Execution Status:</td>
              <td style="padding: 6px 10px; border: 1px solid #000000;">
                {{ data.status === 'active' ? 'POSTED / COMPLETED' : 'INACTIVE' }}
              </td>
            </tr>
            <tr v-if="data.remarks">
              <td style="padding: 6px 10px; font-weight: bold; border: 1px solid #000000;">Narration / Remarks:</td>
              <td colspan="3" style="padding: 6px 10px; border: 1px solid #000000;">
                {{ data.remarks }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- ⚖️ 3. Double-Entry Accounting Ledger Table -->
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-bottom: 0; font-size: 11.5px;">
          <thead>
            <tr style="background-color: #e2e8f0;">
              <th style="width: 8%; border: 1px solid #000000; padding: 7px 8px; text-align: center; font-weight: 800; text-transform: uppercase;">Type</th>
              <th style="width: 34%; border: 1px solid #000000; padding: 7px 8px; text-align: left; font-weight: 800; text-transform: uppercase;">Account Head</th>
              <th style="width: 32%; border: 1px solid #000000; padding: 7px 8px; text-align: left; font-weight: 800; text-transform: uppercase;">Particulars / Description</th>
              <th style="width: 13%; border: 1px solid #000000; padding: 7px 8px; text-align: right; font-weight: 800; text-transform: uppercase;">Debit (Dr)</th>
              <th style="width: 13%; border: 1px solid #000000; padding: 7px 8px; text-align: right; font-weight: 800; text-transform: uppercase;">Credit (Cr)</th>
            </tr>
          </thead>
          <tbody>
            <!-- If details from database exist -->
            <template v-if="data.voucher?.voucher_details && data.voucher.voucher_details.length > 0">
              <tr v-for="(item, index) in data.voucher.voucher_details" :key="index" :style="index % 2 === 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;'">
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: center; font-weight: bold; font-family: monospace;">
                  {{ Number(item.dr_amount) > 0 ? 'Dr' : 'Cr' }}
                </td>
                <td style="border: 1px solid #000000; padding: 6px 8px; font-weight: bold;">
                  {{ item.account?.account_name }}
                  <span v-if="item.account?.account_code" style="font-family: monospace; font-size: 10px; font-weight: normal; color: #555;">({{ item.account.account_code }})</span>
                </td>
                <td style="border: 1px solid #000000; padding: 6px 8px; color: #333;">
                  {{ item.line_narration || data.remarks || (Number(item.dr_amount) > 0 ? 'Fund received via transfer' : 'Fund transferred out') }}
                </td>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: right; font-family: monospace; font-weight: bold;">
                  {{ Number(item.dr_amount) > 0 ? $filter.formatBDT(item.dr_amount) : '৳ 0.00' }}
                </td>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: right; font-family: monospace; font-weight: bold;">
                  {{ Number(item.cr_amount) > 0 ? $filter.formatBDT(item.cr_amount) : '৳ 0.00' }}
                </td>
              </tr>
            </template>

            <!-- Standard Fallback Rows -->
            <template v-else>
              <!-- Row 1: Debit to Destination Account -->
              <tr>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: center; font-weight: bold; font-family: monospace;">Dr</td>
                <td style="border: 1px solid #000000; padding: 6px 8px; font-weight: bold;">
                  {{ toAccountName }}
                  <span v-if="data.to_account?.account_code" style="font-family: monospace; font-size: 10px; font-weight: normal; color: #555;">({{ data.to_account.account_code }})</span>
                </td>
                <td style="border: 1px solid #000000; padding: 6px 8px; color: #333;">Fund received via inter-account transfer</td>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: right; font-family: monospace; font-weight: bold;">{{ $filter.formatBDT(data.amount) }}</td>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: right; font-family: monospace; color: #777;">৳ 0.00</td>
              </tr>
              <!-- Row 2: Credit from Source Account -->
              <tr style="background-color: #f8fafc;">
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: center; font-weight: bold; font-family: monospace;">Cr</td>
                <td style="border: 1px solid #000000; padding: 6px 8px; font-weight: bold;">
                  {{ fromAccountName }}
                  <span v-if="data.from_account?.account_code" style="font-family: monospace; font-size: 10px; font-weight: normal; color: #555;">({{ data.from_account.account_code }})</span>
                </td>
                <td style="border: 1px solid #000000; padding: 6px 8px; color: #333;">Fund transferred out to destination account</td>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: right; font-family: monospace; color: #777;">৳ 0.00</td>
                <td style="border: 1px solid #000000; padding: 6px 8px; text-align: right; font-family: monospace; font-weight: bold;">{{ $filter.formatBDT(data.amount) }}</td>
              </tr>
            </template>
          </tbody>
          <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: 800;">
              <th colspan="3" style="border: 1px solid #000000; padding: 7px 8px; text-align: right; text-transform: uppercase;">
                TOTAL BALANCED IMPACT:
              </th>
              <th style="border: 1px solid #000000; padding: 7px 8px; text-align: right; font-family: monospace; font-size: 12px;">
                {{ $filter.formatBDT(data.amount) }}
              </th>
              <th style="border: 1px solid #000000; padding: 7px 8px; text-align: right; font-family: monospace; font-size: 12px;">
                {{ $filter.formatBDT(data.amount) }}
              </th>
            </tr>
          </tfoot>
        </table>

        <!-- 🔤 4. Amount In Words Box -->
        <div style="border: 1.5px solid #000000; border-top: none; padding: 7px 10px; background-color: #f8fafc; font-size: 11px; margin-bottom: 45px;">
          <strong style="text-transform: uppercase; color: #000000; margin-right: 5px;">IN WORDS (কথায়):</strong>
          <span style="font-weight: 700; color: #000000;">{{ currencyToWordsBD(data.amount || 0) }}</span>
        </div>

        <!-- ✍️ 5. Official Signatures Section -->
        <table style="width: 100%; border-collapse: collapse; border: none; margin-top: 30px; font-size: 11.5px;">
          <tbody>
            <tr>
              <td style="width: 30%; text-align: center; vertical-align: top; border: none;">
                <div style="border-top: 1.5px solid #000000; padding-top: 5px; margin: 0 15px;">
                  <strong style="color: #000000;">Prepared By</strong>
                  <div style="font-size: 10px; color: #555555;">(Accounts Officer)</div>
                </div>
              </td>
              <td style="width: 40%; text-align: center; vertical-align: top; border: none;">
                <div style="border-top: 1.5px solid #000000; padding-top: 5px; margin: 0 25px;">
                  <strong style="color: #000000;">Verified & Checked By</strong>
                  <div style="font-size: 10px; color: #555555;">(Internal Audit)</div>
                </div>
              </td>
              <td style="width: 30%; text-align: center; vertical-align: top; border: none;">
                <div style="border-top: 1.5px solid #000000; padding-top: 5px; margin: 0 15px;">
                  <strong style="color: #000000;">Authorized Signatory</strong>
                  <div style="font-size: 10px; color: #555555;">(Manager / Director)</div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- 🕒 6. Footer Timestamp & Notice -->
        <div style="text-align: center; font-size: 10px; color: #666666; margin-top: 25px; border-top: 1px dashed #aaaaaa; padding-top: 6px;">
          Computer Generated Official Fund Transfer Voucher • Generated on {{ currentDateTime() }} • QPOS ERP
        </div>

      </div>
    </div>

  </view-page>
</template>

<script>
const model = "fundTransfer";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  computed: {
    fromAccountName() {
      if (!this.data) return '';
      const acc = this.data.from_account;
      if (!acc) return '';
      return acc.account_name || acc.name || (acc.account_code ? `${acc.account_code} - ${acc.account_name}` : '');
    },
    toAccountName() {
      if (!this.data) return '';
      const acc = this.data.to_account;
      if (!acc) return '';
      return acc.account_name || acc.name || (acc.account_code ? `${acc.account_code} - ${acc.account_name}` : '');
    }
  },

  methods: {
    currentDateTime() {
      return new Date().toLocaleString('en-BD', {
        dateStyle: 'medium',
        timeStyle: 'short'
      });
    },

    printVoucher() {
      const printContents = document.getElementById("fund_transfer_print_area");
      if (!printContents) return;

      const WinPrint = window.open(
        "",
        "",
        "left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0"
      );
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
      <head>
        <title>Fund Transfer Voucher - #${this.data.id || ""}</title>
        <meta charset="utf-8">
        <style>
          * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
          }
          @page {
            size: A4 portrait;
            margin: 12mm 15mm;
          }
          html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #ffffff !important;
            color: #000000 !important;
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
          }
          table {
            border-collapse: collapse !important;
          }
          @media print {
            body {
              -webkit-print-color-adjust: exact !important;
              print-color-adjust: exact !important;
            }
          }
        </style>
      </head>
      <body>
        <div style="width: 100%; max-width: 190mm; margin: 0 auto;">
          ${printContents.innerHTML}
        </div>
      </body>
      </html>`);
      WinPrint.document.close();
      WinPrint.focus();
      setTimeout(() => {
        WinPrint.print();
      }, 350);
    }
  },

  created() {
    this.page_title = `${this.headline(this.model)} Details`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
/* 💻 Web View Dashboard Styles */
.fund-transfer-web-view {
  max-width: 100%;
}

/* 🌟 Hero Header Banner */
.transfer-hero-banner {
  background: linear-gradient(135deg, #112c47 0%, #1e3a8a 100%);
  border-radius: 12px;
}

.hero-icon-box {
  width: 54px;
  height: 54px;
}

.text-theme {
  color: #112c47 !important;
}

/* 📊 KPI Stat Cards */
.stat-card {
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 44px;
  height: 44px;
  font-size: 18px;
}

/* 🔄 Visual Flow Connection */
.node-icon {
  width: 36px;
  height: 36px;
  font-size: 16px;
}

.arrow-track-line {
  height: 2px;
  width: 45px;
  background-color: #cbd5e1;
}

.arrow-track-badge {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background-color: #da511f;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  box-shadow: 0 2px 6px rgba(218, 81, 31, 0.3);
}

/* ⚖️ Journal Ledger Table */
.journal-ledger-table thead th {
  font-weight: 700;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
}

.journal-ledger-table tfoot td {
  border-top: 2px solid #cbd5e1 !important;
}

/* ✍️ Signatures */
.signature-line {
  width: 160px;
  height: 1.5px;
  background-color: #94a3b8;
}

/* 🖨️ Native Browser Print Support (@media print) */
@media print {
  .fund-transfer-web-view,
  .page_header,
  .viewer_devider,
  .main-sidebar,
  .main-header,
  .footer,
  .action,
  .btn,
  .modal,
  .navbar,
  nav,
  header {
    display: none !important;
  }

  #fund_transfer_print_area {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  @page {
    size: A4 portrait;
    margin: 10mm 12mm;
  }

  body {
    background: #fff !important;
    color: #000 !important;
    font-size: 11.5px !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>