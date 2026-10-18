<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showEditRoute="true"
    :showPrintButton="false"
  >
    <!-- 💻 1. Interactive On-Screen Financial Year View -->
    <div class="financial-year-web-view w-100">
      
      <!-- 🌟 Top Hero / FY Banner -->
      <div class="card border-0 shadow-sm mb-4 fy-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-calendar-alt fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.title || $t('Financial Year') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #{{ data.id }}
                  </span>
                  <span v-if="data.is_current" class="badge bg-warning text-dark font-monospace px-2 py-1">
                    <i class="fas fa-star me-1"></i> {{ $t('Current Active') }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="far fa-calendar-check me-1"></i> {{ $t('Period') }}:
                    <strong class="text-white">{{ data.start_date }} {{ $t('to') }} {{ data.end_date }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-landmark me-1 text-info"></i> {{ $t('Fiscal Accounting Year') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Start Date -->
        <div class="col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Period Commencement') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-calendar-plus"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark font-monospace">
                {{ data.start_date || 'N/A' }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Financial Start Date') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. End Date -->
        <div class="col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Period Closure') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-calendar-minus"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark font-monospace">
                {{ data.end_date || 'N/A' }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Financial End Date') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Posting Lock State -->
        <div class="col-md-4 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Voucher Posting') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas" :class="data.is_closed ? 'fa-lock text-danger' : 'fa-lock-open text-success'"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 font-monospace" :class="data.is_closed ? 'text-danger' : 'text-success'">
                {{ data.is_closed ? $t('Closed / Locked') : $t('Open for Posting') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.is_closed ? $t('Voucher entries locked') : $t('Active transaction postings allowed') }}
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
            {{ $t('Financial Year Details') }}
          </span>
          <span class="badge rounded-pill px-2.5 py-1 font-monospace" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
            {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
          </span>
        </div>
        <div class="card-body p-4">
          <table class="table table-sm table-borderless mb-0">
            <tbody>
              <tr>
                <td class="text-muted ps-0 py-2" style="width: 30%;">{{ $t('Fiscal Title') }}:</td>
                <td class="fw-bold text-dark font-monospace py-2 fs-6">{{ data.title }}</td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Commencement Date') }}:</td>
                <td class="fw-bold text-dark font-monospace py-2">{{ data.start_date }}</td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Closing Date') }}:</td>
                <td class="fw-bold text-dark font-monospace py-2">{{ data.end_date }}</td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Current Active Year') }}:</td>
                <td class="py-2">
                  <span class="badge px-2 py-1 font-monospace" :class="data.is_current ? 'bg-warning text-dark' : 'bg-light text-secondary border'">
                    {{ data.is_current ? 'Yes (Primary Year)' : 'No' }}
                  </span>
                </td>
              </tr>
              <tr>
                <td class="text-muted ps-0 py-2">{{ $t('Closed / Locked') }}:</td>
                <td class="py-2">
                  <span class="badge px-2 py-1 font-monospace" :class="data.is_closed ? 'bg-danger text-white' : 'bg-success text-white'">
                    {{ data.is_closed ? 'Locked' : 'Open' }}
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
const model = "financialYear";

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
.fy-hero-banner {
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