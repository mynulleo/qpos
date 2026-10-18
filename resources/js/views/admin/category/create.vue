<template>
  <create-form @onSubmit="submit">
    <!-- Top Context Header Card (Unified bg-gradient-primary-subtle) -->
    <div class="col-12 mb-3">
      <!-- Edit Mode Header Card -->
      <div v-if="$route.params.id" class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-gradient-primary-subtle border-bottom py-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm">
                <i class="fas fa-tags fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Category...') }}</h5>
                  <span class="badge bg-white text-primary border shadow-xs px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                    #{{ $route.params.id }}
                  </span>
                  <span
                    class="badge rounded-pill px-2.5 py-1"
                    :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                    style="font-size: 0.75rem;"
                  >
                    <i :class="data.status === 'active' ? 'fas fa-check-circle' : 'fas fa-pause-circle'" class="me-1"></i>
                    {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                  </span>
                </div>
                <small class="text-muted d-flex flex-wrap align-items-center gap-3">
                  <span><i class="fas fa-cube me-1 text-info"></i> Module: <strong class="text-dark font-monospace">{{ data.module_name || "Item" }}</strong></span><span><i class="fas fa-sort-numeric-down me-1 text-warning"></i> Sort: <strong class="text-dark font-monospace">#{{ data.sorting ?? 0 }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'category.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
            </router-link>
          </div>
        </div>
      </div>

      <!-- Create Mode Header Card -->
      <div v-else class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-gradient-primary-subtle border-bottom py-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm">
                <i class="fas fa-tags fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('New Category Classification') }}</h5>
                <small class="text-muted">{{ $t('Define product catalog taxonomy, operational module mapping, and hierarchy') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-sitemap me-1 text-info"></i> {{ $t('Catalog Taxonomy') }}
              </span>
              <router-link :to="{ name: 'category.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Category Particulars -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-tag text-primary me-2"></i> {{ $t('Core Classification Details') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Module Selection -->
            <Select
              field="data.module_name"
              :req="true"
              title="System Module"
              col="6"
              v-model="data.module_name"
              label="name"
              :reduce="(obj) => obj.value"
              :options="modules"
              placeholder="--Select Module--"
              :closeOnSelect="true"
            />

            <!-- Category Title -->
            <Input
              v-model="data.title"
              field="data.title"
              title="Category Title / Name"
              placeholder="e.g. Electronics, Clothing, Fresh Produce"
              :req="true"
              col="6"
            />

            <!-- Informational Banner -->
            <div class="col-12 mt-2">
              <div class="p-3 rounded-3 bg-light border text-muted small d-flex align-items-start gap-2.5">
                <div class="text-primary fs-5 mt-0.5">
                  <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                  <strong class="text-dark d-block mb-0.5">{{ $t('Category Classification Guide') }}</strong>
                  <span>{{ $t('Categories organize items for POS checkout, inventory reporting, and barcode grouping. Selecting the proper module assigns this category to items in that specific operational department.') }}</span>
                </div>
              </div>
            </div>

            <!-- Live Category Preview Tag -->
            <div class="col-12" v-if="data.title">
              <div class="p-2.5 rounded border bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small text-muted fw-semibold">
                  <i class="fas fa-eye me-1 text-info"></i> {{ $t('Display Preview:') }}
                </span>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-primary px-3 py-1.5 rounded-pill shadow-xs fs-7">
                    <i class="fas fa-folder me-1"></i> {{ data.title }}
                  </span>
                  <span v-if="data.module_name" class="badge bg-light text-secondary border font-monospace">
                    {{ data.module_name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Order & Status -->
    <div class="col-lg-4 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
        <div>
          <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-sliders-h text-warning me-2"></i> {{ $t('Display & Visibility') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <div class="row g-3">
              <!-- Sorting -->
              <Input
                title="Sort Order / Priority"
                field="data.sorting"
                v-model="data.sorting"
                :req="true"
                col="12"
                type="number"
                placeholder="0"
              />

              <!-- Status Box -->
              <div class="col-12">
                <div
                  class="p-3 rounded-3 border transition-all"
                  :class="data.status === 'active' ? 'bg-success-subtle border-success-subtle' : 'bg-danger-subtle border-danger-subtle'"
                >
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                      <div class="d-flex align-items-center gap-1.5">
                        <span class="fw-bold text-dark fs-6">{{ $t('Status') }}</span>
                        <span
                          class="badge rounded-pill px-2 py-0.5"
                          :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                        >
                          {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                        </span>
                      </div>
                      <p class="text-muted small mb-0 mt-0.5">
                        {{ data.status === 'active' ? $t('Visible in product filters & POS catalog') : $t('Hidden from active product catalogs') }}
                      </p>
                    </div>
                    <Switch
                      v-model="data.status"
                      title=""
                      on-label="Active"
                      off-label="Inactive"
                      :req="true"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer bg-light border-top p-2.5 text-muted small text-center">
          <i class="fas fa-check-circle text-success me-1"></i> {{ $t('Lower sort number appears first in lists.') }}
        </div>
      </div>
    </div>

    <!-- Form Action Footer -->
    <template v-slot:form_footer>
      <div class="col-12 mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="text-muted small">
          <span class="text-danger fw-bold">*</span> {{ $t('Required fields must be completed before saving.') }}
        </div>
        <div class="d-flex gap-2">
          <router-link :to="{ name: 'category.index' }" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> {{ $t('Cancel / Back') }}
          </router-link>
          <button type="submit" class="theme_btn px-4 py-2 rounded-pill shadow-sm" :disabled="$root.submit">
            <template v-if="$root.submit">
              <i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Processing...') }}
            </template>
            <template v-else>
              <i :class="$route.params.id ? 'fas fa-save' : 'fas fa-check-circle'" class="me-1"></i>
              {{ $route.params.id ? $t('Update Category') : $t('Save & Register Category') }}
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = "category";

export default {
  computed: {
    modules() {
      const modules = [];
      if (this.categoriesModuleNames && Object.keys(this.categoriesModuleNames).length > 0) {
        this.categoriesModuleNames.forEach(function (type) {
          modules.push({
            name: type.name,
            value: type.value,
          });
        });
      }
      return modules;
    },
  },
  data() {
    return {
      page_title: "Category",
      model: model,
      data: { status: "active", sorting: 0 },
    };
  },
  provide() {
    return {
      validate: this.validation,
    };
  },
  methods: {
    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          console.log(this.validation.allErrors());
          this.$toast(
            "You need to fill " + error + " more empty mandatory fields",
            "warning"
          );
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
      this.page_title = "Category Edit";
      this.setBreadcrumbs(this.model, "edit");
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = "Category Create";
      this.get_sorting(this.ucfirst(this.model));
      this.setBreadcrumbs(this.model, "create");
    }
  },
  validators: {
    "data.module_name": function (value = null) {
      return Validator.value(value)
        .required("Module Name is required")
        .maxLength(190);
    },
    "data.title": function (value = null) {
      return Validator.value(value)
        .required("Title is required")
        .maxLength(190);
    },
    "data.sorting": function (value = null) {
      return Validator.value(value)
        .digit()
        .regex("^[0-9]*$")
        .required("Sorting is required");
    },
    "data.status": function (value = null) {
      return Validator.value(value).required("Status is required");
    },
  },
};
</script>

<style scoped>
.category-hero-banner {
  background: linear-gradient(135deg, #4f46e5 0%, #1e1b4b 100%);
  color: #fff;
  border-radius: 14px;
}

.hero-icon-box {
  width: 64px;
  height: 64px;
}

.bg-gradient-primary-subtle {
  background: linear-gradient(135deg, rgba(238, 242, 255, 0.95) 0%, rgba(243, 244, 246, 0.95) 100%);
}

.header-icon-box {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important;
}

.transition-all {
  transition: all 0.2s ease-in-out;
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
