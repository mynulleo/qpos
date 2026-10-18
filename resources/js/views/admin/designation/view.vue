<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showEditRoute="true"
    :showPrintButton="true"
    printArea="designation_print_area"
  >
    <!-- 💻 1. Interactive On-Screen Designation View -->
    <div class="designation-web-view w-100">
      
      <!-- 🌟 Top Hero / Designation Banner -->
      <div class="card border-0 shadow-sm mb-4 designation-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-briefcase fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.title || $t('Designation Specification') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #{{ data.id }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Active Designation') : $t('Inactive') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="fas fa-money-bill-wave me-1"></i> {{ $t('Default Monthly Scale') }}:
                    <strong class="text-white">{{ $filter.formatBDT(data.total_salary) }}</strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-id-badge me-1 text-info"></i> {{ $t('HR Job Position') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Total Default Salary -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Monthly Pay') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                {{ $filter.formatBDT(data.total_salary) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Cumulative Scale Base') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Basic Pay -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Basic Salary') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-coins"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                {{ $filter.formatBDT(data.basic) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Core Base Component') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. House Rent -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('House Rent') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-home"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                {{ $filter.formatBDT(data.house_rent) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Housing Allowance') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Medical & Other -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Medical & Other') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-medkit"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                {{ $filter.formatBDT((parseFloat(data.medical) || 0) + (parseFloat(data.other) || 0)) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                Med: {{ $filter.formatBDT(data.medical) }} | Oth: {{ $filter.formatBDT(data.other) }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Breakdown Table Card -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-list text-primary"></i>
            {{ $t('Salary Scale Breakdown Details') }}
          </span>
          <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
            {{ $t('Status') }}: {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted font-monospace">
                <tr>
                  <th style="width: 50px;" class="text-center">#</th>
                  <th>{{ $t('Component Head') }}</th>
                  <th style="width: 150px;" class="text-center">{{ $t('Category') }}</th>
                  <th style="width: 200px;" class="text-end">{{ $t('Amount (BDT)') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center font-monospace text-muted small">{{ $bnNum(1) }}</td>
                  <td class="fw-bold text-dark font-monospace">{{ $t('Basic Salary') }}</td>
                  <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 font-monospace small">{{ $t('Core Pay') }}</span></td>
                  <td class="text-end font-monospace fw-bold fs-6">{{ $filter.formatBDT(data.basic) }}</td>
                </tr>
                <tr>
                  <td class="text-center font-monospace text-muted small">{{ $bnNum(2) }}</td>
                  <td class="fw-bold text-dark font-monospace">{{ $t('House Rent Allowance') }}</td>
                  <td class="text-center"><span class="badge bg-light text-secondary border px-2 py-1 font-monospace small">{{ $t('Allowance') }}</span></td>
                  <td class="text-end font-monospace fw-bold fs-6">{{ $filter.formatBDT(data.house_rent) }}</td>
                </tr>
                <tr>
                  <td class="text-center font-monospace text-muted small">{{ $bnNum(3) }}</td>
                  <td class="fw-bold text-dark font-monospace">{{ $t('Medical Allowance') }}</td>
                  <td class="text-center"><span class="badge bg-light text-secondary border px-2 py-1 font-monospace small">{{ $t('Allowance') }}</span></td>
                  <td class="text-end font-monospace fw-bold fs-6">{{ $filter.formatBDT(data.medical) }}</td>
                </tr>
                <tr v-if="data.other > 0">
                  <td class="text-center font-monospace text-muted small">{{ $bnNum(4) }}</td>
                  <td class="fw-bold text-dark font-monospace">{{ $t('Other Allowances') }}</td>
                  <td class="text-center"><span class="badge bg-light text-secondary border px-2 py-1 font-monospace small">{{ $t('Allowance') }}</span></td>
                  <td class="text-end font-monospace fw-bold fs-6">{{ $filter.formatBDT(data.other) }}</td>
                </tr>
              </tbody>
              <tfoot class="table-light font-monospace fw-bold">
                <tr>
                  <td colspan="3" class="text-end text-uppercase">{{ $t('Total Standard Scale') }}:</td>
                  <td class="text-end text-primary fs-6">{{ $filter.formatBDT(data.total_salary) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Printable Designation Specification Sheet -->
    <div id="designation_print_area" class="d-none d-print-block print-container p-4 bg-white text-dark">
      <div class="print-border p-4">
        <div class="text-center pb-3 border-bottom mb-4">
          <h3 class="fw-bold mb-1 text-uppercase">{{ $root.global?.site_name || 'QPOS ERP' }}</h3>
          <p class="mb-1 text-muted small">{{ $root.global?.address || 'Human Resources Department' }}</p>
          <div class="d-inline-block border border-dark px-3 py-1 mt-2 text-uppercase fw-bold font-monospace fs-6">
            {{ $t('DESIGNATION & SALARY SCALE SPECIFICATION') }}
          </div>
        </div>

        <div class="row g-3 mb-4 font-monospace small">
          <div class="col-6">
            <div><strong>{{ $t('Designation Title') }}:</strong> {{ data.title }}</div>
            <div><strong>{{ $t('Record ID') }}:</strong> #{{ data.id }}</div>
          </div>
          <div class="col-6 text-end">
            <div><strong>{{ $t('Status') }}:</strong> {{ data.status }}</div>
            <div><strong>{{ $t('Date Printed') }}:</strong> {{ $filter.today() }}</div>
          </div>
        </div>

        <table class="table table-bordered mb-4 small font-monospace">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;" class="text-center">#</th>
              <th>{{ $t('Component') }}</th>
              <th style="width: 140px;" class="text-end">{{ $t('Amount (BDT)') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-center">{{ $bnNum(1) }}</td>
              <td>{{ $t('Basic Salary') }}</td>
              <td class="text-end">{{ $filter.formatBDT(data.basic) }}</td>
            </tr>
            <tr>
              <td class="text-center">{{ $bnNum(2) }}</td>
              <td>{{ $t('House Rent') }}</td>
              <td class="text-end">{{ $filter.formatBDT(data.house_rent) }}</td>
            </tr>
            <tr>
              <td class="text-center">{{ $bnNum(3) }}</td>
              <td>{{ $t('Medical') }}</td>
              <td class="text-end">{{ $filter.formatBDT(data.medical) }}</td>
            </tr>
            <tr v-if="data.other > 0">
              <td class="text-center">{{ $bnNum(4) }}</td>
              <td>{{ $t('Other') }}</td>
              <td class="text-end">{{ $filter.formatBDT(data.other) }}</td>
            </tr>
          </tbody>
          <tfoot class="fw-bold">
            <tr>
              <td colspan="2" class="text-end">{{ $t('Total Standard Scale') }}:</td>
              <td class="text-end fs-6">{{ $filter.formatBDT(data.total_salary) }}</td>
            </tr>
          </tfoot>
        </table>

        <div class="row text-center font-monospace small pt-5" style="margin-top: 60px;">
          <div class="col-6">
            <div class="border-top border-dark pt-1">{{ $t('HR Officer') }}</div>
          </div>
          <div class="col-6">
            <div class="border-top border-dark pt-1">{{ $t('Managing Authority') }}</div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "designation";

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
.designation-hero-banner {
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
@media print {
  .print-border {
    border: 2px solid #333;
    padding: 24px;
  }
}
</style>