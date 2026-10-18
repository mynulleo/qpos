<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Filter Client" field="client_id" col="4 mb-3">
        <v-select
          v-model="search_data.client_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="clients"
          placeholder="-- All Clients --"
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
            <i class="fas fa-hand-holding-usd me-1"></i> {{ $t('Accounts Receivable Aging & Due Report') }}
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

      <!-- Main Receivable View -->
      <div class="col-12">
        <!-- 🌟 Top Hero Banner -->
        <div class="card border-0 shadow-sm mb-4 receivable-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-file-invoice-dollar fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ $t('Accounts Receivable Summary') }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ totalClientsWithDue }} {{ $t('Debtors') }}
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
                <div class="small text-white-50 text-uppercase">{{ $t('Total Outstanding Receivable') }}</div>
                <div class="fs-4 fw-bold text-warning">
                  {{ $filter.formatBDT(datas.grand_total || 0) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ $t('Net collectible amount from debtors') }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 4 KPI Stat Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Total Bill Amount -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Invoiced') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-file-invoice"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_bill_amount || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Total Sales / Billed') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Received Amount -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Received') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_received_amount || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Actual Collections Received') }}</small>
              </div>
            </div>
          </div>

          <!-- 3. Discounts Allowed -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-secondary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Discounts Allowed') }}</span>
                  <div class="stat-icon bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-tags"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_discount_amount || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Rebates / Price Reductions') }}</small>
              </div>
            </div>
          </div>

          <!-- 4. Total Net Due -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Net Due') }}</span>
                  <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-exclamation-circle"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-dark font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.grand_total || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Remaining Outstanding Receivable') }}</small>
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
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Accounts Receivable Summary Statement') }}</h5>
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
              <table class="table table-hover align-middle mb-0 receivable-table">
                <thead style="background-color: #F8FAFC;">
                  <tr class="text-uppercase small text-muted border-bottom font-monospace">
                    <th class="ps-4 py-3" style="width: 100px;">{{ $t('Client ID') }}</th>
                    <th class="py-3">{{ $t('Client Details') }}</th>
                    <th class="py-3 text-end" style="width: 150px;">{{ $t('Billed Amount') }}</th>
                    <th class="py-3 text-end" style="width: 150px;">{{ $t('Received Amount') }}</th>
                    <th class="py-3 text-end" style="width: 140px;">{{ $t('Discount') }}</th>
                    <th class="pe-4 py-3 text-end" style="width: 160px;">{{ $t('Total Due') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(receivable, index) in datas.receivables" :key="index">
                    <td class="ps-4 py-2.5 font-monospace small">
                      <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-2 py-1">
                        #{{ receivable.clientid }}
                      </span>
                    </td>
                    <td class="py-2.5">
                      <div class="fw-semibold text-dark">
                        {{ receivable.client_name }}
                      </div>
                      <small v-if="receivable.client_mobile" class="text-muted font-monospace" style="font-size: 11px;">
                        <i class="fas fa-phone-alt me-1 text-success"></i> {{ receivable.client_mobile }}
                      </small>
                    </td>
                    <td class="py-2.5 text-end font-monospace">
                      {{ $filter.formatBDT(receivable.bill_amount) }}
                    </td>
                    <td class="py-2.5 text-end font-monospace text-success fw-semibold">
                      {{ $filter.formatBDT(receivable.received_amount) }}
                    </td>
                    <td class="py-2.5 text-end font-monospace text-muted">
                      {{ Number(receivable.discount_amount) > 0 ? $filter.formatBDT(receivable.discount_amount) : '-' }}
                    </td>
                    <td class="pe-4 py-2.5 text-end font-monospace fw-bold" :class="Number(receivable.total_due) > 0 ? 'text-dark' : 'text-success'">
                      {{ $filter.formatBDT(receivable.total_due) }}
                    </td>
                  </tr>

                  <tr v-if="!datas.receivables || datas.receivables.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-user-check fa-3x mb-3 text-success opacity-50"></i>
                      <div class="font-monospace">{{ $t('No outstanding receivables recorded.') }}</div>
                    </td>
                  </tr>
                </tbody>

                <!-- Table Footer Totals strictly right aligned -->
                <tfoot v-if="datas.receivables && datas.receivables.length > 0" style="background-color: #F8FAFC;" class="border-top-2">
                  <tr class="fw-bold font-monospace">
                    <td colspan="2" class="ps-4 py-3 text-uppercase text-dark">
                      {{ $t('Grand Total') }}
                    </td>
                    <td class="py-3 text-end text-dark fs-6">
                      {{ $filter.formatBDT(datas.total_bill_amount || 0) }}
                    </td>
                    <td class="py-3 text-end text-success fs-6">
                      {{ $filter.formatBDT(datas.total_received_amount || 0) }}
                    </td>
                    <td class="py-3 text-end text-muted fs-6">
                      {{ $filter.formatBDT(datas.total_discount_amount || 0) }}
                    </td>
                    <td class="pe-4 py-3 text-end text-warning fs-6">
                      {{ $filter.formatBDT(datas.grand_total || 0) }}
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
  Title: "Receivable Report",
};
const model = "receivable";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      search_data: {
        client_id: null,
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
        receivables: [],
        total_bill_amount: 0,
        total_received_amount: 0,
        total_discount_amount: 0,
        grand_total: 0,
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

    totalClientsWithDue() {
      if (!this.datas.receivables) return 0;
      return this.datas.receivables.length;
    },
  },

  methods: {
    search() {
      this.getReceivable();
    },

    getReceivable() {
      axios
        .get(`report/receivable/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    getClients() {
      axios
        .get(`clients/`)
        .then((response) => {
          this.clients = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },

    resetSearchData() {
      this.search_data.client_id = null;
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.getReceivable();
    },
  },

  created() {
    this.getReceivable();
    this.getClients();
  },

  validators: {},
};
</script>

<style scoped>
.receivable-hero-banner {
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
.receivable-table th {
  font-size: 11.5px;
  letter-spacing: 0.5px;
}

@media print {
  .print-only-header {
    display: block !important;
  }
}
</style>
