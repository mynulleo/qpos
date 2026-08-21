<template>
  <div class="main-content warranty-claim-view">
    <!-- Loading State -->
    <div class="card border-0 shadow-sm p-5 text-center my-4" v-if="loading">
      <div class="spinner-border theme-text mx-auto mb-3" role="status"></div>
      <h6 class="text-dark fw-bold mb-0">Loading Warranty Claim Details...</h6>
    </div>

    <!-- Main Loaded Content -->
    <div v-else-if="claim">
      <!-- 🌟 Top Header Hero Bar -->
      <div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h4 class="page-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
              <i class="fas fa-shield-alt theme-text"></i>
              <span>Claim Ticket:</span>
              <span class="font-monospace theme-text">{{ claim.claim_no }}</span>
            </h4>
            <span class="badge fs-6 px-3 py-1 rounded-pill shadow-sm" :class="getStatusBadgeClass(claim.current_status)">
              {{ formatStatusLabel(claim.current_status) }}
            </span>
          </div>
          <small class="text-secondary fw-semibold mt-1 d-block">
            <span>Registered on:</span> <strong class="text-dark">{{ claim.claim_date }}</strong>
            <span class="mx-1">•</span>
            <span>Created by:</span> <strong class="text-dark">{{ claim.creator ? claim.creator.name : 'System' }}</strong>
          </small>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm fw-bold d-flex align-items-center gap-1" @click="printClaimSlip">
            <i class="fas fa-print"></i>
            <span>Print Slip (রশিদ)</span>
          </button>
          <router-link :to="{ name: 'warrantyClaim.edit', params: { id: claim.id } }" class="btn btn-outline-primary btn-sm px-3 fw-bold d-flex align-items-center gap-1">
            <i class="fas fa-edit"></i>
            <span>Edit Claim</span>
          </router-link>
          <router-link :to="{ name: 'warrantyClaim.index' }" class="btn btn-outline-secondary btn-sm px-3 fw-bold d-flex align-items-center gap-1">
            <i class="fas fa-arrow-left"></i>
            <span>Back to List</span>
          </router-link>
        </div>
      </div>

      <!-- 📊 Visual Workflow Pipeline Tracker -->
      <div class="card border-0 shadow-sm mb-3 tracker-card">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center position-relative">
            <div class="progress position-absolute w-100 d-none d-md-block" style="height: 4px; z-index: 1; top: 18px;">
              <div class="progress-bar theme-bg" role="progressbar" :style="{ width: pipelineProgress + '%' }"></div>
            </div>

            <div class="pipeline-step text-center position-relative" style="z-index: 2;" :class="{ 'active': isStepActive('received') }">
              <div class="step-icon rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1" :class="isStepActive('received') ? 'theme-bg text-white' : 'bg-light text-secondary border'">
                <i class="fas fa-inbox"></i>
              </div>
              <span class="small fw-bold d-block text-dark">1. Received</span>
              <small class="text-secondary" style="font-size: 11px;">পণ্য গ্রহণ</small>
            </div>

            <div class="pipeline-step text-center position-relative" style="z-index: 2;" :class="{ 'active': isStepActive('in_service') || isStepActive('sent_to_vendor') }">
              <div class="step-icon rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1" :class="(isStepActive('in_service') || isStepActive('sent_to_vendor')) ? 'theme-bg text-white' : 'bg-light text-secondary border'">
                <i class="fas fa-tools"></i>
              </div>
              <span class="small fw-bold d-block text-dark">2. In Service / Vendor</span>
              <small class="text-secondary" style="font-size: 11px;">সার্ভিসিং চলছে</small>
            </div>

            <div class="pipeline-step text-center position-relative" style="z-index: 2;" :class="{ 'active': isStepActive('repaired') || isStepActive('replaced') }">
              <div class="step-icon rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1" :class="(isStepActive('repaired') || isStepActive('replaced')) ? 'theme-bg text-white' : 'bg-light text-secondary border'">
                <i class="fas fa-check"></i>
              </div>
              <span class="small fw-bold d-block text-dark">3. Repaired / Replaced</span>
              <small class="text-secondary" style="font-size: 11px;">মেরামত সম্পন্ন</small>
            </div>

            <div class="pipeline-step text-center position-relative" style="z-index: 2;" :class="{ 'active': isStepActive('ready_for_delivery') }">
              <div class="step-icon rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1" :class="isStepActive('ready_for_delivery') ? 'theme-bg text-white' : 'bg-light text-secondary border'">
                <i class="fas fa-box-open"></i>
              </div>
              <span class="small fw-bold d-block text-dark">4. Ready for Delivery</span>
              <small class="text-secondary" style="font-size: 11px;">ডেলিভারি প্রস্তুত</small>
            </div>

            <div class="pipeline-step text-center position-relative" style="z-index: 2;" :class="{ 'active': isStepActive('delivered') }">
              <div class="step-icon rounded-circle mx-auto d-flex align-items-center justify-content-center mb-1" :class="isStepActive('delivered') ? 'bg-success text-white' : 'bg-light text-secondary border'">
                <i class="fas fa-handshake"></i>
              </div>
              <span class="small fw-bold d-block text-dark">5. Delivered</span>
              <small class="text-secondary" style="font-size: 11px;">গ্রাহককে হস্তান্তর</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Two-Column Layout -->
      <div class="row g-3 align-items-start">
        <!-- 👈 Left Column: Customer, Policy, Item & Financial Details -->
        <div class="col-lg-5 col-md-12 d-flex flex-column gap-3">
          <!-- 1. Customer & Sales Info Card -->
          <div class="card border-0 shadow-sm form-section-card">
            <div class="card-header theme-bg text-white py-2 px-3 d-flex justify-content-between align-items-center">
              <span class="fw-bold small">
                <i class="fas fa-user me-2 text-warning"></i>
                <span>Customer & Sales Info</span>
              </span>
              <span class="badge bg-light text-dark font-monospace" v-if="claim.invoice">
                Inv #{{ claim.invoice.invoice_no || claim.invoice.invoiceno }}
              </span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless info-table mb-0">
                <tbody>
                  <tr>
                    <th width="35%">Customer:</th>
                    <td class="fw-bold text-dark">{{ claim.customer_name }}</td>
                  </tr>
                  <tr>
                    <th>Mobile:</th>
                    <td class="font-monospace fw-bold theme-text">{{ claim.customer_mobile }}</td>
                  </tr>
                  <tr>
                    <th>Address:</th>
                    <td class="text-dark">{{ claim.customer_address || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th>Sale Date:</th>
                    <td class="font-monospace text-dark">{{ claim.sale_date || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 2. Product & Warranty Policy Details Card -->
          <div class="card border-0 shadow-sm form-section-card">
            <div class="card-header theme-bg text-white py-2 px-3">
              <span class="fw-bold small">
                <i class="fas fa-box me-2 text-warning"></i>
                <span>Product & Warranty Policy</span>
              </span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless info-table mb-0">
                <tbody>
                  <tr>
                    <th width="35%">Product Title:</th>
                    <td class="fw-bold text-dark fs-6">{{ claim.item ? claim.item.title : 'Item' }}</td>
                  </tr>
                  <tr>
                    <th>Serial Number:</th>
                    <td>
                      <span class="badge theme-bg text-white font-monospace fs-6 px-2 py-1">
                        {{ claim.serial_no || 'N/A' }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="claim.color || claim.size">
                    <th>Variant:</th>
                    <td>
                      <span class="badge bg-info text-dark me-1" v-if="claim.color">{{ claim.color.title }}</span>
                      <span class="badge bg-secondary" v-if="claim.size">{{ claim.size.title }}</span>
                    </td>
                  </tr>
                  <tr>
                    <th>Coverage Type:</th>
                    <td>
                      <span class="badge" :class="claim.warranty_type === 'guarantee' ? 'bg-success' : 'theme-bg text-white'">
                        {{ claim.warranty_type === 'guarantee' ? 'Guarantee (গ্যারান্টি)' : 'Warranty (ওয়ারেন্টি)' }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th>Policy Duration:</th>
                    <td class="fw-bold text-dark">{{ claim.warranty_period || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th>Policy Expiry:</th>
                    <td class="text-danger fw-bold font-monospace">{{ claim.warranty_expiry_date || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 3. Issue Description & Accessories Card -->
          <div class="card border-0 shadow-sm form-section-card">
            <div class="card-header theme-bg text-white py-2 px-3">
              <span class="fw-bold small">
                <i class="fas fa-clipboard-list me-2 text-warning"></i>
                <span>Problem & Received Items</span>
              </span>
            </div>
            <div class="card-body p-3">
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark mb-1">Problem Description (সমস্যার বিবরণ):</label>
                <div class="p-2 border rounded bg-light text-dark small fw-semibold">{{ claim.problem_description }}</div>
              </div>
              <div class="mb-3" v-if="claim.accessories_received">
                <label class="form-label small fw-bold text-dark mb-1">Received Accessories (সাথে জমা নেওয়া সামগ্রী):</label>
                <div class="p-2 border rounded bg-light text-dark small font-monospace">{{ claim.accessories_received }}</div>
              </div>
              <div class="row g-2 pt-2 border-top">
                <div class="col-6">
                  <span class="text-secondary small d-block">Expected Delivery:</span>
                  <strong class="text-dark font-monospace">{{ claim.expected_delivery_date || 'N/A' }}</strong>
                </div>
                <div class="col-6" v-if="claim.delivered_date">
                  <span class="text-secondary small d-block">Delivered Date:</span>
                  <strong class="text-success font-monospace">{{ claim.delivered_date }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Financial Summary Card -->
          <div class="card border-0 shadow-sm form-section-card">
            <div class="card-header theme-bg text-white py-2 px-3">
              <span class="fw-bold small">
                <i class="fas fa-receipt me-2 text-warning"></i>
                <span>Service Financials (আর্থিক বিবরণ)</span>
              </span>
            </div>
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="small fw-bold text-dark">Internal Service Cost (দোকানের খরচ):</span>
                <strong class="font-monospace text-dark">Tk. {{ formatPrice(claim.service_cost) }}</strong>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-dark">Customer Service Charge (গ্রাহক বিল):</span>
                <strong class="font-monospace theme-text fs-6 fw-bold">Tk. {{ formatPrice(claim.customer_charge) }}</strong>
              </div>
            </div>
          </div>
        </div>

        <!-- 👉 Right Column: Compact Status Update + Sleek Activity Feed Timeline -->
        <div class="col-lg-7 col-md-12 d-flex flex-column gap-3">
          <!-- 📝 1. Compact Status Update Box with Quick Status Chips -->
          <div class="card border-0 shadow-sm form-section-card">
            <div class="card-header theme-bg text-white py-2 px-3 d-flex justify-content-between align-items-center">
              <span class="fw-bold small">
                <i class="fas fa-plus-circle me-2 text-warning"></i>
                <span>Quick Status Update & Note (নতুন স্ট্যাটাস ও নোট)</span>
              </span>
              <span class="badge bg-warning text-dark fw-bold font-monospace">Current: {{ formatStatusLabel(claim.current_status) }}</span>
            </div>
            <div class="card-body p-3 bg-light">
              <form @submit.prevent="addTrackingLog">
                <!-- Quick Status Selector Chips -->
                <div class="mb-2">
                  <label class="form-label small fw-bold text-dark mb-1">Select Next Status (স্ট্যাটাস নির্বাচন করুন):</label>
                  <div class="d-flex flex-wrap gap-1">
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'received' ? 'btn-primary active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'received'"
                    >
                      <i class="fas fa-inbox me-1"></i><span>Received</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'sent_to_vendor' ? 'btn-warning text-dark active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'sent_to_vendor'"
                    >
                      <i class="fas fa-truck me-1"></i><span>Vendor</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'in_service' ? 'btn-info text-dark active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'in_service'"
                    >
                      <i class="fas fa-tools me-1"></i><span>In Service</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'repaired' ? 'btn-success active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'repaired'"
                    >
                      <i class="fas fa-check-circle me-1"></i><span>Repaired</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'ready_for_delivery' ? 'btn-theme active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'ready_for_delivery'"
                    >
                      <i class="fas fa-box-open me-1"></i><span>Ready</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'delivered' ? 'btn-dark active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'delivered'"
                    >
                      <i class="fas fa-handshake me-1"></i><span>Delivered</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm status-pill-btn"
                      :class="logForm.status === 'rejected' ? 'btn-danger active' : 'btn-outline-secondary'"
                      @click="logForm.status = 'rejected'"
                    >
                      <i class="fas fa-ban me-1"></i><span>Rejected</span>
                    </button>
                  </div>
                </div>

                <!-- Compact Form Inputs -->
                <div class="row g-2">
                  <div class="col-12">
                    <textarea
                      class="form-control form-control-sm"
                      rows="2"
                      v-model="logForm.remarks"
                      required
                      placeholder="Write progress note or remarks (e.g. Sent to service center, IC repaired, customer notified)..."
                    ></textarea>
                  </div>

                  <div class="col-md-4">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-white small" title="Follow-up Reminder Date">
                        <i class="fas fa-bell text-warning"></i>
                      </span>
                      <input type="date" class="form-control form-control-sm" v-model="logForm.reminder_date" placeholder="Reminder">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-white small">Bill Tk.</span>
                      <input type="number" step="0.01" min="0" class="form-control form-control-sm font-monospace text-end" v-model.number="logForm.customer_charge" placeholder="Charge">
                    </div>
                  </div>

                  <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-theme btn-sm w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1" :disabled="addingLog">
                      <i :class="addingLog ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                      <span>Save Status Note</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- ⏱️ 2. Redesigned Modern Activity Timeline (Space-Optimized Feed) -->
          <div class="card border-0 shadow-sm form-section-card">
            <div class="card-header theme-bg text-white py-2 px-3 d-flex justify-content-between align-items-center">
              <span class="fw-bold small">
                <i class="fas fa-stream me-2 text-warning"></i>
                <span>Status History & Remarks Timeline (ট্র্যাকিং হিস্টোরি ও টাইমলাইন)</span>
              </span>
              <span class="badge bg-light text-dark fw-bold font-monospace">
                {{ claim.logs ? claim.logs.length : 0 }} Steps
              </span>
            </div>
            <div class="card-body p-3">
              <div class="modern-timeline" v-if="claim.logs && claim.logs.length > 0">
                <div
                  class="modern-timeline-item position-relative pb-3 mb-2"
                  v-for="(log, idx) in claim.logs"
                  :key="log.id || idx"
                  :class="{ 'is-latest': idx === 0 }"
                >
                  <!-- Vertical Connecting Line -->
                  <div class="timeline-v-line" v-if="idx < claim.logs.length - 1"></div>

                  <!-- Timeline Node Icon -->
                  <div class="timeline-node rounded-circle d-flex align-items-center justify-content-center shadow-sm" :class="getStatusBadgeClass(log.status)">
                    <i :class="getStatusIcon(log.status)"></i>
                  </div>

                  <!-- Timeline Content Box -->
                  <div class="timeline-content-card border rounded p-2 bg-white shadow-xs ms-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge px-2 py-0.5 small" :class="getStatusBadgeClass(log.status)">
                          {{ formatStatusLabel(log.status) }}
                        </span>
                        <span class="small fw-bold text-dark">
                          <i class="fas fa-user-circle text-secondary me-1"></i>
                          <span>{{ log.creator ? log.creator.name : 'Staff' }}</span>
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1 font-monospace" style="font-size: 10px;" v-if="idx === 0">
                          Latest
                        </span>
                      </div>
                      <span class="text-secondary font-monospace" style="font-size: 11px;">
                        <i class="fas fa-clock me-1 text-muted"></i>
                        <span>{{ formatDateTime(log.created_at) }}</span>
                      </span>
                    </div>

                    <!-- Note / Remarks Text -->
                    <div class="timeline-remarks-text text-dark small fw-semibold py-1 px-2 rounded bg-light border-start border-3" :class="getRemarksBorderClass(log.status)">
                      {{ log.remarks }}
                    </div>

                    <!-- Inline Meta Tags (Reminder, Charges, Costs) -->
                    <div class="d-flex flex-wrap gap-2 mt-1 pt-1" v-if="log.reminder_date">
                      <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 11px;">
                        <i class="fas fa-bell me-1 text-warning"></i>
                        <span>Follow-up: {{ log.reminder_date }}</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div class="text-center py-4 text-secondary" v-else>
                <i class="fas fa-history fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                <p class="mb-0 small fw-bold">No tracking logs recorded yet.</p>
                <small class="text-muted">Use the quick update box above to add the first status update.</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Hidden Printable Customer Claim Receipt Slip (Dynamic Formats based on Site Settings) -->
      <div id="warranty-claim-print-slip" class="d-none">
        <!-- 1. 🖨️ Thermal 80mm Layout (Standard 3-Inch Warranty Claim Slip) -->
        <div v-if="effectivePrintFormat === 'thermal-80mm'" class="thermal-80mm-slip" style="width: 78mm; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; line-height: 1.35; padding: 4px; margin: 0 auto; color: #000;">
          <div style="text-align: center; margin-bottom: 8px;">
            <h2 style="font-size: 15px; font-weight: bold; margin: 0 0 2px 0; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
            <div style="font-size: 10px;">{{ $root.site?.address || '' }}</div>
            <div style="font-size: 10px;">Mob: {{ $root.site?.mobile1 || '' }} <span v-if="$root.site?.mobile2">/ {{ $root.site?.mobile2 }}</span></div>
            <div style="font-size: 12px; font-weight: bold; margin-top: 4px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 3px 0; letter-spacing: 1px;">
              WARRANTY CLAIM TICKET
            </div>
          </div>

          <div style="margin-bottom: 6px; font-size: 10px; line-height: 1.3;">
            <div style="display: flex; justify-content: space-between;">
              <span><strong>Claim No:</strong> {{ claim.claim_no }}</span>
              <span><strong>Date:</strong> {{ claim.claim_date }}</span>
            </div>
            <div><strong>Customer:</strong> {{ claim.customer_name }}</div>
            <div><strong>Mobile:</strong> {{ claim.customer_mobile }}</div>
            <div v-if="claim.customer_address"><strong>Address:</strong> {{ claim.customer_address }}</div>
            <div><strong>Status:</strong> {{ formatStatusLabel(claim.current_status) }}</div>
          </div>

          <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 4px 0; margin-bottom: 6px; font-size: 10px; line-height: 1.35;">
            <div><strong>Item:</strong> {{ claim.item ? claim.item.title : 'Item' }}</div>
            <div v-if="claim.serial_no"><strong>Serial No:</strong> {{ claim.serial_no }}</div>
            <div v-if="claim.invoice_no"><strong>Inv Ref:</strong> {{ claim.invoice_no }}</div>
            <div><strong>Coverage:</strong> {{ claim.warranty_type === 'guarantee' ? 'Replacement Guarantee' : 'Official Warranty' }} ({{ claim.warranty_period }})</div>
            <div v-if="claim.warranty_expiry_date"><strong>Expiry Date:</strong> {{ claim.warranty_expiry_date }}</div>
            <div v-if="claim.expected_delivery_date"><strong>Exp. Delivery:</strong> {{ claim.expected_delivery_date }}</div>
          </div>

          <div style="margin-bottom: 6px; font-size: 10px; line-height: 1.35;">
            <div><strong>Reported Problem:</strong></div>
            <div style="padding-left: 5px; color: #222;">{{ claim.problem_description }}</div>
            <div v-if="claim.accessories_received" style="margin-top: 2px;">
              <strong>Accessories Received:</strong> {{ claim.accessories_received }}
            </div>
            <div v-if="claim.customer_charge > 0" style="margin-top: 2px; font-weight: bold;">
              <strong>Estimated Charge:</strong> Tk. {{ formatPrice(claim.customer_charge) }}
            </div>
          </div>

          <div style="border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; font-size: 9px; line-height: 1.25;">
            <div>* Please preserve this ticket for product collection.</div>
            <div>* Service turnaround subject to spare parts availability.</div>
          </div>

          <div style="border-top: 1px dashed #000; padding-top: 15px; margin-top: 15px; display: flex; justify-content: space-between; font-size: 9px;">
            <div style="border-top: 1px solid #000; width: 45%; text-align: center;">Customer Sign</div>
            <div style="border-top: 1px solid #000; width: 45%; text-align: center;">Authorized Sign</div>
          </div>
        </div>

        <!-- 2. 🖨️ Thermal 60mm / 58mm Layout (Compact 2-Inch Warranty Claim Slip) -->
        <div v-else-if="effectivePrintFormat === 'thermal-60mm'" class="thermal-60mm-slip" style="width: 56mm; font-family: monospace, Arial; font-size: 9.5px; line-height: 1.25; padding: 2px; margin: 0 auto; color: #000;">
          <div style="text-align: center; margin-bottom: 5px;">
            <h2 style="font-size: 13px; font-weight: bold; margin: 0 0 1px 0; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
            <div style="font-size: 8.5px;">{{ $root.site?.address || '' }}</div>
            <div style="font-size: 8.5px;">Mob: {{ $root.site?.mobile1 || '' }}</div>
            <div style="font-size: 10.5px; font-weight: bold; margin-top: 3px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 2px 0;">
              WARRANTY CLAIM SLIP
            </div>
          </div>

          <div style="margin-bottom: 4px; font-size: 8.5px; line-height: 1.2;">
            <div><strong>Ticket:</strong> {{ claim.claim_no }}</div>
            <div><strong>Date:</strong> {{ claim.claim_date }}</div>
            <div><strong>Cust:</strong> {{ claim.customer_name }}</div>
            <div><strong>Ph:</strong> {{ claim.customer_mobile }}</div>
          </div>

          <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 0; margin-bottom: 4px; font-size: 8.5px;">
            <div><strong>Item:</strong> {{ claim.item ? claim.item.title : 'Item' }}</div>
            <div v-if="claim.serial_no"><strong>S/N:</strong> {{ claim.serial_no }}</div>
            <div><strong>Type:</strong> {{ claim.warranty_type }} ({{ claim.warranty_period }})</div>
            <div v-if="claim.expected_delivery_date"><strong>Delivery:</strong> {{ claim.expected_delivery_date }}</div>
          </div>

          <div style="margin-bottom: 4px; font-size: 8.5px;">
            <div><strong>Problem:</strong> {{ claim.problem_description }}</div>
            <div v-if="claim.accessories_received"><strong>Acc:</strong> {{ claim.accessories_received }}</div>
            <div v-if="claim.customer_charge > 0"><strong>Charge:</strong> Tk. {{ formatPrice(claim.customer_charge) }}</div>
          </div>

          <div style="border-top: 1px dashed #000; padding-top: 12px; margin-top: 10px; display: flex; justify-content: space-between; font-size: 8px;">
            <div style="border-top: 1px solid #000; width: 45%; text-align: center;">Customer</div>
            <div style="border-top: 1px solid #000; width: 45%; text-align: center;">Authorized</div>
          </div>
        </div>

        <!-- 3. 🖨️ Normal Printer A5 Layout (Compact Half-Page Claim Slip) -->
        <div v-else-if="effectivePrintFormat === 'normal-a5'" class="normal-a5-slip" style="width: 100%; max-width: 140mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; line-height: 1.35; color: #111; margin: 0 auto; padding: 6px;">
          <!-- Header -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #112C47; padding-bottom: 8px; margin-bottom: 8px;">
            <div>
              <h2 style="font-size: 16px; font-weight: bold; margin: 0; color: #112C47;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
              <div style="font-size: 10px; color: #444;">{{ $root.site?.address || '' }}</div>
              <div style="font-size: 10px; color: #444;">Phone: {{ $root.site?.mobile1 || '' }} | Email: {{ $root.site?.contact_email || '' }}</div>
            </div>
            <div style="text-align: right;">
              <div style="display: inline-block; background: #112C47; color: #fff; font-size: 11px; font-weight: bold; padding: 2px 10px; border-radius: 3px;">
                WARRANTY CLAIM TICKET
              </div>
              <div style="font-size: 12px; font-weight: bold; margin-top: 4px; font-family: monospace; color: #112C47;">#{{ claim.claim_no }}</div>
              <div style="font-size: 9.5px; color: #555;">Date: {{ claim.claim_date }}</div>
            </div>
          </div>

          <!-- Customer & Product Grid -->
          <div style="display: flex; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
            <div style="width: 48%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; font-size: 10px;">
              <strong style="color: #112C47; text-transform: uppercase; font-size: 9.5px;">Customer Details (গ্রাহকের তথ্য):</strong>
              <div style="font-weight: bold; font-size: 11px; margin-top: 2px;">{{ claim.customer_name }}</div>
              <div>Mobile: <strong>{{ claim.customer_mobile }}</strong></div>
              <div v-if="claim.customer_address">Address: {{ claim.customer_address }}</div>
            </div>
            <div style="width: 48%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; font-size: 10px;">
              <strong style="color: #112C47; text-transform: uppercase; font-size: 9.5px;">Item & Policy Coverage:</strong>
              <div style="font-weight: bold; font-size: 11px; margin-top: 2px;">{{ claim.item ? claim.item.title : 'Item' }}</div>
              <div v-if="claim.serial_no">S/N: <strong style="font-family: monospace; color: #0284c7;">{{ claim.serial_no }}</strong></div>
              <div>Coverage: <span style="font-weight: bold; color: #16a34a;">{{ claim.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }} ({{ claim.warranty_period }})</span></div>
              <div v-if="claim.expected_delivery_date">Exp. Delivery: <strong>{{ claim.expected_delivery_date }}</strong></div>
            </div>
          </div>

          <!-- Issue & Accessories Table -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10px;">
            <tbody>
              <tr style="background: #f1f5f9;">
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; width: 35%; text-align: left;">Reported Defect / Issue:</th>
                <td style="border: 1px solid #cbd5e1; padding: 4px 6px; font-weight: 500;">{{ claim.problem_description }}</td>
              </tr>
              <tr v-if="claim.accessories_received">
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left;">Accessories Received:</th>
                <td style="border: 1px solid #cbd5e1; padding: 4px 6px;">{{ claim.accessories_received }}</td>
              </tr>
              <tr v-if="claim.customer_charge > 0">
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left;">Estimated Service Charge:</th>
                <td style="border: 1px solid #cbd5e1; padding: 4px 6px; font-weight: bold; color: #112C47;">৳ {{ formatPrice(claim.customer_charge) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Terms -->
          <div style="background: #fafafa; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 8px; font-size: 9px; color: #64748b; margin-bottom: 20px; line-height: 1.3;">
            <div>1. Original claim ticket must be presented when collecting the serviced product.</div>
            <div>2. Products not collected within 30 days of readiness notification will not be store responsibility.</div>
          </div>

          <!-- Signatures -->
          <div style="display: flex; justify-content: space-between; font-size: 9px; color: #333;">
            <div style="border-top: 1px dashed #64748b; width: 35%; text-align: center; padding-top: 3px;">Customer's Signature</div>
            <div style="border-top: 1px dashed #64748b; width: 35%; text-align: center; padding-top: 3px;">Authorized Service Engineer</div>
          </div>
        </div>

        <!-- 4. 🖨️ Normal Printer A4 Layout (Formal Corporate Service Claim Acknowledgement) -->
        <div v-else class="normal-a4-slip" style="width: 100%; max-width: 190mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #111; margin: 0 auto; padding: 10px;">
          <!-- Header -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #112C47; padding-bottom: 12px; margin-bottom: 14px;">
            <div>
              <h1 style="font-size: 22px; font-weight: bold; margin: 0 0 4px 0; color: #112C47; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h1>
              <div style="font-size: 11px; color: #475569; max-width: 380px;">{{ $root.site?.address || '' }}</div>
              <div style="font-size: 11px; color: #475569; margin-top: 2px;">
                <span><strong>Phone:</strong> {{ $root.site?.mobile1 || '' }} <span v-if="$root.site?.mobile2">/ {{ $root.site?.mobile2 }}</span></span>
                <span v-if="$root.site?.contact_email" style="margin-left: 10px;"><strong>Email:</strong> {{ $root.site?.contact_email }}</span>
              </div>
            </div>
            <div style="text-align: right;">
              <div style="display: inline-block; background: #112C47; color: #fff; font-size: 13px; font-weight: bold; padding: 4px 14px; border-radius: 4px; letter-spacing: 0.5px;">
                WARRANTY CLAIM ACKNOWLEDGEMENT
              </div>
              <div style="font-size: 15px; font-weight: bold; margin-top: 6px; font-family: monospace; color: #112C47;">#{{ claim.claim_no }}</div>
              <div style="font-size: 11px; color: #64748b;"><strong>Received Date:</strong> {{ claim.claim_date }}</div>
              <div style="font-size: 11px; color: #64748b;"><strong>Status:</strong> {{ formatStatusLabel(claim.current_status) }}</div>
            </div>
          </div>

          <!-- Section 1 & 2: 2 Column Box (Customer Details + Product Specifications) -->
          <div style="display: flex; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
            <!-- Customer Box -->
            <div style="width: 48%; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px;">
              <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 4px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px;">
                1. Customer & Contact Information
              </div>
              <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 4px;">{{ claim.customer_name }}</div>
              <div style="font-size: 11.5px; color: #334155; margin-top: 2px;"><strong>Mobile:</strong> {{ claim.customer_mobile }}</div>
              <div style="font-size: 11px; color: #475569; margin-top: 2px;" v-if="claim.customer_address"><strong>Address:</strong> {{ claim.customer_address }}</div>
            </div>

            <!-- Product Box -->
            <div style="width: 48%; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px;">
              <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 4px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px;">
                2. Product & Warranty Verification
              </div>
              <div style="font-size: 13px; font-weight: bold; color: #0f172a; margin-top: 4px;">{{ claim.item ? claim.item.title : 'Item' }}</div>
              <div style="font-size: 11.5px; color: #334155; margin-top: 2px;" v-if="claim.serial_no">
                Serial No (S/N): <strong style="font-family: monospace; color: #0284c7;">{{ claim.serial_no }}</strong>
              </div>
              <div style="font-size: 11px; color: #475569; margin-top: 2px;" v-if="claim.invoice_no">
                Original Invoice Ref: <strong>#{{ claim.invoice_no }}</strong>
              </div>
              <div style="font-size: 11px; color: #16a34a; font-weight: 600; margin-top: 2px;">
                Policy Coverage: {{ claim.warranty_type === 'guarantee' ? 'Replacement Guarantee' : 'Official Warranty' }} ({{ claim.warranty_period }})
              </div>
            </div>
          </div>

          <!-- Section 3: Technical Defect & Inspection Details -->
          <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 14px; overflow: hidden;">
            <div style="background: #f1f5f9; padding: 6px 12px; font-size: 11px; font-weight: bold; color: #112C47; text-transform: uppercase;">
              3. Claim Inspection, Fault Description & Handover Check
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
              <tbody>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <th style="padding: 8px 12px; width: 30%; text-align: left; background: #fafafa; color: #475569;">Reported Fault / Symptoms:</th>
                  <td style="padding: 8px 12px; font-weight: 500;">{{ claim.problem_description }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <th style="padding: 8px 12px; text-align: left; background: #fafafa; color: #475569;">Received Accessories:</th>
                  <td style="padding: 8px 12px;">{{ claim.accessories_received || 'Main Unit Only' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                  <th style="padding: 8px 12px; text-align: left; background: #fafafa; color: #475569;">Estimated Delivery Date:</th>
                  <td style="padding: 8px 12px; font-weight: bold; color: #0284c7;">{{ claim.expected_delivery_date || 'Within 7-14 Business Days' }}</td>
                </tr>
                <tr v-if="claim.customer_charge > 0">
                  <th style="padding: 8px 12px; text-align: left; background: #fafafa; color: #475569;">Customer Payable / Service Charge:</th>
                  <td style="padding: 8px 12px; font-weight: bold; color: #166534; font-family: monospace;">৳ {{ formatPrice(claim.customer_charge) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Section 4: Terms & Service Agreement -->
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; font-size: 10px; color: #64748b; line-height: 1.4; margin-bottom: 30px;">
            <div style="font-weight: bold; color: #334155; margin-bottom: 2px;">Service Terms & Customer Notice:</div>
            <div>1. Customers must present this original acknowledgement slip when collecting the serviced product.</div>
            <div>2. Any physical burn, water ingress, or unauthorized repair attempts found during lab inspection will void the warranty.</div>
            <div>3. Unclaimed items after 30 days from final notification date may incur storage charges or be disposed of according to policy.</div>
          </div>

          <!-- Signatures -->
          <div style="display: flex; justify-content: space-between; margin-top: 40px; font-size: 11px; color: #334155;">
            <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Customer's Signature</div>
            <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Received By (Staff)</div>
            <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Authorized Service Seal</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const model = 'warrantyClaim';

export default {
  name: 'WarrantyClaimView',
  data() {
    return {
      model: model,
      loading: true,
      claim: null,
      addingLog: false,
      logForm: {
        status: 'in_service',
        remarks: '',
        reminder_date: '',
        service_cost: null,
        customer_charge: null,
      },
    };
  },
  computed: {
    printerType() {
      return this.$root.site?.printer_type || 'thermal';
    },
    normalPaperSize() {
      return this.$root.site?.normal_paper_size || 'A4';
    },
    thermalPaperSize() {
      return this.$root.site?.thermal_paper_size || '80mm';
    },
    effectivePrintFormat() {
      const type = (this.printerType || 'thermal').toString().toLowerCase();
      if (type === 'normal') {
        const size = (this.normalPaperSize || 'A4').toString().toUpperCase();
        return size === 'A5' ? 'normal-a5' : 'normal-a4';
      } else {
        const size = (this.thermalPaperSize || '80mm').toString().toLowerCase();
        return size === '60mm' ? 'thermal-60mm' : 'thermal-80mm';
      }
    },
    pipelineProgress() {
      if (!this.claim) return 0;
      const statusMap = {
        received: 10,
        sent_to_vendor: 30,
        in_service: 45,
        repaired: 70,
        replaced: 75,
        ready_for_delivery: 85,
        delivered: 100,
        rejected: 100,
      };
      return statusMap[this.claim.current_status] || 10;
    },
  },
  methods: {
    loadClaim() {
      this.loading = true;
      axios.get(`${this.model}/${this.$route.params.id}`)
        .then(res => {
          this.claim = res.data;
          this.logForm.status = this.claim.current_status;
          this.logForm.service_cost = this.claim.service_cost;
          this.logForm.customer_charge = this.claim.customer_charge;

          // Auto-print receipt if query param autoPrint=1 was passed from create
          if (this.$route.query.autoPrint) {
            this.$nextTick(() => {
              setTimeout(() => {
                this.printClaimSlip();
              }, 400);
            });
          }
        })
        .catch(err => {
          this.$toast('Failed to load claim details', 'error');
          console.error(err);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    addTrackingLog() {
      if (!this.logForm.remarks || this.logForm.remarks.trim() === '') {
        this.$toast('Please enter a remark or progress note', 'warning');
        return;
      }

      this.addingLog = true;
      axios.post(`warrantyClaim/${this.claim.id}/add-log`, this.logForm)
        .then(res => {
          this.$toast('Tracking status & note updated successfully!', 'success');
          this.claim = res.data.data;
          this.logForm.remarks = '';
          this.logForm.reminder_date = '';
        })
        .catch(err => {
          this.$toast('Failed to add tracking log', 'error');
          console.error(err);
        })
        .finally(() => {
          this.addingLog = false;
        });
    },
    isStepActive(step) {
      if (!this.claim) return false;
      const hierarchy = ['received', 'sent_to_vendor', 'in_service', 'repaired', 'replaced', 'ready_for_delivery', 'delivered'];
      const currentIdx = hierarchy.indexOf(this.claim.current_status);
      const stepIdx = hierarchy.indexOf(step);
      return currentIdx >= stepIdx;
    },
    formatPrice(val) {
      return (parseFloat(val) || 0).toFixed(2);
    },
    formatDateTime(dt) {
      if (!dt) return '';
      return new Date(dt).toLocaleString('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
      });
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
    getStatusIcon(status) {
      const icons = {
        received: 'fas fa-inbox',
        sent_to_vendor: 'fas fa-truck',
        in_service: 'fas fa-tools',
        repaired: 'fas fa-check-circle',
        replaced: 'fas fa-sync-alt',
        ready_for_delivery: 'fas fa-box-open',
        delivered: 'fas fa-handshake',
        rejected: 'fas fa-ban',
      };
      return icons[status] || 'fas fa-circle';
    },
    getRemarksBorderClass(status) {
      const borders = {
        received: 'border-primary',
        sent_to_vendor: 'border-warning',
        in_service: 'border-info',
        repaired: 'border-success',
        replaced: 'border-success',
        ready_for_delivery: 'border-primary',
        delivered: 'border-dark',
        rejected: 'border-danger',
      };
      return borders[status] || 'border-secondary';
    },
    formatStatusLabel(status) {
      const labels = {
        received: 'Received (গৃহীত)',
        sent_to_vendor: 'Sent to Vendor (ভেন্ডরে প্রেরিত)',
        in_service: 'In Service / Repair (মেরামতে আছে)',
        repaired: 'Repaired (মেরামত সম্পন্ন)',
        replaced: 'Replaced (নতুন পরিবর্তন)',
        ready_for_delivery: 'Ready for Delivery (ডেলিভারি প্রস্তুত)',
        delivered: 'Delivered (গ্রাহককে হস্তান্তর)',
        rejected: 'Rejected (বাতিল)',
      };
      return labels[status] || status;
    },
    printClaimSlip() {
      const printContents = document.getElementById('warranty-claim-print-slip');
      if (!printContents || !this.claim) return;
      const format = this.effectivePrintFormat;
      const claimNo = this.claim.claim_no || 'Claim-Slip';

      let pageStyles = '';
      if (format === 'thermal-80mm') {
        pageStyles = `
          @page { size: 80mm auto; margin: 2mm 3mm; }
          html, body { margin: 0; padding: 0; width: 80mm; background: #fff; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; color: #000; }
          .claim-print-wrapper { width: 78mm; margin: 0 auto; padding: 2px 0; }
        `;
      } else if (format === 'thermal-60mm') {
        pageStyles = `
          @page { size: 58mm auto; margin: 1mm 1mm; }
          html, body { margin: 0; padding: 0; width: 58mm; background: #fff; font-family: 'Courier New', Courier, monospace, Arial; font-size: 9.5px; color: #000; }
          .claim-print-wrapper { width: 56mm; margin: 0 auto; padding: 1px 0; }
        `;
      } else if (format === 'normal-a5') {
        pageStyles = `
          @page { size: 148mm 210mm; margin: 5mm 6mm; }
          html, body { margin: 0; padding: 0; width: 148mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10.5px; color: #111; }
          .claim-print-wrapper { width: 138mm; max-width: 138mm; margin: 0 auto; }
        `;
      } else { // normal-a4
        pageStyles = `
          @page { size: 210mm 297mm; margin: 10mm 12mm; }
          html, body { margin: 0; padding: 0; width: 210mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
          .claim-print-wrapper { width: 190mm; max-width: 190mm; margin: 0 auto; }
        `;
      }

      const WinPrint = window.open('', '', 'left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0');
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
      <head>
        <title>Warranty Claim - ${claimNo}</title>
        <meta charset="utf-8">
        <style>
          * { box-sizing: border-box; }
          ${pageStyles}
          @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          }
        </style>
      </head>
      <body>
        <div class="claim-print-wrapper">
          ${printContents.innerHTML}
        </div>
      </body>
      </html>`);
      WinPrint.document.close();
      WinPrint.focus();
      setTimeout(() => {
        WinPrint.print();
      }, 350);
    },
  },
  created() {
    this.loadClaim();
  },
};
</script>

<style scoped>
.warranty-claim-view {
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

.btn-theme {
  background-color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.btn-theme:hover {
  background-color: #1a3d61 !important;
  color: #ffffff !important;
}

.form-section-card {
  border-radius: 8px;
  overflow: hidden;
}

.tracker-card {
  border-radius: 8px;
}

.step-icon {
  width: 38px;
  height: 38px;
  font-size: 15px;
}

.pipeline-step.active .step-icon {
  box-shadow: 0 0 0 4px rgba(17, 44, 70, 0.2);
}

.info-table th {
  color: rgb(255, 255, 255) !important;
  font-weight: 700 !important;
  font-size: 13px !important;
  padding-left: 0;
}

.info-table td {
  font-size: 13px !important;
}

.status-pill-btn {
  font-size: 12px !important;
  padding: 3px 8px !important;
  font-weight: 600 !important;
  border-radius: 20px !important;
  transition: all 0.15s ease-in-out;
}

.status-pill-btn:hover {
  transform: translateY(-1px);
}

/* Modern Compact Timeline */
.modern-timeline {
  max-height: 520px;
  overflow-y: auto;
  padding-right: 6px;
}

.modern-timeline::-webkit-scrollbar {
  width: 5px;
}

.modern-timeline::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.modern-timeline::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.modern-timeline-item {
  padding-left: 2px;
}

.timeline-v-line {
  position: absolute;
  top: 24px;
  left: 13px;
  bottom: 0;
  width: 2px;
  background-color: #e2e8f0;
  z-index: 1;
}

.timeline-node {
  position: absolute;
  top: 0;
  left: 0;
  width: 28px;
  height: 28px;
  font-size: 12px;
  z-index: 2;
}

.timeline-content-card {
  background-color: #ffffff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.timeline-content-card:hover {
  border-color: rgba(17, 44, 70, 0.3) !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.is-latest .timeline-content-card {
  border-color: rgba(17, 44, 70, 0.35) !important;
  box-shadow: 0 2px 6px rgba(17, 44, 70, 0.08);
}

.timeline-remarks-text {
  font-size: 12.5px;
  line-height: 1.4;
}
</style>
