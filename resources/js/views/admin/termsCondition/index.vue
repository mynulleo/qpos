<template>
  <div class="terms-condition-page">
    <div id="list_page_wrapper">
      <!-- 🌟 Standard Modern Page Header Card matching QPOS Theme -->
      <div class="card border-0 shadow-sm mb-2 page_header_card">
        <div class="card-body py-2 px-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <!-- Left: Page Title & Total Count -->
            <div class="d-flex align-items-center gap-2">
              <h5 class="mb-0 fw-bold text-dark text-nowrap form_card_title d-flex align-items-center gap-2">
                <i class="fas fa-file-contract text-primary"></i>
                <span>{{ $t('Terms & Conditions List') }}</span>
              </h5>
              <span class="badge bg-secondary font-monospace" style="font-size: 11px;">
                {{ filteredList.length }}
              </span>
            </div>

            <!-- Center: Quick Search Bar -->
            <div class="header_base_search flex-grow-1 mx-md-3" style="max-width: 480px;">
              <div class="input-group input-group-sm">
                <!-- Module Filter Selector inside search bar -->
                <select
                  class="form-select form-select-sm"
                  style="max-width: 150px;"
                  v-model="selectedModule"
                  @change="applyFilters"
                >
                  <option value="all">{{ $t('All Modules') }}</option>
                  <option value="Invoice">{{ $t('Invoice') }}</option>
                  <option value="Purchase Order">{{ $t('Purchase Order') }}</option>
                  <option value="Warranty">{{ $t('Warranty & Claims') }}</option>
                  <option value="Quotation">{{ $t('Quotation') }}</option>
                </select>

                <!-- Keyword Search Input -->
                <input
                  type="search"
                  class="form-control"
                  :placeholder="$t('Search conditions... (Press Enter)')"
                  v-model="searchKeyword"
                  @keyup.enter="applyFilters"
                  @input="applyFilters"
                />

                <!-- Search Button -->
                <button
                  type="button"
                  class="btn btn-sm px-3 theme_search_btn"
                  @click="applyFilters"
                  :title="$t('Search')"
                >
                  <i class="fas fa-search"></i>
                </button>

                <!-- Clear Search Button -->
                <button
                  v-if="searchKeyword || selectedModule !== 'all' || selectedStatus !== 'all' || selectedDefault !== 'all'"
                  type="button"
                  class="btn btn-outline-secondary btn-sm"
                  @click="resetFilters"
                  :title="$t('Clear Filters')"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <!-- Right: Filter Toggle, Excel, Print & Add New Button -->
            <div class="d-flex align-items-center gap-2 right_page_header">
              <!-- Advance Filter Toggle Button -->
              <button
                type="button"
                class="advance_filter_btn position-relative"
                @click="showAdvanceFilter = !showAdvanceFilter"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                :data-bs-title="$t('Advance Filter')"
                :title="$t('Advance Filter')"
              >
                <i class="fas fa-sliders-h"></i>
                <span
                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size: 9px; padding: 2px 4px;"
                  v-if="activeFilterCount > 0"
                >
                  {{ activeFilterCount }}
                </span>
              </button>

              <!-- Excel Export -->
              <button
                v-if="filteredList.length > 0"
                type="button"
                class="p_btn"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                :data-bs-title="$t('Excel Export')"
                :title="$t('Excel Export')"
              >
                <download-excel
                  :data="filteredList"
                  :fields="json_fields"
                  name="terms_conditions.xls"
                  title="Terms and Conditions"
                  class="d-flex align-items-center justify-content-center w-100 h-100 cursor-pointer"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-excel">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                    <path d="M10 12l4 5" />
                    <path d="M10 17l4 -5" />
                  </svg>
                </download-excel>
              </button>

              <!-- Print Button -->
              <button
                type="button"
                class="p_btn"
                @click="printTable"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                :data-bs-title="$t('Print Table')"
                :title="$t('Print Table')"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-printer">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                  <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                  <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                  <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                </svg>
              </button>

              <!-- Add New Button -->
              <button
                type="button"
                class="btn btn-primary btn-sm theme_btn px-3 d-flex align-items-center gap-1 shadow-sm"
                @click="openModal('create')"
              >
                <i class="fas fa-plus"></i>
                <span class="d-none d-sm-inline">{{ $t('Add New') }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 🔍 Collapsible Advance Filter Bar -->
      <div class="card border-0 shadow-sm mb-2" v-if="showAdvanceFilter">
        <div class="card-body p-3 bg-light rounded">
          <div class="row g-2 align-items-center">
            <div class="col-md-4 col-sm-6">
              <label class="form-label small fw-bold mb-1">{{ $t('Filter by Module') }}:</label>
              <select class="form-select form-select-sm" v-model="selectedModule" @change="applyFilters">
                <option value="all">-- {{ $t('All Modules') }} --</option>
                <option value="Invoice">{{ $t('Invoice') }} ({{ $t('POS / Sales') }})</option>
                <option value="Purchase Order">{{ $t('Purchase Order') }}</option>
                <option value="Warranty">{{ $t('Warranty & Claims') }}</option>
                <option value="Quotation">{{ $t('Quotation') }}</option>
              </select>
            </div>

            <div class="col-md-4 col-sm-6">
              <label class="form-label small fw-bold mb-1">{{ $t('Filter by Default State') }}:</label>
              <select class="form-select form-select-sm" v-model="selectedDefault" @change="applyFilters">
                <option value="all">-- {{ $t('All Defaults') }} --</option>
                <option value="1">{{ $t('Default Only') }}</option>
                <option value="0">{{ $t('Optional Only') }}</option>
              </select>
            </div>

            <div class="col-md-4 col-sm-6">
              <label class="form-label small fw-bold mb-1">{{ $t('Filter by Status') }}:</label>
              <select class="form-select form-select-sm" v-model="selectedStatus" @change="applyFilters">
                <option value="all">-- {{ $t('All Statuses') }} --</option>
                <option value="active">{{ $t('Active') }}</option>
                <option value="inactive">{{ $t('Inactive') }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Standard Base Table Matching QPOS Theme -->
      <div class="base_table_list">
        <div id="printArea" class="table-responsive text-nowrap table-basic table_wrapper shadow-sm rounded bg-white">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr class="tr_stick">
                <th class="sl text-center" style="width: 55px; min-width: 55px;">
                  <span class="heading">{{ $t('SL') }}</span>
                </th>
                <th style="width: 150px; min-width: 140px;">
                  <span class="heading">{{ $t('Module') }}</span>
                </th>
                <th style="min-width: 320px;">
                  <span class="heading">{{ $t('Condition Description') }}</span>
                </th>
                <th class="text-center" style="width: 110px; min-width: 100px;">
                  <span class="heading">{{ $t('Default') }}</span>
                </th>
                <th class="text-center" style="width: 90px; min-width: 80px;">
                  <span class="heading">{{ $t('Sorting') }}</span>
                </th>
                <th class="text-center" style="width: 100px; min-width: 90px;">
                  <span class="heading">{{ $t('Status') }}</span>
                </th>
                <th class="text-center" style="width: 90px; min-width: 90px;">
                  <span class="heading">{{ $t('Action') }}</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in filteredList" :key="item.id">
                <!-- SL -->
                <td class="text-center text-muted font-monospace" style="font-size: 12px;">
                  {{ index + 1 }}
                </td>

                <!-- Module Name Badge (Theme size: small & compact) -->
                <td>
                  <span class="badge" :class="getModuleBadgeClass(item.module_name)" style="font-size: 10.5px; font-weight: 600; padding: 3px 7px;">
                    <i :class="getModuleIcon(item.module_name)" class="me-1"></i>
                    {{ $t(item.module_name) }}
                  </span>
                </td>

                <!-- Condition Description Text -->
                <td>
                  <div class="fw-semibold text-dark text-wrap" style="max-width: 550px; font-size: 12.5px; line-height: 1.45;">
                    {{ item.condition_text }}
                  </div>
                </td>

                <!-- Default Status Badge (Crystal Clear Visibility & Compact) -->
                <td class="text-center">
                  <span
                    v-if="item.is_default"
                    class="badge bg-success text-white px-2 py-1 cursor-pointer shadow-xs"
                    style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;"
                    @click="toggleDefault(item)"
                    :title="$t('Click to toggle Default state')"
                  >
                    <i class="fas fa-check me-1"></i> {{ $t('DEFAULT') }}
                  </span>
                  <span
                    v-else
                    class="badge bg-secondary text-white px-2 py-1 cursor-pointer shadow-xs"
                    style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;"
                    @click="toggleDefault(item)"
                    :title="$t('Click to toggle Default state')"
                  >
                    <i class="fas fa-minus me-1"></i> {{ $t('OPTIONAL') }}
                  </span>
                </td>

                <!-- Sorting Input (Standard QPOS Table Sorting Number) -->
                <td class="text-center">
                  <div class="table_sorting_number mx-auto">
                    <input
                      type="number"
                      min="0"
                      v-model.number="item.sorting"
                      @change="updateSorting(item)"
                      @keyup.enter="updateSorting(item)"
                      class="form-control form-control-sm text-center font-monospace p-1"
                      style="width: 55px; height: 26px; font-size: 11.5px;"
                    />
                  </div>
                </td>

                <!-- Status Badge (Theme matching compact badge) -->
                <td class="text-center">
                  <span
                    v-if="item.status === 'active'"
                    class="badge bg-success text-white px-2 py-1 cursor-pointer shadow-xs"
                    style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;"
                    @click="toggleStatus(item)"
                    :title="$t('Click to toggle Status')"
                  >
                    {{ $t('ACTIVE') }}
                  </span>
                  <span
                    v-else
                    class="badge bg-danger text-white px-2 py-1 cursor-pointer shadow-xs"
                    style="font-size: 10px; font-weight: 600; letter-spacing: 0.3px;"
                    @click="toggleStatus(item)"
                    :title="$t('Click to toggle Status')"
                  >
                    {{ $t('INACTIVE') }}
                  </span>
                </td>

                <!-- Actions: Edit & Delete -->
                <td class="text-center">
                  <div class="d-flex align-items-center justify-content-center gap-1">
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-primary p-1 border-0"
                      @click="openModal('edit', item)"
                      :title="$t('Edit')"
                      style="width: 26px; height: 26px; border-radius: 4px;"
                    >
                      <i class="fas fa-edit" style="font-size: 11px;"></i>
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-danger p-1 border-0"
                      @click="deleteItem(item.id)"
                      :title="$t('Delete')"
                      style="width: 26px; height: 26px; border-radius: 4px;"
                    >
                      <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="filteredList.length === 0">
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="fas fa-file-contract fs-1 opacity-25 d-block mb-2"></i>
                  <span class="fw-bold">{{ $t('No Terms & Conditions Found') }}</span>
                  <p class="small text-muted mb-3">{{ $t('Add conditions for your invoices, purchase orders, or warranty documents.') }}</p>
                  <button type="button" class="btn btn-primary btn-sm theme_btn px-3" @click="openModal('create')">
                    <i class="fas fa-plus me-1"></i> {{ $t('Add First Condition') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 🌟 Create / Edit Modal (Standard QPOS Modal Theme) -->
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1060;" v-if="showModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header py-2 px-3 text-white" style="background-color: #112C47;">
            <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
              <i class="fas fa-file-contract text-warning"></i>
              <span>{{ modalMode === 'create' ? $t('Add Terms & Condition') : $t('Edit Terms & Condition') }}</span>
            </h6>
            <button type="button" class="btn-close btn-close-white" @click="showModal = false" style="font-size: 10px;"></button>
          </div>
          <div class="modal-body p-3">
            <form @submit.prevent="saveForm">
              <!-- Module Selection -->
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark mb-1">
                  <i class="fas fa-layer-group text-primary me-1"></i> {{ $t('Target Module') }}:
                </label>
                <select class="form-select form-select-sm fw-bold" v-model="formData.module_name" required>
                  <option value="Invoice">{{ $t('Invoice') }}</option>
                  <option value="Purchase Order">{{ $t('Purchase Order') }}</option>
                  <option value="Warranty">{{ $t('Warranty & Claims') }}</option>
                  <option value="Quotation">{{ $t('Quotation') }}</option>
                </select>
                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                  {{ $t('Conditions under Invoice will show in the POS Terminal and Sales Receipts.') }}
                </small>
              </div>

              <!-- Condition Text -->
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark mb-1">
                  <i class="fas fa-pen-nib text-primary me-1"></i> {{ $t('Condition Description') }}:
                </label>
                <textarea
                  class="form-control form-control-sm"
                  rows="4"
                  :placeholder="$t('e.g. Please preserve this invoice for any warranty claims and exchange within 7 days.')"
                  v-model="formData.condition_text"
                  required
                ></textarea>
              </div>

              <!-- Sorting & Status Row -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label fw-bold small text-dark mb-1">{{ $t('Sorting Order') }}:</label>
                  <input type="number" class="form-control form-control-sm font-monospace" v-model.number="formData.sorting" placeholder="1" />
                </div>
                <div class="col-6">
                  <label class="form-label fw-bold small text-dark mb-1">{{ $t('Status') }}:</label>
                  <select class="form-select form-select-sm" v-model="formData.status">
                    <option value="active">{{ $t('Active') }}</option>
                    <option value="inactive">{{ $t('Inactive') }}</option>
                  </select>
                </div>
              </div>

              <!-- Default Toggle Box with high clarity -->
              <div class="p-2 border rounded bg-light mb-3">
                <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                  <input
                    class="form-check-input mt-0 cursor-pointer"
                    type="checkbox"
                    role="switch"
                    id="modalDefaultSwitch"
                    v-model="formData.is_default"
                  />
                  <label class="form-check-label fw-bold text-dark cursor-pointer small" for="modalDefaultSwitch">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    {{ $t('Included by Default in POS Terminal & Receipts') }}
                  </label>
                </div>
              </div>

              <!-- Modal Footer -->
              <div class="modal-footer px-0 pb-0 pt-2 border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-secondary" @click="showModal = false">{{ $t('Cancel') }}</button>
                <button type="submit" class="btn btn-sm btn-primary theme_btn px-3 fw-bold shadow-sm" :disabled="isSaving">
                  <i class="fas fa-spinner fa-spin me-1" v-if="isSaving"></i>
                  <i class="fas fa-save me-1" v-else></i>
                  {{ modalMode === 'create' ? $t('Save Condition') : $t('Update Condition') }}
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
      selectedStatus: "all",
      selectedDefault: "all",
      searchKeyword: "",
      showAdvanceFilter: false,
      showModal: false,
      modalMode: "create",
      isSaving: false,
      json_fields: {
        "Module Name": "module_name",
        "Condition Text": "condition_text",
        "Sorting": "sorting",
        "Default": "is_default",
        "Status": "status",
      },
      formData: {
        id: null,
        module_name: "Invoice",
        condition_text: "",
        sorting: 1,
        is_default: true,
        status: "active",
      },
    };
  },
  computed: {
    activeFilterCount() {
      let count = 0;
      if (this.selectedModule !== "all") count++;
      if (this.selectedStatus !== "all") count++;
      if (this.selectedDefault !== "all") count++;
      return count;
    },
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
    applyFilters() {
      let list = [...this.terms];

      // Module filter
      if (this.selectedModule !== "all") {
        list = list.filter((item) => item.module_name === this.selectedModule);
      }

      // Status filter
      if (this.selectedStatus !== "all") {
        list = list.filter((item) => item.status === this.selectedStatus);
      }

      // Default state filter
      if (this.selectedDefault !== "all") {
        const isDef = this.selectedDefault === "1";
        list = list.filter((item) => !!item.is_default === isDef);
      }

      // Keyword search
      if (this.searchKeyword && this.searchKeyword.trim()) {
        const kw = this.searchKeyword.trim().toLowerCase();
        list = list.filter(
          (item) =>
            (item.condition_text && item.condition_text.toLowerCase().includes(kw)) ||
            (item.module_name && item.module_name.toLowerCase().includes(kw))
        );
      }

      this.filteredList = list;
    },
    resetFilters() {
      this.selectedModule = "all";
      this.selectedStatus = "all";
      this.selectedDefault = "all";
      this.searchKeyword = "";
      this.applyFilters();
    },
    getModuleBadgeClass(mod) {
      switch (mod) {
        case "Invoice":
          return "bg-primary text-white";
        case "Purchase Order":
          return "bg-success text-white";
        case "Warranty":
          return "bg-warning text-dark";
        case "Quotation":
          return "bg-info text-dark";
        default:
          return "bg-secondary text-white";
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
        .then(() => {
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
        .catch(() => {
          this.isSaving = false;
          this.$toast("Failed to save condition", "error");
        });
    },
    updateSorting(item) {
      axios
        .put(`termsCondition/${item.id}`, {
          module_name: item.module_name,
          condition_text: item.condition_text,
          sorting: item.sorting,
          is_default: item.is_default,
          status: item.status,
        })
        .then(() => {
          this.$toast("Sorting order updated", "success");
        })
        .catch(() => {
          this.$toast("Failed to update sorting", "error");
        });
    },
    toggleDefault(item) {
      axios
        .post(`termsCondition/${item.id}/toggle-default`)
        .then((res) => {
          item.is_default = res.data.is_default;
          this.$toast("Default status updated", "success");
          this.applyFilters();
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
          this.applyFilters();
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
    printTable() {
      const printContents = document.getElementById("printArea").innerHTML;
      const win = window.open("", "_blank");
      win.document.write(`
        <html>
          <head>
            <title>Terms and Conditions List</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
              body { font-family: sans-serif; padding: 20px; }
              table { width: 100%; border-collapse: collapse; font-size: 12px; }
              th, td { border: 1px solid #ddd; padding: 6px 8px; }
              th { background-color: #f2f2f2; }
              .badge { border: 1px solid #333; padding: 2px 4px; font-size: 10px; }
            </style>
          </head>
          <body>
            <h4 class="mb-3">Terms & Conditions List</h4>
            ${printContents}
          </body>
        </html>
      `);
      win.document.close();
      win.focus();
      setTimeout(() => {
        win.print();
        win.close();
      }, 500);
    },
  },
  created() {
    this.fetchTerms();
  },
};
</script>

<style scoped>
.terms-condition-page {
  padding: 0 4px;
}
.cursor-pointer {
  cursor: pointer;
}
.shadow-xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.theme_search_btn {
  background-color: #112c47;
  color: #fff;
  border: 1px solid #112c47;
}
.theme_search_btn:hover {
  background-color: #0c1f33;
  color: #fff;
}
.theme_btn {
  background-color: #112c47 !important;
  border-color: #112c47 !important;
  color: #fff !important;
}
.theme_btn:hover {
  background-color: #0c1f33 !important;
  border-color: #0c1f33 !important;
}
.advance_filter_btn {
  width: 32px;
  height: 32px;
  background-color: #112c47;
  color: #fff;
  border: none;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all 0.2s ease;
}
.advance_filter_btn:hover {
  background-color: #0c1f33;
  color: #fff;
}
.p_btn {
  width: 32px;
  height: 32px;
  background-color: #fff;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}
.p_btn:hover {
  background-color: #f8f9fa;
  color: #112c47;
  border-color: #112c47;
}
.table-basic thead th {
  background-color: #f8fafc;
  color: #112c47;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 9px 10px;
  border-bottom: 2px solid #edf2f7;
}
.table-basic tbody td {
  padding: 8px 10px;
  font-size: 12px;
  vertical-align: middle;
}
</style>
