<template>
  <div>
    <!-- Top Summary & KPI Metrics Bar -->
    <div class="row g-3 mb-3" v-if="table && table.datas">
      <!-- 1. Total Payments Count -->
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card border-0 shadow-sm kpi_card h-100" @click="setQuickFilter('all')" role="button">
          <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
              <div class="kpi_label text-muted small fw-bold text-uppercase">Total Transactions</div>
              <div class="kpi_value text-dark fw-bold fs-4 font-monospace">
                {{ table.meta ? (table.meta.total || 0) : table.datas.length }}
              </div>
              <small class="text-secondary font-monospace" style="font-size: 11px;">
                <i class="fas fa-list me-1 text-primary"></i> All recorded vouchers
              </small>
            </div>
            <div class="kpi_icon_wrap bg-primary-soft text-primary">
              <i class="fas fa-file-invoice-dollar fa-lg"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Total Receive (Collection) -->
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card border-0 shadow-sm kpi_card h-100" @click="setQuickFilter('Receive')" role="button">
          <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
              <div class="kpi_label text-success small fw-bold text-uppercase">Total Collections</div>
              <div class="kpi_value text-success fw-bold fs-4 font-monospace">
                ৳ {{ this.$filter.money(summaryStats.total_receive) }}
              </div>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                <i class="fas fa-arrow-circle-down text-success me-1"></i> Receive payments
              </small>
            </div>
            <div class="kpi_icon_wrap bg-success-soft text-success">
              <i class="fas fa-arrow-down fa-lg"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Total Pay (Disbursement) -->
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card border-0 shadow-sm kpi_card h-100" @click="setQuickFilter('Pay')" role="button">
          <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
              <div class="kpi_label text-danger small fw-bold text-uppercase">Total Disbursements</div>
              <div class="kpi_value text-danger fw-bold fs-4 font-monospace">
                ৳ {{ this.$filter.money(summaryStats.total_pay) }}
              </div>
              <small class="text-muted font-monospace" style="font-size: 11px;">
                <i class="fas fa-arrow-circle-up text-danger me-1"></i> Supplier / Salary / Exps
              </small>
            </div>
            <div class="kpi_icon_wrap bg-danger-soft text-danger">
              <i class="fas fa-arrow-up fa-lg"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Net Volume -->
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card border-0 shadow-sm kpi_card kpi_card_net h-100">
          <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
              <div class="kpi_label text-muted small fw-bold text-uppercase">Net Cash Balance</div>
              <div class="kpi_value fw-bold fs-4 font-monospace" :class="summaryStats.net_amount >= 0 ? 'text-dark' : 'text-danger'">
                ৳ {{ this.$filter.money(summaryStats.net_amount) }}
              </div>
              <small class="text-secondary font-monospace" style="font-size: 11px;">
                <i class="fas fa-wallet text-primary me-1"></i> (Collections - Payments)
              </small>
            </div>
            <div class="kpi_icon_wrap bg-dark text-white">
              <i class="fas fa-coins fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Index Page with Advanced Multi-Dimensional Search -->
    <index-page :show_status="false">
      <!-- Advanced Search / Filters Slot -->
      <template v-slot:search-field>
        <!-- Quick Preset Filter Buttons Bar -->
        <div class="col-12 mb-2">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 bg-light rounded-3 border">
            <div class="d-flex flex-wrap align-items-center gap-1">
              <span class="small fw-bold text-secondary me-2"><i class="fas fa-bolt text-warning me-1"></i> Quick Presets:</span>
              <button type="button" class="btn btn-xs quick_pill" :class="!search_data.payment_type ? 'quick_pill_active' : ''" @click="setQuickFilter('all')">All</button>
              <button type="button" class="btn btn-xs quick_pill" :class="search_data.payment_type === 'Receive' ? 'quick_pill_active' : ''" @click="setQuickFilter('Receive')">Receive</button>
              <button type="button" class="btn btn-xs quick_pill" :class="search_data.payment_type === 'Pay' ? 'quick_pill_active' : ''" @click="setQuickFilter('Pay')">Pay</button>
              <span class="text-muted mx-1">|</span>
              <button type="button" class="btn btn-xs quick_pill" :class="date_preset === 'today' ? 'quick_pill_active' : ''" @click="setDatePreset('today')">Today</button>
              <button type="button" class="btn btn-xs quick_pill" :class="date_preset === 'yesterday' ? 'quick_pill_active' : ''" @click="setDatePreset('yesterday')">Yesterday</button>
              <button type="button" class="btn btn-xs quick_pill" :class="date_preset === 'last7' ? 'quick_pill_active' : ''" @click="setDatePreset('last7')">Last 7 Days</button>
              <button type="button" class="btn btn-xs quick_pill" :class="date_preset === 'month' ? 'quick_pill_active' : ''" @click="setDatePreset('month')">This Month</button>
            </div>
            <button type="button" class="btn btn-xs btn-outline-danger" @click="resetAllFilters" v-if="hasActiveFilters">
              <i class="fas fa-times me-1"></i> Reset Filters
            </button>
          </div>
        </div>

        <!-- Row 1: Transaction Type & Entity Selectors -->
        <v-select-container title="Transaction Type" field="search_data.payment_type" col="3">
          <v-select v-model="search_data.payment_type" :options="['Receive', 'Pay']"
            placeholder="-- All Types --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <v-select-container title="Select Client" field="search_data.client_id" col="3">
          <v-select v-model="search_data.client_id" label="name" :reduce="(obj) => obj.id" :options="clients"
            placeholder="-- Select Client --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <v-select-container title="Select Supplier" field="search_data.supplier_id" col="3">
          <v-select v-model="search_data.supplier_id" label="org_name" :reduce="(obj) => obj.id"
            :options="$root.global.suppliers" placeholder="-- Select Supplier --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <v-select-container title="Select Employee" field="search_data.employee_id" col="3">
          <v-select v-model="search_data.employee_id" label="full_name" :reduce="(obj) => obj.id"
            :options="$root.global.employees" placeholder="-- Select Employee --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <!-- Row 2: Agent, Payment Method, Mbanking, Fund Account -->
        <v-select-container title="Select Agent" field="search_data.agent_id" col="3">
          <v-select v-model="search_data.agent_id" label="full_name" :reduce="(obj) => obj.id"
            :options="agents" placeholder="-- Select Agent --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <v-select-container title="Payment Method" field="search_data.payment_method" col="3">
          <v-select v-model="search_data.payment_method" label="name" :reduce="(obj) => obj.value"
            :options="$root.global.paymentmethods" placeholder="-- All Methods --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <v-select-container title="Mbanking Type" field="search_data.mbanking_type" col="3">
          <v-select v-model="search_data.mbanking_type" label="name" :reduce="(obj) => obj.value"
            :options="$root.global.mbankings" placeholder="-- All Mbankings --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <v-select-container title="Fund Account" field="search_data.fund_account_id" col="3">
          <v-select v-model="search_data.fund_account_id" label="name" :reduce="(obj) => obj.id"
            :options="fundaccounts" placeholder="-- All Fund Accounts --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <!-- Row 3: Date Range & Amount Range -->
        <date-picker id='searchfrominvoicedate' v-model='search_data.from_payment_date' field='search_data.from_payment_date'
          title='From Payment Date' placeholder='From Date' col='3' :req='false'></date-picker>

        <date-picker id='searchtoPaymentdate' v-model='search_data.to_payment_date' field='search_data.to_payment_date'
          title='To Payment Date' placeholder='To Date' col='3' :req='false'
          :disablePastDates="search_data.from_payment_date"></date-picker>

        <Input v-model="search_data.min_amount" field="search_data.min_amount" title="Min Amount (৳)"
          placeholder="e.g. 100" col="2" :req="false" type="number" />

        <Input v-model="search_data.max_amount" field="search_data.max_amount" title="Max Amount (৳)"
          placeholder="e.g. 50000" col="2" :req="false" type="number" />

        <v-select-container title="Status" field="search_data.status" col="2">
          <v-select v-model="search_data.status" :options="['active', 'deactive']"
            placeholder="-- All Status --" :closeOnSelect="true"></v-select>
        </v-select-container>

        <!-- Active Filter Badges Bar -->
        <div class="col-12 mt-2" v-if="hasActiveFilters">
          <div class="d-flex flex-wrap align-items-center gap-1">
            <span class="small text-muted me-1">Applied:</span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.payment_type">
              Type: {{ search_data.payment_type }} <i class="fas fa-times ms-1" @click="search_data.payment_type = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.client_id">
              Client: {{ getEntityName('client', search_data.client_id) }} <i class="fas fa-times ms-1" @click="search_data.client_id = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.supplier_id">
              Supplier: {{ getEntityName('supplier', search_data.supplier_id) }} <i class="fas fa-times ms-1" @click="search_data.supplier_id = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.employee_id">
              Employee: {{ getEntityName('employee', search_data.employee_id) }} <i class="fas fa-times ms-1" @click="search_data.employee_id = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.agent_id">
              Agent: {{ getEntityName('agent', search_data.agent_id) }} <i class="fas fa-times ms-1" @click="search_data.agent_id = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.payment_method">
              Method: {{ search_data.payment_method }} <i class="fas fa-times ms-1" @click="search_data.payment_method = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.fund_account_id">
              Fund: {{ getEntityName('fund', search_data.fund_account_id) }} <i class="fas fa-times ms-1" @click="search_data.fund_account_id = ''"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.from_payment_date || search_data.to_payment_date">
              Date: {{ search_data.from_payment_date || 'Start' }} → {{ search_data.to_payment_date || 'End' }}
              <i class="fas fa-times ms-1" @click="search_data.from_payment_date = ''; search_data.to_payment_date = ''; date_preset = '';"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.min_amount || search_data.max_amount">
              ৳ {{ search_data.min_amount || 0 }} - {{ search_data.max_amount || '∞' }}
              <i class="fas fa-times ms-1" @click="search_data.min_amount = ''; search_data.max_amount = '';"></i>
            </span>
            <span class="badge bg-primary-soft text-primary filter_chip" v-if="search_data.status">
              Status: {{ search_data.status }} <i class="fas fa-times ms-1" @click="search_data.status = ''"></i>
            </span>
          </div>
        </div>
      </template>

      <!-- Custom Table Rows -->
      <template v-slot:payslipno="{ item }">
        <td class="text-nowrap">
          <div class="d-flex align-items-center gap-1">
            <span class="badge bg-dark text-white font-monospace payslip_badge">
              #{{ item.payslipno || item.id }}
            </span>
          </div>
          <div class="small text-muted font-monospace mt-1" style="font-size: 11.5px;">
            <i class="far fa-calendar-alt me-1 text-primary"></i> {{ item.payment_date }}
          </div>
        </td>
      </template>

      <template v-slot:payment_type="{ item }">
        <td class="text-center text-nowrap">
          <span class="badge" :class="item.payment_type === 'Receive' ? 'badge_receive' : 'badge_pay'">
            <i :class="item.payment_type === 'Receive' ? 'fas fa-arrow-down me-1' : 'fas fa-arrow-up me-1'"></i>
            {{ item.payment_type }}
          </span>
        </td>
      </template>

      <template v-slot:client_id="{ item }">
        <td>
          <div v-if="item.client">
            <span class="badge bg-info-soft text-info me-1">Client</span>
            <strong class="text-dark">{{ item.client.name }}</strong>
            <div class="small text-muted font-monospace" v-if="item.client.mobile">
              <i class="fas fa-phone-alt me-1 text-success"></i>{{ item.client.mobile }}
            </div>
            <div class="small text-secondary font-monospace" v-if="item.client.clientid">
              <i class="fas fa-id-badge me-1"></i>{{ item.client.clientid }}
            </div>
          </div>
          <div v-else-if="item.supplier">
            <span class="badge bg-warning-soft text-dark me-1">Supplier</span>
            <strong class="text-dark">{{ item.supplier.org_name }}</strong>
            <div class="small text-muted font-monospace" v-if="item.supplier.mobile">
              <i class="fas fa-phone-alt me-1 text-success"></i>{{ item.supplier.mobile }}
            </div>
            <div class="small text-secondary font-monospace" v-if="item.supplier.supid">
              <i class="fas fa-id-badge me-1"></i>{{ item.supplier.supid }}
            </div>
          </div>
          <div v-else-if="item.employee">
            <span class="badge bg-primary-soft text-primary me-1">Employee</span>
            <strong class="text-dark">{{ item.employee.full_name }}</strong>
            <div class="small text-muted font-monospace" v-if="item.employee.mobile">
              <i class="fas fa-phone-alt me-1 text-success"></i>{{ item.employee.mobile }}
            </div>
          </div>
          <div v-else-if="item.agent">
            <span class="badge bg-secondary-soft text-secondary me-1">Agent</span>
            <strong class="text-dark">{{ item.agent.full_name }}</strong>
            <div class="small text-muted font-monospace" v-if="item.agent.mobile">
              <i class="fas fa-phone-alt me-1 text-success"></i>{{ item.agent.mobile }}
            </div>
          </div>
          <div v-else-if="item.office_expense">
            <span class="badge bg-danger-soft text-danger me-1">Office Expense</span>
            <strong class="text-dark">Office Operations</strong>
          </div>
          <span v-else-if="item.client_id" class="text-dark small fw-semibold">
            Client #{{ item.client_id }}
          </span>
          <span v-else-if="item.supplier_id" class="text-dark small fw-semibold">
            Supplier #{{ item.supplier_id }}
          </span>
          <span v-else-if="item.employee_id" class="text-dark small fw-semibold">
            Employee #{{ item.employee_id }}
          </span>
          <span v-else-if="item.agent_id" class="text-dark small fw-semibold">
            Agent #{{ item.agent_id }}
          </span>
          <span v-else class="text-muted small">-- N/A --</span>
        </td>
      </template>

      <template v-slot:payment_method="{ item }">
        <td class="text-nowrap">
          <span class="badge bg-light text-dark border me-1 font-monospace">
            {{ item.payment_method || 'Cash' }}
          </span>
          <span class="badge bg-secondary-soft text-secondary font-monospace" v-if="item.mbanking_type">
            {{ item.mbanking_type }}
          </span>
          <div class="small text-muted font-monospace mt-1" v-if="item.trxid || item.chequeno || item.bank">
            <span v-if="item.trxid"><i class="fas fa-hashtag text-primary"></i> {{ item.trxid }}</span>
            <span v-if="item.chequeno"><i class="fas fa-money-check text-info"></i> Chq: {{ item.chequeno }}</span>
            <span v-if="item.bank"><i class="fas fa-university text-secondary"></i> {{ item.bank.bank_name }}</span>
          </div>
        </td>
      </template>

      <template v-slot:fund_account_id="{ item }">
        <td>
          <span class="small font-monospace fw-semibold text-secondary" v-if="item.fund_account">
            <i class="fas fa-wallet me-1 text-primary"></i>
            {{ item.fund_account.account_code ? item.fund_account.account_code + ' - ' : '' }}{{ item.fund_account.account_name || item.fund_account.name }}
          </span>
          <span v-else-if="item.fund_account_id" class="text-muted small">
            Account #{{ item.fund_account_id }}
          </span>
          <span v-else class="text-muted small">--</span>
        </td>
      </template>

      <template v-slot:amount="{ item }">
        <td class="text-end text-nowrap font-monospace fw-bold fs-6" :class="item.payment_type === 'Receive' ? 'text-success' : 'text-primary'">
          {{ $filter.formatBDT(item.amount) }}
        </td>
      </template>

      <template v-slot:status="{ item }">
        <td class="text-center text-nowrap">
          <span class="badge" :class="item.status === 'active' ? 'bg-success-soft text-success' : 'bg-secondary-soft text-secondary'">
            <i class="fas fa-circle me-1" style="font-size: 8px;"></i> {{ item.status }}
          </span>
        </td>
      </template>
    </index-page>
  </div>
