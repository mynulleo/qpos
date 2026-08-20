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

      <!-- Client Selection -->
      <v-select-container title="Select Client" field="search_data.client_id" col="3">
        <v-select
          v-model="search_data.client_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="clients"
          placeholder="-- All Clients --"
          :closeOnSelect="true">
          <template v-slot:option="option">
            <div class="d-flex justify-content-between align-items-center">
              <span>{{ option.name }}</span>
              <small class="text-muted font-monospace">{{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <!-- Transaction Type -->
      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-muted">Transaction Type (লেনদেনের ধরন)</label>
          <select class="form-select form-select-sm" v-model="search_data.type">
            <option value="">-- All Types (সকল ধরন) --</option>
            <option value="Earn">Points Earned (পয়েন্ট জমা)</option>
            <option value="Redeem">Points Redeemed (পয়েন্ট ছাড়/ডিসকাউন্ট)</option>
            <option value="Convert_To_Cash">Converted to Cash (টাকায় রূপান্তর)</option>
          </select>
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
            <h6 class="mb-0 fw-bold text-secondary d-flex align-items-center gap-2">
              <i class="fas fa-gift text-primary fs-5"></i>
              <span>Coupon & Customer Loyalty Points Report (কুপন ও রিওয়ার্ড পয়েন্ট রিপোর্ট)</span>
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
          <!-- Total Points Earned -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Total Points Earned</div>
                  <div class="fs-4 fw-bold mt-1">{{ formatNumber(summaryData.total_earned_all_time) }} Pts</div>
                </div>
                <div class="metric-icon"><i class="fas fa-coins"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Period Earned: {{ formatNumber(summaryData.period_earned) }} Pts</small>
            </div>
          </div>

          <!-- Total Points Redeemed -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-warning text-dark h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-muted small fw-bold text-uppercase">Total Points Redeemed</div>
                  <div class="fs-4 fw-bold mt-1 text-dark">{{ formatNumber(summaryData.total_redeemed_all_time) }} Pts</div>
                </div>
                <div class="metric-icon"><i class="fas fa-hand-holding-usd text-dark"></i></div>
              </div>
              <small class="text-muted mt-2 d-block">Period Redeemed: {{ formatNumber(summaryData.period_redeemed) }} Pts</small>
            </div>
          </div>

          <!-- Active Points in Circulation -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Active Points Balance</div>
                  <div class="fs-4 fw-bold mt-1">{{ formatNumber(summaryData.total_active_points) }} Pts</div>
                </div>
                <div class="metric-icon"><i class="fas fa-wallet"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Customer Outstanding Balance</small>
            </div>
          </div>

          <!-- Total Liability Equivalent in Tk -->
          <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-info text-white h-100">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="text-white-50 small fw-bold text-uppercase">Points Liability Value</div>
                  <div class="fs-4 fw-bold mt-1">Tk. {{ formatMoney(summaryData.total_liability_tk) }}</div>
                </div>
                <div class="metric-icon"><i class="fas fa-tag"></i></div>
              </div>
              <small class="text-white-50 mt-2 d-block">Equivalent Cash Value</small>
            </div>
          </div>
        </div>
      </div>

      <!-- 📋 Printable Report Area & Screen Data Table -->
      <div class="col-12" id="printArea">
        <!-- 🧾 Report Header (Visible in Print) -->
        <div class="text-center mb-3 report-title d-none d-print-block">
          <h3 class="fw-bold mb-0">{{ $root.site ? $root.site.title : 'QPOS Store' }}</h3>
          <p class="mb-1 small">{{ $root.site ? $root.site.address : '' }}</p>
          <p class="small text-muted">Phone: {{ $root.site ? $root.site.mobile1 : '' }} | Email: {{ $root.site ? $root.site.contact_email : '' }}</p>
          <h5 class="fw-bold mt-2 text-decoration-underline">Coupon & Customer Loyalty Points Statement</h5>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <div>
            <h6 class="fw-bold mb-1 text-dark">
              Transaction Details
              <span class="badge bg-secondary font-monospace ms-2">{{ datas.records ? datas.records.length : 0 }} Records</span>
            </h6>
            <div class="small text-muted" v-if="datas.client">
              Client: <strong>{{ datas.client.name }}</strong> ({{ datas.client.mobile }}) | Available Balance: <strong>{{ formatNumber(datas.client.points_balance) }} Pts</strong>
            </div>
          </div>
          <div class="text-end small text-muted">
            <div>Period: <strong>{{ dateRangeLabel }}</strong></div>
            <div>Generated: <strong>{{ currentDate }}</strong></div>
          </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive bg-white rounded border shadow-sm">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="small text-muted text-uppercase">
                <th width="4%" class="text-center">#</th>
                <th width="11%">Date</th>
                <th width="20%">Customer Details</th>
                <th width="12%" class="text-center">Type</th>
                <th width="10%" class="text-end">Points In</th>
                <th width="10%" class="text-end">Points Out</th>
                <th width="11%" class="text-end">Equivalent (Tk.)</th>
                <th width="12%">Invoice / Ref</th>
                <th width="10%" class="text-end">Balance After</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(record, index) in (datas.records || [])" :key="record.id">
                <td class="text-center text-muted small">{{ index + 1 }}</td>
                <td class="font-monospace small">
                  {{ formatDate(record.transaction_date || record.created_at) }}
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ record.client ? record.client.name : 'N/A' }}</div>
                  <small class="text-muted font-monospace">{{ record.client ? record.client.mobile : '' }}</small>
                </td>
                <td class="text-center">
                  <span class="badge px-2 py-1" :class="getTypeBadgeClass(record.type)">
                    {{ formatTypeName(record.type) }}
                  </span>
                </td>
                <td class="text-end font-monospace fw-bold" :class="Number(record.points_in) > 0 ? 'text-success' : 'text-muted'">
                  {{ Number(record.points_in) > 0 ? '+' + formatNumber(record.points_in) : '-' }}
                </td>
                <td class="text-end font-monospace fw-bold" :class="Number(record.points_out) > 0 ? 'text-danger' : 'text-muted'">
                  {{ Number(record.points_out) > 0 ? '-' + formatNumber(record.points_out) : '-' }}
                </td>
                <td class="text-end font-monospace fw-bold text-dark">
                  Tk. {{ formatMoney(record.amount_equivalent || 0) }}
                </td>
                <td>
                  <div class="font-monospace small fw-bold text-primary" v-if="record.invoice">
                    {{ record.invoice.invoice_no }}
                  </div>
                  <small class="text-muted d-block" style="font-size: 11px;">{{ record.description || '-' }}</small>
                </td>
                <td class="text-end font-monospace fw-bold text-dark">
                  {{ formatNumber(record.balance) }} Pts
                </td>
              </tr>
              <tr v-if="!datas.records || datas.records.length === 0">
                <td colspan="9" class="text-center py-5 text-muted">
                  <i class="fas fa-receipt fa-3x mb-3 text-secondary opacity-25 d-block"></i>
                  <h6 class="fw-bold text-secondary">No Coupon or Point Transactions Found</h6>
                  <p class="small text-muted mb-0">Try changing the date range or client filter above.</p>
                </td>
              </tr>
            </tbody>
            <tfoot class="table-light fw-bold" v-if="datas.records && datas.records.length > 0">
              <tr>
                <td colspan="4" class="text-end text-uppercase">Period Total:</td>
                <td class="text-end text-success font-monospace">+{{ formatNumber(summaryData.period_earned) }} Pts</td>
                <td class="text-end text-danger font-monospace">-{{ formatNumber(summaryData.period_redeemed) }} Pts</td>
                <td class="text-end font-monospace">Tk. {{ formatMoney(summaryData.period_equivalent_tk) }}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- 📌 Footer Note (Visible in Print) -->
        <div class="mt-4 pt-3 border-top small text-muted d-none d-print-block">
          <div class="d-flex justify-content-between align-items-end">
            <div>
              <p class="mb-0">Generated by: <strong>{{ $root.auth ? $root.auth.name : 'Admin' }}</strong></p>
              <p class="mb-0">This is a system generated report and does not require physical signature.</p>
            </div>
            <div class="text-center" style="min-width: 150px;">
              <div style="border-bottom: 1px solid #000; width: 120px; margin: 0 auto 5px auto;"></div>
              <div>Authorized Signature</div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </index-page>
