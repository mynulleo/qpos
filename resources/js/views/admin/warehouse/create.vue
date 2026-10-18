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
                <i class="fas fa-warehouse fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.name || $t('Loading Warehouse...') }}</h5>
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
                  <span><i class="fas fa-code-branch me-1 text-warning"></i> Branch: <strong class="text-dark">{{ getBranchName(data.branch_id) }}</strong></span><span><i class="fas fa-boxes me-1 text-info"></i> Capacity: <strong class="text-dark">{{ data.capacity || "N/A" }}</strong></span><span v-if="data.contact_person"><i class="fas fa-user-tie me-1 text-success"></i> In-Charge: <strong class="text-dark">{{ data.contact_person }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'warehouse.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-warehouse fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('New Warehouse & Depot Facility') }}</h5>
                <small class="text-muted">{{ $t('Configure storage facilities, physical depot locations, inventory limits, and designated warehouse managers') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-boxes me-1 text-info"></i> {{ $t('Storage & Logistics') }}
              </span>
              <router-link :to="{ name: 'warehouse.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Facility Particulars & Physical Location -->
    <div class="col-lg-8 col-md-12">
      <!-- Card 1: Facility Particulars -->
      <div class="card border shadow-sm rounded-3 bg-white mb-3">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-building text-primary me-2"></i> {{ $t('Facility Identity & Storage Parameters') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Section 1 of 3') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <Input
              v-model="data.name"
              field="data.name"
              title="Warehouse Name"
              placeholder="e.g. Central Depot / Tejgaon Main Warehouse"
              col="8"
              :req="true"
            />
            <Input
              v-model="data.code"
              field="data.code"
              title="Warehouse Code"
              placeholder="e.g. WH-01"
              col="4"
              :req="false"
            />
            <Select
              title="Assigned Branch / Unit"
              v-model="data.branch_id"
              field="data.branch_id"
              label="title"
              :reduce="(obj) => obj.id"
              :options="$root.global.branches"
              col="6"
              placeholder="--Select Branch--"
              :closeOnSelect="true"
              :required="false"
            />
            <Input
              v-model="data.capacity"
              field="data.capacity"
              title="Storage Capacity"
              placeholder="e.g. 10,000 Boxes / 5,000 Sq Ft"
              col="6"
              :req="false"
            />
          </div>
        </div>
      </div>

      <!-- Card 2: Physical Location & Address -->
      <div class="card border shadow-sm rounded-3 bg-white">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-map-marker-alt text-danger me-2"></i> {{ $t('Physical Location & Geographic Address') }}
          </h6>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 small">
            {{ $t('Section 2 of 3') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <Textarea
              v-model="data.address"
              field="data.address"
              title="Full Physical / Depot Address"
              placeholder="Enter comprehensive road, plot, industrial zone, and district details..."
              col="12"
              :required="false"
            />
            <div class="col-12 mt-1">
              <div class="p-2.5 rounded bg-light border text-muted small d-flex align-items-center gap-2">
                <i class="fas fa-info-circle text-primary fs-5"></i>
                <span>{{ $t('This physical address is printed on delivery challans, goods receiving notes (GRN), and transfer memos.') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): In-Charge, Operations & Status -->
    <div class="col-lg-4 col-md-12">
      <!-- Card 3: In-Charge & Contact Personnel -->
      <div class="card border shadow-sm rounded-3 bg-white mb-3">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-user-tie text-info me-2"></i> {{ $t('In-Charge & Communication') }}
          </h6>
          <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 small">
            {{ $t('Section 3 of 3') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <Input
              v-model="data.contact_person"
              field="data.contact_person"
              title="Contact Person (In-Charge)"
              placeholder="e.g. Md. Rafiqul Islam"
              col="12"
              :req="false"
            />
            <Input
              v-model="data.contact_person_phone"
              field="data.contact_person_phone"
              title="Contact Person Mobile"
              placeholder="01XXXXXXXXX"
              col="12"
              :req="false"
            />
            <Input
              v-model="data.phone"
              field="data.phone"
              title="Facility Landline / Phone"
              placeholder="e.g. 02-9876543"
              col="12"
              :req="false"
            />
            <Input
              v-model="data.email"
              field="data.email"
              title="Facility Official Email"
              placeholder="warehouse@company.com"
              col="12"
              :req="false"
            />
          </div>
        </div>
      </div>

      <!-- Card 4: Operational Settings & Status -->
      <div class="card border shadow-sm rounded-3 bg-white">
        <div class="card-header bg-light py-2.5 px-3 border-bottom">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-sliders-h text-warning me-2"></i> {{ $t('Operational Settings') }}
          </h6>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <Input
              v-model="data.sorting"
              field="data.sorting"
              title="Display Order / Sorting"
              type="number"
              col="12"
              placeholder="0"
              :req="true"
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
                      <span class="fw-bold text-dark fs-6">{{ $t('Facility Status') }}</span>
                      <span
                        class="badge rounded-pill px-2 py-0.5"
                        :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                      >
                        {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                      </span>
                    </div>
                    <p class="text-muted small mb-0 mt-0.5">
                      {{ data.status === 'active' ? $t('Available for GRN, stock and delivery') : $t('Temporarily disabled from inventory flow') }}
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
    </div>

    <!-- Form Action Footer -->
    <template v-slot:form_footer>
      <div class="col-12 mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="text-muted small">
          <span class="text-danger fw-bold">*</span> {{ $t('Required fields must be completed before saving.') }}
        </div>
        <div class="d-flex gap-2">
          <router-link :to="{ name: 'warehouse.index' }" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> {{ $t('Cancel / Back') }}
          </router-link>
          <button type="submit" class="theme_btn px-4 py-2 rounded-pill shadow-sm" :disabled="$root.submit">
            <template v-if="$root.submit">
              <i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Processing...') }}
            </template>
            <template v-else>
              <i :class="$route.params.id ? 'fas fa-save' : 'fas fa-check-circle'" class="me-1"></i>
              {{ $route.params.id ? $t('Update Warehouse Details') : $t('Save & Register Warehouse') }}
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = 'warehouse';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        name: '',
        code: '',
        contact_person: '',
        contact_person_phone: '',
        phone: '',
        email: '',
        capacity: '',
        branch_id: null,
        address: '',
        sorting: 0,
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
    getBranchName(branchId) {
      if (!branchId || !this.$root.global?.branches) return '';
      const b = this.$root.global.branches.find((item) => item.id == branchId);
      return b ? (b.title || b.branch_name) : '';
    },

    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
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
      this.get_sorting(this.ucfirst(this.model));
    }
  },

  validators: {
    'data.name': function (value = null) {
      return Validator.value(value).required('Warehouse Name is required');
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
.warehouse-hero-banner {
  background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
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
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
}

.transition-all {
  transition: all 0.2s ease-in-out;
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
