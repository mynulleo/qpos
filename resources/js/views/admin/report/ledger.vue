<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Select Account" field="search_data.account_id" col="4">
        <v-select
          v-model="search_data.account_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="$root.global.allaccounts"
          :placeholder="$t('-- Select Account --')"
          :closeOnSelect="true"
          @input="search"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfromexpensedate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date"
        placeholder="From Date"
        col="4"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoexpensedate"
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
            <i class="fas fa-book-open me-1"></i> {{ $t('General Ledger Account Statement') }}
          </span>
          <span v-if="datas.account_name" class="text-muted small font-monospace">
            {{ $t('Period') }}: <strong>{{ dateRange }}</strong>
          </span>
        </div>
        <div v-if="datas.account_name" class="print_action text-end">
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
      <div v-if="datas.account_name" class="col-12">
        <!-- 🌟 Top Hero / Account Banner -->
        <div class="card border-0 shadow-sm mb-4 ledger-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-file-invoice fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ datas.account_name }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ datas.account_type || $t('Account') }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span>
                      <i class="far fa-calendar-alt me-1"></i> {{ $t('Statement Period') }}: {{ dateRange }}
                    </span>
                    <span>
                      <i class="fas fa-clock me-1"></i> {{ $t('As of') }}: {{ reportDate }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Balance Pill -->
              <div class="text-md-end text-white font-monospace">
                <div class="small text-white-50 text-uppercase">{{ $t('Closing Balance') }}</div>
                <div class="fs-4 fw-bold text-white">
                  {{ $filter.formatBDT(datas.closing_balance || 0) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ $t('Cumulative Account Balance') }}
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
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Balance at Start of Period') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Debit -->
          <div class="col-xl-3 col-md-6 col-12">
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
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Total Debit Entries') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Total Credit -->
          <div class="col-xl-3 col-md-6 col-12">
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
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Total Credit Entries') }}</small>
              </div>
            </div>
          </div>

          <!-- 4. Net Closing Balance -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Closing Balance') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-wallet"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.closing_balance || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Ending Balance') }}</small>
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
                {{ $t('Email') }}: {{ $root.site.contact_email }} | {{ $t('Phone') }}: {{ $root.site.mobile1 }}
              </p>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div>
                <h5 class="fw-bold mb-1 text-dark">{{ datas.account_name }} - {{ $t('Account Ledger Statement') }}</h5>
                <div class="small text-dark font-monospace">
                  {{ $t('Type') }}: <strong>{{ datas.account_type }}</strong> | {{ $t('Period') }}: <strong>{{ dateRange }}</strong>
                </div>
              </div>
              <div class="text-end font-monospace small">
                <div>{{ $t('Generated') }}: <strong>{{ reportDate }}</strong></div>
              </div>
            </div>
          </div>

          <!-- Table Content -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 ledger-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 130px;">{{ $t('Date') }}</th>
                    <th class="py-3" style="width: 140px;">{{ $t('Voucher No') }}</th>
                    <th class="py-3">{{ $t('Particulars / Narration') }}</th>
                    <th class="py-3 text-end" style="width: 150px;">{{ $t('Debit Amount') }}</th>
                    <th class="py-3 text-end" style="width: 150px;">{{ $t('Credit Amount') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 160px;">{{ $t('Running Balance') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Opening Balance Row -->
                  <tr class="bg-light fw-semibold">
                    <td class="ps-4 py-2.5 font-monospace small">
                      {{ datas.opening_date || '-' }}
                    </td>
                    <td class="py-2.5 font-monospace small">
                      <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-1">
                        {{ $t('OPENING') }}
                      </span>
                    </td>
                    <td class="py-2.5">
                      <span class="text-dark fw-semibold">{{ $t('Opening Balance Brought Forward') }}</span>
                    </td>
                    <td class="py-2.5 text-end font-monospace text-muted">-</td>
                    <td class="py-2.5 text-end font-monospace text-muted">-</td>
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold text-dark">
                      {{ $filter.formatBDT(datas.opening_balance || 0) }}
                    </td>
                  </tr>

                  <!-- Entries -->
                  <tr v-for="(record, index) in datas.entries" :key="index">
                    <td class="ps-4 py-2.5 font-monospace small">
                      {{ record.voucher?.voucher_date ? $filter.enFormat(record.voucher.voucher_date) : '-' }}
                    </td>
                    <td class="py-2.5 font-monospace small">
                      <span v-if="record.voucher?.voucherno" class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-1">
                        {{ record.voucher.voucherno }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="py-2.5">
                      <div class="text-dark">
                        {{ record.line_narration || record.voucher?.narration || this.$t('General Entry') }}
                      </div>
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
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold text-dark">
                      {{ $filter.formatBDT(record.running_balance) }}
                    </td>
                  </tr>

                  <tr v-if="!datas.entries || datas.entries.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                      <div class="font-monospace">{{ $t('No entries recorded in this ledger during the period.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="3" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Total Period Activity & Closing Balance') }}
                    </td>
                    <td class="py-3 text-end text-danger fs-6">
                      {{ $filter.formatBDT(datas.total_debit || 0) }}
                    </td>
                    <td class="py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(datas.total_credit || 0) }}
                    </td>
                    <td class="pe-4 py-3 text-end text-primary fs-6">
                      {{ $filter.formatBDT(datas.closing_balance || 0) }}
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
                <div>{{ $t('Generated by') }}: <strong>{{ $t('Admin') }}</strong></div>
                <div style="font-size: 10px;">{{ $t('This is a computer-generated statement and requires no signature.') }}</div>
              </div>
              <div class="col-6 text-end">
                <div>{{ $t('Page 1 of 1') }}</div>
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
            <i class="fas fa-book fa-2x"></i>
          </div>
          <h5 class="fw-bold text-dark font-monospace mb-1">{{ $t('Please Select an Account') }}</h5>
          <p class="text-muted small mb-0 font-monospace">
            {{ $t('Choose an account head from the dropdown above to view its detailed ledger statement.') }}
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
  Title: "General Ledger",
};
const model = "ledger";

export default {
  data() {
    return {
      model: model,
      page_title: "Account Ledger",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      search_data: {
        account_id: null,
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
      return this.$t('Current Month');
    },
  },

  methods: {
    search() {
      this.getLedger();
    },

    getLedger() {
      if (!this.search_data.account_id) return;
      axios
        .get(`report/ledger/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    resetSearchData() {
      this.search_data.account_id = null;
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.datas = {};
    },
  },

  created() {
    // If account_id is passed via query params, auto-search
    if (this.$route.query.account_id) {
      this.search_data.account_id = Number(this.$route.query.account_id);
      this.getLedger();
    }
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
