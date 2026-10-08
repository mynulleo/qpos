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
                :off-label="$t('Inactive')"
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
              <v-select
                v-model="data.category_id"
                label="title"
                :reduce="(obj) => obj.id"
                :options="categories"
                :placeholder="$t('-- Select Category --')"
                :closeOnSelect="true"
              />
            </div>

            <!-- 5. Item Dropdown (Visible when scope is item) -->
            <div class="col-md-6" v-if="data.discount_type === 'item'">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Target Product / Item") }} <span class="text-danger">*</span></label>
              <v-select
                v-model="data.item_id"
                label="title"
                :reduce="(obj) => obj.id"
                :options="items"
                :placeholder="$t('-- Select Product / Item --')"
                :closeOnSelect="true"
              >
                <template #option="option">
                  <div>
                    <span class="fw-bold">{{ option.title }}</span>
                    <span class="badge bg-light text-dark border ms-2 font-monospace" v-if="option.barcode">{{ option.barcode }}</span>
                  </div>
                </template>
              </v-select>
            </div>

            <!-- 6. Applicable On (Price Nature) -->
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark mb-1">{{ $t("Applicable On (Sale Nature)") }} <span class="text-danger">*</span></label>
              <v-select
                v-model="data.applicable_on"
                label="title"
                :reduce="(obj) => obj.value"
                :options="applicableOptions"
                :placeholder="$t('-- Select Applicable Sale Nature --')"
                :closeOnSelect="true"
              />
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
        item_id: null,
        applicable_on: "both",
        unit: "percentage",
        discount_value: 10,
        valid_from: this.$filter.today(),
        valid_to: this.calculateDefaultValidTo(),
        status: "active",
        description: "",
      },
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
  watch: {
    "data.discount_type"(newVal) {
      if (newVal === "category") {
        this.data.item_id = null;
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
    submit() {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (this.data.discount_type === "category" && !this.data.category_id) {
          this.$toast("Please select a target Category", "warning");
          return false;
        }

        if (this.data.discount_type === "item" && !this.data.item_id) {
          this.$toast("Please select a target Item", "warning");
          return false;
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
      this.get_data(`${this.model}/${this.$route.params.id}`);
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
</style>
