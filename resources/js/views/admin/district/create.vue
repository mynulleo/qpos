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
                <i class="fas fa-map-marked-alt fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.district_name || $t('Loading District...') }}</h5>
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
                  <span><i class="fas fa-globe-asia me-1 text-warning"></i> Country: <strong class="text-dark">Bangladesh</strong></span><span><i class="fas fa-map-pin me-1 text-info"></i> Scope: <strong class="text-dark">Administrative District / Zila</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'district.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-map-marked-alt fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Register District / Zila') }}</h5>
                <small class="text-muted">{{ $t('Configure administrative districts for regional customer profiles, delivery rates, and area mapping') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-map-marker-alt me-1 text-info"></i> {{ $t('Geographic Zone') }}
              </span>
              <router-link :to="{ name: 'district.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): District Particulars & Quick Chips -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-map-pin text-primary me-2"></i> {{ $t('District Particulars') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- District Name Input -->
            <Input
              v-model="data.district_name"
              field="data.district_name"
              title="District Name / Title"
              placeholder="e.g. Dhaka, Chittagong, Sylhet, Rajshahi, Khulna"
              :req="true"
              col="12"
            />

            <!-- Quick Suggestion Chips for Major BD Districts -->
            <div class="col-12 mt-1">
              <label class="small text-muted fw-semibold mb-1 d-block">
                <i class="fas fa-map me-1 text-primary"></i> {{ $t('Major Divisions & Districts (Click to Fill):') }}
              </label>
              <div class="d-flex flex-wrap gap-2 pt-1">
                <button
                  type="button"
                  v-for="d in popularDistricts"
                  :key="d"
                  class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 shadow-2xs hover-district-chip"
                  @click="data.district_name = d"
                >
                  <i class="fas fa-map-pin text-primary" style="font-size: 10px;"></i>
                  <span class="small fw-medium">{{ d }}</span>
                </button>
              </div>
            </div>

            <!-- Live Location Tag Preview -->
            <div class="col-12 mt-3" v-if="data.district_name">
              <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <span class="small text-muted fw-semibold d-block mb-1">
                    <i class="fas fa-eye me-1 text-info"></i> {{ $t('Geographical Representation:') }}
                  </span>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-3 py-1.5 rounded-pill shadow-xs fs-7 d-flex align-items-center gap-1.5">
                      <i class="fas fa-map-marker-alt text-warning"></i>
                      {{ data.district_name }}, Bangladesh
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border small">
                      {{ $t('Parent District') }}
                    </span>
                  </div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                  <i class="fas fa-check-circle me-1"></i> {{ data.status === 'active' ? $t('Active District') : $t('Inactive') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Status & Areas Guide -->
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
                <span class="fw-semibold text-dark small">{{ $t('District State') }}</span>
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
                  ? $t('District is active and available in client billing, supplier shipping, and employee records.') 
                  : $t('Disabled district will be hidden from location dropdowns.') }}
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

        <!-- Next Steps Card -->
        <div class="card border shadow-sm rounded-3 bg-white flex-grow-1">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-info-circle text-info me-2"></i> {{ $t('Hierarchical Mapping') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <p class="text-muted small mb-0">
              {{ $t('Next, add specific Areas / Upazilas linked to this district to provide granular delivery choices for e-commerce and POS customer orders.') }}
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
            <router-link :to="{ name: 'district.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
              {{ $route.params.id ? $t('Update District') : $t('Save District') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'district';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        district_name: '',
        status: 'active',
      },
      popularDistricts: [
        'Dhaka',
        'Chattogram',
        'Sylhet',
        'Rajshahi',
        'Khulna',
        'Barishal',
        'Rangpur',
        'Mymensingh',
        'Cumilla',
        'Gazipur',
        'Narayanganj',
        'Bogura',
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
      this.data.district_name = '';
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
    'data.district_name': function (value = null) {
      return Validator.value(value).required('District Name is required');
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

.district-hero-banner {
  background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}

.hover-district-chip {
  transition: all 0.2s ease-in-out;
}

.hover-district-chip:hover {
  background-color: #dbeafe !important;
  border-color: #1d4ed8 !important;
  color: #1e40af !important;
  transform: translateY(-1px);
}

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
</style>