</template>

<script>
const model = "payment";

const tableColumns = [
  { field: "payslipno", title: "Payslip & Date", width: "140px" },
  { field: "payment_type", title: "Type", align: "center", width: "100px" },
  { field: "client_id", title: "Party / Payee Info", subfield: "client.name", width: "240px" },
  { field: "payment_method", title: "Payment Method / Trx", width: "180px" },
  { field: "fund_account_id", title: "Fund Account", subfield: "fund_account.account_name", width: "160px" },
  { field: "amount", title: "Amount (৳)", align: "right", width: "140px" },
  { field: "status", title: "Status", align: "center", width: "90px" },
];

const json_fields = {
  "Payslip No": "payslipno",
  "Payment Type": "payment_type",
  "Client": "client.name",
  "Supplier": "supplier.org_name",
  "Employee": "employee.full_name",
  "Agent": "agent.full_name",
  "Payment Date": "payment_date",
  "Payment Method": "payment_method",
  "Mbanking Type": "mbanking_type",
  "Trx ID": "trxid",
  "Cheque No": "chequeno",
  "Fund Account": "fund_account.name",
  "Amount": "amount",
  "Status": "status"
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      date_preset: "",
      fields_name: {
        default: "All Fields (Keyword)",
        payslipno: "Payslip No",
        client: "Client Name / ID",
        supplier: "Supplier Name / ID",
        employee: "Employee",
        agent: "Agent",
        trxid: "Trx ID",
        chequeno: "Cheque No",
        accountno: "Account No",
        branch: "Branch"
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 20,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        payment_type: this.$route.query.payment_type ?? "",
        client_id: this.$route.query.client_id ?? "",
        supplier_id: this.$route.query.supplier_id ?? "",
        employee_id: this.$route.query.employee_id ?? "",
        agent_id: this.$route.query.agent_id ?? "",
        payment_method: this.$route.query.payment_method ?? "",
        mbanking_type: this.$route.query.mbanking_type ?? "",
        fund_account_id: this.$route.query.fund_account_id ?? "",
        from_payment_date: this.$route.query.from_payment_date ?? "",
        to_payment_date: this.$route.query.to_payment_date ?? "",
        min_amount: this.$route.query.min_amount ?? "",
        max_amount: this.$route.query.max_amount ?? "",
        status: this.$route.query.status ?? "",
        office_expense: this.$route.query.office_expense ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      clients: [],
      agents: [],
      fundaccounts: [],
    };
  },

  provide() {
    return {
      validate: this.validation,
      model: this.model,
      fields_name: this.fields_name,
      search_data: this.search_data,
      table: this.table,
      json_fields: this.json_fields,
      search: this.search,
      resetSearchData: this.resetSearchData,
    };
  },

  computed: {
    hasActiveFilters() {
      return !!(
        this.search_data.payment_type ||
        this.search_data.client_id ||
        this.search_data.supplier_id ||
        this.search_data.employee_id ||
        this.search_data.agent_id ||
        this.search_data.payment_method ||
        this.search_data.mbanking_type ||
        this.search_data.fund_account_id ||
        this.search_data.from_payment_date ||
        this.search_data.to_payment_date ||
        this.search_data.min_amount ||
        this.search_data.max_amount ||
        this.search_data.status
      );
    },

    summaryStats() {
      if (!this.table || !this.table.datas) {
        return { total_receive: 0, total_pay: 0, net_amount: 0 };
      }
      let receive = 0;
      let pay = 0;
      this.table.datas.forEach(item => {
        const amt = parseFloat(item.amount) || 0;
        if (item.payment_type === 'Receive') {
          receive += amt;
        } else {
          pay += amt;
        }
      });
      return {
        total_receive: receive,
        total_pay: pay,
        net_amount: receive - pay
      };
    }
  },

  methods: {
    search() {
      this.get_paginate(this.model, this.search_data);
    },

    setQuickFilter(type) {
      if (type === 'all') {
        this.search_data.payment_type = '';
      } else {
        this.search_data.payment_type = type;
      }
      this.search();
    },

    setDatePreset(preset) {
      this.date_preset = preset;
      const today = new Date();

      if (preset === 'today') {
        const formatted = this.formatDate(today);
        this.search_data.from_payment_date = formatted;
        this.search_data.to_payment_date = formatted;
      } else if (preset === 'yesterday') {
        const yest = new Date(today);
        yest.setDate(yest.getDate() - 1);
        const formatted = this.formatDate(yest);
        this.search_data.from_payment_date = formatted;
        this.search_data.to_payment_date = formatted;
      } else if (preset === 'last7') {
        const start = new Date(today);
        start.setDate(start.getDate() - 7);
        this.search_data.from_payment_date = this.formatDate(start);
        this.search_data.to_payment_date = this.formatDate(today);
      } else if (preset === 'month') {
        const start = new Date(today.getFullYear(), today.getMonth(), 1);
        this.search_data.from_payment_date = this.formatDate(start);
        this.search_data.to_payment_date = this.formatDate(today);
      }
      this.search();
    },

    formatDate(date) {
      const day = date.getDate();
      const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      const month = months[date.getMonth()];
      const year = date.getFullYear();
      return `${day} ${month}, ${year}`;
    },

    resetAllFilters() {
      this.search_data.payment_type = "";
      this.search_data.client_id = "";
      this.search_data.supplier_id = "";
      this.search_data.employee_id = "";
      this.search_data.agent_id = "";
      this.search_data.payment_method = "";
      this.search_data.mbanking_type = "";
      this.search_data.fund_account_id = "";
      this.search_data.from_payment_date = "";
      this.search_data.to_payment_date = "";
      this.search_data.min_amount = "";
      this.search_data.max_amount = "";
      this.search_data.status = "";
      this.search_data.value = "";
      this.date_preset = "";
      this.search();
    },

    resetSearchData() {
      this.search_data.pagination = 20;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.resetAllFilters();
    },

    getClients() {
      axios.get(`clients/`)
        .then((response) => {
          this.clients = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },

    getAgents() {
      if (this.$root.global && this.$root.global.agents && this.$root.global.agents.length > 0) {
        this.agents = this.$root.global.agents;
      } else {
        axios.get(`getagents`)
          .then((response) => {
            this.agents = response.data;
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },

    getFundAccounts() {
      axios.get(`getfundaccounts/`)
        .then((response) => {
          this.fundaccounts = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },

    getEntityName(type, id) {
      if (!id) return "";
      if (type === 'client') {
        const item = this.clients.find(c => c.id == id);
        return item ? item.name : `#${id}`;
      } else if (type === 'supplier') {
        const item = (this.$root.global?.suppliers || []).find(s => s.id == id);
        return item ? item.org_name : `#${id}`;
      } else if (type === 'employee') {
        const item = (this.$root.global?.employees || []).find(e => e.id == id);
        return item ? item.full_name : `#${id}`;
      } else if (type === 'agent') {
        const item = this.agents.find(a => a.id == id);
        return item ? item.full_name : `#${id}`;
      } else if (type === 'fund') {
        const item = this.fundaccounts.find(f => f.id == id);
        return item ? item.name : `#${id}`;
      }
      return id;
    }
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `Payment & Vouchers`;
    this.search();
    this.getClients();
    this.getAgents();
    this.getFundAccounts();
  },

  validators: {},
};
</script>

<style scoped>
.kpi_card {
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #e2e8f0 !important;
  transition: all 0.2s ease-in-out;
}
.kpi_card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06) !important;
}
.kpi_card_net {
  border-left: 3px solid #0e2942 !important;
}
.kpi_icon_wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.quick_pill {
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #475569;
  font-weight: 600;
  padding: 2px 10px;
  font-size: 11.5px;
  transition: all 0.15s ease;
}
.quick_pill:hover {
  background: #f1f5f9;
  color: #0e2942;
  border-color: #0e2942;
}
.quick_pill_active {
  background: #0e2942 !important;
  color: #ffffff !important;
  border-color: #0e2942 !important;
}

.filter_chip {
  font-size: 11.5px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
  cursor: default;
}
.filter_chip i {
  cursor: pointer;
}
.filter_chip i:hover {
  color: #dc2626;
}

.payslip_badge {
  font-size: 12px;
  letter-spacing: 0.5px;
  padding: 3px 8px;
  border-radius: 6px;
}

.badge_receive {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
  font-weight: 700;
  font-size: 11.5px;
  padding: 4px 10px;
  border-radius: 20px;
}
.badge_pay {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecdd3;
  font-weight: 700;
  font-size: 11.5px;
  padding: 4px 10px;
  border-radius: 20px;
}

.bg-primary-soft {
  background: rgba(14, 41, 66, 0.08);
}
.bg-success-soft {
  background: rgba(16, 185, 129, 0.1);
}
.bg-danger-soft {
  background: rgba(239, 68, 68, 0.1);
}
.bg-warning-soft {
  background: rgba(245, 158, 11, 0.12);
}
.bg-info-soft {
  background: rgba(6, 182, 212, 0.1);
}
.bg-secondary-soft {
  background: rgba(100, 116, 139, 0.1);
}
</style>