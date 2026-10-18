<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" :showPrintButton="false" :button="false" :showEditRoute="false" printArea="wastage_print_area">
    <!-- 💻 1. Interactive Web Dashboard View -->
    <div class="wastage-view-wrapper wastage-web-view">
      <!-- 🌟 Top Hero / Wastage Header Banner -->
      <div class="card border-0 shadow-sm mb-4 wastage-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-dumpster-fire fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">{{ $t('Wastage & Scrap Audit') }} #{{ data.audit_number || ('WST-' + data.id) }}</h4>
                  <span class="badge" :class="(data.approved_by || data.status === 'approved') ? 'bg-success text-white' : 'bg-warning text-dark'">
                    <i :class="(data.approved_by || data.status === 'approved') ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                    {{ (data.approved_by || data.status === 'approved') ? $t('Approved & Stock Deducted') : $t('Pending Review & Approval') }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="hasAnySerials">
                    <i class="fas fa-barcode me-1"></i> {{ $t('Serialized Stock') }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="data.branch">
                    <i class="fas fa-code-branch me-1"></i> {{ data.branch.title }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>{{ $t('Audit Date') }}: <strong class="text-white">{{ data.audit_date || $t('N/A') }}</strong></span>
                  <span><i class="fas fa-user-tie me-1"></i>{{ $t('Auditor') }}: <strong class="text-white">{{ data.auditor?.title || data.auditor?.full_name || data.audited_by || $t('Store Auditor') }}</strong></span>
                  <span v-if="data.creator"><i class="fas fa-user-edit me-1"></i>{{ $t('Logged By') }}: <strong class="text-white">{{ data.creator.name }}</strong></span>
                  <span v-if="data.approved_admin"><i class="fas fa-user-check me-1 text-success"></i>{{ $t('Approved By') }}: <strong class="text-white">{{ data.approved_admin.name }}</strong> ({{ data.approved_date }})</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons (Print Voucher & Quick Back) -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="printWastageVoucher">
                <i class="fas fa-print me-1"></i> {{ $t('Print Disposal Voucher') }}
              </button>
              <router-link
                v-if="!data.approved_by && data.status !== 'approved' && $root.checkPermission('wastage.edit')"
                :to="{ name: model + '.edit', params: { id: data.id }, query: { page: $route.query.page } }"
                class="btn btn-outline-light btn-sm fw-bold px-3"
              >
                <i class="fas fa-pencil-alt me-1"></i> {{ $t('Edit Audit') }}
              </router-link>
              <router-link :to="{ name: model + '.index' }" class="btn btn-outline-light btn-sm fw-bold px-3">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Cards -->
      <div class="row g-3 mb-4">
        <!-- Total Line Items -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Product Lines') }}</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-list-check"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">
                {{ data.wastage_details ? data.wastage_details.length : 0 }} {{ $t('Line(s)') }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Across') }} {{ uniqueCategoriesCount }} {{ $t('category(ies)') }}
              </small>
            </div>
          </div>
        </div>

        <!-- Total Scrapped Quantity -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Scrapped Units') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">
                {{ Number(data.total_qty || 0).toLocaleString() }} {{ $t('Units') }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Physical inventory written off') }}
              </small>
            </div>
          </div>
        </div>

        <!-- Total Financial Loss -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Financial Loss') }}</span>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-danger font-monospace text-nowrap">
                ৳ {{ formatNum(data.total_loss_amount) }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Asset reduction from inventory value') }}
              </small>
            </div>
          </div>
        </div>

        <!-- Dominant Scrap Reason -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Primary Scrap Reason') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-circle-exclamation"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark text-truncate" :title="dominantReason">
                {{ dominantReason || $t('Stock Adjustment') }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Affecting') }} {{ dominantReasonLines }} {{ $t('line item(s)') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Two-Column Metadata Overview (Audit Logistics vs Approval & Financials) -->
      <div class="row g-3 mb-4">
        <!-- Left: Audit Logistics & Custody -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-clipboard-list"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Audit Logistics & Custody') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Audit number, branch, lead auditor and justification') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-spec-table">
                  <tbody>
                    <tr>
                      <td class="spec-label" width="38%"><i class="fas fa-hashtag me-2 text-muted"></i>{{ $t('Audit Reference No') }}</td>
                      <td class="spec-value font-monospace fw-bold text-dark">{{ data.audit_number || ('WST-' + data.id) }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="far fa-calendar-alt me-2 text-muted"></i>{{ $t('Audit Conducted Date') }}</td>
                      <td class="spec-value font-monospace text-dark">{{ data.audit_date || $t('N/A') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-code-branch me-2 text-muted"></i>{{ $t('Branch / Store') }}</td>
                      <td class="spec-value fw-semibold text-dark">{{ data.branch?.title || $t('Head Office / Central Store') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-user-tie me-2 text-muted"></i>{{ $t('Lead Auditor') }}</td>
                      <td class="spec-value fw-bold text-dark">{{ data.auditor?.title || data.auditor?.full_name || $t('Store Auditor') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-users me-2 text-muted"></i>{{ $t('Audit Team / Ref') }}</td>
                      <td class="spec-value">{{ data.audited_by || $t('Internal Quality & Audit Team') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-user-edit me-2 text-muted"></i>{{ $t('Logged By') }}</td>
                      <td class="spec-value">{{ data.creator?.name || $t('System Administrator') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-sticky-note me-2 text-muted"></i>{{ $t('Audit Justification / Note') }}</td>
                      <td class="spec-value fst-italic text-muted">{{ data.note || $t('Routine stock inspection and expired/damaged goods disposal.') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Approval & Financial Valuation -->
        <div class="col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Approval & Financial Valuation') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Management sign-off and inventory ledger write-off status') }}</small>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-spec-table">
                  <tbody>
                    <tr>
                      <td class="spec-label" width="38%"><i class="fas fa-check-double me-2 text-muted"></i>{{ $t('Approval Status') }}</td>
                      <td class="spec-value">
                        <span class="badge" :class="(data.approved_by || data.status === 'approved') ? 'bg-success text-white' : 'bg-warning text-dark'">
                          <i :class="(data.approved_by || data.status === 'approved') ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                          {{ (data.approved_by || data.status === 'approved') ? $t('Approved') : $t('Pending Approval') }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-user-check me-2 text-muted"></i>{{ $t('Approved By') }}</td>
                      <td class="spec-value fw-bold text-dark">
                        {{ data.approved_admin?.name || data.approved_admin?.full_name || $t('Awaiting Sign-off') }}
                        <small v-if="data.approved_admin?.email" class="text-muted d-block font-monospace" style="font-size: 11px;">
                          {{ data.approved_admin.email }}
                        </small>
                      </td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="far fa-calendar-check me-2 text-muted"></i>{{ $t('Approval Timestamp') }}</td>
                      <td class="spec-value font-monospace">{{ data.approved_date || $t('Pending Review') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-boxes-stacked me-2 text-muted"></i>{{ $t('Total Quantity') }}</td>
                      <td class="spec-value font-monospace fw-bold text-dark">{{ data.total_qty || 0 }} {{ $t('Units') }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-sack-dollar me-2 text-muted"></i>{{ $t('Financial Loss Valuation') }}</td>
                      <td class="spec-value font-monospace fw-bold text-danger fs-6">৳ {{ formatNum(data.total_loss_amount) }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-warehouse me-2 text-muted"></i>{{ $t('Stock Deduction') }}</td>
                      <td class="spec-value">
                        <span v-if="data.approved_by || data.status === 'approved'" class="text-success fw-bold small">
                          <i class="fas fa-circle-check me-1"></i>{{ $t('Deducted from Stock Ledger') }}
                        </span>
                        <span v-else class="text-warning fw-bold small">
                          <i class="fas fa-hourglass-half me-1"></i>{{ $t('Pending Approval (Stock Retained)') }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-spell-check me-2 text-muted"></i>{{ $t('Amount In Words') }}</td>
                      <td class="spec-value small text-muted fw-semibold">{{ inWordsText }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📦 Scrapped Line Items Table -->
      <div class="card border-0 shadow-sm mb-4 table-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-boxes"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Itemized Scrap & Wastage Details') }}</h6>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Complete breakdown of scrapped goods, variants, disposal reasons and loss valuation') }}</small>
            </div>
          </div>
          <span class="badge theme-bg text-white font-monospace px-3 py-1.5">
            {{ data.wastage_details ? data.wastage_details.length : 0 }} {{ $t('Item(s) Recorded') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-items-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width: 45px;">{{ $t('#') }}</th>
                  <th>{{ $t('Product Details') }}</th>
                  <th style="width: 120px;" v-if="hasAnyVariants">{{ $t('Variant') }}</th>
                  <th class="text-center" style="width: 130px;" v-if="hasAnySerials">{{ $t('Serial Number') }}</th>
                  <th style="width: 170px;">{{ $t('Wastage Reason & Remarks') }}</th>
                  <th class="text-center" style="width: 115px;">{{ $t('Expiry Date') }}</th>
                  <th class="text-center" style="width: 110px;">{{ $t('Quantity') }}</th>
                  <th class="text-end" style="width: 120px;">{{ $t('Unit Cost') }}</th>
                  <th class="text-end" style="width: 130px;">{{ $t('Total Loss') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.wastage_details" :key="index">
                  <!-- # -->
                  <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>

                  <!-- Product Details -->
                  <td>
                    <div class="fw-bold text-dark fs-6">{{ item.item?.title || $t('Item #') + item.item_id }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                      <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 11px;">
                        {{ item.category?.title || item.item?.category?.title || $t('General') }}
                      </span>
                      <small class="text-muted font-monospace" v-if="item.item?.barcode" style="font-size: 11px;">
                        <i class="fas fa-barcode me-1"></i>{{ item.item.barcode }}
                      </small>
                      <small class="text-muted" v-if="item.item?.brand" style="font-size: 11px;">
                        <i class="fas fa-tag me-1"></i>{{ item.item.brand }}
                      </small>
                    </div>
                  </td>

                  <!-- Variant -->
                  <td v-if="hasAnyVariants">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                      <span class="badge bg-secondary" v-if="item.color?.title">{{ item.color.title }}</span>
                      <span class="badge bg-info text-dark" v-if="item.size?.title">{{ item.size.title }}</span>
                      <span class="text-muted small" v-if="!item.color?.title && !item.size?.title">-</span>
                    </div>
                  </td>

                  <!-- Serial Numbers -->
                  <td class="text-center" v-if="hasAnySerials">
                    <div v-if="item.serial_no">
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary font-monospace px-2"
                        @click="openSerialModal(item)"
                        :title="$t('View Serial Numbers')"
                      >
                        <i class="fas fa-barcode me-1"></i> {{ item.serial_no }}
                      </button>
                    </div>
                    <span v-else class="text-muted small">-</span>
                  </td>

                  <!-- Reason & Remarks -->
                  <td>
                    <span class="badge" :class="getReasonBadgeClass(item.reason)">
                      <i :class="getReasonIcon(item.reason)" class="me-1"></i>
                      {{ item.reason || $t('Unspecified') }}
                    </span>
                    <div v-if="item.remarks" class="small text-muted fst-italic mt-1">
                      {{ item.remarks }}
                    </div>
                  </td>

                  <!-- Expiry Date -->
                  <td class="text-center font-monospace small">
                    <span v-if="item.expired_date" class="badge bg-light text-dark border">
                      <i class="far fa-calendar-times text-danger me-1"></i>{{ item.expired_date }}
                    </span>
                    <span v-else class="text-muted">-</span>
                  </td>

                  <!-- Quantity with Unit -->
                  <td class="text-center font-monospace fw-bold text-dark fs-6">
                    {{ item.quantity }}
                    <small class="text-muted fw-normal d-block" style="font-size: 11px;">
                      {{ item.unit?.title || item.item?.unit?.title || $t('Pcs') }}
                    </small>
                  </td>

                  <!-- Unit Cost -->
                  <td class="text-end font-monospace text-muted text-nowrap">
                    ৳ {{ formatNum(item.unit_price) }}
                  </td>

                  <!-- Total Loss -->
                  <td class="text-end font-monospace fw-bold text-danger fs-6 text-nowrap">
                    ৳ {{ formatNum(item.total_amount) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold fs-6">
                  <td :colspan="3 + (hasAnyVariants ? 1 : 0) + (hasAnySerials ? 1 : 0)" class="text-end text-uppercase tracking-wider">
                    {{ $t('Grand Total Scrapped Loss:') }}
                  </td>
                  <td class="text-center font-monospace text-dark fs-5">
                    {{ data.total_qty || 0 }}
                  </td>
                  <td></td>
                  <td class="text-end font-monospace text-danger fs-5 text-nowrap">
                    ৳ {{ formatNum(data.total_loss_amount) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 📊 Wastage Reason & Financial Impact Breakdown -->
      <div class="row g-3 mb-4" v-if="reasonSummaryList.length > 0">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon bg-danger bg-opacity-10 text-danger rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-chart-pie"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Wastage Reason & Financial Impact Breakdown') }}</h6>
                <small class="text-muted" style="font-size: 11px;">{{ $t('Categorized loss analysis across disposal causes') }}</small>
              </div>
            </div>
            <div class="card-body p-3">
              <div class="row g-3">
                <div class="col-md-4 col-sm-6 col-12" v-for="(rs, rIdx) in reasonSummaryList" :key="rIdx">
                  <div class="p-3 rounded border bg-light h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="badge" :class="getReasonBadgeClass(rs.reason)">
                        <i :class="getReasonIcon(rs.reason)" class="me-1"></i> {{ rs.reason }}
                      </span>
                      <span class="badge bg-white text-dark border font-monospace">{{ rs.count }} {{ $t('Line(s)') }}</span>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-2">
                      <div class="small text-muted">{{ $t('Units Lost:') }} <strong class="text-dark font-monospace">{{ rs.qty }}</strong></div>
                      <div class="fw-bold font-monospace text-danger fs-6">৳ {{ formatNum(rs.loss) }}</div>
                    </div>
                    <!-- Progress Bar for loss share -->
                    <div class="progress mt-2" style="height: 6px;">
                      <div
                        class="progress-bar"
                        :class="rs.reason === 'Expired' ? 'bg-danger' : (rs.reason.includes('Damaged') ? 'bg-warning' : 'bg-primary')"
                        role="progressbar"
                        :style="{ width: rs.percentage + '%' }"
                        :aria-valuenow="rs.percentage"
                        aria-valuemin="0"
                        aria-valuemax="100"
                      ></div>
                    </div>
                    <small class="text-muted mt-1 d-block text-end" style="font-size: 10px;">{{ rs.percentage }}% {{ $t('of total loss') }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 🛡️ Approval Workflow & Stock Control Card -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Wastage Verification & Stock Deduction Control') }}</h6>
              <small class="text-muted" style="font-size: 11px;">{{ $t('Management authority to approve stock write-off or restore inventory') }}</small>
            </div>
          </div>
          <span class="badge" :class="(data.approved_by || data.status === 'approved') ? 'bg-success text-white' : 'bg-secondary text-white'">
            {{ (data.approved_by || data.status === 'approved') ? $t('Authorized') : $t('Pending Authorization') }}
          </span>
        </div>
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-lg-8 col-12 mb-3 mb-lg-0">
              <div class="alert alert-info border-0 shadow-sm mb-0">
                <h6 class="alert-heading fw-semibold mb-1">
                  <i class="fas fa-info-circle me-1"></i> {{ $t('Wastage Audit Guidelines') }}
                </h6>
                <p class="mb-1 small">
                  {{ $t('Carefully inspect the scrapped items, physical condition, and valuation listed above.') }}
                </p>
                <p class="mb-1 small">
                  <strong>{{ $t('Upon Approval:') }}</strong> {{ $t('The scrapped quantities will be automatically deducted from your live inventory stock ledger, and your approval credentials will be permanently stamped.') }}
                </p>
                <p class="mb-0 small text-danger fw-semibold">
                  <strong>{{ $t('If Approval is Canceled:') }}</strong> {{ $t('The scrapped quantities will be restored back into stock, and the audit status will revert to pending.') }}
                </p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-lg-4 col-12 text-lg-end text-center">
              <div v-if="!data.approved_by && data.status !== 'approved'">
                <button
                  type="button"
                  v-if="$root.checkPermission('wastage.approved')"
                  @click="approved"
                  class="btn btn-success fw-bold px-4 py-2 shadow-sm"
                >
                  <i class="fas fa-check-circle me-1"></i> {{ $t('Approve Wastage Audit') }}
                </button>
              </div>
              <div v-else>
                <button
                  type="button"
                  v-if="$root.checkPermission('wastage.approvalcancel')"
                  @click="approvalcancel"
                  class="btn btn-outline-danger fw-bold px-4 py-2 shadow-sm"
                >
                  <i class="fas fa-times-circle me-1"></i> {{ $t('Cancel Approval & Restore Stock') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Official Printable Scrap Disposal Voucher Sheet (#wastage_print_area) -->
    <div id="wastage_print_area" class="d-none d-print-block print-voucher-page">
      <!-- Corporate Header -->
      <div class="text-center pb-3 mb-3 border-bottom">
        <h3 class="fw-bold mb-0 text-uppercase">{{ $root.site?.name || 'QPOS Retail & Wholesale' }}</h3>
        <p class="small text-muted mb-1">{{ $root.site?.address || '' }}</p>
        <p class="small text-muted mb-2 font-monospace">
          <span v-if="$root.site?.mobile">{{ $t('Tel') }}: {{ $root.site.mobile }}</span>
          <span v-if="$root.site?.email" class="ms-3">{{ $t('Email') }}: {{ $root.site.email }}</span>
        </p>
        <div class="d-inline-block border border-dark px-3 py-1 fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 1.5px;">
          {{ $t('WASTAGE & SCRAP DISPOSAL VOUCHER') }}
        </div>
      </div>

      <!-- Metadata Box -->
      <div class="row g-2 mb-3">
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-meta-table">
            <tbody>
              <tr>
                <th width="45%" class="bg-light">{{ $t('Audit Number') }}:</th>
                <td class="font-monospace fw-bold">{{ data.audit_number || ('WST-' + data.id) }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Audit Date') }}:</th>
                <td>{{ data.audit_date }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Branch / Store') }}:</th>
                <td>{{ data.branch?.title || $t('Main Store') }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Auditor / Team') }}:</th>
                <td>{{ data.auditor?.title || data.auditor?.full_name || data.audited_by || 'Store Auditor' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-meta-table">
            <tbody>
              <tr>
                <th width="45%" class="bg-light">{{ $t('Disposal Status') }}:</th>
                <td class="fw-bold">{{ (data.approved_by || data.status === 'approved') ? $t('APPROVED & SCRAPPED') : $t('PENDING APPROVAL') }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Approved By') }}:</th>
                <td>{{ data.approved_admin?.name || data.approved_admin?.full_name || $t('Pending') }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Approved Date') }}:</th>
                <td>{{ data.approved_date || $t('N/A') }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Logged By') }}:</th>
                <td>{{ data.creator?.name || 'Admin' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Itemized Table -->
      <table class="table table-bordered align-middle print-items-table mb-3">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 35px;">#</th>
            <th class="text-start">{{ $t('Product Details & Barcode') }}</th>
            <th style="width: 100px;" v-if="hasAnyVariants">{{ $t('Variant') }}</th>
            <th style="width: 120px;">{{ $t('Reason') }}</th>
            <th style="width: 90px;">{{ $t('Quantity') }}</th>
            <th class="text-end" style="width: 100px;">{{ $t('Unit Cost') }}</th>
            <th class="text-end" style="width: 110px;">{{ $t('Total Loss') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data.wastage_details" :key="index">
            <td class="text-center font-monospace">{{ index + 1 }}</td>
            <td>
              <div class="fw-bold text-dark">{{ item.item?.title || $t('Item #') + item.item_id }}</div>
              <small class="text-muted d-block font-monospace" v-if="item.item?.barcode">
                Barcode: {{ item.item.barcode }}
              </small>
            </td>
            <td v-if="hasAnyVariants" class="text-center small">
              <span v-if="item.color?.title">{{ item.color.title }}</span>
              <span v-if="item.size?.title"> / {{ item.size.title }}</span>
              <span v-if="!item.color?.title && !item.size?.title">-</span>
            </td>
            <td class="small">
              <strong>{{ item.reason || '-' }}</strong>
              <div v-if="item.remarks" class="fst-italic text-muted">{{ item.remarks }}</div>
            </td>
            <td class="text-center font-monospace fw-bold">
              {{ item.quantity }} {{ item.unit?.title || item.item?.unit?.title || $t('Pcs') }}
            </td>
            <td class="text-end font-monospace">৳ {{ formatNum(item.unit_price) }}</td>
            <td class="text-end font-monospace fw-bold">৳ {{ formatNum(item.total_amount) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="fw-bold">
            <td :colspan="3 + (hasAnyVariants ? 1 : 0)" class="text-end text-uppercase">{{ $t('Grand Total:') }}</td>
            <td class="text-center font-monospace">{{ data.total_qty || 0 }}</td>
            <td></td>
            <td class="text-end font-monospace">৳ {{ formatNum(data.total_loss_amount) }}</td>
          </tr>
        </tfoot>
      </table>

      <!-- In Words & Notes -->
      <div class="p-2 border bg-light mb-4 small">
        <div><strong>{{ $t('Amount in Words:') }}</strong> {{ inWordsText }}</div>
        <div v-if="data.note" class="mt-1"><strong>{{ $t('Audit Justification / Note:') }}</strong> {{ data.note }}</div>
      </div>

      <!-- Signature Blocks -->
      <div class="row pt-5 mt-4 text-center">
        <div class="col-4">
          <div class="border-top pt-2 mx-3">
            <strong class="d-block text-dark">{{ data.auditor?.title || data.auditor?.full_name || data.audited_by || $t('Auditor') }}</strong>
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
            <strong class="d-block text-dark">{{ data.approved_admin?.name || data.approved_admin?.full_name || $t('Management') }}</strong>
            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $t('Authorized Approval & Seal') }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Serial Numbers Modal (for Electronics / Serialized Items) -->
    <div
      v-if="activeSerialItem"
      class="modal fade show d-block"
      style="background: rgba(0, 0, 0, 0.5); z-index: 1060;"
      tabindex="-1"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
          <div class="modal-header theme-bg text-white py-3 px-4">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-barcode"></i>
              <div>
                <h6 class="modal-title fw-bold mb-0 text-white">{{ $t('Serial Number Details') }}</h6>
                <small class="text-white-50" style="font-size: 11px;">{{ activeSerialItem.item?.title || $t('Product') }}</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="activeSerialItem = null"></button>
          </div>
          <div class="modal-body p-4">
            <div class="p-3 bg-light rounded border text-center font-monospace fs-5 fw-bold text-dark mb-3">
              {{ activeSerialItem.serial_no }}
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="copySerial(activeSerialItem.serial_no)">
                <i class="far fa-copy me-1"></i> {{ $t('Copy Serial Number') }}
              </button>
            </div>
          </div>
          <div class="modal-footer py-2 px-4 bg-light border-top">
            <button type="button" class="btn btn-secondary btn-sm px-4" @click="activeSerialItem = null">{{ $t('Close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "wastage";

export default {
  name: "WastageView",
  data() {
    return {
      page_title: "Wastage Details",
      model: model,
      data: {},
      fileColumns: [],
      activeSerialItem: null,
    };
  },

  computed: {
    hasAnyVariants() {
      return this.data.wastage_details?.some(
        (d) => d.color_id || d.size_id || d.color?.title || d.size?.title
      );
    },

    hasAnySerials() {
      return this.data.wastage_details?.some(
        (d) => Boolean(d.serial_no && String(d.serial_no).trim())
      );
    },

    uniqueCategoriesCount() {
      if (!this.data.wastage_details) return 0;
      const cats = new Set(
        this.data.wastage_details
          .map((d) => d.category?.title || d.item?.category?.title)
          .filter(Boolean)
      );
      return cats.size || 1;
    },

    dominantReason() {
      if (!this.data.wastage_details || this.data.wastage_details.length === 0) return "";
      const counts = {};
      this.data.wastage_details.forEach((d) => {
        const r = d.reason || "Other";
        counts[r] = (counts[r] || 0) + 1;
      });
      let best = "";
      let max = 0;
      for (const r in counts) {
        if (counts[r] > max) {
          max = counts[r];
          best = r;
        }
      }
      return best;
    },

    dominantReasonLines() {
      if (!this.dominantReason || !this.data.wastage_details) return 0;
      return this.data.wastage_details.filter(
        (d) => (d.reason || "Other") === this.dominantReason
      ).length;
    },

    reasonSummaryList() {
      if (!this.data.wastage_details || this.data.wastage_details.length === 0) return [];
      const map = {};
      const totalLoss = parseFloat(this.data.total_loss_amount) || 1;

      this.data.wastage_details.forEach((d) => {
        const r = d.reason || "Other";
        if (!map[r]) {
          map[r] = {
            reason: r,
            count: 0,
            qty: 0,
            loss: 0,
          };
        }
        map[r].count += 1;
        map[r].qty += parseFloat(d.quantity) || 0;
        map[r].loss += parseFloat(d.total_amount) || 0;
      });

      return Object.values(map).map((entry) => ({
        ...entry,
        percentage: Math.round((entry.loss / totalLoss) * 100),
      }));
    },

    inWordsText() {
      const num = parseFloat(this.data?.total_loss_amount);
      if (isNaN(num) || num <= 0) return "Zero Taka Only";

      const a = [
        "", "One ", "Two ", "Three ", "Four ", "Five ", "Six ", "Seven ", "Eight ", "Nine ",
        "Ten ", "Eleven ", "Twelve ", "Thirteen ", "Fourteen ", "Fifteen ", "Sixteen ",
        "Seventeen ", "Eighteen ", "Nineteen ",
      ];
      const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

      const inWords = (n) => {
        let str = "";
        if (n >= 10000000) {
          str += inWords(Math.floor(n / 10000000)) + "Crore ";
          n %= 10000000;
        }
        if (n >= 100000) {
          str += inWords(Math.floor(n / 100000)) + "Lakh ";
          n %= 100000;
        }
        if (n >= 1000) {
          str += inWords(Math.floor(n / 1000)) + "Thousand ";
          n %= 1000;
        }
        if (n >= 100) {
          str += inWords(Math.floor(n / 100)) + "Hundred ";
          n %= 100;
        }
        if (n > 0) {
          if (n < 20) {
            str += a[n];
          } else {
            str += b[Math.floor(n / 10)] + " " + a[n % 10];
          }
        }
        return str;
      };

      const integerPart = Math.floor(num);
      const decimalPart = Math.round((num - integerPart) * 100);
      let result = inWords(integerPart).trim() + " Taka";
      if (decimalPart > 0) {
        result += " and " + inWords(decimalPart).trim() + " Poisa";
      }
      return result + " Only";
    },
  },

  methods: {
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    getReasonBadgeClass(reason) {
      if (!reason) return "bg-secondary";
      const r = reason.toLowerCase();
      if (r.includes("expired")) return "bg-danger";
      if (r.includes("damage")) return "bg-warning text-dark";
      if (r.includes("broken") || r.includes("defect")) return "bg-purple text-white";
      if (r.includes("theft") || r.includes("lost")) return "bg-dark text-white";
      if (r.includes("spill") || r.includes("leak")) return "bg-info text-dark";
      return "bg-secondary text-white";
    },

    getReasonIcon(reason) {
      if (!reason) return "fas fa-info-circle";
      const r = reason.toLowerCase();
      if (r.includes("expired")) return "fas fa-calendar-times";
      if (r.includes("damage")) return "fas fa-box-tissue";
      if (r.includes("broken") || r.includes("defect")) return "fas fa-crutch";
      if (r.includes("theft") || r.includes("lost")) return "fas fa-user-secret";
      if (r.includes("spill") || r.includes("leak")) return "fas fa-tint";
      return "fas fa-tag";
    },

    openSerialModal(item) {
      this.activeSerialItem = item;
    },

    copySerial(sn) {
      navigator.clipboard.writeText(sn).then(() => {
        this.$toast(`Copied serial: ${sn}`, "success");
      });
    },

    printWastageVoucher() {
      const title = "Wastage & Scrap Disposal Voucher - " + (this.data.audit_number || this.data.id);
      this.print("wastage_print_area", title);
    },

    approved() {
      if (!confirm("Are you sure you want to approve this wastage audit? It will deduct stock from inventory.")) {
        return;
      }
      axios
        .post("wastage/approved", {
          wastage_id: this.$route.params.id,
        })
        .then((res) => {
          this.$toast(res.data.message || "Approved successfully", "success");
          this.get_data(`${this.model}/${this.$route.params.id}`);
        })
        .catch((err) => {
          this.$toast(err.response?.data?.message || "Approval failed", "error");
        });
    },

    approvalcancel() {
      if (!confirm("Are you sure you want to cancel the approval of this wastage? Deducted stock will be restored.")) {
        return;
      }

      axios
        .post("wastage/approvalcancel", {
          wastage_id: this.$route.params.id,
        })
        .then((res) => {
          this.$toast(res.data.message || "Approval cancelled successfully", "success");
          this.get_data(`${this.model}/${this.$route.params.id}`);
        })
        .catch((err) => {
          this.$toast(err.response?.data?.message || "Approval cancel failed", "error");
        });
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} Details`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.wastage-view-wrapper {
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

.wastage-hero-banner {
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
