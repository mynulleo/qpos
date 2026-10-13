<template>
  <index-page :show_status="false">
    <!-- 📊 Sleek Top KPI Metric Stats Cards -->
    <template v-slot:header-summary>
      <div class="row g-3 mb-3 expense-kpi-row">
        <!-- 1. Total Expense -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Expense') }}</span>
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-success font-monospace">
                {{ $t('Tk.') }} {{ $locale === 'bn' ? $bnNum(totalExpense) : totalExpense }}
              </h4>
              <small class="text-muted">
                {{ $locale === 'bn' ? $bnNum(table.datas ? table.datas.length : 0) : (table.datas ? table.datas.length : 0) }} {{ $t('Total Vouchers') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 2. Approved Expenses -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Approved Expense') }}</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-theme font-monospace">
                {{ $t('Tk.') }} {{ $locale === 'bn' ? $bnNum(totalApprovedExpense) : totalApprovedExpense }}
              </h4>
              <small class="text-muted">
                {{ $locale === 'bn' ? $bnNum(approvedCount) : approvedCount }} {{ $t('Approved') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 3. Pending Approval -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Pending Approval') }}</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="fas fa-clock"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-warning font-monospace">
                {{ $t('Tk.') }} {{ $locale === 'bn' ? $bnNum(totalPendingExpense) : totalPendingExpense }}
              </h4>
              <small class="text-muted">
                {{ $locale === 'bn' ? $bnNum(pendingCount) : pendingCount }} {{ $t('Pending Approval') }}
              </small>
            </div>
          </div>
        </div>

        <!-- 4. Active Status Ratio -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted fw-bold small text-uppercase">{{ $t('Status') }}</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="fas fa-shield-alt"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-1 text-dark font-monospace">
                {{ $locale === 'bn' ? $bnNum(activeCount) : activeCount }} / {{ $locale === 'bn' ? $bnNum(table.datas ? table.datas.length : 0) : (table.datas ? table.datas.length : 0) }}
              </h4>
              <small class="text-muted">
                {{ $t('Active') }} {{ $t('Voucher') }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🔍 Filter Fields in Advance Drawer -->
    <template v-slot:search-field>
      <v-select-container :title="$t('Employee')" field="search_data.employee_id" col="3">
        <v-select
          v-model="search_data.employee_id"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="$root.global.employees"
          :placeholder="$t('--Select Employee--')"
          :closeOnSelect="true"
        />
      </v-select-container>

      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label class="form-label fw-bold small text-secondary mb-1">{{ $t('Approval Status') }}</label>
          <select class="form-select form-select-sm shadow-none" v-model="search_data.approval_status">
            <option value="">{{ $t('-- All Status --') }}</option>
            <option value="approved">{{ $t('Approved') }}</option>
            <option value="pending">{{ $t('Pending Approval') }}</option>
          </select>
        </div>
      </div>

      <div class="col-md-2 col-sm-6">
        <div class="form-group">
          <label class="form-label fw-bold small text-secondary mb-1">{{ $t('Status') }}</label>
          <select class="form-select form-select-sm shadow-none" v-model="search_data.status">
            <option value="">{{ $t('-- All Status --') }}</option>
            <option value="active">{{ $t('Active') }}</option>
            <option value="inactive">{{ $t('Inactive') }}</option>
          </select>
        </div>
      </div>

      <date-picker
        id="searchfromexpensedate"
        v-model="search_data.from_expense_date"
        field="search_data.from_expense_date"
        :title="$t('From Expense Date')"
        :placeholder="$t('From Expense Date')"
        col="2"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoexpensedate"
        v-model="search_data.to_expense_date"
        field="search_data.to_expense_date"
        :title="$t('To Expense Date')"
        :placeholder="$t('To Expense Date')"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_expense_date"
      ></date-picker>
    </template>

    <!-- 📋 Custom Table Column Slots -->
    <!-- 1. Expense ID (Clickable Voucher Link) -->
    <template v-slot:expenseid="{ item }">
      <td>
        <router-link
          :to="{ name: 'expense.show', params: { id: item.id } }"
          class="fw-bold font-monospace text-decoration-none d-inline-flex align-items-center gap-1 expense-voucher-link"
          style="color: #112C47;"
        >
          <i class="fas fa-file-invoice-dollar text-primary"></i>
          <span>#{{ item.expenseid || item.id }}</span>
        </router-link>
      </td>
    </template>

    <!-- 2. Expense Date -->
    <template v-slot:expense_date="{ item }">
      <td>
        <span class="font-monospace text-dark d-inline-flex align-items-center gap-1">
          <i class="far fa-calendar-alt text-muted"></i>
          {{ item.expense_date || '-' }}
        </span>
      </td>
    </template>

    <!-- 3. Employee -->
    <template v-slot:employee_id="{ item }">
      <td>
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 26px; height: 26px; font-size: 11px;">
            <i class="fas fa-user-tie"></i>
          </div>
          <span class="fw-semibold text-dark">
            {{ item.employee ? item.employee.full_name : $t('Not Assigned') }}
          </span>
        </div>
      </td>
    </template>

    <!-- 4. Total Amount -->
    <template v-slot:total_amount="{ item }">
      <td class="text-end">
        <span class="font-monospace fw-bold text-success fs-6">
          {{ $t('Tk.') }} {{ $locale === 'bn' ? $bnNum($filter.formatBDT(item.total_amount || 0)) : $filter.formatBDT(item.total_amount || 0) }}
        </span>
      </td>
    </template>

    <!-- 5. Approval Status / Approved By -->
    <template v-slot:approved_by="{ item }">
      <td class="text-center">
        <span
          v-if="item.approved_by"
          class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1"
        >
          <i class="fas fa-check-circle"></i>
          {{ item.approved_admin ? item.approved_admin.full_name : $t('Approved') }}
        </span>
        <span
          v-else
          class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1"
        >
          <i class="fas fa-clock"></i>
          {{ $t('Pending Approval') }}
        </span>
      </td>
    </template>

    <!-- 6. Approved Date -->
    <template v-slot:approved_date="{ item }">
      <td class="text-center">
        <span class="font-monospace text-muted small" v-if="item.approved_date">
          <i class="fas fa-calendar-check me-1 text-success"></i>
          {{ item.approved_date }}
        </span>
        <span v-else class="text-muted">-</span>
      </td>
    </template>

    <!-- 7. Status -->
    <template v-slot:status="{ item }">
      <td class="text-center">
        <span
          class="badge font-monospace text-uppercase px-2 py-1"
          :class="item.status === 'active' ? 'bg-success' : 'bg-secondary'"
        >
          {{ $t(item.status === 'active' ? 'Active' : 'Inactive') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "expense";

const tableColumns = [
  { field: "expenseid", title: "Expense ID" },
  { field: "expense_date", title: "Expense Date" },
  { field: "employee_id", title: "Employee", subfield: "employee.full_name" },
  { field: "total_amount", title: "Total Amount", align: "right" },
  { field: "approved_by", title: "Approval Status", align: "center" },
  { field: "approved_date", title: "Approved Date", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Expense ID": "expenseid",
  "Expense Date": "expense_date",
  "Total Amount": "total_amount",
  "Approved By": "approved_by",
  "Approved Date": "approved_date",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", expenseid: "Expense ID" },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        approval_status: this.$route.query.approval_status ?? "",
        employee_id: this.$route.query.employee_id ?? null,
        from_expense_date: this.$route.query.from_expense_date ?? "",
        to_expense_date: this.$route.query.to_expense_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
    };
  },
  computed: {
    totalExpense() {
      if (!this.table.datas || this.table.datas.length === 0) return "0.00";
      const amount = this.table.datas.reduce((sum, row) => sum + Number(row.total_amount || 0), 0);
      return this.$filter.formatBDT(amount);
    },
    totalApprovedExpense() {
      if (!this.table.datas || this.table.datas.length === 0) return "0.00";
      const amount = this.table.datas.reduce((sum, row) => {
        return row.approved_by ? sum + Number(row.total_amount || 0) : sum;
      }, 0);
      return this.$filter.formatBDT(amount);
    },
    totalPendingExpense() {
      if (!this.table.datas || this.table.datas.length === 0) return "0.00";
      const amount = this.table.datas.reduce((sum, row) => {
        return !row.approved_by ? sum + Number(row.total_amount || 0) : sum;
      }, 0);
      return this.$filter.formatBDT(amount);
    },
    approvedCount() {
      if (!this.table.datas) return 0;
      return this.table.datas.filter((row) => !!row.approved_by).length;
    },
    pendingCount() {
      if (!this.table.datas) return 0;
      return this.table.datas.filter((row) => !row.approved_by).length;
    },
    activeCount() {
      if (!this.table.datas) return 0;
      return this.table.datas.filter((row) => row.status === "active").length;
    },
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
  methods: {
    search() {
      this.get_paginate(this.model, this.search_data);
    },
    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.approval_status = "";
      this.search_data.employee_id = null;
      this.search_data.from_expense_date = "";
      this.search_data.to_expense_date = "";
    },
  },
  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
  },
  validators: {},
};
</script>

<style scoped>
.stat-card {
  transition: all 0.25s ease-in-out;
  background-color: #ffffff;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
.theme-bg-soft {
  background-color: rgba(17, 44, 71, 0.1);
}
.text-theme {
  color: #112c47;
}
.expense-voucher-link:hover {
  text-decoration: underline !important;
  color: #0d6efd !important;
}
</style>