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
                <i class="fas fa-shield-alt fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ (data.model_name || $t('Media Rule')) + ' &rarr; ' + data.field_name }}</h5>
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
                  <span><i class="fas fa-weight-hanging me-1 text-warning"></i> {{ $t('Max Size') }}: <strong class="text-dark">{{ data.max_size ? data.max_size + " KB" : "N/A" }}</strong></span><span v-if="data.field_type === 'image'"><i class="fas fa-vector-square me-1 text-info"></i> {{ $t('Min Dimensions') }}: <strong class="text-dark">{{ data.min_width || 0 }} x {{ data.min_height || 0 }} PX</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'mediaValidator.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
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
                <i class="fas fa-shield-alt fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Configure Media Upload Validator') }}</h5>
                <small class="text-muted">{{ $t('Enforce upload security constraints, max filesize thresholds, and thumbnail dimensions') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-file-image me-1 text-info"></i> {{ $t('Upload Security') }}
              </span>
              <router-link :to="{ name: 'mediaValidator.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to List') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Form Grid (2 Columns: 8 + 4) -->
    <!-- Left Column (8 cols): Media Constraints -->
    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-sliders-h text-primary me-2"></i> {{ $t('Upload Constraints & Rules') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Required Info') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Model Name -->
            <div class="col-md-6 col-12">
              <Input
                v-model="data.model_name"
                field="data.model_name"
                title="Model Name"
                placeholder="e.g. Item, Employee, SiteSetting, Brand"
                :req="true"
                col="12"
              />
            </div>

            <!-- Field Name -->
            <div class="col-md-6 col-12">
              <Input
                v-model="data.field_name"
                field="data.field_name"
                title="Field Name in Database"
                placeholder="e.g. image, photo, logo, signature"
                :req="true"
                col="12"
              />
            </div>

            <!-- Field Type -->
            <div class="col-md-6 col-12">
              <Select
                title="Field Type"
                v-model="data.field_type"
                field="data.field_type"
                label="name"
                :reduce="(obj) => obj.value"
                :options="field_types"
                placeholder="-- Select Type --"
                :closeOnSelect="true"
                :required="true"
                col="12"
              />
            </div>

            <!-- Max Size -->
            <div class="col-md-6 col-12">
              <Input
                v-model="data.max_size"
                field="data.max_size"
                title="Max File Size (in KB)"
                placeholder="e.g. 2048 (for 2 MB), 1024 (for 1 MB)"
                :req="true"
                col="12"
              />
            </div>

            <!-- Image-Specific Parameters -->
            <template v-if="data.field_type === 'image'">
              <div class="col-12 mt-2">
                <div class="p-3 rounded-3 border bg-light">
                  <h6 class="fw-bold text-dark mb-2">
                    <i class="fas fa-crop-alt text-primary me-1.5"></i> {{ $t('Image Dimension & Thumbnail Resize Settings') }}
                  </h6>
                  <div class="row g-3">
                    <div class="col-md-6 col-12">
                      <Input
                        v-model="data.min_width"
                        field="data.min_width"
                        title="Min Width (PX)"
                        placeholder="e.g. 300"
                        :req="true"
                        col="12"
                      />
                    </div>
                    <div class="col-md-6 col-12">
                      <Input
                        v-model="data.min_height"
                        field="data.min_height"
                        title="Min Height (PX)"
                        placeholder="e.g. 300"
                        :req="true"
                        col="12"
                      />
                    </div>
                    <div class="col-12">
                      <x-multiple-select
                        v-model="data.resize_value"
                        field="data.resize_value"
                        title="Resize Dimensions (Width x Height - Large to Small)"
                        placeholder="Type & press enter. Example: 800x600, 300x200"
                        :req="true"
                        col="12"
                      />
                      <div class="form-text text-muted small mt-1 ms-1">
                        <i class="fas fa-info-circle me-1 text-info"></i>
                        {{ $t('System will automatically generate web-optimized thumbnails at these exact dimensions.') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column (4 cols): Policy & Status -->
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
            <Switch
              v-model="data.status"
              field="data.status"
              title="Rule Enforcement Status"
              on-label="Active"
              off-label="Deactive"
              :req="true"
              col="12"
            />
          </div>
        </div>

        <!-- Policy Card -->
        <div class="card border shadow-sm rounded-3 bg-white flex-grow-1">
          <div class="card-header bg-light py-2.5 px-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-shield-virus text-danger me-2"></i> {{ $t('Validation Enforcement') }}
            </h6>
          </div>
          <div class="card-body p-3">
            <p class="text-muted small mb-2">
              <i class="fas fa-check text-success me-1"></i>
              {{ $t('Ensures uploaded files conform strictly to size limits to safeguard server storage.') }}
            </p>
            <p class="text-muted small mb-0">
              <i class="fas fa-check text-success me-1"></i>
              {{ $t('Prevents blurry low-resolution catalog images on POS checkout screens.') }}
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
            <router-link :to="{ name: 'mediaValidator.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2">
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
              {{ $route.params.id ? $t('Update Rule') : $t('Save Rule') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'mediaValidator';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        model_name: '',
        field_name: '',
        field_type: '',
        max_size: '',
        min_width: '',
        min_height: '',
        resize_value: [],
        status: 'active',
      },
    };
  },

  computed: {
    field_types() {
      return [
        { name: this.$t('Image'), value: 'image' },
        { name: this.$t('File / Document'), value: 'file' },
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
      this.data.field_name = '';
      this.data.field_type = '';
      this.data.max_size = '';
      this.resetImageFields();
      this.data.status = 'active';
    },

    resetImageFields() {
      this.data.min_width = '';
      this.data.min_height = '';
      this.data.resize_value = [];
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
          if (this.data.field_type !== 'image') {
            this.resetImageFields();
          }

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
    'data.model_name': function (value = null) {
      return Validator.value(value).required('Model Name is required');
    },

    'data.field_name': function (value = null) {
      return Validator.value(value).required('Field Name is required');
    },

    'data.field_type': function (value = null) {
      return Validator.value(value).required('Field Type is required');
    },

    'data.max_size': function (value = null) {
      return Validator.value(value)
        .required('Max Size is required')
        .digit()
        .minLength(1);
    },

    'data.min_width, data.field_type': function (
      minWidth = null,
      fieldType = null
    ) {
      if (fieldType === 'image') {
        return Validator.value(minWidth)
          .required('Min Width is required')
          .digit()
          .minLength(1);
      } else {
        return Validator.value(minWidth);
      }
    },

    'data.min_height, data.field_type': function (
      minHeight = null,
      fieldType = null
    ) {
      if (fieldType === 'image') {
        return Validator.value(minHeight)
          .required('Min Height is required')
          .digit()
          .minLength(1);
      } else {
        return Validator.value(minHeight);
      }
    },

    'data.resize_value, data.field_type': function (
      resizeValue = null,
      fieldType = null
    ) {
      if (fieldType === 'image') {
        return Validator.value(resizeValue).required('Resize Value is required');
      } else {
        return Validator.value(resizeValue);
      }
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

.media-hero-banner {
  background: linear-gradient(135deg, #6d28d9 0%, #9333ea 100%);
}

.hero-icon-box {
  width: 58px;
  height: 58px;
  backdrop-filter: blur(4px);
}
</style>
