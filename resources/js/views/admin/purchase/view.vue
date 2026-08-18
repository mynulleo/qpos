<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="purchase-view-wrapper">
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
                  <h4 class="fw-bold mb-0 text-white">Purchase #{{ data.invoiceno || data.id || 'N/A' }}</h4>
                  <span class="badge" :class="data.is_closed ? 'bg-success text-white' : 'bg-danger text-white'">
                    <i :class="data.is_closed ? 'fas fa-check-circle me-1' : 'fas fa-clock me-1'"></i>
                    {{ data.is_closed ? 'Closed / Settled' : 'Open / Due' }}
                  </span>
                  <span class="badge bg-light bg-opacity-25 text-white" v-if="hasAnySerials">
                    <i class="fas fa-microchip me-1"></i> Serialized Stock
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span><i class="far fa-calendar-alt me-1"></i>Date: <strong class="text-white">{{ data.purchase_date || 'N/A' }}</strong></span>
                  <span><i class="fas fa-store me-1"></i>Supplier: <strong class="text-white">{{ data.supplier?.org_name || data.supplier?.name || 'N/A' }}</strong></span>
                  <span><i class="fas fa-boxes me-1"></i>Total Items: <strong class="text-white">{{ data.purchase_details?.length || 0 }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2">
              <router-link :to="{ name: 'purchase.index' }" class="btn btn-outline-light btn-sm px-3 fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Back to List
              </router-link>
              <button type="button" class="btn btn-light btn-sm text-theme fw-bold px-3 shadow-sm" @click="printPurchaseVoucher">
                <i class="fas fa-print me-1"></i> Print Bill
              </button>
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
                <span class="text-muted fw-bold small text-uppercase">Sub Total</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-calculator"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace">৳ {{ formatNum(data.amount) }}</h4>
            </div>
          </div>
        </div>

        <!-- Discount -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-6">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Discount</span>
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
                <span class="text-muted fw-bold small text-uppercase">VAT / Tax</span>
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
                <span class="text-muted fw-bold small text-uppercase">Net Total Amount</span>
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
                <h6 class="fw-bold mb-0 text-dark">Supplier & Order Details</h6>
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
                    <td class="spec-label"><i class="fas fa-file-invoice me-2 text-muted"></i>Challan / Invoice No</td>
                    <td class="spec-value font-monospace fw-bold text-dark">{{ data.invoiceno || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="far fa-calendar-check me-2 text-muted"></i>Purchase Date</td>
                    <td class="spec-value font-monospace">{{ data.purchase_date || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="spec-label"><i class="fas fa-toggle-on me-2 text-muted"></i>Payment Status</td>
                    <td class="spec-value">
                      <span class="badge" :class="data.is_closed ? 'bg-success' : 'bg-danger'">
                        {{ data.is_closed ? 'Settled / Closed' : 'Open / Due' }}
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

        <!-- Purchased Items & Serial Numbers Table (8-col) -->
        <div class="col-xl-8 col-lg-12">
          <div class="card border-0 shadow-sm h-100 table-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">Purchased Products & Line Items</h6>
                  <small class="text-muted" style="font-size: 11px;">Breakdown of quantities, unit costs, selling prices and serial numbers</small>
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
                      <th class="text-center" style="width: 45px;">#</th>
                      <th>Product Details</th>
                      <th>Category</th>
                      <th v-if="hasAnyVariants">Variant</th>
                      <th class="text-center">Qty</th>
                      <th class="text-end">Cost Price</th>
                      <th class="text-end">Selling Price</th>
                      <th class="text-center" v-if="isElectronicsShop || hasAnySerials">Serial Numbers</th>
                      <th class="text-end" style="width: 120px;">Total Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(pdetail, index) in data.purchase_details" :key="index">
                      <td class="text-center font-monospace text-muted">{{ index + 1 }}</td>
                      <td>
                        <div class="fw-bold text-dark">{{ pdetail.item?.title || 'Unknown Product' }}</div>
                        <small class="text-muted font-monospace" style="font-size: 11px;">{{ pdetail.item?.barcode || '' }}</small>
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
                          <span class="text-muted small font-monospace">-</span>
                        </template>
                      </td>

                      <td class="text-end font-monospace fw-bold text-theme">
                        ৳ {{ formatNum(pdetail.total_amount) }}
                      </td>
                    </tr>

                    <tr v-if="!data.purchase_details || data.purchase_details.length === 0">
                      <td :colspan="hasAnyVariants ? 9 : 8" class="text-center py-4 text-muted">
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
    isElectronicsShop() {
      return this.$root.site_setting?.shop_type === 'electronics' || this.data?.shop_type === 'electronics';
    },
    hasAnyVariants() {
      return this.data.purchase_details?.some(d => d.color_id || d.size_id || d.color?.title || d.size?.title);
    },
    hasAnySerials() {
      return this.data.purchase_details?.some(d => Boolean(d.serial_no && String(d.serial_no).trim()));
    },
    totalQty() {
      if (!this.data.purchase_details) return 0;
      return this.data.purchase_details.reduce((sum, d) => sum + (parseFloat(d.qty) || 0), 0);
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
      window.print();
    }
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

@media print {
  .purchase-hero-banner,
  .btn,
  .modal {
    display: none !important;
  }
}
</style>