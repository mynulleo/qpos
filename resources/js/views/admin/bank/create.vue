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
                <i class="fas fa-university fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.bank_name || $t('Loading Bank...') }}</h5>
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
                  <span><i class="fas fa-credit-card me-1 text-warning"></i> Module: <strong class="text-dark">Banking & Financial Accounts</strong></span><span><i class="fas fa-check-shield me-1 text-info"></i> Category: <strong class="text-dark">Commercial Schedule Bank</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'bank.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-university fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Register Banking Institution') }}</h5>
                <small class="text-muted">{{ $t('Register commercial banks and financial institutions for linked bank accounts and cheque clearing') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-landmark me-1 text-info"></i> {{ $t('Banking Gateway') }}
              </span>
              <router-link :to="{ name: 'bank.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Bank Particulars & Presets -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-university text-success me-2"></i> {{ $t('Bank Institution Particulars') }}
          </h6>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Bank Name Input -->
            <Input
              v-model="data.bank_name"
              field="data.bank_name"
              title="Bank Name / Institution Title"
              placeholder="e.g. Dutch-Bangla Bank PLC, BRAC Bank PLC, Islami Bank Bangladesh"
              :req="true"
              col="12"
            />

            <!-- Quick Suggestion Chips for Bangladesh Banks -->
            <div class="col-12 mt-1">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-building me-1 text-success"></i> {{ $t('Popular Commercial Banks (Click to Fill):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  v-for="b in popularBanks"
                  :key="b"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs hover-bank-chip"
                  @click="data.bank_name = b"
                >
                  <i class="fas fa-university text-success" style="font-size: 10px;"></i>
                  <span class="small fw-medium">{{ b }}</span>
                </button>
              </div>
            </div>

            <!-- Stylized Bank Passbook Mockup Preview -->
            <div class="col-12 mt-3" v-if="data.bank_name">
              <div class="p-3 rounded-4 bg-gradient-dark text-white shadow-sm border position-relative overflow-hidden" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-university fs-4 text-warning"></i>
                    <span class="fw-bold fs-6 tracking-wide text-uppercase">{{ data.bank_name }}</span>
                  </div>
                  <span class="badge bg-white bg-opacity-20 text-white font-monospace small px-2 py-1">
                    {{ data.status === 'active' ? 'Active Gateway' : 'Inactive' }}
                  </span>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-white border-opacity-20 text-white-50 small">
                  <span>Linked Branch Accounts: <strong class="text-white">Supported</strong></span>
                  <span>Cheque & Fund Transfer: <strong class="text-white">Enabled</strong></span>
                </div>
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
                <span class="fw-semibold text-dark small">{{ $t('Institution Status') }}</span>
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
                  ? $t('Active bank is selectable when registering company bank accounts and cheque payments.') 
                  : $t('Disabled bank is hidden from new account forms.') }}
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

        <!-- Hint Card -->
        <div class="card border shadow-sm rounded-3 bg-white flex-grow-1">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-info-circle text-info me-2"></i> {{ $t('Account Structure') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <p class="text-muted small mb-0">
              {{ $t('After registering this bank, navigate to Bank Accounts under Accounts module to enter account numbers, branch names, and opening balances.') }}
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
            <router-link :to="{ name: 'bank.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
              {{ $route.params.id ? $t('Update Bank') : $t('Save Bank') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'bank';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        bank_name: '',
        status: 'active',
      },
      popularBanks: [
        'Dutch-Bangla Bank (DBBL)',
        'BRAC Bank',
        'Islami Bank Bangladesh',
        'City Bank',
        'Eastern Bank (EBL)',
        'Prime Bank',
        'Sonali Bank',
        'Mutual Trust Bank (MTB)',
        'United Commercial Bank (UCB)',
        'Standard Chartered',
      ],
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    resetForm() {
      this.data.bank_name = '';
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
    'data.bank_name': function (value = null) {
      return Validator.value(value).required('Bank Name is required');
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

.bank-hero-banner {
  background: linear-gradient(135deg, #047857 0%, #10b981 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}

.hover-bank-chip {
  transition: all 0.2s ease-in-out;
}

.hover-bank-chip:hover {
  background-color: #d1fae5 !important;
  border-color: #047857 !important;
  color: #065f46 !important;
  transform: translateY(-1px);
}

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
</style>
