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
                <i class="fas fa-user-tag fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Designation...') }}</h5>
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
                  <span><i class="fas fa-money-bill-wave me-1 text-success"></i> Default Base Salary: <strong class="text-dark">৳ {{ Number(data.total_salary || 0).toLocaleString() }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'designation.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Designations') }}
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
                <i class="fas fa-user-tag fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('New Employee Designation') }}</h5>
                <small class="text-muted">{{ $t('Configure employee job role, organizational rank, and default compensation scale') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-briefcase me-1 text-info"></i> {{ $t('Job Roles') }}
              </span>
              <router-link :to="{ name: 'designation.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Designations') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Title & Compensation Breakdown -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-id-card text-primary me-2"></i> {{ $t('Designation & Compensation Scale') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Designation Title -->
            <Input
              v-model="data.title"
              field="data.title"
              title="Designation Title / Role"
              placeholder="e.g. Senior Software Engineer, Store Manager, Sales Executive, Cashier"
              :req="true"
              col="12"
            />

            <!-- Salary Breakdown Fieldset -->
            <div class="col-12 mt-2">
              <div class="p-3 rounded-3 border bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="fw-bold text-dark mb-0">
                    <i class="fas fa-calculator text-success me-1.5"></i> {{ $t('Standard Salary Scale (Optional Auto-Fill for Staff)') }}
                  </h6>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace fw-bold">
                    Total: ৳ {{ Number(data.total_salary || 0).toLocaleString() }}
                  </span>
                </div>
                <div class="row g-2">
                  <Input v-model="data.basic" field="data.basic" title="Basic Salary (৳)" col="3" type="number" :req="false" />
                  <Input v-model="data.house_rent" field="data.house_rent" title="House Rent (৳)" col="3" type="number" :req="false" />
                  <Input v-model="data.medical" field="data.medical" title="Medical (৳)" col="3" type="number" :req="false" />
                  <Input v-model="data.other" field="data.other" title="Other Allowances (৳)" col="3" type="number" :req="false" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Status -->
    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
      <div class="d-flex flex-column gap-3 h-100">
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
                <span class="fw-semibold text-dark small">{{ $t('Designation State') }}</span>
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
                  ? $t('Designation is active and selectable when registering or promoting employees.') 
                  : $t('Disabled designation is hidden from employee profile dropdowns.') }}
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
            <router-link :to="{ name: 'designation.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
              {{ $route.params.id ? $t('Update Designation') : $t('Save Designation') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'designation';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        title: '',
        basic: 0,
        house_rent: 0,
        medical: 0,
        other: 0,
        total_salary: 0,
        status: 'active',
      },
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  watch: {
    'data.basic': 'calculatesalary',
    'data.house_rent': 'calculatesalary',
    'data.medical': 'calculatesalary',
    'data.other': 'calculatesalary',
  },

  methods: {
    resetForm() {
      this.data.title = '';
      this.data.basic = 0;
      this.data.house_rent = 0;
      this.data.medical = 0;
      this.data.other = 0;
      this.data.total_salary = 0;
      this.data.status = 'active';
    },

    calculatesalary: function () {
      const basic = parseInt(this.data.basic) || 0;
      const house = parseInt(this.data.house_rent) || 0;
      const medical = parseInt(this.data.medical) || 0;
      const other = parseInt(this.data.other) || 0;
      this.data.total_salary = basic + house + medical + other;
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
</style>
