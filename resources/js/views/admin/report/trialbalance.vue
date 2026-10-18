<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
      <date-picker
        id="searchfromdate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date"
        placeholder="From Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtodate"
        v-model="search_data.to_date"
        field="search_data.to_date"
        title="To Date"
        placeholder="To Date"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_date"
      ></date-picker>
    </template>

    <template v-slot:table-list>
      <!-- Action Bar with Single Print Button -->
      <div class="col-12 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge px-3 py-2 text-white font-monospace" style="background-color: #112C47; font-size: 12px;">
            <i class="fas fa-balance-scale me-1"></i> {{ $t('Trial Balance Report') }}
          </span>
          <span class="text-muted small font-monospace">
            Period: <strong>{{ dateRange }}</strong>
          </span>
        </div>
        <div class="print_action text-end">
          <button
            class="btn btn-sm btn-outline-dark px-3 py-1.5 rounded-pill font-monospace fw-semibold shadow-sm d-flex align-items-center gap-1.5"
            @click="print('printArea', model)"
          >
            <i class="fas fa-print text-primary"></i>
            <span>{{ $t('Print Statement') }}</span>
          </button>
        </div>
      </div>

      <!-- Main Trial Balance View -->
      <div class="col-12">
        <!-- 🌟 Top Hero Banner -->
        <div class="card border-0 shadow-sm mb-4 trialbalance-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-scale-balanced fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ $t('General Ledger Trial Balance') }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ isBalanced ? $t('Balanced Parity') : $t('Discrepancy Detected') }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span>
                      <i class="far fa-calendar-alt me-1"></i> Period: {{ dateRange }}
                    </span>
                    <span>
                      <i class="fas fa-clock me-1"></i> Generated: {{ reportDate }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Balance Pill -->
              <div class="text-md-end text-white font-monospace">
                <div class="small text-white-50 text-uppercase">{{ $t('Accounting Parity') }}</div>
                <div class="fs-4 fw-bold" :class="isBalanced ? 'text-success' : 'text-danger'">
                  <i class="fas me-1" :class="isBalanced ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                  {{ isBalanced ? $t('Balanced') : $filter.formatBDT(variance) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ isBalanced ? $t('Total Debit equals Total Credit') : $t('Unbalanced Variance') }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 3 KPI Stat Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Total Debit -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Debit') }}</span>
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-down"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_debit || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Sum of all debit account balances') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Credit -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Credit') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-up"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_credit || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Sum of all credit account balances') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Parity Variance -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-4"
              :class="isBalanced ? 'border-primary' : 'border-warning'">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Variance / Difference') }}</span>
                  <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center"
                    :class="isBalanced ? 'bg-primary bg-opacity-10 text-primary' : 'bg-warning bg-opacity-10 text-warning'">
                    <i class="fas" :class="isBalanced ? 'fa-check' : 'fa-balance-scale-right'"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 font-monospace text-nowrap" :class="isBalanced ? 'text-primary' : 'text-danger'">
                  {{ $filter.formatBDT(variance) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">
                  {{ isBalanced ? $t('Accounts in equilibrium (0.00)') : $t('Discrepancy to investigate') }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📑 Tabular Statement Card & Print Area -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white" id="printArea">
          <!-- Print Only Header -->
          <div class="d-none print-only-header p-4 border-bottom">
            <div class="text-center mb-3">
              <h3 class="fw-bold mb-0 text-dark">{{ $root.site.title }}</h3>
              <p class="mb-1 text-muted small">{{ $root.site.address }}</p>
              <p class="mb-0 text-muted small">
                Email: {{ $root.site.contact_email }} | Phone: {{ $root.site.mobile1 }}
              </p>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div>
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Trial Balance Statement') }}</h5>
                <div class="small text-dark font-monospace">
                  Period: <strong>{{ dateRange }}</strong>
                </div>
              </div>
              <div class="text-end font-monospace small">
                <div>Generated: <strong>{{ reportDate }}</strong></div>
              </div>
            </div>
          </div>

          <!-- Table Content -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 trialbalance-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 220px;">{{ $t('Account Type') }}</th>
                    <th class="py-3">{{ $t('Account Head') }}</th>
                    <th class="py-3 text-end" style="width: 200px;">{{ $t('Debit Amount') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 200px;">{{ $t('Credit Amount') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in filteredAccounts"
                    :key="index"
                    :class="record.parent_id == null ? 'bg-light-subtle' : ''"
                  >
                    <!-- Parent Category Heading -->
                    <template v-if="record.parent_id == null">
                      <td class="ps-4 py-3">
                        <span class="badge px-2.5 py-1 text-white font-monospace text-uppercase" style="background-color: #112C47; font-size: 11px;">
                          {{ record.account_type || 'Category' }}
                        </span>
                      </td>
                      <td class="py-3 fw-bold text-dark fs-6" colspan="3">
                        <i class="fas fa-folder-open me-2 text-primary opacity-75"></i>
                        {{ record.account_name }}
                      </td>
                    </template>

                    <!-- Sub Account Ledger Record -->
                    <template v-else>
                      <td class="ps-4 py-2.5 font-monospace small text-muted">
                        <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-0.5">
                          {{ record.account_type }}
                        </span>
                      </td>
                      <td class="py-2.5">
                        <span class="fw-semibold text-dark ps-2">
                          {{ record.account_name }}
                        </span>
                      </td>
                      <td class="py-2.5 text-end font-monospace">
                        <span v-if="Number(record.debit) > 0" class="text-danger fw-semibold">
                          {{ $filter.formatBDT(record.debit) }}
                        </span>
                        <span v-else class="text-muted">-</span>
                      </td>
                      <td class="pe-4 py-2.5 text-end font-monospace">
                        <span v-if="Number(record.credit) > 0" class="text-success fw-semibold">
                          {{ $filter.formatBDT(record.credit) }}
                        </span>
                        <span v-else class="text-muted">-</span>
                      </td>
                    </template>
                  </tr>

                  <tr v-if="!datas.accounts || datas.accounts.length === 0">
                    <td colspan="4" class="text-center py-5 text-muted">
                      <i class="fas fa-scale-balanced fa-3x mb-3 text-secondary opacity-50"></i>
                      <div class="font-monospace">{{ $t('No accounts available for trial balance.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="2" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Grand Total') }}
                    </td>
                    <td class="py-3 text-end text-danger fs-6">
                      {{ $filter.formatBDT(datas.total_debit || 0) }}
                    </td>
                    <td class="pe-4 py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(datas.total_credit || 0) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Print Footer -->
          <div class="d-none print-only-header p-4 border-top">
            <div class="row align-items-center text-muted small font-monospace">
              <div class="col-6">
                <div>Generated by: <strong>Admin</strong></div>
                <div style="font-size: 10px;">This is a computer-generated statement and requires no signature.</div>
              </div>
              <div class="col-6 text-end">
                <div>Page 1 of 1</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </index-page>
</template>

<script>
import axios from "axios";
import moment from "moment";

const tableColumns = [{ field: "status", title: "Status", align: "center" }];
const json_fields = {
  Title: "Trial Balance",
};
const model = "trialbalance";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      search_data: {
        from_date: null,
        to_date: null,
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      datas: {
        accounts: [],
        total_debit: 0,
        total_credit: 0,
      },
    };
  },

  provide() {
    return {
      validate: this.validation,
      model: this.model,
      search_data: this.search_data,
      table: this.table,
      json_fields: this.json_fields,
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
      return "Current Period";
    },

    variance() {
      const dr = Number(this.datas.total_debit) || 0;
      const cr = Number(this.datas.total_credit) || 0;
      return Math.abs(dr - cr);
    },

    isBalanced() {
      return this.variance < 0.01;
    },

    filteredAccounts() {
      if (!this.datas.accounts) return [];
      // Show parents and children that have activity or all children
      return this.datas.accounts;
    },
  },

  methods: {
    search() {
      this.getTrialBalance();
    },

    getTrialBalance() {
      axios
        .get(`report/trialbalance/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    resetSearchData() {
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.getTrialBalance();
    },
  },

  created() {
    this.getTrialBalance();
  },

  validators: {},
};
</script>

<style scoped>
.trialbalance-hero-banner {
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
.trialbalance-table th {
  font-size: 11.5px;
  letter-spacing: 0.5px;
}

@media print {
  .print-only-header {
    display: block !important;
  }
}
</style>
