<template>
  <div class="container-fluid p-3 warranty-claim-report-page">
    <!-- 🌟 Top Header: Title, Global Search, Advance Filter Toggle & Actions -->
    <div class="card border-0 shadow-sm mb-3 d-print-none">
      <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Left: Page Title -->
          <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark text-nowrap d-flex align-items-center gap-2">
              <i class="fas fa-shield-alt theme-text"></i>
              <span>Warranty Claims Audit Report (ওয়ারেন্টি ক্লেইম অডিট রিপোর্ট)</span>
            </h5>
            <span class="badge bg-secondary font-monospace">{{ summary.total_claims || 0 }}</span>
          </div>

          <!-- Center: Quick Search Input -->
          <div class="flex-grow-1 mx-md-3" style="max-width: 480px;">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Search Claim No, Serial, Invoice, Customer, Mobile, Product... (Enter)"
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
              name="warranty_claims_audit_report.xls"
              title="Export to Excel"
            >
              <i class="fas fa-file-excel"></i>
            </download-excel>

            <!-- Print Report Button -->
            <button
              type="button"
              class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1"
              @click="triggerPrint"
              title="Print Audit Report"
            >
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>

        <!-- 📂 Collapsible Advance Filter Drawer Panel -->
        <div v-show="showAdvanced" class="mt-2 pt-2 border-top advance-filter-panel transition-all">
          <div class="row g-2 align-items-end">
            <!-- Claim Status Filter -->
            <div class="col-md-3 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Claim Status</label>
              <select class="form-select form-select-sm" v-model="search_data.current_status" @change="search">
                <option value="all">-- All Statuses --</option>
                <option value="received">Received (গৃহীত)</option>
                <option value="sent_to_vendor">Sent to Vendor (ভেন্ডরে)</option>
                <option value="in_service">In Service (মেরামতে)</option>
                <option value="repaired">Repaired (মেরামত সম্পন্ন)</option>
                <option value="replaced">Replaced (নতুন পরিবর্তন)</option>
                <option value="ready_for_delivery">Ready for Delivery (প্রস্তুত)</option>
                <option value="delivered">Delivered (হস্তান্তরিত)</option>
                <option value="rejected">Rejected (বাতিল)</option>
              </select>
            </div>

            <!-- Coverage Policy Filter -->
            <div class="col-md-3 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Coverage Type</label>
              <select class="form-select form-select-sm" v-model="search_data.warranty_type" @change="search">
                <option value="all">-- All Policies --</option>
                <option value="warranty">Warranty (ওয়ারেন্টি)</option>
                <option value="guarantee">Guarantee (গ্যারান্টি)</option>
              </select>
            </div>

            <!-- From Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Claim Date From</label>
              <input type="date" class="form-control form-control-sm" v-model="search_data.from_date" @change="search">
            </div>

            <!-- To Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Claim Date To</label>
              <input type="date" class="form-control form-control-sm" v-model="search_data.to_date" @change="search">
            </div>

            <!-- Filter Buttons -->
            <div class="col-md-2 col-sm-12 d-flex gap-1 justify-content-end">
              <button type="button" class="btn btn-sm theme_search_btn w-100 fw-bold" @click="search">
                <i class="fas fa-filter me-1"></i> Apply
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary px-3" @click="resetSearch" title="Reset Filters">
                <i class="fas fa-undo"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📊 Financial & Operational KPI Cards -->
    <div class="row g-2 mb-3 d-print-none">
      <!-- Total Claims Registered -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">Total Claims</div>
              <h4 class="mb-0 fw-bold theme-text font-monospace">{{ summary.total_claims || 0 }}</h4>
            </div>
            <div class="kpi-icon-box theme-bg-soft text-dark">
              <i class="fas fa-file-invoice"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Service Cost (Shop Expense) -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-danger">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Total technician and vendor service cost incurred by shop">Service Cost (খরচ)</div>
              <h4 class="mb-0 fw-bold text-danger font-monospace">Tk. {{ formatPrice(summary.total_service_cost) }}</h4>
            </div>
            <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
              <i class="fas fa-tools"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Customer Charge Collected -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-success">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Total charge collected from customers">Cust. Charge (আদায়)</div>
              <h4 class="mb-0 fw-bold text-success font-monospace">Tk. {{ formatPrice(summary.total_customer_charge) }}</h4>
            </div>
            <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
              <i class="fas fa-hand-holding-usd"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Net Servicing Balance -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4" :class="summary.net_balance >= 0 ? 'border-primary' : 'border-warning'">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Net profit or expense on servicing">Net Margin</div>
              <h4 class="mb-0 fw-bold font-monospace" :class="summary.net_balance >= 0 ? 'text-primary' : 'text-danger'">
                {{ summary.net_balance >= 0 ? '+' : '' }}Tk. {{ formatPrice(summary.net_balance) }}
              </h4>
            </div>
            <div class="kpi-icon-box bg-light text-dark">
              <i class="fas fa-balance-scale"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Currently In Servicing Pipeline -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-info">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Under repair, vendor, or received">In Pipeline (পেন্ডিং)</div>
              <h4 class="mb-0 fw-bold text-info font-monospace">{{ summary.pending_service || 0 }}</h4>
            </div>
            <div class="kpi-icon-box bg-info bg-opacity-10 text-info">
              <i class="fas fa-spinner"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Successfully Delivered -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm kpi-card bg-white p-2 border-start border-4 border-dark">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold" title="Delivered back to customer">Delivered (হস্তান্তরিত)</div>
              <h4 class="mb-0 fw-bold text-dark font-monospace">{{ summary.delivered || 0 }}</h4>
            </div>
            <div class="kpi-icon-box bg-dark bg-opacity-10 text-dark">
              <i class="fas fa-check-double"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🚦 Quick Interactive Status Pipeline Filter Chips -->
    <div class="card border-0 shadow-sm mb-3 d-print-none">
      <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <span class="small fw-bold text-muted me-1">Status Pipeline:</span>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'all' ? 'btn-dark' : 'btn-outline-secondary'"
            @click="filterByStatus('all')"
          >
            All ({{ summary.total_claims || 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'received' ? 'theme-bg text-white' : 'btn-outline-primary'"
            @click="filterByStatus('received')"
          >
            Received ({{ summary.status_breakdown ? summary.status_breakdown.received : 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'sent_to_vendor' ? 'btn-warning text-dark' : 'btn-outline-warning'"
            @click="filterByStatus('sent_to_vendor')"
          >
            Sent to Vendor ({{ summary.status_breakdown ? summary.status_breakdown.sent_to_vendor : 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'in_service' ? 'btn-info text-dark' : 'btn-outline-info'"
            @click="filterByStatus('in_service')"
          >
            In Service ({{ summary.status_breakdown ? summary.status_breakdown.in_service : 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'repaired' ? 'btn-success text-white' : 'btn-outline-success'"
            @click="filterByStatus('repaired')"
          >
            Repaired ({{ summary.status_breakdown ? summary.status_breakdown.repaired : 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'ready_for_delivery' ? 'theme-bg text-white' : 'btn-outline-dark'"
            @click="filterByStatus('ready_for_delivery')"
          >
            Ready ({{ summary.status_breakdown ? summary.status_breakdown.ready_for_delivery : 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'delivered' ? 'btn-dark text-white' : 'btn-outline-secondary'"
            @click="filterByStatus('delivered')"
          >
            Delivered ({{ summary.status_breakdown ? summary.status_breakdown.delivered : 0 }})
          </button>
          <button
            type="button"
            class="btn btn-xs py-1 px-2 rounded-pill"
            :class="search_data.current_status === 'rejected' ? 'btn-danger text-white' : 'btn-outline-danger'"
            @click="filterByStatus('rejected')"
          >
            Rejected ({{ summary.status_breakdown ? summary.status_breakdown.rejected : 0 }})
          </button>
        </div>
      </div>
    </div>

    <!-- 🖨️ Main Report Print & Screen Area (#printArea) -->
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
            <i class="fas fa-shield-alt me-1"></i>
            Warranty & Guarantee Claims Audit Report (ওয়ারেন্টি ও গ্যারান্টি ক্লেইম অডিট)
          </h5>
          <small class="text-muted">
            Total Claims: <strong>{{ meta.total || (records ? records.length : 0) }}</strong> | Generated on: <strong>{{ currentDate }}</strong>
          </small>
        </div>

        <!-- Print Financial Summary Strip -->
        <div class="row g-2 mb-2 text-start">
          <div class="col-4 border p-1 text-center bg-light">
            <small class="d-block text-muted">Total Service Cost</small>
            <strong class="text-danger font-monospace">Tk. {{ formatPrice(summary.total_service_cost) }}</strong>
          </div>
          <div class="col-4 border p-1 text-center bg-light">
            <small class="d-block text-muted">Total Customer Charge</small>
            <strong class="text-success font-monospace">Tk. {{ formatPrice(summary.total_customer_charge) }}</strong>
          </div>
          <div class="col-4 border p-1 text-center bg-light">
            <small class="d-block text-muted">Net Margin</small>
            <strong class="font-monospace" :class="summary.net_balance >= 0 ? 'text-primary' : 'text-danger'">
              {{ summary.net_balance >= 0 ? '+' : '' }}Tk. {{ formatPrice(summary.net_balance) }}
            </strong>
          </div>
        </div>
      </div>

      <!-- 📋 Main Claims Audit Table -->
      <div class="card border-0 shadow-sm mb-2 table-card">
        <div class="card-body p-0 table-responsive report-table-wrap">
          <table class="table table-hover table-sm align-middle mb-0 claims-audit-table">
            <thead class="sticky-top" style="z-index: 2;">
              <tr>
                <th class="text-center" width="3%">#</th>
                <th width="14%">Claim Ticket</th>
                <th width="20%">Product & Serial</th>
                <th width="18%">Customer & Invoice</th>
                <th width="16%">Problem Description</th>
                <th width="14%" class="text-end">Servicing Financials</th>
                <th width="15%" class="text-center">Tracking Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(claim, idx) in records"
                :key="claim.id || idx"
                class="claim-table-row"
              >
                <td class="text-center fw-bold text-secondary font-monospace">{{ (meta.from || 1) + idx }}</td>

                <!-- Claim Ticket & Dates -->
                <td>
                  <router-link
                    :to="{ name: 'warrantyClaim.show', params: { id: claim.id } }"
                    class="fw-bold font-monospace theme-text text-decoration-none d-block d-print-none"
                    title="Click to View Tracking Details"
                  >
                    <i class="fas fa-ticket-alt me-1 text-warning"></i>
                    <span>{{ claim.claim_no }}</span>
                  </router-link>
                  <span class="fw-bold font-monospace d-none d-print-inline">
                    {{ claim.claim_no }}
                  </span>
                  <div class="small text-dark font-monospace mt-0.5" style="font-size: 11px;">
                    <span>Claim: {{ claim.claim_date }}</span>
                  </div>
                  <small class="text-secondary font-monospace d-block" v-if="claim.expected_delivery_date" style="font-size: 10px;">
                    <span>Exp: {{ claim.expected_delivery_date }}</span>
                  </small>
                </td>

                <!-- Product & Serial -->
                <td>
                  <div class="fw-bold text-dark text-truncate" style="max-width: 230px;" :title="claim.item ? claim.item.title : 'Item'">
                    {{ claim.item ? claim.item.title : 'Item' }}
                  </div>
                  <div class="badge theme-bg text-white font-monospace mt-1 px-2 py-0.5" v-if="claim.serial_no">
                    <i class="fas fa-barcode me-1 d-print-none"></i>
                    <span>{{ claim.serial_no }}</span>
                  </div>
                  <div class="small text-secondary mt-0.5" style="font-size: 11px;" v-if="claim.color || claim.size">
                    {{ claim.color ? claim.color.title : '' }} {{ claim.size ? '(' + claim.size.title + ')' : '' }}
                  </div>
                </td>

                <!-- Customer & Invoice -->
                <td>
                  <div class="fw-bold text-dark">{{ claim.customer_name || 'Walk-in Customer' }}</div>
                  <div class="small font-monospace theme-text fw-semibold">
                    <i class="fas fa-phone-alt me-1 text-secondary d-print-none"></i>
                    <span>{{ claim.customer_mobile || 'N/A' }}</span>
                  </div>
                  <small class="text-secondary font-monospace d-block mt-0.5" v-if="claim.invoice" style="font-size: 11px;">
                    <span>Inv: #{{ claim.invoice.invoice_no }}</span>
                    <span v-if="claim.sale_date"> ({{ claim.sale_date }})</span>
                  </small>
                </td>

                <!-- Problem Description & Accessories -->
                <td>
                  <div class="text-dark small fw-semibold text-truncate" style="max-width: 200px;" :title="claim.problem_description">
                    {{ claim.problem_description || 'N/A' }}
                  </div>
                  <div class="small text-muted mt-0.5" v-if="claim.accessories_received" style="font-size: 11px;">
                    <i class="fas fa-paperclip me-1 d-print-none"></i>Acc: {{ claim.accessories_received }}
                  </div>
                  <div class="mt-1">
                    <span class="badge" :class="claim.warranty_type === 'guarantee' ? 'bg-success' : 'theme-bg text-white'" style="font-size: 10px;">
                      {{ claim.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}
                    </span>
                    <span class="small font-monospace text-muted ms-1" v-if="claim.warranty_period" style="font-size: 10px;">{{ claim.warranty_period }}</span>
                  </div>
                </td>

                <!-- Servicing Financials (Service Cost vs Customer Charge) -->
                <td class="text-end font-monospace">
                  <div class="small">
                    <span class="text-muted">Cost: </span>
                    <strong class="text-danger">Tk. {{ formatPrice(claim.service_cost) }}</strong>
                  </div>
                  <div class="small mt-0.5">
                    <span class="text-muted">Charge: </span>
                    <strong class="text-success">Tk. {{ formatPrice(claim.customer_charge) }}</strong>
                  </div>
                  <div class="small mt-0.5 pt-0.5 border-top" style="font-size: 10px;">
                    <span :class="Number(claim.customer_charge) >= Number(claim.service_cost) ? 'text-primary' : 'text-danger'">
                      {{ Number(claim.customer_charge) >= Number(claim.service_cost) ? '+ Tk. ' : '- Tk. ' }}
                      {{ formatPrice(Math.abs(Number(claim.customer_charge) - Number(claim.service_cost))) }}
                    </span>
                  </div>
                </td>

                <!-- Tracking Status & Floating Actions -->
                <td class="text-center position-relative">
                  <span class="badge px-2 py-1 rounded-pill shadow-xs" :class="getStatusBadgeClass(claim.current_status)">
                    {{ formatStatusLabel(claim.current_status) }}
                  </span>

                  <!-- Latest Log Remark snippet if any -->
                  <small class="d-block text-muted text-truncate mt-1 font-monospace" style="max-width: 140px; font-size: 10px;" v-if="claim.logs && claim.logs.length > 0" :title="claim.logs[0].remarks">
                    <span>{{ claim.logs[0].remarks }}</span>
                  </small>

                  <!-- ⭐️ Floating Row Hover Action Buttons (Hidden in print) -->
                  <div class="hover-floating-actions d-print-none">
                    <div class="btn-group btn-group-sm shadow-sm bg-white border rounded px-1 py-1">
                      <!-- View Details -->
                      <router-link
                        :to="{ name: 'warrantyClaim.show', params: { id: claim.id } }"
                        class="btn btn-xs btn-outline-primary border-0"
                        title="View Claim Details & Full Tracking Log"
                      >
                        <i class="fas fa-eye"></i>
                      </router-link>

                      <!-- Edit Claim -->
                      <router-link
                        :to="{ name: 'warrantyClaim.edit', params: { id: claim.id } }"
                        class="btn btn-xs btn-outline-warning border-0"
                        title="Edit Claim"
                      >
                        <i class="fas fa-edit"></i>
                      </router-link>
                    </div>
                  </div>
                </td>
              </tr>

              <!-- Empty Records State -->
              <tr v-if="!loading && (!records || records.length === 0)">
                <td colspan="7" class="text-center py-5 text-secondary">
                  <i class="fas fa-shield-alt fa-3x theme-text opacity-50 mb-3 d-block"></i>
                  <h6 class="fw-bold text-dark">No Warranty Claims Found</h6>
                  <p class="small text-muted mb-0">Try changing your search keywords or date range filters.</p>
                </td>
              </tr>

              <!-- Loading Spinner State -->
              <tr v-if="loading">
                <td colspan="7" class="text-center py-5">
                  <div class="spinner-border theme-text" role="status"></div>
                  <div class="mt-2 small text-secondary fw-semibold">Auditing warranty claims data...</div>
                </td>
              </tr>
            </tbody>
            <!-- Financial Totals on Table Footer -->
            <tfoot class="table-light fw-bold" v-if="records && records.length > 0">
              <tr>
                <td colspan="5" class="text-end text-uppercase font-monospace small">Total Servicing Costs / Charges:</td>
                <td class="text-end font-monospace">
                  <div class="text-danger small">Cost: Tk. {{ formatPrice(summary.total_service_cost) }}</div>
                  <div class="text-success small">Charge: Tk. {{ formatPrice(summary.total_customer_charge) }}</div>
                </td>
                <td class="text-center font-monospace">
                  <span class="badge" :class="summary.net_balance >= 0 ? 'bg-primary' : 'bg-danger'">
                    Net: {{ summary.net_balance >= 0 ? '+' : '' }}Tk. {{ formatPrice(summary.net_balance) }}
                  </span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- 📌 Footer Note (Visible in Print) -->
      <div class="mt-3 pt-2 border-top small text-muted d-none d-print-block">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-0">Generated by: <strong>{{ ($root.auth && $root.auth.name) ? $root.auth.name : 'Admin' }}</strong></p>
            <p class="mb-0">This is a system generated warranty audit report from QPOS.</p>
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
              <span class="text-muted fw-bold">Filtered Claims:</span>
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
  name: 'WarrantyClaimAuditReport',
  data() {
    return {
      model: 'Warranty & Guarantee Claims Audit Report',
      loading: false,
      showAdvanced: false,
      search_data: {
        keyword: '',
        pagination: 20,
        page: 1,
        current_status: 'all',
        warranty_type: 'all',
        from_date: '',
        to_date: '',
      },
      records: [],
      summary: {
        total_claims: 0,
        total_service_cost: 0,
        total_customer_charge: 0,
        net_balance: 0,
        pending_service: 0,
        repaired_ready: 0,
        delivered: 0,
        rejected: 0,
        warranty_count: 0,
        guarantee_count: 0,
        status_breakdown: {},
      },
      meta: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
      },
      exportFields: {
        'Claim Ticket': 'claim_no',
        'Claim Date': 'claim_date',
        'Serial / IMEI': 'serial_no',
        'Product Title': 'product_title',
        'Category': 'category_title',
        'Customer Name': 'customer_name',
        'Customer Mobile': 'customer_mobile',
        'Invoice No': 'invoice_no',
        'Sale Date': 'sale_date',
        'Coverage': 'warranty_type',
        'Warranty Period': 'warranty_period',
        'Problem Description': 'problem_description',
        'Status': 'status_text',
        'Service Cost (Tk)': 'service_cost',
        'Customer Charge (Tk)': 'customer_charge',
        'Expected Delivery': 'expected_delivery_date',
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
      if (this.search_data.current_status !== 'all') count++;
      if (this.search_data.warranty_type !== 'all') count++;
      if (this.search_data.from_date) count++;
      if (this.search_data.to_date) count++;
      return count;
    },
    exportData() {
      return (this.records || []).map(c => ({
        claim_no: c.claim_no,
        claim_date: c.claim_date,
        serial_no: c.serial_no,
        product_title: c.item ? c.item.title : 'Item',
        category_title: c.item && c.item.category ? c.item.category.title : 'N/A',
        customer_name: c.customer_name,
        customer_mobile: c.customer_mobile,
        invoice_no: c.invoice ? c.invoice.invoice_no : 'N/A',
        sale_date: c.sale_date || '',
        warranty_type: c.warranty_type,
        warranty_period: c.warranty_period,
        problem_description: c.problem_description,
        status_text: this.formatStatusLabel(c.current_status),
        service_cost: c.service_cost || 0,
        customer_charge: c.customer_charge || 0,
        expected_delivery_date: c.expected_delivery_date || '',
      }));
    },
  },
  methods: {
    fetchReport(page = 1) {
      this.loading = true;
      this.search_data.page = page;

      axios.get('report/warrantyclaim', { params: this.search_data })
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
          this.$toast('Failed to load warranty claims audit report', 'error');
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
        current_status: 'all',
        warranty_type: 'all',
        from_date: '',
        to_date: '',
      };
      this.fetchReport(1);
    },
    filterByStatus(status) {
      this.search_data.current_status = status;
      this.search();
    },
    changePage(page) {
      if (page >= 1 && page <= (this.meta.last_page || 1)) {
        this.fetchReport(page);
      }
    },
    formatPrice(val) {
      const num = Number(val) || 0;
      return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    triggerPrint() {
      if (typeof this.print === 'function') {
        this.print('printArea', 'Warranty & Guarantee Claims Audit Report');
      } else {
        window.print();
      }
    },
    getStatusBadgeClass(status) {
      const classes = {
        received: 'theme-bg text-white',
        sent_to_vendor: 'bg-warning text-dark',
        in_service: 'bg-info text-dark',
        repaired: 'bg-success text-white',
        replaced: 'bg-success text-white',
        ready_for_delivery: 'theme-bg-soft theme-text border border-primary',
        delivered: 'bg-dark text-white',
        rejected: 'bg-danger text-white',
      };
      return classes[status] || 'bg-secondary text-white';
    },
    formatStatusLabel(status) {
      const labels = {
        received: 'Received (গৃহীত)',
        sent_to_vendor: 'Sent to Vendor (ভেন্ডরে)',
        in_service: 'In Service (মেরামতে)',
        repaired: 'Repaired (মেরামত সম্পন্ন)',
        replaced: 'Replaced (নতুন পরিবর্তন)',
        ready_for_delivery: 'Ready (ডেলিভারি প্রস্তুত)',
        delivered: 'Delivered (হস্তান্তরিত)',
        rejected: 'Rejected (বাতিল)',
      };
      return labels[status] || status;
    },
  },
  created() {
    this.fetchReport(1);
  },
};
</script>

<style scoped>
.warranty-claim-report-page {
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
  max-height: calc(100vh - 310px);
  overflow-y: auto;
}

.claims-audit-table thead {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.claims-audit-table thead th {
  color: #ffffff !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  padding: 8px 12px !important;
  border: none !important;
  background-color: rgb(17, 44, 70) !important;
}

.claims-audit-table tbody td {
  padding: 8px 12px !important;
  font-size: 13px !important;
}

/* ⭐️ Floating Row Hover Action Buttons */
.claim-table-row {
  position: relative !important;
  transition: background-color 0.15s ease-in-out;
}

.claim-table-row:hover {
  background-color: #eef6ff !important;
}

.claim-table-row:hover td {
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

.claim-table-row:hover .hover-floating-actions {
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
  .claims-audit-table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  .claims-audit-table th, .claims-audit-table td {
    border: 1px solid #dee2e6 !important;
    padding: 6px !important;
  }
  .claims-audit-table thead th {
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
