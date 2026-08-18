<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Section -->
    <template v-slot:search-field>
      <!-- Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1"><i class="fas fa-calendar-alt me-1"></i>Quick Date:</span>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'all' }" @click="applyDatePreset('all')">All Time</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'today' }" @click="applyDatePreset('today')">Today</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'yesterday' }" @click="applyDatePreset('yesterday')">Yesterday</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'last7' }" @click="applyDatePreset('last7')">Last 7 Days</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisMonth' }" @click="applyDatePreset('thisMonth')">This Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'lastMonth' }" @click="applyDatePreset('lastMonth')">Last Month</button>
          <button type="button" class="btn btn-xs btn-outline-primary" :class="{ 'active': activePreset === 'thisYear' }" @click="applyDatePreset('thisYear')">This Year</button>
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
      <!-- Top Action Bar -->
      <div class="col-md-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <h6 class="mb-0 fw-bold text-secondary">
              <i class="fas fa-wallet me-1 text-primary"></i> Fund Accounts Overview (ফান্ড অ্যাকাউন্টস)
            </h6>
          </div>

          <!-- Print & Export Buttons -->
          <div class="d-flex align-items-center gap-2">
            <download-excel
              v-if="exportData.length > 0"
              class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
              :data="exportData"
              :name="exportFileName">
              <i class="fas fa-file-excel"></i> Export Excel
            </download-excel>
            <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
              data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Print Report"
              v-x-tooltip @click="print('printArea', model)">
              <i class="fas fa-print"></i> Print Report
            </button>
          </div>
        </div>
      </div>

      <!-- 🌟 Metric Summary Cards -->
      <div class="col-12 mb-4">
        <div class="row g-3">
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Total Available Fund</div>
                  <div class="fs-4 fw-bold mt-1">Tk. {{ formatMoney(totalFund) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-money-bill-wave"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Current Net Fund Balance</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Opening Balance (প্রারম্ভিক)</div>
                  <div class="fs-4 fw-bold mt-1">Tk. {{ formatMoney(summaryData.total_opening) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-balance-scale"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Base Opening Fund</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-info text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Total Inflow / Received (মোট জমা)</div>
                  <div class="fs-4 fw-bold mt-1">Tk. {{ formatMoney(summaryData.total_income) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Received / Inflow</small>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-danger text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Total Outflow / Paid (মোট খরচ)</div>
                  <div class="fs-4 fw-bold mt-1">Tk. {{ formatMoney(summaryData.total_expense) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-file-invoice-dollar"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Paid / Outflow</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 🖨️ Printable Area & Table -->
      <div class="col-12" id="printArea">
        <!-- 🧾 Report Header (Visible in Print) -->
        <div class="text-center mb-3 report-title">
          <h3 class="fw-bold">{{ $root.site.title || 'QPOS' }}</h3>
          <p class="mb-1 text-muted">{{ $root.site.address || 'Dhaka, Bangladesh' }}</p>
          <p class="text-muted small">
            Email: {{ $root.site.contact_email || 'N/A' }} | Phone: {{ $root.site.mobile1 || 'N/A' }}
          </p>
          <div class="border-top border-bottom py-2 my-2 bg-light">
            <h5 class="fw-bold mb-0 text-dark">
              <i class="fas fa-wallet me-1"></i>
              Fund Balance Report (ফান্ড ব্যালেন্স প্রতিবেদন)
            </h5>
            <small class="text-muted">
              Period: <strong>{{ dateRange }}</strong> | Report Date: <strong>{{ reportDate }}</strong>
            </small>
          </div>
        </div>

        <!-- 📊 Fund Accounts Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead class="table-dark text-center">
              <tr>
                <th width="5%">#</th>
                <th width="30%">Account Name (অ্যাকাউন্টের নাম)</th>
                <th width="15%">Opening Balance (প্রারম্ভিক স্থিতি)</th>
                <th width="16%">Total Inflow / Received (মোট জমা)</th>
                <th width="16%">Total Outflow / Paid (মোট খরচ)</th>
                <th width="18%">Current Balance (বর্তমান স্থিতি)</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="fundList.length > 0">
                <tr v-for="(record, index) in fundList" :key="index">
                  <td class="text-center">{{ index + 1 }}</td>
                  <td class="fw-bold text-dark">
                    <i class="fas fa-piggy-bank me-1 text-primary"></i>
                    {{ record.account_name }}
                  </td>
                  <td class="text-end font-monospace">
                    Tk. {{ formatMoney(record.opening_balance) }}
                    <small class="text-muted d-block" v-if="record.opening_balance_type">({{ record.opening_balance_type }})</small>
                  </td>
                  <td class="text-end font-monospace text-success fw-bold">
                    Tk. {{ formatMoney(record.total_income) }}
                  </td>
                  <td class="text-end font-monospace text-danger fw-bold">
                    Tk. {{ formatMoney(record.total_expense) }}
                  </td>
                  <td class="text-end font-monospace fw-bold fs-6" :class="Number(record.current_balance) >= 0 ? 'text-primary' : 'text-danger'">
                    Tk. {{ formatMoney(record.current_balance) }}
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No fund accounts found or no transactions recorded.
                  </td>
                </tr>
              </template>
            </tbody>
            <tfoot class="table-dark font-monospace fw-bold" v-if="fundList.length > 0">
              <tr>
                <td colspan="2" class="text-end">Total Grand Summary (সর্বমোট):</td>
                <td class="text-end">Tk. {{ formatMoney(summaryData.total_opening) }}</td>
                <td class="text-end text-success">Tk. {{ formatMoney(summaryData.total_income) }}</td>
                <td class="text-end text-warning">Tk. {{ formatMoney(summaryData.total_expense) }}</td>
                <td class="text-end text-info fs-6">Tk. {{ formatMoney(totalFund) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- 📌 Footer Note -->
        <div class="mt-4 small text-muted d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-0">Generated by: <strong>{{ $root.admin ? $root.admin.name : 'System Admin' }}</strong></p>
            <small>This report is system generated and authentic without signature.</small>
          </div>
          <div class="text-end">
            <div class="border-top pt-1 mt-4 px-4 d-inline-block text-center">
              <strong>Authorized Signature</strong>
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
        "SL": index + 1,
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
          this.$toast("Failed to load fund balance report data", "error");
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },

    formatMoney(val) {
      const num = Number(val || 0);
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
.btn-xs {
  padding: 2px 8px;
  font-size: 11px;
  font-weight: 500;
  border-radius: 4px;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
}

.bg-gradient-info {
  background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
}

.bg-gradient-success {
  background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
}

.bg-gradient-danger {
  background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
}

.metric-icon {
  font-size: 24px;
  opacity: 0.35;
}

@media print {
  .d-print-none {
    display: none !important;
  }
  .p_btn {
    display: none !important;
  }
}
</style>
