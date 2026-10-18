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
                <i class="fas fa-balance-scale fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Unit...') }}</h5>
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
                  <span><i class="fas fa-cube me-1 text-warning"></i> Scope: <strong class="text-dark">{{ data.module || "Item" }}</strong></span><span><i class="fas fa-ruler me-1 text-info"></i> Unit: <strong class="text-dark">{{ data.title || "N/A" }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'unit.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-balance-scale fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Create Measurement Unit') }}</h5>
                <small class="text-muted">{{ $t('Define standard units of measure (Piece, Kg, Box, Meter, Liter) for POS billing and inventory calculations') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-ruler-combined me-1 text-info"></i> {{ $t('Unit of Measure') }}
              </span>
              <router-link :to="{ name: 'unit.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Unit Particulars -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-balance-scale text-success me-2"></i> {{ $t('Unit Definition Particulars') }}
          </h6>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Module Scope -->
            <div class="col-md-5 col-12">
              <Select
                title="Applicable Module"
                v-model="data.module"
                field="data.module"
                label="name"
                :reduce="(obj) => obj.value"
                :options="availableModules"
                placeholder="-- Select Module Scope --"
                :closeOnSelect="true"
                col="12"
                :required="true"
              />
            </div>

            <!-- Unit Title -->
            <div class="col-md-7 col-12">
              <Input
                v-model="data.title"
                field="data.title"
                title="Unit Name / Symbol"
                placeholder="e.g. Pcs, Box, Kg, Liter, Meter, Dozen"
                :req="true"
                col="12"
              />
            </div>

            <!-- Quick Suggestion Chips -->
            <div class="col-12 mt-1">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-magic me-1 text-success"></i> {{ $t('Standard Measurement Presets (Click to Fill):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  v-for="u in unitPresets"
                  :key="u.code"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs transition-all hover-unit-chip"
                  @click="data.title = u.code"
                >
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 font-monospace" style="font-size: 10px;">{{ u.code }}</span>
                  <span class="small text-dark fw-medium">{{ u.name }}</span>
                </button>
              </div>
            </div>

            <!-- Live Bill & Stock Representation Preview -->
            <div class="col-12 mt-3" v-if="data.title">
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <span class="small text-muted fw-semibold d-block mb-1">
                    <i class="fas fa-eye me-1 text-info"></i> {{ $t('POS Receipt & Invoice Display Sample:') }}
                  </span>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark px-3 py-1.5 rounded-pill shadow-xs fs-7">
                      Qty: <strong>10 {{ data.title }}</strong> @ ৳ 250 / {{ data.title }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border small">
                      Scope: {{ data.module || 'Item' }}
                    </span>
                  </div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                  <i class="fas fa-check-circle me-1"></i> {{ data.status === 'active' ? 'Active Unit' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Status & Guidelines -->
    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
      <div class="d-flex flex-column gap-3 h-100">
        <!-- Status Card -->
        <div class="card border shadow-sm rounded-3 bg-white">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-toggle-on text-success me-2"></i> {{ $t('Operational Status') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <div class="p-3 rounded-3 border bg-light mb-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold text-dark small">{{ $t('Unit Status') }}</span>
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
                  ? $t('Active units appear in purchase GRN, item cataloging, and POS checkout quantity selections.') 
                  : $t('Disabled units cannot be chosen for new items.') }}
              </p>
            </div>

            <Switch
              v-model="data.status"
              field="data.status"
              title="Toggle Unit Status"
              on-label="Active"
              off-label="Deactive"
              :req="true"
            />
          </div>
        </div>

        <!-- Guidance Card -->
        <div class="card border shadow-sm rounded-3 bg-white flex-grow-1">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-lightbulb text-warning me-2"></i> {{ $t('Usage Tip') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <p class="text-muted small mb-2">
              <i class="fas fa-check text-success me-1.5"></i>
              {{ $t('For retail goods, piece-based units like Pcs or Box are most common.') }}
            </p>
            <p class="text-muted small mb-0">
              <i class="fas fa-check text-success me-1.5"></i>
              {{ $t('For bulk supplies and wholesale, use Kg, Meter, or Liter.') }}
            </p>
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
            <router-link :to="{ name: 'unit.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
            <button type="submit" class="btn btn-success btn-sm px-4 py-1.5 rounded-2 fw-semibold shadow-sm text-white">
              <i class="fas fa-save me-1.5"></i>
              {{ $route.params.id ? $t('Update Unit') : $t('Save Unit') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'unit';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        module: 'Item',
        title: '',
        status: 'active',
      },
      unitPresets: [
        { code: 'Pcs', name: 'Piece / Single' },
        { code: 'Box', name: 'Carton / Box' },
        { code: 'Kg', name: 'Kilogram' },
        { code: 'Gm', name: 'Gram' },
        { code: 'Ltr', name: 'Liter' },
        { code: 'Mtr', name: 'Meter' },
        { code: 'Dzn', name: 'Dozen' },
        { code: 'Pack', name: 'Packet' },
        { code: 'Set', name: 'Set' },
        { code: 'Pair', name: 'Pair' },
      ],
    };
  },

  computed: {
    availableModules() {
      if (this.$root.global && this.$root.global.modules && this.$root.global.modules.length > 0) {
        return this.$root.global.modules;
      }
      return [
        { name: 'Item', value: 'Item' },
        { name: 'Expense', value: 'Expense' },
        { name: 'Purchase', value: 'Purchase' },
      ];
    },
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    resetForm() {
      this.data.module = 'Item';
      this.data.title = '';
      this.data.status = 'active';
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
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }
  },

  validators: {
    'data.module': function (value = null) {
      return Validator.value(value).required('Module is required');
    },
    'data.title': function (value = null) {
      return Validator.value(value).required('Title is required');
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

.unit-hero-banner {
  background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}

.hover-unit-chip {
  transition: all 0.2s ease-in-out;
}

.hover-unit-chip:hover {
  background-color: #d1fae5 !important;
  border-color: #059669 !important;
  color: #065f46 !important;
  transform: translateY(-1px);
}

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
</style>
