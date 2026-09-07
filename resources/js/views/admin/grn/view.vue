<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" printArea="grn_print_area">
    <!-- 💻 1. On-Screen Interactive Web Dashboard View -->
    <div class="grn-view-wrapper grn-web-view">
      <!-- 🌟 Top Hero / GRN Header Banner -->
      <div class="card border-0 shadow-sm mb-4 grn-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-clipboard-check fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">Goods Receive Note #{{ data.grn_no || data.id || 'N/A' }}</h4>
                  <span class="badge" :class="data.is_closed ? 'bg-success text-white' : 'bg-warning text-dark'">
                    <i :class="data.is_closed ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                    {{ data.is_closed ? 'Fully Paid / Settled' : 'Payment Due' }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="hasAnySerials">
                    <i class="fas fa-barcode me-1"></i> Serialized Stock
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>Receive Date: <strong class="text-white">{{ data.grn_date || 'N/A' }}</strong></span>
                  <span><i class="fas fa-file-invoice me-1"></i>PO: <strong class="text-white">{{ data.purchase?.invoiceno || 'N/A' }}</strong></span>
                  <span><i class="fas fa-warehouse me-1"></i>Warehouse: <strong class="text-white">{{ data.warehouse?.name || 'N/A' }}</strong></span>
                  <span><i class="fas fa-user-tag me-1"></i>Supplier: <strong class="text-white">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2">
              <router-link :to="{ name: 'grn.index' }" class="btn btn-outline-light btn-sm px-3 fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Back to List
              </router-link>
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="printGrnVoucher">
                <i class="fas fa-print me-1"></i> Print GRN Note
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Cards -->
      <div class="row g-3 mb-4">
        <!-- Total Received Qty -->
        <div class="col-xl-4 col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Total Received Quantity</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace text-nowrap">{{ data.total_qty || 0 }} Units</h4>
              <small class="text-muted" style="font-size: 11px;">
                Across {{ data.grn_details?.length || 0 }} product line(s)
              </small>
            </div>
          </div>
        </div>

        <!-- Total Received Value -->
        <div class="col-xl-4 col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Total Received Value</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-success font-monospace text-nowrap">৳ {{ formatNum(data.total_amount) }}</h4>
              <small class="text-muted" style="font-size: 11px;">
                Inventory valuation addition
              </small>
            </div>
          </div>
        </div>

        <!-- Paid / Due Value -->
        <div class="col-xl-4 col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Paid / Outstanding Due</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-balance-scale"></i>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-wrap text-nowrap">
                <h4 class="fw-bold mb-0 text-primary font-monospace text-nowrap">৳ {{ formatNum(data.paid_amount) }}</h4>
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold font-monospace text-nowrap" style="font-size: 11px; padding: 4px 8px;">
                  Due: ৳ {{ formatNum(dueAmount) }}
                </span>
              </div>
              <small class="text-muted d-block mt-1" style="font-size: 11px;">
                Supplier payable settlement status
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Content: Meta Info (4-col) & Received Line Items Table (8-col) -->
      <div class="row g-3">
        <!-- Details Card (4-col) -->
        <div class="col-xl-4 col-lg-5 col-12">
          <div class="card border-0 shadow-sm info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-info-circle"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">Receiving Metadata</h6>
                <small class="text-muted" style="font-size: 11px;">Warehouse, supplier and challan details</small>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-spec-table">
                  <tbody>
                    <tr>
                      <td class="spec-label"><i class="fas fa-warehouse me-2 text-muted"></i>Destination Warehouse</td>
                      <td class="spec-value fw-bold text-dark">{{ data.warehouse?.name || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-barcode me-2 text-muted"></i>Warehouse Code</td>
                      <td class="spec-value font-monospace">{{ data.warehouse?.code || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-building me-2 text-muted"></i>Supplier / Vendor</td>
                      <td class="spec-value fw-bold text-dark">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-phone me-2 text-muted"></i>Supplier Mobile</td>
                      <td class="spec-value font-monospace">{{ data.supplier?.mobile || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-file-invoice me-2 text-muted"></i>PO Invoice</td>
                      <td class="spec-value font-monospace fw-bold text-dark">{{ data.purchase?.invoiceno || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="far fa-calendar-check me-2 text-muted"></i>PO Date</td>
                      <td class="spec-value font-monospace">{{ data.purchase?.purchase_date || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-truck-loading me-2 text-muted"></i>Supplier Challan No</td>
                      <td class="spec-value font-monospace fw-bold text-dark">{{ data.challan_no || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="far fa-calendar-alt me-2 text-muted"></i>Challan Date</td>
                      <td class="spec-value font-monospace">{{ data.challan_date || 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-user-check me-2 text-muted"></i>Received By</td>
                      <td class="spec-value">{{ data.received_by || 'Store Keeper' }}</td>
                    </tr>
                    <tr>
                      <td class="spec-label"><i class="fas fa-sticky-note me-2 text-muted"></i>Note</td>
                      <td class="spec-value">{{ data.note || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Received Line Items Table (8-col) -->
        <div class="col-xl-8 col-lg-7 col-12">
          <div class="card border-0 shadow-sm table-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">Received Product Lines</h6>
                  <small class="text-muted" style="font-size: 11px;">Quantities received into stock and verified</small>
                </div>
              </div>
              <span class="badge theme-bg text-white font-monospace">
                {{ data.grn_details?.length || 0 }} Items Received
              </span>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-items-table">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center" style="width: 45px;">#</th>
                      <th>Product Details</th>
                      <th style="width: 120px;">Category</th>
                      <th style="width: 110px;" v-if="hasAnyVariants">Variant</th>
                      <th class="text-center" style="width: 80px;">Ordered</th>
                      <th class="text-center" style="width: 110px;">Received Qty</th>
                      <th class="text-end" style="width: 110px;">Unit Cost</th>
                      <th class="text-center" style="width: 110px;" v-if="hasAnySerials">Serial Numbers</th>
                      <th class="text-end" style="width: 140px;">Total Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(gdetail, index) in data.grn_details" :key="index">
                      <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>
                      <td>
                        <div class="fw-bold text-dark">{{ gdetail.item?.title || 'Item #' + gdetail.item_id }}</div>
                        <small class="text-muted font-monospace" style="font-size: 11px;">
                          <i class="fas fa-barcode me-1"></i>{{ gdetail.item?.barcode || '-' }}
                        </small>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border">{{ gdetail.category?.title || gdetail.item?.category?.title || 'General' }}</span>
                      </td>
                      <td v-if="hasAnyVariants">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                          <span class="badge bg-secondary" v-if="gdetail.color?.title">{{ gdetail.color?.title }}</span>
                          <span class="badge bg-info text-dark" v-if="gdetail.size?.title">{{ gdetail.size?.title }}</span>
                          <span class="text-muted small" v-if="!gdetail.color?.title && !gdetail.size?.title">-</span>
                        </div>
                      </td>
                      <td class="text-center font-monospace text-secondary">
                        {{ gdetail.ordered_qty }}
                      </td>
                      <td class="text-center font-monospace fw-bold text-primary">
                        {{ gdetail.received_qty }} <small class="text-muted fw-normal">{{ gdetail.unit?.title || 'Pcs' }}</small>
                      </td>
                      <td class="text-end font-monospace text-muted text-nowrap">
                        ৳ {{ formatNum(gdetail.unit_price) }}
                      </td>

                      <!-- Serial Number Action / Badge -->
                      <td class="text-center" v-if="hasAnySerials">
                        <template v-if="getSerialsList(gdetail.serial_no).length > 0">
                          <button
                            type="button"
                            class="btn btn-xs btn-outline-theme d-inline-flex align-items-center gap-1 shadow-sm font-monospace"
                            @click="openSerialModal(gdetail)"
                            title="Click to view all Serial Numbers"
                          >
                            <i class="fas fa-barcode"></i>
                            <strong>{{ getSerialsList(gdetail.serial_no).length }}</strong> Serials
                          </button>
                        </template>
                        <template v-else>
                          <span class="text-muted small font-monospace">-</span>
                        </template>
                      </td>

                      <td class="text-end font-monospace fw-bold text-theme text-nowrap">
                        ৳ {{ formatNum(gdetail.total_amount) }}
                      </td>
                    </tr>

                    <tr v-if="!data.grn_details || data.grn_details.length === 0">
                      <td :colspan="totalTableCols" class="text-center py-4 text-muted">
                        No goods receive items found.
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-light fw-bold">
                    <tr>
                      <td :colspan="hasAnyVariants ? 5 : 4" class="text-end">Summary Totals:</td>
                      <td class="text-center font-monospace text-primary fs-6">{{ data.total_qty }}</td>
                      <td v-if="!hasAnySerials"></td>
                      <td colspan="2" v-else></td>
                      <td class="text-end font-monospace text-theme fs-6 fw-bold text-nowrap">
                        ৳ {{ formatNum(data.total_amount) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Dedicated Corporate A4 GRN Printable Voucher (Shown exclusively in print) -->
    <div id="grn_print_area" class="grn-print-voucher d-none d-print-block">
      <!-- Voucher Header -->
      <div class="print-header d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
          <h3 class="fw-bold mb-1 text-uppercase text-dark">{{ $root.site?.title || 'QPOS STORE' }}</h3>
          <p class="mb-0 text-muted small">{{ $root.site?.address || '' }}</p>
          <p class="mb-0 text-muted small">
            <span>Phone: {{ $root.site?.mobile1 || '' }}</span>
            <span v-if="$root.site?.contact_email" class="ms-2">| Email: {{ $root.site?.contact_email }}</span>
          </p>
        </div>
        <div class="text-end">
          <div class="voucher-title-badge">
            GOODS RECEIVE NOTE (GRN)
          </div>
          <div class="fw-bold fs-6 font-monospace mt-1 text-dark">#{{ data.grn_no || data.id }}</div>
          <div class="small text-muted font-monospace">Date: <strong>{{ data.grn_date || 'N/A' }}</strong></div>
          <div class="small mt-1">
            Status:
            <span class="badge" :class="data.is_closed ? 'bg-success text-white' : 'bg-warning text-dark'">
              {{ data.is_closed ? 'Fully Paid / Settled' : 'Payment Due' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2-Column Metadata Box -->
      <div class="row g-2 mb-3 print-meta-box">
        <!-- Warehouse / Receiving Details -->
        <div class="col-6">
          <div class="p-2 border rounded bg-light h-100">
            <div class="fw-bold small text-uppercase text-secondary border-bottom pb-1 mb-2">
              <i class="fas fa-warehouse me-1"></i> Destination & Receiver
            </div>
            <table class="table table-sm table-borderless mb-0 small-meta-table">
              <tbody>
                <tr>
                  <td class="text-muted" width="40%">Warehouse:</td>
                  <td class="fw-bold text-dark">{{ data.warehouse?.name || 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Code:</td>
                  <td class="font-monospace text-dark">{{ data.warehouse?.code || '-' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Received By:</td>
                  <td class="text-dark">{{ data.received_by || 'Store Keeper' }}</td>
                </tr>
                <tr v-if="data.note">
                  <td class="text-muted">Note:</td>
                  <td class="text-dark">{{ data.note }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Supplier & PO Details -->
        <div class="col-6">
          <div class="p-2 border rounded bg-light h-100">
            <div class="fw-bold small text-uppercase text-secondary border-bottom pb-1 mb-2">
              <i class="fas fa-truck me-1"></i> Supplier & Purchase Order
            </div>
            <table class="table table-sm table-borderless mb-0 small-meta-table">
              <tbody>
                <tr>
                  <td class="text-muted" width="40%">Supplier / Vendor:</td>
                  <td class="fw-bold text-dark">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Contact Mobile:</td>
                  <td class="font-monospace text-dark">{{ data.supplier?.mobile || '-' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">PO Invoice No:</td>
                  <td class="font-monospace fw-bold text-dark">{{ data.purchase?.invoiceno || '-' }} <span class="fw-normal">({{ data.purchase?.purchase_date || '-' }})</span></td>
                </tr>
                <tr>
                  <td class="text-muted">Supplier Challan:</td>
                  <td class="font-monospace text-dark">{{ data.challan_no || '-' }} <span class="fw-normal">({{ data.challan_date || '-' }})</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Full-Width Received Items Table -->
      <div class="mb-3">
        <table class="table table-bordered align-middle print-items-table mb-0">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width: 35px;">#</th>
              <th>Product Details & Barcode</th>
              <th style="width: 110px;">Category</th>
              <th style="width: 90px;" v-if="hasAnyVariants">Variant</th>
              <th class="text-center" style="width: 75px;">Ordered</th>
              <th class="text-center" style="width: 90px;">Received</th>
              <th class="text-end" style="width: 95px;">Unit Rate</th>
              <th class="text-center" style="width: 110px;" v-if="hasAnySerials">Serial Numbers</th>
              <th class="text-end" style="width: 115px;">Total (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(gdetail, index) in data.grn_details" :key="index">
              <td class="text-center font-monospace">{{ index + 1 }}</td>
              <td>
                <div class="fw-bold text-dark">{{ gdetail.item?.title || 'Item #' + gdetail.item_id }}</div>
                <small class="text-muted font-monospace" v-if="gdetail.item?.barcode">
                  Barcode: {{ gdetail.item?.barcode }}
                </small>
              </td>
              <td>{{ gdetail.category?.title || gdetail.item?.category?.title || 'General' }}</td>
              <td v-if="hasAnyVariants">
                <span v-if="gdetail.color?.title">{{ gdetail.color?.title }}</span>
                <span v-if="gdetail.size?.title"> / {{ gdetail.size?.title }}</span>
                <span v-if="!gdetail.color?.title && !gdetail.size?.title">-</span>
              </td>
              <td class="text-center font-monospace">{{ gdetail.ordered_qty }}</td>
              <td class="text-center font-monospace fw-bold">{{ gdetail.received_qty }} {{ gdetail.unit?.title || 'Pcs' }}</td>
              <td class="text-end font-monospace text-nowrap">৳ {{ formatNum(gdetail.unit_price) }}</td>
              <td class="text-center small font-monospace" v-if="hasAnySerials">
                {{ gdetail.serial_no || '-' }}
              </td>
              <td class="text-end font-monospace fw-bold text-nowrap">৳ {{ formatNum(gdetail.total_amount) }}</td>
            </tr>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td :colspan="hasAnyVariants ? 5 : 4" class="text-end">Summary Totals:</td>
              <td class="text-center font-monospace fs-6">{{ data.total_qty }}</td>
              <td v-if="!hasAnySerials"></td>
              <td colspan="2" v-else></td>
              <td class="text-end font-monospace fs-6 text-nowrap">৳ {{ formatNum(data.total_amount) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Financial Summary & Balance Due Breakdown -->
      <div class="row justify-content-end mb-4">
        <div class="col-5">
          <table class="table table-sm table-bordered print-summary-table mb-0">
            <tbody>
              <tr>
                <td class="text-muted" width="55%">Total Received Amount:</td>
                <td class="text-end font-monospace fw-bold text-nowrap">৳ {{ formatNum(data.total_amount) }}</td>
              </tr>
              <tr>
                <td class="text-muted">Settled / Paid Amount:</td>
                <td class="text-end font-monospace text-success fw-bold text-nowrap">৳ {{ formatNum(data.paid_amount) }}</td>
              </tr>
              <tr class="table-light">
                <td class="fw-bold">Outstanding Due:</td>
                <td class="text-end font-monospace text-danger fw-bold text-nowrap">৳ {{ formatNum(dueAmount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Signatures Block -->
      <div class="print-signatures-block mt-5 pt-3">
        <div class="row text-center">
          <div class="col-3">
            <div class="sig-line">Prepared By</div>
          </div>
          <div class="col-3">
            <div class="sig-line">Store Keeper / Received By</div>
          </div>
          <div class="col-3">
            <div class="sig-line">Quality / Verified By</div>
          </div>
          <div class="col-3">
            <div class="sig-line">Authorized Approval</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔍 Serial Numbers Popup Modal -->
    <div v-if="activeSerialItem" class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.55); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px; overflow: hidden;">
          <div class="modal-header py-3 px-4 theme-bg text-white border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-barcode fs-5"></i>
              <div>
                <h6 class="modal-title fw-bold mb-0 text-white">Received Serial Numbers & IMEI</h6>
                <small class="text-white-50" style="font-size: 11px;">{{ activeSerialItem.item?.title || 'Product Serials' }}</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="activeSerialItem = null"></button>
          </div>
          <div class="modal-body p-4">
            <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded border">
              <span class="small fw-semibold text-dark">
                Total Serials Received: <strong class="theme-text font-monospace fs-6">{{ modalSerialsList.length }}</strong>
              </span>
              <button type="button" class="btn btn-xs btn-outline-secondary" @click="copyAllSerials">
                <i class="fas fa-copy me-1"></i> Copy All
              </button>
            </div>

            <!-- Serials Grid -->
            <div class="p-3 border rounded bg-light" style="max-height: 280px; overflow-y: auto;">
              <div class="row g-2">
                <div class="col-md-6 col-sm-12" v-for="(sn, sIdx) in modalSerialsList" :key="sIdx">
                  <div class="p-2 bg-white rounded border d-flex align-items-center justify-content-between shadow-sm">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                      <span class="badge theme-bg text-white font-monospace" style="font-size: 10px;">#{{ sIdx + 1 }}</span>
                      <span class="font-monospace fw-bold text-dark text-truncate" style="font-size: 12px;">{{ sn }}</span>
                    </div>
                    <button type="button" class="btn btn-xs btn-light border" @click="copySingleSerial(sn)" title="Copy Serial">
                      <i class="far fa-copy text-muted"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-2 px-4 bg-light border-top">
            <button type="button" class="btn btn-secondary btn-sm px-4" @click="activeSerialItem = null">Close</button>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "grn";

export default {
  name: "GrnView",
  data() {
    return {
      page_title: "Goods Receive Note Details",
      model: model,
      data: {},
      activeSerialItem: null,
      fileColumns: [],
    };
  },
  computed: {
    isElectronicsShop() {
      const shopType = this.$root.site_setting?.shop_type || this.$root.site?.shop_type || this.data?.shop_type;
      return shopType === 'electronics';
    },
    hasAnyVariants() {
      return this.data.grn_details?.some(d => d.color_id || d.size_id || d.color?.title || d.size?.title);
    },
    hasAnySerials() {
      return this.data.grn_details?.some(d => Boolean(d.serial_no && String(d.serial_no).trim()));
    },
    totalTableCols() {
      return 6 + (this.hasAnyVariants ? 1 : 0) + (this.hasAnySerials ? 1 : 0);
    },
    dueAmount() {
      const total = parseFloat(this.data?.total_amount) || 0;
      const paid = parseFloat(this.data?.paid_amount) || 0;
      return Math.max(0, total - paid);
    },
    modalSerialsList() {
      if (!this.activeSerialItem) return [];
      return this.getSerialsList(this.activeSerialItem.serial_no);
    }
  },
  methods: {
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    getSerialsList(serialStr) {
      if (!serialStr) return [];
      if (Array.isArray(serialStr)) return serialStr;
      try {
        const parsed = JSON.parse(serialStr);
        if (Array.isArray(parsed)) return parsed;
      } catch (e) {}
      return String(serialStr)
        .split(",")
        .map(s => s.trim())
        .filter(Boolean);
    },
    openSerialModal(item) {
      this.activeSerialItem = item;
    },
    copyAllSerials() {
      if (this.modalSerialsList.length === 0) return;
      const text = this.modalSerialsList.join("\n");
      navigator.clipboard.writeText(text).then(() => {
        this.$toast("All serial numbers copied to clipboard", "success");
      });
    },
    copySingleSerial(sn) {
      navigator.clipboard.writeText(sn).then(() => {
        this.$toast(`Copied: ${sn}`, "success");
      });
    },
    printGrnVoucher() {
      this.print("grn_print_area", "Goods Receive Note - " + (this.data.grn_no || this.data.id));
    }
  },
  created() {
    this.page_title = "Goods Receive Note Details";
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.grn-view-wrapper {
  font-family: inherit;
}

.theme-bg {
  background-color: rgb(17, 44, 70) !important;
}

.theme-text {
  color: rgb(17, 44, 70) !important;
}

.text-theme {
  color: rgb(17, 44, 70) !important;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 70, 0.1) !important;
}

.btn-outline-theme {
  color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
}

.btn-outline-theme:hover {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

/* Hero Banner */
.grn-hero-banner {
  background: linear-gradient(135deg, rgb(17, 44, 70) 0%, #1e3a5f 100%);
  border-radius: 8px;
}

.hero-icon-box {
  width: 54px;
  height: 54px;
  min-width: 54px;
}

/* Stat Cards */
.stat-card {
  border-radius: 8px;
  transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 36px;
  height: 36px;
  font-size: 15px;
}

/* Info & Table Cards */
.info-card,
.table-card {
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.section-icon {
  width: 34px;
  height: 34px;
  min-width: 34px;
  font-size: 15px;
}

/* Spec Table */
.custom-spec-table tr td {
  padding: 11px 16px;
  font-size: 13px;
}

.custom-spec-table .spec-label {
  width: 42%;
  color: #64748b;
  font-weight: 500;
  white-space: nowrap;
}

.custom-spec-table .spec-value {
  width: 58%;
  color: #1e293b;
  word-break: break-word;
}

/* Items Table */
.custom-items-table tr th {
  padding: 10px 12px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
}

.custom-items-table tr td {
  padding: 11px 12px;
  font-size: 13px;
}

.btn-xs {
  padding: 3px 8px !important;
  font-size: 11px !important;
  border-radius: 4px !important;
}

/* 🖨️ Printable Voucher Styling */
.grn-print-voucher {
  font-family: 'Segoe UI', Arial, sans-serif;
  color: #111;
  background: #fff;
}

.voucher-title-badge {
  display: inline-block;
  background-color: #112C47;
  color: #fff;
  font-size: 13px;
  font-weight: bold;
  padding: 4px 12px;
  border-radius: 4px;
  letter-spacing: 0.5px;
}

.small-meta-table td {
  padding: 2px 4px !important;
  font-size: 11px;
}

.print-items-table th {
  background-color: #f1f5f9 !important;
  color: #1e293b !important;
  font-size: 11px;
  font-weight: 600;
  padding: 6px 8px !important;
}

.print-items-table td {
  padding: 6px 8px !important;
  font-size: 11px;
}

.print-summary-table td {
  padding: 4px 8px !important;
  font-size: 11px;
}

.sig-line {
  border-top: 1px dashed #64748b;
  padding-top: 5px;
  font-size: 10px;
  font-weight: bold;
  color: #334155;
}

@media print {
  /* Hide all interactive dashboard elements in browser print */
  .grn-web-view,
  .grn-hero-banner,
  .viewer_action_btn,
  .help_btn,
  .page_header,
  .viewer_devider,
  .action,
  .btn,
  .modal,
  .navbar,
  .sidebar,
  .main-sidebar,
  .main-header,
  header,
  nav {
    display: none !important;
  }

  .grn-print-voucher {
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
    font-size: 11px !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>


