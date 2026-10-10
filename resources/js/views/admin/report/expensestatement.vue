<template>
    <index-page :defaultTable="false" :show_status="false">
        <!-- 🔍 Search & Filter Fields -->
        <template v-slot:search-field>
            <!-- Quick Date Presets -->
            <div class="col-12 mb-3">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted small fw-bold me-1">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i>{{ $t("Quick Date:") }}
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-primary"
                        :class="{ 'active': activePreset === 'today' }"
                        @click="applyDatePreset('today')">{{ $t("Today") }}</button>
                    <button type="button" class="btn btn-xs btn-outline-primary"
                        :class="{ 'active': activePreset === 'yesterday' }"
                        @click="applyDatePreset('yesterday')">{{ $t("Yesterday") }}</button>
                    <button type="button" class="btn btn-xs btn-outline-primary"
                        :class="{ 'active': activePreset === 'last7' }"
                        @click="applyDatePreset('last7')">{{ $t("Last 7 Days") }}</button>
                    <button type="button" class="btn btn-xs btn-outline-primary"
                        :class="{ 'active': activePreset === 'thisMonth' }"
                        @click="applyDatePreset('thisMonth')">{{ $t("This Month") }}</button>
                    <button type="button" class="btn btn-xs btn-outline-primary"
                        :class="{ 'active': activePreset === 'lastMonth' }"
                        @click="applyDatePreset('lastMonth')">{{ $t("Last Month") }}</button>
                    <button type="button" class="btn btn-xs btn-outline-primary"
                        :class="{ 'active': activePreset === 'thisYear' }"
                        @click="applyDatePreset('thisYear')">{{ $t("This Year") }}</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary"
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
                <label class="form-label small fw-bold">{{ $t('Search Keyword') }}</label>
                <input type="text" class="form-control form-control-sm" v-model="search_data.keyword"
                    :placeholder="$t('Ref # / Payee / Head / Source')" @keyup.enter="search" />
            </div>
        </template>

        <!-- 📊 Report Content & Table -->
        <template v-slot:table-list>
            <!-- 🌟 Action Bar (Quick Module Filter Tabs & Export / Print) -->
            <div class="col-12 mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <!-- Source filter pills -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" class="btn btn-sm"
                            :class="search_data.module_type === 'all' || !search_data.module_type ? 'btn-primary' : 'btn-outline-primary'"
                            @click="filterByModule('all')">
                            {{ $t('All Sources') }}
                            <span class="badge bg-white text-primary ms-1">{{ datas.counts?.all ?? (datas.details?.length || 0) }}</span>
                        </button>
                        <button type="button" class="btn btn-sm"
                            :class="search_data.module_type === 'expense' ? 'btn-info text-dark' : 'btn-outline-info'"
                            @click="filterByModule('expense')">
                            {{ $t('Expense') }}
                            <span class="badge bg-dark text-white ms-1">{{ datas.counts?.expense ?? 0 }}</span>
                        </button>
                        <button type="button" class="btn btn-sm"
                            :class="search_data.module_type === 'grn' ? 'btn-primary' : 'btn-outline-primary'"
                            @click="filterByModule('grn')">
                            {{ $t('GRN / Purchases') }}
                            <span class="badge bg-white text-primary ms-1">{{ datas.counts?.grn ?? 0 }}</span>
                        </button>
                        <button type="button" class="btn btn-sm"
                            :class="search_data.module_type === 'loan' ? 'btn-warning text-dark' : 'btn-outline-warning'"
                            @click="filterByModule('loan')">
                            {{ $t('Loan & Advance') }}
                            <span class="badge bg-dark text-white ms-1">{{ datas.counts?.loan ?? 0 }}</span>
                        </button>
                        <button type="button" class="btn btn-sm"
                            :class="search_data.module_type === 'salary' ? 'btn-success' : 'btn-outline-success'"
                            @click="filterByModule('salary')">
                            {{ $t('Salary') }}
                            <span class="badge bg-white text-success ms-1">{{ datas.counts?.salary ?? 0 }}</span>
                        </button>
                        <button type="button" class="btn btn-sm"
                            :class="search_data.module_type === 'commission' ? 'btn-secondary' : 'btn-outline-secondary'"
                            @click="filterByModule('commission')">
                            {{ $t('Commission') }}
                            <span class="badge bg-white text-secondary ms-1">{{ datas.counts?.commission ?? 0 }}</span>
                        </button>
                    </div>

                    <!-- Export & Print Actions -->
                    <div class="d-flex align-items-center gap-2">
                        <download-excel
                            v-if="exportData.length > 0"
                            class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
                            :data="exportData"
                            :name="exportFileName">
                            <i class="fas fa-file-excel"></i> {{ $t("Export Excel") }}
                        </download-excel>
                        <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
                            data-bs-toggle="tooltip" data-bs-placement="top" :data-bs-title="$t('Print')"
                            v-x-tooltip @click="print('printArea', model)">
                            <i class="fas fa-print"></i> {{ $t("Print") }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🌟 Financial KPI Metric Cards -->
            <div class="col-12 mb-4 d-print-none">
                <div class="row g-3">
                    <!-- Total Expense / Outflow -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-danger text-white h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Total Expense / Outflow') }}</div>
                                    <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(datas.total_expense) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-money-bill-wave"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">
                                {{ $t('Gross Outflow Value') }}
                            </small>
                        </div>
                    </div>

                    <!-- Total Paid Amount -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Total Paid Amount') }}</div>
                                    <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(datas.total_paid) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">
                                {{ $t('Settled Outflows') }} ({{ paidPercentage }}%)
                            </small>
                        </div>
                    </div>

                    <!-- Total Due / Balance -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-warning text-white h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Total Due / Balance') }}</div>
                                    <div class="fs-4 fw-bold mt-1">৳ {{ formatMoney(datas.total_due) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">
                                {{ $t('Outstanding Payables') }}
                            </small>
                        </div>
                    </div>

                    <!-- Selected Module & Count -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">{{ $t('Total Records') }}</div>
                                    <div class="fs-4 fw-bold mt-1">{{ datas.details?.length || 0 }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-file-invoice"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block text-truncate">
                                Source: <strong>{{ getActiveModuleName() }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🧾 Report Area (Printable) -->
            <div class="my-3" id="printArea">
                <!-- Report Header for Print -->
                <div class="text-center mb-3 report-title">
                    <h3 class="fw-bold mb-1">{{ $root.site?.title || 'QPOS' }}.</h3>
                    <p class="mb-1 text-muted">{{ $root.site?.address }}</p>
                    <p class="text-muted small">
                        Email: {{ $root.site?.contact_email }} | Phone: {{ $root.site?.mobile1 }}
                    </p>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">{{ $t("Expense & Outflow Statement") }}</h5>
                        <small class="text-muted">{{ $t("Generated Date") }}: <strong>{{ reportDate }}</strong></small>
                    </div>
                    <div class="text-end small text-muted">
                        <span v-if="search_data.from_date && search_data.to_date">
                            {{ $t('Period') }}: <strong>{{ search_data.from_date }}</strong> to <strong>{{ search_data.to_date }}</strong>
                        </span>
                        <span v-else>{{ $t('Period') }}: <strong>{{ $t('All Time') }}</strong></span>
                        <span class="ms-2">| {{ $t('Source') }}: <strong>{{ getActiveModuleName() }}</strong></span>
                    </div>
                </div>

                <!-- 📊 Statement Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="fw-bold text-center small text-uppercase">
                                <th style="width: 45px;">{{ $t('#') }}</th>
                                <th style="width: 100px;">{{ $t('Source / Module') }}</th>
                                <th style="width: 110px;">{{ $t('Date') }}</th>
                                <th style="width: 150px;">{{ $t('Ref / Voucher #') }}</th>
                                <th style="width: 120px;">{{ $t('Branch') }}</th>
                                <th>{{ $t('Account / Head') }}</th>
                                <th>{{ $t('Payee / Beneficiary') }}</th>
                                <th style="width: 120px;">{{ $t('Approved By') }}</th>
                                <th style="width: 120px;" class="text-end">{{ $t('Amount (৳)') }}</th>
                                <th style="width: 120px;" class="text-end">{{ $t('Paid Amnt (৳)') }}</th>
                                <th style="width: 120px;" class="text-end">{{ $t('Due Amnt (৳)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="datas.details && datas.details.length > 0">
                                <tr v-for="(pdetail, index) in datas.details" :key="pdetail.id || index">
                                    <td class="text-center text-muted small">{{ index + 1 }}</td>
                                    <td class="text-center">
                                        <span :class="getSourceBadgeClass(pdetail.source_type)">
                                            {{ pdetail.source_type || 'Expense' }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap small">
                                        {{ pdetail.date || pdetail.expense?.expense_date }}
                                    </td>
                                    <td class="text-center fw-semibold text-primary small">
                                        {{ pdetail.ref_no || pdetail.expense?.expenseid || ('EXP-' + (pdetail.expense_id || '')) }}
                                    </td>
                                    <td class="text-center small">
                                        {{ pdetail.branch_name || pdetail.expense?.branch?.branch_name || 'Main Branch' }}
                                    </td>
                                    <td class="small">
                                        {{ pdetail.account_name || (pdetail.account?.account_code ? pdetail.account?.account_code + ' - ' : '') + (pdetail.account?.account_name || '') }}
                                    </td>
                                    <td class="small fw-semibold">
                                        {{ pdetail.payee_name || pdetail.expense?.employee?.full_name || 'Office' }}
                                    </td>
                                    <td class="text-center small text-muted">
                                        {{ pdetail.approved_by_name || pdetail.expense?.approved_admin?.full_name || 'Admin' }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ formatMoney(pdetail.amount) }}
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        {{ formatMoney(pdetail.paid_amount) }}
                                    </td>
                                    <td class="text-end fw-bold" :class="Number(pdetail.due_amount || 0) > 0 ? 'text-danger' : 'text-muted'">
                                        {{ formatMoney(pdetail.due_amount != null ? pdetail.due_amount : (pdetail.amount - pdetail.paid_amount)) }}
                                    </td>
                                </tr>

                                <!-- 📌 Totals Summary Row -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="8" class="text-end">
                                        <strong>{{ $t("Total") }} ({{ datas.details.length }} {{ $t("Records") }}):</strong>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <strong>{{ formatMoney(datas.total_expense) }}</strong>
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        <strong>{{ formatMoney(datas.total_paid) }}</strong>
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        <strong>{{ formatMoney(datas.total_due) }}</strong>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                        <span>{{ $t("No expense or outflow records found for the selected criteria.") }}</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- 📌 Footer Note -->
                <div class="mt-4 pt-2 border-top d-flex justify-content-between small text-muted">
                    <div>
                        <p class="mb-0">Generated by: <strong>{{ $root.admin?.full_name || 'Admin' }}</strong></p>
                        <p class="mb-0">This report is system generated and reflects real-time payment settlement balances.</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0">{{ $t('Printed on') }}: {{ reportDate }}</p>
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
            reportDate: moment().format('D MMMM, YYYY'),
            json_fields: json_fields,
            activePreset: 'all',
            moduleOptions: [
                { label: 'All Modules (Expense, Loan, GRN, Salary, Commission)', value: 'all' },
                { label: 'Expense', value: 'expense' },
                { label: 'GRN (Supplier Purchases)', value: 'grn' },
                { label: 'Loan & Advance', value: 'loan' },
                { label: 'Salary & Allowance', value: 'salary' },
                { label: 'Commission', value: 'commission' },
            ],
            search_data: {
                module_type: 'all',
                branch_id: null,
                employee_id: null,
                supplier_id: null,
                payment_method: null,
                keyword: '',
                from_date: null,
                to_date: null
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
                breakdown: {}
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
            if (exp <= 0) return '0';
            return Math.min(100, (paid / exp) * 100).toFixed(1);
        },
        exportData() {
            if (!this.datas.details || this.datas.details.length === 0) return [];
            return this.datas.details.map((item, idx) => ({
                'SL': idx + 1,
                'Source': item.source_type || 'Expense',
                'Date': item.date || item.expense?.expense_date || '',
                'Ref No': item.ref_no || '',
                'Branch': item.branch_name || 'Main Branch',
                'Account / Head': item.account_name || '',
                'Payee / Beneficiary': item.payee_name || '',
                'Approved By': item.approved_by_name || '',
                'Amount (Tk)': Number(item.amount || 0),
                'Paid Amount (Tk)': Number(item.paid_amount || 0),
                'Due Amount (Tk)': Number(item.due_amount != null ? item.due_amount : (item.amount - item.paid_amount)),
            }));
        },
        exportFileName() {
            return `expense_statement_${moment().format('YYYY_MM_DD')}.xls`;
        }
    },

    methods: {
        search() {
            this.getIncomeStatement();
        },
        resetSearchData() {
            this.search_data = {
                module_type: 'all',
                branch_id: null,
                employee_id: null,
                supplier_id: null,
                payment_method: null,
                keyword: '',
                from_date: null,
                to_date: null
            };
            this.activePreset = 'all';
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
            const opt = this.moduleOptions.find(o => o.value === this.search_data.module_type);
            return opt ? opt.label : 'All Modules';
        },
        formatMoney(val) {
            if (val == null || isNaN(val)) return '0.00';
            return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        getSourceBadgeClass(type) {
            switch ((type || '').toLowerCase()) {
                case 'expense':
                    return 'badge bg-info text-dark px-2 py-1';
                case 'grn':
                    return 'badge bg-primary px-2 py-1 text-white';
                case 'loan':
                    return 'badge bg-warning text-dark px-2 py-1';
                case 'salary':
                    return 'badge bg-success px-2 py-1 text-white';
                case 'commission':
                    return 'badge bg-secondary px-2 py-1 text-white';
                default:
                    return 'badge bg-dark px-2 py-1 text-white';
            }
        },
        ucfirst(str) {
            if (!str) return "";
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
    },

    created() {
        this.getIncomeStatement();
    },

    validators: {},
};
</script>

<style scoped>
.btn-xs {
    padding: 0.2rem 0.55rem;
    font-size: 0.75rem;
    border-radius: 0.25rem;
}
.btn-xs.active {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
}

.metric-icon {
    font-size: 2rem;
    opacity: 0.35;
}

@media print {
    .d-print-none {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>
