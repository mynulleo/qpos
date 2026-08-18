<template>
  <div class="container-fluid p-3 serial-report-page">
    <!-- 🌟 Top Header: Title, Global Search, Advance Filter Toggle & Actions -->
    <div class="card border-0 shadow-sm mb-3 d-print-none">
      <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Left: Page Title -->
          <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark text-nowrap d-flex align-items-center gap-2">
              <i class="fas fa-barcode theme-text"></i>
              <span>Serial & Warranty Audit Report (সিরিয়াল ও ওয়ারেন্টি অডিট রিপোর্ট)</span>
            </h5>
            <span class="badge bg-secondary font-monospace">{{ summary.total_serials || 0 }}</span>
          </div>

          <!-- Center: Quick Search Input -->
          <div class="flex-grow-1 mx-md-3" style="max-width: 460px;">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Search Serial/IMEI, Invoice, Customer, Product... (Enter)"
                v-model="search_data.keyword"
                @keyup.enter="search"
              >
              <button type="button" class="btn btn-sm px-3 theme_search_btn" @click="search">
                <i class="fas fa-search me-1"></i> Search
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" v-if="search_data.keyword" @click="search_data.keyword = ''; search()">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Right: Action Buttons -->
          <div class="d-flex align-items-center gap-2">
            <!-- Advance Filter Toggle Button -->
            <button
              type="button"
              class="advance_filter_btn position-relative"
              @click="showAdvanced = !showAdvanced"
              title="Advance Filter"
            >
              <i class="fas fa-sliders-h"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 4px;" v-if="activeFilterCount > 0">
                {{ activeFilterCount }}
              </span>
            </button>

            <!-- Export to Excel -->
            <download-excel
              v-if="records && records.length > 0"
              class="btn btn-sm btn-outline-success cursor-pointer"
              :data="exportData"
              :fields="exportFields"
              name="serial_warranty_audit_report.xls"
              title="Export to Excel"
            >
              <i class="fas fa-file-excel"></i>
            </download-excel>

            <!-- Print Table Button -->
            <button
              type="button"
              class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1"
              @click="triggerPrint"
              title="Print Report"
            >
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>

        <!-- 📂 Collapsible Advance Filter Drawer Panel -->
        <div v-show="showAdvanced" class="mt-2 pt-2 border-top advance-filter-panel transition-all">
          <div class="row g-2 align-items-end">
            <!-- Sales Status Filter -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Sales Status</label>
              <select class="form-select form-select-sm" v-model="search_data.sales_status" @change="search">
                <option value="all">-- All Sales Status --</option>
                <option value="sold">Sold (বিক্রয় হয়েছে)</option>
                <option value="unsold">In Stock / Unsold (স্টকে আছে)</option>
              </select>
            </div>

            <!-- Claim & Lifecycle Status Filter -->
            <div class="col-md-3 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Claim & Expiry Lifecycle</label>
              <select class="form-select form-select-sm" v-model="search_data.claim_status" @change="search">
                <option value="all">-- All Lifecycles --</option>
                <option value="sold_unclaimed_active">Sold & Never Claimed (Active Policy)</option>
                <option value="sold_unclaimed_expired">Sold & Never Claimed (Expired Policy)</option>
                <option value="claimed">Claimed (ক্লেইম করা হয়েছে)</option>
                <option value="unsold">In Stock / Unsold (অবিক্রীত)</option>
              </select>
            </div>

            <!-- Warranty Expiry Status Filter -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Warranty Policy Status</label>
              <select class="form-select form-select-sm" v-model="search_data.warranty_status" @change="search">
                <option value="all">-- All Warranty States --</option>
                <option value="active">Active (মেয়াদ আছে)</option>
                <option value="expired">Expired (মেয়াদ শেষ)</option>
                <option value="no_warranty">No Policy (পলিসি নেই)</option>
              </select>
            </div>

            <!-- Sale From Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Sale Date From</label>
              <input type="date" class="form-control form-control-sm" v-model="search_data.from_date" @change="search">
            </div>

            <!-- Sale To Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Sale Date To</label>
              <input type="date" class="form-control form-control-sm" v-model="search_data.to_date" @change="search">
            </div>

            <!-- Filter Buttons -->
            <div class="col-md-1 col-sm-12 d-flex gap-1 justify-content-end">
              <button type="button" class="btn btn-sm theme_search_btn w-100" @click="search" title="Apply Filter">
                <i class="fas fa-filter"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" @click="resetSearch" title="Reset Filters">
                <i class="fas fa-undo"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📊 KPI Metrics Overview Cards (Interactive & Screen View) -->
    <div class="row g-2 mb-3 d-print-none">
      <!-- Total Serials -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">Total Serials</div>
              <h4 class="mb-0 fw-bold theme-text font-monospace">{{ summary.total_serials || 0 }}</h4>
            </div>
            <div class="kpi-icon-box theme-bg-soft text-dark">
              <i class="fas fa-barcode"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Sold Serials -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">Sold Products</div>
              <h4 class="mb-0 fw-bold text-success font-monospace">{{ summary.total_sold || 0 }}</h4>
            </div>
            <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
              <i class="fas fa-shopping-cart"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- In Stock Unsold -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">In Stock (Unsold)</div>
              <h4 class="mb-0 fw-bold text-secondary font-monospace">{{ summary.total_unsold || 0 }}</h4>
            </div>
            <div class="kpi-icon-box bg-secondary bg-opacity-10 text-secondary">
              <i class="fas fa-boxes"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Sold & Unclaimed (Active Policy) -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-info">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Sold and never claimed with active warranty">Active Unclaimed</div>
              <h4 class="mb-0 fw-bold text-info font-monospace">{{ summary.total_unclaimed_active || 0 }}</h4>
            </div>
            <div class="kpi-icon-box bg-info bg-opacity-10 text-info">
              <i class="fas fa-shield-alt"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Sold & Expired (Never Claimed) -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-warning">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Sold and expired without filing any claims">Expired Unclaimed</div>
              <h4 class="mb-0 fw-bold text-danger font-monospace">{{ summary.total_unclaimed_expired || 0 }}</h4>
            </div>
            <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
              <i class="fas fa-clock"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Claims Filed -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-primary">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">Claims Filed</div>
              <h4 class="mb-0 fw-bold theme-text font-monospace">{{ summary.total_claimed || 0 }}</h4>
            </div>
            <div class="kpi-icon-box theme-bg-soft theme-text">
              <i class="fas fa-tools"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ Main Report Print & Screen Container (#printArea) -->
    <div id="printArea">
      <!-- 🧾 Company & Report Header (Specifically visible in Print) -->
      <div class="text-center mb-3 report-title d-none d-print-block">
        <h3 class="fw-bold mb-1">{{ ($root.site && $root.site.title) ? $root.site.title : 'QPOS' }}</h3>
        <p class="mb-1 text-muted small">{{ ($root.site && $root.site.address) ? $root.site.address : 'Dhaka, Bangladesh' }}</p>
        <p class="text-muted small mb-2" v-if="$root.site && ($root.site.mobile1 || $root.site.contact_email)">
          Phone: {{ $root.site.mobile1 || 'N/A' }} | Email: {{ $root.site.contact_email || 'N/A' }}
        </p>
        <div class="border-top border-bottom py-2 my-2 bg-light">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-barcode me-1"></i>
            Serial Number & Warranty Lifecycle Audit Report (সিরিয়াল ও ওয়ারেন্টি অডিট রিপোর্ট)
          </h5>
          <small class="text-muted">
            Total Records: <strong>{{ meta.total || (records ? records.length : 0) }}</strong> | Generated on: <strong>{{ currentDate }}</strong>
          </small>
        </div>

        <!-- Print KPI Summary Strip -->
        <div class="row g-2 mb-2 text-start">
          <div class="col-4 border p-1 text-center bg-light">
            <small class="d-block text-muted">Total Serials</small>
            <strong class="font-monospace">{{ summary.total_serials || 0 }}</strong>
          </div>
          <div class="col-4 border p-1 text-center bg-light">
            <small class="d-block text-muted">Sold Products</small>
            <strong class="text-success font-monospace">{{ summary.total_sold || 0 }}</strong>
          </div>
          <div class="col-4 border p-1 text-center bg-light">
            <small class="d-block text-muted">In Stock (Unsold)</small>
            <strong class="text-secondary font-monospace">{{ summary.total_unsold || 0 }}</strong>
          </div>
        </div>
      </div>

      <!-- 📋 Main Serial Audit Table -->
      <div class="card border-0 shadow-sm mb-2 table-card">
        <div class="card-body p-0 table-responsive report-table-wrap">
          <table class="table table-hover table-sm align-middle mb-0 serial-table">
            <thead class="sticky-top" style="z-index: 2;">
              <tr>
                <th class="text-center" width="3%">#</th>
                <th width="14%">Serial / IMEI No</th>
                <th width="20%">Product Info</th>
                <th width="18%">Sales & Customer Details</th>
                <th width="15%">Warranty Policy</th>
                <th width="15%" class="text-center">Lifecycle & Claim State</th>
                <th width="15%" class="text-center">Expiry Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, idx) in records"
                :key="row.serial_no + '_' + idx"
                class="serial-table-row"
              >
                <td class="text-center fw-bold text-secondary font-monospace">{{ (meta.from || 1) + idx }}</td>

                <!-- Serial / IMEI Number -->
                <td>
                  <div class="d-flex align-items-center gap-1">
                    <span class="badge theme-bg text-white font-monospace px-2 py-1" style="font-size: 12px;">
                      <i class="fas fa-barcode me-1 d-print-none"></i>
                      <span>{{ row.serial_no }}</span>
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1 d-print-none" @click="copySerial(row.serial_no)" title="Copy Serial">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                  <small class="text-secondary d-block mt-0.5" v-if="row.supplier_name">
                    <span>Sup: {{ row.supplier_name }}</span>
                  </small>
                </td>

                <!-- Product Info -->
                <td>
                  <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" :title="row.item_title">
                    {{ row.item_title }}
                  </div>
                  <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5" style="font-size: 11px;">
                    <span class="text-muted font-monospace" v-if="row.barcode">[{{ row.barcode }}]</span>
                    <span class="text-secondary" v-if="row.category_title">{{ row.category_title }}</span>
                    <span class="badge bg-light text-dark border" v-if="row.color_title || row.size_title">
                      {{ row.color_title }} {{ row.size_title ? '(' + row.size_title + ')' : '' }}
                    </span>
                  </div>
                </td>

                <!-- Sales & Customer Details -->
                <td>
                  <div v-if="row.is_sold">
                    <div class="d-flex align-items-center gap-1">
                      <span class="badge bg-success bg-opacity-10 text-success border border-success font-monospace px-1 py-0.5" style="font-size: 10px;">SOLD</span>
                      <router-link
                        v-if="row.invoice_id"
                        :to="{ name: 'invoice.show', params: { id: row.invoice_id } }"
                        class="fw-bold font-monospace theme-text text-decoration-none d-print-none"
                        style="font-size: 12px;"
                        title="View Invoice"
                      >
                        #{{ row.invoice_no }}
                      </router-link>
                      <span class="fw-bold font-monospace d-none d-print-inline">#{{ row.invoice_no }}</span>
                      <span class="small font-monospace text-muted" v-if="row.sale_date">({{ row.sale_date }})</span>
                    </div>
                    <div class="fw-semibold text-dark text-truncate mt-1" style="max-width: 220px; font-size: 12px;">
                      {{ row.customer_name }}
                    </div>
                    <small class="text-muted font-monospace" v-if="row.customer_mobile">
                      <i class="fas fa-phone-alt me-1 d-print-none" style="font-size: 10px;"></i>{{ row.customer_mobile }}
                    </small>
                  </div>
                  <div v-else>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace px-2 py-1">
                      <i class="fas fa-box-open me-1 d-print-none"></i>In Stock (Unsold)
                    </span>
                  </div>
                </td>

                <!-- Warranty Policy -->
                <td>
                  <div v-if="row.warranty_type && row.warranty_type !== 'none'">
                    <span class="badge" :class="row.warranty_type === 'guarantee' ? 'bg-success' : 'theme-bg text-white'">
                      {{ row.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}
                    </span>
                    <span class="small font-monospace text-dark ms-1 fw-bold">{{ row.warranty_period }}</span>
                    <small class="text-secondary d-block mt-1 font-monospace" v-if="row.warranty_expiry_date" style="font-size: 11px;">
                      <span>Expiry: {{ row.warranty_expiry_date }}</span>
                    </small>
                  </div>
                  <div v-else class="text-muted small">
                    <span>No Policy</span>
                  </div>
                </td>

                <!-- Lifecycle & Claim State -->
                <td class="text-center">
                  <!-- Case 1: Claimed -->
                  <div v-if="row.claim_status === 'claimed'">
                    <span class="badge bg-primary px-2 py-1 rounded-pill">
                      <i class="fas fa-tools me-1 d-print-none"></i>Claimed ({{ row.claims_count }})
                    </span>
                    <div v-if="row.claims && row.claims.length > 0" class="mt-1">
                      <router-link :to="{ name: 'warrantyClaim.show', params: { id: row.claims[0].id } }" class="small font-monospace theme-text fw-bold text-decoration-none d-print-none">
                        {{ row.claims[0].claim_no }}
                      </router-link>
                      <span class="small font-monospace fw-bold d-none d-print-inline">{{ row.claims[0].claim_no }}</span>
                    </div>
                  </div>

                  <!-- Case 2: Sold & Unclaimed (Active Policy) -->
                  <div v-else-if="row.claim_status === 'sold_unclaimed_active'">
                    <span class="badge bg-info text-dark px-2 py-1 rounded-pill">
                      <i class="fas fa-check-circle me-1 d-print-none"></i>Sold (Never Claimed)
                    </span>
                    <small class="text-muted d-block mt-0.5 font-monospace" style="font-size: 10px;">Active Policy</small>
                  </div>

                  <!-- Case 3: Sold & Expired (Never Claimed) -->
                  <div v-else-if="row.claim_status === 'sold_unclaimed_expired'">
                    <span class="badge bg-danger text-white px-2 py-1 rounded-pill">
                      <i class="fas fa-history me-1 d-print-none"></i>Expired (Never Claimed)
                    </span>
                    <small class="text-danger d-block mt-0.5 font-monospace" style="font-size: 10px;">No claims recorded</small>
                  </div>

                  <!-- Case 4: In Stock / Unsold -->
                  <div v-else-if="row.claim_status === 'unsold'">
                    <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                      <i class="fas fa-store me-1 d-print-none"></i>Unsold In Stock
                    </span>
                  </div>

                  <!-- Case 5: Sold without policy -->
                  <div v-else>
                    <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">
                      Sold (No Warranty)
                    </span>
                  </div>
                </td>

                <!-- Expiry Status & Floating Actions -->
                <td class="text-center position-relative">
                  <!-- Warranty Expiry Status Badge -->
                  <div v-if="row.warranty_status === 'active'">
                    <span class="badge bg-success px-2 py-1 font-monospace">
                      Active ({{ row.remaining_days }}d left)
                    </span>
                  </div>
                  <div v-else-if="row.warranty_status === 'expired'">
                    <span class="badge bg-danger px-2 py-1 font-monospace">
                      Expired ({{ row.expired_days }}d ago)
                    </span>
                  </div>
                  <div v-else-if="row.warranty_status === 'unsold'">
                    <span class="badge bg-secondary bg-opacity-25 text-dark px-2 py-1 font-monospace">
                      Stock
                    </span>
                  </div>
                  <div v-else>
                    <span class="badge bg-light text-muted border px-2 py-1 font-monospace">
                      N/A
                    </span>
                  </div>

                  <!-- ⭐️ Floating Row Hover Action Buttons (Hidden in print) -->
                  <div class="hover-floating-actions d-print-none">
                    <div class="btn-group btn-group-sm shadow-sm bg-white border rounded px-1 py-1">
                      <!-- Direct Register Claim Link (if sold) -->
                      <router-link
                        v-if="row.is_sold"
                        :to="{ name: 'warrantyClaim.create', query: { serial_no: row.serial_no } }"
                        class="btn btn-xs btn-outline-warning border-0"
                        title="File New Warranty Claim for this Serial"
                      >
                        <i class="fas fa-plus-circle me-1"></i>Claim
                      </router-link>

                      <!-- View Claim (if claimed) -->
                      <router-link
                        v-if="row.claims && row.claims.length > 0"
                        :to="{ name: 'warrantyClaim.show', params: { id: row.claims[0].id } }"
                        class="btn btn-xs btn-outline-primary border-0"
                        title="View Claim Ticket"
                      >
                        <i class="fas fa-shield-alt"></i>
                      </router-link>

                      <!-- View Invoice (if sold) -->
                      <router-link
                        v-if="row.invoice_id"
                        :to="{ name: 'invoice.show', params: { id: row.invoice_id } }"
                        class="btn btn-xs btn-outline-dark border-0"
                        title="View Invoice"
                      >
                        <i class="fas fa-file-invoice"></i>
                      </router-link>
                    </div>
                  </div>
                </td>
              </tr>

              <!-- Empty Records State -->
              <tr v-if="!loading && (!records || records.length === 0)">
                <td colspan="7" class="text-center py-5 text-secondary">
                  <i class="fas fa-barcode fa-3x theme-text opacity-50 mb-3 d-block"></i>
                  <h6 class="fw-bold text-dark">No Serial Numbers Found</h6>
                  <p class="small text-muted mb-0">Try changing your search keywords or filter criteria.</p>
                </td>
              </tr>

              <!-- Loading Spinner State -->
              <tr v-if="loading">
                <td colspan="7" class="text-center py-5">
                  <div class="spinner-border theme-text" role="status"></div>
                  <div class="mt-2 small text-secondary fw-semibold">Auditing serial numbers and warranty statuses...</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 📌 Footer Note (Visible in Print) -->
      <div class="mt-3 pt-2 border-top small text-muted d-none d-print-block">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-0">Generated by: <strong>{{ ($root.auth && $root.auth.name) ? $root.auth.name : 'Admin' }}</strong></p>
            <p class="mb-0">This is a system generated audit report from QPOS.</p>
          </div>
          <div class="text-end font-monospace">
            <p class="mb-0">Page 1 of 1</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ⭐️ Bottom Footer: Summary & Standalone Pagination (Screen View) -->
    <div class="card border-0 shadow-sm d-print-none">
      <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <!-- Summary count on bottom left -->
          <div class="d-flex flex-wrap align-items-center gap-3" style="font-size: 12px;">
            <div class="d-flex align-items-center gap-1">
              <span class="text-muted fw-bold">Filtered Serials:</span>
              <span class="badge theme-bg text-white font-monospace">{{ meta.total || 0 }}</span>
            </div>
            <div class="small text-secondary font-monospace d-none d-md-inline" v-if="meta.total > 0">
              Showing {{ meta.from || 1 }} to {{ meta.to || records.length }} of {{ meta.total }}
            </div>
          </div>

          <!-- Pagination & Per Page Selector on bottom right -->
          <div class="d-flex align-items-center gap-2" v-if="meta && meta.total > 0">
            <div class="d-flex align-items-center gap-1">
              <button
                type="button"
                class="btn btn-xs btn-outline-secondary py-1 px-2"
                :disabled="meta.current_page <= 1"
                @click="changePage(meta.current_page - 1)"
                title="Previous Page"
              >
                <i class="fas fa-chevron-left"></i>
              </button>

              <span class="small text-dark font-monospace px-1">
                {{ meta.current_page || 1 }} / {{ meta.last_page || 1 }}
              </span>

              <button
                type="button"
                class="btn btn-xs btn-outline-secondary py-1 px-2"
                :disabled="meta.current_page >= meta.last_page"
                @click="changePage(meta.current_page + 1)"
                title="Next Page"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>

            <!-- Per-Page Selector -->
            <select
              class="form-select form-select-sm py-0 font-monospace"
              style="width: 75px; height: 28px; font-size: 11px;"
              v-model.number="search_data.pagination"
              @change="search"
            >
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
              <option :value="200">200</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SerialWarrantyReport',
  data() {
    return {
      model: 'Serial & Warranty Audit Report',
      loading: false,
      showAdvanced: false,
      search_data: {
        keyword: '',
        pagination: 20,
        page: 1,
        sales_status: 'all',
        claim_status: 'all',
        warranty_status: 'all',
        from_date: '',
        to_date: '',
      },
      records: [],
      summary: {
        total_serials: 0,
        total_sold: 0,
        total_unsold: 0,
        total_claimed: 0,
        total_unclaimed_active: 0,
        total_unclaimed_expired: 0,
      },
      meta: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
      },
      exportFields: {
        'Serial / IMEI': 'serial_no',
        'Product Title': 'item_title',
        'Barcode': 'barcode',
        'Category': 'category_title',
        'Color': 'color_title',
        'Size': 'size_title',
        'Sales Status': 'sales_status_text',
        'Invoice No': 'invoice_no',
        'Sale Date': 'sale_date',
        'Customer Name': 'customer_name',
        'Customer Mobile': 'customer_mobile',
        'Coverage Type': 'warranty_type',
        'Warranty Period': 'warranty_period',
        'Expiry Date': 'warranty_expiry_date',
        'Warranty Status': 'warranty_status_text',
        'Lifecycle State': 'claim_status_text',
      },
    };
  },
  computed: {
    currentDate() {
      return new Date().toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
      });
    },
    activeFilterCount() {
      let count = 0;
      if (this.search_data.sales_status !== 'all') count++;
      if (this.search_data.claim_status !== 'all') count++;
      if (this.search_data.warranty_status !== 'all') count++;
      if (this.search_data.from_date) count++;
      if (this.search_data.to_date) count++;
      return count;
    },
    exportData() {
      return (this.records || []).map(r => ({
        serial_no: r.serial_no,
        item_title: r.item_title,
        barcode: r.barcode,
        category_title: r.category_title,
        color_title: r.color_title || '',
        size_title: r.size_title || '',
        sales_status_text: r.is_sold ? 'Sold' : 'In Stock (Unsold)',
        invoice_no: r.invoice_no,
        sale_date: r.sale_date || '',
        customer_name: r.customer_name,
        customer_mobile: r.customer_mobile,
        warranty_type: r.warranty_type,
        warranty_period: r.warranty_period,
        warranty_expiry_date: r.warranty_expiry_date || '',
        warranty_status_text: r.warranty_status === 'active' ? `Active (${r.remaining_days}d left)` : (r.warranty_status === 'expired' ? `Expired (${r.expired_days}d ago)` : r.warranty_status),
        claim_status_text: r.claim_status === 'claimed' ? `Claimed (${r.claims_count})` : (r.claim_status === 'sold_unclaimed_active' ? 'Sold & Unclaimed (Active)' : (r.claim_status === 'sold_unclaimed_expired' ? 'Sold & Unclaimed (Expired)' : r.claim_status)),
      }));
    },
  },
  methods: {
    fetchReport(page = 1) {
      this.loading = true;
      this.search_data.page = page;

      axios.get('report/serial', { params: this.search_data })
        .then(res => {
          if (res.data) {
            this.records = res.data.records || [];
            this.summary = res.data.summary || this.summary;
            this.meta = res.data.meta || {
              current_page: page,
              last_page: 1,
              from: 1,
              to: (this.records || []).length,
              total: (this.records || []).length,
            };
          }
        })
        .catch(err => {
          this.$toast('Failed to load serial report data', 'error');
          console.error(err);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    search() {
      this.fetchReport(1);
    },
    resetSearch() {
      this.search_data = {
        keyword: '',
        pagination: 20,
        page: 1,
        sales_status: 'all',
        claim_status: 'all',
        warranty_status: 'all',
        from_date: '',
        to_date: '',
      };
      this.fetchReport(1);
    },
    changePage(page) {
      if (page >= 1 && page <= (this.meta.last_page || 1)) {
        this.fetchReport(page);
      }
    },
    copySerial(serial) {
      if (!serial) return;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(serial)
          .then(() => this.$toast(`Serial "${serial}" copied!`, 'success'))
          .catch(() => this.$toast('Failed to copy', 'error'));
      }
    },
    triggerPrint() {
      if (typeof this.print === 'function') {
        this.print('printArea', 'Serial & Warranty Audit Report');
      } else {
        window.print();
      }
    },
  },
  created() {
    this.fetchReport(1);
  },
};
</script>

<style scoped>
.serial-report-page {
  font-family: inherit;
}

.theme-bg {
  background-color: rgb(17, 44, 70) !important;
}

.theme-text {
  color: rgb(17, 44, 70) !important;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 70, 0.1) !important;
}

