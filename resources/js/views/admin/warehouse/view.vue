<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showEditRoute="true"
    :showPrintButton="false"
  >
    <!-- 💻 1. Interactive On-Screen Warehouse View -->
    <div class="warehouse-web-view w-100">
      
      <!-- 🌟 Top Hero / Warehouse Header Banner -->
      <div class="card border-0 shadow-sm mb-4 warehouse-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-warehouse fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.name || $t('Warehouse Details') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    {{ data.code || ('#WH-' + data.id) }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Active Facility') : $t('Inactive') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span v-if="data.branch">
                    <i class="fas fa-code-branch me-1 text-warning"></i> {{ $t('Branch') }}:
                    <strong class="text-white">{{ data.branch.title }}</strong>
                  </span>
                  <span v-if="data.capacity">
                    <i class="fas fa-boxes me-1 text-info"></i> {{ $t('Capacity') }}:
                    <strong class="text-white">{{ data.capacity }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-dolly-flatbed me-1 text-info"></i> {{ $t('Inventory Storage Node') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Total Capacity -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Storage Capacity') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-cubes"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark font-monospace text-truncate">
                {{ data.capacity || $t('Standard') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Stock Holding Volume') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Assigned Branch -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Assigned Branch') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-store"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="data.branch?.title">
                {{ data.branch ? data.branch.title : $t('All Branches') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Affiliated Business Unit') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Contact Supervisor -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Contact Person') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-user-shield"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="data.contact_person">
                {{ data.contact_person || $t('Unassigned') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ data.contact_person_phone || $t('No direct phone') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Operational Status -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Facility Status') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-toggle-on"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 font-monospace" :class="data.status === 'active' ? 'text-success' : 'text-danger'">
                {{ data.status === 'active' ? $t('Operational') : $t('Inactive') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                Sorting Priority: #{{ data.sorting ?? 0 }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Specification Cards Grid -->
      <div class="row g-4 mb-4">
        <!-- 1. Warehouse Details -->
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100 bg-white rounded-3">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="fas fa-info-circle text-primary"></i>
                {{ $t('Warehouse Particulars') }}
              </span>
            </div>
            <div class="card-body p-4">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted ps-0 py-2" style="width: 40%;">{{ $t('Warehouse Name') }}:</td>
                    <td class="fw-bold text-dark py-2">{{ data.name }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Warehouse Code') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">{{ data.code || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Branch') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.branch ? data.branch.title : 'All Branches' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Capacity') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.capacity || 'Not Specified' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Sorting Order') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.sorting ?? 0 }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 2. Contact & Address Details -->
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100 bg-white rounded-3">
            <div class="card-header bg-white py-3 px-4 border-bottom">
              <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="fas fa-address-book text-primary"></i>
                {{ $t('Contact & Location Details') }}
              </span>
            </div>
            <div class="card-body p-4">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted ps-0 py-2" style="width: 40%;">{{ $t('Contact Person') }}:</td>
                    <td class="fw-bold text-dark py-2">{{ data.contact_person || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Contact Mobile') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">{{ data.contact_person_phone || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Warehouse Phone') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.phone || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Email Address') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.email || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Physical Address') }}:</td>
                    <td class="text-dark py-2">{{ data.address || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "warehouse";

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
.warehouse-hero-banner {
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
