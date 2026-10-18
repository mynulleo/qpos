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
                <i class="fas fa-coins fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Loading Currency...') }}</h5>
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
                  <span><i class="fas fa-signature me-1 text-warning"></i> Symbol: <strong class="text-dark fs-6">{{ data.symbol || "N/A" }}</strong></span><span><i class="fas fa-receipt me-1 text-info"></i> Sample: <strong class="text-dark">{{ data.symbol || "" }} 1,250.00 {{ data.short_name || "" }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'currency.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-coins fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Configure Currency') }}</h5>
                <small class="text-muted">{{ $t('Define monetary currencies, ISO codes, and symbols for POS receipts and customer invoices') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-money-bill-wave me-1 text-info"></i> {{ $t('Monetary Currency') }}
              </span>
              <router-link :to="{ name: 'currency.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Currency Particulars & Presets -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-coins text-warning me-2"></i> {{ $t('Monetary Particulars') }}
          </h6>
          <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Title -->
            <div class="col-md-6 col-12">
              <Input
                v-model="data.title"
                field="data.title"
                title="Currency Name / Full Title"
                placeholder="e.g. Bangladeshi Taka, US Dollar, Euro"
                :req="true"
                col="12"
              />
            </div>

            <!-- Short Name -->
            <div class="col-md-3 col-6">
              <Input
                v-model="data.short_name"
                field="data.short_name"
                title="ISO Code"
                placeholder="e.g. BDT, USD, EUR"
                :req="true"
                col="12"
              />
            </div>

            <!-- Symbol -->
            <div class="col-md-3 col-6">
              <Input
                v-model="data.symbol"
                field="data.symbol"
                title="Symbol"
                placeholder="e.g. ৳, $, €"
                :req="true"
                col="12"
              />
            </div>

            <!-- Quick Suggestion Chips for Global Currencies -->
            <div class="col-12 mt-1">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-globe me-1 text-warning"></i> {{ $t('Standard Global Currencies (Click to Fill):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  v-for="c in currencyPresets"
                  :key="c.code"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs hover-currency-chip"
                  @click="applyCurrencyPreset(c)"
                >
                  <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-1.5 py-0.5 font-monospace fw-bold">{{ c.symbol }}</span>
                  <span class="small fw-medium">{{ c.title }} ({{ c.code }})</span>
                </button>
              </div>
            </div>

            <!-- Live Receipt Preview Card -->
            <div class="col-12 mt-3" v-if="data.symbol || data.short_name">
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <span class="small text-muted fw-semibold d-block mb-1">
                    <i class="fas fa-file-invoice-dollar me-1 text-info"></i> {{ $t('POS Receipt & Price Tag Sample:') }}
                  </span>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark px-3 py-1.5 rounded-pill shadow-xs fs-6">
                      <span class="text-warning fw-bold me-1">{{ data.symbol }}</span> 3,450.00 <span class="text-white-50 small ms-1">{{ data.short_name }}</span>
                    </span>
                    <span v-if="data.is_default" class="badge bg-warning text-dark border small fw-bold">
                      {{ $t('Default Store Currency') }}
                    </span>
                  </div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                  <i class="fas fa-check-circle me-1"></i> {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Default Flag & Status -->
    <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
      <div class="d-flex flex-column gap-3 h-100">
        <!-- Default Currency Card -->
        <div class="card border shadow-sm rounded-3 bg-white">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-star text-warning me-2"></i> {{ $t('Primary System Setting') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <div class="p-2.5 rounded-3 border bg-light mb-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="small fw-semibold text-dark">{{ $t('Is Default Currency') }}</span>
                <span :class="data.is_default ? 'badge bg-warning text-dark fw-bold' : 'badge bg-secondary'">
                  {{ data.is_default ? 'YES' : 'NO' }}
                </span>
              </div>
              <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                {{ $t('If enabled, all ledger balance sheets, POS product price tags, and profit reports default to this currency.') }}
              </p>
            </div>

            <SwitchBoolean
              v-model="data.is_default"
              field="data.is_default"
              title="Set As Default System Currency"
              on-label="Yes"
              off-label="No"
              col="12"
            />
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
            <router-link :to="{ name: 'currency.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
            <button type="submit" class="btn btn-warning btn-sm px-4 py-1.5 rounded-2 fw-semibold shadow-sm text-dark">
              <i class="fas fa-save me-1.5"></i>
              {{ $route.params.id ? $t('Update Currency') : $t('Save Currency') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'currency';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        title: '',
        short_name: '',
        symbol: '',
        is_default: 0,
        status: 'active',
      },
      currencyPresets: [
        { title: 'Bangladeshi Taka', code: 'BDT', symbol: '৳' },
        { title: 'US Dollar', code: 'USD', symbol: '$' },
        { title: 'Euro', code: 'EUR', symbol: '€' },
        { title: 'British Pound', code: 'GBP', symbol: '£' },
        { title: 'Saudi Riyal', code: 'SAR', symbol: '﷼' },
        { title: 'UAE Dirham', code: 'AED', symbol: 'د.إ' },
        { title: 'Indian Rupee', code: 'INR', symbol: '₹' },
        { title: 'Canadian Dollar', code: 'CAD', symbol: '$' },
      ],
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    applyCurrencyPreset(c) {
      this.data.title = c.title;
      this.data.short_name = c.code;
      this.data.symbol = c.symbol;
    },

    resetForm() {
      this.data.title = '';
      this.data.short_name = '';
      this.data.symbol = '';
      this.data.is_default = 0;
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
    'data.short_name': function (value = null) {
      return Validator.value(value).required('Short Name is required');
    },
    'data.symbol': function (value = null) {
      return Validator.value(value).required('Symbol is required');
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

.currency-hero-banner {
  background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}

.hover-currency-chip {
  transition: all 0.2s ease-in-out;
}

.hover-currency-chip:hover {
  background-color: #fef3c7 !important;
  border-color: #b45309 !important;
  color: #78350f !important;
  transform: translateY(-1px);
}

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
</style>
