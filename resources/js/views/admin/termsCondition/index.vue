<template>
  <div class="container-fluid p-3 terms-condition-page">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm mb-3 text-white" style="background-color: #112C47;">
      <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-white bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <i class="fas fa-file-contract fs-5 text-warning"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-bold text-white">Terms & Conditions Management (শর্তাবলী ব্যবস্থাপনা)</h5>
            <small class="text-white-50" style="font-size: 12px;">Configure default terms and conditions for Invoices, Purchase Orders, Warranty Claims, and Quotations</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm d-flex align-items-center gap-2 px-3" @click="openModal('create')">
            <i class="fas fa-plus"></i> Add New Condition (শর্ত যোগ করুন)
          </button>
        </div>
      </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body p-3">
        <div class="row g-2 align-items-center justify-content-between">
          <!-- Module Filter Tabs -->
          <div class="col-lg-8 col-md-12">
            <div class="d-flex flex-wrap gap-1">
              <button
                type="button"
                v-for="m in moduleOptions"
                :key="m.value"
                class="btn btn-sm font-monospace"
                :class="selectedModule === m.value ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                @click="filterByModule(m.value)"
              >
                <i :class="m.icon" class="me-1"></i> {{ m.label }}
                <span class="badge ms-1" :class="selectedModule === m.value ? 'bg-white text-dark' : 'bg-secondary'">
                  {{ getCountForModule(m.value) }}
                </span>
              </button>
            </div>
          </div>

          <!-- Keyword Search & Status Filter -->
          <div class="col-lg-4 col-md-12">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
              <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Search conditions..."
                v-model="searchKeyword"
                @input="applyFilters"
              />
              <button v-if="searchKeyword" class="btn btn-outline-secondary" type="button" @click="searchKeyword = ''; applyFilters()">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Terms List Table / Card -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive" v-if="filteredList.length > 0">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light table-head-custom">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 170px;">Module Name</th>
                <th>Condition Description / Text</th>
                <th style="width: 140px;" class="text-center">Default (ডিফল্ট)</th>
                <th style="width: 120px;" class="text-center">Status</th>
                <th style="width: 120px;" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in filteredList" :key="item.id">
                <td class="text-center font-monospace text-muted">{{ idx + 1 }}</td>
                <td>
                  <span class="badge" :class="getModuleBadgeClass(item.module_name)" style="font-size: 11.5px; padding: 5px 8px;">
                    <i :class="getModuleIcon(item.module_name)" class="me-1"></i>
                    {{ item.module_name }}
                  </span>
                </td>
                <td>
                  <div class="fw-semibold text-dark">{{ item.condition_text }}</div>
                  <small class="text-muted font-monospace" v-if="item.sorting > 0">Order: {{ item.sorting }}</small>
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    class="btn btn-xs py-1 px-2 font-monospace border-0 rounded-pill"
                    :class="item.is_default ? 'btn-success bg-opacity-25 text-success fw-bold' : 'btn-light text-muted'"
                    @click="toggleDefault(item)"
                    title="Toggle default inclusion"
                  >
                    <i :class="item.is_default ? 'fas fa-check-circle me-1' : 'far fa-circle me-1'"></i>
                    {{ item.is_default ? 'Checked Default' : 'Optional' }}
                  </button>
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    class="btn btn-xs py-1 px-2 font-monospace border-0 rounded-pill"
                    :class="item.status === 'active' ? 'btn-primary bg-opacity-10 text-primary fw-bold' : 'btn-danger bg-opacity-10 text-danger fw-bold'"
                    @click="toggleStatus(item)"
                    title="Toggle Active Status"
                  >
                    <i class="fas fa-dot-circle me-1"></i>
                    {{ item.status === 'active' ? 'Active' : 'Inactive' }}
                  </button>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" @click="openModal('edit', item)" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" @click="deleteItem(item.id)" title="Delete">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-5">
          <i class="fas fa-file-contract fs-1 text-muted opacity-25 mb-3"></i>
          <h6 class="fw-bold text-muted">No Terms & Conditions Found</h6>
          <p class="text-muted small mb-3">Add conditions for your invoices, purchase orders, or warranty documents.</p>
          <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" @click="openModal('create')">
            <i class="fas fa-plus me-1"></i> Add First Condition
          </button>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1060;" v-if="showModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header py-3 text-white" style="background-color: #112C47;">
            <h6 class="modal-title fw-bold text-white mb-0">
              <i class="fas fa-file-contract me-2 text-warning"></i>
              {{ modalMode === 'create' ? 'Add Terms & Condition' : 'Edit Terms & Condition' }}
            </h6>
            <button type="button" class="btn-close btn-close-white" @click="showModal = false"></button>
          </div>
          <div class="modal-body p-3">
            <form @submit.prevent="saveForm">
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">
                  <i class="fas fa-layer-group text-primary me-1"></i> Target Module (মডিউলের নাম):
                </label>
                <select class="form-select form-select-sm fw-bold" v-model="formData.module_name" required>
                  <option value="Invoice">Invoice (বিক্রয় রশিদ / চালান)</option>
                  <option value="Purchase Order">Purchase Order (ক্রয় আদেশ)</option>
                  <option value="Warranty">Warranty & Claims (ওয়ারেন্টি ও সার্ভিস)</option>
                  <option value="Quotation">Quotation (কোটেশন / প্রস্তাবনা)</option>
                </select>
                <small class="text-muted" style="font-size: 11px;">Conditions under <strong>Invoice</strong> will show in the POS Terminal and Sales Receipts.</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">
                  <i class="fas fa-pen-nib text-primary me-1"></i> Condition Text (শর্তাবলী বিবরণ):
                </label>
                <textarea
                  class="form-control form-control-sm"
                  rows="4"
                  placeholder="e.g. Please preserve this invoice for any warranty claims and exchange within 7 days."
                  v-model="formData.condition_text"
                  required
                ></textarea>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label fw-bold small text-dark">Sorting Order (ক্রম):</label>
                  <input type="number" class="form-control form-control-sm font-monospace" v-model.number="formData.sorting" placeholder="1" />
                </div>
                <div class="col-6">
                  <label class="form-label fw-bold small text-dark">Status (স্ট্যাটাস):</label>
                  <select class="form-select form-select-sm" v-model="formData.status">
                    <option value="active">Active (সক্রিয়)</option>
                    <option value="inactive">Inactive (নিষ্ক্রিয়)</option>
                  </select>
                </div>
              </div>

              <div class="p-2 border rounded bg-light mb-2">
                <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                  <input
                    class="form-check-input mt-0 cursor-pointer"
                    type="checkbox"
                    role="switch"
                    id="modalDefaultSwitch"
                    v-model="formData.is_default"
                  />
                  <label class="form-check-label fw-bold text-dark cursor-pointer small" for="modalDefaultSwitch">
                    Included / Checked by Default in POS Terminal & Documents
                  </label>
                </div>
              </div>

              <div class="modal-footer px-0 pb-0 pt-2 border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-secondary" @click="showModal = false">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold shadow-sm" :disabled="isSaving">
                  <i class="fas fa-spinner fa-spin me-1" v-if="isSaving"></i>
                  <i class="fas fa-save me-1" v-else></i>
                  {{ modalMode === 'create' ? 'Save Condition' : 'Update Condition' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "TermsConditionIndex",
  data() {
    return {
      terms: [],
      filteredList: [],
      selectedModule: "all",
      searchKeyword: "",
      showModal: false,
      modalMode: "create",
      isSaving: false,
      formData: {
        id: null,
        module_name: "Invoice",
        condition_text: "",
        sorting: 1,
        is_default: true,
        status: "active",
      },
      moduleOptions: [
        { value: "all", label: "All Modules", icon: "fas fa-list" },
        { value: "Invoice", label: "Invoice (POS / Sales)", icon: "fas fa-file-invoice" },
        { value: "Purchase Order", label: "Purchase Order", icon: "fas fa-shopping-cart" },
        { value: "Warranty", label: "Warranty", icon: "fas fa-shield-alt" },
        { value: "Quotation", label: "Quotation", icon: "fas fa-file-invoice-dollar" },
      ],
    };
  },
  methods: {
    fetchTerms() {
      axios
        .get("termsCondition")
        .then((res) => {
          this.terms = res.data || [];
          this.applyFilters();
        })
        .catch((err) => {
          console.error(err);
          this.$toast("Failed to load Terms & Conditions", "error");
        });
    },
    filterByModule(module) {
      this.selectedModule = module;
      this.applyFilters();
    },
    applyFilters() {
      let list = [...this.terms];
      if (this.selectedModule !== "all") {
        list = list.filter((item) => item.module_name === this.selectedModule);
      }
      if (this.searchKeyword && this.searchKeyword.trim()) {
        const kw = this.searchKeyword.trim().toLowerCase();
        list = list.filter(
          (item) =>
            item.condition_text.toLowerCase().includes(kw) ||
            item.module_name.toLowerCase().includes(kw)
        );
      }
      this.filteredList = list;
    },
    getCountForModule(module) {
      if (module === "all") return this.terms.length;
      return this.terms.filter((t) => t.module_name === module).length;
    },
    getModuleBadgeClass(mod) {
      switch (mod) {
        case "Invoice":
          return "bg-primary";
        case "Purchase Order":
          return "bg-success";
        case "Warranty":
          return "bg-warning text-dark";
        case "Quotation":
          return "bg-info text-dark";
        default:
          return "bg-secondary";
      }
    },
    getModuleIcon(mod) {
      switch (mod) {
        case "Invoice":
          return "fas fa-file-invoice";
        case "Purchase Order":
          return "fas fa-shopping-cart";
        case "Warranty":
          return "fas fa-shield-alt";
        case "Quotation":
          return "fas fa-file-invoice-dollar";
        default:
          return "fas fa-file-contract";
      }
    },
    openModal(mode, item = null) {
      this.modalMode = mode;
      if (mode === "edit" && item) {
        this.formData = {
          id: item.id,
          module_name: item.module_name,
          condition_text: item.condition_text,
          sorting: item.sorting || 1,
          is_default: !!item.is_default,
          status: item.status || "active",
        };
      } else {
        this.formData = {
          id: null,
          module_name: this.selectedModule !== "all" ? this.selectedModule : "Invoice",
          condition_text: "",
          sorting: this.terms.length + 1,
          is_default: true,
          status: "active",
        };
      }
      this.showModal = true;
    },
    saveForm() {
      if (!this.formData.condition_text.trim()) {
        this.$toast("Condition text is required", "warning");
        return;
      }
      this.isSaving = true;
      const url =
        this.modalMode === "create"
          ? "termsCondition"
          : `termsCondition/${this.formData.id}`;
      const method = this.modalMode === "create" ? "post" : "put";

      axios[method](url, this.formData)
        .then((res) => {
          this.isSaving = false;
          this.showModal = false;
          this.$toast(
            this.modalMode === "create"
              ? "Condition added successfully!"
              : "Condition updated successfully!",
            "success"
          );
          this.fetchTerms();
        })
        .catch((err) => {
          this.isSaving = false;
          this.$toast("Failed to save condition", "error");
        });
    },
    toggleDefault(item) {
      axios
        .post(`termsCondition/${item.id}/toggle-default`)
        .then((res) => {
          item.is_default = res.data.is_default;
          this.$toast("Default status updated", "success");
        })
        .catch(() => {
          this.$toast("Failed to update status", "error");
        });
    },
    toggleStatus(item) {
      axios
        .post(`termsCondition/${item.id}/toggle-status`)
        .then((res) => {
          item.status = res.data.status;
          this.$toast("Status updated", "success");
        })
        .catch(() => {
          this.$toast("Failed to update status", "error");
        });
    },
    deleteItem(id) {
      if (!confirm("Are you sure you want to delete this condition?")) return;
      axios
        .delete(`termsCondition/${id}`)
        .then(() => {
          this.$toast("Condition deleted successfully", "success");
          this.fetchTerms();
        })
        .catch(() => {
          this.$toast("Failed to delete condition", "error");
        });
    },
  },
  created() {
    this.fetchTerms();
  },
};
</script>

<style scoped>
.terms-condition-page {
  font-family: inherit;
}
.table-head-custom th {
  font-size: 12px;
  font-weight: 700;
  color: #112c47;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px 12px;
}
</style>
