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
                <i class="fas fa-palette fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Color...') }}</h5>
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
                  <span><i class="fas fa-sort-numeric-down me-1 text-warning"></i> Sort Priority: <strong class="text-dark font-monospace">#{{ data.sorting ?? 0 }}</strong></span><span><i class="fas fa-swatchbook me-1 text-info"></i> Type: <strong class="text-dark">Product Variant Color</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'color.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-palette fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('New Color Variant') }}</h5>
                <small class="text-muted">{{ $t('Register shade title for product color options, matrix combinations, and POS tags') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-swatchbook me-1 text-info"></i> {{ $t('Color Variant') }}
              </span>
              <router-link :to="{ name: 'color.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Color Identity & Presets -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-paint-brush text-primary me-2"></i> {{ $t('Color Variant Particulars') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Color Name / Title -->
            <Input
              v-model="data.title"
              field="data.title"
              title="Color Name / Title"
              placeholder="e.g. Royal Blue, Midnight Black, Crimson Red, Off-White"
              :req="true"
              col="12"
            />

            <!-- Quick Inspiration Palette Chips -->
            <div class="col-12 mt-2">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-magic me-1 text-primary"></i> {{ $t('Quick Suggestion Chips (Click to Fill):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  v-for="preset in colorPresets"
                  :key="preset.name"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs transition-all hover-chip"
                  @click="data.title = preset.name"
                >
                  <span
                    class="rounded-circle border"
                    :style="{ backgroundColor: preset.hex, width: '13px', height: '13px', display: 'inline-block' }"
                  ></span>
                  <span class="small fw-medium">{{ preset.name }}</span>
                </button>
              </div>
            </div>

            <!-- Informational Banner -->
            <div class="col-12 mt-2">
              <div class="p-3 rounded-3 bg-light border text-muted small d-flex align-items-start gap-2.5">
                <div class="text-primary fs-5 mt-0.5">
                  <i class="fas fa-info-circle"></i>
                </div>
                <div>
                  <strong class="text-dark d-block mb-0.5">{{ $t('Usage in Product Variations') }}</strong>
                  <span>{{ $t('This color will be selectable when creating or editing multi-attribute items, inventory stock units, and POS receipts.') }}</span>
                </div>
              </div>
            </div>

            <!-- Live Preview Chip -->
            <div class="col-12" v-if="data.title">
              <div class="p-2.5 rounded border bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="small text-muted fw-semibold">
                  <i class="fas fa-eye me-1 text-info"></i> {{ $t('Variant Tag Preview:') }}
                </span>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-dark px-3 py-1.5 rounded-pill shadow-xs fs-7 d-flex align-items-center gap-1.5">
                    <i class="fas fa-circle text-info" style="font-size: 8px;"></i>
                    {{ data.title }}
                  </span>
                  <span class="badge bg-success-subtle text-success border border-success-subtle small">
                    {{ data.status === 'active' ? 'Active Variant' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Sorting & Status -->
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
                v-model="data.sorting"
                field="data.sorting"
                title="Sort Order / Priority"
                :req="true"
                type="number"
                placeholder="0"
                col="12"
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
                        <span class="fw-bold text-dark fs-6">{{ $t('Color Status') }}</span>
                        <span
                          class="badge rounded-pill px-2 py-0.5"
                          :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                        >
                          {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                        </span>
                      </div>
                      <p class="text-muted small mb-0 mt-0.5">
                        {{ data.status === 'active' ? $t('Available for product variants & POS') : $t('Hidden from new product creation') }}
                      </p>
                    </div>
                    <Switch
                      v-model="data.status"
                      field="data.status"
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
          <router-link :to="{ name: 'color.index' }" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> {{ $t('Cancel / Back') }}
          </router-link>
          <button type="submit" class="theme_btn px-4 py-2 rounded-pill shadow-sm" :disabled="$root.submit">
            <template v-if="$root.submit">
              <i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Processing...') }}
            </template>
            <template v-else>
              <i :class="$route.params.id ? 'fas fa-save' : 'fas fa-check-circle'" class="me-1"></i>
              {{ $route.params.id ? $t('Update Color Variant') : $t('Save & Register Color') }}
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = "color";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      data: { status: "active", sorting: 0 },
      colorPresets: [
        { name: "Black", hex: "#000000" },
        { name: "White", hex: "#ffffff" },
        { name: "Navy Blue", hex: "#001f3f" },
        { name: "Royal Blue", hex: "#4169e1" },
        { name: "Crimson Red", hex: "#dc143c" },
        { name: "Emerald Green", hex: "#2ecc71" },
        { name: "Dark Gray", hex: "#555555" },
        { name: "Gold", hex: "#ffd700" },
        { name: "Beige", hex: "#f5f5dc" },
        { name: "Maroon", hex: "#800000" },
      ],
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
      this.page_title = this.headline(this.model) + " Edit";
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + " Create";
      this.get_sorting(this.ucfirst(this.model));
    }
  },

  validators: {
    "data.title": function (value = null) {
      return Validator.value(value).required("Title is required");
    },
    "data.sorting": function (value = null) {
      return Validator.value(value)
        .digit()
        .regex("^[0-9]*$")
        .required("Sorting is required");
    },
  },
};
</script>

<style scoped>
.color-hero-banner {
  background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%);
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
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
}

.transition-all {
  transition: all 0.2s ease-in-out;
}

.hover-chip:hover {
  background-color: #e2e8f0 !important;
  transform: translateY(-1px);
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
