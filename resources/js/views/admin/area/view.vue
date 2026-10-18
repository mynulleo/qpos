<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showEditRoute="true"
    :showPrintButton="false"
  >
    <!-- 💻 1. Interactive On-Screen Area View -->
    <div class="area-web-view w-100">
      
      <!-- 🌟 Top Hero / Area Banner -->
      <div class="card border-0 shadow-sm mb-4 area-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-map-pin fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.area_name || $t('Area Profile') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #{{ data.id }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Active Area') : $t('Inactive') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="fas fa-map-marked-alt me-1"></i>
                    {{ data.district ? data.district.district_name : $t('Regional Zone') }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-map-marker-alt me-1 text-info"></i> {{ $t('Sub-District / Local Area') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Parent District -->
        <div class="col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Parent District') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-city"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark">
                {{ data.district ? data.district.district_name : 'N/A' }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Administrative Zone / Jurisdiction') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Status -->
        <div class="col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Status') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-toggle-on"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 font-monospace" :class="data.status === 'active' ? 'text-success' : 'text-danger'">
                {{ data.status === 'active' ? $t('Active & Enabled') : $t('Inactive') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.status === 'active' ? $t('Available for customer/branch address selection') : $t('Hidden') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Specification Card -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-info-circle text-primary"></i>
            {{ $t('Area Details') }}
          </span>
          <span class="badge rounded-pill px-2.5 py-1 font-monospace" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
            {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
          </span>
        </div>
        <div class="card-body p-4">
          <table class="table table-sm table-borderless mb-0">
            <tbody>
              <tr>
                <td class="text-muted ps-0 py-2" style="width: 30%;">{{ $t('Area Name') }}:</td>
                <td class="fw-bold text-dark font-monospace py-2 fs-6">{{ data.area_name }}</td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('District') }}:</td>
                <td class="py-2">
                  <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF;">
                    <i class="fas fa-map-marked-alt me-1"></i>
                    {{ data.district ? data.district.district_name : 'General District' }}
                  </span>
                </td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Status') }}:</td>
                <td class="py-2">
                  <span class="badge rounded-pill px-2.5 py-1 font-monospace" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "area";

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
.area-hero-banner {
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
</style>