<template>
  <create-form @onSubmit="submit">
    <div class="col-12">
      <div class="card border shadow-sm mb-4">
        <div class="card-header bg-light py-2">
          <span class="fw-bold text-dark">
            <i class="fas fa-tags me-1 text-primary"></i> {{ $t("Discount Configuration") }}
          </span>
        </div>
        <div class="card-body p-4">
          <div class="row g-3">
            <!-- 1. Campaign / Discount Name -->
            <Input
              v-model="data.name"
              field="data.name"
              :title="$t('Campaign / Discount Title')"
              col="8"
              :placeholder="$t('e.g. 10% Off on Electronics / Eid Mega Discount')"
              :req="true"
            />

            <!-- 2. Status Switch -->
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Status") }} <span class="text-danger">*</span></label>
              <Switch
                v-model="data.status"
                field="data.status"
                title=""
                :on-label="$t('Active')"
                :off-label="$t('Deactive')"
                :on-value="'active'"
                :off-value="'deactive'"
                :req="true"
                col="12"
              />
            </div>

            <!-- 3. Scope Selection (Category Wise or Item Wise) -->
            <div class="col-12">
              <div class="p-3 bg-light rounded border">
                <label class="form-label small fw-bold text-dark d-block mb-2">
                  <i class="fas fa-layer-group me-1 text-primary"></i> {{ $t("Discount Scope:") }} <span class="text-danger">*</span>
                </label>
                <div class="d-flex flex-wrap gap-4 align-items-center">
                  <div class="form-check form-check-inline cursor-pointer">
                    <input
                      class="form-check-input cursor-pointer"
                      type="radio"
                      id="scopeCategory"
                      value="category"
                      v-model="data.discount_type"
                    />
                    <label class="form-check-label fw-bold text-dark cursor-pointer ms-1" for="scopeCategory">
                      <i class="fas fa-folder text-warning me-1"></i> {{ $t("Category Wise Discount") }}
                    </label>
                  </div>

                  <div class="form-check form-check-inline cursor-pointer">
                    <input
                      class="form-check-input cursor-pointer"
                      type="radio"
                      id="scopeItem"
                      value="item"
                      v-model="data.discount_type"
                    />
                    <label class="form-check-label fw-bold text-dark cursor-pointer ms-1" for="scopeItem">
                      <i class="fas fa-box text-success me-1"></i> {{ $t("Item Wise Discount") }}
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- 4. Category Dropdown (Visible when scope is category) -->
            <div class="col-md-6" v-if="data.discount_type === 'category'">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Target Category") }} <span class="text-danger">*</span></label>
              <div class="v-select-wrapper">
                <v-select
                  v-model="data.category_id"
                  label="title"
                  :reduce="(obj) => obj.id"
                  :options="categories"
                  :placeholder="$t('-- Select Category --')"
                  :closeOnSelect="true"
                />
              </div>
            </div>

            <!-- 5. Unified Item Selection (Visible when scope is item for both Create and Edit) -->
            <div class="col-12" v-if="data.discount_type === 'item'">
              <div class="card border p-3 rounded bg-white shadow-sm">
                <!-- Header with Title, Count & Action Buttons -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                  <div class="d-flex align-items-center gap-2">
                    <label class="form-label fw-bold text-dark mb-0">
                      <i class="fas fa-boxes text-primary me-1"></i>
                      {{ $t("Target Products / Items") }} <span class="text-danger">*</span>
                    </label>
                    <span v-if="data.items && data.items.length > 0" class="badge bg-success px-2 py-1">
                      {{ data.items.length }} {{ $t("Item(s) Selected") }}
                    </span>
                  </div>

                  <!-- Quick Action Buttons -->
                  <div class="d-flex align-items-center gap-2">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary py-1 px-3 d-flex align-items-center gap-1"
                      @click="selectAllItems"
                      :title="$t('Select all items')"
                    >
                      <i class="fas fa-check-double"></i>
                      <span>{{ $t("Select All Items") }} ({{ items.length }})</span>
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger py-1 px-3 d-flex align-items-center gap-1"
                      @click="clearAllItems"
                      v-if="data.items && data.items.length > 0"
                    >
                      <i class="fas fa-trash-alt"></i>
                      <span>{{ $t("Clear Selection") }}</span>
                    </button>
                  </div>
                </div>

                <!-- Category Quick Helper Row -->
                <div class="row g-2 align-items-center mb-3 p-2 bg-light rounded border">
                  <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">{{ $t("Quick Filter / Add by Category:") }}</label>
                    <div class="v-select-wrapper">
                      <v-select
                        v-model="categoryFilterId"
                        label="title"
                        :reduce="(obj) => obj.id"
                        :options="categories"
                        :placeholder="$t('-- Filter by Category --')"
                        :closeOnSelect="true"
                      />
                    </div>
                  </div>
                  <div class="col-md-7 d-flex align-items-end pt-md-4">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary py-1 px-3 me-2"
                      v-if="categoryFilterId"
                      @click="addCategoryItems"
                    >
                      <i class="fas fa-plus-circle text-success me-1"></i>
                      {{ $t("Add All Items from this Category") }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-link text-muted py-1 px-2 text-decoration-none"
                      v-if="categoryFilterId"
                      @click="categoryFilterId = null"
                    >
                      <i class="fas fa-times me-1"></i> {{ $t("Clear Filter") }}
                    </button>
                  </div>
                </div>

                <!-- Multiple Items Dropdown with Right-side Checkmark & Single-place entry -->
                <div class="form-group mb-1">
                  <label class="form-label small fw-bold text-dark mb-1">
                    {{ $t("Select Items (Search by Product Name or Barcode)") }} <span class="text-danger">*</span>
                  </label>
                  <div class="v-select-wrapper v-select-multiple-wrapper">
                    <v-select
                      v-model="data.items"
                      :multiple="true"
                      label="title"
                      :reduce="(obj) => obj.id"
                      :options="filteredItems"
                      :placeholder="$t('-- Search & Select Products / Items --')"
                      :closeOnSelect="false"
                      :deselect-from-dropdown="true"
                    >
                      <template #option="option">
                        <div class="d-flex align-items-center justify-content-between py-1 w-100">
                          <div class="d-flex align-items-center">
                            <span class="fw-bold text-dark">{{ option.title }}</span>
                            <span class="badge bg-light text-dark border ms-2 font-monospace" v-if="option.barcode">{{ option.barcode }}</span>
                            <span class="text-muted small ms-2" v-if="option.category && option.category.title">({{ option.category.title }})</span>
                          </div>
                          <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small font-monospace" v-if="option.sell_price">৳{{ option.sell_price }}</span>
                            <i v-if="isItemSelected(option.id)" class="fas fa-check-circle text-success fs-6 ms-2" :title="$t('Selected')"></i>
                            <i v-else class="far fa-circle text-muted fs-6 ms-2 opacity-25"></i>
                          </div>
                        </div>
                      </template>
                    </v-select>
                  </div>
                  <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle text-primary me-1"></i>
                    {{ $t("All selected items are saved together in a single campaign row. Click selected items in dropdown or click '×' to deselect.") }}
                  </small>
                </div>
              </div>
            </div>

            <!-- 6. Applicable On (Price Nature) -->
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Applicable On (Sale Nature)") }} <span class="text-danger">*</span></label>
              <div class="v-select-wrapper">
                <v-select
                  v-model="data.applicable_on"
                  label="title"
                  :reduce="(obj) => obj.value"
                  :options="applicableOptions"
                  :placeholder="$t('-- Select Applicable Sale Nature --')"
                  :closeOnSelect="true"
                />
              </div>
              <small class="text-muted d-block mt-1">
                {{ $t("Specifies whether this discount applies to Retail sale price, Wholesale sale price, or Both.") }}
              </small>
            </div>

            <!-- 7. Discount Unit (% or Fixed) -->
            <div class="col-md-3">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Discount Unit") }} <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm fw-bold" v-model="data.unit">
                <option value="percentage">{{ $t("Percentage (%)") }}</option>
                <option value="fixed">{{ $t("Fixed Amount (৳)") }}</option>
              </select>
            </div>

            <!-- 8. Discount Value -->
            <div class="col-md-3">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Discount Value") }} <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light fw-bold">
                  {{ data.unit === 'percentage' ? '%' : '৳' }}
                </span>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control form-control-sm text-end font-monospace fw-bold fs-6"
                  placeholder="0.00"
                  v-model.number="data.discount_value"
                />
              </div>
            </div>

            <!-- 9. Validity Date Range (From & To) -->
            <div class="col-md-3">
              <date-picker
                id="valid_from_date"
                v-model="data.valid_from"
                field="data.valid_from"
                :title="$t('Valid From Date')"
                :placeholder="$t('Valid From')"
                col="12"
                :req="true"
              ></date-picker>
            </div>

            <div class="col-md-3">
              <date-picker
                id="valid_to_date"
                v-model="data.valid_to"
                field="data.valid_to"
                :title="$t('Valid To Date')"
                :placeholder="$t('Valid To')"
                col="12"
                :req="true"
              ></date-picker>
            </div>

            <!-- 10. Description / Notes -->
            <div class="col-12">
              <Textarea
                v-model="data.description"
                field="data.description"
                :title="$t('Description / Remarks (Optional)')"
                col="12"
                :req="false"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Custom Footer Actions -->
    <template #form_footer>
      <div class="col-12 mt-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 bg-white border rounded shadow-sm">
          <router-link :to="{ name: model + '.index' }" class="btn btn-outline-secondary px-4 fw-semibold">
            <i class="fas fa-arrow-left me-1"></i> {{ $t("Back to List") }}
          </router-link>

          <button
            type="submit"
            class="theme_btn px-5 py-2 fw-bold d-flex align-items-center gap-2 shadow"
            :disabled="$root.submit"
          >
            <template v-if="$root.submit">
              <i class="fa fa-spinner fa-spin"></i> {{ $t("Processing...") }}
            </template>
            <template v-else>
              <i class="fas fa-check-circle"></i> {{ $t($route.params.id ? "Update Discount" : "Save Discount") }}
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = "discount";

export default {
  data() {
    return {
      model: model,
      page_title: "Discount",
      data: {
        name: "",
        discount_type: "category",
        category_id: null,
        items: [],
        applicable_on: "both",
        unit: "percentage",
        discount_value: 10,
        valid_from: this.$filter.today(),
        valid_to: this.calculateDefaultValidTo(),
        status: "active",
        description: "",
      },
      categoryFilterId: null,
      categories: [],
      items: [],
      applicableOptions: [
        { title: "Both (Retail & Wholesale)", value: "both" },
        { title: "Retail Only", value: "retail" },
        { title: "Wholesale Only", value: "wholesale" },
      ],
    };
  },
  provide() {
    return {
      validate: this.validation,
    };
  },
  computed: {
    filteredItems() {
      if (!this.categoryFilterId) {
        return this.items;
      }
      return this.items.filter((item) => item.category_id == this.categoryFilterId);
    },
  },
  watch: {
    "data.discount_type"(newVal) {
      if (newVal === "category") {
        this.data.items = [];
      } else if (newVal === "item") {
        this.data.category_id = null;
      }
    },
  },
  methods: {
    calculateDefaultValidTo() {
      const d = new Date();
      d.setDate(d.getDate() + 30);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const day = String(d.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    isItemSelected(itemId) {
      return Array.isArray(this.data.items) && this.data.items.includes(itemId);
    },
    getCategories() {
      axios.get("getcategories/Item").then((res) => {
        this.categories = res.data || [];
      });
    },
    getItems() {
      axios.get("item?allData=true").then((res) => {
        this.items = res.data || [];
      });
    },
    selectAllItems() {
      this.data.items = this.items.map((i) => i.id);
      this.$toast(`${this.items.length} items selected`, "info");
    },
    clearAllItems() {
      this.data.items = [];
    },
    addCategoryItems() {
      if (!this.categoryFilterId) return;
      const catItems = this.items.filter((i) => i.category_id == this.categoryFilterId);
      const newIds = catItems.map((i) => i.id);
      const current = Array.isArray(this.data.items) ? this.data.items : [];
      const merged = Array.from(new Set([...current, ...newIds]));
      this.data.items = merged;
      this.$toast(`${newIds.length} items added from category`, "success");
    },
    submit() {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (this.data.discount_type === "category" && !this.data.category_id) {
          this.$toast("Please select a target Category", "warning");
          return false;
        }

        if (this.data.discount_type === "item") {
          if (!Array.isArray(this.data.items) || this.data.items.length === 0) {
            this.$toast("Please select at least one Product / Item", "warning");
            return false;
          }
        }

        if (error > 0) {
          this.$toast(`You need to fill ${error} more empty mandatory fields`, "warning");
          return false;
        }

        if (res) {
          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = "Discount Edit";
      this.setBreadcrumbs(this.model, "edit");
      this.get_data(`${this.model}/${this.$route.params.id}`).then((res) => {
        if (res && res.data) {
          if (res.data.title && !this.data.name) this.data.name = res.data.title;
          if (res.data.scope) this.data.discount_type = res.data.scope;
          if (res.data.items && Array.isArray(res.data.items)) {
            this.data.items = res.data.items.map(Number);
          } else if (res.data.item_id) {
            this.data.items = [Number(res.data.item_id)];
          }
          if (res.data.notes && !this.data.description) this.data.description = res.data.notes;
          if (res.data.status === 1 || res.data.status === '1' || res.data.status === 'active') {
            this.data.status = 'active';
          } else {
            this.data.status = 'deactive';
          }
        }
      });
    } else {
      this.page_title = "Discount Create";
      this.setBreadcrumbs(this.model, "create");
    }
    this.getCategories();
    this.getItems();
  },
  validators: {
    "data.name": function (value = null) {
      return Validator.value(value).required("Campaign / Discount Title is required");
    },
    "data.discount_type": function (value = null) {
      return Validator.value(value).required("Discount Scope is required");
    },
    "data.applicable_on": function (value = null) {
      return Validator.value(value).required("Applicable Sale Nature is required");
    },
    "data.unit": function (value = null) {
      return Validator.value(value).required("Discount Unit is required");
    },
    "data.discount_value": function (value = null) {
      return Validator.value(value).required("Discount Value is required");
    },
    "data.valid_from": function (value = null) {
      return Validator.value(value).required("Valid From Date is required");
    },
    "data.valid_to": function (value = null) {
      return Validator.value(value).required("Valid To Date is required");
    },
  },
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

/* Fix v-select standard container & borders */
.v-select-wrapper {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e1e3e1;
  width: 100%;
  min-height: 38px;
}

.v-select-multiple-wrapper {
  min-height: 44px;
}

/* Fix vue-select styling */
:deep(.v-select .vs__dropdown-toggle) {
  border: none !important;
  padding: 4px 8px !important;
  min-height: 38px !important;
  border-radius: 8px !important;
  background: transparent !important;
}

/* Override global .vs__selected { position: absolute !important; } for multiple mode */
:deep(.v-select.vs--multiple .vs__selected) {
  position: static !important;
  display: inline-flex !important;
  align-items: center !important;
  background-color: #eef5ff !important;
  color: #0d6efd !important;
  border: 1px solid #cfe2ff !important;
  border-radius: 6px !important;
  padding: 2px 8px !important;
  margin: 2px 4px 2px 0 !important;
  font-size: 12px !important;
  font-weight: 500 !important;
  line-height: 1.4 !important;
  z-index: 1 !important;
}

:deep(.v-select.vs--multiple .vs__selected-options) {
  display: flex !important;
  flex-wrap: wrap !important;
  align-items: center !important;
  gap: 3px !important;
  padding: 2px !important;
  max-height: 140px !important;
  overflow-y: auto !important;
}

:deep(.v-select.vs--multiple .vs__deselect) {
  fill: #0d6efd !important;
  margin-left: 6px !important;
  cursor: pointer !important;
}

:deep(.v-select.vs--multiple .vs__deselect:hover) {
  fill: #dc3545 !important;
}

:deep(.v-select.vs--multiple .vs__search) {
  margin: 2px !important;
  padding: 2px 4px !important;
  border: none !important;
  font-size: 13px !important;
}

:deep(.vs__dropdown-option) {
  padding: 8px 12px !important;
  border-bottom: 1px solid #f2f2f2 !important;
}

:deep(.vs__dropdown-option--highlight) {
  background-color: #f0f7ff !important;
  color: #0d6efd !important;
}

:deep(.vs__dropdown-option--selected) {
  background-color: #f6fdf8 !important;
}
</style>
