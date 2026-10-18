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
                <i class="fas fa-sliders-h fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Parameter...') }}</h5>
                  <span class="badge bg-white text-primary border shadow-xs px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                    #{{ $route.params.id }}
                  </span>
                  <span
                    v-if="data.is_basic == 1"
                    class="badge bg-warning text-dark font-monospace px-2.5 py-1 rounded-pill small fw-bold"
                  >
                    <i class="fas fa-star me-1"></i> {{ $t('Foundation Basic Salary') }}
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
                  <span>
                    <i class="fas fa-percent me-1 text-info"></i> Ratio:
                    <strong class="text-dark">{{ data.is_basic == 1 ? '100% (Base Foundation)' : (data.percentage_of_basic ? (data.percentage_of_basic + '% of Basic') : 'Fixed / Custom') }}</strong>
                  </span>
                  <span>
                    <i class="fas fa-sort-numeric-down me-1 text-warning"></i> Sort Priority:
                    <strong class="text-dark font-monospace">#{{ data.sorting ?? 0 }}</strong>
                  </span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'salaryParameter.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Parameters') }}
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
                <i class="fas fa-sliders-h fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('New Salary Structure Parameter') }}</h5>
                <small class="text-muted">{{ $t('Configure payroll breakdown components, basic salary ratios, and automated allowance percentages') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-calculator me-1 text-success"></i> {{ $t('Payroll Parameters') }}
              </span>
              <router-link :to="{ name: 'salaryParameter.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Parameters') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Parameter Particulars & Rules -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-calculator text-primary me-2"></i> {{ $t('Salary Component Particulars') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Component Title Input -->
            <Input
              v-model="data.title"
              field="data.title"
              title="Component Name / Title"
              placeholder="e.g. Basic Salary, House Rent Allowance, Medical Allowance, Conveyance"
              :req="true"
              col="12"
            />

            <!-- Quick Suggestion Chips for Standard HR Components -->
            <div class="col-12 mt-1">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-lightbulb me-1 text-warning"></i> {{ $t('Standard Salary Breakdown Presets (Click to Auto-Configure):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  v-for="p in parameterPresets"
                  :key="p.title"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs transition-all hover-chip"
                  @click="applyPreset(p)"
                >
                  <span
                    class="badge rounded-pill px-1.5 py-0.5 font-monospace"
                    :class="p.is_basic ? 'bg-warning text-dark' : 'bg-primary-subtle text-primary'"
                    style="font-size: 10px;"
                  >
                    {{ p.is_basic ? 'BASE' : (p.percentage ? p.percentage + '%' : 'FIXED') }}
                  </span>
                  <span class="small fw-medium">{{ p.title }}</span>
                </button>
              </div>
            </div>

            <!-- Calculation Logic Box -->
            <div class="col-12 mt-2">
              <div class="p-3 rounded-3 border bg-light">
                <h6 class="fw-bold text-dark mb-2">
                  <i class="fas fa-cog text-primary me-1.5"></i> {{ $t('Payroll Calculation Rule') }}
                </h6>
                <div class="row g-3 align-items-center">
                  <!-- Is Basic Toggle -->
                  <div class="col-md-5 col-12">
                    <label class="small fw-semibold text-dark mb-1 d-block">{{ $t('Is This The Base Salary?') }}</label>
                    <SwitchBoolean
                      v-model="data.is_basic"
                      field="data.is_basic"
                      title="Is Basic Salary"
                      on-label="Yes (Foundation)"
                      off-label="No (Allowance/Deduction)"
                      :req="true"
                      col="12"
                    />
                  </div>

                  <!-- Percentage of Basic (if not basic) -->
                  <div class="col-md-7 col-12" v-if="data.is_basic == 0">
                    <Input
                      v-model="data.percentage_of_basic"
                      field="data.percentage_of_basic"
                      title="Percentage of Basic (%)"
                      placeholder="e.g. 50 (for 50% of Basic), 10, 0"
                      type="number"
                      :req="false"
                      col="12"
                    />
                    <div class="form-text text-muted small mt-1 ms-1">
                      <i class="fas fa-info-circle me-1 text-info"></i>
                      {{ $t('If specified, employee monthly salary will automatically calculate this allowance based on their base salary.') }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Live Calculation Formula Simulation -->
            <div class="col-12 mt-2" v-if="data.title">
              <div class="p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-2xs">
                <div>
                  <span class="small text-muted fw-semibold d-block mb-1">
                    <i class="fas fa-eye me-1 text-info"></i> {{ $t('Calculation Simulation (Assuming ৳ 30,000 Base Salary):') }}
                  </span>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark px-3 py-1.5 rounded-pill shadow-xs fs-7">
                      <template v-if="data.is_basic == 1">
                        <strong>{{ data.title }}</strong>: 100% Base (৳ 30,000.00)
                      </template>
                      <template v-else-if="Number(data.percentage_of_basic) > 0">
                        <strong>{{ data.title }}</strong>: {{ data.percentage_of_basic }}% &times; ৳ 30,000 = ৳ {{ (30000 * Number(data.percentage_of_basic) / 100).toLocaleString() }}
                      </template>
                      <template v-else>
                        <strong>{{ data.title }}</strong>: {{ $t('Fixed Allowance / Independent Amount') }}
                      </template>
                    </span>
                  </div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                  <i class="fas fa-check-circle me-1"></i> {{ data.status === 'active' ? $t('Active Parameter') : $t('Inactive') }}
                </span>
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
              :req="false"
              col="12"
            />
            <div class="form-text text-muted small mt-1 ms-1">
              <i class="fas fa-info-circle me-1 text-info"></i>
              {{ $t('Orders components in salary slip (1: Basic, 2: House Rent, 3: Medical).') }}
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
                <span class="fw-semibold text-dark small">{{ $t('Parameter State') }}</span>
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
                  ? $t('Active parameters appear in employee compensation cards and automated salary sheets.') 
                  : $t('Disabled parameter is excluded from new employee profiles.') }}
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
            <router-link :to="{ name: 'salaryParameter.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
            <button type="submit" class="btn btn-primary btn-sm px-4 py-1.5 rounded-2 fw-semibold shadow-sm">
              <i class="fas fa-save me-1.5"></i>
              {{ $route.params.id ? $t('Update Parameter') : $t('Save Parameter') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'salaryParameter';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        title: '',
        is_basic: 0,
        percentage_of_basic: '',
        sorting: 0,
        status: 'active',
      },
      parameterPresets: [
        { title: 'Basic Salary', is_basic: 1, percentage: null, sorting: 1 },
        { title: 'House Rent Allowance', is_basic: 0, percentage: 50, sorting: 2 },
        { title: 'Medical Allowance', is_basic: 0, percentage: 10, sorting: 3 },
        { title: 'Conveyance Allowance', is_basic: 0, percentage: 0, sorting: 4 },
        { title: 'Festival Bonus', is_basic: 0, percentage: 100, sorting: 5 },
        { title: 'Provident Fund (Staff)', is_basic: 0, percentage: 10, sorting: 6 },
      ],
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    applyPreset(p) {
      this.data.title = p.title;
      this.data.is_basic = p.is_basic;
      this.data.percentage_of_basic = p.percentage !== null ? p.percentage : '';
      if (!this.$route.params.id && p.sorting) {
        this.data.sorting = p.sorting;
      }
    },

    resetForm() {
      this.data.title = '';
      this.data.is_basic = 0;
      this.data.percentage_of_basic = '';
      this.data.status = 'active';
      this.get_sorting('SalaryParameter');
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
      this.get_sorting('SalaryParameter');
    }
  },

  validators: {
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

.hover-chip {
  transition: all 0.2s ease-in-out;
}

.hover-chip:hover {
  background-color: #e0e7ff !important;
  border-color: #6366f1 !important;
  color: #4338ca !important;
  transform: translateY(-1px);
}

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
</style>
