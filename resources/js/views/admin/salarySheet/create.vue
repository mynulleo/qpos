<template>
  <create-form @onSubmit="submit">
    <!-- Top Hero / Context Header Banner -->
    <div class="col-12 mb-3">
      <!-- Edit Mode Header Card -->
      <div v-if="$route.params.id" class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-gradient-primary-subtle border-bottom py-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm">
                <i class="fas fa-file-invoice-dollar fs-4"></i>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.title || $t('Payroll Sheet') }}</h5>
                  <span class="badge bg-white text-primary border shadow-xs px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                    {{ data.month }} {{ data.year }}
                  </span>
                  <span
                    class="badge rounded-pill px-2.5 py-1"
                    :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                    style="font-size: 0.75rem;"
                  >
                    <i :class="data.status === 'active' ? 'fas fa-check-circle' : 'fas fa-pause-circle'" class="me-1"></i>
                    {{ data.status === 'active' ? $t('Active Sheet') : $t('Draft / Inactive') }}
                  </span>
                </div>
                <small class="text-muted d-flex flex-wrap align-items-center gap-3">
                  <span><i class="fas fa-calendar-alt me-1 text-warning"></i> Generated: <strong class="text-dark">{{ data.generated_date }}</strong></span>
                  <span><i class="fas fa-users me-1 text-info"></i> Total Staff: <strong class="text-dark">{{ (data.salary_sheet_details || []).length }} Employees</strong></span>
                  <span><i class="fas fa-wallet me-1 text-success"></i> Net Payroll: <strong class="text-dark">৳ {{ formatCurrency(summaryGrandTotal) }}</strong></span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'salarySheet.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Sheets') }}
            </router-link>
          </div>
        </div>
      </div>

      <!-- Create Mode Header Card -->
      <div v-else class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-gradient-primary-subtle border-bottom py-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box bg-primary text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm">
                <i class="fas fa-file-invoice-dollar fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('Generate Monthly Salary Sheet & Payroll') }}</h5>
                <small class="text-muted">{{ $t('Review employee salaries, add bonuses, verify loan installments, and calculate net disbursements') }}</small>
              </div>
            </div>
            <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
              <i class="fas fa-calculator me-1 text-success"></i> {{ $t('Payroll Processing') }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 1. Master Configuration Fieldset -->
    <div class="row g-3 mb-3">
      <div class="col-12">
        <div class="card border shadow-sm rounded-3 bg-white">
          <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-sliders-h text-primary me-2"></i> {{ $t('Payroll Master Parameters') }}
            </h6>
            <span class="badge bg-light text-muted border px-2 py-0.5 small">
              {{ data.month || 'Month' }} {{ data.year || 'Year' }}
            </span>
          </div>
          <div class="card-body p-3">
            <div class="row g-3 align-items-center">
              <date-picker
                id="date3"
                v-model="data.generated_date"
                field="data.generated_date"
                title="Generated Date"
                placeholder="Generated Date"
                col="3"
                :req="true"
              ></date-picker>

              <Input
                v-model="data.title"
                field="data.title"
                col="3"
                title="Payroll Title"
                placeholder="e.g. Salary Sheet of October 2026"
                :req="true"
              />

              <Select
                title="Target Month"
                v-model="data.month"
                field="data.month"
                label="name"
                col="2"
                :reduce="(obj) => obj.value"
                :options="$root.global.montharray"
                placeholder="--Select Month--"
                :closeOnSelect="true"
                :required="true"
                @change="onMonthYearChange"
              />

              <Select
                title="Fiscal Year"
                v-model="data.year"
                field="data.year"
                label="name"
                col="2"
                :reduce="(obj) => obj.value"
                :options="$root.global.recentyears"
                placeholder="--Select Year--"
                :closeOnSelect="true"
                :required="true"
                @change="onMonthYearChange"
              />

              <Switch
                v-model="data.status"
                field="data.status"
                title="Status"
                on-label="Active"
                off-label="Inactive"
                col="2"
                :req="true"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Live Executive KPI Summary Cards -->
    <div class="row g-3 mb-3">
      <!-- Total Staff -->
      <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-primary">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">{{ $t('Total Employees') }}</div>
              <h4 class="fw-bold text-primary mb-0 mt-1">
                {{ $bnNum((data.salary_sheet_details || []).length) }} <span class="fs-6 fw-normal text-muted">{{ $t('Staff') }}</span>
              </h4>
              <div class="small text-muted mt-1">
                {{ $bnNum(filteredDetails.length) }} {{ $t('displaying in list') }}
              </div>
            </div>
            <div class="kpi-icon bg-primary-subtle text-primary rounded-circle p-3">
              <i class="fas fa-users fs-5"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Base Payroll -->
      <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-info">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">{{ $t('Total Basic Payroll') }}</div>
              <h4 class="fw-bold text-dark mb-0 mt-1">
                ৳ {{ formatCurrency(summaryTotalSalary) }}
              </h4>
              <div class="small text-muted mt-1">
                {{ $t('Base salary before adjustments') }}
              </div>
            </div>
            <div class="kpi-icon bg-info-subtle text-info rounded-circle p-3">
              <i class="fas fa-wallet fs-5"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Deductions & Loan Installments -->
      <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-danger">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">{{ $t('Loan Inst. & Deduct') }}</div>
              <h4 class="fw-bold text-danger mb-0 mt-1">
                ৳ {{ formatCurrency(summaryTotalInstallment + summaryTotalDeduct) }}
              </h4>
              <div class="small text-muted mt-1">
                {{ $t('Loan') }}: {{ formatCurrency(summaryTotalInstallment) }} | {{ $t('Ded') }}: {{ formatCurrency(summaryTotalDeduct) }}
              </div>
            </div>
            <div class="kpi-icon bg-danger-subtle text-danger rounded-circle p-3">
              <i class="fas fa-hand-holding-usd fs-5"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Net Payable -->
      <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3 border-start border-4 border-success">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">{{ $t('Net Payable Payroll') }}</div>
              <h4 class="fw-bold text-success mb-0 mt-1">
                ৳ {{ formatCurrency(summaryGrandTotal) }}
              </h4>
              <div class="small text-muted mt-1">
                {{ $t('Bonus') }}: ৳ {{ formatCurrency(summaryTotalBonus) }}
              </div>
            </div>
            <div class="kpi-icon bg-success-subtle text-success rounded-circle p-3">
              <i class="fas fa-money-bill-wave fs-5"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Employee Payroll & Deductions Matrix -->
    <div class="row g-3 mb-3">
      <div class="col-12">
        <div class="card border shadow-sm rounded-3 bg-white">
          <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-list-check text-primary me-2"></i> {{ $t('Employee Salary & Loan Installment Breakdown') }}
            </h6>

            <div class="d-flex align-items-center flex-wrap gap-2">
              <!-- Search by Name or Designation -->
              <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input
                  type="text"
                  v-model="searchQuery"
                  class="form-control border-start-0 border-end-0"
                  :placeholder="$t('Search employee / designation...')"
                />
                <button
                  v-if="searchQuery"
                  type="button"
                  class="btn btn-outline-secondary border-start-0"
                  @click="searchQuery = ''"
                  title="Clear search"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Recalculate Button -->
              <button
                type="button"
                class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs"
                @click="loadEmployees"
                :disabled="isLoading"
                v-if="!$route.params.id"
                title="Refresh Installment calculations based on selected Month/Year"
              >
                <i class="fas fa-sync-alt me-1" :class="{ 'fa-spin': isLoading }"></i>
                {{ isLoading ? $t('Loading...') : $t('Reload Staff') }}
              </button>
            </div>
          </div>

          <!-- Table Content -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-bordered table-sm align-middle mb-0">
                <thead class="table-light text-center small text-secondary">
                  <tr>
                    <th style="width: 45px;">#</th>
                    <th style="text-align: left; min-width: 200px;">{{ $t('Employee Information') }}</th>
                    <th style="width: 140px;" class="text-end">{{ $t('Basic Salary') }} (৳)</th>
                    <th style="width: 130px;" class="text-end">{{ $t('Bonus') }} (+)</th>
                    <th style="width: 140px;" class="text-end">{{ $t('Loan Inst.') }} (-)</th>
                    <th style="width: 130px;" class="text-end">{{ $t('Other Deduct') }} (-)</th>
                    <th style="width: 150px;" class="text-end">{{ $t('Net Payable') }} (৳)</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(row, idx) in filteredDetails" :key="row.employee_id || idx">
                    <td class="text-center fw-bold text-muted small">{{ $bnNum(idx + 1) }}</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;">
                          <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-dark">
                            {{ row.employee_name }}
                          </div>
                          <div class="small text-muted" v-if="row.designation">
                            <span class="badge bg-light text-secondary border small">{{ row.designation }}</span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Basic Salary (Readonly) -->
                    <td class="text-end">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0 small">৳</span>
                        <input
                          type="number"
                          step="any"
                          class="form-control text-end bg-light fw-semibold"
                          v-model="row.salary"
                          readonly
                        />
                      </div>
                    </td>

                    <!-- Bonus (+) -->
                    <td>
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-success-subtle text-success border-end-0 small">+</span>
                        <input
                          type="number"
                          step="any"
                          class="form-control text-end fw-bold text-success"
                          v-model="row.bonus"
                          placeholder="0"
                          @input="calculate(row)"
                        />
                      </div>
                    </td>

                    <!-- Loan Installment (-) -->
                    <td>
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-danger-subtle text-danger border-end-0 small">-</span>
                        <input
                          type="number"
                          step="any"
                          class="form-control text-end fw-bold text-danger"
                          v-model="row.installment"
                          placeholder="0"
                          @input="calculate(row)"
                        />
                      </div>
                      <div v-if="row.installment > 0" class="text-end mt-0.5">
                        <small class="badge bg-danger-subtle text-danger" style="font-size: 0.65rem;">
                          <i class="fas fa-hand-holding-usd me-0.5"></i> Loan Recovery
                        </small>
                      </div>
                    </td>

                    <!-- Deduct (-) -->
                    <td>
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-danger-subtle text-danger border-end-0 small">-</span>
                        <input
                          type="number"
                          step="any"
                          class="form-control text-end fw-bold text-danger"
                          v-model="row.deduct"
                          placeholder="0"
                          @input="calculate(row)"
                        />
                      </div>
                    </td>

                    <!-- Net Total (Calculated) -->
                    <td class="text-end pe-3">
                      <div class="d-flex align-items-center justify-content-end gap-1">
                        <span class="badge bg-success-subtle text-success fs-7 font-monospace fw-bold px-2.5 py-1.5 border border-success-subtle w-100 text-end">
                          ৳ {{ formatCurrency(row.total) }}
                        </span>
                      </div>
                    </td>
                  </tr>

                  <!-- Empty State -->
                  <tr v-if="filteredDetails.length === 0">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <i class="fas fa-search me-1"></i> No matching employees found.
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals -->
                <tfoot class="table-dark fw-bold small">
                  <tr>
                    <td colspan="2" class="text-uppercase ps-3 py-2">Total Payroll Summary:</td>
                    <td class="text-end font-monospace py-2">৳ {{ formatCurrency(summaryTotalSalary) }}</td>
                    <td class="text-end font-monospace text-success py-2">+৳ {{ formatCurrency(summaryTotalBonus) }}</td>
                    <td class="text-end font-monospace text-warning py-2">-৳ {{ formatCurrency(summaryTotalInstallment) }}</td>
                    <td class="text-end font-monospace text-danger py-2">-৳ {{ formatCurrency(summaryTotalDeduct) }}</td>
                    <td class="text-end font-monospace text-success pe-3 fs-6 py-2">৳ {{ formatCurrency(summaryGrandTotal) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Action Footer -->
    <template v-slot:form_footer>
      <div class="col-12 mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="text-muted small">
          <i class="fas fa-shield-alt text-success me-1"></i>
          {{ $t('Salary sheet creates individual payslip vouchers and prepares disbursable amounts.') }}
        </div>
        <div class="d-flex gap-2">
          <router-link :to="{ name: 'salarySheet.index' }" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> {{ $t('Cancel / Back') }}
          </router-link>
          <button type="submit" class="theme_btn px-4 py-2 rounded-pill shadow-sm" :disabled="$root.submit">
            <template v-if="$root.submit">
              <i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Processing...') }}
            </template>
            <template v-else>
              <i :class="$route.params.id ? 'fas fa-save' : 'fas fa-check-circle'" class="me-1"></i>
              {{ $route.params.id ? $t('Update Salary Sheet') : $t('Finalize & Generate Salary Sheet') }}
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = 'salarySheet';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      searchQuery: '',
      isLoading: false,
      data: {
        generated_date: this.$filter.today(),
        title: 'Salary Sheet of ' + this.$filter.enFormat(this.$filter.today(), 'MMMM YYYY'),
        month: this.$filter.enFormat(this.$filter.today(), 'MMMM'),
        year: this.$filter.enFormat(this.$filter.today(), 'YYYY'),
        status: 'active',
        salary_sheet_details: [],
      },
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  computed: {
    filteredDetails() {
      const details = this.data.salary_sheet_details || [];
      if (!this.searchQuery) return details;
      const q = this.searchQuery.toLowerCase().trim();
      return details.filter((item) => {
        const nameMatch = (item.employee_name || '').toLowerCase().includes(q);
        const desigMatch = (item.designation || '').toLowerCase().includes(q);
        return nameMatch || desigMatch;
      });
    },

    summaryTotalSalary() {
      return (this.data.salary_sheet_details || []).reduce((acc, row) => acc + (parseFloat(row.salary) || 0), 0);
    },

    summaryTotalBonus() {
      return (this.data.salary_sheet_details || []).reduce((acc, row) => acc + (parseFloat(row.bonus) || 0), 0);
    },

    summaryTotalInstallment() {
      return (this.data.salary_sheet_details || []).reduce((acc, row) => acc + (parseFloat(row.installment) || 0), 0);
    },

    summaryTotalDeduct() {
      return (this.data.salary_sheet_details || []).reduce((acc, row) => acc + (parseFloat(row.deduct) || 0), 0);
    },

    summaryGrandTotal() {
      return (this.data.salary_sheet_details || []).reduce((acc, row) => acc + (parseFloat(row.total) || 0), 0);
    },
  },

  methods: {
    formatCurrency(val) {
      const num = parseFloat(val) || 0;
      return num.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    onMonthYearChange() {
      if (this.data.month && this.data.year) {
        this.data.title = 'Salary Sheet of ' + this.data.month + ' ' + this.data.year;
      }
    },

    calculate(row) {
      const sal = parseFloat(row.salary) || 0;
      const bon = parseFloat(row.bonus) || 0;
      const com = parseFloat(row.commission) || 0;
      const ded = parseFloat(row.deduct) || 0;
      const ins = parseFloat(row.installment) || 0;
      row.total = sal + bon + com - ded - ins;
    },

    loadEmployees() {
      this.isLoading = true;
      axios
        .get('getsalarysheetdata')
        .then((res) => {
          this.data.salary_sheet_details = (res.data || []).map((emp) => ({
            employee_id: emp.employee_id,
            employee_name: emp.employee_name,
            designation: emp.designation,
            salary: parseFloat(emp.salary) || 0,
            bonus: 0,
            installment: parseFloat(emp.installment) || 0,
            installment_history: emp.installment_history,
            deduct: 0,
            total: (parseFloat(emp.salary) || 0) - (parseFloat(emp.installment) || 0),
            is_paid: 0,
            status: 'active',
          }));
          this.isLoading = false;
        })
        .catch((err) => {
          console.error(err);
          this.isLoading = false;
        });
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
      this.loadEmployees();
    }
  },

  validators: {
    'data.title': function (value = null) {
      return Validator.value(value).required('Title is required');
    },
    'data.month': function (value = null) {
      return Validator.value(value).required('Month is required');
    },
    'data.year': function (value = null) {
      return Validator.value(value).required('Year is required');
    },
    'data.generated_date': function (value = null) {
      return Validator.value(value).required('Generated Date is required');
    },
  },
};
</script>

<style scoped>
.salary-hero-banner {
  background: linear-gradient(135deg, #065f46 0%, #064e3b 100%);
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
  background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}

.kpi-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-primary-subtle {
  background-color: rgba(13, 110, 253, 0.1) !important;
}
.bg-info-subtle {
  background-color: rgba(13, 202, 240, 0.12) !important;
}
.bg-danger-subtle {
  background-color: rgba(220, 53, 69, 0.1) !important;
}
.bg-success-subtle {
  background-color: rgba(25, 135, 84, 0.12) !important;
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