.theme_search_btn {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
  border: 1px solid rgb(17, 44, 70) !important;
  font-weight: 600;
  transition: all 0.2s ease-in-out;
}

.theme_search_btn:hover {
  background-color: #1a3d61 !important;
  color: #ffffff !important;
}

.advance_filter_btn {
  background-color: #f1f5f9;
  color: rgb(17, 44, 70);
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  width: 31px;
  height: 31px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.advance_filter_btn:hover {
  background-color: rgb(17, 44, 70);
  color: #ffffff;
  border-color: rgb(17, 44, 70);
}

.kpi-card {
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}

.kpi-icon-box {
  width: 38px;
  height: 38px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.table-card {
  border-radius: 8px;
  overflow: hidden;
}

.report-table-wrap {
  min-height: 420px;
  max-height: calc(100vh - 290px);
  overflow-y: auto;
}

.serial-table thead {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.serial-table thead th {
  color: #ffffff !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  padding: 8px 12px !important;
  border: none !important;
  background-color: rgb(17, 44, 70) !important;
}

.serial-table tbody td {
  padding: 8px 12px !important;
  font-size: 13px !important;
}

/* ⭐️ Floating Row Hover Action Buttons */
.serial-table-row {
  position: relative !important;
  transition: background-color 0.15s ease-in-out;
}

.serial-table-row:hover {
  background-color: #eef6ff !important;
}

.serial-table-row:hover td {
  background-color: #eef6ff !important;
}

.hover-floating-actions {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  z-index: 1000;
  white-space: nowrap;
  transition: opacity 0.15s ease-in-out, visibility 0.15s ease-in-out;
}

.serial-table-row:hover .hover-floating-actions {
  opacity: 1 !important;
  visibility: visible !important;
  pointer-events: auto !important;
}

.btn-xs {
  padding: 0.2rem 0.45rem;
  font-size: 0.8rem;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.advance-filter-panel {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 🖨️ Clean Print Rules */
@media print {
  .d-print-none {
    display: none !important;
  }
  .d-print-block {
    display: block !important;
  }
  .d-print-inline {
    display: inline !important;
  }
  .report-table-wrap {
    max-height: none !important;
    overflow: visible !important;
  }
  .serial-table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  .serial-table th, .serial-table td {
    border: 1px solid #dee2e6 !important;
    padding: 6px !important;
  }
  .serial-table thead th {
    background-color: #112C47 !important;
    color: #ffffff !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .badge {
    border: 1px solid #ccc !important;
    color: #000 !important;
    background: transparent !important;
  }
  .hover-floating-actions {
    display: none !important;
  }
}
</style>
