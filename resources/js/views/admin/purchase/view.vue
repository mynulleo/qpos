<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" printArea="purchase_print_area">
    <!-- 💻 1. On-Screen Interactive Web Dashboard View (Full Theme UI & Icons) -->
    <div class="purchase-view-wrapper purchase-web-view d-print-none">
      <!-- 🌟 Top Hero / Purchase Header Banner -->
      <div class="card border-0 shadow-sm mb-4 purchase-hero-banner">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-truck-loading fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">#{{ data.invoiceno || data.id || 'N/A' }}</h4>
                  <span class="badge" :class="data.is_closed ? 'bg-success text-white' : 'bg-danger text-white'">
                    <i :class="data.is_closed ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                    {{ data.is_closed ? $t('Closed / Settled') : $t('Open / Due') }}
                  </span>
                  <span
                    class="badge"
                    :class="data.receive_status === 'Received' ? 'bg-success text-white' : (data.receive_status === 'Partial' ? 'bg-info text-dark' : 'bg-warning text-dark')"
                  >
                    <i :class="data.receive_status === 'Received' ? 'fas fa-check-double me-1' : 'fas fa-boxes me-1'"></i>
                    {{ data.receive_status || 'Pending Delivery' }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="hasAnySerials">
                    <i class="fas fa-microchip me-1"></i> {{ $t('Serialized Stock') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>Date: <strong class="text-white">{{ data.purchase_date || 'N/A' }}</strong></span>
                  <span><i class="fas fa-store me-1"></i>Supplier: <strong class="text-white">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</strong></span>
                  <span><i class="fas fa-boxes me-1"></i>Total Items: <strong class="text-white">{{ data.purchase_details?.length || 0 }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons (Right side: Receive Goods GRN) -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
              <router-link
                v-if="data.receive_status !== 'Received'"
                :to="{ name: 'grn.create', query: { purchase_id: data.id } }"
                class="btn btn-success btn-sm fw-bold px-3 shadow-sm d-flex align-items-center gap-1"
              >
                <i class="fas fa-clipboard-check"></i> {{ $t('Receive Goods (GRN)') }}
              </router-link>
              <span
                v-else
                class="badge bg-success bg-opacity-75 text-white px-3 py-2 font-monospace"
              >
                <i class="fas fa-check-double me-1"></i> {{ $t('All Goods Received') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 Top Metric KPI Summary Cards -->
      <div class="row g-3 mb-4">
        <!-- Sub Total Amount -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Sub Total') }}</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-calculator"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace">৳ {{ formatNum(data.amount || totalLineAmounts) }}</h4>
            </div>
          </div>
        </div>

        <!-- Discount -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Discount') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-percentage"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-warning font-monospace">৳ {{ formatNum(data.discount) }}</h4>
            </div>
          </div>
        </div>

        <!-- Tax / VAT -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('VAT / Tax') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-receipt"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-info font-monospace">৳ {{ formatNum(data.tax) }}</h4>
            </div>
          </div>
        </div>

        <!-- Net Payable Amount -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100" style="border-left: 4px solid rgb(17, 44, 70) !important;">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Net Total Amount') }}</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-coins"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-theme font-monospace">৳ {{ formatNum(data.total_amount) }}</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Content: Supplier Info (4-col) & Purchased Items Table (8-col) -->
      <div class="row g-3">
        <!-- Supplier & Metadata Card (4-col) -->
        <div class="col-xl-4 col-lg-12">
          <div class="card border-0 shadow-sm h-100 info-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-truck"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $t('Supplier & Order Details') }}</h6>
                <small class="text-muted" style="font-size: 11px;">Vendor credentials and purchase metadata</small>
              </div>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover align-middle mb-0 custom-spec-table">
                <tbody>
                  <tr>
                    <td class="spec-label"><i class="fas fa-building me-2 text-muted"></i>Supplier / Vendor</td>
                    <td class="spec-value fw-bold text-dark">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-phone-alt me-2 text-muted"></i>Contact Phone</td>
                    <td class="spec-value font-monospace">{{ data.supplier?.mobile || data.supplier?.phone || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-envelope me-2 text-muted"></i>Email</td>
                    <td class="spec-value font-monospace">{{ data.supplier?.email || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Address</td>
                    <td class="spec-value">{{ data.supplier?.address || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-file-invoice me-2 text-muted"></i>PO / Challan No</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.invoiceno || ('PO-' + data.id) }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-calendar-check me-2 text-muted"></i>Purchase Date</td>
                    <td class="spec-value font-monospace">{{ data.purchase_date || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-toggle-on me-2 text-muted"></i>Payment Status</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.is_closed ? 'bg-success' : 'bg-danger'">
                        {{ data.is_closed ? $t('Closed / Settled') : $t('Open / Due') }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-shipping-fast me-2 text-muted"></i>Delivery Status</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.receive_status === 'Received' ? 'bg-success' : 'bg-info text-dark'">
                        {{ data.receive_status || 'Pending' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-clock me-2 text-muted"></i>Created At</td>
                    <td class="spec-value font-monospace">{{ enFormat(data.created_at) || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Purchased Items Table (8-col) -->
        <div class="col-xl-8 col-lg-12">
          <div class="card border-0 shadow-sm h-100 table-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">{{ $t('Purchased Products & Line Items') }}</h6>
                  <small class="text-muted" style="font-size: 11px;">Breakdown of quantities, unit costs, selling prices and serials</small>
                </div>
              </div>
              <span class="badge theme-bg text-white font-monospace">
                {{ data.purchase_details?.length || 0 }} Line Items
              </span>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-items-table">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center" style="width: 45px;">{{ $t('#') }}</th>
                      <th>{{ $t('Product Details') }}</th>
                      <th>{{ $t('Category') }}</th>
                      <th v-if="hasAnyVariants">{{ $t('Variant') }}</th>
                      <th class="text-center">{{ $t('Qty') }}</th>
                      <th class="text-end">{{ $t('Cost Price') }}</th>
                      <th class="text-end">{{ $t('Selling Price') }}</th>
                      <th class="text-center" v-if="isElectronicsShop || hasAnySerials">{{ $t('Serial Numbers') }}</th>
                      <th class="text-end" style="width: 120px;">{{ $t('Total Amount') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(pdetail, index) in data.purchase_details" :key="index">
                      <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>
                      <td>
                        <div class="fw-bold text-dark">{{ pdetail.item?.title || 'Product #' + pdetail.item_id }}</div>
                        <small class="text-muted font-monospace" v-if="pdetail.item?.barcode" style="font-size: 11px;">
                          {{ pdetail.item?.barcode }}
                        </small>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border">{{ pdetail.category?.title || pdetail.item?.category?.title || 'General' }}</span>
                      </td>
                      <td v-if="hasAnyVariants">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                          <span class="badge bg-secondary" v-if="pdetail.color?.title">{{ pdetail.color?.title }}</span>
                          <span class="badge bg-info text-dark" v-if="pdetail.size?.title">{{ pdetail.size?.title }}</span>
                          <span class="text-muted small" v-if="!pdetail.color?.title && !pdetail.size?.title">-</span>
                        </div>
                      </td>
                      <td class="text-center font-monospace fw-bold">
                        {{ pdetail.qty }} <small class="text-muted fw-normal">{{ pdetail.unit?.title || 'Pcs' }}</small>
                      </td>
                      <td class="text-end font-monospace text-muted">
                        ৳ {{ formatNum(pdetail.price) }}
                      </td>
                      <td class="text-end font-monospace text-success fw-semibold">
                        ৳ {{ formatNum(pdetail.selling_price) }}
                      </td>
                      <!-- Serial Number Action / Badge -->
                      <td class="text-center" v-if="isElectronicsShop || hasAnySerials">
                        <template v-if="getSerialsList(pdetail.serial_no).length > 0">
                          <button
                            type="button"
                            class="btn btn-xs btn-outline-theme d-inline-flex align-items-center gap-1 shadow-sm font-monospace"
                            @click="openSerialModal(pdetail)"
                            title="Click to view all Serial Numbers"
                          >
                            <i class="fas fa-barcode"></i>
                            <strong>{{ getSerialsList(pdetail.serial_no).length }}</strong> Serials
                          </button>
                        </template>
                        <template v-else>
                          <span class="text-muted small">-</span>
                        </template>
                      </td>
                      <td class="text-end font-monospace fw-bold text-theme">
                        ৳ {{ formatNum(pdetail.total_amount) }}
                      </td>
                    </tr>

                    <tr v-if="!data.purchase_details || data.purchase_details.length === 0">
                      <td :colspan="hasAnyVariants ? (isElectronicsShop || hasAnySerials ? 9 : 8) : (isElectronicsShop || hasAnySerials ? 8 : 7)" class="text-center py-4 text-muted">
                        No purchase items recorded in this voucher.
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-light fw-bold">
                    <tr>
                      <td :colspan="hasAnyVariants ? 4 : 3" class="text-end">Summary Totals:</td>
                      <td class="text-center font-monospace">{{ totalQty }}</td>
                      <td colspan="2" v-if="!isElectronicsShop && !hasAnySerials"></td>
                      <td colspan="3" v-else></td>
                      <td class="text-end font-monospace text-theme fs-6">৳ {{ formatNum(data.total_amount) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📦 Linked Goods Receive Notes (GRN) Section (if any GRNs received) -->
      <div class="card border-0 shadow-sm mt-4 info-card" v-if="data.grns && data.grns.length > 0">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Goods Receive Notes (GRN) History') }}</h6>
              <small class="text-muted" style="font-size: 11px;">Shipments and inventory received against this purchase order</small>
            </div>
          </div>
          <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 font-monospace px-2 py-1">
            <i class="fas fa-boxes me-1"></i> {{ data.grns.length }} GRN Shipment(s)
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-items-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width: 50px;">{{ $t('#') }}</th>
                  <th>{{ $t('GRN Number') }}</th>
                  <th>{{ $t('GRN Date') }}</th>
                  <th>{{ $t('Destination Warehouse') }}</th>
                  <th class="text-center">{{ $t('Received Qty') }}</th>
                  <th class="text-end">{{ $t('GRN Valuation') }}</th>
                  <th class="text-center" style="width: 100px;">{{ $t('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(grn, gIdx) in data.grns" :key="gIdx">
                  <td class="text-center font-monospace text-muted">{{ gIdx + 1 }}</td>
                  <td>
                    <router-link :to="{ name: 'grn.show', params: { id: grn.id } }" class="fw-bold font-monospace text-primary text-decoration-none">
                      <i class="fas fa-file-invoice me-1"></i>{{ grn.grn_no }}
                    </router-link>
                  </td>
                  <td class="font-monospace">{{ grn.grn_date || 'N/A' }}</td>
                  <td>
                    <span v-if="grn.warehouse" class="badge bg-secondary bg-opacity-10 text-dark border">
                      <i class="fas fa-warehouse me-1 text-primary"></i>{{ grn.warehouse.name }}
                    </span>
                    <span v-else class="text-muted small">N/A</span>
                  </td>
                  <td class="text-center font-monospace fw-bold text-success">
                    {{ Number(grn.total_qty || 0).toLocaleString() }} Units
                  </td>
                  <td class="text-end font-monospace fw-bold text-dark">
                    ৳ {{ formatNum(grn.total_amount) }}
                  </td>
                  <td class="text-center">
                    <router-link :to="{ name: 'grn.show', params: { id: grn.id } }" class="btn btn-xs btn-outline-primary shadow-none px-2 py-1">
                      <i class="fas fa-eye me-1"></i> View GRN
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 📜 Terms & Conditions Section (ক্রয় আদেশ শর্তাবলী) -->
      <div class="card border-0 shadow-sm mt-4 info-card terms-card" v-if="formattedTerms && formattedTerms.length > 0">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-file-contract"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Terms & Conditions') }}</h6>
              <small class="text-muted" style="font-size: 11px;">Standard commercial terms, delivery guidelines and purchase obligations</small>
            </div>
          </div>
          <span class="badge theme-bg text-white font-monospace px-2 py-1">
            {{ formattedTerms.length }} Condition(s)
          </span>
        </div>
        <div class="card-body p-3 bg-white">
          <div class="d-flex flex-column gap-2">
            <div
              v-for="(term, tIdx) in formattedTerms"
              :key="tIdx"
              class="d-flex align-items-start gap-3 p-2 px-3 rounded bg-light border border-light"
            >
              <span
                class="badge theme-bg text-white rounded-circle d-flex align-items-center justify-content-center fw-bold font-monospace mt-1"
                style="width: 22px; height: 22px; min-width: 22px; font-size: 11px;"
              >
                {{ tIdx + 1 }}
              </span>
              <div class="text-dark fw-medium" style="font-size: 13px; line-height: 1.5;">
                {{ term }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📝 Internal Notes & Memo Section -->
      <div class="card border-0 shadow-sm mt-4 info-card" v-if="data.note">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
          <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
            <i class="fas fa-comment-alt"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-0 text-dark">{{ $t('Purchase Notes & Memo') }}</h6>
            <small class="text-muted" style="font-size: 11px;">Internal remarks or delivery instructions</small>
          </div>
        </div>
        <div class="card-body p-3 bg-white">
          <div class="p-3 rounded bg-light border text-dark" style="white-space: pre-line; font-size: 13px;">
            {{ data.note }}
          </div>
        </div>
      </div>

      <!-- ✍️ Signatures Block (Screen) -->
      <div class="row pt-4 mt-4 g-4 po-signatures-row">
        <div class="col-6">
          <div class="p-3 border rounded bg-light bg-opacity-25 text-center">
            <div style="height: 40px;"></div>
            <div class="border-top pt-2">
              <div class="fw-bold text-dark small">{{ $t('Supplier / Vendor Acceptance') }}</div>
              <small class="text-muted font-monospace" style="font-size: 11px;">Signature & Official Seal</small>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="p-3 border rounded bg-light bg-opacity-25 text-center">
            <div style="height: 40px;"></div>
            <div class="border-top pt-2">
              <div class="fw-bold text-dark small">{{ $t('Authorized Procurement Officer') }}</div>
              <small class="text-muted font-monospace" style="font-size: 11px;">Signature & Approval</small>
            </div>
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
                <h6 class="modal-title fw-bold mb-0 text-white">Serial Numbers & IMEI Tracking</h6>
                <small class="text-white-50" style="font-size: 11px;">{{ activeSerialItem.item?.title || 'Product Serials' }}</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="activeSerialItem = null"></button>
          </div>
          <div class="modal-body p-4">
            <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light rounded border">
              <span class="small fw-semibold text-dark">
                Total Registered Serials: <strong class="theme-text font-monospace fs-6">{{ modalSerialsList.length }}</strong>
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

    <!-- 🖨️ 2. Dedicated Standard Corporate A4 Purchase Order (Clean, Official, Set Below Top Header) -->
    <div id="purchase_print_area" class="po-print-voucher d-none d-print-block">
      <!-- 1. Corporate Header Section -->
      <div class="print-header d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <!-- 1.1 Company Information (Left) -->
        <div class="d-flex align-items-center gap-3">
          <img
            v-if="$root.site && $root.site.logo"
            :src="$root.site.logo"
            alt="Logo"
            class="print-company-logo"
          />
          <div>
            <h3 class="fw-bold mb-0 text-uppercase" style="color: #112C47;">
              {{ $root.site ? $root.site.title : 'QPOS STORE' }}
            </h3>
            <p class="mb-0 text-muted small" v-if="$root.site && $root.site.address">
              {{ $root.site.address }}
            </p>
            <p class="mb-0 text-muted small" v-if="$root.site">
              <span v-if="$root.site.mobile1">Phone: {{ $root.site.mobile1 }}</span>
              <span v-if="$root.site.contact_email" class="ms-2">| Email: {{ $root.site.contact_email }}</span>
              <span v-if="$root.site.web" class="ms-2">| Web: {{ $root.site.web }}</span>
            </p>
          </div>
        </div>

        <!-- 1.2 Document Title & Badge (Right) -->
        <div class="text-end">
          <div class="voucher-title-badge">
            {{ $t('PURCHASE ORDER') }}
          </div>
          <div class="fw-bold fs-6 font-monospace mt-1 text-dark">
            #{{ data.invoiceno || ('PO-' + data.id) }}
          </div>
          <div class="small text-muted font-monospace">
            Date: <strong>{{ data.purchase_date || 'N/A' }}</strong>
          </div>
          <div class="small mt-1">
            Payment:
            <span class="badge" :class="data.is_closed ? 'bg-success text-white' : 'bg-warning text-dark'">
              {{ data.is_closed ? 'Paid / Settled' : 'Payment Due' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Vendor & Order Metadata Box (2-Column Box) -->
      <div class="row g-2 mb-3 print-meta-box">
        <!-- 2.1 Supplier / Vendor Credentials -->
        <div class="col-6">
          <div class="p-2 border rounded bg-light h-100">
            <div class="fw-bold small text-uppercase text-secondary border-bottom pb-1 mb-2">{{ $t('Supplier / Vendor Details') }}</div>
            <table class="table table-sm table-borderless mb-0 small-meta-table">
              <tbody>
                <tr>
                  <td class="text-muted" style="width: 38%;">Supplier / Firm:</td>
                  <td class="fw-bold text-dark">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</td>
                </tr>
                <tr v-if="data.supplier?.name && data.supplier?.org_name && data.supplier?.name !== data.supplier?.org_name">
                  <td class="text-muted">Contact Person:</td>
                  <td class="text-dark">{{ data.supplier.name }}</td>
                </tr>
                <tr v-if="data.supplier?.mobile || data.supplier?.phone">
                  <td class="text-muted">Phone / Mobile:</td>
                  <td class="font-monospace text-dark">{{ data.supplier.mobile || data.supplier.phone }}</td>
                </tr>
                <tr v-if="data.supplier?.email">
                  <td class="text-muted">Email:</td>
                  <td class="font-monospace text-dark">{{ data.supplier.email }}</td>
                </tr>
                <tr v-if="data.supplier?.address">
                  <td class="text-muted">Address:</td>
                  <td class="text-dark">{{ data.supplier.address }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 2.2 Order Information -->
        <div class="col-6">
          <div class="p-2 border rounded bg-light h-100">
            <div class="fw-bold small text-uppercase text-secondary border-bottom pb-1 mb-2">{{ $t('Order Details') }}</div>
            <table class="table table-sm table-borderless mb-0 small-meta-table">
              <tbody>
                <tr>
                  <td class="text-muted" style="width: 40%;">PO Number:</td>
                  <td class="font-monospace fw-bold text-dark">{{ data.invoiceno || ('PO-' + data.id) }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Issue Date:</td>
                  <td class="font-monospace text-dark">{{ data.purchase_date || 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Payment Terms:</td>
                  <td class="text-dark fw-semibold">{{ data.is_closed ? $t('Closed / Settled') : $t('Open / Due') }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Delivery Status:</td>
                  <td class="font-monospace fw-bold text-dark">{{ data.receive_status || 'Pending' }}</td>
                </tr>
                <tr v-if="data.created_at">
                  <td class="text-muted">Generated At:</td>
                  <td class="font-monospace text-muted">{{ enFormat(data.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 3. Purchased Items Table (Standard A4 Table) -->
      <div class="mb-3">
        <table class="table table-bordered align-middle print-items-table mb-0">
          <thead>
            <tr>
              <th class="text-center" style="width: 35px;">{{ $t('#') }}</th>
              <th>{{ $t('Product Details & Barcode') }}</th>
              <th style="width: 100px;">{{ $t('Category') }}</th>
              <th style="width: 90px;" v-if="hasAnyVariants">{{ $t('Variant') }}</th>
              <th class="text-center" style="width: 60px;">{{ $t('Unit') }}</th>
              <th class="text-center" style="width: 60px;">{{ $t('Qty') }}</th>
              <th class="text-end" style="width: 95px;">{{ $t('Unit Cost') }}</th>
              <th class="text-end" style="width: 110px;">{{ $t('Total') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(pdetail, index) in data.purchase_details" :key="index">
              <td class="text-center font-monospace">{{ index + 1 }}</td>
              <td>
                <div class="fw-bold text-dark">{{ pdetail.item?.title || 'Product #' + pdetail.item_id }}</div>
                <small class="text-muted font-monospace" v-if="pdetail.item?.barcode">
                  Barcode: {{ pdetail.item.barcode }}
                </small>
              </td>
              <td>
                {{ pdetail.category?.title || pdetail.item?.category?.title || 'General' }}
              </td>
              <td v-if="hasAnyVariants">
                <span v-if="pdetail.color?.title">{{ pdetail.color?.title }}</span>
                <span v-if="pdetail.size?.title"> / {{ pdetail.size?.title }}</span>
                <span v-if="!pdetail.color?.title && !pdetail.size?.title">-</span>
              </td>
              <td class="text-center font-monospace">
                {{ pdetail.unit?.title || 'Pcs' }}
              </td>
              <td class="text-center font-monospace fw-bold">
                {{ pdetail.qty }}
              </td>
              <td class="text-end font-monospace">
                ৳ {{ formatNum(pdetail.price) }}
              </td>
              <td class="text-end font-monospace fw-bold">
                ৳ {{ formatNum(pdetail.total_amount) }}
              </td>
            </tr>

            <tr v-if="!data.purchase_details || data.purchase_details.length === 0">
              <td :colspan="hasAnyVariants ? 8 : 7" class="text-center py-3 text-muted">
                No items recorded in this purchase order.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 4. Financial Summary & Amount in Words Breakdown -->
      <div class="row g-2 mb-3 print-summary-row">
        <!-- 4.1 In-Words & Notes (Left 7-col) -->
        <div class="col-7">
          <div class="p-2 border rounded bg-light h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="fw-bold small text-uppercase text-secondary mb-1">
                Amount in Words (টাকায় কথায়):
              </div>
              <div class="p-2 bg-white rounded border fw-bold text-dark small font-monospace text-capitalize">
                {{ $filter.numberToEnglishBD(data.total_amount) }}.
              </div>
            </div>

            <div v-if="data.note" class="mt-2">
              <div class="fw-bold small text-secondary">Note / Remarks:</div>
              <div class="p-1 text-dark small" style="white-space: pre-line;">{{ data.note }}</div>
            </div>
          </div>
        </div>

        <!-- 4.2 Totals Table (Right 5-col) -->
        <div class="col-5">
          <table class="table table-bordered table-sm print-summary-table mb-0 bg-white">
            <tbody>
              <tr>
                <td class="text-muted fw-bold">Sub Total (উপ-মোট):</td>
                <td class="text-end font-monospace fw-bold text-dark">
                  ৳ {{ formatNum(data.amount || totalLineAmounts) }}
                </td>
              </tr>
              <tr v-if="Number(data.discount) > 0">
                <td class="text-muted fw-bold">Discount (ছাড়):</td>
                <td class="text-end font-monospace text-danger fw-bold">
                  - ৳ {{ formatNum(data.discount) }}
                </td>
              </tr>
              <tr v-if="Number(data.tax) > 0">
                <td class="text-muted fw-bold">VAT / Tax (ভ্যাট):</td>
                <td class="text-end font-monospace text-dark fw-bold">
                  + ৳ {{ formatNum(data.tax) }}
                </td>
              </tr>
              <tr>
                <td class="text-muted fw-bold">Total Ordered Qty:</td>
                <td class="text-end font-monospace fw-bold text-dark">
                  {{ totalQty }} Units
                </td>
              </tr>
              <tr class="print-grand-total-row">
                <td class="fw-bold text-uppercase text-dark">Grand Total:</td>
                <td class="text-end font-monospace fw-bold fs-6 text-dark">
                  ৳ {{ formatNum(data.total_amount) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. Commercial Terms & Conditions Block (ক্রয় আদেশ শর্তাবলী) -->
      <div class="mb-4 print-terms-box" v-if="formattedTerms && formattedTerms.length > 0">
        <div class="p-2 border rounded bg-light">
          <div class="fw-bold small text-uppercase text-secondary border-bottom pb-1 mb-2">
            Terms & Conditions (ক্রয় আদেশ শর্তাবলী):
          </div>
          <div class="d-flex flex-column gap-1">
            <div
              v-for="(term, tIdx) in formattedTerms"
              :key="tIdx"
              class="d-flex align-items-start gap-2 small text-dark"
            >
              <span class="badge bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold font-monospace" style="width: 16px; height: 16px; min-width: 16px; font-size: 9px; margin-top: 2px;">
                {{ tIdx + 1 }}
              </span>
              <div style="line-height: 1.35; font-size: 10.5px;">
                {{ term }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Physical Dual Signatures Block -->
      <div class="row print-sig-container">
        <div class="col-6">
          <div class="text-start">
            <div class="print-sig-space"></div>
            <div class="print-sig-line"></div>
            <div class="fw-bold text-dark small mt-1">{{ $t('Supplier / Vendor Acceptance') }}</div>
            <div class="text-muted small" style="font-size: 10px;">Signature & Company Seal</div>
            <div class="text-muted small" style="font-size: 10px;">Date: ____________________</div>
          </div>
        </div>
        <div class="col-6 text-end">
          <div class="text-end d-inline-block">
            <div class="print-sig-space"></div>
            <div class="print-sig-line"></div>
            <div class="fw-bold text-dark small mt-1">Authorized Signatory</div>
            <div class="text-dark small fw-semibold" style="font-size: 10px;">For: {{ $root.site ? $root.site.title : 'QPOS ERP' }}</div>
            <div class="text-muted small" style="font-size: 10px;">Official Approval & Stamp</div>
          </div>
        </div>
      </div>

      <!-- 7. Document Footer -->
      <div class="text-center text-muted small mt-4 pt-2 border-top border-1" style="font-size: 9.5px;">
        <em>This Purchase Order is computer generated by {{ $root.site ? $root.site.title : 'QPOS ERP' }}. All supplies are subject to the agreed terms & conditions.</em>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "purchase";

export default {
  name: "PurchaseView",
  data() {
    return {
      page_title: "Purchase Details",
      model: model,
      data: {},
      activeSerialItem: null,
      fileColumns: [],
    };
  },
  computed: {
    canEdit() {
      if (this.data.can_edit !== undefined && this.data.can_edit !== null) {
        return Boolean(this.data.can_edit);
      }
      if (this.data.grns_count > 0 || (this.data.grns && this.data.grns.length > 0)) {
        return false;
      }
      if (this.data.receive_status && this.data.receive_status !== "Pending") {
        return false;
      }
      return true;
    },
    isElectronicsShop() {
      const shopType = (this.site?.shop_type || this.$root.site?.shop_type || this.$root.site_setting?.shop_type || this.data?.shop_type || "").toLowerCase();
      return shopType === "electronics";
    },
    hasAnyVariants() {
      return Boolean(this.data.purchase_details?.some(d => d.color_id || d.size_id || d.color?.title || d.size?.title));
    },
    hasAnySerials() {
      return Boolean(this.data.purchase_details?.some(d => Boolean(d.serial_no && String(d.serial_no).trim())));
    },
    totalQty() {
      if (!this.data.purchase_details) return 0;
      return this.data.purchase_details.reduce((sum, d) => sum + (parseFloat(d.qty) || 0), 0);
    },
    totalLineAmounts() {
      if (!this.data.purchase_details) return 0;
      return this.data.purchase_details.reduce((sum, d) => sum + (parseFloat(d.total_amount) || (parseFloat(d.qty) * parseFloat(d.price)) || 0), 0);
    },
    formattedTerms() {
      if (!this.data.terms_conditions) return [];
      let raw = this.data.terms_conditions;
      if (typeof raw === "string") {
        try {
          raw = JSON.parse(raw);
        } catch (e) {
          return raw
            .split("\n")
            .map(t => t.trim())
            .filter(Boolean);
        }
      }
      if (Array.isArray(raw)) {
        return raw
          .map(item => {
            if (typeof item === "string") return item.trim();
            if (item && typeof item === "object") {
              if (item.checked === false) return null;
              return (item.condition || item.condition_text || item.title || "").trim();
            }
            return "";
          })
          .filter(Boolean);
      }
      return [];
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
    printPurchaseVoucher() {
      this.print("purchase_print_area", "Purchase Order #" + (this.data.invoiceno || this.data.id || ""));
    },
  },
  created() {
    this.page_title = "Purchase Details";
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.purchase-view-wrapper {
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
.purchase-hero-banner {
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
  width: 45%;
  color: #64748b;
  font-weight: 500;
}

.custom-spec-table .spec-value {
  width: 55%;
  color: #1e293b;
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

/* 🖨️ Dedicated Print Styles */
.print-company-logo {
  max-height: 55px;
  max-width: 120px;
  object-fit: contain;
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

.print-grand-total-row {
  background-color: #f1f5f9 !important;
  border-top: 2px solid #112C47 !important;
  border-bottom: 2px solid #112C47 !important;
}

.print-sig-container {
  margin-top: 55px;
  padding-top: 15px;
}

.print-sig-space {
  height: 45px;
}

.print-sig-line {
  width: 220px;
  border-top: 1.5px dashed #475569;
  margin-bottom: 5px;
}

/* 🖨️ Complete A4 Paper Size Print Media Rules */
@media print {
  @page {
    size: A4 portrait;
    margin: 10mm 12mm;
  }

  html, body {
    background: #ffffff !important;
    color: #000000 !important;
    font-size: 11px !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Suppress all screen-only UI headers, action buttons, icons, modals, and sidebar */
  .purchase-web-view,
  .purchase-hero-banner,
  .d-print-none,
  .viewer_action_btn,
  .help_btn,
  .page_header,
  .right_page_header,
  .viewer_devider,
  .action,
  .btn,
  .modal,
  .navbar,
  .sidebar,
  .main-sidebar,
  .main-header,
  .footer,
  .nav-header,
  .help_info_sidebar,
  .help_overlay,
  header,
  nav {
    display: none !important;
  }

  /* Display ONLY the clean corporate printable PO voucher */
  .po-print-voucher,
  #purchase_print_area {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .po-print-voucher * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .print-meta-box,
  .print-summary-row,
  .print-terms-box {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .print-sig-container {
    margin-top: 65px !important;
    padding-top: 15px !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .print-sig-space {
    height: 45px !important;
  }

  .print-items-table tr {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .badge {
    border: 1px solid #cbd5e1 !important;
    color: #000000 !important;
    background: #f8fafc !important;
  }
}
</style>