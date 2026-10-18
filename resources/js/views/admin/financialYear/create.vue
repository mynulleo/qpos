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
                <i class="fas fa-calendar-alt fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Financial Year...') }}</h5>
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
                  <span><i class="fas fa-calendar-day me-1 text-warning"></i> Period: <strong class="text-dark">{{ data.start_date || "N/A" }} ~ {{ data.end_date || "N/A" }}</strong></span><span><i :class="data.is_closed ? 'fas fa-lock text-danger' : 'fas fa-lock-open text-success'" class="me-1"></i> Books: <strong class="text-dark">{{ data.is_closed ? "Closed" : "Open" }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'financialYear.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-calendar-alt fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Configure Financial Year') }}</h5>
                <small class="text-muted">{{ $t('Establish accounting fiscal periods, ledger balances cutoff, and transaction date validation') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-balance-scale-right me-1 text-info"></i> {{ $t('Fiscal Accounting') }}
              </span>
              <router-link :to="{ name: 'financialYear.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Fiscal Details & Period -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-clock text-primary me-2"></i> {{ $t('Fiscal Period Particulars') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- FY Title -->
            <Input
              v-model="data.title"
              field="data.title"
              title="Fiscal Year Title / Identifier"
              placeholder="e.g. FY 2026-2027, Fiscal 2026, Calendar Year 2026"
              :req="true"
              col="12"
            />

            <!-- Start Date & End Date -->
            <div class="col-md-6 col-12">
              <label class="form-label small fw-semibold text-dark mb-1">
                <i class="fas fa-calendar-day text-success me-1"></i> {{ $t('Start Date') }} <span class="text-danger">*</span>
              </label>
              <date-picker
                id="date1"
                v-model="data.start_date"
                field="data.start_date"
                title="Start Date"
                placeholder="YYYY-MM-DD"
                col="12"
                :req="true"
              />
            </div>

            <div class="col-md-6 col-12">
              <label class="form-label small fw-semibold text-dark mb-1">
                <i class="fas fa-calendar-times text-danger me-1"></i> {{ $t('End Date') }} <span class="text-danger">*</span>
              </label>
              <date-picker
                id="date2"
                v-model="data.end_date"
                field="data.end_date"
                title="End Date"
                placeholder="YYYY-MM-DD"
                col="12"
                :req="true"
              />
            </div>

            <!-- Quick Preset Date Buttons -->
            <div class="col-12 mt-1">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-magic me-1 text-primary"></i> {{ $t('Quick Standard Presets (Click to Auto-Fill Dates):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs hover-preset-chip"
                  @click="applyPreset('bd_current')"
                >
                  <i class="fas fa-landmark text-primary" style="font-size: 10px;"></i>
                  <span class="small fw-medium">BD FY 2026-2027 (01 Jul 2026 ~ 30 Jun 2027)</span>
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs hover-preset-chip"
                  @click="applyPreset('cal_current')"
                >
                  <i class="fas fa-calendar text-info" style="font-size: 10px;"></i>
                  <span class="small fw-medium">Calendar 2026 (01 Jan 2026 ~ 31 Dec 2026)</span>
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs hover-preset-chip"
                  @click="applyPreset('bd_next')"
                >
                  <i class="fas fa-step-forward text-success" style="font-size: 10px;"></i>
                  <span class="small fw-medium">Next BD FY (01 Jul 2027 ~ 30 Jun 2028)</span>
                </button>
              </div>
            </div>

            <!-- Period Summary Card -->
            <div class="col-12 mt-3" v-if="data.start_date && data.end_date">
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <span class="small text-muted fw-semibold d-block mb-1">
                    <i class="fas fa-chart-line me-1 text-info"></i> {{ $t('Configured Accounting Span:') }}
                  </span>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark px-3 py-1.5 rounded-pill shadow-xs fs-7">
                      {{ data.start_date }} &rarr; {{ data.end_date }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border small">
                      {{ data.title || 'FY Period' }}
                    </span>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span v-if="data.is_current" class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small fw-bold">
                    <i class="fas fa-star me-1"></i> {{ $t('Active FY') }}
                  </span>
                  <span v-if="data.is_closed" class="badge bg-danger text-white px-2.5 py-1 rounded-pill small">
                    <i class="fas fa-lock me-1"></i> {{ $t('Books Closed') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Accounting Flags & Status -->
    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
      <div class="d-flex flex-column gap-3 h-100">
        <!-- Fiscal Controls Card -->
        <div class="card border shadow-sm rounded-3 bg-white">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-sliders-h text-primary me-2"></i> {{ $t('Accounting Parameters') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <div class="d-flex flex-column gap-3">
              <!-- Is Current Toggle -->
              <div class="p-2.5 rounded-3 border bg-light">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-semibold text-dark">{{ $t('Is Current Financial Year') }}</span>
                  <span :class="data.is_current ? 'badge bg-warning text-dark' : 'badge bg-secondary'">
                    {{ data.is_current ? 'YES' : 'NO' }}
                  </span>
                </div>
                <p class="text-muted small mb-2" style="font-size: 0.75rem;">
                  {{ $t('Sets this period as default for voucher entries, statements, and new sales invoices.') }}
                </p>
                <SwitchBoolean
                  v-model="data.is_current"
                  field="data.is_current"
                  title="Make Default Active FY"
                  on-label="ON"
                  off-label="OFF"
                  :req="true"
                  col="12"
                />
              </div>

              <!-- Is Closed Toggle -->
              <div class="p-2.5 rounded-3 border bg-light">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-semibold text-dark">{{ $t('Is Closed (Year-End Lock)') }}</span>
                  <span :class="data.is_closed ? 'badge bg-danger' : 'badge bg-success'">
                    {{ data.is_closed ? 'LOCKED' : 'OPEN' }}
                  </span>
                </div>
                <p class="text-muted small mb-2" style="font-size: 0.75rem;">
                  {{ $t('Prevents back-dated debit/credit voucher postings after year-end financial closing.') }}
                </p>
                <SwitchBoolean
                  v-model="data.is_closed"
                  field="data.is_closed"
                  title="Lock Fiscal Transactions"
                  on-label="ON"
                  off-label="OFF"
                  :req="true"
                  col="12"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Operational Status Card -->
        <div class="card border shadow-sm rounded-3 bg-white flex-grow-1">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-toggle-on text-success me-2"></i> {{ $t('Operational Status') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <Switch
              v-model="data.status"
              field="data.status"
              title="Operational State"
              on-label="Active"
              off-label="Deactive"
              :req="true"
              col="12"
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
            <router-link :to="{ name: 'financialYear.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
              {{ $route.params.id ? $t('Update Financial Year') : $t('Save Financial Year') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'financialYear';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        title: '',
        start_date: '',
        end_date: '',
        is_current: 0,
        is_closed: 0,
        status: 'active',
      },
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    applyPreset(type) {
      if (type === 'bd_current') {
        this.data.title = 'FY 2026-2027';
        this.data.start_date = '2026-07-01';
        this.data.end_date = '2027-06-30';
      } else if (type === 'cal_current') {
        this.data.title = 'FY 2026';
        this.data.start_date = '2026-01-01';
        this.data.end_date = '2026-12-31';
      } else if (type === 'bd_next') {
        this.data.title = 'FY 2027-2028';
        this.data.start_date = '2027-07-01';
        this.data.end_date = '2028-06-30';
      }
    },

    resetForm() {
      this.data.title = '';
      this.data.start_date = '';
      this.data.end_date = '';
      this.data.is_current = 0;
      this.data.is_closed = 0;
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
    'data.title': function (value = null) {
      return Validator.value(value).required('Title is required');
    },
    'data.start_date': function (value = null) {
      return Validator.value(value).required('Start Date is required');
    },
    'data.end_date': function (value = null) {
      return Validator.value(value).required('End Date is required');
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

.fy-hero-banner {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}

.hover-preset-chip {
  transition: all 0.2s ease-in-out;
}

.hover-preset-chip:hover {
  background-color: #e2e8f0 !important;
  border-color: #334155 !important;
  color: #0f172a !important;
  transform: translateY(-1px);
}

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
</style>
