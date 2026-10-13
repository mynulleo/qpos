<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" printArea="client_print_area">
    <!-- 💻 1. Interactive Web Dashboard View (Full Executive Theme UI) -->
    <div class="client-view-wrapper client-web-view d-print-none">
      
      <!-- 🌟 Hero Profile Banner Card -->
      <div class="card border-0 shadow-sm mb-4 client-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            
            <!-- Left: Avatar & Primary Info -->
            <div class="d-flex align-items-center gap-3">
              <div class="hero-avatar rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                <span class="avatar-initials fw-bold">{{ clientInitials }}</span>
              </div>

              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h3 class="fw-bold mb-0 text-white">{{ data.name || data.org_name || $t('Client Profile') }}</h3>
                  
                  <span class="badge bg-white text-dark font-monospace shadow-sm">
                    <i class="fas fa-id-badge me-1 text-primary"></i>#{{ data.clientid || data.id }}
                  </span>

                  <!-- Customer Type Badge -->
                  <span
                    class="badge text-uppercase px-2.5 py-1 fw-bold shadow-sm"
                    :class="data.customer_type === 'wholesale' ? 'bg-primary text-white' : 'bg-info text-dark'"
                  >
                    <i :class="data.customer_type === 'wholesale' ? 'fas fa-boxes me-1' : 'fas fa-user me-1'"></i>
                    {{ data.customer_type === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                  </span>

                  <!-- Status Badge -->
                  <span
                    class="badge px-2.5 py-1 fw-bold text-uppercase shadow-sm"
                    :class="data.status === 'active' || data.status === 'Active' || data.status == 1 ? 'bg-success text-white' : 'bg-secondary text-white'"
                  >
                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                    {{ data.status || $t('Active') }}
                  </span>

                  <!-- Membership Tier Badge -->
                  <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold shadow-sm" v-if="data.membership_type">
                    <i class="fas fa-crown me-1 text-dark"></i>{{ data.membership_type }}
                  </span>
                </div>

                <!-- Sub-title / Org Name -->
                <div v-if="data.org_name && data.name && data.org_name !== data.name" class="text-white-50 mt-1 fw-semibold fs-6">
                  <i class="fas fa-building me-1 text-warning"></i>{{ data.org_name }}
                </div>

                <!-- Contact & Meta Chips -->
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap">
                  <!-- Mobile Phone -->
                  <span class="contact-pill" v-if="data.mobile">
                    <a :href="'tel:' + data.mobile" class="text-white text-decoration-none fw-semibold">
                      <i class="fas fa-phone-alt me-1 text-warning"></i>{{ data.mobile }}
                    </a>
                    <button type="button" class="btn btn-link btn-sm text-white-50 p-0 ms-1 copy-btn" @click="copyText(data.mobile)" :title="$t('Copy Number')">
                      <i class="far fa-copy"></i>
                    </button>
                  </span>

                  <!-- Email -->
                  <span class="contact-pill" v-if="data.email">
                    <a :href="'mailto:' + data.email" class="text-white text-decoration-none fw-semibold">
                      <i class="fas fa-envelope me-1 text-warning"></i>{{ data.email }}
                    </a>
                    <button type="button" class="btn btn-link btn-sm text-white-50 p-0 ms-1 copy-btn" @click="copyText(data.email)" :title="$t('Copy Email')">
                      <i class="far fa-copy"></i>
                    </button>
                  </span>

                  <!-- Address / Location -->
                  <span v-if="data.address || data.district">
                    <i class="fas fa-map-marker-alt me-1 text-warning"></i>
                    <span class="text-white">
                      {{ data.address }}
                      <template v-if="data.district?.district_name">
                        ({{ data.district.district_name }}<template v-if="data.area?.area_name">, {{ data.area.area_name }}</template>)
                      </template>
                    </span>
                  </span>

                  <!-- Registration Date -->
                  <span v-if="data.reg_date">
                    <i class="far fa-calendar-alt me-1 text-warning"></i>
                    {{ $t('Registered:') }} <strong class="text-white">{{ data.reg_date }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right: Quick Action Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
              <!-- New POS Sale -->
              <router-link
                :to="{ name: 'pos.index', query: { client_id: data.id } }"
                class="btn btn-success btn-sm fw-bold px-3 shadow-sm d-flex align-items-center gap-1.5"
                :title="$t('Create new sale for this client in POS')"
              >
                <i class="fas fa-cash-register"></i>
                <span>{{ $t('New POS Sale') }}</span>
              </router-link>

              <!-- Receive Payment -->
              <router-link
                :to="{ name: 'payment.create', query: { client_id: data.id } }"
                class="btn btn-warning btn-sm fw-bold px-3 shadow-sm text-dark d-flex align-items-center gap-1.5"
                :title="$t('Receive payment from this client')"
              >
                <i class="fas fa-hand-holding-usd"></i>
                <span>{{ $t('Receive Payment') }}</span>
              </router-link>

              <!-- Edit Client Profile -->
              <router-link
                :to="{ name: 'client.edit', params: { id: data.id } }"
                class="btn btn-outline-light btn-sm fw-bold px-3 d-flex align-items-center gap-1.5"
              >
                <i class="fas fa-user-edit"></i>
                <span>{{ $t('Edit Profile') }}</span>
              </router-link>

              <!-- Print Profile Statement -->
              <button
                type="button"
                class="btn btn-light btn-sm fw-bold px-3 shadow-sm text-dark d-flex align-items-center gap-1.5"
                @click="printClientProfile"
                :title="$t('Print complete client summary statement')"
              >
                <i class="fas fa-print"></i>
                <span>{{ $t('Print Profile') }}</span>
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- 📊 Top Executive Metric KPI Cards (5 Primary Metrics) -->
      <div class="row g-3 mb-4">
        
        <!-- 1. Total Purchases -->
        <div class="col-xl col-md-4 col-sm-6">
          <div class="card border-0 shadow-sm h-100 metric-card metric-primary">
            <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
              <div class="overflow-hidden">
                <span class="text-muted small fw-bold text-uppercase d-block text-truncate">{{ $t('Total Purchases') }}</span>
                <h4 class="fw-bold mb-1 font-monospace text-dark text-nowrap">
                  <span class="text-nowrap">৳&nbsp;{{ formatNum(summary.total_purchase) }}</span>
                </h4>
                <small class="text-secondary text-nowrap d-block">
                  <strong>{{ summary.total_invoices || 0 }}</strong> {{ $t('Invoices') }}
                  <span class="text-success ms-1">({{ summary.paid_invoices_count || 0 }} {{ $t('Paid') }})</span>
                </small>
              </div>
              <div class="metric-icon-box bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-shopping-bag fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Total Paid -->
        <div class="col-xl col-md-4 col-sm-6">
          <div class="card border-0 shadow-sm h-100 metric-card metric-success">
            <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
              <div class="overflow-hidden">
                <span class="text-muted small fw-bold text-uppercase d-block text-truncate">{{ $t('Total Paid') }}</span>
                <h4 class="fw-bold mb-1 font-monospace text-success text-nowrap">
                  <span class="text-nowrap">৳&nbsp;{{ formatNum(summary.total_paid) }}</span>
                </h4>
                <small class="text-secondary text-nowrap d-block">
                  <strong>{{ summary.total_payments_count || 0 }}</strong> {{ $t('Payments') }}
                </small>
              </div>
              <div class="metric-icon-box bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-wallet fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Total Due (Highlighted if outstanding) -->
        <div class="col-xl col-md-4 col-sm-6">
          <div
            class="card border-0 shadow-sm h-100 metric-card"
            :class="summary.total_due > 0 ? 'metric-danger border-start-danger' : 'metric-success'"
          >
            <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
              <div class="overflow-hidden">
                <span class="text-muted small fw-bold text-uppercase d-block text-truncate">
                  {{ $t('Total Outstanding Due') }}
                </span>
                <h4
                  class="fw-bold mb-1 font-monospace text-nowrap"
                  :class="summary.total_due > 0 ? 'text-danger' : 'text-success'"
                >
                  <span class="text-nowrap">৳&nbsp;{{ formatNum(summary.total_due) }}</span>
                </h4>
                <small class="text-secondary d-block text-nowrap">
                  <span v-if="summary.previous_due > 0" class="text-nowrap">Prev: ৳&nbsp;{{ formatNum(summary.previous_due) }} | </span>
                  <span class="text-nowrap">Invoice: ৳&nbsp;{{ formatNum(summary.invoice_due) }}</span>
                </small>
              </div>
              <div
                class="metric-icon-box rounded-circle d-flex align-items-center justify-content-center"
                :class="summary.total_due > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success'"
              >
                <i class="fas fa-file-invoice-dollar fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Loyalty Points Balance -->
        <div class="col-xl col-md-6 col-sm-6">
          <div class="card border-0 shadow-sm h-100 metric-card metric-warning">
            <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
              <div class="overflow-hidden">
                <span class="text-muted small fw-bold text-uppercase d-block text-truncate">{{ $t('Loyalty Points') }}</span>
                <h4 class="fw-bold mb-1 font-monospace text-warning-dark text-nowrap">
                  {{ formatPoints(summary.points_balance) }} <small class="fs-6 fw-normal text-muted">{{ $t('Pts') }}</small>
                </h4>
                <small class="text-secondary text-nowrap d-block">
                  ≈ <strong class="text-nowrap">৳&nbsp;{{ formatNum(summary.points_cash_value) }}</strong>
                  <span class="text-muted ms-1">({{ summary.total_points_earned || 0 }} {{ $t('Earned') }})</span>
                </small>
              </div>
              <div class="metric-icon-box bg-warning bg-opacity-15 text-warning-dark rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-coins fs-5"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. Sales Returns -->
        <div class="col-xl col-md-6 col-sm-6">
          <div class="card border-0 shadow-sm h-100 metric-card metric-info">
            <div class="card-body p-3 d-flex align-items-center justify-content-between gap-2">
              <div class="overflow-hidden">
                <span class="text-muted small fw-bold text-uppercase d-block text-truncate">{{ $t('Sales Returns') }}</span>
                <h4 class="fw-bold mb-1 font-monospace text-dark text-nowrap">
                  <span class="text-nowrap">৳&nbsp;{{ formatNum(summary.total_return_amount) }}</span>
                </h4>
                <small class="text-secondary text-nowrap d-block">
                  <strong>{{ summary.total_returns || 0 }}</strong> {{ $t('Returns Recorded') }}
                </small>
              </div>
              <div class="metric-icon-box bg-info bg-opacity-15 text-info rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-undo-alt fs-5"></i>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- 📑 Deep-Dive Interactive Tabs Navigation -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-2 px-3">
          <ul class="nav nav-pills client-nav-pills gap-1 flex-wrap">
            
            <!-- Tab 1: Client Full Information & Bank (DEFAULT ACTIVE TAB) -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'profile' }"
                @click="activeTab = 'profile'"
              >
                <i class="fas fa-id-card me-1.5"></i>
                <span>{{ $t('Client Information & Bank') }}</span>
              </button>
            </li>

            <!-- Tab 2: Invoices / Purchases -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'invoices' }"
                @click="activeTab = 'invoices'"
              >
                <i class="fas fa-file-invoice me-1.5"></i>
                <span>{{ $t('Invoices / Purchases') }}</span>
                <span class="badge ms-1.5 rounded-pill" :class="activeTab === 'invoices' ? 'bg-white text-dark' : 'bg-secondary text-white'">
                  {{ (data.invoices || []).length }}
                </span>
              </button>
            </li>

            <!-- Tab 3: Payments History -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'payments' }"
                @click="activeTab = 'payments'"
              >
                <i class="fas fa-receipt me-1.5"></i>
                <span>{{ $t('Payment History') }}</span>
                <span class="badge ms-1.5 rounded-pill" :class="activeTab === 'payments' ? 'bg-white text-dark' : 'bg-secondary text-white'">
                  {{ (data.payments || []).length }}
                </span>
              </button>
            </li>

            <!-- Tab 4: Sales Returns -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'returns' }"
                @click="activeTab = 'returns'"
              >
                <i class="fas fa-undo me-1.5"></i>
                <span>{{ $t('Sales Returns') }}</span>
                <span class="badge ms-1.5 rounded-pill" :class="activeTab === 'returns' ? 'bg-white text-dark' : 'bg-secondary text-white'">
                  {{ (data.sales_returns || []).length }}
                </span>
              </button>
            </li>

            <!-- Tab 5: Loyalty Points -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'points' }"
                @click="activeTab = 'points'"
              >
                <i class="fas fa-gem me-1.5"></i>
                <span>{{ $t('Loyalty Points') }}</span>
                <span class="badge ms-1.5 rounded-pill" :class="activeTab === 'points' ? 'bg-white text-dark' : 'bg-secondary text-white'">
                  {{ (data.point_transactions || []).length }}
                </span>
              </button>
            </li>

            <!-- Tab 6: Warranty Claims -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'warranty' }"
                @click="activeTab = 'warranty'"
              >
                <i class="fas fa-shield-alt me-1.5"></i>
                <span>{{ $t('Warranty Claims') }}</span>
                <span class="badge ms-1.5 rounded-pill" :class="activeTab === 'warranty' ? 'bg-white text-dark' : 'bg-secondary text-white'">
                  {{ (data.warranty_claims || []).length }}
                </span>
              </button>
            </li>

            <!-- Tab 7: Quotations -->
            <li class="nav-item">
              <button
                type="button"
                class="nav-link"
                :class="{ 'active': activeTab === 'quotations' }"
                @click="activeTab = 'quotations'"
              >
                <i class="fas fa-file-contract me-1.5"></i>
                <span>{{ $t('Quotations') }}</span>
                <span class="badge ms-1.5 rounded-pill" :class="activeTab === 'quotations' ? 'bg-white text-dark' : 'bg-secondary text-white'">
                  {{ (data.quotations || []).length }}
                </span>
              </button>
            </li>

          </ul>
        </div>

        <!-- Tab Body Content -->
        <div class="card-body p-3 p-md-4">
          
          <!-- ================= TAB 1: FULL CLIENT INFORMATION & BANK (DEFAULT ACTIVE) ================= -->
          <div v-if="activeTab === 'profile'">
            <div class="row g-4">
              
              <!-- Card A: Personal & Contact Information -->
              <div class="col-md-6">
                <div class="card border shadow-none h-100">
                  <div class="card-header bg-light py-2 px-3 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-user-circle text-primary"></i>
                    <span>{{ $t('Personal & Contact Information') }}</span>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped mb-0 custom-info-table">
                      <tbody>
                        <tr>
                          <th width="40%">{{ $t('Full Name') }}</th>
                          <td>{{ data.name || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Organization / Business') }}</th>
                          <td>{{ data.org_name || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Mobile Phone') }}</th>
                          <td class="font-monospace fw-bold">
                            <a :href="'tel:' + data.mobile" class="text-dark text-decoration-none" v-if="data.mobile">
                              {{ data.mobile }}
                            </a>
                            <span v-else>-</span>
                          </td>
                        </tr>
                        <tr>
                          <th>{{ $t('Email Address') }}</th>
                          <td>
                            <a :href="'mailto:' + data.email" v-if="data.email">{{ data.email }}</a>
                            <span v-else>-</span>
                          </td>
                        </tr>
                        <tr>
                          <th>{{ $t('Customer Type') }}</th>
                          <td>
                            <span class="badge" :class="data.customer_type === 'wholesale' ? 'bg-primary' : 'bg-info text-dark'">
                              {{ data.customer_type === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <th>{{ $t('Membership Tier') }}</th>
                          <td>{{ data.membership_type || 'Regular' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Card B: Address & Location -->
              <div class="col-md-6">
                <div class="card border shadow-none h-100">
                  <div class="card-header bg-light py-2 px-3 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-map-marked-alt text-success"></i>
                    <span>{{ $t('Location & Address') }}</span>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped mb-0 custom-info-table">
                      <tbody>
                        <tr>
                          <th width="40%">{{ $t('Street Address') }}</th>
                          <td>{{ data.address || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('District') }}</th>
                          <td>{{ data.district?.district_name || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Area / Thana') }}</th>
                          <td>{{ data.area?.area_name || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Full Geographic Location') }}</th>
                          <td>
                            {{ data.address }}
                            <template v-if="data.district">
                              , {{ data.district.district_name }}
                              <template v-if="data.area">({{ data.area.area_name }})</template>
                            </template>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Card C: Bank & Financial Configuration -->
              <div class="col-md-6">
                <div class="card border shadow-none h-100">
                  <div class="card-header bg-light py-2 px-3 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-university text-warning"></i>
                    <span>{{ $t('Banking & Financial Details') }}</span>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped mb-0 custom-info-table">
                      <tbody>
                        <tr>
                          <th width="40%">{{ $t('Account Name') }}</th>
                          <td>{{ data.account_name || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Account Number') }}</th>
                          <td class="font-monospace fw-bold">{{ data.account_no || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Bank Name') }}</th>
                          <td>{{ data.bank?.bank_name || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Branch Name') }}</th>
                          <td>{{ data.branch || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Previous Due Balance') }}</th>
                          <td class="font-monospace fw-bold text-nowrap" :class="data.previous_due > 0 ? 'text-danger' : 'text-success'">
                            <span class="text-nowrap">৳&nbsp;{{ formatNum(data.previous_due) }}</span>
                          </td>
                        </tr>
                        <tr>
                          <th>{{ $t('Due Limit (Credit Limit)') }}</th>
                          <td class="font-monospace fw-bold text-dark text-nowrap">
                            <span class="text-nowrap">৳&nbsp;{{ formatNum(data.due_limit) }}</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Card D: System & Office Metadata -->
              <div class="col-md-6">
                <div class="card border shadow-none h-100">
                  <div class="card-header bg-light py-2 px-3 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-cog text-secondary"></i>
                    <span>{{ $t('System & Registration Information') }}</span>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped mb-0 custom-info-table">
                      <tbody>
                        <tr>
                          <th width="40%">{{ $t('Client Unique ID') }}</th>
                          <td class="font-monospace fw-bold text-primary">#{{ data.clientid || data.id }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Registration Date') }}</th>
                          <td class="font-monospace">{{ data.reg_date || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Account Status') }}</th>
                          <td>
                            <span class="badge" :class="data.status === 'active' || data.status == 1 ? 'bg-success' : 'bg-secondary'">
                              {{ data.status || 'Active' }}
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <th>{{ $t('Internal Notes / Remarks') }}</th>
                          <td class="small">{{ data.note || '-' }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Created At') }}</th>
                          <td class="font-monospace small text-muted">{{ formatDate(data.created_at) }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Last Updated At') }}</th>
                          <td class="font-monospace small text-muted">{{ formatDate(data.updated_at) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- ================= TAB 2: INVOICES & PURCHASES ================= -->
          <div v-else-if="activeTab === 'invoices'">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
              <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark">
                  <i class="fas fa-shopping-cart text-primary me-2"></i>{{ $t('Sales Invoices & Purchase History') }}
                </h5>
                <span class="badge bg-light text-muted border font-monospace">{{ filteredInvoices.length }} {{ $t('records') }}</span>
              </div>

              <!-- Quick Invoice Search & Filter -->
              <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: 240px;">
                  <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                  <input
                    type="text"
                    class="form-control form-control-sm border-start-0"
                    :placeholder="$t('Filter invoice no / date...')"
                    v-model="invoiceSearch"
                  >
                </div>

                <select class="form-select form-select-sm" style="width: 140px;" v-model="invoiceStatusFilter">
                  <option value="all">{{ $t('All Status') }}</option>
                  <option value="paid">{{ $t('Paid Only') }}</option>
                  <option value="due">{{ $t('Due Only') }}</option>
                </select>
              </div>
            </div>

            <!-- Invoices Table -->
            <div class="table-responsive" v-if="filteredInvoices.length > 0">
              <table class="table table-hover align-middle border mb-0 custom-profile-table">
                <thead class="table-light">
                  <tr>
                    <th width="4%">#</th>
                    <th>{{ $t('Invoice No') }}</th>
                    <th>{{ $t('Date') }}</th>
                    <th class="text-end">{{ $t('Original') }}</th>
                    <th class="text-end">{{ $t('Discount') }}</th>
                    <th class="text-end">{{ $t('VAT') }}</th>
                    <th class="text-end">{{ $t('Total') }}</th>
                    <th class="text-end">{{ $t('Paid') }}</th>
                    <th class="text-end">{{ $t('Due') }}</th>
                    <th class="text-center">{{ $t('Status') }}</th>
                    <th class="text-center" width="10%">{{ $t('Action') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(inv, idx) in filteredInvoices" :key="'inv_' + inv.id">
                    <td class="text-muted font-monospace">{{ idx + 1 }}</td>
                    
                    <td>
                      <router-link
                        :to="{ name: 'invoice.bill', params: { id: inv.id } }"
                        class="fw-bold font-monospace text-decoration-none theme-text"
                        :title="$t('View Invoice Bill')"
                      >
                        {{ inv.invoice_no }}
                      </router-link>
                    </td>

                    <td>
                      <span class="small font-monospace">{{ inv.invoice_date }}</span>
                    </td>

                    <td class="text-end font-monospace text-nowrap">৳&nbsp;{{ formatNum(inv.original_amount) }}</td>
                    
                    <td class="text-end font-monospace text-muted text-nowrap">
                      <span v-if="inv.discount > 0">-৳&nbsp;{{ formatNum(inv.discount) }}</span>
                      <span v-else>0.00</span>
                    </td>

                    <td class="text-end font-monospace text-muted text-nowrap">+৳&nbsp;{{ formatNum(inv.vat) }}</td>
                    
                    <td class="text-end font-monospace fw-bold text-dark text-nowrap">
                      ৳&nbsp;{{ formatNum(inv.amount) }}
                    </td>

                    <td class="text-end font-monospace text-success fw-semibold text-nowrap">
                      ৳&nbsp;{{ formatNum(inv.paid_amount) }}
                    </td>

                    <td class="text-end font-monospace fw-bold text-nowrap" :class="(inv.amount - inv.paid_amount) > 0 ? 'text-danger' : 'text-success'">
                      ৳&nbsp;{{ formatNum(inv.amount - inv.paid_amount) }}
                    </td>

                    <td class="text-center">
                      <span
                        class="badge rounded-pill px-2.5 py-1 fw-bold"
                        :class="inv.is_closed ? 'bg-success text-white' : (inv.paid_amount > 0 ? 'bg-warning text-dark' : 'bg-danger text-white')"
                      >
                        {{ inv.is_closed ? $t('Paid') : (inv.paid_amount > 0 ? $t('Partial') : $t('Due')) }}
                      </span>
                    </td>

                    <td class="text-center">
                      <router-link
                        :to="{ name: 'invoice.bill', params: { id: inv.id } }"
                        class="btn btn-outline-primary btn-xs py-1 px-2 fw-semibold shadow-sm"
                        :title="$t('View Bill')"
                      >
                        <i class="fas fa-file-invoice me-1"></i>{{ $t('Bill') }}
                      </router-link>
                    </td>
                  </tr>
                </tbody>
                <!-- Table Footer Summary -->
                <tfoot class="table-light fw-bold font-monospace">
                  <tr>
                    <td colspan="6" class="text-end">{{ $t('Summary Total:') }}</td>
                    <td class="text-end text-dark text-nowrap">৳&nbsp;{{ formatNum(summary.total_purchase) }}</td>
                    <td class="text-end text-success text-nowrap">৳&nbsp;{{ formatNum(summary.total_paid) }}</td>
                    <td class="text-end text-danger text-nowrap">৳&nbsp;{{ formatNum(summary.invoice_due) }}</td>
                    <td colspan="2"></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5 text-muted" v-else>
              <i class="fas fa-shopping-bag fa-3x mb-3 text-secondary opacity-50"></i>
              <h6 class="fw-bold">{{ $t('No Invoices Found') }}</h6>
              <p class="small text-muted">{{ $t('This client does not have any sales invoice matching the criteria.') }}</p>
              <router-link :to="{ name: 'pos.index', query: { client_id: data.id } }" class="btn btn-primary btn-sm px-3 mt-2">
                <i class="fas fa-plus me-1"></i>{{ $t('Create First Sale') }}
              </router-link>
            </div>
          </div>

          <!-- ================= TAB 3: PAYMENT HISTORY ================= -->
          <div v-else-if="activeTab === 'payments'">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
              <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark">
                  <i class="fas fa-receipt text-success me-2"></i>{{ $t('Payments & Receipts History') }}
                </h5>
                <span class="badge bg-light text-muted border font-monospace">{{ filteredPayments.length }} {{ $t('records') }}</span>
              </div>

              <!-- Quick Search -->
              <div class="input-group input-group-sm" style="width: 260px;">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                <input
                  type="text"
                  class="form-control form-control-sm border-start-0"
                  :placeholder="$t('Filter payslip / method...')"
                  v-model="paymentSearch"
                >
              </div>
            </div>

            <!-- Payments Table -->
            <div class="table-responsive" v-if="filteredPayments.length > 0">
              <table class="table table-hover align-middle border mb-0 custom-profile-table">
                <thead class="table-light">
                  <tr>
                    <th width="4%">#</th>
                    <th>{{ $t('Payslip No') }}</th>
                    <th>{{ $t('Date') }}</th>
                    <th>{{ $t('Type') }}</th>
                    <th>{{ $t('Method') }}</th>
                    <th>{{ $t('Account / Bank') }}</th>
                    <th>{{ $t('TrxID / Cheque') }}</th>
                    <th class="text-end">{{ $t('Discount') }}</th>
                    <th class="text-end">{{ $t('Amount') }}</th>
                    <th class="text-center">{{ $t('Status') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(pay, pIdx) in filteredPayments" :key="'pay_' + pay.id">
                    <td class="text-muted font-monospace">{{ pIdx + 1 }}</td>
                    <td class="font-monospace fw-bold text-dark">{{ pay.payslipno }}</td>
                    <td class="font-monospace small">{{ pay.payment_date }}</td>
                    <td>
                      <span class="badge" :class="pay.payment_type === 'Receive' ? 'bg-success text-white' : 'bg-primary text-white'">
                        {{ pay.payment_type }}
                      </span>
                    </td>
                    <td>
                      <span class="fw-semibold text-dark">{{ pay.payment_method }}</span>
                      <small v-if="pay.mbanking_type" class="text-muted d-block font-monospace">({{ pay.mbanking_type }})</small>
                    </td>
                    <td class="small">
                      <span v-if="pay.fund_account?.account_name">{{ pay.fund_account.account_name }}</span>
                      <span v-else-if="pay.bank?.bank_name">{{ pay.bank.bank_name }}</span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="font-monospace small text-muted">
                      {{ pay.trxid || pay.chequeno || '-' }}
                    </td>
                    <td class="text-end font-monospace text-muted text-nowrap">
                      <span v-if="pay.discount > 0">৳&nbsp;{{ formatNum(pay.discount) }}</span>
                      <span v-else>-</span>
                    </td>
                    <td class="text-end font-monospace fw-bold text-success fs-6 text-nowrap">
                      ৳&nbsp;{{ formatNum(pay.amount) }}
                    </td>
                    <td class="text-center">
                      <span class="badge bg-light text-dark border">{{ pay.status }}</span>
                    </td>
                  </tr>
                </tbody>
                <!-- Table Footer Summary -->
                <tfoot class="table-light fw-bold font-monospace">
                  <tr>
                    <td colspan="8" class="text-end">{{ $t('Total Payments Received:') }}</td>
                    <td class="text-end text-success fs-6 text-nowrap">৳&nbsp;{{ formatNum(summary.total_payments_amount) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5 text-muted" v-else>
              <i class="fas fa-hand-holding-usd fa-3x mb-3 text-secondary opacity-50"></i>
              <h6 class="fw-bold">{{ $t('No Payment Records') }}</h6>
              <p class="small text-muted">{{ $t('No payments have been recorded for this client yet.') }}</p>
              <router-link :to="{ name: 'payment.create', query: { client_id: data.id } }" class="btn btn-warning btn-sm text-dark px-3 mt-2">
                <i class="fas fa-plus me-1"></i>{{ $t('Record Payment') }}
              </router-link>
            </div>
          </div>

          <!-- ================= TAB 4: SALES RETURNS ================= -->
          <div v-else-if="activeTab === 'returns'">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-undo-alt text-info me-2"></i>{{ $t('Sales Returns & Product Replacements') }}
              </h5>
              <span class="badge bg-light text-muted border font-monospace">{{ (data.sales_returns || []).length }} {{ $t('records') }}</span>
            </div>

            <!-- Returns Table -->
            <div class="table-responsive" v-if="(data.sales_returns || []).length > 0">
              <table class="table table-hover align-middle border mb-0 custom-profile-table">
                <thead class="table-light">
                  <tr>
                    <th width="4%">#</th>
                    <th>{{ $t('Return No') }}</th>
                    <th>{{ $t('Date') }}</th>
                    <th>{{ $t('Invoice Ref') }}</th>
                    <th class="text-center">{{ $t('Total Qty') }}</th>
                    <th class="text-end">{{ $t('Refund Amount') }}</th>
                    <th>{{ $t('Refund Method') }}</th>
                    <th>{{ $t('Reason / Note') }}</th>
                    <th class="text-center">{{ $t('Status') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(ret, rIdx) in data.sales_returns" :key="'ret_' + ret.id">
                    <td class="text-muted font-monospace">{{ rIdx + 1 }}</td>
                    <td class="font-monospace fw-bold text-dark">{{ ret.return_no }}</td>
                    <td class="font-monospace small">{{ ret.return_date }}</td>
                    <td class="font-monospace">
                      <span v-if="ret.invoice?.invoice_no">{{ ret.invoice.invoice_no }}</span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="text-center font-monospace fw-bold">{{ ret.total_qty }}</td>
                    <td class="text-end font-monospace fw-bold text-danger text-nowrap">
                      ৳&nbsp;{{ formatNum(ret.total_refund_amount) }}
                    </td>
                    <td>{{ ret.payment_method || 'Adjusted / Cash' }}</td>
                    <td class="small text-muted">{{ ret.return_reason || ret.note || '-' }}</td>
                    <td class="text-center">
                      <span class="badge bg-success">{{ ret.status || 'Completed' }}</span>
                    </td>
                  </tr>
                </tbody>
                <!-- Table Footer Summary -->
                <tfoot class="table-light fw-bold font-monospace">
                  <tr>
                    <td colspan="5" class="text-end">{{ $t('Total Refund Issued:') }}</td>
                    <td class="text-end text-danger text-nowrap">৳&nbsp;{{ formatNum(summary.total_return_amount) }}</td>
                    <td colspan="3"></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5 text-muted" v-else>
              <i class="fas fa-undo-alt fa-3x mb-3 text-secondary opacity-50"></i>
              <h6 class="fw-bold">{{ $t('No Sales Return Records') }}</h6>
              <p class="small text-muted">{{ $t('This client has not returned any purchased items.') }}</p>
            </div>
          </div>

          <!-- ================= TAB 5: LOYALTY POINTS ================= -->
          <div v-else-if="activeTab === 'points'">
            <!-- Loyalty Overview Card Banner -->
            <div class="card border-0 bg-light shadow-sm mb-4">
              <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                  <div class="col-md-3 border-end">
                    <span class="text-muted small fw-bold text-uppercase d-block">{{ $t('Current Point Balance') }}</span>
                    <h3 class="fw-bold mb-0 text-warning-dark font-monospace">
                      {{ formatPoints(summary.points_balance) }} <small class="fs-6 text-muted">{{ $t('Points') }}</small>
                    </h3>
                  </div>
                  <div class="col-md-3 border-end">
                    <span class="text-muted small fw-bold text-uppercase d-block">{{ $t('Estimated Cash Value') }}</span>
                    <h4 class="fw-bold mb-0 text-success font-monospace text-nowrap">
                      ৳&nbsp;{{ formatNum(summary.points_cash_value) }}
                    </h4>
                    <small class="text-muted text-nowrap">Rate: {{ summary.points_redeem_rate }} Pts = ৳&nbsp;1.00</small>
                  </div>
                  <div class="col-md-3 border-end">
                    <span class="text-muted small fw-bold text-uppercase d-block">{{ $t('Total Earned') }}</span>
                    <h4 class="fw-bold mb-0 text-primary font-monospace">
                      +{{ formatPoints(summary.total_points_earned) }}
                    </h4>
                  </div>
                  <div class="col-md-3">
                    <span class="text-muted small fw-bold text-uppercase d-block">{{ $t('Total Redeemed') }}</span>
                    <h4 class="fw-bold mb-0 text-danger font-monospace">
                      -{{ formatPoints(summary.total_points_redeemed) }}
                    </h4>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-gem text-warning me-2"></i>{{ $t('Loyalty Points Transaction Ledger') }}
              </h5>
              <span class="badge bg-light text-muted border font-monospace">{{ (data.point_transactions || []).length }} {{ $t('transactions') }}</span>
            </div>

            <!-- Points Ledger Table -->
            <div class="table-responsive" v-if="(data.point_transactions || []).length > 0">
              <table class="table table-hover align-middle border mb-0 custom-profile-table">
                <thead class="table-light">
                  <tr>
                    <th width="4%">#</th>
                    <th>{{ $t('Date') }}</th>
                    <th>{{ $t('Type') }}</th>
                    <th class="text-end">{{ $t('Points') }}</th>
                    <th class="text-end">{{ $t('Cash Equivalent') }}</th>
                    <th>{{ $t('Invoice Ref') }}</th>
                    <th>{{ $t('Balance After') }}</th>
                    <th>{{ $t('Description') }}</th>
                    <th>{{ $t('Created By') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(tx, tIdx) in data.point_transactions" :key="'pt_' + tx.id">
                    <td class="text-muted font-monospace">{{ tIdx + 1 }}</td>
                    <td class="font-monospace small">{{ tx.transaction_date || formatDate(tx.created_at) }}</td>
                    <td>
                      <span class="badge" :class="tx.type === 'Earn' ? 'bg-success text-white' : 'bg-danger text-white'">
                        {{ tx.type === 'Earn' ? $t('Earned (+)') : $t('Redeemed (-)') }}
                      </span>
                    </td>
                    <td class="text-end font-monospace fw-bold" :class="tx.type === 'Earn' ? 'text-success' : 'text-danger'">
                      {{ tx.type === 'Earn' ? '+' + formatPoints(tx.points_in) : '-' + formatPoints(tx.points_out) }}
                    </td>
                    <td class="text-end font-monospace text-muted text-nowrap">
                      ৳&nbsp;{{ formatNum(tx.amount_equivalent || (tx.points_in ? tx.points_in / 10 : tx.points_out / 10)) }}
                    </td>
                    <td class="font-monospace">
                      <router-link
                        v-if="tx.invoice_id"
                        :to="{ name: 'invoice.bill', params: { id: tx.invoice_id } }"
                        class="text-decoration-none theme-text fw-semibold"
                      >
                        {{ tx.invoice?.invoice_no || ('#' + tx.invoice_id) }}
                      </router-link>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="font-monospace fw-semibold text-dark">
                      {{ formatPoints(tx.balance || tx.balance_after) }} Pts
                    </td>
                    <td class="small">{{ tx.description || '-' }}</td>
                    <td class="small text-muted">{{ tx.creator?.full_name || tx.creator?.name || 'System' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5 text-muted" v-else>
              <i class="fas fa-coins fa-3x mb-3 text-secondary opacity-50"></i>
              <h6 class="fw-bold">{{ $t('No Loyalty Point Transactions') }}</h6>
              <p class="small text-muted">{{ $t('Points are awarded automatically upon checkout according to the loyalty program policy.') }}</p>
            </div>
          </div>

          <!-- ================= TAB 6: WARRANTY CLAIMS ================= -->
          <div v-else-if="activeTab === 'warranty'">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-shield-alt text-primary me-2"></i>{{ $t('Warranty & Guarantee Claim Tickets') }}
              </h5>
              <span class="badge bg-light text-muted border font-monospace">{{ (data.warranty_claims || []).length }} {{ $t('tickets') }}</span>
            </div>

            <!-- Warranty Claims Table -->
            <div class="table-responsive" v-if="(data.warranty_claims || []).length > 0">
              <table class="table table-hover align-middle border mb-0 custom-profile-table">
                <thead class="table-light">
                  <tr>
                    <th width="4%">#</th>
                    <th>{{ $t('Ticket No') }}</th>
                    <th>{{ $t('Claim Date') }}</th>
                    <th>{{ $t('Item Details') }}</th>
                    <th>{{ $t('Serial No') }}</th>
                    <th>{{ $t('Claim Type') }}</th>
                    <th>{{ $t('Current Status') }}</th>
                    <th class="text-end">{{ $t('Charge') }}</th>
                    <th class="text-center" width="10%">{{ $t('Action') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(clm, cIdx) in data.warranty_claims" :key="'clm_' + clm.id">
                    <td class="text-muted font-monospace">{{ cIdx + 1 }}</td>
                    <td>
                      <router-link
                        :to="{ name: 'warrantyClaim.show', params: { id: clm.id } }"
                        class="fw-bold font-monospace theme-text text-decoration-none"
                      >
                        {{ clm.claim_no }}
                      </router-link>
                    </td>
                    <td class="font-monospace small">{{ clm.claim_date }}</td>
                    <td>
                      <span class="fw-bold text-dark d-block">{{ clm.item?.title || $t('Product') }}</span>
                      <small class="text-muted font-monospace" v-if="clm.item?.barcode">{{ clm.item.barcode }}</small>
                    </td>
                    <td class="font-monospace small text-primary">{{ clm.serial_no || '-' }}</td>
                    <td>
                      <span class="badge bg-light text-dark border">{{ clm.claim_type || 'Warranty' }}</span>
                    </td>
                    <td>
                      <span class="badge" :class="getWarrantyStatusBadge(clm.current_status)">
                        {{ clm.current_status || 'Received' }}
                      </span>
                    </td>
                    <td class="text-end font-monospace text-dark text-nowrap">
                      ৳&nbsp;{{ formatNum(clm.customer_charge || 0) }}
                    </td>
                    <td class="text-center">
                      <router-link
                        :to="{ name: 'warrantyClaim.show', params: { id: clm.id } }"
                        class="btn btn-outline-primary btn-xs py-1 px-2 fw-semibold shadow-sm"
                      >
                        <i class="fas fa-eye me-1"></i>{{ $t('View') }}
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5 text-muted" v-else>
              <i class="fas fa-shield-alt fa-3x mb-3 text-secondary opacity-50"></i>
              <h6 class="fw-bold">{{ $t('No Warranty Claims') }}</h6>
              <p class="small text-muted">{{ $t('This client has not lodged any warranty service requests.') }}</p>
            </div>
          </div>

          <!-- ================= TAB 7: QUOTATIONS ================= -->
          <div v-else-if="activeTab === 'quotations'">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-file-contract text-secondary me-2"></i>{{ $t('Quotations & Price Proposals') }}
              </h5>
              <span class="badge bg-light text-muted border font-monospace">{{ (data.quotations || []).length }} {{ $t('quotations') }}</span>
            </div>

            <!-- Quotations Table -->
            <div class="table-responsive" v-if="(data.quotations || []).length > 0">
              <table class="table table-hover align-middle border mb-0 custom-profile-table">
                <thead class="table-light">
                  <tr>
                    <th width="4%">#</th>
                    <th>{{ $t('Quotation No') }}</th>
                    <th>{{ $t('Date') }}</th>
                    <th>{{ $t('Validity') }}</th>
                    <th>{{ $t('Subject') }}</th>
                    <th class="text-end">{{ $t('Total Amount') }}</th>
                    <th class="text-center">{{ $t('Status') }}</th>
                    <th class="text-center" width="10%">{{ $t('Action') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(q, qIdx) in data.quotations" :key="'quot_' + q.id">
                    <td class="text-muted font-monospace">{{ qIdx + 1 }}</td>
                    <td class="fw-bold font-monospace theme-text">{{ q.quotation_no }}</td>
                    <td class="font-monospace small">{{ q.quotation_date }}</td>
                    <td class="font-monospace small text-muted">{{ q.validity_date || '-' }}</td>
                    <td>{{ q.subject || '-' }}</td>
                    <td class="text-end font-monospace fw-bold text-dark text-nowrap">
                      ৳&nbsp;{{ formatNum(q.total_amount) }}
                    </td>
                    <td class="text-center">
                      <span class="badge bg-info text-dark">{{ q.status || 'Draft' }}</span>
                    </td>
                    <td class="text-center">
                      <router-link
                        :to="{ name: 'quotation.show', params: { id: q.id } }"
                        class="btn btn-outline-primary btn-xs py-1 px-2 fw-semibold shadow-sm"
                      >
                        <i class="fas fa-eye me-1"></i>{{ $t('View') }}
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5 text-muted" v-else>
              <i class="fas fa-file-contract fa-3x mb-3 text-secondary opacity-50"></i>
              <h6 class="fw-bold">{{ $t('No Quotations Recorded') }}</h6>
              <p class="small text-muted">{{ $t('No price proposals or quotations have been created for this client yet.') }}</p>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- 🖨️ 2. Clean Dedicated Print Layout (Client Full Information & Financial Summary Report) -->
    <div id="client_print_area" class="client-print-statement p-4 bg-white text-dark">
      <!-- Organization Header -->
      <div class="text-center mb-4 border-bottom pb-3">
        <h3 class="fw-bold mb-1 text-dark">{{ $root.site?.title || 'QPOS Retail & Wholesale' }}</h3>
        <p class="small mb-1 text-secondary">
          {{ $root.site?.address || '' }}
          <span v-if="$root.site?.phone"> | Tel: {{ $root.site.phone }}</span>
          <span v-if="$root.site?.email"> | Email: {{ $root.site.email }}</span>
        </p>
        <h5 class="fw-bold text-uppercase mt-2 letter-spacing-1 theme-text">
          {{ $t('Client Profile & Financial Summary Statement') }}
        </h5>
        <div class="small font-monospace text-muted">
          {{ $t('Report Generated:') }} {{ new Date().toLocaleString('en-GB') }}
        </div>
      </div>

      <!-- 1. Client Complete Information Section (Print Table) -->
      <h6 class="fw-bold text-dark border-bottom pb-1 mb-2 d-flex align-items-center gap-1.5">
        <i class="fas fa-id-card text-primary"></i>
        <span>{{ $t('1. Client Personal, Contact & Banking Details') }}</span>
      </h6>
      <div class="row g-3 mb-4">
        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-table">
            <tbody>
              <tr>
                <th width="40%" class="bg-light">{{ $t('Client Name:') }}</th>
                <td class="fw-bold">{{ data.name || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Business / Org:') }}</th>
                <td>{{ data.org_name || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Client ID:') }}</th>
                <td class="font-monospace fw-bold">#{{ data.clientid || data.id }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Mobile Phone:') }}</th>
                <td class="font-monospace fw-bold">{{ data.mobile || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Email Address:') }}</th>
                <td>{{ data.email || 'N/A' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Customer Type:') }}</th>
                <td class="text-capitalize">{{ data.customer_type || 'Retail' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Membership Tier:') }}</th>
                <td>{{ data.membership_type || 'Regular' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Account Status:') }}</th>
                <td>{{ data.status || 'Active' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="col-6">
          <table class="table table-sm table-bordered mb-0 print-table">
            <tbody>
              <tr>
                <th width="40%" class="bg-light">{{ $t('Registration Date:') }}</th>
                <td class="font-monospace">{{ data.reg_date || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Street Address:') }}</th>
                <td>{{ data.address || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('District & Area:') }}</th>
                <td>
                  {{ data.district?.district_name || '-' }}
                  <template v-if="data.area?.area_name"> ({{ data.area.area_name }})</template>
                </td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Bank Name:') }}</th>
                <td>{{ data.bank?.bank_name || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Branch Name:') }}</th>
                <td>{{ data.branch || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Account Name:') }}</th>
                <td>{{ data.account_name || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Account Number:') }}</th>
                <td class="font-monospace fw-bold">{{ data.account_no || '-' }}</td>
              </tr>
              <tr>
                <th class="bg-light">{{ $t('Internal Notes:') }}</th>
                <td class="small">{{ data.note || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. Financial Overview & Summary Balances Section (Print Table) -->
      <h6 class="fw-bold text-dark border-bottom pb-1 mb-2 d-flex align-items-center gap-1.5">
        <i class="fas fa-file-invoice-dollar text-success"></i>
        <span>{{ $t('2. Financial Overview & Summary Balances') }}</span>
      </h6>

      <!-- Summary KPI Highlight Cards in Print -->
      <div class="row g-2 mb-3 text-center">
        <div class="col border p-2 rounded bg-light">
          <small class="text-muted fw-bold d-block">{{ $t('TOTAL PURCHASE') }}</small>
          <strong class="font-monospace fs-6 text-nowrap">৳&nbsp;{{ formatNum(summary.total_purchase) }}</strong>
          <small class="text-muted d-block" style="font-size: 10px;">{{ summary.total_invoices || 0 }} {{ $t('Invoices') }}</small>
        </div>
        <div class="col border p-2 rounded bg-light">
          <small class="text-muted fw-bold d-block">{{ $t('TOTAL PAID') }}</small>
          <strong class="font-monospace fs-6 text-success text-nowrap">৳&nbsp;{{ formatNum(summary.total_paid) }}</strong>
          <small class="text-muted d-block" style="font-size: 10px;">{{ summary.total_payments_count || 0 }} {{ $t('Payments') }}</small>
        </div>
        <div class="col border p-2 rounded bg-light" style="border: 2px solid #dc3545 !important;">
          <small class="text-danger fw-bold d-block">{{ $t('TOTAL DUE BALANCE') }}</small>
          <strong class="font-monospace fs-6 text-danger text-nowrap">৳&nbsp;{{ formatNum(summary.total_due) }}</strong>
          <small class="text-muted d-block" style="font-size: 10px;">{{ $t('Net Outstanding') }}</small>
        </div>
        <div class="col border p-2 rounded bg-light">
          <small class="text-muted fw-bold d-block">{{ $t('LOYALTY POINTS') }}</small>
          <strong class="font-monospace fs-6 text-warning-dark text-nowrap">{{ formatPoints(summary.points_balance) }} Pts</strong>
          <small class="text-muted d-block text-nowrap" style="font-size: 10px;">≈ ৳&nbsp;{{ formatNum(summary.points_cash_value) }}</small>
        </div>
        <div class="col border p-2 rounded bg-light">
          <small class="text-muted fw-bold d-block">{{ $t('SALES RETURNS') }}</small>
          <strong class="font-monospace fs-6 text-nowrap">৳&nbsp;{{ formatNum(summary.total_return_amount) }}</strong>
          <small class="text-muted d-block" style="font-size: 10px;">{{ summary.total_returns || 0 }} {{ $t('Returns') }}</small>
        </div>
      </div>

      <!-- Detailed Financial Breakdown Table in Print -->
      <table class="table table-sm table-bordered align-middle mb-4 print-table">
        <thead class="table-light">
          <tr>
            <th width="4%">#</th>
            <th width="45%">{{ $t('Financial Particulars / Parameter') }}</th>
            <th width="26%" class="text-center">{{ $t('Record Details / Count') }}</th>
            <th width="25%" class="text-end">{{ $t('Balance / Amount') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="text-center font-monospace">1</td>
            <td class="fw-semibold">{{ $t('Total Sales Purchases (সর্বমোট বিক্রয়)') }}</td>
            <td class="text-center small">{{ summary.total_invoices || 0 }} {{ $t('Invoices') }} ({{ summary.paid_invoices_count || 0 }} {{ $t('Paid') }}, {{ summary.due_invoices_count || 0 }} {{ $t('Due') }})</td>
            <td class="text-end font-monospace fw-bold text-dark text-nowrap">৳&nbsp;{{ formatNum(summary.total_purchase) }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">2</td>
            <td class="fw-semibold">{{ $t('Total Amount Paid (পরিশোধিত টাকা)') }}</td>
            <td class="text-center small">{{ summary.total_payments_count || 0 }} {{ $t('Payment Transactions') }}</td>
            <td class="text-end font-monospace fw-bold text-success text-nowrap">৳&nbsp;{{ formatNum(summary.total_paid) }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">3</td>
            <td>{{ $t('Current Invoice Due (ইনভয়েস বকেয়া)') }}</td>
            <td class="text-center small">{{ summary.due_invoices_count || 0 }} {{ $t('Unsettled Invoices') }}</td>
            <td class="text-end font-monospace text-dark text-nowrap">৳&nbsp;{{ formatNum(summary.invoice_due) }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">4</td>
            <td>{{ $t('Previous Due Balance (পূর্বের বকেয়া)') }}</td>
            <td class="text-center small">{{ $t('Opening Balance Carried Forward') }}</td>
            <td class="text-end font-monospace text-dark text-nowrap">৳&nbsp;{{ formatNum(summary.previous_due) }}</td>
          </tr>
          <tr class="table-light fw-bold" style="background-color: #f1f5f9 !important;">
            <td class="text-center font-monospace">5</td>
            <td class="text-danger fw-bold">{{ $t('Total Net Outstanding Due (সর্বমোট বকেয়া)') }}</td>
            <td class="text-center text-danger small fw-bold">{{ $t('Total Payable to Company') }}</td>
            <td class="text-end font-monospace fw-bold fs-6 text-danger text-nowrap">৳&nbsp;{{ formatNum(summary.total_due) }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">6</td>
            <td>{{ $t('Credit Limit / Due Limit (সর্বোচ্চ বকেয়া সীমা)') }}</td>
            <td class="text-center small">{{ $t('Authorized Credit Margin') }}</td>
            <td class="text-end font-monospace fw-bold text-dark text-nowrap">৳&nbsp;{{ formatNum(data.due_limit) }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">7</td>
            <td>{{ $t('Loyalty Points Balance (লয়ালটি পয়েন্ট)') }}</td>
            <td class="text-center small">Earned: {{ formatPoints(summary.total_points_earned) }} | Redeemed: {{ formatPoints(summary.total_points_redeemed) }}</td>
            <td class="text-end font-monospace text-warning-dark fw-bold text-nowrap">
              {{ formatPoints(summary.points_balance) }} Pts (<span class="text-nowrap">≈ ৳&nbsp;{{ formatNum(summary.points_cash_value) }}</span>)
            </td>
          </tr>
          <tr>
            <td class="text-center font-monospace">8</td>
            <td>{{ $t('Total Sales Returns (পণ্য ফেরত মূল্য)') }}</td>
            <td class="text-center small">{{ summary.total_returns || 0 }} {{ $t('Return Cases') }}</td>
            <td class="text-end font-monospace text-dark text-nowrap">৳&nbsp;{{ formatNum(summary.total_return_amount) }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">9</td>
            <td>{{ $t('Warranty Service Tickets (ওয়ারেন্টি ক্লেইম)') }}</td>
            <td class="text-center small">{{ summary.total_warranty_claims || 0 }} {{ $t('Claim Records') }}</td>
            <td class="text-end font-monospace text-muted">{{ summary.total_warranty_claims ? summary.total_warranty_claims + ' Tickets' : 'None' }}</td>
          </tr>
          <tr>
            <td class="text-center font-monospace">10</td>
            <td>{{ $t('Quotations / Proposals Issued (কোটেশন)') }}</td>
            <td class="text-center small">{{ summary.total_quotations || 0 }} {{ $t('Proposals') }}</td>
            <td class="text-end font-monospace text-dark text-nowrap">৳&nbsp;{{ formatNum(summary.total_quotations_amount) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Signatures Footer -->
      <div class="row pt-5 mt-5">
        <div class="col-4 text-center">
          <div class="border-top pt-1 text-muted small fw-semibold">{{ $t('Customer Signature') }}</div>
        </div>
        <div class="col-4 text-center">
          <div class="border-top pt-1 text-muted small fw-semibold">{{ $t('Prepared By') }}</div>
        </div>
        <div class="col-4 text-center">
          <div class="border-top pt-1 text-muted small fw-semibold">{{ $t('Authorized Signature & Seal') }}</div>
        </div>
      </div>
    </div>

  </view-page>
</template>

<script>
const model = "client";

export default {
  name: "ClientProfileView",
  data() {
    return {
      page_title: "Client Profile",
      model: model,
      activeTab: "profile", // Default active tab is Client Information
      invoiceSearch: "",
      invoiceStatusFilter: "all",
      paymentSearch: "",
      data: {
        summary: {},
        invoices: [],
        payments: [],
        sales_returns: [],
        point_transactions: [],
        warranty_claims: [],
        quotations: [],
      },
      fileColumns: [],
    };
  },

  computed: {
    summary() {
      return this.data.summary || {};
    },

    clientInitials() {
      const name = (this.data.name || this.data.org_name || "").trim();
      if (!name) return "CL";
      const parts = name.split(" ").filter(Boolean);
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    },

    filteredInvoices() {
      let list = this.data.invoices || [];
      if (this.invoiceStatusFilter === "paid") {
        list = list.filter((i) => i.is_closed == 1);
      } else if (this.invoiceStatusFilter === "due") {
        list = list.filter((i) => !i.is_closed);
      }

      if (this.invoiceSearch && this.invoiceSearch.trim() !== "") {
        const term = this.invoiceSearch.toLowerCase().trim();
        list = list.filter(
          (i) =>
            (i.invoice_no && i.invoice_no.toLowerCase().includes(term)) ||
            (i.invoice_date && i.invoice_date.toLowerCase().includes(term))
        );
      }
      return list;
    },

    filteredPayments() {
      let list = this.data.payments || [];
      if (this.paymentSearch && this.paymentSearch.trim() !== "") {
        const term = this.paymentSearch.toLowerCase().trim();
        list = list.filter(
          (p) =>
            (p.payslipno && p.payslipno.toLowerCase().includes(term)) ||
            (p.payment_method && p.payment_method.toLowerCase().includes(term)) ||
            (p.trxid && p.trxid.toLowerCase().includes(term)) ||
            (p.payment_date && p.payment_date.toLowerCase().includes(term))
        );
      }
      return list;
    },
  },

  methods: {
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatPoints(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0";
      return Number.isInteger(num) ? num.toString() : num.toFixed(2);
    },

    formatDate(dateStr) {
      if (!dateStr) return "-";
      try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString("en-GB", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        });
      } catch (e) {
        return dateStr;
      }
    },

    copyText(text) {
      if (!text) return;
      navigator.clipboard.writeText(text).then(() => {
        this.$toast(`Copied: ${text}`, "success");
      });
    },

    getWarrantyStatusBadge(status) {
      const s = (status || "").toLowerCase();
      if (s.includes("deliver")) return "bg-success text-white";
      if (s.includes("repair") || s.includes("replace")) return "bg-primary text-white";
      if (s.includes("service") || s.includes("progress")) return "bg-warning text-dark";
      return "bg-secondary text-white";
    },

    printClientProfile() {
      const title = `Client Statement - ${this.data.name || this.data.org_name || this.data.clientid || ""}`;
      this.print("client_print_area", title);
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} Profile Overview`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
/* 🌟 Hero Profile Banner Styling */
.client-hero-banner {
  background: linear-gradient(135deg, rgb(17, 44, 70) 0%, rgb(28, 64, 99) 100%);
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.client-hero-banner::after {
  content: "";
  position: absolute;
  top: -50%;
  right: -20%;
  width: 380px;
  height: 380px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
  pointer-events: none;
}

.hero-avatar {
  width: 76px;
  height: 76px;
  background: #ffffff;
  color: rgb(17, 44, 70);
  border: 3px solid rgba(255, 255, 255, 0.3);
  font-size: 26px;
  flex-shrink: 0;
}

.avatar-initials {
  letter-spacing: 1px;
}

.contact-pill {
  display: inline-flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  padding: 3px 10px;
  border-radius: 20px;
}

.copy-btn {
  font-size: 11px;
  opacity: 0.7;
  transition: opacity 0.2s;
}
.copy-btn:hover {
  opacity: 1;
}

/* 📊 Metric KPI Cards */
.metric-card {
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.metric-card h4 {
  font-size: clamp(1.1rem, 1.25vw, 1.35rem) !important;
  white-space: nowrap !important;
  letter-spacing: -0.3px;
}

.metric-icon-box {
  width: 44px;
  height: 44px;
  flex-shrink: 0;
}

.metric-icon-box i {
  font-size: 1.2rem !important;
}

.text-nowrap {
  white-space: nowrap !important;
}

.border-start-danger {
  border-left: 4px solid #dc3545 !important;
}

.text-warning-dark {
  color: #b45309 !important;
}

/* 📑 Navigation Pills */
.client-nav-pills .nav-link {
  color: #475569;
  font-weight: 600;
  font-size: 13.5px;
  padding: 8px 16px;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
}

.client-nav-pills .nav-link:hover {
  background-color: #f1f5f9;
  color: rgb(17, 44, 70);
}

.client-nav-pills .nav-link.active {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
  box-shadow: 0 2px 6px rgba(17, 44, 70, 0.2);
}

/* 📋 Custom Tables */
.custom-profile-table thead th {
  font-size: 12.5px;
  font-weight: 700;
  text-transform: uppercase;
  color: #475569;
  padding: 10px 12px;
}

.custom-profile-table tbody td {
  padding: 11px 12px;
  font-size: 13.5px;
}

.custom-info-table th {
  background-color: #f8fafc;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
  padding: 10px 14px;
}

.custom-info-table td {
  padding: 10px 14px;
  font-size: 13.5px;
}

/* 🖨️ Dedicated Print Styling */
.client-print-statement {
  display: none;
}

@media print {
  .client-web-view {
    display: none !important;
  }

  .client-print-statement {
    display: block !important;
    width: 100% !important;
    padding: 0 !important;
    color: #000 !important;
  }

  .print-table th,
  .print-table td {
    border: 1px solid #000 !important;
    padding: 5px 8px !important;
    font-size: 11.5px !important;
  }

  .print-table th {
    background-color: #f1f5f9 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>