<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Select Employee" field="search_data.employee_id" col="4">
        <v-select
          v-model="search_data.employee_id"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="employees"
          placeholder="-- Select Employee --"
          :closeOnSelect="true"
          @input="search"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfromdate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date"
        placeholder="From Date"
        col="4"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtodate"
        v-model="search_data.to_date"
        field="search_data.to_date"
        title="To Date"
        placeholder="To Date"
        col="4"
        :req="false"
        :disablePastDates="search_data.from_date"
      ></date-picker>
    </template>

    <template v-slot:table-list>
      <!-- Action Bar with Print Button -->
      <div class="col-12 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge px-3 py-2 text-white font-monospace" style="background-color: #112C47; font-size: 12px;">
            <i class="fas fa-id-badge me-1"></i> {{ $t('Employee Ledger Statement') }}
          </span>
          <span v-if="datas.employee" class="text-muted small font-monospace">
            Period: <strong>{{ dateRange }}</strong>
          </span>
        </div>
        <div v-if="datas.employee" class="print_action text-end">
          <button
            class="btn btn-sm btn-outline-dark px-3 py-1.5 rounded-pill font-monospace fw-semibold shadow-sm d-flex align-items-center gap-1.5"
            @click="print('printArea', model)"
          >
            <i class="fas fa-print text-primary"></i>
            <span>{{ $t('Print Statement') }}</span>
          </button>
        </div>
      </div>

      <!-- Main Ledger View -->
      <div v-if="datas.employee" class="col-12">
        <!-- 🌟 Top Hero / Employee Banner -->
        <div class="card border-0 shadow-sm mb-4 ledger-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-user-cog fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ datas.employee.full_name }}
                    </h4>
                    <span v-if="datas.employee.empid" class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      #{{ datas.employee.empid }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span v-if="datas.employee.mobile">
                      <i class="fas fa-phone-alt me-1 text-success"></i> {{ datas.employee.mobile }}
                    </span>
                    <span v-if="datas.employee.email">
                      <i class="far fa-envelope me-1 text-info"></i> {{ datas.employee.email }}
                    </span>
                    <span v-if="datas.employee.address">
                      <i class="fas fa-map-marker-alt me-1 text-warning"></i> {{ datas.employee.address }}
                    </span>
                    <span>
                      <i class="far fa-calendar-alt me-1"></i> As of: {{ reportDate }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Balance Pill -->
              <div class="text-md-end text-white font-monospace">
                <div class="small text-white-50 text-uppercase">{{ $t('Closing Balance') }}</div>
                <div class="fs-4 fw-bold" :class="closingBalance > 0 ? 'text-warning' : 'text-white'">
                  {{ $filter.formatBDT(closingBalance) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ closingBalance > 0 ? $t('Payable Due') : (closingBalance < 0 ? $t('Advance / Loan Receivable') : $t('Settled')) }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 4 KPI Stat Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Opening Balance -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Opening Balance') }}</span>
                  <div class="stat-icon bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-history"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.opening_balance || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Previous Outstanding Due') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Period Credit (Salary / Commission Accrued) -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Credit (Payable)') }}</span>
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-coins"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                  {{ $filter.formatBDT(totalCredit) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Salary & Commissions Accrued') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Total Period Debit (Disbursed / Paid) -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Debit (Paid)') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-hand-holding-usd"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(totalDebit) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Payments & Disbursements') }}</small>
              </div>
            </div>
          </div>

          <!-- 4. Closing Balance -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Net Balance') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-balance-scale"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                  {{ $filter.formatBDT(closingBalance) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Current Outstanding Balance') }}</small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📑 Ledger Table Card -->
        <div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
          <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
              <i class="fas fa-receipt text-primary"></i>
              {{ $t('Transaction Ledger Breakdown') }}
            </span>
            <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1">
              {{ transactionCount }} {{ $t('Entries') }}
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted font-monospace">
                  <tr>
                    <th style="width: 120px;">{{ $t('Date') }}</th>
                    <th style="width: 150px;">{{ $t('Voucher No') }}</th>
                    <th>{{ $t('Particulars / Account') }}</th>
                    <th style="width: 140px;" class="text-end">{{ $t('Debit (Paid)') }}</th>
                    <th style="width: 140px;" class="text-end">{{ $t('Credit (Accrued)') }}</th>
                    <th style="width: 150px;" class="text-end">{{ $t('Balance (BDT)') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in datas.records"
                    :key="index"
                    :class="{ 'table-light fw-semibold': !record.id }"
                  >
                    <td class="font-monospace small">
                      <i class="far fa-calendar-alt text-secondary me-1"></i>
                      {{ $filter.enFormat(record.voucher_date) }}
                    </td>
                    <td class="font-monospace small">
                      <span v-if="record.voucherno" class="badge bg-light text-dark border font-monospace">
                        {{ record.voucherno }}
                      </span>
                      <span v-else class="text-muted">—</span>
                    </td>
                    <td>
                      <span class="font-monospace text-dark" :class="{ 'fw-bold text-primary': !record.id }">
                        {{ record.account_name }}
                      </span>
                    </td>
                    <td class="text-end font-monospace" :class="Number(record.dr_amount) > 0 ? 'text-success fw-semibold' : 'text-muted'">
                      {{ Number(record.dr_amount) > 0 ? $filter.formatBDT(record.dr_amount) : '—' }}
                    </td>
                    <td class="text-end font-monospace" :class="Number(record.cr_amount) > 0 ? 'text-danger fw-semibold' : 'text-muted'">
                      {{ Number(record.cr_amount) > 0 ? $filter.formatBDT(record.cr_amount) : '—' }}
                    </td>
                    <td class="text-end font-monospace fw-bold" :class="Number(record.running_balance) > 0 ? 'text-dark' : 'text-success'">
                      {{ $filter.formatBDT(record.running_balance) }}
                    </td>
                  </tr>
                </tbody>
                <!-- 🎯 Properly Right-Aligned Table Footer -->
                <tfoot class="table-light font-monospace fw-bold">
                  <tr>
                    <td colspan="3" class="text-end text-uppercase">{{ $t('Total Period Activity') }}:</td>
                    <td class="text-end text-success fs-6">{{ $filter.formatBDT(totalDebit) }}</td>
                    <td class="text-end text-danger fs-6">{{ $filter.formatBDT(totalCredit) }}</td>
                    <td class="text-end text-primary fs-6">{{ $filter.formatBDT(closingBalance) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- 🖨️ Printable Ledger Sheet -->
        <div id="printArea" class="d-none d-print-block print-container p-4 bg-white text-dark">
          <div class="print-border p-4">
            <!-- Organization Header -->
            <div class="text-center pb-3 border-bottom mb-4">
              <h3 class="fw-bold mb-1 text-uppercase">{{ $root.site?.title || $root.global?.site_name || 'QPOS ERP' }}</h3>
              <p class="mb-1 text-muted small">{{ $root.site?.address || $root.global?.address }}</p>
              <p class="mb-1 text-muted small">Email: {{ $root.site?.contact_email }} | Phone: {{ $root.site?.mobile1 }}</p>
              <div class="d-inline-block border border-dark px-3 py-1 mt-2 text-uppercase fw-bold font-monospace fs-6">
                {{ $t('EMPLOYEE STATEMENT OF ACCOUNT') }}
              </div>
            </div>

            <!-- Employee Metadata -->
            <div class="row g-3 mb-4 font-monospace small">
              <div class="col-7">
                <div><strong>{{ $t('Employee Name') }}:</strong> {{ datas.employee.full_name }}</div>
                <div v-if="datas.employee.empid"><strong>{{ $t('Employee ID') }}:</strong> {{ datas.employee.empid }}</div>
                <div><strong>{{ $t('Contact Mobile') }}:</strong> {{ datas.employee.mobile }}</div>
                <div v-if="datas.employee.email"><strong>{{ $t('Email') }}:</strong> {{ datas.employee.email }}</div>
              </div>
              <div class="col-5 text-end">
                <div><strong>{{ $t('Statement Date') }}:</strong> {{ reportDate }}</div>
                <div><strong>{{ $t('Period') }}:</strong> {{ dateRange }}</div>
                <div><strong>{{ $t('Opening Balance') }}:</strong> {{ $filter.formatBDT(datas.opening_balance || 0) }}</div>
                <div><strong>{{ $t('Closing Balance') }}:</strong> {{ $filter.formatBDT(closingBalance) }}</div>
              </div>
            </div>

            <!-- Ledger Table -->
            <table class="table table-bordered mb-4 small font-monospace">
              <thead class="table-light">
                <tr>
                  <th style="width: 100px;">{{ $t('Date') }}</th>
                  <th style="width: 130px;">{{ $t('Voucher No') }}</th>
                  <th>{{ $t('Particulars') }}</th>
                  <th style="width: 120px;" class="text-end">{{ $t('Debit') }}</th>
                  <th style="width: 120px;" class="text-end">{{ $t('Credit') }}</th>
                  <th style="width: 130px;" class="text-end">{{ $t('Balance') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(record, idx) in datas.records" :key="idx">
                  <td>{{ $filter.enFormat(record.voucher_date) }}</td>
                  <td>{{ record.voucherno || '—' }}</td>
                  <td>{{ record.account_name }}</td>
                  <td class="text-end">{{ Number(record.dr_amount) > 0 ? $filter.formatBDT(record.dr_amount) : '—' }}</td>
                  <td class="text-end">{{ Number(record.cr_amount) > 0 ? $filter.formatBDT(record.cr_amount) : '—' }}</td>
                  <td class="text-end fw-bold">{{ $filter.formatBDT(record.running_balance) }}</td>
                </tr>
              </tbody>
              <tfoot class="fw-bold">
                <tr>
                  <td colspan="3" class="text-end text-uppercase">{{ $t('Total') }}:</td>
                  <td class="text-end">{{ $filter.formatBDT(totalDebit) }}</td>
                  <td class="text-end">{{ $filter.formatBDT(totalCredit) }}</td>
                  <td class="text-end">{{ $filter.formatBDT(closingBalance) }}</td>
                </tr>
              </tfoot>
            </table>

            <!-- Signatures -->
            <div class="row text-center font-monospace small pt-5" style="margin-top: 60px;">
              <div class="col-4">
                <div class="border-top border-dark pt-1">{{ $t('Prepared By') }}</div>
              </div>
              <div class="col-4">
                <div class="border-top border-dark pt-1">{{ $t('Checked By') }}</div>
              </div>
              <div class="col-4">
                <div class="border-top border-dark pt-1">{{ $t('Employee Signature') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="col-12 py-5 text-center">
        <div class="card border-0 shadow-sm p-5 bg-white rounded-3">
          <div class="text-muted mb-3">
            <i class="fas fa-search fs-1 text-secondary opacity-50"></i>
          </div>
          <h5 class="fw-bold text-dark">{{ $t('No Employee Selected') }}</h5>
          <p class="text-muted small mb-0">
            {{ $t('Please select an employee from the dropdown above to load their interactive transaction ledger statement.') }}
          </p>
        </div>
      </div>
    </template>
  </index-page>
</template>

<script>
import moment from "moment";

const model = "employeeledger";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      reportDate: moment().format('D MMMM, YYYY'),
      search_data: {
        employee_id: this.$route.query.employee_id ? Number(this.$route.query.employee_id) : null,
        from_date: this.$route.query.from_date ?? "",
        to_date: this.$route.query.to_date ?? "",
      },
      table: {
        columns: [],
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      datas: { employee: null, opening_balance: 0, records: [] },
      employees: [],
    };
  },

  provide() {
    return {
      validate: this.validation,
      model: this.model,
      search_data: this.search_data,
      table: this.table,
      search: this.search,
      resetSearchData: this.resetSearchData,
    };
  },

  computed: {
    dateRange() {
      if (this.search_data.from_date && this.search_data.to_date) {
        return (
          moment(this.search_data.from_date).format("D MMM, YYYY") +
          " to " +
          moment(this.search_data.to_date).format("D MMM, YYYY")
        );
      }
      return "All Available Dates";
    },

    totalDebit() {
      if (!this.datas?.records?.length) return 0;
      return this.datas.records.reduce((sum, r) => sum + (parseFloat(r.dr_amount) || 0), 0);
    },

    totalCredit() {
      if (!this.datas?.records?.length) return 0;
      return this.datas.records.reduce((sum, r) => sum + (parseFloat(r.cr_amount) || 0), 0);
    },

    closingBalance() {
      if (!this.datas?.records?.length) return Number(this.datas?.opening_balance || 0);
      const lastRow = this.datas.records[this.datas.records.length - 1];
      return parseFloat(lastRow.running_balance) || 0;
    },

    transactionCount() {
      if (!this.datas?.records) return 0;
      return this.datas.records.filter((r) => r.id).length;
    }
  },

  methods: {
    search() {
      if (!this.search_data.employee_id) return;
      this.getEmployeeLedger();
    },

    resetSearchData() {
      this.search_data.employee_id = null;
      this.search_data.from_date = "";
      this.search_data.to_date = "";
      this.datas = { employee: null, opening_balance: 0, records: [] };
    },

    getEmployees() {
      axios
        .get("employees/")
        .then((response) => {
          this.employees = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },

    getEmployeeLedger() {
      if (!this.search_data.employee_id) return;
      this.$root.tableSpinner = true;
      axios
        .get("report/employeeledger/", { params: this.search_data })
        .then((res) => {
          this.datas = res.data || { employee: null, opening_balance: 0, records: [] };
        })
        .catch((error) => {
          console.error(error);
        })
        .finally(() => {
          this.$root.tableSpinner = false;
          this.$root.spinner = false;
        });
    },

    print(divId, model) {
      this.$htmlToPaper(divId);
    }
  },

  created() {
    this.getEmployees();
    if (this.search_data.employee_id) {
      this.getEmployeeLedger();
    }
  },

  validators: {},
};
</script>

<style scoped>
.ledger-hero-banner {
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
