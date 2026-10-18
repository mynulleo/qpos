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
                <i class="fas fa-question-circle fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.model_name || $t('Help Documentation') }}</h5>
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
                  <span><i class="fas fa-sort-numeric-down me-1 text-warning"></i> Sorting: <strong class="text-dark">#{{ data.sorting ?? 0 }}</strong></span><span><i class="fas fa-book-open me-1 text-info"></i> Guidance: <strong class="text-dark">Operator Tooltip Modal</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'helpInfo.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-question-circle fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Create In-App Help Guide') }}</h5>
                <small class="text-muted">{{ $t('Author rich operational guidelines, training steps, and FAQ modals accessible inside admin views') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-book-reader me-1 text-info"></i> {{ $t('Documentation Guide') }}
              </span>
              <router-link :to="{ name: 'helpInfo.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Documentation Content -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-edit text-primary me-2"></i> {{ $t('Guide Content & Scope') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Target Model Name -->
            <div class="col-md-6 col-12">
              <Input
                v-model="data.model_name"
                field="data.model_name"
                title="Target Module / Model Name"
                placeholder="e.g. Item, Pos, Purchase, Expense, SalarySheet"
                :req="true"
                col="12"
              />
            </div>

            <!-- Page Type Selector -->
            <div class="col-md-6 col-12">
              <Select
                title="Page Type / Context"
                v-model="data.page_type"
                field="data.page_type"
                label="name"
                :reduce="(obj) => obj.value"
                :options="page_type"
                placeholder="-- Select Page Scope --"
                :closeOnSelect="true"
                :required="true"
                col="12"
              />
            </div>

            <!-- CKEditor for Rich Content -->
            <div class="col-12 mt-2">
              <label class="form-label small fw-semibold text-dark mb-1">
                <i class="fas fa-paragraph text-primary me-1"></i> {{ $t('Guide Instructions & Documentation') }}
              </label>
              <editor
                v-model="data.description"
                title=""
                col="12"
              />
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
                <span class="fw-semibold text-dark small">{{ $t('Guide Visibility') }}</span>
                <span
                  class="badge rounded-pill px-2.5 py-1 text-uppercase"
                  :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'"
                  style="font-size: 0.75rem;"
                >
                  <i :class="data.status === 'active' ? 'fas fa-dot-circle' : 'fas fa-circle'" class="me-1"></i>
                  {{ data.status === 'active' ? $t('Live / Visible') : $t('Draft / Hidden') }}
                </span>
              </div>
              <p class="text-muted small mb-0">
                {{ data.status === 'active' 
                  ? $t('Guide will pop up for staff operators when clicking the question mark icon in the target view.') 
                  : $t('Draft guide is hidden from operators.') }}
              </p>
            </div>

            <Switch
              v-model="data.status"
              field="data.status"
              title="Visibility Status"
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
            <router-link :to="{ name: 'helpInfo.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
              {{ $route.params.id ? $t('Update Help Guide') : $t('Save Help Guide') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
import Editor from '../../../components/Form/CKEditor';

const model = 'helpInfo';

export default {
  components: { Editor },
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        model_name: '',
        page_type: 'index',
        sorting: 0,
        description: '',
        status: 'active',
      },
    };
  },

  computed: {
    page_type() {
      return [
        { name: this.$t('Index Page (List View)'), value: 'index' },
        { name: this.$t('Create Page (Data Entry)'), value: 'create' },
        { name: this.$t('View Page (Details / Print)'), value: 'view' },
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
      this.data.model_name = '';
      this.data.page_type = 'index';
      this.data.description = '';
      this.data.status = 'active';
      this.get_sorting('HelpInfo');
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
          const form = document.getElementById('form');
          const formData = new FormData(form);
          if (this.data.description) {
            formData.append('description', this.data.description);
          }
          formData.append('page_type', this.data.page_type);
          formData.append('status', this.data.status);
          if (this.data.id) {
            this.update(this.model, formData, this.data.id, true);
          } else {
            this.store(this.model, formData);
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
      this.get_sorting('HelpInfo');
    }
  },

  validators: {
    'data.model_name': function (value = null) {
      return Validator.value(value).required('Module Name is required');
    },
    'data.page_type': function (value = null) {
      return Validator.value(value).required('Page Type is required');
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

.help-hero-banner {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}
</style>
