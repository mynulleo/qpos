<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="true"
    :showEditRoute="!isLocked"
    :showDeleteButton="!isLocked"
    :showPrintButton="true"
    printArea="challan_print_area"
  >
    <!-- 💻 1. Interactive On-Screen ERP Dashboard View (Hidden on Print) -->
    <div class="challan-web-view w-100 d-print-none">
      <!-- 🌟 Top Hero / Challan Header Banner -->
      <div class="card border-0 shadow-sm mb-3 challan-hero-banner text-white">
        <div class="card-body p-3.5">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-truck-ramp-box fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ $t('Delivery Challan') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2.5 py-1">
                    {{ data.challan_no ? ('#' + data.challan_no) : ('#CH-' + data.id) }}
                  </span>

                  <!-- Active Status -->
                  <span class="badge" :class="(data.status == 1 || data.status === 'active' || data.status === 'Active') ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="(data.status == 1 || data.status === 'active' || data.status === 'Active') ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ (data.status == 1 || data.status === 'active' || data.status === 'Active') ? $t('Active & Dispatched') : $t('Inactive') }}
                  </span>

                  <!-- Receive Status Badge -->
                  <span v-if="data.receive_status === 'received'" class="badge bg-success text-white border border-white border-opacity-25 px-2.5 py-1 font-monospace">
                    <i class="fas fa-check-double me-1"></i>{{ $t('Fully Received') }}
                  </span>
                  <span v-else-if="data.receive_status === 'partial'" class="badge bg-warning text-dark border border-white border-opacity-25 px-2.5 py-1 font-monospace">
                    <i class="fas fa-hourglass-half me-1"></i>{{ $t('Partially Received') }}
                  </span>
                  <span v-else class="badge bg-white bg-opacity-20 text-white border border-white border-opacity-25 px-2.5 py-1 font-monospace">
                    <i class="fas fa-clock me-1"></i>{{ $t('Pending Acceptance') }}
                  </span>

                  <!-- Invoiced Badge -->
                  <span v-if="isLocked" class="badge bg-info text-dark fw-bold border border-white px-2.5 py-1 font-monospace">
                    <i class="fas fa-file-invoice-dollar me-1"></i>
                    {{ data.invoice ? ('#' + data.invoice.invoice_no) : $t('Invoiced') }}
                  </span>
                </div>

                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="far fa-calendar-alt me-1"></i> {{ $t('Challan Date') }}:
                    <strong class="text-white">{{ data.challan_date || '-' }}</strong>
                  </span>
                  <span v-if="data.client">
                    <i class="fas fa-building me-1"></i> {{ $t('Client') }}:
                    <strong class="text-white">{{ data.client.org_name || data.client.name }}</strong>
                  </span>
                  <span v-if="data.accepted_at">
                    <i class="fas fa-calendar-check me-1"></i> {{ $t('Accepted At') }}:
                    <strong class="text-white">{{ data.accepted_at }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons Deck -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <!-- Button 1: Accept Challan / Receive Goods -->
              <button
                type="button"
                class="btn btn-sm btn-success d-inline-flex align-items-center gap-1.5 px-3 py-2 shadow-sm fw-semibold"
                @click="openAcceptModal"
                v-if="!isLocked"
              >
                <i class="fas fa-clipboard-check"></i>
                <span>{{ data.receive_status === 'partial' ? $t('Update / Receive More') : $t('Accept Challan') }}</span>
              </button>

              <!-- Button 2: Generate Invoice from Accepted Items -->
              <button
                type="button"
                class="btn btn-sm btn-light text-primary d-inline-flex align-items-center gap-1.5 px-3 py-2 shadow-sm fw-bold"
                @click="openInvoiceModal"
                v-if="!isLocked && totalReceiveQty > 0"
              >
                <i class="fas fa-file-invoice-dollar"></i>
                <span>{{ $t('Generate Invoice') }}</span>
              </button>

              <!-- Button 3: View Invoiced Document -->
              <router-link
                v-if="isLocked"
                :to="{ name: 'invoice.show', params: { id: (data.invoice_id || (data.invoice && data.invoice.id)) } }"
                class="btn btn-sm btn-light text-dark fw-bold d-inline-flex align-items-center gap-1.5 px-3 py-2 shadow-sm font-monospace"
              >
                <i class="fas fa-file-invoice text-primary"></i>
                <span>{{ $t('View Invoice') }}</span>
                <i class="fas fa-arrow-right ms-1"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- 🔒 Invoiced & Locked Banner Alert -->
      <div class="alert alert-warning border border-warning shadow-sm rounded-3 mb-3 d-flex flex-wrap align-items-center justify-content-between p-3 gap-2" v-if="isLocked">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-warning bg-opacity-25 text-warning p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="fas fa-lock fs-5"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-0 text-dark">{{ $t('Challan is Locked (Invoiced)') }}</h6>
            <small class="text-muted">
              {{ $t('An invoice has already been generated for this challan. This challan is permanently locked and cannot be edited or deleted.') }}
            </small>
          </div>
        </div>
        <router-link
          :to="{ name: 'invoice.show', params: { id: (data.invoice_id || (data.invoice && data.invoice.id)) } }"
          class="btn btn-sm btn-dark font-monospace d-inline-flex align-items-center gap-1 px-3 py-1.5"
        >
          <i class="fas fa-file-invoice"></i> {{ $t('Open Invoice') }} <i class="fas fa-arrow-right ms-1"></i>
        </router-link>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-3">
        <!-- 1. Total Line Items -->
        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Items') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes-stacked"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-primary font-monospace">
                {{ data.challan_details ? data.challan_details.length : 0 }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Dispatched Product Lines') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Total Dispatched Quantity -->
        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Dispatched Qty') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-dolly"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-dark font-monospace">
                {{ totalOrderQty }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Total units sent from store') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Total Received Quantity -->
        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Accepted / Received') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-circle-check"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-success font-monospace">
                {{ totalReceiveQty }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ $t('Confirmed accepted units') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Pending / Balance Quantity -->
        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Pending Balance') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-clock-rotate-left"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 font-monospace" :class="balanceQty > 0 ? 'text-danger' : 'text-success'">
                {{ balanceQty }}
              </h4>
              <small class="text-muted" style="font-size: 11px;">
                {{ balanceQty > 0 ? $t('Undelivered / In-transit') : $t('Fully received & confirmed') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 2-Column Detail Cards -->
      <div class="row g-3 mb-3">
        <!-- Card 1: Client & Delivery Logistics -->
        <div class="col-md-6 col-12">
          <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom px-3 py-2.5 d-flex align-items-center justify-content-between">
              <span class="fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-location-dot text-danger"></i>
                <span>{{ $t('Client & Consignee Logistics') }}</span>
              </span>
              <span class="badge bg-primary bg-opacity-10 text-primary small font-monospace">{{ $t('CONSIGNEE') }}</span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 meta-table">
                <tbody>
                  <tr>
                    <td class="text-secondary fw-semibold py-1" width="35%">{{ $t('Client / Org') }}:</td>
                    <td class="text-dark fw-bold py-1">
                      {{ data.client ? (data.client.org_name || data.client.name) : $t('Direct Customer') }}
                    </td>
                  </tr>
                  <tr v-if="data.client && data.client.mobile">
                    <td class="text-secondary fw-semibold py-1">{{ $t('Client Mobile') }}:</td>
                    <td class="text-dark font-monospace py-1">
                      <a :href="'tel:' + data.client.mobile" class="text-decoration-none text-dark fw-bold">
                        <i class="fas fa-phone-alt text-success me-1"></i>{{ data.client.mobile }}
                      </a>
                    </td>
                  </tr>
                  <tr v-if="data.client && data.client.email">
                    <td class="text-secondary fw-semibold py-1">{{ $t('Client Email') }}:</td>
                    <td class="text-muted small py-1">{{ data.client.email }}</td>
                  </tr>
                  <tr>
                    <td class="text-secondary fw-semibold py-1">{{ $t('Delivery Location') }}:</td>
                    <td class="text-dark fw-semibold py-1 text-break">
                      <i class="fas fa-map-pin text-danger me-1"></i>
                      {{ data.delivery_location || (data.client ? data.client.address : '-') }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-secondary fw-semibold py-1">{{ $t('Received By') }}:</td>
                    <td class="text-dark py-1">
                      <span class="fw-bold">{{ data.receive_by || '-' }}</span>
                      <span v-if="data.receive_by_designation" class="badge bg-light text-secondary border ms-1">{{ data.receive_by_designation }}</span>
                    </td>
                  </tr>
                  <tr v-if="data.receive_by_mobile">
                    <td class="text-secondary fw-semibold py-1">{{ $t('Receiver Mobile') }}:</td>
                    <td class="font-monospace text-dark py-1">
                      <a :href="'tel:' + data.receive_by_mobile" class="text-decoration-none text-dark fw-bold">
                        <i class="fas fa-phone-alt text-success me-1"></i>{{ data.receive_by_mobile }}
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Card 2: Dispatch & Fulfillment Details -->
        <div class="col-md-6 col-12">
          <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
            <div class="card-header bg-light border-bottom px-3 py-2.5 d-flex align-items-center justify-content-between">
              <span class="fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-truck-fast text-primary"></i>
                <span>{{ $t('Dispatch & Fulfillment Status') }}</span>
              </span>
              <span class="badge bg-secondary bg-opacity-10 text-dark small font-monospace">{{ $t('DISPATCH') }}</span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 meta-table">
                <tbody>
                  <tr>
                    <td class="text-secondary fw-semibold py-1" width="35%">{{ $t('Challan Date') }}:</td>
                    <td class="text-dark font-monospace fw-bold py-1">{{ data.challan_date || '-' }}</td>
                  </tr>
                  <tr>
                    <td class="text-secondary fw-semibold py-1">{{ $t('Delivered By') }}:</td>
                    <td class="text-dark py-1">
                      <i class="fas fa-truck text-primary me-1"></i>
                      <span>{{ data.delivery_by || $t('Company Logistics') }}</span>
                      <span v-if="data.delivery_by_mobile" class="font-monospace text-muted ms-1">({{ data.delivery_by_mobile }})</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-secondary fw-semibold py-1">{{ $t('Receive Status') }}:</td>
                    <td class="py-1">
                      <span v-if="data.receive_status === 'received'" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 font-monospace">
                        <i class="fas fa-check-circle me-1"></i>{{ $t('Fully Received') }}
                      </span>
                      <span v-else-if="data.receive_status === 'partial'" class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 font-monospace">
                        <i class="fas fa-hourglass-half me-1"></i>{{ $t('Partially Received') }}
                      </span>
                      <span v-else class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace">
                        <i class="fas fa-clock me-1"></i>{{ $t('Pending Acceptance') }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-secondary fw-semibold py-1">{{ $t('Invoicing Status') }}:</td>
                    <td class="py-1">
                      <span v-if="isLocked" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace">
                        <i class="fas fa-lock me-1"></i>{{ $t('Invoiced') }} ({{ data.invoice ? ('#' + data.invoice.invoice_no) : '#' + data.invoice_id }})
                      </span>
                      <span v-else class="badge bg-light text-muted border font-monospace">
                        <i class="far fa-circle me-1"></i>{{ $t('Not Invoiced') }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="data.accepted_at">
                    <td class="text-secondary fw-semibold py-1">{{ $t('Acceptance Timestamp') }}:</td>
                    <td class="text-muted small font-monospace py-1">{{ data.accepted_at }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Line Items Card (Web View) -->
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
        <div class="card-header bg-light border-bottom px-3 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="fas fa-boxes-stacked text-primary"></i>
              <span>{{ $t('Challan Line Items') }}</span>
            </span>
            <span class="badge bg-primary bg-opacity-10 text-primary font-monospace">
              {{ data.challan_details ? data.challan_details.length : 0 }} {{ $t('Items Recorded') }}
            </span>
          </div>

          <!-- Quick Action Buttons in line item header -->
          <div class="d-flex align-items-center gap-2" v-if="!isLocked">
            <button
              type="button"
              class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1"
              @click="openAcceptModal"
            >
              <i class="fas fa-clipboard-check"></i> {{ $t('Accept / Receive Goods') }}
            </button>
            <button
              type="button"
              class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1"
              @click="openInvoiceModal"
              v-if="totalReceiveQty > 0"
            >
              <i class="fas fa-file-invoice-dollar"></i> {{ $t('Create Invoice') }}
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="text-uppercase small" style="background-color: #112C47; color: #ffffff;">
              <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 45%;">{{ $t('Item / Description & Specifications') }}</th>
                <th style="width: 15%;" class="text-center">{{ $t('Dispatched Qty') }}</th>
                <th style="width: 15%;" class="text-center">{{ $t('Received Qty') }}</th>
                <th style="width: 20%;">{{ $t('Remarks') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in data.challan_details" :key="index">
                <td class="text-center font-monospace text-muted fw-bold">{{ index + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2 flex-wrap mb-1" v-if="item.category || (item.item && item.item.category)">
                    <span class="badge bg-light text-dark border px-2 py-0.5 small font-monospace">
                      <i class="fas fa-tag me-1 text-primary"></i>
                      {{ (item.category && item.category.title) || (item.item && item.item.category && item.item.category.title) }}
                    </span>
                  </div>
                  <div class="fw-bold text-dark fs-6" v-if="item.item">
                    {{ item.item.title }}
                  </div>
                  <div class="small text-muted" v-if="item.description" style="white-space: pre-line;">
                    {{ item.description }}
                  </div>
                </td>
                <td class="text-center font-monospace fw-bold text-dark fs-6">{{ item.qty }}</td>
                <td class="text-center font-monospace fw-bold text-success fs-6">
                  <span v-if="Number(item.receive_qty || 0) > 0" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 font-monospace">
                    {{ item.receive_qty }}
                  </span>
                  <span v-else class="text-muted small">0.00</span>
                </td>
                <td class="small text-muted">{{ item.remark || '-' }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-light fw-bold">
              <tr>
                <td colspan="2" class="text-end text-uppercase text-secondary py-2">{{ $t('Total Quantities') }}:</td>
                <td class="text-center font-monospace text-primary py-2 fs-6">{{ totalOrderQty }}</td>
                <td class="text-center font-monospace text-success py-2 fs-6">{{ totalReceiveQty }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- 📄 2. Official Printable A4 Delivery Challan Sheet -->
    <div class="challan-print-sheet" id="challan_print_area">
      <!-- 🎨 Executive Top Accent Line -->
      <div class="executive-top-bar"></div>

      <div class="challan-sheet-inner">
        <!-- 🔺 TOP CONTENT -->
        <div class="challan-top-content">
          <!-- 1. Corporate Header Section -->
          <div class="row align-items-center pb-2 mb-2 border-bottom border-2 header-section">
            <div class="col-7">
              <div class="d-flex align-items-center gap-2.5">
                <img v-if="companyLogo" :src="companyLogo" alt="Logo" class="company-logo" />
                <div>
                  <h4 class="fw-bold mb-0 company-title" style="color: #112C47;">{{ $root.site ? $root.site.title : 'QPOS ERP' }}</h4>
                  <p class="text-muted small mb-0 mt-0.5 company-meta" v-if="$root.site && $root.site.address">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $root.site.address }}
                  </p>
                  <p class="text-muted small mb-0 company-meta" v-if="$root.site">
                    <span v-if="$root.site.mobile1"><i class="fas fa-phone-alt me-1 text-success"></i> {{ $root.site.mobile1 }}</span>
                    <span v-if="$root.site.contact_email" class="ms-2"><i class="fas fa-envelope me-1 text-primary"></i> {{ $root.site.contact_email }}</span>
                    <span v-if="$root.site.web" class="ms-2"><i class="fas fa-globe me-1 text-info"></i> {{ $root.site.web }}</span>
                  </p>
                </div>
              </div>
            </div>

            <div class="col-5 text-end">
              <h3 class="fw-bold text-uppercase tracking-wide mb-0 document-title" style="color: #112C47;">{{ $t('Delivery Challan') }}</h3>
              <span class="badge border px-2.5 py-1 font-monospace small" style="background-color: #f1f5f9; color: #112C47; border-color: #cbd5e1 !important; font-size: 10px;">
                {{ $t('OFFICIAL DISPATCH') }}
              </span>
            </div>
          </div>

          <!-- 2. Challan & Client Info Meta Blocks (Compact 2-Column Grid) -->
          <div class="row g-2 mb-2 customer-meta-row">
            <!-- 2.1 Consignee / Client Info -->
            <div class="col-6">
              <div class="p-2 bg-light rounded-2 border h-100 client-info-box" style="border-left: 3px solid #112C47 !important;">
                <div class="text-uppercase fw-bold border-bottom pb-1 mb-1 box-header-title d-flex align-items-center justify-content-between" style="color: #112C47 !important;">
                  <span><i class="fas fa-user-tie me-1"></i> {{ $t('Consignee / Delivery To') }}:</span>
                </div>
                <div class="fw-bold text-dark client-display-name">
                  {{ data.client ? (data.client.org_name || data.client.name) : $t('Direct Customer') }}
                </div>
                <div class="text-muted client-meta-line" v-if="data.delivery_location || (data.client && data.client.address)">
                  <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                  <strong>{{ $t('Destination') }}:</strong> {{ data.delivery_location || data.client.address }}
                </div>
                <div class="text-muted client-meta-line" v-if="data.receive_by">
                  <i class="fas fa-user-check me-1 text-primary"></i>
                  <strong>{{ $t('Attention') }}:</strong> {{ data.receive_by }}
                  <span v-if="data.receive_by_designation">({{ data.receive_by_designation }})</span>
                </div>
                <div class="text-muted client-meta-line" v-if="data.receive_by_mobile">
                  <i class="fas fa-phone-alt me-1 text-success"></i>
                  <span>{{ data.receive_by_mobile }}</span>
                </div>
              </div>
            </div>

            <!-- 2.2 Challan Details Reference -->
            <div class="col-6">
              <div class="p-2 bg-light rounded-2 border h-100 meta-info-box" style="border-left: 3px solid #112C47 !important;">
                <div class="text-uppercase fw-bold border-bottom pb-1 mb-1 box-header-title d-flex align-items-center justify-content-between" style="color: #112C47 !important;">
                  <span><i class="fas fa-info-circle me-1"></i> {{ $t('Challan Details') }}:</span>
                </div>
                <table class="table table-sm table-borderless mb-0 meta-table">
                  <tbody>
                    <tr>
                      <td class="fw-bold text-secondary py-0">{{ $t('Challan No') }}:</td>
                      <td class="text-end font-monospace fw-bold py-0" style="color: #112C47;">
                        {{ data.challan_no ? ('#' + data.challan_no) : ('#CH-' + data.id) }}
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-bold text-secondary py-0">{{ $t('Challan Date') }}:</td>
                      <td class="text-end font-monospace text-dark fw-semibold py-0">{{ data.challan_date || '-' }}</td>
                    </tr>
                    <tr v-if="data.delivery_by">
                      <td class="fw-bold text-secondary py-0">{{ $t('Delivered By') }}:</td>
                      <td class="text-end text-dark py-0">
                        {{ data.delivery_by }}
                        <span v-if="data.delivery_by_mobile" class="small font-monospace">({{ data.delivery_by_mobile }})</span>
                      </td>
                    </tr>
                    <tr v-if="data.receive_status">
                      <td class="fw-bold text-secondary py-0">{{ $t('Status') }}:</td>
                      <td class="text-end text-dark py-0 text-capitalize font-monospace">{{ data.receive_status }}</td>
                    </tr>
                    <tr v-if="data.is_invoiced">
                      <td class="fw-bold text-secondary py-0">{{ $t('Invoice No') }}:</td>
                      <td class="text-end text-primary font-monospace fw-bold py-0">{{ data.invoice ? data.invoice.invoice_no : 'Invoiced' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- 3. Line Items Table -->
          <div class="table-responsive mb-2">
            <table class="table table-sm table-bordered align-middle mb-0 challan-table">
              <thead class="theme-table-header text-center small text-uppercase">
                <tr>
                  <th style="width: 5%;">{{ $t('SL') }}</th>
                  <th style="width: 50%;" class="text-start">{{ $t('Item Description & Specifications') }}</th>
                  <th style="width: 15%;" class="text-center">{{ $t('Order Qty') }}</th>
                  <th style="width: 15%;" class="text-center">{{ $t('Received Qty') }}</th>
                  <th style="width: 15%;">{{ $t('Remarks') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.challan_details" :key="index" class="challan-item-row">
                  <td class="text-center font-monospace text-muted fw-bold">{{ $bnNum(index + 1) }}</td>
                  <td>
                    <div class="fw-bold text-dark item-title">
                      <span class="badge border px-1.5 py-0.5 font-monospace text-dark bg-light me-1" style="font-size: 10px;" v-if="item.category || (item.item && item.item.category)">
                        {{ (item.category && item.category.title) || (item.item && item.item.category && item.item.category.title) }}
                      </span>
                      <span v-if="item.item">{{ item.item.title }}</span>
                    </div>
                    <div class="text-muted item-desc" v-if="item.description" style="white-space: pre-line;">
                      {{ item.description }}
                    </div>
                  </td>
                  <td class="text-center font-monospace fw-bold text-dark">{{ item.qty }}</td>
                  <td class="text-center font-monospace text-success fw-bold">
                    {{ (item.receive_qty == "0.00" || !item.receive_qty) ? '' : item.receive_qty }}
                  </td>
                  <td class="small text-muted">{{ item.remark || '' }}</td>
                </tr>
              </tbody>
              <tfoot class="bg-light fw-bold">
                <tr>
                  <td colspan="2" class="text-end text-uppercase small text-secondary py-1">{{ $t('Total Quantities') }}:</td>
                  <td class="text-center font-monospace text-dark py-1">{{ totalOrderQty }}</td>
                  <td class="text-center font-monospace text-success py-1">{{ totalReceiveQty }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- 🔻 BOTTOM CONTENT (Anchored directly to the page bottom in print) -->
        <div class="challan-bottom-content">
          <!-- 4. Three-Column Signature Blocks -->
          <div class="row signature-container align-items-end">
            <!-- 4.1 Left: Receiver Signature -->
            <div class="col-4">
              <div class="signature-box text-start">
                <div class="signature-line mb-1"></div>
                <div class="fw-bold text-dark sig-title">{{ $t("Receiver's Signature") }}</div>
                <div class="text-muted sig-sub">{{ $t("Name & Stamp with Date") }}</div>
              </div>
            </div>

            <!-- 4.2 Center: Driver / Courier Signature -->
            <div class="col-4">
              <div class="signature-box text-center">
                <div class="signature-line mb-1 mx-auto" style="max-width: 140px;"></div>
                <div class="fw-bold text-dark sig-title">{{ $t("Delivered By") }}</div>
                <div class="text-muted sig-sub">{{ $t("Driver / Personnel Signature") }}</div>
              </div>
            </div>

            <!-- 4.3 Right: Authorized Company Store Officer -->
            <div class="col-4">
              <div class="signature-box text-end">
                <div class="signature-line mb-1 ms-auto" style="max-width: 140px;"></div>
                <div class="fw-bold text-dark sig-title">{{ $t("Authorized Signatory") }}</div>
                <div class="text-muted sig-sub">{{ $root.site ? $root.site.title : 'QPOS ERP' }}</div>
              </div>
            </div>
          </div>

          <!-- 5. Micro Footer Timestamp & System Brand -->
          <div class="mt-3 pt-1 border-top d-flex justify-content-between align-items-center sheet-system-footer">
            <span>{{ $t('Generated on') }}: <strong>{{ printTimestamp }}</strong></span>
            <span>{{ $t('This is an official computer-generated delivery challan') }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 📦 MODAL 1: ACCEPT CHALLAN (Partial / Full Receive Recording) -->
    <div v-if="showAcceptModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.55); z-index: 1060;">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-3">
          <div class="modal-header bg-light border-bottom py-2.5 px-3">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0 fs-6">
              <i class="fas fa-clipboard-check text-success"></i>
              <span>{{ $t('Accept Goods / Receive Confirmation') }}</span>
              <span class="badge bg-secondary bg-opacity-10 text-dark font-monospace small">
                {{ data.challan_no ? ('#' + data.challan_no) : ('#CH-' + data.id) }}
              </span>
            </h5>
            <button type="button" class="btn-close" @click="showAcceptModal = false" :disabled="acceptLoading"></button>
          </div>
          <div class="modal-body p-3">
            <div class="alert alert-info py-2 px-3 small mb-3 rounded-2">
              <i class="fas fa-info-circle me-1"></i>
              {{ $t('You can specify exact quantities received for each line item. Partial item receipts are supported and will update the delivery status.') }}
            </div>

            <!-- Handover inputs -->
            <div class="row g-2 mb-3 bg-light p-2.5 rounded-2 border">
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Receiver Name') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="acceptForm.receive_by" placeholder="Full name of receiver" />
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Designation') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="acceptForm.receive_by_designation" placeholder="e.g. Store Manager" />
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Contact Mobile') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="acceptForm.receive_by_mobile" placeholder="017xxxxxxxx" />
              </div>
            </div>

            <!-- Quick Auto-Fill Buttons -->
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="fw-bold small text-dark">{{ $t('Item Receipts Table') }}</span>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-0.5 small" @click="autoFillFullReceipt">
                  <i class="fas fa-check-double me-1"></i>{{ $t('Receive All in Full') }}
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-0.5 small" @click="autoFillZeroReceipt">
                  <i class="fas fa-rotate-left me-1"></i>{{ $t('Reset to 0') }}
                </button>
              </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="text-uppercase small" style="background-color: #112C47; color: #ffffff;">
                  <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 42%;">{{ $t('Item Description') }}</th>
                    <th style="width: 14%;" class="text-center">{{ $t('Dispatched Qty') }}</th>
                    <th style="width: 18%;" class="text-center">{{ $t('Receive Qty') }}</th>
                    <th style="width: 22%;">{{ $t('Remarks / Condition') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in acceptForm.items" :key="idx">
                    <td class="text-center font-monospace text-muted fw-bold">{{ idx + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark small" v-if="item.title">{{ item.title }}</div>
                      <div class="text-muted small" v-if="item.description">{{ item.description }}</div>
                    </td>
                    <td class="text-center font-monospace fw-bold text-dark">{{ item.order_qty }}</td>
                    <td>
                      <input
                        type="number"
                        step="any"
                        min="0"
                        :max="item.order_qty"
                        class="form-control form-control-sm text-center font-monospace fw-bold"
                        :class="Number(item.receive_qty) >= Number(item.order_qty) ? 'border-success text-success' : (Number(item.receive_qty) > 0 ? 'border-warning text-warning' : '')"
                        v-model.number="item.receive_qty"
                        placeholder="0.00"
                      />
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm" v-model="item.remark" placeholder="Notes / box intact" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-secondary" @click="showAcceptModal = false" :disabled="acceptLoading">
              {{ $t('Cancel') }}
            </button>
            <button type="button" class="btn btn-sm btn-success px-3 fw-bold" @click="saveAcceptance" :disabled="acceptLoading">
              <span v-if="acceptLoading" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="fas fa-check-circle me-1"></i>
              {{ $t('Confirm & Save Receipt') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 💰 MODAL 2: GENERATE INVOICE FROM ACCEPTED ITEMS -->
    <div
      v-if="showInvoiceModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 1060;"
    >
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-2xl border-0 rounded-3 overflow-hidden">
          <!-- Modal Header with Rich Theme Accent -->
          <div class="modal-header py-3 px-4 d-flex justify-content-between align-items-center text-white" style="background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%);">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-white bg-opacity-20 rounded-3 p-2.5 text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                <i class="fas fa-file-invoice-dollar fs-4"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h5 class="modal-title fw-bold fs-6 mb-0 text-white">
                    {{ $t('Generate Invoice from Accepted Goods') }}
                  </h5>
                  <span class="badge bg-success text-white font-monospace border border-white border-opacity-25 px-2 py-0.5" style="font-size: 11px;">
                    <i class="fas fa-boxes-stacked me-1"></i>{{ invoiceForm.items.length }} {{ $t('Accepted Items') }}
                  </span>
                </div>
                <span class="small text-white-50" style="font-size: 12px;">
                  {{ $t('Review billable quantities, select pricing tier (Retail / Wholesale), and confirm sales invoice') }}
                </span>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="showInvoiceModal = false" :disabled="invoiceLoading"></button>
          </div>

          <div class="modal-body p-4 bg-light bg-opacity-50">
            <!-- Alert Banner -->
            <div class="alert alert-info border-info border-opacity-25 bg-info bg-opacity-10 py-2.5 px-3 small mb-3 rounded-3 d-flex align-items-center gap-2">
              <i class="fas fa-shield-halved text-info fs-5"></i>
              <span>
                {{ $t('An official Sales Invoice will be issued for accepted goods. Once created, this challan will be locked against editing.') }}
              </span>
            </div>

            <!-- Top Metadata & Pricing Tier Selection Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
              <div class="card-body p-3">
                <div class="row g-3 align-items-end">
                  <!-- 1. Invoice Date (Fixed asterisk alignment!) -->
                  <div class="col-lg-3 col-md-6 col-12">
                    <date-picker
                      id="inv_date"
                      v-model="invoiceForm.invoice_date"
                      field="invoiceForm.invoice_date"
                      title="Invoice Date"
                      placeholder="Invoice Date"
                      col="12"
                      :req="true"
                    ></date-picker>
                  </div>

                  <!-- 2. Client / Billed To with Customer Type Badge -->
                  <div class="col-lg-3 col-md-6 col-12">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center">
                      <span>{{ $t('Client / Billed To') }}</span>
                      <span
                        class="badge rounded-pill px-2 py-0.5 text-uppercase font-monospace"
                        :class="(data.client && data.client.customer_type === 'wholesale') ? 'bg-warning text-dark' : 'bg-primary text-white'"
                        style="font-size: 10px;"
                      >
                        {{ (data.client && data.client.customer_type) ? (data.client.customer_type + ' client') : 'Retail client' }}
                      </span>
                    </label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted"><i class="fas fa-building-user"></i></span>
                      <input
                        type="text"
                        class="form-control form-control-sm bg-light fw-bold text-dark"
                        :value="data.client ? (data.client.org_name || data.client.name) : $t('Direct Customer')"
                        readonly
                      />
                    </div>
                  </div>

                  <!-- 3. Pricing Tier Selector (Retail vs Wholesale toggle) -->
                  <div class="col-lg-3 col-md-6 col-12">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center">
                      <span>{{ $t('Pricing Mode') }}</span>
                      <small class="text-primary fw-bold text-uppercase">{{ invoiceForm.price_type === 'wholesale' ? $t('Wholesale (পাইকারি)') : $t('Retail (খুচরা)') }}</small>
                    </label>
                    <div class="btn-group w-100 shadow-sm" role="group">
                      <button
                        type="button"
                        class="btn btn-sm fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 py-1.5"
                        :class="invoiceForm.price_type === 'retail' ? 'btn-primary text-white' : 'btn-outline-secondary'"
                        @click="setPriceType('retail')"
                      >
                        <i class="fas fa-tag"></i>
                        <span>{{ $t('Retail') }}</span>
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 py-1.5"
                        :class="invoiceForm.price_type === 'wholesale' ? 'btn-warning text-dark' : 'btn-outline-secondary'"
                        @click="setPriceType('wholesale')"
                      >
                        <i class="fas fa-boxes-packing"></i>
                        <span>{{ $t('Wholesale') }}</span>
                      </button>
                    </div>
                  </div>

                  <!-- 4. Challan Reference -->
                  <div class="col-lg-3 col-md-6 col-12">
                    <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Challan Reference') }}</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted"><i class="fas fa-file-invoice"></i></span>
                      <input
                        type="text"
                        class="form-control form-control-sm bg-light font-monospace fw-bold text-dark"
                        :value="data.challan_no ? ('#' + data.challan_no) : ('#CH-' + data.id)"
                        readonly
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Items Table Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white overflow-hidden">
              <div class="table-responsive mb-0">
                <table class="table table-hover align-middle mb-0">
                  <thead class="text-uppercase small" style="background-color: #112C47; color: #ffffff;">
                    <tr>
                      <th style="width: 4%;" class="text-center">#</th>
                      <th style="width: 40%;" class="ps-3">{{ $t('Product & Specifications') }}</th>
                      <th style="width: 14%;" class="text-center">{{ $t('Invoiced Qty') }}</th>
                      <th style="width: 22%;" class="text-center">{{ $t('Unit Rate (৳)') }}</th>
                      <th style="width: 20%;" class="text-end pe-3">{{ $t('Line Total (৳)') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, idx) in invoiceForm.items" :key="idx" class="border-bottom">
                      <!-- Index -->
                      <td class="text-center font-monospace text-muted fw-bold">{{ idx + 1 }}</td>

                      <!-- Product & Description -->
                      <td class="ps-3">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1" v-if="item.category_title">
                          <span class="badge bg-light text-dark border px-2 py-0.5 small font-monospace">
                            <i class="fas fa-tag me-1 text-primary"></i>{{ item.category_title }}
                          </span>
                        </div>
                        <div class="fw-bold text-dark fs-6">{{ item.title }}</div>
                        <small class="text-muted font-monospace d-block" v-if="item.barcode">
                          <i class="fas fa-barcode me-1"></i>{{ item.barcode }}
                        </small>
                        <div class="small text-muted" v-if="item.description && item.description !== item.title">
                          {{ item.description }}
                        </div>
                      </td>

                      <!-- Invoiced Quantity -->
                      <td class="text-center">
                        <span class="font-monospace fw-bold text-dark fs-6">{{ item.qty }}</span>
                        <span class="badge bg-light text-primary border font-monospace ms-1 small">
                          {{ item.unit_title }}
                        </span>
                      </td>

                      <!-- Unit Rate Input with Retail & Wholesale Quick Buttons -->
                      <td class="text-center px-3">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text bg-light text-muted fw-bold">৳</span>
                          <input
                            type="number"
                            step="any"
                            min="0"
                            class="form-control form-control-sm text-end font-monospace fw-bold"
                            v-model.number="item.rate"
                            placeholder="0.00"
                          />
                        </div>
                        <!-- Helper quick toggle tags under rate input -->
                        <div class="d-flex align-items-center justify-content-center gap-1 mt-1.5" style="font-size: 11px;">
                          <button
                            type="button"
                            class="badge border text-decoration-none py-1 px-1.5"
                            :class="Number(item.rate) === Number(item.retail_price) ? 'bg-primary text-white border-primary shadow-sm' : 'bg-light text-muted border-secondary-subtle'"
                            @click="item.rate = Number(item.retail_price)"
                            title="Click to apply Retail Price"
                          >
                            Retail: ৳{{ Number(item.retail_price || 0).toLocaleString() }}
                          </button>
                          <button
                            type="button"
                            class="badge border text-decoration-none py-1 px-1.5"
                            :class="Number(item.rate) === Number(item.wholesale_price) ? 'bg-warning text-dark border-warning shadow-sm' : 'bg-light text-muted border-secondary-subtle'"
                            @click="item.rate = Number(item.wholesale_price)"
                            title="Click to apply Wholesale Price"
                          >
                            Wholesale: ৳{{ Number(item.wholesale_price || 0).toLocaleString() }}
                          </button>
                        </div>
                      </td>

                      <!-- Line Total -->
                      <td class="text-end font-monospace fw-bold text-primary pe-3 fs-6">
                        ৳ {{ (Number(item.qty || 0) * Number(item.rate || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Financial Calculation Summary Card -->
            <div class="row justify-content-end">
              <div class="col-lg-5 col-md-7 col-12">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                  <div class="card-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-bold small text-dark d-flex align-items-center gap-1.5">
                      <i class="fas fa-calculator text-primary"></i>
                      <span>{{ $t('Financial Summary') }}</span>
                    </span>
                    <span class="badge bg-light text-muted border font-monospace small">BDT (৳)</span>
                  </div>
                  <div class="card-body p-3">
                    <!-- Subtotal -->
                    <div class="d-flex justify-content-between align-items-center mb-2 small pb-1 border-bottom">
                      <span class="text-muted fw-semibold">{{ $t('Subtotal') }}:</span>
                      <span class="font-monospace fw-bold text-dark fs-6">
                        ৳ {{ invoiceSubtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </span>
                    </div>

                    <!-- Discount -->
                    <div class="d-flex justify-content-between align-items-center mb-2 small pb-1 border-bottom">
                      <span class="text-muted fw-semibold">{{ $t('Discount (৳)') }}:</span>
                      <div class="input-group input-group-sm" style="width: 140px;">
                        <span class="input-group-text bg-light text-muted">৳</span>
                        <input
                          type="number"
                          step="any"
                          min="0"
                          class="form-control form-control-sm text-end font-monospace"
                          v-model.number="invoiceForm.discount"
                          placeholder="0.00"
                        />
                      </div>
                    </div>

                    <!-- VAT / Tax -->
                    <div class="d-flex justify-content-between align-items-center mb-2 small pb-1 border-bottom">
                      <span class="text-muted fw-semibold">{{ $t('VAT / Tax (৳)') }}:</span>
                      <div class="input-group input-group-sm" style="width: 140px;">
                        <span class="input-group-text bg-light text-muted">৳</span>
                        <input
                          type="number"
                          step="any"
                          min="0"
                          class="form-control form-control-sm text-end font-monospace"
                          v-model.number="invoiceForm.vat"
                          placeholder="0.00"
                        />
                      </div>
                    </div>

                    <!-- Net Payable Box -->
                    <div class="rounded-3 p-3 mt-3 d-flex justify-content-between align-items-center text-white shadow-sm" style="background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%);">
                      <div>
                        <span class="d-block small text-white-50 text-uppercase fw-semibold" style="font-size: 10px;">{{ $t('Net Amount Due') }}</span>
                        <span class="fw-bold fs-6">{{ $t('Net Payable Amount') }}</span>
                      </div>
                      <div class="text-end">
                        <span class="font-monospace fw-bold fs-5 text-white">
                          ৳ {{ invoiceGrandTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-white py-2.5 px-4 border-top d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-semibold" @click="showInvoiceModal = false" :disabled="invoiceLoading">
              <i class="fas fa-times me-1"></i> {{ $t('Cancel') }}
            </button>
            <button
              type="button"
              class="btn btn-primary btn-sm px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
              @click="generateInvoice"
              :disabled="invoiceLoading || invoiceForm.items.length === 0"
            >
              <span v-if="invoiceLoading" class="spinner-border spinner-border-sm"></span>
              <i v-else class="fas fa-check-circle"></i>
              <span>{{ $t('Create & Issue Invoice') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "challan";

export default {
  data() {
    return {
      model: model,
      page_title: "Delivery Challan Details",
      data: {
        challan_no: "",
        challan_date: "",
        delivery_location: "",
        delivery_by: "",
        delivery_by_mobile: "",
        receive_by: "",
        receive_by_designation: "",
        receive_by_mobile: "",
        receive_status: "pending",
        is_invoiced: 0,
        invoice_id: null,
        status: "active",
        client: null,
        invoice: null,
        challan_details: [],
      },
      // Modals
      showAcceptModal: false,
      acceptLoading: false,
      acceptForm: {
        receive_by: "",
        receive_by_designation: "",
        receive_by_mobile: "",
        items: [],
      },

      showInvoiceModal: false,
      invoiceLoading: false,
      invoiceForm: {
        invoice_date: this.$filter ? this.$filter.today() : "",
        price_type: "retail",
        discount: 0,
        vat: 0,
        items: [],
      },
    };
  },

  computed: {
    isLocked() {
      return Boolean(this.data.is_invoiced || this.data.invoice_id);
    },

    companyLogo() {
      const s = this.$root.site;
      if (!s) return "";
      const candidates = [s.logo_two, s.logo_one, s.original_logo, s.logo_small_two, s.original_logo_small];
      for (const c of candidates) {
        if (c && typeof c === "string" && c.trim() !== "" && !c.includes("no_server_image")) {
          return c;
        }
      }
      if (s.logo && typeof s.logo === "string" && s.logo.startsWith("http")) {
        return s.logo;
      }
      if (s.logo && typeof s.logo === "string" && s.logo.startsWith("{")) {
        try {
          const parsed = JSON.parse(s.logo);
          const relative = parsed["300x90"] || parsed.original || Object.values(parsed)[0];
          if (relative) {
            const baseStorage = this.$root.storage_url ? this.$root.storage_url.replace(/\/+$/, "") : "/storage";
            return `${baseStorage}/${relative.replace(/^\/+/, "")}`;
          }
        } catch (e) {}
      }
      return "";
    },

    totalOrderQty() {
      if (!this.data.challan_details || !Array.isArray(this.data.challan_details)) return 0;
      return this.data.challan_details.reduce((sum, item) => sum + Number(item.qty || 0), 0);
    },

    totalReceiveQty() {
      if (!this.data.challan_details || !Array.isArray(this.data.challan_details)) return 0;
      return this.data.challan_details.reduce((sum, item) => sum + Number(item.receive_qty || 0), 0);
    },

    balanceQty() {
      return Math.max(0, this.totalOrderQty - this.totalReceiveQty);
    },

    printTimestamp() {
      const d = new Date();
      return d.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    invoiceSubtotal() {
      if (!this.invoiceForm.items || !Array.isArray(this.invoiceForm.items)) return 0;
      return this.invoiceForm.items.reduce(
        (sum, item) => sum + Number(item.qty || 0) * Number(item.rate || 0),
        0
      );
    },

    invoiceGrandTotal() {
      const sub = this.invoiceSubtotal;
      const disc = Number(this.invoiceForm.discount || 0);
      const vat = Number(this.invoiceForm.vat || 0);
      return Math.max(0, sub - disc + vat);
    },
  },

  methods: {
    // ---------------- Accept Modal Methods ----------------
    openAcceptModal() {
      this.acceptForm.receive_by = this.data.receive_by || "";
      this.acceptForm.receive_by_designation = this.data.receive_by_designation || "";
      this.acceptForm.receive_by_mobile = this.data.receive_by_mobile || "";

      this.acceptForm.items = (this.data.challan_details || []).map((d) => ({
        id: d.id,
        title: d.item ? d.item.title : "",
        description: d.description || "",
        order_qty: Number(d.qty || 0),
        receive_qty: Number(d.receive_qty || 0) > 0 ? Number(d.receive_qty) : Number(d.qty || 0),
        remark: d.remark || "",
      }));

      this.showAcceptModal = true;
    },

    autoFillFullReceipt() {
      this.acceptForm.items.forEach((item) => {
        item.receive_qty = item.order_qty;
      });
    },

    autoFillZeroReceipt() {
      this.acceptForm.items.forEach((item) => {
        item.receive_qty = 0;
      });
    },

    saveAcceptance() {
      this.acceptLoading = true;
      axios
        .post(`challan/${this.data.id}/accept`, {
          items: this.acceptForm.items.map((i) => ({
            id: i.id,
            receive_qty: Number(i.receive_qty || 0),
            remark: i.remark || "",
          })),
          receive_by: this.acceptForm.receive_by,
          receive_by_designation: this.acceptForm.receive_by_designation,
          receive_by_mobile: this.acceptForm.receive_by_mobile,
        })
        .then((res) => {
          this.acceptLoading = false;
          this.showAcceptModal = false;
          this.$toast(res.data.message || "Challan accepted successfully!", "success");
          if (res.data.challan) {
            this.data = res.data.challan;
          } else {
            this.get_data(`${this.model}/${this.$route.params.id}`);
          }
        })
        .catch((err) => {
          this.acceptLoading = false;
          const msg =
            err.response && err.response.data && err.response.data.message
              ? err.response.data.message
              : "Error recording acceptance.";
          this.$toast(msg, "error");
        });
    },

    // ---------------- Invoice Modal Methods ----------------
    openInvoiceModal() {
      const acceptedItems = (this.data.challan_details || []).filter(
        (d) => Number(d.receive_qty || 0) > 0
      );

      if (acceptedItems.length === 0) {
        this.$toast("Please accept items first before generating an invoice.", "warning");
        return;
      }

      // Determine default pricing mode based on client's customer_type
      const clientCustomerType = (this.data.client?.customer_type || "").toLowerCase();
      const initialPriceType = clientCustomerType === "wholesale" ? "wholesale" : "retail";

      this.invoiceForm.invoice_date = this.$filter ? this.$filter.today() : "";
      this.invoiceForm.price_type = initialPriceType;
      this.invoiceForm.discount = 0;
      this.invoiceForm.vat = 0;

      this.invoiceForm.items = acceptedItems.map((d) => {
        const retailPrice = d.item ? Number(d.item.retail_price || 0) : 0;
        const wholesalePrice = d.item ? Number(d.item.wholesale_price || 0) : 0;
        const defaultRate = initialPriceType === "wholesale"
          ? (wholesalePrice > 0 ? wholesalePrice : retailPrice)
          : (retailPrice > 0 ? retailPrice : wholesalePrice);

        return {
          id: d.id,
          title: d.item ? d.item.title : (d.description || "Product Item"),
          description: d.description || "",
          category_title: (d.category?.title) || (d.item?.category?.title) || "",
          barcode: d.item?.barcode || "",
          unit_title: (d.item?.unit?.title) || "Pcs",
          retail_price: retailPrice,
          wholesale_price: wholesalePrice,
          qty: Number(d.receive_qty || 0),
          rate: defaultRate,
        };
      });

      this.showInvoiceModal = true;
    },

    setPriceType(type) {
      this.invoiceForm.price_type = type;
      if (!this.invoiceForm.items) return;
      this.invoiceForm.items.forEach((item) => {
        if (type === "wholesale") {
          item.rate = item.wholesale_price > 0 ? item.wholesale_price : item.retail_price;
        } else {
          item.rate = item.retail_price > 0 ? item.retail_price : item.wholesale_price;
        }
      });
      this.$toast(
        type === "wholesale"
          ? "Wholesale prices applied (পাইকারি মূল্য নির্ধারণ করা হয়েছে)"
          : "Retail prices applied (খুচরা মূল্য নির্ধারণ করা হয়েছে)",
        "info"
      );
    },

    generateInvoice() {
      if (!this.data.client_id) {
        this.$toast("Challan has no assigned client. Please assign a client first.", "error");
        return;
      }

      this.invoiceLoading = true;
      axios
        .post(`challan/${this.data.id}/create-invoice`, {
          invoice_date: this.invoiceForm.invoice_date,
          price_type: this.invoiceForm.price_type,
          discount: Number(this.invoiceForm.discount || 0),
          vat: Number(this.invoiceForm.vat || 0),
          items: this.invoiceForm.items.map((i) => ({
            id: i.id,
            qty: Number(i.qty || 0),
            rate: Number(i.rate || 0),
          })),
        })
        .then((res) => {
          this.invoiceLoading = false;
          this.showInvoiceModal = false;
          this.$toast(res.data.message || "Invoice generated successfully!", "success");

          if (res.data.challan) {
            this.data = res.data.challan;
          }

          if (res.data.invoice_id) {
            if (this.$swal) {
              this.$swal({
                title: "Invoice Generated!",
                text: `Invoice #${res.data.invoice_no} has been created and this challan is now locked.`,
                icon: "success",
                showCancelButton: true,
                confirmButtonText: "View Invoice",
                cancelButtonText: "Stay on Challan",
                confirmButtonColor: "#112C47",
              }).then((result) => {
                if (result.isConfirmed) {
                  this.$router.push({ name: 'invoice.show', params: { id: res.data.invoice_id } });
                }
              });
            } else {
              this.$router.push({ name: 'invoice.show', params: { id: res.data.invoice_id } });
            }
          }
        })
        .catch((err) => {
          this.invoiceLoading = false;
          const msg =
            err.response && err.response.data && err.response.data.message
              ? err.response.data.message
              : "Error generating invoice.";
          this.$toast(msg, "error");
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
/* ==========================================================================
   💻 1. On-Screen Web View Styling
   ========================================================================== */
.challan-hero-banner {
  background: linear-gradient(135deg, #112c47 0%, #1e4976 100%);
  border-radius: 8px;
}

.hero-icon-box {
  width: 52px;
  height: 52px;
}

.stat-card {
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.06) !important;
}

.stat-icon {
  width: 38px;
  height: 38px;
  font-size: 16px;
}

/* ==========================================================================
   📄 2. Printable A4 Delivery Challan Sheet Styling
   ========================================================================== */
.challan-print-sheet {
  width: 100%;
  max-width: 920px;
  margin: 0 auto;
  border-radius: 8px;
  background-color: #ffffff;
  color: #1e293b;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
  border: 1px solid #e2e8f0;
  overflow: hidden;
  margin-top: 20px;
}

.executive-top-bar {
  height: 4px;
  background: linear-gradient(90deg, #112c47 0%, #1e4976 60%, #0284c7 100%);
  width: 100%;
}

.challan-sheet-inner {
  padding: 24px 28px;
  min-height: 850px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.challan-top-content {
  flex: 1 0 auto;
  width: 100%;
}

.challan-bottom-content {
  flex-shrink: 0;
  margin-top: auto;
  width: 100%;
}

.company-logo {
  max-height: 48px;
  max-width: 130px;
  object-fit: contain;
}

.company-title {
  font-size: 1.18rem;
  line-height: 1.2;
}

.company-meta {
  font-size: 11px;
  line-height: 1.35;
}

.document-title {
  font-size: 1.35rem;
  letter-spacing: 0.05em;
  line-height: 1.1;
}

.client-info-box,
.meta-info-box {
  background-color: #f8fafc;
  border-color: #e2e8f0 !important;
  font-size: 11px;
  line-height: 1.35;
}

.box-header-title {
  font-size: 11px;
  letter-spacing: 0.03em;
}

.client-display-name {
  font-size: 13px;
  margin-bottom: 2px;
}

.client-meta-line {
  font-size: 10.5px;
  margin-top: 1px;
}

.meta-table td {
  padding: 1.5px 0;
  font-size: 11px;
}

.theme-table-header {
  background-color: #112c47 !important;
  color: #ffffff !important;
}

.theme-table-header th {
  padding: 5px 6px;
  font-size: 10.5px;
  letter-spacing: 0.03em;
  border-color: #1e3a5f !important;
}

.challan-table td {
  padding: 5px 6px;
  font-size: 11px;
  border-color: #cbd5e1 !important;
}

.item-title {
  font-size: 11px;
}

.item-desc {
  font-size: 10px;
  line-height: 1.3;
}

.signature-container {
  padding-top: 28px;
  margin-top: 10px;
}

.signature-line {
  border-top: 1.5px dashed #475569;
  width: 100%;
}

.sig-title {
  font-size: 11px;
}

.sig-sub {
  font-size: 9.5px;
}

.sheet-system-footer {
  font-size: 9.5px;
  color: #94a3b8;
}

/* ==========================================================================
   🖨️ 3. Print Media Queries (Strict 1-Page Optimization)
   ========================================================================== */
@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 10mm 8mm 10mm;
  }

  body {
    background: transparent !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .d-print-none,
  .challan-web-view,
  .page_header,
  .viewer_devider,
  header,
  nav,
  aside,
  footer,
  .btn,
  .modal {
    display: none !important;
  }

  .challan-print-sheet {
    box-shadow: none !important;
    border: none !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    page-break-inside: avoid !important;
    page-break-after: avoid !important;
  }

  .challan-sheet-inner {
    padding: 0 !important;
    min-height: auto !important;
  }

  .executive-top-bar {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .theme-table-header {
    background-color: #112c47 !important;
    color: #ffffff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .theme-table-header th {
    color: #ffffff !important;
  }

  .client-info-box,
  .meta-info-box {
    background-color: #f8fafc !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .signature-container {
    padding-top: 24px !important;
    page-break-inside: avoid !important;
  }
}
</style>
