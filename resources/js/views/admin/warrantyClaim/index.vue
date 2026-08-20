<template>
  <div class="container-fluid p-3 warranty-claim-index">
    <!-- 🌟 Top Header Bar: Title, Search, Advance Filter Toggle & Actions -->
    <div class="card border-0 shadow-sm mb-2">
      <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Left: Page Title -->
          <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark text-nowrap d-flex align-items-center gap-2">
              <i class="fas fa-shield-alt theme-text"></i>
              <span>Warranty Claims (ওয়ারেন্টি ক্লেইম)</span>
            </h5>
            <span class="badge bg-secondary font-monospace">{{ table.meta.total || 0 }}</span>
          </div>

          <!-- Center: Default Quick Search Bar -->
          <div class="flex-grow-1 mx-md-3" style="max-width: 480px;">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Search Serial, Claim No, Mobile, Customer, Product... (Press Enter)"
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

          <!-- Right: Advance Filter Toggle & Action Buttons -->
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
              v-if="table.datas && table.datas.length > 0"
              class="btn btn-sm btn-outline-success cursor-pointer"
              :data="exportData"
              :fields="exportFields"
              name="warranty_claims.xls"
              title="Export to Excel"
            >
              <i class="fas fa-file-excel"></i>
            </download-excel>

            <!-- Print Table -->
            <button
              type="button"
              class="btn btn-sm btn-outline-dark"
              @click="printTable"
              title="Print Table"
            >
              <i class="fas fa-print"></i>
            </button>

            <!-- New Claim Button -->
            <router-link :to="{ name: 'warrantyClaim.create' }" class="btn-theme-action shadow-sm" title="New Warranty Claim">
              <span>
                <i class="fas fa-plus-circle"></i>
              </span>
              New Claim
            </router-link>
          </div>
        </div>

        <!-- 📂 Collapsible Advance Filter Drawer Panel -->
        <div v-show="showAdvanced" class="mt-2 pt-2 border-top advance-filter-panel transition-all">
          <div class="row g-2 align-items-end">
            <!-- Specific Serial Search -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Serial / IMEI No</label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="Serial No..." v-model="search_data.serial_no" @keyup.enter="search">
            </div>

            <!-- Specific Claim No -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Claim Ticket No</label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="CLM-..." v-model="search_data.claim_no" @keyup.enter="search">
            </div>

            <!-- Claim Status Filter -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Claim Status</label>
              <select class="form-select form-select-sm" v-model="search_data.current_status" @change="search">
                <option value="">-- All Statuses --</option>
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

            <!-- Coverage Type Filter -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Coverage Type</label>
              <select class="form-select form-select-sm" v-model="search_data.warranty_type" @change="search">
                <option value="">-- All Types --</option>
                <option value="warranty">Warranty (ওয়ারেন্টি)</option>
                <option value="guarantee">Guarantee (গ্যারান্টি)</option>
              </select>
            </div>

            <!-- From Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">From Date</label>
              <input type="date" class="form-control form-control-sm" v-model="search_data.from_date" @change="search">
            </div>

            <!-- To Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">To Date</label>
              <input type="date" class="form-control form-control-sm" v-model="search_data.to_date" @change="search">
            </div>

            <!-- Action Buttons in Filter -->
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
              <button type="button" class="btn btn-sm theme_search_btn px-4 fw-bold" @click="search">
                <i class="fas fa-filter me-1"></i> Apply Filter
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary px-3" @click="resetSearch">
                <i class="fas fa-undo me-1"></i> Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📋 Claims List Table Card (No Visible Actions Column Header - Row Hover Floating Action Menu) -->
    <div class="card border-0 shadow-sm mb-2 table-card">
      <div class="card-body p-0 table-responsive" id="warrantyTablePrintArea" style="min-height: 420px; max-height: calc(100vh - 230px); overflow-y: auto;">
        <table class="table table-hover table-sm align-middle mb-0 claims-table">
          <thead class="sticky-top" style="z-index: 2;">
            <tr>
              <th class="text-center" width="4%">#</th>
              <th width="15%">Claim Ticket</th>
              <th width="12%">Dates</th>
              <th width="23%">Product & Serial</th>
              <th width="18%">Customer</th>
              <th width="13%">Coverage Policy</th>
              <th width="15%" class="text-center">Tracking Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(claim, idx) in table.datas"
              :key="claim.id || idx"
              class="claim-table-row"
            >
              <td class="text-center fw-bold text-secondary font-monospace">{{ (table.meta.from || 1) + idx }}</td>
              <td>
                <router-link :to="{ name: 'warrantyClaim.show', params: { id: claim.id } }" class="fw-bold font-monospace theme-text text-decoration-none d-block" title="Click to View Details">
                  <i class="fas fa-ticket-alt me-1 text-warning"></i>
                  <span>{{ claim.claim_no }}</span>
                </router-link>
                <small class="text-secondary font-monospace" v-if="claim.invoice">
                  <span>Inv: #{{ claim.invoice.invoice_no || claim.invoice.invoiceno }}</span>
                </small>
              </td>
              <td>
                <div class="fw-bold text-dark font-monospace" style="font-size: 12px;">{{ claim.claim_date }}</div>
                <small class="text-secondary font-monospace d-block" v-if="claim.expected_delivery_date" style="font-size: 11px;">
                  <span>Exp: {{ claim.expected_delivery_date }}</span>
                </small>
              </td>
              <td>
                <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" :title="claim.item ? claim.item.title : 'Item'">
                  {{ claim.item ? claim.item.title : 'Item' }}
                </div>
                <div class="badge theme-bg text-white font-monospace mt-1 px-2 py-0.5" v-if="claim.serial_no">
                  <i class="fas fa-barcode me-1"></i><span>{{ claim.serial_no }}</span>
                </div>
                <small class="text-secondary d-block mt-0.5" v-if="claim.color || claim.size" style="font-size: 11px;">
                  {{ claim.color ? claim.color.title : '' }} {{ claim.size ? '(' + claim.size.title + ')' : '' }}
                </small>
              </td>
              <td>
                <div class="fw-bold text-dark">{{ claim.customer_name || 'Walk-in Customer' }}</div>
                <div class="small font-monospace theme-text fw-semibold">
                  <i class="fas fa-phone-alt me-1 text-secondary"></i>
                  <span>{{ claim.customer_mobile || 'N/A' }}</span>
                </div>
              </td>
              <td>
                <span class="badge" :class="claim.warranty_type === 'guarantee' ? 'bg-success' : 'theme-bg text-white'">
                  {{ claim.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}
                </span>
                <small class="text-secondary d-block mt-1 font-monospace" style="font-size: 11px;">{{ claim.warranty_period || '' }}</small>
                <small class="text-danger fw-bold font-monospace d-block" v-if="claim.warranty_expiry_date" style="font-size: 11px;">
                  <span>Exp: {{ claim.warranty_expiry_date }}</span>
                </small>
              </td>
              <td class="text-center position-relative">
                <span class="badge px-2 py-1 rounded-pill shadow-xs" :class="getStatusBadgeClass(claim.current_status)">
                  {{ formatStatusLabel(claim.current_status) }}
                </span>

                <!-- ⭐️ Seamless Floating Row Hover Action Buttons (Appears On Hover) -->
                <div class="hover-floating-actions">
                  <div class="btn-group btn-group-sm shadow-sm bg-white border rounded px-1 py-1">
                    <!-- View Details -->
                    <router-link
                      :to="{ name: 'warrantyClaim.show', params: { id: claim.id } }"
                      class="btn btn-xs btn-outline-primary border-0"
                      title="View Claim Details & Tracking History"
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

                    <!-- Delete Claim -->
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-danger border-0"
                      @click="deleteClaim(claim.id)"
                      title="Delete Claim"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </td>
            </tr>

            <!-- Empty / No Records State -->
            <tr v-if="!loading && (!table.datas || table.datas.length === 0)">
              <td colspan="7" class="text-center py-5 text-secondary">
                <i class="fas fa-shield-alt fa-3x theme-text opacity-50 mb-3 d-block"></i>
                <h6 class="fw-bold text-dark">No Warranty Claims Found</h6>
                <p class="small text-muted mb-0">Search with different criteria or click 'New Claim' to register a warranty ticket.</p>
              </td>
            </tr>

            <!-- Loading Spinner State -->
            <tr v-if="loading">
              <td colspan="7" class="text-center py-5">
                <div class="spinner-border theme-text" role="status"></div>
                <div class="mt-2 small text-secondary fw-semibold">Loading warranty claims...</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ⭐️ Bottom Sleek Footer: KPI Summary & Standalone Pagination -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <!-- Summary count info on bottom left -->
          <div class="d-flex flex-wrap align-items-center gap-3" style="font-size: 12px;">
            <div class="d-flex align-items-center gap-1">
              <span class="text-muted fw-bold">Total Claims:</span>
              <span class="badge theme-bg text-white font-monospace">{{ table.meta.total || 0 }}</span>
            </div>
            <div class="small text-secondary font-monospace d-none d-md-inline" v-if="table.meta.total > 0">
              Showing {{ table.meta.from || 1 }} to {{ table.meta.to || table.datas.length }}
            </div>
          </div>

          <!-- Pagination & Per Page Selector on bottom right -->
          <div class="d-flex align-items-center gap-2" v-if="table.meta && table.meta.total > 0">
            <div class="d-flex align-items-center gap-1">
              <button
                type="button"
                class="btn btn-xs btn-outline-secondary py-1 px-2"
                :disabled="table.meta.current_page <= 1"
                @click="changePage(table.meta.current_page - 1)"
                title="Previous Page"
              >
                <i class="fas fa-chevron-left"></i>
              </button>

              <span class="small text-dark font-monospace px-1">
                {{ table.meta.current_page || 1 }} / {{ table.meta.last_page || 1 }}
              </span>

              <button
                type="button"
                class="btn btn-xs btn-outline-secondary py-1 px-2"
                :disabled="table.meta.current_page >= table.meta.last_page"
                @click="changePage(table.meta.current_page + 1)"
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
              <option :value="15">15</option>
              <option :value="30">30</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const model = 'warrantyClaim';

export default {
  name: 'WarrantyClaimIndex',
  data() {
    return {
      model: model,
      loading: false,
      showAdvanced: false,
      search_data: {
        keyword: '',
        pagination: 15,
        page: 1,
        claim_no: '',
        serial_no: '',
        customer_mobile: '',
        customer_name: '',
        current_status: '',
        warranty_type: '',
        from_date: '',
        to_date: '',
      },
      table: {
        datas: [],
        meta: {
          current_page: 1,
          last_page: 1,
          from: 0,
          to: 0,
          total: 0,
        },
      },
      exportFields: {
        'Claim No': 'claim_no',
        'Claim Date': 'claim_date',
        'Serial No': 'serial_no',
        'Product': 'product_title',
        'Customer Name': 'customer_name',
        'Customer Mobile': 'customer_mobile',
        'Coverage': 'warranty_type',
        'Warranty Period': 'warranty_period',
        'Expiry Date': 'warranty_expiry_date',
        'Status': 'current_status',
        'Service Cost': 'service_cost',
        'Customer Charge': 'customer_charge',
      },
    };
  },
  computed: {
    activeFilterCount() {
      let count = 0;
      if (this.search_data.serial_no) count++;
      if (this.search_data.claim_no) count++;
      if (this.search_data.current_status) count++;
      if (this.search_data.warranty_type) count++;
      if (this.search_data.from_date) count++;
      if (this.search_data.to_date) count++;
      return count;
    },
    exportData() {
      return (this.table.datas || []).map(c => ({
        claim_no: c.claim_no,
        claim_date: c.claim_date,
        serial_no: c.serial_no,
        product_title: c.item ? c.item.title : 'Item',
        customer_name: c.customer_name,
        customer_mobile: c.customer_mobile,
        warranty_type: c.warranty_type,
        warranty_period: c.warranty_period,
        warranty_expiry_date: c.warranty_expiry_date,
        current_status: this.formatStatusLabel(c.current_status),
        service_cost: c.service_cost,
        customer_charge: c.customer_charge,
      }));
    },
  },
  methods: {
    fetchClaims(page = 1) {
      this.loading = true;
      this.search_data.page = page;

      axios.get(this.model, { params: this.search_data })
        .then(res => {
          if (res.data && res.data.data) {
            this.table.datas = res.data.data;
            this.table.meta = res.data.meta || {
              current_page: page,
              last_page: 1,
              from: 1,
              to: res.data.data.length,
              total: res.data.data.length,
            };
          } else if (Array.isArray(res.data)) {
            this.table.datas = res.data;
            this.table.meta = {
              current_page: page,
              last_page: 1,
              from: 1,
              to: res.data.length,
              total: res.data.length,
            };
          }
        })
        .catch(err => {
          this.$toast('Failed to load warranty claims', 'error');
          console.error(err);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    search() {
      this.fetchClaims(1);
    },
    resetSearch() {
      this.search_data = {
        keyword: '',
        pagination: 15,
        page: 1,
        claim_no: '',
        serial_no: '',
        customer_mobile: '',
        customer_name: '',
        current_status: '',
        warranty_type: '',
        from_date: '',
        to_date: '',
      };
      this.fetchClaims(1);
    },
    changePage(page) {
      if (page >= 1 && page <= (this.table.meta.last_page || 1)) {
        this.fetchClaims(page);
      }
    },
    deleteClaim(id) {
      if (confirm('Are you sure you want to delete this warranty claim?')) {
        axios.delete(`${this.model}/${id}`)
          .then(() => {
            this.$toast('Claim deleted successfully', 'success');
            this.fetchClaims(this.search_data.page);
          })
          .catch(err => {
            this.$toast('Failed to delete claim', 'error');
          });
      }
    },
    printTable() {
      const printContents = document.getElementById('warrantyTablePrintArea');
      if (!printContents) return;

      const printWindow = window.open('', '', 'width=900,height=700');
      printWindow.document.write('<html><head><title>Warranty Claims Report</title>');
      printWindow.document.write('<link rel="stylesheet" href="/assets/backend/css/style.css">');
      printWindow.document.write('<style>body { padding: 20px; font-family: sans-serif; font-size: 12px; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 6px; text-align: left; } th { background-color: #112C47; color: #fff; }</style>');
      printWindow.document.write('</head><body>');
      printWindow.document.write('<h3 style="margin-bottom: 5px;">Warranty Claims List</h3>');
      printWindow.document.write(printContents.innerHTML);
      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 400);
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
    this.fetchClaims(1);
  },
};
</script>

<style scoped>
.warranty-claim-index {
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

.btn-theme-action {
  background-color: rgb(17, 44, 70);
  color: #ffffff;
  border-radius: 6px;
  padding: 4px 12px;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-theme-action:hover {
  background-color: #1a3d61;
  color: #ffffff;
  box-shadow: 0 2px 6px rgba(17, 44, 70, 0.3);
}

.filter-card, .table-card {
  border-radius: 8px;
  overflow: hidden;
}

.claims-table thead {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.claims-table thead th {
  color: #ffffff !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  padding: 8px 12px !important;
  border: none !important;
  background-color: rgb(17, 44, 70) !important;
}

.claims-table tbody td {
  padding: 8px 12px !important;
  font-size: 13px !important;
}

/* ⭐️ Seamless Floating Row Hover Action Buttons (No visible action column header) */
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
</style>
