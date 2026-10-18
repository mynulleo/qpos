<template>
  <index-page :defaultTable="false" :show_status="false">
    <template v-slot:search-field>
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
            <i class="fas fa-file-invoice-dollar me-1"></i> {{ $t('Statement of Financial Position') }}
          </span>
          <span class="text-muted small font-monospace">
            {{ $t('Period') }}: <strong>{{ dateRange }}</strong>
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

      <!-- Main Balance Sheet View -->
      <div class="col-12">
        <!-- 🌟 Top Hero Banner -->
        <div class="card border-0 shadow-sm mb-4 balancesheet-hero-banner text-white">
          <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-white bg-opacity-20 text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                  <i class="fas fa-landmark fs-3"></i>
                </div>
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0 text-white">
                      {{ $t('Corporate Balance Sheet') }}
                    </h4>
                    <span class="badge bg-white bg-opacity-25 text-white font-monospace border border-white border-opacity-25 px-2 py-1">
                      {{ isBalanced ? $t('Balanced (A = L + E)') : $t('Variance') }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-3 mt-2 text-white-50 small flex-wrap font-monospace">
                    <span>
                      <i class="far fa-calendar-alt me-1"></i> {{ $t('As of') }}: {{ asOfDate }}
                    </span>
                    <span>
                      <i class="fas fa-clock me-1"></i> {{ $t('Generated') }}: {{ reportDate }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right Balance Pill -->
              <div class="text-md-end text-white font-monospace">
                <div class="small text-white-50 text-uppercase">{{ $t('Accounting Equilibrium') }}</div>
                <div class="fs-4 fw-bold" :class="isBalanced ? 'text-success' : 'text-danger'">
                  <i class="fas me-1" :class="isBalanced ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
                  {{ isBalanced ? $t('Balanced') : $filter.formatBDT(variance) }}
                </div>
                <small class="text-white-50" style="font-size: 11px;">
                  {{ isBalanced ? $t('Total Assets match Total Liabilities & Equity') : $t('Parity Variance') }}
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- 📊 3 KPI Stat Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Total Assets -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Assets') }}</span>
                  <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-coins"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-primary font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_assets || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $t('Current & Non-Current Holdings') }}</small>
              </div>
            </div>
          </div>

          <!-- 2. Total Liabilities & Equity -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Total Liabilities & Equity') }}</span>
                  <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-hand-holding-usd"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 text-success font-monospace text-nowrap">
                  {{ $filter.formatBDT(datas.total_liabilities_equity || 0) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">
                  {{ $t('Liabilities') }}: {{ $filter.formatBDT(totalLiabilities) }} | {{ $t('Equity') }}: {{ $filter.formatBDT(totalEquity) }}
                </small>
              </div>
            </div>
          </div>

          <!-- 3. Balance Check -->
          <div class="col-md-4 col-12">
            <div class="card stat-card border-0 shadow-sm h-100 bg-white border-start border-4"
              :class="isBalanced ? 'border-info' : 'border-danger'">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted fw-bold small text-uppercase">{{ $t('Balance Parity') }}</span>
                  <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center"
                    :class="isBalanced ? 'bg-info bg-opacity-10 text-info' : 'bg-danger bg-opacity-10 text-danger'">
                    <i class="fas" :class="isBalanced ? 'fa-balance-scale' : 'fa-triangle-exclamation'"></i>
                  </div>
                </div>
                <h5 class="fw-bold mb-1 font-monospace text-nowrap" :class="isBalanced ? 'text-dark' : 'text-danger'">
                  {{ isBalanced ? '0.00' : $filter.formatBDT(variance) }}
                </h5>
                <small class="text-muted font-monospace" style="font-size: 11px;">
                  {{ isBalanced ? $t('Assets equal Liabilities + Equity') : $t('Discrepancy') }}
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
                {{ $t('Email') }}: {{ $root.site.contact_email }} | {{ $t('Phone') }}: {{ $root.site.mobile1 }}
              </p>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div>
                <h5 class="fw-bold mb-1 text-dark">{{ $t('Statement of Financial Position (Balance Sheet)') }}</h5>
                <div class="small text-dark font-monospace">
                  {{ $t('As of') }}: <strong>{{ asOfDate }}</strong>
                </div>
              </div>
              <div class="text-end font-monospace small">
                <div>{{ $t('Generated') }}: <strong>{{ reportDate }}</strong></div>
              </div>
            </div>
          </div>

          <div class="card-body p-4">
            <div class="row g-4">
              <!-- Left Column: Assets -->
              <div class="col-lg-6 col-12">
                <div class="border rounded-3 p-3 bg-light-subtle h-100 d-flex flex-column justify-content-between">
                  <div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                      <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fas fa-coins text-primary"></i>
                        <span>{{ $t('Assets') }}</span>
                      </div>
                      <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary font-monospace px-2.5 py-1">
                        {{ $t('Debit Balance') }}
                      </span>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                          <tr class="text-uppercase small text-muted font-monospace border-bottom">
                            <th style="width: 80px;">{{ $t('Code') }}</th>
                            <th>{{ $t('Account Head') }}</th>
                            <th class="text-end" style="width: 130px;">{{ $t('Balance') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(asset, index) in datas.assets" :key="index">
                            <td class="font-monospace small text-muted">
                              <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-1.5 py-0.5">
                                {{ asset.code || '-' }}
                              </span>
                            </td>
                            <td>
                              <span class="fw-semibold text-dark">{{ asset.name }}</span>
                            </td>
                            <td class="text-end font-monospace fw-semibold" :class="Number(asset.balance) >= 0 ? 'text-dark' : 'text-danger'">
                              {{ $filter.formatBDT(asset.balance) }}
                            </td>
                          </tr>
                          <tr v-if="!datas.assets || datas.assets.length === 0">
                            <td colspan="3" class="text-center py-4 text-muted font-monospace">
                              {{ $t('No asset accounts found.') }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- Assets Total Footer -->
                  <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top font-monospace fw-bold">
                    <span class="text-uppercase text-dark">{{ $t('Total Assets') }}:</span>
                    <span class="text-primary fs-6">{{ $filter.formatBDT(datas.total_assets || 0) }}</span>
                  </div>
                </div>
              </div>

              <!-- Right Column: Liabilities & Equity -->
              <div class="col-lg-6 col-12">
                <div class="border rounded-3 p-3 bg-light-subtle h-100 d-flex flex-column justify-content-between">
                  <div>
                    <!-- Liabilities Sub-Section -->
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                      <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fas fa-file-invoice text-danger"></i>
                        <span>{{ $t('Liabilities') }}</span>
                      </div>
                      <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger font-monospace px-2.5 py-1">
                        {{ $t('Credit Balance') }}
                      </span>
                    </div>

                    <div class="table-responsive mb-4">
                      <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                          <tr class="text-uppercase small text-muted font-monospace border-bottom">
                            <th style="width: 80px;">{{ $t('Code') }}</th>
                            <th>{{ $t('Account Head') }}</th>
                            <th class="text-end" style="width: 130px;">{{ $t('Balance') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(liability, index) in datas.liabilities" :key="'liab-' + index">
                            <td class="font-monospace small text-muted">
                              <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-1.5 py-0.5">
                                {{ liability.code || '-' }}
                              </span>
                            </td>
                            <td>
                              <span class="fw-semibold text-dark">{{ liability.name }}</span>
                            </td>
                            <td class="text-end font-monospace fw-semibold" :class="Number(liability.balance) >= 0 ? 'text-dark' : 'text-danger'">
                              {{ $filter.formatBDT(liability.balance) }}
                            </td>
                          </tr>
                          <tr v-if="!datas.liabilities || datas.liabilities.length === 0">
                            <td colspan="3" class="text-center py-2 text-muted font-monospace">
                              {{ $t('No liabilities recorded.') }}
                            </td>
                          </tr>
                        </tbody>
                        <tfoot class="border-top">
                          <tr class="font-monospace fw-semibold small">
                            <td colspan="2" class="text-muted">{{ $t('Total Liabilities') }}:</td>
                            <td class="text-end text-danger">{{ $filter.formatBDT(totalLiabilities) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>

                    <!-- Equity Sub-Section -->
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                      <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fas fa-chart-pie text-success"></i>
                        <span>{{ $t('Equity & Capital') }}</span>
                      </div>
                      <span class="badge rounded-pill bg-success bg-opacity-10 text-success font-monospace px-2.5 py-1">
                        {{ $t('Equity') }}
                      </span>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                          <tr class="text-uppercase small text-muted font-monospace border-bottom">
                            <th style="width: 80px;">{{ $t('Code') }}</th>
                            <th>{{ $t('Account Head') }}</th>
                            <th class="text-end" style="width: 130px;">{{ $t('Balance') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(equitydata, index) in datas.equity" :key="'eq-' + index">
                            <td class="font-monospace small text-muted">
                              <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 px-1.5 py-0.5">
                                {{ equitydata.code || '-' }}
                              </span>
                            </td>
                            <td>
                              <span class="fw-semibold text-dark">{{ equitydata.name }}</span>
                            </td>
                            <td class="text-end font-monospace fw-semibold" :class="Number(equitydata.balance) >= 0 ? 'text-dark' : 'text-danger'">
                              {{ $filter.formatBDT(equitydata.balance) }}
                            </td>
                          </tr>
                          <tr v-if="!datas.equity || datas.equity.length === 0">
                            <td colspan="3" class="text-center py-2 text-muted font-monospace">
                              {{ $t('No equity accounts recorded.') }}
                            </td>
                          </tr>
                        </tbody>
                        <tfoot class="border-top">
                          <tr class="font-monospace fw-semibold small">
                            <td colspan="2" class="text-muted">{{ $t('Total Equity') }}:</td>
                            <td class="text-end text-success">{{ $filter.formatBDT(totalEquity) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>

                  <!-- Liabilities & Equity Grand Total Footer -->
                  <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top font-monospace fw-bold">
                    <span class="text-uppercase text-dark">{{ $t('Total Liabilities & Equity') }}:</span>
                    <span class="text-success fs-6">{{ $filter.formatBDT(datas.total_liabilities_equity || 0) }}</span>
                  </div>
                </div>
              </div>
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
    </template>
  </index-page>
</template>

<script>
import axios from "axios";
import moment from "moment";

const tableColumns = [{ field: "status", title: "Status", align: "center" }];
const json_fields = {
  Title: "Balance Sheet",
};
const model = "balancesheet";

export default {
  data() {
    return {
      model: model,
      page_title: "Balance Sheet",
      reportDate: moment().format("D MMMM, YYYY"),
      json_fields: json_fields,
      search_data: {
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
        assets: [],
        liabilities: [],
        equity: [],
        total_assets: 0,
        total_liabilities_equity: 0,
      },
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
    asOfDate() {
      if (this.search_data.to_date) {
        return moment(this.search_data.to_date).format("D MMMM, YYYY");
      }
      return moment().format("D MMMM, YYYY");
    },

    dateRange() {
      if (this.search_data.from_date && this.search_data.to_date) {
        return (
          moment(this.search_data.from_date).format("D MMM, YYYY") +
          " to " +
          moment(this.search_data.to_date).format("D MMM, YYYY")
        );
      }
      return "Cumulative up to " + this.asOfDate;
    },

    totalLiabilities() {
      if (!this.datas.liabilities) return 0;
      return this.datas.liabilities.reduce((sum, item) => sum + (Number(item.balance) || 0), 0);
    },

    totalEquity() {
      if (!this.datas.equity) return 0;
      return this.datas.equity.reduce((sum, item) => sum + (Number(item.balance) || 0), 0);
    },

    variance() {
      const a = Number(this.datas.total_assets) || 0;
      const le = Number(this.datas.total_liabilities_equity) || 0;
      return Math.abs(a - le);
    },

    isBalanced() {
      return this.variance < 0.01;
    },
  },

  methods: {
    search() {
      this.getBalanceSheet();
    },

    getBalanceSheet() {
      axios
        .get(`report/balancesheet/`, { params: this.search_data })
        .then((res) => {
          this.datas = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    resetSearchData() {
      this.search_data.from_date = null;
      this.search_data.to_date = null;
      this.getBalanceSheet();
    },
  },

  created() {
    this.getBalanceSheet();
  },

  validators: {},
};
</script>

<style scoped>
.balancesheet-hero-banner {
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

@media print {
  .print-only-header {
    display: block !important;
  }
}
</style>
