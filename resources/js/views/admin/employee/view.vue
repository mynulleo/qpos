<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showEditRoute="true"
    :showPrintButton="true"
    printArea="employee_print_area"
  >
    <!-- 💻 1. Executive Employee Profile Screen -->
    <div class="employee-web-view w-100">
      
      <!-- 🌟 Top Hero / Profile Header Banner -->
      <div class="card border-0 shadow-sm mb-4 employee-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
              <!-- Employee Photo Avatar -->
              <div class="employee-avatar-wrapper rounded-circle p-1 bg-white bg-opacity-20 shadow-sm flex-shrink-0">
                <img
                  v-if="data.image"
                  :src="data.image"
                  :alt="data.full_name"
                  class="rounded-circle object-fit-cover"
                  style="width: 72px; height: 72px;"
                />
                <div
                  v-else
                  class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold fs-2"
                  style="width: 72px; height: 72px; background-color: #112C47;"
                >
                  {{ (data.full_name || 'E').charAt(0).toUpperCase() }}
                </div>
              </div>

              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.full_name || $t('Employee Profile') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    #{{ data.empid || ('EMP-' + String(data.id || '').padStart(4, '0')) }}
                  </span>
                  <span class="badge" :class="data.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'">
                    <i class="fas" :class="data.status === 'active' ? 'fa-check-circle me-1' : 'fa-pause-circle me-1'"></i>
                    {{ data.status === 'active' ? $t('Active Staff') : $t('Inactive') }}
                  </span>
                </div>

                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span v-if="data.designation?.title">
                    <i class="fas fa-id-badge me-1 text-info"></i> {{ data.designation.title }}
                  </span>
                  <span v-if="data.offic_branch?.branch_name">
                    <i class="fas fa-building me-1 text-warning"></i> {{ data.offic_branch.branch_name }}
                  </span>
                  <span v-if="data.joining_date">
                    <i class="far fa-calendar-alt me-1"></i> Joined: {{ data.joining_date }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Contact Badges -->
            <div class="d-flex flex-column align-items-md-end gap-1 font-monospace small text-white-50">
              <span v-if="data.mobile" class="text-white">
                <i class="fas fa-phone me-1 text-success"></i> {{ data.mobile }}
              </span>
              <span v-if="data.email" class="text-white-50">
                <i class="far fa-envelope me-1 text-info"></i> {{ data.email }}
              </span>
              <span v-if="data.nid" class="text-white-50">
                <i class="far fa-id-card me-1"></i> {{ $t('NID') }}: {{ data.nid }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Total Monthly Gross Salary -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Monthly Gross Salary') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                {{ $filter.formatBDT(data.salary || totalSalary) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Disbursable Monthly Compensation') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Designation & Department -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Designation / Role') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-briefcase"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="data.designation?.title">
                {{ data.designation?.title || $t('Unassigned') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('ID') }}: {{ data.empid || ('#' + data.id) }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Office Branch -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Assigned Branch') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-store"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark text-truncate" :title="data.offic_branch?.branch_name">
                {{ data.offic_branch?.branch_name || $t('Main Branch') }}
              </h5>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Operational Station') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Joining Date & Status -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Employment Date') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-calendar-check"></i>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-dark font-monospace">
                {{ data.joining_date || 'N/A' }}
              </h5>
              <small class="text-success fw-semibold font-monospace" style="font-size: 11px;">
                <i class="fas fa-circle me-1" style="font-size: 7px;"></i>{{ data.status === 'active' ? $t('In Active Service') : $t('Separated') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Employee Profile Cards Grid -->
      <div class="row g-4 mb-4">
        <!-- 1. Personal & Identity Particulars -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100 bg-white rounded-3">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
              <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="fas fa-user text-primary"></i>
                {{ $t('Personal & Contact Particulars') }}
              </span>
            </div>
            <div class="card-body p-4">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted ps-0 py-2" style="width: 40%;">{{ $t('Employee ID') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">#{{ data.empid || data.id }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Full Name') }}:</td>
                    <td class="fw-bold text-dark py-2">{{ data.full_name || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Mobile Phone') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">{{ data.mobile || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Email Address') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.email || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('National ID (NID)') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.nid || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Date of Birth') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.dob || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Residential Address') }}:</td>
                    <td class="text-dark py-2">{{ data.address || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 2. Bank & Disbursal Information -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100 bg-white rounded-3">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
              <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="fas fa-university text-primary"></i>
                {{ $t('Bank & Payroll Account Information') }}
              </span>
            </div>
            <div class="card-body p-4">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted ps-0 py-2" style="width: 40%;">{{ $t('Bank Name') }}:</td>
                    <td class="fw-bold text-dark font-monospace py-2">{{ data.bank?.bank_name || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Bank Branch') }}:</td>
                    <td class="text-dark font-monospace py-2">{{ data.branch_name || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Account Name') }}:</td>
                    <td class="fw-bold text-dark py-2">{{ data.account_name || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Account Number') }}:</td>
                    <td class="fw-bold text-primary font-monospace py-2 fs-6">{{ data.account_no || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Designation') }}:</td>
                    <td class="text-dark py-2">{{ data.designation?.title || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted ps-0 py-2">{{ $t('Posting Branch') }}:</td>
                    <td class="text-dark py-2">{{ data.offic_branch?.branch_name || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 💵 3. Salary Parameter Structure Breakdown -->
      <div v-if="data.employee_salaries && data.employee_salaries.length" class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-coins text-warning"></i>
            {{ $t('Salary Structure & Parameter Allowances') }}
          </span>
          <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace px-2 py-1">
            {{ $t('Total Gross') }}: {{ $filter.formatBDT(totalSalary) }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted font-monospace">
                <tr>
                  <th style="width: 50px;" class="text-center">#</th>
                  <th>{{ $t('Salary Parameter / Allowance') }}</th>
                  <th style="width: 150px;" class="text-center">{{ $t('Type') }}</th>
                  <th style="width: 200px;" class="text-end">{{ $t('Amount (BDT)') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(salary, idx) in data.employee_salaries" :key="idx">
                  <td class="text-center font-monospace text-muted small">{{ $bnNum(idx + 1) }}</td>
                  <td>
                    <span class="fw-bold text-dark font-monospace">{{ salary.title }}</span>
                  </td>
                  <td class="text-center">
                    <span v-if="salary.is_basic" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 font-monospace small">
                      {{ $t('Basic Salary') }}
                    </span>
                    <span v-else class="badge bg-light text-secondary border px-2 py-1 font-monospace small">
                      {{ $t('Allowance') }}
                    </span>
                  </td>
                  <td class="text-end font-monospace fw-bold fs-6 text-dark">
                    {{ $filter.formatBDT(salary.amount) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light font-monospace fw-bold">
                <tr>
                  <td colspan="3" class="text-end text-uppercase">{{ $t('Total Monthly Salary') }}:</td>
                  <td class="text-end text-primary fs-6">{{ $filter.formatBDT(totalSalary) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 👨‍👩‍👧 4. Family & Emergency Contacts -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-users text-primary"></i>
            {{ $t('Family & Emergency Contact Particulars') }}
          </span>
        </div>
        <div class="card-body p-4">
          <div class="row g-4">
            <!-- Father Info -->
            <div class="col-md-4">
              <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                <h6 class="fw-bold text-dark mb-3">
                  <i class="fas fa-male text-secondary me-1"></i> {{ $t('Father Information') }}
                </h6>
                <div class="small mb-1"><strong>{{ $t('Name') }}:</strong> {{ data.father_name || 'N/A' }}</div>
                <div class="small mb-1"><strong>{{ $t('Occupation') }}:</strong> {{ data.father_occupation || 'N/A' }}</div>
                <div class="small mb-1 font-monospace"><strong>{{ $t('Mobile') }}:</strong> {{ data.father_mobile || 'N/A' }}</div>
                <div class="small font-monospace"><strong>{{ $t('NID') }}:</strong> {{ data.father_nid || 'N/A' }}</div>
              </div>
            </div>

            <!-- Mother Info -->
            <div class="col-md-4">
              <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                <h6 class="fw-bold text-dark mb-3">
                  <i class="fas fa-female text-secondary me-1"></i> {{ $t('Mother Information') }}
                </h6>
                <div class="small mb-1"><strong>{{ $t('Name') }}:</strong> {{ data.mother_name || 'N/A' }}</div>
                <div class="small mb-1"><strong>{{ $t('Occupation') }}:</strong> {{ data.mother_occupation || 'N/A' }}</div>
                <div class="small mb-1 font-monospace"><strong>{{ $t('Mobile') }}:</strong> {{ data.mother_mobile || 'N/A' }}</div>
                <div class="small font-monospace"><strong>{{ $t('NID') }}:</strong> {{ data.mother_nid || 'N/A' }}</div>
              </div>
            </div>

            <!-- Spouse Info -->
            <div class="col-md-4">
              <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                <h6 class="fw-bold text-dark mb-3">
                  <i class="fas fa-heart text-secondary me-1"></i> {{ $t('Spouse Information') }}
                </h6>
                <div class="small mb-1"><strong>{{ $t('Name') }}:</strong> {{ data.spouse_name || 'N/A' }}</div>
                <div class="small font-monospace"><strong>{{ $t('Mobile') }}:</strong> {{ data.spouse_mobile || 'N/A' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Printable Employee Record Dossier -->
    <div id="employee_print_area" class="d-none d-print-block print-container p-4 bg-white text-dark">
      <div class="print-border p-4">
        <!-- Header -->
        <div class="text-center pb-3 border-bottom mb-4">
          <h3 class="fw-bold mb-1 text-uppercase">{{ $root.global?.site_name || 'QPOS ERP' }}</h3>
          <p class="mb-1 text-muted small">{{ $root.global?.address || 'Human Resources Department' }}</p>
          <div class="d-inline-block border border-dark px-3 py-1 mt-2 text-uppercase fw-bold font-monospace fs-6">
            {{ $t('EMPLOYEE PROFILE DOSSIER') }}
          </div>
        </div>

        <!-- Meta & Photo Grid -->
        <div class="row g-3 mb-4">
          <div class="col-8 font-monospace small">
            <div><strong>{{ $t('Employee ID') }}:</strong> {{ data.empid || ('#' + data.id) }}</div>
            <div><strong>{{ $t('Full Name') }}:</strong> {{ data.full_name }}</div>
            <div><strong>{{ $t('Designation') }}:</strong> {{ data.designation?.title }}</div>
            <div><strong>{{ $t('Branch') }}:</strong> {{ data.offic_branch?.branch_name }}</div>
            <div><strong>{{ $t('Joining Date') }}:</strong> {{ data.joining_date }}</div>
            <div><strong>{{ $t('Mobile') }}:</strong> {{ data.mobile }}</div>
            <div><strong>{{ $t('NID') }}:</strong> {{ data.nid }}</div>
          </div>
          <div class="col-4 text-end">
            <img v-if="data.image" :src="data.image" alt="photo" style="width: 100px; height: 115px; object-fit: cover; border: 1px solid #ccc; padding: 2px;" />
            <div v-else style="width: 100px; height: 115px; border: 1px dashed #ccc; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">
              {{ $t('No Photo') }}
            </div>
          </div>
        </div>

        <!-- Salary Parameters Table -->
        <div v-if="data.employee_salaries && data.employee_salaries.length" class="mb-4">
          <div class="fw-bold small text-uppercase mb-1 font-monospace">{{ $t('Salary Structure') }}</div>
          <table class="table table-bordered mb-0 small font-monospace">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th>{{ $t('Parameter') }}</th>
                <th style="width: 140px;" class="text-end">{{ $t('Amount (BDT)') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in data.employee_salaries" :key="i">
                <td class="text-center">{{ $bnNum(i + 1) }}</td>
                <td>{{ item.title }}</td>
                <td class="text-end">{{ $filter.formatBDT(item.amount) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="fw-bold">
                <td colspan="2" class="text-end">{{ $t('Total Gross Salary') }}:</td>
                <td class="text-end">{{ $filter.formatBDT(totalSalary) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Official Signatures -->
        <div class="row text-center font-monospace small pt-5" style="margin-top: 60px;">
          <div class="col-4">
            <div class="border-top border-dark pt-1">{{ $t('Employee Signature') }}</div>
          </div>
          <div class="col-4">
            <div class="border-top border-dark pt-1">{{ $t('HR Officer') }}</div>
          </div>
          <div class="col-4">
            <div class="border-top border-dark pt-1">{{ $t('Managing Authority') }}</div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "employee";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [{ field: "image", title: "Image" }],
    };
  },

  computed: {
    totalSalary() {
      if (this.data.salary) return this.data.salary;
      if (Array.isArray(this.data.employee_salaries)) {
        return this.data.employee_salaries.reduce((acc, curr) => acc + (parseFloat(curr.amount) || 0), 0);
      }
      return 0;
    }
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.employee-hero-banner {
  background: linear-gradient(135deg, #112C47 0%, #1e3c72 100%);
  border-radius: 12px;
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