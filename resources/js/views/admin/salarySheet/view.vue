<template>
  <view-page
    :defaultTable="false"
    :showCreateRoute="false"
    :showDeleteButton="false"
    :showPrintButton="true"
    printArea="salary_sheet_print_area"
  >
    <!-- 💻 1. Interactive On-Screen Payroll Screen -->
    <div class="salary-sheet-web-view w-100">
      
      <!-- 🌟 Top Hero / Payroll Header Banner -->
      <div class="card border-0 shadow-sm mb-4 salary-sheet-hero-banner text-white">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center">
                <i class="fas fa-file-invoice-dollar fs-3"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h4 class="fw-bold mb-0 text-white">
                    {{ data.title || $t('Payroll Salary Sheet') }}
                  </h4>
                  <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                    {{ data.month }} - {{ data.year }}
                  </span>
                  <span class="badge" :class="data.approved_by ? 'bg-success text-white' : 'bg-warning text-dark'">
                    <i class="fas" :class="data.approved_by ? 'fa-check-circle me-1' : 'fa-clock me-1'"></i>
                    {{ data.approved_by ? $t('Approved') : $t('Pending Approval') }}
                  </span>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                  <span>
                    <i class="far fa-calendar-alt me-1"></i> {{ $t('Generated Date') }}:
                    <strong class="text-white">{{ data.generated_date || '-' }}</strong>
                  </span>
                  <span v-if="data.approved_admin?.full_name">
                    <i class="fas fa-user-check me-1"></i> {{ $t('Approved By') }}:
                    <strong class="text-white">{{ data.approved_admin.full_name }}</strong> ({{ data.approved_date }})
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Summary Badge without duplicate buttons -->
            <div class="d-none d-md-flex align-items-center gap-2">
              <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-25 px-3 py-2 text-uppercase font-monospace small">
                <i class="fas fa-shield-alt me-1 text-info"></i> {{ $t('Audited Payroll Batch') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 KPI Metric Stat Cards -->
      <div class="row g-3 mb-4">
        <!-- 1. Grand Total Net Payable -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Net Payable') }}</span>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-money-check-alt"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                {{ $filter.formatBDT(numericGrandTotal) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Net Disbursable Amount') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Gross Base Salary -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-info border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Base Salary') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-coins"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                {{ $filter.formatBDT(numericTotalSalary) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Standard Monthly Pay') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Total Bonus & Incentives -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Bonus') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-gift"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                {{ $filter.formatBDT(numericTotalBonus) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Additional Allowances') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Total Deductions & Installments -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Deductions') }}</span>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-minus-circle"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                {{ $filter.formatBDT(numericTotalDeduct + numericTotalInstallment) }}
              </h4>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                {{ $t('Loan/Adv. & Adjustments') }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Main Payroll Breakdown Table Card -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-users text-primary"></i>
            {{ $t('Employee Salary Ledger') }}
          </span>
          <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1">
            {{ $bnNum(data.salary_sheet_details ? data.salary_sheet_details.length : 0) }} {{ $t('Employees') }}
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light text-uppercase small text-muted font-monospace">
                <tr>
                  <th style="width: 45px;" class="text-center">#</th>
                  <th>{{ $t('Employee Name') }}</th>
                  <th>{{ $t('Designation') }}</th>
                  <th style="width: 130px;" class="text-end">{{ $t('Base Salary') }}</th>
                  <th style="width: 120px;" class="text-end">{{ $t('Bonus') }}</th>
                  <th style="width: 120px;" class="text-end">{{ $t('Installment') }}</th>
                  <th style="width: 120px;" class="text-end">{{ $t('Deduct') }}</th>
                  <th style="width: 140px;" class="text-end">{{ $t('Net Total') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in data.salary_sheet_details" :key="index">
                  <td class="text-center font-monospace text-muted small">{{ $bnNum(index + 1) }}</td>
                  <td>
                    <div class="fw-bold text-dark font-monospace" style="color: #112C47 !important;">
                      {{ row.employee_name }}
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border font-monospace small">
                      {{ row.designation }}
                    </span>
                  </td>
                  <td class="text-end font-monospace">{{ $filter.formatBDT(row.salary) }}</td>
                  <td class="text-end font-monospace text-success">{{ $filter.formatBDT(row.bonus) }}</td>
                  <td class="text-end font-monospace text-warning">{{ $filter.formatBDT(row.installment) }}</td>
                  <td class="text-end font-monospace text-danger">{{ $filter.formatBDT(row.deduct) }}</td>
                  <td class="text-end font-monospace fw-bold text-primary fs-6">{{ $filter.formatBDT(row.total) }}</td>
                </tr>
              </tbody>
              <tfoot class="table-light font-monospace fw-bold">
                <tr>
                  <td colspan="3" class="text-end text-uppercase">{{ $t('Batch Total') }}:</td>
                  <td class="text-end">{{ $filter.formatBDT(numericTotalSalary) }}</td>
                  <td class="text-end text-success">{{ $filter.formatBDT(numericTotalBonus) }}</td>
                  <td class="text-end text-warning">{{ $filter.formatBDT(numericTotalInstallment) }}</td>
                  <td class="text-end text-danger">{{ $filter.formatBDT(numericTotalDeduct) }}</td>
                  <td class="text-end text-primary fs-6">{{ $filter.formatBDT(numericGrandTotal) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 🔐 Approval & Compliance Section -->
      <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
            <i class="fas fa-clipboard-check text-primary"></i>
            {{ $t('Salary Sheet Approval Workflow') }}
          </span>
          <span class="badge" :class="data.approved_by ? 'bg-success text-white' : 'bg-warning text-dark'">
            {{ data.approved_by ? $t('Approved & Locked') : $t('Draft - Pending Approval') }}
          </span>
        </div>
        <div class="card-body p-4">
          <div class="alert alert-info border-0 shadow-sm mb-0">
            <h6 class="alert-heading fw-bold mb-2">
              <i class="fas fa-info-circle me-1"></i> {{ $t('Salary Sheet Approval Guidelines') }}
            </h6>
            <p class="mb-1 small">
              {{ $t('Please carefully review the Salary Sheet amounts above and verify attendance and bonus allocations.') }}
            </p>
            <p class="mb-1 small">
              {{ $t('Once approved, the approver’s identity and timestamp will be logged into the general audit ledger.') }}
            </p>
            <p class="mb-3 text-danger fw-semibold small">
              {{ $t('After approval, this Salary Sheet cannot be modified or deleted without cancellation.') }}
            </p>

            <!-- Action Buttons inside Alert -->
            <div class="d-flex justify-content-end" v-if="!data.approved_by">
              <button
                v-if="$root.checkPermission('salarySheet.approved')"
                type="button"
                @click="approved"
                class="btn btn-success fw-bold px-4 rounded-pill shadow-sm d-flex align-items-center gap-2"
              >
                <i class="fas fa-check-circle"></i> {{ $t('Approve Salary Sheet') }}
              </button>
            </div>
            <div class="d-flex justify-content-end" v-else>
              <button
                v-if="$root.checkPermission('salarySheet.approvalcancel')"
                type="button"
                @click="approvalcancel"
                class="btn btn-danger fw-bold px-4 rounded-pill shadow-sm d-flex align-items-center gap-2"
              >
                <i class="fas fa-times-circle"></i> {{ $t('Cancel Approval') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ 2. Printable Official Payroll Document -->
    <div id="salary_sheet_print_area" class="d-none d-print-block print-container p-4 bg-white text-dark">
      <div class="print-border p-4">
        <!-- Header -->
        <div class="text-center pb-3 border-bottom mb-4">
          <h3 class="fw-bold mb-1 text-uppercase">{{ $root.global?.site_name || 'QPOS ERP' }}</h3>
          <p class="mb-1 text-muted small">{{ $root.global?.address || 'Official Payroll Report' }}</p>
          <div class="d-inline-block border border-dark px-3 py-1 mt-2 text-uppercase fw-bold font-monospace fs-6">
            {{ data.title || 'MONTHLY SALARY SHEET' }} ({{ data.month }} - {{ data.year }})
          </div>
        </div>

        <!-- Meta Grid -->
        <div class="row g-3 mb-4 font-monospace small">
          <div class="col-6">
            <div><strong>{{ $t('Generated Date') }}:</strong> {{ data.generated_date }}</div>
            <div><strong>{{ $t('Batch Period') }}:</strong> {{ data.month }} - {{ data.year }}</div>
          </div>
          <div class="col-6 text-end">
            <div><strong>{{ $t('Status') }}:</strong> {{ data.approved_by ? $t('Approved') : $t('Pending') }}</div>
            <div><strong>{{ $t('Approved By') }}:</strong> {{ data.approved_admin?.full_name || 'N/A' }}</div>
          </div>
        </div>

        <!-- Table -->
        <table class="table table-bordered mb-4 small font-monospace">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;" class="text-center">#</th>
              <th>{{ $t('Employee') }}</th>
              <th>{{ $t('Designation') }}</th>
              <th class="text-end">{{ $t('Salary') }}</th>
              <th class="text-end">{{ $t('Bonus') }}</th>
              <th class="text-end">{{ $t('Installment') }}</th>
              <th class="text-end">{{ $t('Deduct') }}</th>
              <th class="text-end">{{ $t('Net Total') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in data.salary_sheet_details" :key="idx">
              <td class="text-center">{{ $bnNum(idx + 1) }}</td>
              <td>{{ row.employee_name }}</td>
              <td>{{ row.designation }}</td>
              <td class="text-end">{{ $filter.formatBDT(row.salary) }}</td>
              <td class="text-end">{{ $filter.formatBDT(row.bonus) }}</td>
              <td class="text-end">{{ $filter.formatBDT(row.installment) }}</td>
              <td class="text-end">{{ $filter.formatBDT(row.deduct) }}</td>
              <td class="text-end fw-bold">{{ $filter.formatBDT(row.total) }}</td>
            </tr>
          </tbody>
          <tfoot class="fw-bold">
            <tr>
              <td colspan="3" class="text-end text-uppercase">{{ $t('Grand Total') }}:</td>
              <td class="text-end">{{ $filter.formatBDT(numericTotalSalary) }}</td>
              <td class="text-end">{{ $filter.formatBDT(numericTotalBonus) }}</td>
              <td class="text-end">{{ $filter.formatBDT(numericTotalInstallment) }}</td>
              <td class="text-end">{{ $filter.formatBDT(numericTotalDeduct) }}</td>
              <td class="text-end fs-6">{{ $filter.formatBDT(numericGrandTotal) }}</td>
            </tr>
          </tfoot>
        </table>

        <!-- Signatures -->
        <div class="row text-center font-monospace small pt-5" style="margin-top: 60px;">
          <div class="col-4">
            <div class="border-top border-dark pt-1">{{ $t('Prepared By (HR)') }}</div>
          </div>
          <div class="col-4">
            <div class="border-top border-dark pt-1">{{ $t('Checked By (Accounts)') }}</div>
          </div>
          <div class="col-4">
            <div class="border-top border-dark pt-1">{{ $t('Approved By (Management)') }}</div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "salarySheet";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],
    };
  },

  computed: {
    numericTotalSalary() {
      if (!this.data.salary_sheet_details) return 0;
      return this.data.salary_sheet_details.reduce((sum, row) => sum + Number(row.salary || 0), 0);
    },
    numericTotalBonus() {
      if (!this.data.salary_sheet_details) return 0;
      return this.data.salary_sheet_details.reduce((sum, row) => sum + Number(row.bonus || 0), 0);
    },
    numericTotalInstallment() {
      if (!this.data.salary_sheet_details) return 0;
      return this.data.salary_sheet_details.reduce((sum, row) => sum + Number(row.installment || 0), 0);
    },
    numericTotalDeduct() {
      if (!this.data.salary_sheet_details) return 0;
      return this.data.salary_sheet_details.reduce((sum, row) => sum + Number(row.deduct || 0), 0);
    },
    numericGrandTotal() {
      if (!this.data.salary_sheet_details) return 0;
      return this.data.salary_sheet_details.reduce((sum, row) => sum + Number(row.total || 0), 0);
    }
  },

  methods: {
    approved() {
      if (!confirm(this.$t('Are you sure you want to approve this salary sheet?'))) {
        return;
      }
      axios
        .post('salarySheet/approved', {
          salary_sheet_id: this.$route.params.id
        })
        .then((res) => {
          this.$toast(res.data.message || 'Approved successfully', "success");
          this.get_data(`${this.model}/${this.$route.params.id}`);
        });
    },

    approvalcancel() {
      if (!confirm(this.$t('Are you sure you want to cancel the approval of this salary sheet?'))) {
        return;
      }
      axios
        .post('salarySheet/approvalcancel', {
          salary_sheet_id: this.$route.params.id
        })
        .then((res) => {
          this.$toast(res.data.message || 'Approval cancelled successfully', "success");
          this.get_data(`${this.model}/${this.$route.params.id}`);
        });
    }
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.salary-sheet-hero-banner {
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