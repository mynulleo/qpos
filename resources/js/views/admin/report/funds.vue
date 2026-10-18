<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1 font-monospace">
            <i class="fas fa-calendar-alt me-1 text-primary"></i>{{ $t("Quick Date:") }}
          </span>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'all' }" @click="applyDatePreset('all')">{{ $t("All Time") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'today' }" @click="applyDatePreset('today')">{{ $t("Today") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'yesterday' }" @click="applyDatePreset('yesterday')">{{ $t("Yesterday") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'last7' }" @click="applyDatePreset('last7')">{{ $t("Last 7 Days") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'thisMonth' }" @click="applyDatePreset('thisMonth')">{{ $t("This Month") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'lastMonth' }" @click="applyDatePreset('lastMonth')">{{ $t("Last Month") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace" :class="{ 'active': activePreset === 'thisYear' }" @click="applyDatePreset('thisYear')">{{ $t("This Year") }}</button>
        </div>
      </div>

      <!-- Date Range -->
      <date-picker id="searchfromdate" v-model="search_data.from_date" field="search_data.from_date"
        title="From Date" placeholder="From Date" col="3" :req="false"></date-picker>
      <date-picker id="searchtodate" v-model="search_data.to_date" field="search_data.to_date"
        title="To Date" placeholder="To Date" col="3" :req="false"></date-picker>
    </template>

    <!-- 📊 Table & Content List -->
    <template v-slot:table-list>
      <!-- Action Bar with Single Print & Excel Button -->
      <div class="col-12 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge px-3 py-2 text-white font-monospace" style="background-color: #112C47; font-size: 12px;">
            <i class="fas fa-wallet me-1"></i> {{ $t('Fund Accounts Position Report') }}
          </span>
          <span class="text-muted small font-monospace">
            Period: <strong>{{ dateRange }}</strong>
          </span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <download-excel
            v-if="exportData.length > 0"
            class="btn btn-sm btn-outline-success px-3 py-1.5 rounded-pill font-monospace fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5"
            :data="exportData"
            :name="exportFileName"
          >
            <i class="fas fa-file-excel"></i>
            <span>{{ $t("Export Excel") }}</span>
          </download-excel>
          <button
            class="btn btn-sm btn-outline-dark px-3 py-1.5 rounded-pill font-monospace fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5"
            @click="print('printArea', model)"
          >
            <i class="fas fa-print text-primary"></i>
            <span>{{ $t('Print Report') }}</span>
          </button>
        </div>
      </div>

      <!-- Main Funds View -->
      <div class="col-12">
        <!-- 🌟 Top Hero Banner -->
        <div class="card border-0 shadow-sm mb-4 funds-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-piggy-bank fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ $t('Cash & Bank Fund Balances') }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ fundList.length }} {{ $t('Liquid Accounts') }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span>
                      <i class="far fa-calendar-alt me-1"></i> Period: {{ dateRange }}
                    </span>
                    <span>
                      <i class="fas fa-clock me-1"></i> As of: {{ reportDate }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Balance Pill -->
              <div class="text-md-end text-white font-monospace">
                <div class="small text-white-50 text-uppercase">{{ $t('Total Available Liquidity') }}</div>
                <div class="fs-4 fw-bold text-white">
                  {{ $filter.formatBDT(totalFund) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ $t('Consolidated Net Fund Balance') }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 4 KPI Stat Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Total Opening -->
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
                  {{ $filter.formatBDT(summaryData.total_opening || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Base Opening Funds') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Inflow -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Inflow / Received') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-down"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(summaryData.total_income || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Collections & Deposits') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Total Outflow -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Outflow / Paid') }}</span>
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-up"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                  {{ $filter.formatBDT(summaryData.total_expense || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Payments & Disbursements') }}</small>
              </div>
            </div>
          </div>

          <!-- 4. Total Net Liquidity -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Net Liquid Balance') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-wallet"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                  {{ $filter.formatBDT(totalFund) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Total Available Cash & Bank') }}</small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📑 Tabular Statement Card & Print Area -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white" id="printArea">
          <!-- Print Only Header -->
          <div class="d-none print-only-header p-4 border-bottom">
            <div class="text-center mb-3">
              <h3 class="fw-bold mb-0 text-dark">{{ $root.site.title || 'QPOS' }}</h3>
              <p class="mb-1 text-muted small">{{ $root.site.address }}</p>
              <p class="mb-0 text-muted small">
                Email: {{ $root.site.contact_email }} | Phone: {{ $root.site.mobile1 }}
              </p>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div>
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Fund Accounts Balance Report') }}</h5>
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
              <table class="table table-hover align-middle mb-0 funds-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 70px;">{{ $t('#') }}</th>
                    <th class="py-3">{{ $t('Account Name') }}</th>
                    <th class="py-3 text-end" style="width: 170px;">{{ $t('Opening Balance') }}</th>
                    <th class="py-3 text-end" style="width: 170px;">{{ $t('Inflow / Received') }}</th>
                    <th class="py-3 text-end" style="width: 170px;">{{ $t('Outflow / Paid') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 180px;">{{ $t('Current Balance') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(record, index) in fundList" :key="index">
                    <td class="ps-4 py-2.5 font-monospace small text-muted">
                      {{ index + 1 }}
                    </td>
                    <td class="py-2.5">
                      <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-piggy-bank text-primary opacity-75"></i>
                        <span>{{ record.account_name }}</span>
                      </div>
                    </td>
                    <td class="py-2.5 text-end font-monospace">
                      {{ $filter.formatBDT(record.opening_balance) }}
                      <small class="text-muted d-block" v-if="record.opening_balance_type" style="font-size: 10px;">
                        ({{ record.opening_balance_type }})
                      </small>
                    </td>
                    <td class="py-2.5 text-end font-monospace text-success fw-semibold">
                      {{ $filter.formatBDT(record.total_income) }}
                    </td>
                    <td class="py-2.5 text-end font-monospace text-danger fw-semibold">
                      {{ $filter.formatBDT(record.total_expense) }}
                    </td>
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold fs-6" :class="Number(record.current_balance) >= 0 ? 'text-primary' : 'text-danger'">
                      {{ $filter.formatBDT(record.current_balance) }}
                    </td>
                  </tr>

                  <tr v-if="!fundList || fundList.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-wallet fa-3x mb-3 text-secondary opacity-50"></i>
                      <div class="font-monospace">{{ $t('No fund accounts or transactions found for this period.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot v-if="fundList && fundList.length > 0" style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="2" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Total Grand Summary') }}
                    </td>
                    <td class="py-3 text-end text-dark fs-6">
                      {{ $filter.formatBDT(summaryData.total_opening || 0) }}
                    </td>
                    <td class="py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(summaryData.total_income || 0) }}
                    </td>
                    <td class="py-3 text-end text-danger fs-6">
                      {{ $filter.formatBDT(summaryData.total_expense || 0) }}
                    </td>
                    <td class="pe-4 py-3 text-end text-primary fs-6">
                      {{ $filter.formatBDT(totalFund) }}
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
                <div>Generated by: <strong>{{ $root.admin ? $root.admin.name : 'System Admin' }}</strong></div>
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
import JsonExcel from "vue-json-excel3";

const model = "funds";

export default {
  components: {
    downloadExcel: JsonExcel,
  },

  data() {
    return {
      model: model,
      page_title: "Fund Balance Report",
      reportDate: moment().format("D MMMM, YYYY h:mm A"),
      activePreset: "all",
      search_data: {
        from_date: null,
        to_date: null,
      },
      table: {
        columns: [],
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      datas: {
        funds: [],
        total_fund: 0,
        summary: {
          total_opening: 0,
          total_income: 0,
          total_expense: 0,
          total_fund: 0,
          total_accounts: 0,
        },
      },
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
    fundList() {
      if (this.datas && Array.isArray(this.datas.funds)) {
        return this.datas.funds;
      }
      return [];
    },

    totalFund() {
      if (this.datas && this.datas.total_fund !== undefined) {
        return this.datas.total_fund;
      }
      return 0;
    },

    summaryData() {
      if (this.datas && this.datas.summary) {
        return this.datas.summary;
      }
      return {
        total_opening: 0,
        total_income: 0,
        total_expense: 0,
        total_fund: this.totalFund,
        total_accounts: this.fundList.length,
      };
    },

    dateRange() {
      if (this.search_data.from_date && this.search_data.to_date) {
        return (
          moment(this.search_data.from_date).format("D MMM, YYYY") +
          " to " +
          moment(this.search_data.to_date).format("D MMM, YYYY")
        );
      }
      return "All Time";
    },

    exportFileName() {
      return `Fund_Balance_Report_${moment().format("YYYY-MM-DD")}.xls`;
    },

    exportData() {
      return this.fundList.map((record, index) => ({
        SL: index + 1,
        "Account Name": record.account_name,
        "Opening Balance": Number(record.opening_balance || 0),
        "Opening Type": record.opening_balance_type || "Debit",
        "Total Inflow / Received": Number(record.total_income || 0),
        "Total Outflow / Paid": Number(record.total_expense || 0),
        "Current Balance": Number(record.current_balance || 0),
      }));
    },
  },

  methods: {
    search() {
      this.getFunds();
    },

    resetSearchData() {
      this.activePreset = "all";
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.getFunds();
    },

    applyDatePreset(preset) {
      this.activePreset = preset;
      const today = moment();

      switch (preset) {
        case "all":
          this.search_data.from_date = null;
          this.search_data.to_date = null;
          break;
        case "today":
          this.search_data.from_date = today.format("YYYY-MM-DD");
          this.search_data.to_date = today.format("YYYY-MM-DD");
          break;
        case "yesterday":
          const yest = moment().subtract(1, "days");
          this.search_data.from_date = yest.format("YYYY-MM-DD");
          this.search_data.to_date = yest.format("YYYY-MM-DD");
          break;
        case "last7":
          this.search_data.from_date = moment().subtract(6, "days").format("YYYY-MM-DD");
          this.search_data.to_date = today.format("YYYY-MM-DD");
          break;
        case "thisMonth":
          this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
          this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
          break;
        case "lastMonth":
          const lastMonth = moment().subtract(1, "months");
          this.search_data.from_date = lastMonth.startOf("month").format("YYYY-MM-DD");
          this.search_data.to_date = lastMonth.endOf("month").format("YYYY-MM-DD");
          break;
        case "thisYear":
          this.search_data.from_date = moment().startOf("year").format("YYYY-MM-DD");
          this.search_data.to_date = moment().endOf("year").format("YYYY-MM-DD");
          break;
      }
      this.getFunds();
    },

    getFunds() {
      this.$root.spinner = true;
      this.reportDate = moment().format("D MMMM, YYYY h:mm A");

      axios
        .get("report/funds", { params: this.search_data })
        .then((res) => {
          if (res.data) {
            this.datas = res.data;
          }
        })
        .catch((err) => {
          console.error("Funds report error:", err);
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },
  },

  created() {
    this.page_title = "Fund Balance Report";
    this.getFunds();
  },

  validators: {},
};
</script>

<style scoped>
.funds-hero-banner {
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
.btn-xs {
  padding: 3px 10px;
  font-size: 11.5px;
  border-radius: 20px;
}
.funds-table th {
  font-size: 11.5px;
  letter-spacing: 0.5px;
}

@media print {
  .print-only-header {
    display: block !important;
  }
}
</style>
