<template>
  <index-page :defaultTable="false" :show_status="false">
    <!-- 🔍 Search & Filter Fields -->
    <template v-slot:search-field>
      <!-- Quick Date Presets -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted small fw-bold me-1 font-monospace">
            <i class="fas fa-calendar-alt me-1 text-primary"></i>{{ $t("Quick Date:") }}
          </span>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'today' }"
            @click="applyDatePreset('today')">{{ $t("Today") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'yesterday' }"
            @click="applyDatePreset('yesterday')">{{ $t("Yesterday") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'last7' }"
            @click="applyDatePreset('last7')">{{ $t("Last 7 Days") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'thisMonth' }"
            @click="applyDatePreset('thisMonth')">{{ $t("This Month") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'lastMonth' }"
            @click="applyDatePreset('lastMonth')">{{ $t("Last Month") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'thisYear' }"
            @click="applyDatePreset('thisYear')">{{ $t("This Year") }}</button>
          <button type="button" class="btn btn-xs btn-outline-dark font-monospace"
            :class="{ 'active': activePreset === 'all' }"
            @click="applyDatePreset('all')">{{ $t("All Time") }}</button>
        </div>
      </div>

      <!-- Module / Source Filter -->
      <v-select-container title="Module / Source" field="search_data.module_type" col="3 mb-3">
        <v-select v-model="search_data.module_type" label="label" :reduce="obj => obj.value"
          :options="moduleOptions" placeholder="-- All Sources --" :closeOnSelect="true" />
      </v-select-container>

      <!-- Branch Filter -->
      <v-select-container title="Branch" field="search_data.branch_id" col="3 mb-3">
        <v-select v-model="search_data.branch_id" label="branch_name" :reduce="obj => obj.id"
          :options="$root.global.branches" placeholder="-- All Branches --" :closeOnSelect="true" />
      </v-select-container>

      <!-- Employee Filter -->
      <v-select-container title="Employee" field="search_data.employee_id" col="3 mb-3">
        <v-select v-model="search_data.employee_id" label="full_name" :reduce="obj => obj.id"
          :options="$root.global.employees" placeholder="-- Select Employee --" :closeOnSelect="true" />
      </v-select-container>

      <!-- Supplier Filter (for GRN) -->
      <v-select-container title="Supplier (GRN)" field="search_data.supplier_id" col="3 mb-3">
        <v-select v-model="search_data.supplier_id" label="org_name" :reduce="obj => obj.id"
          :options="$root.global.suppliers" placeholder="-- Select Supplier --" :closeOnSelect="true" />
      </v-select-container>

      <!-- Payment Method Filter -->
      <v-select-container title="Payment Method" field="search_data.payment_method" col="3 mb-3">
        <v-select v-model="search_data.payment_method" label="name" :reduce="obj => obj.value"
          :options="$root.global.paymentmethods" placeholder="-- Select Method --" :closeOnSelect="true" />
      </v-select-container>

      <!-- Datepickers -->
      <date-picker id='searchfromexpensedate' v-model='search_data.from_date' field='search_data.from_date'
        title='From Date' placeholder='From Date' col='3 mb-3' :req='false'></date-picker>
      <date-picker id='searchtoexpensedate' v-model='search_data.to_date' field='search_data.to_date'
        title='To Date' placeholder='To Date' col='3 mb-3' :req='false'></date-picker>

      <!-- Keyword Search -->
      <div class="col-md-3 mb-3">
        <label class="form-label small fw-bold font-monospace">{{ $t('Search Keyword') }}</label>
        <input type="text" class="form-control form-control-sm font-monospace" v-model="search_data.keyword"
          :placeholder="$t('Ref # / Payee / Head / Source')" @keyup.enter="search" />
      </div>
    </template>

    <!-- 📊 Report Content & Table -->
    <template v-slot:table-list>
      <!-- Action Bar with Module Pills & Single Print / Excel -->
      <div class="col-12 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Source filter pills -->
          <div class="d-flex flex-wrap gap-1.5 align-items-center">
            <button type="button" class="btn btn-xs rounded-pill font-monospace"
              :class="search_data.module_type === 'all' || !search_data.module_type ? 'btn-dark text-white' : 'btn-outline-dark'"
              @click="filterByModule('all')">
              {{ $t('All Sources') }}
              <span class="badge bg-white text-dark ms-1">{{ datas.counts?.all ?? (datas.details?.length || 0) }}</span>
            </button>
            <button type="button" class="btn btn-xs rounded-pill font-monospace"
              :class="search_data.module_type === 'expense' ? 'btn-dark text-white' : 'btn-outline-dark'"
              @click="filterByModule('expense')">
              {{ $t('Expense') }}
              <span class="badge bg-white text-dark ms-1">{{ datas.counts?.expense ?? 0 }}</span>
            </button>
            <button type="button" class="btn btn-xs rounded-pill font-monospace"
              :class="search_data.module_type === 'grn' ? 'btn-dark text-white' : 'btn-outline-dark'"
              @click="filterByModule('grn')">
              {{ $t('GRN / Purchases') }}
              <span class="badge bg-white text-dark ms-1">{{ datas.counts?.grn ?? 0 }}</span>
            </button>
            <button type="button" class="btn btn-xs rounded-pill font-monospace"
              :class="search_data.module_type === 'loan' ? 'btn-dark text-white' : 'btn-outline-dark'"
              @click="filterByModule('loan')">
              {{ $t('Loan & Advance') }}
              <span class="badge bg-white text-dark ms-1">{{ datas.counts?.loan ?? 0 }}</span>
            </button>
            <button type="button" class="btn btn-xs rounded-pill font-monospace"
              :class="search_data.module_type === 'salary' ? 'btn-dark text-white' : 'btn-outline-dark'"
              @click="filterByModule('salary')">
              {{ $t('Salary') }}
              <span class="badge bg-white text-dark ms-1">{{ datas.counts?.salary ?? 0 }}</span>
            </button>
            <button type="button" class="btn btn-xs rounded-pill font-monospace"
              :class="search_data.module_type === 'commission' ? 'btn-dark text-white' : 'btn-outline-dark'"
              @click="filterByModule('commission')">
              {{ $t('Commission') }}
              <span class="badge bg-white text-dark ms-1">{{ datas.counts?.commission ?? 0 }}</span>
            </button>
          </div>

          <!-- Export & Single Print Action -->
          <div class="d-flex align-items-center gap-2">
            <download-excel
              v-if="exportData.length > 0"
              class="btn btn-sm btn-outline-success px-3 py-1.5 rounded-pill font-monospace fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5"
              :data="exportData"
              :name="exportFileName">
              <i class="fas fa-file-excel"></i>
              <span>{{ $t("Export Excel") }}</span>
            </download-excel>
            <button
              class="btn btn-sm btn-outline-dark px-3 py-1.5 rounded-pill font-monospace fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5"
              @click="print('printArea', model)">
              <i class="fas fa-print text-primary"></i>
              <span>{{ $t("Print Statement") }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Expense Statement Content -->
      <div class="col-12">
        <!-- 🌟 Top Hero Banner -->
        <div class="card border-0 shadow-sm mb-4 expense-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-money-check-alt fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ $t('Expense & Outflow Statement') }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ getActiveModuleName() }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span>
                      <i class="far fa-calendar-alt me-1"></i> Period: {{ search_data.from_date && search_data.to_date ? search_data.from_date + ' to ' + search_data.to_date : 'All Time' }}
                    </span>
                    <span>
                      <i class="fas fa-clock me-1"></i> As of: {{ reportDate }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Balance Pill -->
              <div class="text-md-end text-white font-monospace">
                <div class="small text-white-50 text-uppercase">{{ $t('Total Gross Outflow') }}</div>
                <div class="fs-4 fw-bold text-white">
                  {{ $filter.formatBDT(datas.total_expense || 0) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  Paid: {{ paidPercentage }}% | Due: {{ $filter.formatBDT(datas.total_due || 0) }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 4 KPI Stat Cards -->
        <div class="row g-3 mb-4 d-print-none">
          <!-- 1. Total Outflow -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Outflow') }}</span>
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-money-bill-wave"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_expense || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Gross Outflow Value') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Paid Amount -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Paid') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_paid || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">
                  {{ $t('Settled Payments') }} ({{ paidPercentage }}%)
                </small>
              </div>
            </div>
          </div>

          <!-- 3. Total Due / Balance -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Due / Balance') }}</span>
                  <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_due || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Outstanding Payables') }}</small>
              </div>
            </div>
          </div>

          <!-- 4. Total Records -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Transactions') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-receipt"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                  {{ datas.details?.length || 0 }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">
                  Source: {{ getActiveModuleName() }}
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
              <h3 class="fw-bold mb-0 text-dark">{{ $root.site?.title || 'QPOS' }}</h3>
              <p class="mb-1 text-muted small">{{ $root.site?.address }}</p>
              <p class="mb-0 text-muted small">
                Email: {{ $root.site?.contact_email }} | Phone: {{ $root.site?.mobile1 }}
              </p>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div>
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Expense & Outflow Statement') }}</h5>
                <div class="small text-dark font-monospace">
                  Source: <strong>{{ getActiveModuleName() }}</strong> | Period: <strong>{{ search_data.from_date && search_data.to_date ? search_data.from_date + ' to ' + search_data.to_date : 'All Time' }}</strong>
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
              <table class="table table-hover align-middle mb-0 expensestatement-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 50px;">{{ $t('#') }}</th>
                    <th class="py-3" style="width: 100px;">{{ $t('Source') }}</th>
                    <th class="py-3" style="width: 110px;">{{ $t('Date') }}</th>
                    <th class="py-3" style="width: 130px;">{{ $t('Ref / Voucher') }}</th>
                    <th class="py-3" style="width: 120px;">{{ $t('Branch') }}</th>
                    <th class="py-3">{{ $t('Account / Head') }}</th>
                    <th class="py-3">{{ $t('Payee / Beneficiary') }}</th>
                    <th class="py-3 text-end" style="width: 130px;">{{ $t('Amount') }}</th>
                    <th class="py-3 text-end" style="width: 130px;">{{ $t('Paid Amount') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 130px;">{{ $t('Due Amount') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(pdetail, index) in datas.details" :key="pdetail.id || index">
                    <td class="ps-4 py-2.5 font-monospace small text-muted">{{ index + 1 }}</td>
                    <td class="py-2.5 font-monospace small">
                      <span :class="getSourceBadgeClass(pdetail.source_type)">
                        {{ pdetail.source_type || 'Expense' }}
                      </span>
                    </td>
                    <td class="py-2.5 font-monospace small">
                      {{ pdetail.date ? $filter.enFormat(pdetail.date) : (pdetail.expense?.expense_date ? $filter.enFormat(pdetail.expense.expense_date) : '-') }}
                    </td>
                    <td class="py-2.5 font-monospace small">
                      <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-0.5">
                        {{ pdetail.ref_no || pdetail.expense?.expenseid || ('EXP-' + (pdetail.expense_id || '')) }}
                      </span>
                    </td>
                    <td class="py-2.5 small text-muted">
                      {{ pdetail.branch_name || pdetail.expense?.branch?.branch_name || 'Main Branch' }}
                    </td>
                    <td class="py-2.5">
                      <div class="fw-semibold text-dark">
                        {{ pdetail.account_name || (pdetail.account?.account_code ? pdetail.account?.account_code + ' - ' : '') + (pdetail.account?.account_name || '') }}
                      </div>
                    </td>
                    <td class="py-2.5 small">
                      {{ pdetail.payee_name || pdetail.expense?.employee?.full_name || 'Office' }}
                    </td>
                    <td class="py-2.5 text-end font-monospace fw-semibold text-dark">
                      {{ $filter.formatBDT(pdetail.amount) }}
                    </td>
                    <td class="py-2.5 text-end font-monospace fw-semibold text-success">
                      {{ $filter.formatBDT(pdetail.paid_amount) }}
                    </td>
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold" :class="Number(pdetail.due_amount != null ? pdetail.due_amount : (pdetail.amount - pdetail.paid_amount)) > 0 ? 'text-danger' : 'text-muted'">
                      {{ $filter.formatBDT(pdetail.due_amount != null ? pdetail.due_amount : (pdetail.amount - pdetail.paid_amount)) }}
                    </td>
                  </tr>

                  <tr v-if="!datas.details || datas.details.length === 0">
                    <td colspan="10" class="text-center py-5 text-muted">
                      <i class="fas fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
                      <div class="font-monospace">{{ $t('No expense or outflow records found for the selected criteria.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot v-if="datas.details && datas.details.length > 0" style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="7" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Total') }} ({{ datas.details.length }} {{ $t('Records') }}):
                    </td>
                    <td class="py-3 text-end text-dark fs-6">
                      {{ $filter.formatBDT(datas.total_expense || 0) }}
                    </td>
                    <td class="py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(datas.total_paid || 0) }}
                    </td>
                    <td class="pe-4 py-3 text-end text-danger fs-6">
                      {{ $filter.formatBDT(datas.total_due || 0) }}
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
                <div>Generated by: <strong>{{ $root.admin?.full_name || 'Admin' }}</strong></div>
                <div style="font-size: 10px;">This report is system generated and reflects real-time payment settlement balances.</div>
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
  Title: "Expense Statement",
};
const model = "expenseStatement";

export default {
  data() {
    return {
      model: model,
      page_title: "Expense Statement",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      activePreset: "all",
      moduleOptions: [
        { label: "All Modules (Expense, Loan, GRN, Salary, Commission)", value: "all" },
        { label: "Expense", value: "expense" },
        { label: "GRN (Supplier Purchases)", value: "grn" },
        { label: "Loan & Advance", value: "loan" },
        { label: "Salary & Allowance", value: "salary" },
        { label: "Commission", value: "commission" },
      ],
      search_data: {
        module_type: "all",
        branch_id: null,
        employee_id: null,
        supplier_id: null,
        payment_method: null,
        keyword: "",
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
        details: [],
        total_expense: 0,
        total_paid: 0,
        total_due: 0,
        counts: {
          all: 0,
          expense: 0,
          loan: 0,
          grn: 0,
          salary: 0,
          commission: 0,
        },
        breakdown: {},
      },
      clients: [],
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
    paidPercentage() {
      const exp = Number(this.datas.total_expense || 0);
      const paid = Number(this.datas.total_paid || 0);
      if (exp <= 0) return "0";
      return Math.min(100, (paid / exp) * 100).toFixed(1);
    },

    exportData() {
      if (!this.datas.details || this.datas.details.length === 0) return [];
      return this.datas.details.map((item, idx) => ({
        SL: idx + 1,
        Source: item.source_type || "Expense",
        Date: item.date || item.expense?.expense_date || "",
        "Ref No": item.ref_no || "",
        Branch: item.branch_name || "Main Branch",
        "Account / Head": item.account_name || "",
        "Payee / Beneficiary": item.payee_name || "",
        "Approved By": item.approved_by_name || "",
        "Amount (Tk)": Number(item.amount || 0),
        "Paid Amount (Tk)": Number(item.paid_amount || 0),
        "Due Amount (Tk)": Number(
          item.due_amount != null ? item.due_amount : item.amount - item.paid_amount
        ),
      }));
    },

    exportFileName() {
      return `expense_statement_${moment().format("YYYY_MM_DD")}.xls`;
    },
  },

  methods: {
    search() {
      this.getIncomeStatement();
    },

    resetSearchData() {
      this.search_data = {
        module_type: "all",
        branch_id: null,
        employee_id: null,
        supplier_id: null,
        payment_method: null,
        keyword: "",
        from_date: null,
        to_date: null,
      };
      this.activePreset = "all";
      this.getIncomeStatement();
    },

    filterByModule(type) {
      this.search_data.module_type = type;
      this.getIncomeStatement();
    },

    applyDatePreset(type) {
      this.activePreset = type;
      if (type === "today") {
        this.search_data.from_date = moment().format("YYYY-MM-DD");
        this.search_data.to_date = moment().format("YYYY-MM-DD");
      } else if (type === "yesterday") {
        this.search_data.from_date = moment().subtract(1, "day").format("YYYY-MM-DD");
        this.search_data.to_date = moment().subtract(1, "day").format("YYYY-MM-DD");
      } else if (type === "last7") {
        this.search_data.from_date = moment().subtract(6, "days").format("YYYY-MM-DD");
        this.search_data.to_date = moment().format("YYYY-MM-DD");
      } else if (type === "thisMonth") {
        this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
        this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
      } else if (type === "lastMonth") {
        this.search_data.from_date = moment().subtract(1, "month").startOf("month").format("YYYY-MM-DD");
        this.search_data.to_date = moment().subtract(1, "month").endOf("month").format("YYYY-MM-DD");
      } else if (type === "thisYear") {
        this.search_data.from_date = moment().startOf("year").format("YYYY-MM-DD");
        this.search_data.to_date = moment().endOf("year").format("YYYY-MM-DD");
      } else if (type === "all") {
        this.search_data.from_date = null;
        this.search_data.to_date = null;
      }
      this.getIncomeStatement();
    },

    getIncomeStatement() {
      if (this.$root && this.$root.tableSpinner !== undefined) {
        this.$root.tableSpinner = true;
      }
      axios
        .get(`report/expensestatement/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error("Expense statement fetch error:", err);
        })
        .finally(() => {
          if (this.$root && this.$root.tableSpinner !== undefined) {
            this.$root.tableSpinner = false;
          }
        });
    },

    getActiveModuleName() {
      const opt = this.moduleOptions.find((o) => o.value === this.search_data.module_type);
      return opt ? opt.label : "All Modules";
    },

    getSourceBadgeClass(type) {
      switch ((type || "").toLowerCase()) {
        case "expense":
          return "badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 px-2 py-0.5";
        case "grn":
          return "badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5";
        case "loan":
          return "badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-0.5";
        case "salary":
          return "badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5";
        case "commission":
          return "badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-0.5";
        default:
          return "badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-0.5";
      }
    },
  },

  created() {
    this.getIncomeStatement();
  },

  validators: {},
};
</script>

<style scoped>
.expense-hero-banner {
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
.expensestatement-table th {
  font-size: 11.5px;
  letter-spacing: 0.5px;
}

@media print {
  .print-only-header {
    display: block !important;
  }
  .d-print-none {
    display: none !important;
  }
}
</style>
