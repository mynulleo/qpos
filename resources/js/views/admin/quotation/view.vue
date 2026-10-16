<template>
  <div class="quotation-view-wrapper w-100">
    <!-- 🖨️ Top Action Bar (Hidden on Print) -->
    <div class="card shadow-sm border-0 bg-white mb-3 d-print-none action-bar-card">
      <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- Left: Back button & Quotation Identifier -->
        <div class="d-flex align-items-center gap-3">
          <router-link :to="{ name: 'quotation.index' }" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs">
            <i class="fas fa-arrow-left"></i> {{ $t('Back to List') }}
          </router-link>
          
          <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark fs-6 font-monospace">{{ data.quotation_no || 'Quotation' }}</span>
            <span class="badge rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 font-monospace text-uppercase" :class="statusBadgeClass(data.status)">
              <span class="status-pulse-dot" :class="'dot-' + (data.status || 'draft')"></span>
              {{ $t(data.status || 'draft') }}
            </span>
          </div>
        </div>

        <!-- Right: Actions -->
        <div class="d-flex align-items-center gap-2">
          <!-- Quick Status Changer -->
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-dark dropdown-toggle d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-sync-alt text-primary"></i> {{ $t('Change Status') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2">
              <li class="dropdown-header text-uppercase small text-muted px-3">{{ $t('Select Status') }}</li>
              <li><a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2" href="#" @click.prevent="updateStatus('draft')"><i class="fas fa-file text-secondary"></i> {{ $t('Draft') }}</a></li>
              <li><a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2" href="#" @click.prevent="updateStatus('sent')"><i class="fas fa-paper-plane text-primary"></i> {{ $t('Sent') }}</a></li>
              <li><a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2" href="#" @click.prevent="updateStatus('accepted')"><i class="fas fa-check-circle text-success"></i> {{ $t('Accepted') }}</a></li>
              <li><a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2" href="#" @click.prevent="updateStatus('declined')"><i class="fas fa-times-circle text-danger"></i> {{ $t('Declined') }}</a></li>
              <li><a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2" href="#" @click.prevent="updateStatus('converted')"><i class="fas fa-file-invoice text-info"></i> {{ $t('Converted to Invoice') }}</a></li>
              <li><a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2" href="#" @click.prevent="updateStatus('expired')"><i class="fas fa-clock text-warning"></i> {{ $t('Expired') }}</a></li>
            </ul>
          </div>

          <router-link :to="{ name: 'quotation.edit', params: { id: data.id } }" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-xs" v-if="data.id">
            <i class="fas fa-edit"></i> {{ $t('Edit Quotation') }}
          </router-link>

          <button type="button" class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-sm" @click="printQuotation">
            <i class="fas fa-print"></i> {{ $t('Print Quotation') }}
          </button>
        </div>
      </div>
    </div>

    <!-- 📄 Printable Quotation A4 Page Canvas (Executive Layout) -->
    <div class="quotation-print-sheet" id="printArea">
      <!-- 🎨 Executive Top Accent Line -->
      <div class="executive-top-bar"></div>

      <div class="quotation-sheet-inner">
        <!-- 🔺 TOP & MIDDLE CONTENT -->
        <div class="quotation-top-content">
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
              <h3 class="fw-bold text-uppercase tracking-wide mb-0 document-title" style="color: #112C47;">{{ $t('Quotation') }}</h3>
              <span class="badge border px-2.5 py-1 font-monospace small official-badge" style="background-color: #f1f5f9; color: #112C47; border-color: #cbd5e1 !important; font-size: 10px;">
                {{ $t('OFFICIAL PROPOSAL') }}
              </span>
            </div>
          </div>

          <!-- 2. Quotation Info & Customer Meta Blocks (Executive Card Style) -->
          <div class="row g-2 mb-2 customer-meta-row">
            <!-- 2.1 Bill To / Client Info -->
            <div class="col-6">
              <div class="client-info-box h-100 rounded-2 border">
                <div class="box-header-title d-flex align-items-center justify-content-between px-2.5 py-1 border-bottom">
                  <span class="d-flex align-items-center gap-1.5" style="color: #112C47;">
                    <i class="fas fa-user-tie text-primary"></i>
                    <span class="fw-bold">{{ $t('QUOTATION FOR') }}</span>
                  </span>
                  <span class="text-muted small fw-normal">{{ $t('CLIENT DETAILS') }}</span>
                </div>
                <div class="p-2">
                  <div class="fw-bold text-dark client-display-name">{{ data.client_name || (data.client ? data.client.org_name || data.client.name : $t('Walk-in Customer')) }}</div>
                  <div class="client-meta-line" v-if="data.client && data.client.name && data.client.org_name">
                    <span class="text-muted">{{ $t('Attn') }}:</span> <strong class="text-dark">{{ data.client.name }}</strong>
                  </div>
                  <div class="client-meta-line" v-if="data.client_phone || (data.client && data.client.mobile)">
                    <i class="fas fa-phone-alt me-1 text-success"></i> 
                    <span>{{ data.client_phone || (data.client ? data.client.mobile : '') }}</span>
                  </div>
                  <div class="client-meta-line" v-if="data.client_email || (data.client && data.client.email)">
                    <i class="fas fa-envelope me-1 text-primary"></i> 
                    <span>{{ data.client_email || (data.client ? data.client.email : '') }}</span>
                  </div>
                  <div class="client-meta-line text-truncate" v-if="clientAddressText" :title="clientAddressText">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> 
                    <span>{{ clientAddressText }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2.2 Quotation Meta Details -->
            <div class="col-6">
              <div class="meta-info-box h-100 rounded-2 border">
                <div class="box-header-title d-flex align-items-center justify-content-between px-2.5 py-1 border-bottom">
                  <span class="d-flex align-items-center gap-1.5" style="color: #112C47;">
                    <i class="fas fa-info-circle text-primary"></i>
                    <span class="fw-bold">{{ $t('QUOTATION DETAILS') }}</span>
                  </span>
                  <span class="text-muted small fw-normal">{{ $t('OFFICIAL REF') }}</span>
                </div>
                <div class="p-2">
                  <table class="table table-sm table-borderless mb-0 meta-table">
                    <tbody>
                      <tr>
                        <td class="text-secondary fw-semibold py-0">{{ $t('Quotation No') }}:</td>
                        <td class="text-end font-monospace fw-bold py-0" style="color: #112C47;">{{ data.quotation_no || 'N/A' }}</td>
                      </tr>
                      <tr>
                        <td class="text-secondary fw-semibold py-0">{{ $t('Quotation Date') }}:</td>
                        <td class="text-end font-monospace text-dark fw-semibold py-0">{{ formatDate(data.quotation_date) }}</td>
                      </tr>
                      <tr v-if="data.validity_date">
                        <td class="text-secondary fw-semibold py-0">{{ $t('Valid Until') }}:</td>
                        <td class="text-end font-monospace text-danger fw-bold py-0">{{ formatDate(data.validity_date) }}</td>
                      </tr>
                      <tr v-if="data.reference_no">
                        <td class="text-secondary fw-semibold py-0">{{ $t('Reference / RFQ') }}:</td>
                        <td class="text-end font-monospace text-dark py-0">{{ data.reference_no }}</td>
                      </tr>
                      <tr v-if="data.customer_type">
                        <td class="text-secondary fw-semibold py-0">{{ $t('Customer Type') }}:</td>
                        <td class="text-end font-monospace text-dark text-capitalize py-0">
                          <span :class="data.customer_type === 'wholesale' ? 'badge bg-warning text-dark' : 'badge bg-primary text-white'" style="font-size: 9px; padding: 1px 5px;">
                            {{ data.customer_type }}
                          </span>
                        </td>
                      </tr>
                      <tr v-if="data.prepared_by && data.prepared_by.name">
                        <td class="text-secondary fw-semibold py-0">{{ $t('Prepared By') }}:</td>
                        <td class="text-end text-dark py-0">{{ data.prepared_by.full_name || data.prepared_by.name }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Subject Banner -->
          <div class="subject-banner rounded-2 border mb-2 px-2.5 py-1 d-flex align-items-center" v-if="data.subject">
            <span class="badge border text-uppercase me-2 subject-badge" style="background-color: #112C47; color: #ffffff;">
              {{ $t('Subject') }}
            </span>
            <span class="text-dark fw-semibold subject-text">{{ data.subject }}</span>
          </div>

          <!-- 4. Quotation Line Items Table -->
          <div class="table-responsive mb-2 quotation-table-wrapper">
            <table class="table table-sm table-bordered align-middle mb-0 quotation-table">
              <thead class="theme-table-header text-center small text-uppercase">
                <tr>
                  <th style="width: 5%;">{{ $t('SL') }}</th>
                  <th style="width: 47%;" class="text-start">{{ $t('Item Description & Specifications') }}</th>
                  <th style="width: 9%;">{{ $t('Unit') }}</th>
                  <th style="width: 9%;">{{ $t('Qty') }}</th>
                  <th style="width: 15%;" class="text-end">{{ $t('Unit Price') }}</th>
                  <th style="width: 15%;" class="text-end">{{ $t('Total Amount') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.quotation_details" :key="index" class="quotation-item-row" :class="{ 'row-even': index % 2 === 1 }">
                  <td class="text-center font-monospace text-muted fw-bold">{{ $bnNum(index + 1) }}</td>
                  <td>
                    <div class="fw-bold text-dark item-title">{{ item.item_name || (item.item ? item.item.title : 'N/A') }}</div>
                    <div v-if="item.description" class="text-muted item-desc" style="white-space: pre-line;">
                      {{ item.description }}
                    </div>
                  </td>
                  <td class="text-center font-monospace item-unit">
                    {{ item.unit_name || (item.unit ? item.unit.title : (item.item && item.item.unit ? item.item.unit.title : $t('Pcs'))) }}
                  </td>
                  <td class="text-center font-monospace fw-bold text-dark item-qty">{{ formatNumber(item.qty) }}</td>
                  <td class="text-end font-monospace text-dark item-price">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="text-end font-monospace fw-bold text-dark item-total">{{ formatCurrency(item.total_price) }}</td>
                </tr>
              </tbody>
              <!-- 5. Financial Totals Footer Table -->
              <tfoot>
                <tr>
                  <td colspan="4" :rowspan="totalSummaryRowCount" class="align-top bg-light border inwords-cell">
                    <!-- 🌟 IN WORDS SECTION -->
                    <div class="inwords-wrapper">
                      <div class="fw-bold text-uppercase inwords-title d-flex align-items-center gap-1" style="color: #112C47;">
                        <i class="fas fa-coins text-warning"></i>
                        <span>{{ $t('Amount in Words') }}:</span>
                      </div>
                      <div class="inwords-box fw-bold text-dark font-monospace text-capitalize">
                        {{ $filter.numberToEnglishBD(data.total_amount) }}.
                      </div>
                    </div>

                    <!-- Special Note if any -->
                    <div v-if="data.note" class="quotation-note mt-1.5 p-1 px-1.5 rounded bg-white border">
                      <strong class="text-dark">{{ $t('Note') }}:</strong> {{ data.note }}
                    </div>
                  </td>

                  <!-- Sub Total -->
                  <td class="text-end fw-bold text-secondary summary-label">{{ $t('Sub Total') }}:</td>
                  <td class="text-end font-monospace fw-bold text-dark summary-val">{{ formatCurrency(data.sub_total) }}</td>
                </tr>

                <!-- Discount -->
                <tr v-if="Number(data.discount_amount) > 0">
                  <td class="text-end fw-bold text-secondary summary-label">
                    {{ $t('Discount') }} {{ data.discount_type === 'percentage' ? '(' + $bnNum(data.discount) + '%)' : '' }}:
                  </td>
                  <td class="text-end font-monospace text-danger fw-bold summary-val">- {{ formatCurrency(data.discount_amount) }}</td>
                </tr>

                <!-- VAT / Tax -->
                <tr v-if="Number(data.tax_amount) > 0">
                  <td class="text-end fw-bold text-secondary summary-label">{{ $t('VAT / Tax') }} ({{ $bnNum(data.tax_percent) }}%):</td>
                  <td class="text-end font-monospace text-dark fw-bold summary-val">+ {{ formatCurrency(data.tax_amount) }}</td>
                </tr>

                <!-- Shipping -->
                <tr v-if="Number(data.shipping_cost) > 0">
                  <td class="text-end fw-bold text-secondary summary-label">{{ $t('Shipping / Handling') }}:</td>
                  <td class="text-end font-monospace text-dark fw-bold summary-val">+ {{ formatCurrency(data.shipping_cost) }}</td>
                </tr>

                <!-- Grand Total -->
                <tr class="grand-total-row">
                  <td class="text-end fw-bold text-uppercase text-dark summary-label grand-label" style="color: #000000 !important;">{{ $t('Grand Total') }}:</td>
                  <td class="text-end font-monospace fw-bold text-dark summary-val grand-val" style="color: #000000 !important;">{{ currencySymbol }} {{ formatCurrency(data.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- 6. Commercial Terms & Conditions Block -->
          <div class="mb-2 terms-section" v-if="data.payment_terms || data.delivery_terms || data.warranty_terms || formattedTermsConditions.length > 0">
            <div class="terms-box rounded-2 border">
              <div class="box-header-title d-flex align-items-center justify-content-between px-2.5 py-1 border-bottom">
                <span class="d-flex align-items-center gap-1.5" style="color: #112C47;">
                  <i class="fas fa-file-contract text-primary"></i>
                  <span class="fw-bold">{{ $t('TERMS & CONDITIONS') }}</span>
                </span>
                <span class="text-muted small fw-normal">{{ $t('COMMERCIAL POLICIES') }}</span>
              </div>
              <div class="p-2">
                <div class="terms-grid text-dark">
                  <div class="term-item" v-if="data.payment_terms">
                    <span class="term-tag"><i class="fas fa-credit-card me-1 text-primary"></i>{{ $t('Payment') }}:</span>
                    <span class="term-val">{{ data.payment_terms }}</span>
                  </div>
                  <div class="term-item" v-if="data.delivery_terms">
                    <span class="term-tag"><i class="fas fa-truck me-1 text-info"></i>{{ $t('Delivery') }}:</span>
                    <span class="term-val">{{ data.delivery_terms }}</span>
                  </div>
                  <div class="term-item" v-if="data.warranty_terms">
                    <span class="term-tag"><i class="fas fa-shield-alt me-1 text-success"></i>{{ $t('Warranty') }}:</span>
                    <span class="term-val">{{ data.warranty_terms }}</span>
                  </div>
                </div>
                <div class="mt-1.5" v-if="formattedTermsConditions.length > 0">
                  <ul class="mb-0 ps-0 list-unstyled d-flex flex-wrap gap-2 terms-list">
                    <li v-for="(tc, tcIdx) in formattedTermsConditions" :key="tcIdx" class="d-inline-flex align-items-center gap-1">
                      <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 8.5px; padding: 1px 4px;">{{ tcIdx + 1 }}</span>
                      <span>{{ tc }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 🔻 BOTTOM CONTENT (Anchored to the page bottom in print) -->
        <div class="quotation-bottom-content">
          <!-- 7. Dual Signature Blocks -->
          <div class="row signature-container align-items-end">
            <!-- 7.1 Left: Client Acceptance / Receiver Signature -->
            <div class="col-6">
              <div class="signature-box text-start">
                <div class="signature-line mb-1"></div>
                <div class="fw-bold text-dark sig-title">{{ $t('Client Acceptance / Receiver Signature') }}</div>
                <div class="text-muted sig-sub">{{ $t('Name & Designation') }}: ______________________</div>
                <div class="text-muted sig-sub">{{ $t('Date & Seal') }}: ___________________________</div>
              </div>
            </div>

            <!-- 7.2 Right: Authorised Signatory -->
            <div class="col-6 text-end">
              <div class="signature-box text-end d-inline-block">
                <div class="signature-line mb-1 ms-auto"></div>
                <div class="fw-bold text-dark sig-title">{{ $t('Authorised Signature') }}</div>
                <div class="text-dark fw-semibold sig-sub">{{ $t('For') }}: {{ $root.site ? $root.site.title : 'QPOS ERP' }}</div>
                <div class="text-muted sig-sub">{{ $t('Official Company Seal') }}</div>
              </div>
            </div>
          </div>

          <!-- 8. Document Footer -->
          <div class="text-center text-muted quotation-footer">
            <em>{{ $t('This quotation is computer generated by') }} {{ $root.site ? $root.site.title : 'QPOS' }}. {{ $t('Thank you for your business!') }}</em>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import moment from "moment";

const model = "quotation";

export default {
  data() {
    return {
      model: model,
      page_title: "Quotation View",
      data: {
        quotation_no: "",
        quotation_date: "",
        validity_date: "",
        client_name: "",
        client_phone: "",
        client_email: "",
        client_address: "",
        subject: "",
        reference_no: "",
        status: "draft",
        sub_total: 0,
        discount_type: "fixed",
        discount: 0,
        discount_amount: 0,
        tax_percent: 0,
        tax_amount: 0,
        shipping_cost: 0,
        total_amount: 0,
        payment_terms: "",
        delivery_terms: "",
        warranty_terms: "",
        terms_conditions: [],
        note: "",
        quotation_details: [],
        currency: null,
      },
    };
  },

  computed: {
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

    currencySymbol() {
      if (this.data.currency && this.data.currency.symbol) {
        return this.data.currency.symbol;
      }
      return '৳';
    },

    clientAddressText() {
      const addr = this.data.client_address || (this.data.client ? this.data.client.address : "");
      if (!addr || addr.trim() === "" || addr.trim().toUpperCase() === "N/A") return "";
      return addr.trim();
    },

    totalSummaryRowCount() {
      let count = 2; // sub_total and total_amount
      if (Number(this.data.discount_amount) > 0) count++;
      if (Number(this.data.tax_amount) > 0) count++;
      if (Number(this.data.shipping_cost) > 0) count++;
      return count;
    },

    formattedTermsConditions() {
      if (!this.data.terms_conditions) return [];
      if (Array.isArray(this.data.terms_conditions)) {
        return this.data.terms_conditions.filter((t) => typeof t === "string" && t.trim() !== "");
      }
      if (typeof this.data.terms_conditions === "string") {
        try {
          const parsed = JSON.parse(this.data.terms_conditions);
          if (Array.isArray(parsed)) {
            return parsed.filter((t) => typeof t === "string" && t.trim() !== "");
          }
        } catch (e) {}
        return this.data.terms_conditions.split("\n").map((t) => t.trim()).filter(Boolean);
      }
      return [];
    },
  },

  methods: {
    formatDate(val) {
      if (!val) return "N/A";
      return moment(val).format("D MMMM, YYYY");
    },

    formatNumber(val) {
      const num = Number(val || 0);
      return this.$bnNum(num.toLocaleString());
    },

    formatCurrency(val) {
      const num = Number(val || 0);
      const str = num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      return this.$bnNum(str);
    },

    statusBadgeClass(status) {
      const map = {
        draft: "bg-secondary text-white",
        sent: "bg-primary text-white",
        accepted: "bg-success text-white",
        declined: "bg-danger text-white",
        converted: "bg-info text-dark",
        expired: "bg-warning text-dark",
      };
      return map[status] || "bg-secondary text-white";
    },

    loadQuotation(id) {
      this.$root.spinner = true;
      axios
        .get(`quotation/${id}`)
        .then((res) => {
          this.data = res.data;
          this.page_title = `Quotation: ${this.data.quotation_no || ''}`;
        })
        .catch((err) => {
          console.error("Error loading quotation:", err);
          this.$toast("Failed to load quotation", "error");
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },

    updateStatus(newStatus) {
      axios
        .post(`quotation/${this.data.id}/change-status`, { status: newStatus })
        .then((res) => {
          this.data.status = newStatus;
          this.$toast(res.data.message || `Status updated to ${newStatus}`, "success");
        })
        .catch((err) => {
          this.$toast("Failed to update status", "error");
        });
    },

    printQuotation() {
      window.print();
    },
  },

  created() {
    if (this.$route.params.id) {
      this.loadQuotation(this.$route.params.id);
    }
  },
};
</script>

<style scoped>
/* ==========================================================================
   Base Screen & Canvas Styling (Executive Document Layout)
   ========================================================================== */
.quotation-view-wrapper {
  width: 100%;
}

.action-bar-card {
  border-radius: 8px;
  background: #ffffff;
}

.status-pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}
.dot-draft { background-color: #cbd5e1; }
.dot-sent { background-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.35); }
.dot-accepted { background-color: #4ade80; box-shadow: 0 0 0 2px rgba(74, 222, 128, 0.35); }
.dot-declined { background-color: #f87171; }
.dot-converted { background-color: #22d3ee; }
.dot-expired { background-color: #facc15; }

/* The Official Quotation Paper Canvas on Screen */
.quotation-print-sheet {
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
}

/* 4px Executive Brand Accent Line */
.executive-top-bar {
  height: 4px;
  background: linear-gradient(90deg, #112C47 0%, #1e4976 60%, #0284c7 100%);
  width: 100%;
}

.quotation-sheet-inner {
  padding: 24px 28px;
  min-height: 850px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.quotation-top-content {
  flex: 1 0 auto;
  width: 100%;
}

.quotation-bottom-content {
  flex-shrink: 0;
  margin-top: auto;
  width: 100%;
}

/* Header */
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
  font-size: 1.4rem;
  letter-spacing: 0.06em;
  line-height: 1.1;
}

/* Info Cards */
.client-info-box,
.meta-info-box {
  background-color: #ffffff;
  border-color: #e2e8f0 !important;
  font-size: 11px;
  line-height: 1.35;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.box-header-title {
  background-color: #f8fafc;
  font-size: 10.5px;
  letter-spacing: 0.03em;
  border-bottom: 1px solid #e2e8f0 !important;
}

.client-display-name {
  font-size: 13px;
  line-height: 1.25;
  margin-bottom: 2px;
}

.client-meta-line {
  font-size: 11px;
  line-height: 1.35;
}

.meta-table td {
  padding: 1.5px 0 !important;
  font-size: 11px;
  line-height: 1.35;
}

/* Subject */
.subject-banner {
  background-color: #f8fafc;
  border-color: #e2e8f0 !important;
  border-left: 3px solid #112C47 !important;
  font-size: 11.5px;
}

.subject-badge {
  font-size: 9.5px;
  padding: 2px 6px;
  letter-spacing: 0.04em;
}

.subject-text {
  font-size: 11.5px;
}

/* Table */
.quotation-table {
  border-color: #cbd5e1 !important;
}

.quotation-table th {
  padding: 5px 6px !important;
  font-size: 11px;
  font-weight: 600;
  vertical-align: middle;
}

.quotation-table td {
  padding: 4px 6px !important;
  font-size: 11.5px;
  line-height: 1.3;
}

.theme-table-header th {
  background-color: #112C47 !important;
  color: #ffffff !important;
  border-color: #112C47 !important;
}

.row-even {
  background-color: #f8fafc;
}

.item-title {
  font-size: 11.5px;
  line-height: 1.25;
}

.item-desc {
  font-size: 10px;
  line-height: 1.2;
  margin-top: 1px;
}

/* In words & Summary */
.inwords-cell {
  background-color: #f8fafc !important;
  padding: 5px 8px !important;
}

.inwords-title {
  font-size: 10px;
  letter-spacing: 0.03em;
  margin-bottom: 2px;
}

.inwords-box {
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  padding: 3px 6px;
  border-radius: 4px;
  font-size: 10.5px;
  line-height: 1.3;
}

.quotation-note {
  font-size: 10px;
  line-height: 1.25;
}

.summary-label {
  font-size: 11px;
  padding: 3px 6px !important;
}

.summary-val {
  font-size: 11.5px;
  padding: 3px 6px !important;
}

.grand-total-row {
  background-color: #f1f5f9 !important;
  border-top: 2px solid #112C47 !important;
  border-bottom: 2px solid #112C47 !important;
}

.grand-label {
  font-size: 12px !important;
  font-weight: 700 !important;
  padding: 4px 6px !important;
}

.grand-val {
  font-size: 13px !important;
  font-weight: 800 !important;
  padding: 4px 6px !important;
}

/* Terms & Conditions */
.terms-box {
  background-color: #ffffff;
  border-color: #e2e8f0 !important;
}

.terms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 2px 12px;
  font-size: 10px;
  line-height: 1.3;
}

.term-tag {
  font-weight: 700;
  margin-right: 3px;
}

.terms-list {
  font-size: 9.5px;
  line-height: 1.3;
}

/* Signatures */
.signature-container {
  margin-top: 20px;
  padding-top: 6px;
}

.signature-line {
  width: 170px;
  border-top: 1.5px solid #334155;
}

.sig-title {
  font-size: 11px;
  line-height: 1.25;
}

.sig-sub {
  font-size: 9.5px;
  line-height: 1.3;
}

/* Footer */
.quotation-footer {
  margin-top: 10px;
  padding-top: 4px;
  font-size: 9.5px;
  border-top: 1px solid #e2e8f0;
}

/* ==========================================================================
   🖨️ PRINT MEDIA OPTIMIZATIONS (A4 Bottom-Anchored Signatures & Clean Fit)
   ========================================================================== */
@page {
  size: A4 portrait;
  margin: 8mm 10mm 8mm 10mm;
}

@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  body, html {
    background-color: #ffffff !important;
    margin: 0 !important;
    padding: 0 !important;
    height: 100% !important;
    font-size: 11px !important;
  }

  .d-print-none,
  .main-sidebar,
  .main-header,
  .footer,
  .nav-header {
    display: none !important;
  }

  .quotation-view-wrapper {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    min-height: 100% !important;
    background: transparent !important;
  }

  .quotation-print-sheet {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    border-radius: 0 !important;
    background: #ffffff !important;
  }

  .executive-top-bar {
    height: 3px !important;
    background: #112C47 !important;
  }

  /* 🌟 Push bottom content (signatures + footer) directly to page bottom */
  .quotation-sheet-inner {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    min-height: 270mm !important; /* A4 printable area with 8mm margins is ~281mm */
    width: 100% !important;
    padding: 0 !important;
    box-sizing: border-box !important;
  }

  .quotation-top-content {
    flex: 1 0 auto !important;
    width: 100% !important;
  }

  .quotation-bottom-content {
    flex-shrink: 0 !important;
    margin-top: auto !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    width: 100% !important;
  }

  .header-section {
    padding-bottom: 4px !important;
    margin-bottom: 6px !important;
    border-bottom: 1.5px solid #cbd5e1 !important;
  }

  .company-logo {
    max-height: 42px !important;
    max-width: 120px !important;
  }

  .customer-meta-row {
    margin-bottom: 6px !important;
  }

  .client-info-box,
  .meta-info-box {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-left: 3px solid #112C47 !important;
  }

  .box-header-title {
    background-color: #f1f5f9 !important;
    border-bottom: 1px solid #cbd5e1 !important;
  }

  .subject-banner {
    padding: 3px 8px !important;
    margin-bottom: 6px !important;
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    border-left: 3px solid #112C47 !important;
  }

  .table-responsive {
    overflow: visible !important;
    display: block !important;
    margin-bottom: 6px !important;
  }

  .quotation-table {
    border-collapse: collapse !important;
    width: 100% !important;
    page-break-inside: auto;
  }

  .theme-table-header th {
    background-color: #112C47 !important;
    color: #ffffff !important;
    border: 1px solid #112C47 !important;
    padding: 4px 5px !important;
    font-size: 10px !important;
  }

  .quotation-table td {
    padding: 3.5px 5px !important;
    font-size: 10.5px !important;
    border: 1px solid #cbd5e1 !important;
  }

  .row-even {
    background-color: #f8fafc !important;
  }

  .quotation-item-row {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .grand-total-row {
    background-color: #f1f5f9 !important;
    border-top: 2px solid #112C47 !important;
    border-bottom: 2px solid #112C47 !important;
  }

  .grand-label {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    padding: 3.5px 5px !important;
  }

  .grand-val {
    font-size: 12px !important;
    font-weight: 800 !important;
    padding: 3.5px 5px !important;
  }

  .inwords-cell {
    padding: 4px 6px !important;
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
  }

  .inwords-box {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    padding: 2px 5px !important;
    font-size: 9.5px !important;
  }

  .terms-section {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    margin-bottom: 6px !important;
  }

  .terms-box {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-left: 3px solid #112C47 !important;
  }

  .terms-grid {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 2px 14px !important;
    font-size: 9.5px !important;
  }

  .signature-container {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    margin-top: 14px !important;
    padding-top: 0 !important;
  }

  .signature-line {
    width: 160px !important;
    border-top: 1.5px solid #000000 !important;
    margin-bottom: 2px !important;
  }

  .sig-title {
    font-size: 10px !important;
    font-weight: 700 !important;
  }

  .sig-sub {
    font-size: 9px !important;
  }

  .quotation-footer {
    margin-top: 6px !important;
    padding-top: 2px !important;
    font-size: 8.5px !important;
  }
}
</style>
