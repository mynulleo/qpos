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
                <i class="fas fa-layer-group fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Series...') }}</h5>
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
                  <span><i class="fas fa-sort-numeric-down me-1 text-warning"></i> Sort Priority: <strong class="text-dark font-monospace">#{{ data.sorting ?? 0 }}</strong></span><span v-if="selectedBrandTitle"><i class="fas fa-tag me-1 text-info"></i> Brand: <strong class="text-dark">{{ selectedBrandTitle }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'series.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-layer-group fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Create Product Series / Line') }}</h5>
                <small class="text-muted">{{ $t('Group product models under a brand line for structured POS cataloging') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-stream me-1 text-info"></i> {{ $t('Product Series') }}
              </span>
              <router-link :to="{ name: 'series.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Series Definition -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-sitemap text-info me-2"></i> {{ $t('Series Hierarchy & Details') }}
          </h6>
          <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Category Filter (Cascades to Brands) -->
            <div class="col-md-6 col-12">
              <Select
                title="Filter by Category (Optional)"
                v-model="data.category_id"
                field="data.category_id"
                label="title"
                :reduce="(obj) => obj.id"
                :options="categories"
                placeholder="-- Select Category to Filter Brands --"
                :closeOnSelect="true"
                :required="false"
                col="12"
              />
            </div>

            <!-- Brand Selection -->
            <div class="col-md-6 col-12">
              <Select
                title="Parent Brand"
                v-model="data.brand_id"
                field="data.brand_id"
                label="title"
                :reduce="(obj) => obj.id"
                :options="brands"
                placeholder="-- Select Brand --"
                :closeOnSelect="true"
                :required="true"
                col="12"
              />
            </div>

            <!-- Series Title -->
            <Input
              v-model="data.title"
              field="data.title"
              title="Series / Model Line Title"
              placeholder="e.g. Galaxy S24, iPhone 16 Pro, IdeaPad, Pavilion"
              :req="true"
              col="12"
            />

            <!-- Description Textarea -->
            <Textarea
              v-model="data.description"
              field="data.description"
              :required="false"
              title="Series Description / Specifications Note"
              placeholder="Add optional notes, release generation, or product tier details..."
              col="12"
            />

            <!-- Live Hierarchy Breadcrumb Preview -->
            <div class="col-12 mt-2" v-if="data.title || data.brand_id">
              <div class="p-3 rounded-3 border bg-light">
                <span class="small text-muted fw-semibold d-block mb-1.5">
                  <i class="fas fa-route me-1 text-primary"></i> {{ $t('Catalog Hierarchy Preview:') }}
                </span>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-0 bg-transparent p-0 small">
                    <li class="breadcrumb-item text-muted">
                      <i class="fas fa-folder me-1 text-warning"></i>
                      {{ selectedCategoryTitle || 'Any Category' }}
                    </li>
                    <li class="breadcrumb-item text-dark fw-medium">
                      <i class="fas fa-copyright me-1 text-primary"></i>
                      {{ selectedBrandTitle || 'Select Brand' }}
                    </li>
                    <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">
                      <i class="fas fa-layer-group me-1"></i>
                      {{ data.title || 'New Series' }}
                    </li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Sorting & Status -->
    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
      <div class="d-flex flex-column gap-3 h-100">
        <!-- Display Order & Priority Card -->
        <div class="card border shadow-sm rounded-3 bg-white">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-sort-amount-down text-warning me-2"></i> {{ $t('Display Priority') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <Input
              v-model="data.sorting"
              field="data.sorting"
              title="Sort Sequence Number"
              placeholder="e.g. 1, 2, 3..."
              type="number"
              :req="true"
              col="12"
            />
            <div class="form-text text-muted small mt-1 ms-1">
              <i class="fas fa-info-circle me-1 text-info"></i>
              {{ $t('Lower number displays first in POS series filters.') }}
            </div>
          </div>
        </div>

        <!-- Status Card -->
        <div class="card border shadow-sm rounded-3 bg-white flex-grow-1">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-toggle-on text-success me-2"></i> {{ $t('Operational Status') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <div class="p-3 rounded-3 border bg-light mb-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold text-dark small">{{ $t('Series State') }}</span>
                <span
                  class="badge rounded-pill px-2.5 py-1 text-uppercase"
                  :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'"
                  style="font-size: 0.75rem;"
                >
                  <i :class="data.status === 'active' ? 'fas fa-dot-circle' : 'fas fa-circle'" class="me-1"></i>
                  {{ data.status === 'active' ? $t('Live / Active') : $t('Disabled') }}
                </span>
              </div>
              <p class="text-muted small mb-0">
                {{ data.status === 'active' 
                  ? $t('Series line is active and available for item cataloging and POS filtering.') 
                  : $t('Disabled series cannot be assigned to new stock items.') }}
              </p>
            </div>

            <Switch
              v-model="data.status"
              field="data.status"
              title="Toggle Active Status"
              on-label="Active"
              off-label="Deactive"
              :req="true"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons Footer -->
    <div class="col-12 mt-3">
      <div class="card border shadow-sm rounded-3 bg-white">
        <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="text-muted small">
            <i class="fas fa-asterisk text-danger me-1" style="font-size: 8px;"></i>
            {{ $t('Fields marked with red asterisk are mandatory.') }}
          </div>
          <div class="d-flex align-items-center gap-2">
            <router-link :to="{ name: 'series.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
              <i class="fas fa-times me-1"></i> {{ $t('Cancel') }}
            </router-link>
            <button
              type="button"
              class="btn btn-light border btn-sm px-3 py-1.5 rounded-2 text-muted"
              @click="resetForm"
              v-if="!$route.params.id"
            >
              <i class="fas fa-redo me-1"></i> {{ $t('Reset') }}
            </button>
            <button type="submit" class="btn btn-info btn-sm px-4 py-1.5 rounded-2 fw-semibold shadow-sm text-white">
              <i class="fas fa-save me-1.5"></i>
              {{ $route.params.id ? $t('Update Series') : $t('Save Series') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'series';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        category_id: null,
        brand_id: null,
        title: '',
        description: '',
        status: 'active',
        sorting: 0,
      },
      categories: [],
      brands: [],
    };
  },

  computed: {
    selectedCategoryTitle() {
      if (!this.data.category_id || !this.categories.length) return null;
      const found = this.categories.find((c) => c.id === this.data.category_id);
      return found ? found.title : null;
    },
    selectedBrandTitle() {
      if (!this.data.brand_id || !this.brands.length) return null;
      const found = this.brands.find((b) => b.id === this.data.brand_id);
      return found ? found.title : null;
    },
  },

  watch: {
    'data.category_id'(newVal, oldVal) {
      this.getBrands(newVal);
      if (oldVal && newVal !== oldVal) {
        this.data.brand_id = null;
      }
    },
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    resetForm() {
      this.data.category_id = null;
      this.data.brand_id = null;
      this.data.title = '';
      this.data.description = '';
      this.data.status = 'active';
      this.get_sorting(this.ucfirst(this.model));
      this.getBrands();
    },

    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          console.log(this.validation.allErrors());
          this.$toast(
            'You need to fill ' + error + ' more empty mandatory fields',
            'warning'
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

    getCategories() {
      let module = 'Item';
      axios.get(`getcategories/${module}`)
        .then((response) => {
          this.categories = response.data;
        });
    },

    getBrands(categoryId = null) {
      const catId = categoryId || this.data.category_id;
      const url = catId ? `getbrands/${catId}` : `getbrands`;
      axios.get(url)
        .then((response) => {
          this.brands = response.data;
        });
    },
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`).then(() => {
        this.getBrands(this.data.category_id);
      });
    } else {
      this.page_title = this.headline(this.model) + ' Create';
      this.get_sorting(this.ucfirst(this.model));
      this.getBrands();
    }
    this.getCategories();
  },

  validators: {
    'data.brand_id': function (value = null) {
      return Validator.value(value).required('Brand is required');
    },
    'data.title': function (value = null) {
      return Validator.value(value).required('Title is required');
    },
    'data.sorting': function (value = null) {
      return Validator.value(value)
        .digit()
        .regex('^[0-9]*$')
        .required('Sorting is required');
    },
  },
};
</script>

<style scoped>

.bg-gradient-primary-subtle {
  background: linear-gradient(135deg, rgba(238, 242, 255, 0.95) 0%, rgba(243, 244, 246, 0.95) 100%) !important;
}

.header-icon-box {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important;
}

.series-hero-banner {
  background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}
</style>