</template>

<script>
const model = "report";

export default {
  data() {
    return {
      model: model,
      page_title: "Coupon & Loyalty Points Report",
      activePreset: "all",
      clients: [],
      search_data: {
        client_id: null,
        type: "",
        from_date: "",
        to_date: "",
      },
      datas: {
        records: [],
        client: null,
        summary: {},
        settings: {},
      },
      currentDate: new Date().toLocaleDateString("en-GB"),
    };
  },

  computed: {
    summaryData() {
      return this.datas.summary || {
        total_earned_all_time: 0,
        total_redeemed_all_time: 0,
        total_active_points: 0,
        total_liability_tk: 0,
        period_earned: 0,
        period_redeemed: 0,
        period_equivalent_tk: 0,
        total_transactions: 0,
      };
    },

    dateRangeLabel() {
      if (this.search_data.from_date && this.search_data.to_date) {
        return `${this.search_data.from_date} to ${this.search_data.to_date}`;
      } else if (this.search_data.from_date) {
        return `From ${this.search_data.from_date}`;
      } else if (this.search_data.to_date) {
        return `Up to ${this.search_data.to_date}`;
      }
      return "All Time";
    },

    exportFileName() {
      return `Coupon_Points_Report_${new Date().toISOString().slice(0, 10)}.xls`;
    },

    exportData() {
      if (!this.datas.records) return [];
      return this.datas.records.map((r, idx) => ({
        "SL": idx + 1,
        "Date": r.transaction_date || r.created_at,
        "Client ID": r.client ? r.client.clientid : "",
        "Client Name": r.client ? r.client.name : "",
        "Mobile": r.client ? r.client.mobile : "",
        "Type": this.formatTypeName(r.type),
        "Points In": Number(r.points_in) || 0,
        "Points Out": Number(r.points_out) || 0,
        "Equivalent Amount (Tk)": Number(r.amount_equivalent) || 0,
        "Invoice No": r.invoice ? r.invoice.invoice_no : "",
        "Description": r.description || "",
        "Balance After": Number(r.balance) || 0,
      }));
    },
  },

  methods: {
    formatMoney(val) {
      return Number(val || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatNumber(val) {
      return Number(val || 0).toLocaleString("en-US");
    },

    formatDate(val) {
      if (!val) return "";
      return new Date(val).toLocaleDateString("en-GB");
    },

    formatTypeName(type) {
      switch (type) {
        case "Earn": return "Points Earned";
        case "Redeem": return "Points Redeemed";
        case "Convert_To_Cash": return "Cash Converted";
        case "Adjustment": return "Point Adjustment";
        default: return type;
      }
    },

    getTypeBadgeClass(type) {
      switch (type) {
        case "Earn": return "bg-success text-white";
        case "Redeem": return "bg-warning text-dark";
        case "Convert_To_Cash": return "bg-primary text-white";
        case "Adjustment": return "bg-info text-white";
        default: return "bg-secondary text-white";
      }
    },

    applyDatePreset(preset) {
      this.activePreset = preset;
      const today = new Date();
      const format = (d) => {
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();
        return `${dd}-${mm}-${yyyy}`;
      };

      if (preset === "all") {
        this.search_data.from_date = "";
        this.search_data.to_date = "";
      } else if (preset === "today") {
        this.search_data.from_date = format(today);
        this.search_data.to_date = format(today);
      } else if (preset === "yesterday") {
        const y = new Date(today);
        y.setDate(y.getDate() - 1);
        this.search_data.from_date = format(y);
        this.search_data.to_date = format(y);
      } else if (preset === "last7") {
        const d7 = new Date(today);
        d7.setDate(d7.getDate() - 6);
        this.search_data.from_date = format(d7);
        this.search_data.to_date = format(today);
      } else if (preset === "thisMonth") {
        const startMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        this.search_data.from_date = format(startMonth);
        this.search_data.to_date = format(today);
      } else if (preset === "lastMonth") {
        const startLast = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const endLast = new Date(today.getFullYear(), today.getMonth(), 0);
        this.search_data.from_date = format(startLast);
        this.search_data.to_date = format(endLast);
      } else if (preset === "thisYear") {
        const startYear = new Date(today.getFullYear(), 0, 1);
        this.search_data.from_date = format(startYear);
        this.search_data.to_date = format(today);
      }

      this.getReportData();
    },

    getClients() {
      axios.get("client?page=1&per_page=1000")
        .then((res) => {
          if (res.data && res.data.data) {
            this.clients = res.data.data;
          } else if (Array.isArray(res.data)) {
            this.clients = res.data;
          }
        })
        .catch((err) => console.error(err));
    },

    getReportData() {
      this.$root.spinner = true;
      axios.get("report/coupon", { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
          this.$toast("Failed to load coupon report data", "error");
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },
  },

  watch: {
    "search_data.client_id"() {
      this.getReportData();
    },
    "search_data.type"() {
      this.getReportData();
    },
    "search_data.from_date"() {
      this.getReportData();
    },
    "search_data.to_date"() {
      this.getReportData();
    },
  },

  mounted() {
    this.getClients();
    this.getReportData();
  },
};
</script>

<style scoped>
.btn-xs {
  padding: 2px 8px;
  font-size: 11px;
  border-radius: 4px;
}

.bg-gradient-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
}

.bg-gradient-info {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
}

.metric-icon {
  font-size: 2rem;
  opacity: 0.35;
}

.card {
  height: auto !important;
}
</style>
