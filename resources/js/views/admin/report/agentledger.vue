<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Select Agent" field="search_data.agent_id" col="4">
        <v-select
          v-model="search_data.agent_id"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="agents"
          placeholder="-- Select Agent --"
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
      <!-- Action Bar with Single Print Button -->
      <div class="col-12 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge px-3 py-2 text-white font-monospace" style="background-color: #112C47; font-size: 12px;">
            <i class="fas fa-book-open me-1"></i> {{ $t('Agent Ledger Statement') }}
          </span>
          <span v-if="datas.agent" class="text-muted small font-monospace">
            Period: <strong>{{ dateRange }}</strong>
          </span>
        </div>
        <div v-if="datas.agent" class="print_action text-end">
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
      <div v-if="datas.agent" class="col-12">
        <!-- 🌟 Top Hero / Agent Banner -->
        <div class="card border-0 shadow-sm mb-4 ledger-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-user-tie fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ datas.agent.full_name }}
                    </h4>
                    <span v-if="datas.agent.id" class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      #{{ datas.agent.id }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span v-if="datas.agent.mobile">
                      <i class="fas fa-phone-alt me-1 text-success"></i> {{ datas.agent.mobile }}
                    </span>
                    <span v-if="datas.agent.organization">
                      <i class="fas fa-building me-1 text-info"></i> {{ datas.agent.organization }}
                    </span>
                    <span v-if="datas.agent.designation">
                      <i class="fas fa-briefcase me-1 text-warning"></i> {{ datas.agent.designation }}
                    </span>
                    <span v-if="datas.agent.address">
                      <i class="fas fa-map-marker-alt me-1 text-light"></i> {{ datas.agent.address }}
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
                  {{ closingBalance > 0 ? $t('Payable Due') : (closingBalance < 0 ? $t('Receivable / Advance') : $t('Settled')) }}
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
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Previous Balance') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Period Debit -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Debit (Paid)') }}</span>
                  <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-circle-down"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-danger font-monospace text-nowrap">
                  {{ $filter.formatBDT(totalDebit) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Payments / Adjustments') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Total Period Credit -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Credit (Commission)') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrow-circle-up"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(totalCredit) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Earned Commissions / Bills') }}</small>
              </div>
            </div>
          </div>

          <!-- 4. Net Closing Balance -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Closing Balance') }}</span>
                  <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center"
                    :class="closingBalance > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-primary bg-opacity-10 text-primary'">
                    <i class="fas fa-wallet"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 font-monospace text-nowrap" :class="closingBalance > 0 ? 'text-dark' : 'text-primary'">
                  {{ $filter.formatBDT(closingBalance) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">
                  {{ closingBalance > 0 ? $t('Payable') : $t('Settled') }}
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
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Agent Ledger Statement') }}</h5>
                <div class="small text-dark font-monospace">
                  <strong>{{ datas.agent.full_name }}</strong>
                  <span v-if="datas.agent.mobile"> | {{ datas.agent.mobile }}</span>
                  <span v-if="datas.agent.address"> | {{ datas.agent.address }}</span>
                </div>
              </div>
              <div class="text-end font-monospace small">
                <div>Period: <strong>{{ dateRange }}</strong></div>
                <div>Generated: <strong>{{ reportDate }}</strong></div>
              </div>
            </div>
          </div>

          <!-- Table Content -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 ledger-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 140px;">{{ $t('Date') }}</th>
                    <th class="py-3" style="width: 150px;">{{ $t('Voucher No') }}</th>
                    <th class="py-3">{{ $t('Particulars / Account') }}</th>
                    <th class="py-3 text-end" style="width: 140px;">{{ $t('Debit (Paid)') }}</th>
                    <th class="py-3 text-end" style="width: 140px;">{{ $t('Credit (Earned)') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 160px;">{{ $t('Balance') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(record, index) in datas.records"
                    :key="index"
                    :class="{'bg-light fw-semibold': !record.voucherno && index === 0}"
                  >
                    <td class="ps-4 py-2.5 font-monospace small">
                      <span v-if="record.voucher_date">
                        {{ $filter.enFormat(record.voucher_date) }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="py-2.5 font-monospace small">
                      <span v-if="record.voucherno" class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-1">
                        {{ record.voucherno }}
                      </span>
                      <span v-else class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                        {{ $t('Opening') }}
                      </span>
                    </td>
                    <td class="py-2.5">
                      <div class="fw-semibold text-dark">
                        {{ record.account_name || 'General Transaction' }}
                      </div>
                      <small v-if="record.account_type" class="text-muted font-monospace" style="font-size: 11px;">
                        Type: {{ record.account_type }}
                      </small>
                    </td>
                    <td class="py-2.5 text-end font-monospace">
                      <span v-if="Number(record.dr_amount) > 0" class="text-danger fw-semibold">
                        {{ $filter.formatBDT(record.dr_amount) }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="py-2.5 text-end font-monospace">
                      <span v-if="Number(record.cr_amount) > 0" class="text-success fw-semibold">
                        {{ $filter.formatBDT(record.cr_amount) }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold" :class="Number(record.running_balance) > 0 ? 'text-dark' : 'text-primary'">
                      {{ $filter.formatBDT(record.running_balance) }}
                    </td>
                  </tr>

                  <tr v-if="!datas.records || datas.records.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                      <div class="font-monospace">{{ $t('No ledger records found for this period.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot v-if="datas.records && datas.records.length > 0" style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="3" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Total Period Activity & Closing') }}
                    </td>
                    <td class="py-3 text-end text-danger fs-6">
                      {{ $filter.formatBDT(totalDebit) }}
                    </td>
                    <td class="py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(totalCredit) }}
                    </td>
                    <td class="pe-4 py-3 text-end fs-6" :class="closingBalance > 0 ? 'text-dark' : 'text-primary'">
                      {{ $filter.formatBDT(closingBalance) }}
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

      <!-- Empty / Placeholder State -->
      <div v-else class="col-12 py-5 text-center bg-white rounded-3 shadow-sm border">
        <div class="py-4">
          <div class="empty-state-icon mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
            style="background-color: #F1F5F9; width: 70px; height: 70px; color: #112C47;">
            <i class="fas fa-user-tie fa-2x"></i>
          </div>
          <h5 class="fw-bold text-dark font-monospace mb-1">{{ $t('Please Select an Agent') }}</h5>
          <p class="text-muted small mb-0 font-monospace">
            {{ $t('Select an agent from the filter above to generate the statement.') }}
          </p>
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
  Title: "Agent Ledger",
};
const model = "agentledger";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      search_data: {
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
      datas: {},
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
      return "All Historical Transactions";
    },

    totalDebit() {
      if (!this.datas.records || !this.datas.records.length) return 0;
      return this.datas.records.reduce((sum, item) => sum + (Number(item.dr_amount) || 0), 0);
    },

    totalCredit() {
      if (!this.datas.records || !this.datas.records.length) return 0;
      return this.datas.records.reduce((sum, item) => sum + (Number(item.cr_amount) || 0), 0);
    },

    closingBalance() {
      if (!this.datas.records || !this.datas.records.length) {
        return Number(this.datas.opening_balance) || 0;
      }
      const lastRow = this.datas.records[this.datas.records.length - 1];
      return Number(lastRow.running_balance) || 0;
    },
  },

  methods: {
    search() {
      this.getAgentLedger();
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

    getAgentLedger() {
      if (!this.search_data.agent_id) return;
      axios
        .get(`report/agentledger/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    resetSearchData() {
      this.search_data.agent_id = null;
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.datas = {};
    },
  },

  created() {
    this.getAgents();
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
.ledger-table th {
  font-size: 11.5px;
  letter-spacing: 0.5px;
}

@media print {
  .print-only-header {
    display: block !important;
  }
}
</style>
