<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Suppliers" field="supplier_id" col="2 mb-3">
        <v-select
          v-model="search_data.supplier_id"
          label="org_name"
          :reduce="(obj) => obj.id"
          :options="$root.global.suppliers"
          placeholder="-- All Suppliers --"
          :closeOnSelect="true"
          @input="search"
        />
      </v-select-container>

      <v-select-container title="Employees" field="employee_id" col="2 mb-3">
        <v-select
          v-model="search_data.employee_id"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="$root.global.employees"
          placeholder="-- All Employees --"
          :closeOnSelect="true"
          @input="search"
        />
      </v-select-container>

      <v-select-container title="Agents" field="agent_id" col="2 mb-3">
        <v-select
          v-model="search_data.agent_id"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="agents"
          placeholder="-- All Agents --"
          :closeOnSelect="true"
          @input="search"
        />
      </v-select-container>

      <date-picker
        id="searchfromexpensedate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date"
        placeholder="From Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoexpensedate"
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
            <i class="fas fa-file-invoice me-1"></i> {{ $t('Accounts Payable Outstanding Report') }}
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

      <!-- Main Payable View -->
      <div class="col-12">
        <!-- 🌟 Top Hero Banner -->
        <div class="card border-0 shadow-sm mb-4 payable-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-money-check-alt fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ $t('Accounts Payable Summary') }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ totalCreditors }} {{ $t('Creditors / Payees') }}
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
                <div class="small text-white-50 text-uppercase">{{ $t('Total Outstanding Payable') }}</div>
                <div class="fs-4 fw-bold text-danger">
                  {{ $filter.formatBDT(datas.total_outstanding || 0) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ $t('Total liabilities due to vendors & staff') }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 3 KPI Stat Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Total Payable Incurred -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Payable (Billed)') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-file-invoice"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_payable || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Total Incurred Bills & Liabilities') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Paid -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Paid') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-check-double"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_paid || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Disbursements Made') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Net Outstanding -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Outstanding Due') }}</span>
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_outstanding || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Unpaid Balance to Settle') }}</small>
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
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Accounts Payable Statement') }}</h5>
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
              <table class="table table-hover align-middle mb-0 payable-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 130px;">{{ $t('Date') }}</th>
                    <th class="py-3" style="width: 140px;">{{ $t('Source Type') }}</th>
                    <th class="py-3">{{ $t('Payee / Beneficiary') }}</th>
                    <th class="py-3 text-end" style="width: 160px;">{{ $t('Payable Amount') }}</th>
                    <th class="py-3 text-end" style="width: 160px;">{{ $t('Paid Amount') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 170px;">{{ $t('Outstanding Due') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(payable, index) in datas.payables" :key="index">
                    <td class="ps-4 py-2.5 font-monospace small">
                      {{ payable.date ? $filter.enFormat(payable.date) : '-' }}
                    </td>
                    <td class="py-2.5 font-monospace small">
                      <span class="badge px-2 py-1 text-uppercase"
                        :class="payable.source === 'Supplier' ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : (payable.source === 'Employee' ? 'bg-info bg-opacity-10 text-info border border-info border-opacity-25' : 'bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25')">
                        {{ payable.source }}
                      </span>
                    </td>
                    <td class="py-2.5">
                      <div class="fw-semibold text-dark">
                        {{ payable.name }}
                      </div>
                    </td>
                    <td class="py-2.5 text-end font-monospace text-dark">
                      {{ $filter.formatBDT(payable.payable) }}
                    </td>
                    <td class="py-2.5 text-end font-monospace text-success fw-semibold">
                      {{ $filter.formatBDT(payable.paid) }}
                    </td>
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold" :class="Number(payable.outstanding) > 0 ? 'text-danger' : 'text-success'">
                      {{ $filter.formatBDT(payable.outstanding) }}
                    </td>
                  </tr>

                  <tr v-if="!datas.payables || datas.payables.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                      <div class="font-monospace">{{ $t('No outstanding payables found.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot v-if="datas.payables && datas.payables.length > 0" style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="3" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Grand Total') }}
                    </td>
                    <td class="py-3 text-end text-dark fs-6">
                      {{ $filter.formatBDT(datas.total_payable || 0) }}
                    </td>
                    <td class="py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(datas.total_paid || 0) }}
                    </td>
                    <td class="pe-4 py-3 text-end text-danger fs-6">
                      {{ $filter.formatBDT(datas.total_outstanding || 0) }}
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
  Title: "Payable Report",
};
const model = "payable";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      search_data: {
        supplier_id: null,
        employee_id: null,
        agent_id: null,
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
        payables: [],
        total_payable: 0,
        total_paid: 0,
        total_outstanding: 0,
      },
      agents: [],
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

    totalCreditors() {
      if (!this.datas.payables) return 0;
      return this.datas.payables.length;
    },
  },

  methods: {
    search() {
      this.getPayable();
    },

    getPayable() {
      axios
        .get(`report/payable/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    getAgents() {
      if (this.$root.global && this.$root.global.agents && this.$root.global.agents.length > 0) {
        this.agents = this.$root.global.agents;
      } else {
        axios
          .get(`getagents`)
          .then((response) => {
            this.agents = response.data;
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },

    resetSearchData() {
      this.search_data.supplier_id = null;
      this.search_data.employee_id = null;
      this.search_data.agent_id = null;
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.getPayable();
    },
  },

  created() {
    this.getPayable();
    this.getAgents();
  },

  validators: {},
};
</script>

<style scoped>
.payable-hero-banner {
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
.payable-table th {
  font-size: 11.5px;
  letter-spacing: 0.5px;
}

@media print {
  .print-only-header {
    display: block !important;
  }
}
</style>
