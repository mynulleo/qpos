<template>
  <div class="quotation-view-wrapper">
    <!-- 🖨️ Top Action Bar (Hidden on Print) -->
    <div class="col-12 mb-3 d-print-none">
      <div class="card shadow-sm border-0 bg-white">
        <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <router-link :to="{ name: 'quotation.index' }" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
              <i class="fas fa-arrow-left"></i> Back to List
            </router-link>
            <span class="badge font-monospace px-3 py-2 fs-6" :class="statusBadgeClass(data.status)">
              <i class="fas fa-circle me-1 small"></i> Status: {{ (data.status || 'draft').toUpperCase() }}
            </span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <!-- Quick Status Changer -->
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-dark dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-sync-alt"></i> Change Status
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="#" @click.prevent="updateStatus('draft')"><i class="fas fa-file text-secondary me-2"></i>Draft</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="updateStatus('sent')"><i class="fas fa-paper-plane text-primary me-2"></i>Sent</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="updateStatus('accepted')"><i class="fas fa-check-circle text-success me-2"></i>Accepted</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="updateStatus('declined')"><i class="fas fa-times-circle text-danger me-2"></i>Declined</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="updateStatus('converted')"><i class="fas fa-file-invoice text-info me-2"></i>Converted to Invoice</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="updateStatus('expired')"><i class="fas fa-clock text-warning me-2"></i>Expired</a></li>
              </ul>
            </div>

            <router-link :to="{ name: 'quotation.edit', params: { id: data.id } }" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" v-if="data.id">
              <i class="fas fa-edit"></i> Edit
            </router-link>

            <button type="button" class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1 shadow-sm px-3" @click="printQuotation">
              <i class="fas fa-print"></i> Print Quotation
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 📄 Printable Quotation A4 Page Canvas -->
    <div class="quotation-print-sheet shadow bg-white p-4 p-md-5 mx-auto" id="printArea">
      <!-- 1. Corporate Header Section -->
      <div class="row align-items-center pb-4 mb-4 border-bottom border-2 header-section">
        <div class="col-7">
          <div class="d-flex align-items-center gap-3">
            <img v-if="$root.site && $root.site.logo" :src="$root.site.logo" alt="Logo" class="company-logo" />
            <div>
              <h2 class="fw-bold mb-0 company-title" style="color: #112C47;">{{ $root.site ? $root.site.title : 'QPOS ERP' }}</h2>
              <p class="text-muted small mb-0 mt-1" v-if="$root.site && $root.site.address">
                <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $root.site.address }}
              </p>
              <p class="text-muted small mb-0" v-if="$root.site">
                <span v-if="$root.site.mobile1"><i class="fas fa-phone-alt me-1 text-success"></i> {{ $root.site.mobile1 }}</span>
                <span v-if="$root.site.contact_email" class="ms-2"><i class="fas fa-envelope me-1 text-primary"></i> {{ $root.site.contact_email }}</span>
                <span v-if="$root.site.web" class="ms-2"><i class="fas fa-globe me-1 text-info"></i> {{ $root.site.web }}</span>
              </p>
            </div>
          </div>
        </div>

        <div class="col-5 text-end">
          <h2 class="fw-bold text-uppercase tracking-wide mb-1 document-title" style="color: #112C47;">QUOTATION</h2>
          <span class="badge border px-3 py-1 font-monospace small" style="background-color: #f1f5f9; color: #112C47; border-color: #cbd5e1 !important;">
            OFFICIAL PROPOSAL
          </span>
        </div>
      </div>

      <!-- 2. Quotation Info & Customer Meta Blocks -->
      <div class="row g-3 mb-4">
        <!-- 2.1 Bill To / Client Info -->
        <div class="col-6">
          <div class="p-3 bg-light rounded-3 border h-100 client-info-box" style="border-left: 4px solid #112C47 !important;">
            <div class="text-uppercase fw-bold small mb-2 border-bottom pb-1" style="color: #112C47;">
              <i class="fas fa-user-tie me-1"></i> Quotation For / Customer Info:
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ data.client_name || (data.client ? data.client.org_name || data.client.name : 'Walk-in Customer') }}</h5>
            <div class="small text-muted mb-1" v-if="data.client && data.client.name && data.client.org_name">
              <strong>Attn:</strong> {{ data.client.name }}
            </div>
            <div class="small text-muted mb-1" v-if="data.client_phone || (data.client && data.client.mobile)">
              <i class="fas fa-phone-alt me-1 text-primary"></i> {{ data.client_phone || (data.client ? data.client.mobile : '') }}
            </div>
            <div class="small text-muted mb-1" v-if="data.client_email || (data.client && data.client.email)">
              <i class="fas fa-envelope me-1 text-primary"></i> {{ data.client_email || (data.client ? data.client.email : '') }}
            </div>
            <div class="small text-muted" v-if="data.client_address || (data.client && data.client.address)">
              <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ data.client_address || (data.client ? data.client.address : '') }}
            </div>
          </div>
        </div>

        <!-- 2.2 Quotation Meta Details -->
        <div class="col-6">
          <div class="p-3 bg-light rounded-3 border h-100 meta-info-box" style="border-left: 4px solid #112C47 !important;">
            <div class="text-uppercase fw-bold small mb-2 border-bottom pb-1" style="color: #112C47;">
              <i class="fas fa-info-circle me-1"></i> Quotation Details:
            </div>
            <table class="table table-sm table-borderless mb-0 small">
              <tbody>
                <tr>
                  <td class="fw-bold text-secondary" style="width: 45%;">Quotation No:</td>
                  <td class="text-end font-monospace fw-bold" style="color: #112C47;">{{ data.quotation_no || 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="fw-bold text-secondary">Quotation Date:</td>
                  <td class="text-end font-monospace text-dark fw-semibold">{{ formatDate(data.quotation_date) }}</td>
                </tr>
                <tr v-if="data.validity_date">
                  <td class="fw-bold text-secondary">Valid Until:</td>
                  <td class="text-end font-monospace text-danger fw-bold">{{ formatDate(data.validity_date) }}</td>
                </tr>
                <tr v-if="data.reference_no">
                  <td class="fw-bold text-secondary">Reference / RFQ:</td>
                  <td class="text-end font-monospace text-dark">{{ data.reference_no }}</td>
                </tr>
                <tr v-if="data.prepared_by && data.prepared_by.name">
                  <td class="fw-bold text-secondary">Prepared By:</td>
                  <td class="text-end text-dark">{{ data.prepared_by.full_name || data.prepared_by.name }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 3. Subject Banner -->
      <div class="alert bg-light border py-2 px-3 mb-4 rounded-3" style="border-left: 4px solid #112C47 !important;" v-if="data.subject">
        <strong style="color: #112C47;">Subject:</strong>
        <span class="text-dark fw-semibold ms-1">{{ data.subject }}</span>
      </div>

      <!-- 4. Quotation Line Items Table -->
      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle mb-0 quotation-table">
          <thead class="theme-table-header text-center small text-uppercase">
            <tr>
              <th style="width: 5%;">SL</th>
              <th style="width: 45%;" class="text-start">Item Description & Specifications</th>
              <th style="width: 10%;">Unit</th>
              <th style="width: 10%;">Qty</th>
              <th style="width: 15%;" class="text-end">Unit Price (৳)</th>
              <th style="width: 15%;" class="text-end">Total Amount (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data.quotation_details" :key="index">
              <td class="text-center font-monospace text-muted small fw-bold">{{ index + 1 }}</td>
              <td>
                <div class="fw-bold text-dark item-title">{{ item.item_name || (item.item ? item.item.title : 'N/A') }}</div>
                <div v-if="item.description" class="small text-muted mt-1 item-desc" style="white-space: pre-line;">
                  {{ item.description }}
                </div>
              </td>
              <td class="text-center small font-monospace">
                {{ item.unit_name || (item.unit ? item.unit.title : (item.item && item.item.unit ? item.item.unit.title : 'Pcs')) }}
              </td>
              <td class="text-center font-monospace fw-bold text-dark">{{ formatNumber(item.qty) }}</td>
              <td class="text-end font-monospace text-dark">{{ formatCurrency(item.unit_price) }}</td>
              <td class="text-end font-monospace fw-bold text-dark">{{ formatCurrency(item.total_price) }}</td>
            </tr>
          </tbody>
          <!-- 5. Financial Totals Footer Table -->
          <tfoot>
            <tr>
              <td colspan="4" rowspan="5" class="align-top p-3 bg-light border">
                <!-- 🌟 IN WORDS SECTION (Requirement 4) -->
                <div class="mb-3">
                  <div class="fw-bold text-uppercase small mb-1" style="color: #112C47;">
                    <i class="fas fa-money-bill-wave me-1 text-success"></i> Amount in Words (টাকায় কথায়):
                  </div>
                  <div class="p-2 bg-white rounded border fw-bold text-dark small font-monospace text-capitalize" style="border-color: #cbd5e1 !important;">
                    {{ $filter.numberToEnglishBD(data.total_amount) }}.
                  </div>
                </div>

                <!-- Special Note if any -->
                <div v-if="data.note" class="small text-muted">
                  <strong class="text-dark">Note:</strong> {{ data.note }}
                </div>
              </td>

              <!-- Sub Total -->
              <td class="text-end fw-bold text-secondary small">Sub Total (উপ-মোট):</td>
              <td class="text-end font-monospace fw-bold text-dark">{{ formatCurrency(data.sub_total) }}</td>
            </tr>

            <!-- Discount -->
            <tr v-if="Number(data.discount_amount) > 0">
              <td class="text-end fw-bold text-secondary small">
                Discount {{ data.discount_type === 'percentage' ? '(' + data.discount + '%)' : '' }}:
              </td>
              <td class="text-end font-monospace text-danger fw-bold">- {{ formatCurrency(data.discount_amount) }}</td>
            </tr>

            <!-- VAT / Tax -->
            <tr v-if="Number(data.tax_amount) > 0">
              <td class="text-end fw-bold text-secondary small">VAT / Tax ({{ data.tax_percent }}%):</td>
              <td class="text-end font-monospace text-dark fw-bold">+ {{ formatCurrency(data.tax_amount) }}</td>
            </tr>

            <!-- Shipping -->
            <tr v-if="Number(data.shipping_cost) > 0">
              <td class="text-end fw-bold text-secondary small">Shipping / Handling:</td>
              <td class="text-end font-monospace text-dark fw-bold">+ {{ formatCurrency(data.shipping_cost) }}</td>
            </tr>

            <!-- Grand Total (Dark Black Text with Theme Accent Border) -->
            <tr class="grand-total-row">
              <td class="text-end fw-bold text-uppercase fs-6 text-dark" style="color: #000000 !important;">Grand Total (সর্বমোট):</td>
              <td class="text-end font-monospace fw-bold fs-5 text-dark" style="color: #000000 !important;">৳ {{ formatCurrency(data.total_amount) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- 6. Commercial Terms & Conditions Block -->
      <div class="row g-3 mb-5 terms-section" v-if="data.payment_terms || data.delivery_terms || data.warranty_terms || data.terms_conditions">
        <div class="col-12">
          <div class="p-3 bg-light rounded-3 border" style="border-left: 4px solid #112C47 !important;">
            <h6 class="fw-bold text-uppercase small mb-2 border-bottom pb-1" style="color: #112C47;">
              <i class="fas fa-file-contract me-1"></i> Terms & Conditions (শর্তাবলী)
            </h6>
            <div class="row g-2 small text-dark">
              <div class="col-md-6" v-if="data.payment_terms">
                <strong>• Payment Terms:</strong> {{ data.payment_terms }}
              </div>
              <div class="col-md-6" v-if="data.delivery_terms">
                <strong>• Delivery Terms:</strong> {{ data.delivery_terms }}
              </div>
              <div class="col-md-6" v-if="data.warranty_terms">
                <strong>• Warranty Terms:</strong> {{ data.warranty_terms }}
              </div>
              <div class="col-12 mt-2" v-if="data.terms_conditions">
                <div style="white-space: pre-line;">{{ data.terms_conditions }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. 🌟 PHYSICAL DUAL SIGNATURE BLOCKS (Requirement 5) -->
      <div class="row pt-5 mt-5 signature-container">
        <!-- 7.1 Left: Client Acceptance / Receiver Signature -->
        <div class="col-6">
          <div class="signature-box text-start">
            <div class="signature-line mb-2"></div>
            <div class="fw-bold text-dark">Client Acceptance / Receiver Signature</div>
            <div class="text-muted small">Name: _______________________________</div>
            <div class="text-muted small mt-1">Designation: ________________________</div>
            <div class="text-muted small mt-1">Date & Seal: ________________________</div>
          </div>
        </div>

        <!-- 7.2 Right: Authorised Signatory -->
        <div class="col-6 text-end">
          <div class="signature-box text-end d-inline-block">
            <div class="signature-line mb-2"></div>
            <div class="fw-bold text-dark">Authorised Signature</div>
            <div class="text-dark small fw-semibold">For: {{ $root.site ? $root.site.title : 'QPOS ERP' }}</div>
            <div class="text-muted small mt-1">Official Company Seal</div>
          </div>
        </div>
      </div>

      <!-- 8. Document Footer -->
      <div class="text-center text-muted small mt-5 pt-3 border-top border-1">
        <em>This quotation is computer generated by {{ $root.site ? $root.site.title : 'QPOS' }}. Thank you for your business!</em>
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
        terms_conditions: "",
        note: "",
        quotation_details: [],
      },
    };
  },

  methods: {
    formatDate(val) {
      if (!val) return "N/A";
      return moment(val).format("D MMMM, YYYY");
    },

    formatNumber(val) {
      const num = Number(val || 0);
      return num.toLocaleString();
    },

    formatCurrency(val) {
      const num = Number(val || 0);
      return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
.quotation-print-sheet {
  max-width: 900px;
  min-height: 1050px;
  border-radius: 8px;
}

.company-logo {
  max-height: 65px;
  max-width: 140px;
  object-fit: contain;
}

.tracking-wide {
  letter-spacing: 0.08em;
}

.quotation-table th,
.quotation-table td {
  padding: 8px 10px;
}

.theme-table-header th {
  background-color: #112C47 !important;
  color: #ffffff !important;
  border-color: #112C47 !important;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.grand-total-row {
  background-color: #f1f5f9 !important;
  border-top: 2px solid #112C47 !important;
  border-bottom: 2px solid #112C47 !important;
}

.grand-total-row td {
  color: #000000 !important;
  font-weight: 700 !important;
}

.signature-line {
  width: 220px;
  border-top: 2px solid #333;
}

@media print {
  body {
    background-color: #fff !important;
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
  }

  .quotation-print-sheet {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
  }

  .theme-table-header th {
    background-color: #112C47 !important;
    color: #ffffff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .grand-total-row {
    background-color: #f1f5f9 !important;
    border-top: 2px solid #112C47 !important;
    border-bottom: 2px solid #112C47 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .grand-total-row td {
    color: #000000 !important;
  }

  .signature-container {
    page-break-inside: avoid;
  }
}
</style>
