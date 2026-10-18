<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showEditRoute="true"
    :showPrintButton="false"
  >
    <!-- 💻 1. Interactive On-Screen Help Info View -->
    <div class="help-web-view w-100">
      
      <!-- 🌟 Top Hero / Help Banner -->
      <div class="card border-0 shadow-sm mb-4 help-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-question-circle fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.model_name || $t('Help Documentation') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #{{ data.id }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Published Guide') : $t('Draft') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="fas fa-file-code me-1"></i> {{ $t('Target Page') }}: {{ data.page_type }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-book-reader me-1 text-info"></i> {{ $t('User Guide Manual') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Target Scope -->
        <div class="col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Target Page Scope') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-desktop"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 font-monospace text-dark text-uppercase">
                {{ data.page_type || 'General' }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Guide modal context within module') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Status -->
        <div class="col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Publication Status') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-toggle-on"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 font-monospace" :class="data.status === 'active' ? 'text-success' : 'text-danger'">
                {{ data.status === 'active' ? $t('Live & Visible to Users') : $t('Hidden') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.status === 'active' ? $t('Shown in help modal buttons') : $t('Unpublished') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Specification & Content Card -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-info-circle text-primary"></i>
            {{ $t('Help Documentation Details') }}
          </span>
          <span class="badge rounded-pill px-2.5 py-1 font-monospace" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
            {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
          </span>
        </div>
        <div class="card-body p-4">
          <table class="table table-sm table-borderless mb-4">
            <tbody>
              <tr>
                <td class="text-muted ps-0 py-2" style="width: 25%;">{{ $t('Model Name') }}:</td>
                <td class="fw-bold text-dark font-monospace py-2 fs-6">{{ data.model_name }}</td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Page Type') }}:</td>
                <td class="py-2">
                  <span class="badge rounded-pill px-2.5 py-1 font-monospace"
                    :class="data.page_type === 'index' ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'">
                    {{ data.page_type }}
                  </span>
                </td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Display Sorting') }}:</td>
                <td class="py-2 font-monospace text-dark">{{ data.sorting ?? 0 }}</td>
              </tr>
            </tbody>
          </table>

          <div class="border-top pt-3">
            <h6 class="fw-bold text-dark mb-2 font-monospace">
              <i class="fas fa-file-alt me-1 text-primary"></i> {{ $t('Guide Content / Instructions') }}:
            </h6>
            <div class="p-3 bg-light rounded-3 text-dark border help-content" v-html="data.description || data.plain_description">
            </div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "helpInfo";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],
    };
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.help-hero-banner {
  background: linear-gradient(135deg, #112C47 0%, #1e3c72 100%);
  border-radius: 12px;
}
.hero-icon-box {
  width: 52px;
  height: 52px;
}
.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 10px;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.stat-icon {
  width: 38px;
  height: 38px;
  font-size: 16px;
}
.help-content {
  line-height: 1.6;
}
</style>
