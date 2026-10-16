<template>
  <index-page :show_status="false">
    <!-- 📊 Executive KPI Statistics Cards Deck -->
    <template v-slot:header-summary>
      <div class="row g-2 mb-3 challan_kpi_deck">
        <!-- Card 1: Total Challans -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_blue">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Total Challans') }}</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.total_challans || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1 d-flex flex-wrap gap-1">
                  <span class="badge bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle me-1"></i>{{ $t('Active') }}: {{ stats.active_challans || 0 }}
                  </span>
                  <span class="badge bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-archive me-1"></i>{{ $t('Inactive') }}: {{ (stats.total_challans || 0) - (stats.active_challans || 0) }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(17, 44, 71, 0.1); color: #112C47;">
                <i class="fas fa-truck-ramp-box fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: This Month's Deliveries -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_cyan">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('This Month Dispatches') }}</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.this_month || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-info bg-opacity-10 text-info">
                    <i class="fas fa-calendar-check me-1"></i> {{ $t('Current Month Activity') }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box bg-info bg-opacity-10 text-info">
                <i class="fas fa-calendar-alt fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Total Dispatched Items Quantity -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_purple">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Total Dispatched Qty') }}</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.total_items || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-boxes me-1"></i> {{ $t('Units Delivered') }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fas fa-dolly fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Active Challans -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_green">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Active Shipments') }}</div>
                <div class="fs-4 fw-bolder text-success font-monospace mt-1">
                  {{ Number(stats.active_challans || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1">
                  <i class="fas fa-check-double text-success me-1"></i> {{ $t('Recorded & Dispatched') }}
                </div>
              </div>
              <div class="kpi_icon_box bg-success bg-opacity-10 text-success">
                <i class="fas fa-clipboard-check fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🔍 Advanced Search / Filter Controls (No Workorder) -->
    <template v-slot:search-field>
      <v-select-container title="Select Client" field="search_data.client_id" col="3">
        <v-select
          v-model="search_data.client_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="clients"
          :placeholder="$t('-- Select Client --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Receive / Acceptance Status') }}</label>
        <select v-model="search_data.receive_status" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Receive Statuses --') }}</option>
          <option value="pending">{{ $t('Pending Acceptance') }}</option>
          <option value="partial">{{ $t('Partially Received') }}</option>
          <option value="received">{{ $t('Fully Received') }}</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Invoice Status') }}</label>
        <select v-model="search_data.is_invoiced" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Invoicing --') }}</option>
          <option value="1">{{ $t('Invoiced (Locked)') }}</option>
          <option value="0">{{ $t('Uninvoiced') }}</option>
        </select>
      </div>

      <date-picker
        id="searchfromchallandate"
        v-model="search_data.from_challan_date"
        field="search_data.from_challan_date"
        title="From Challan Date"
        placeholder="From Date"
        col="2"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoChallandate"
        v-model="search_data.to_challan_date"
        field="search_data.to_challan_date"
        title="To Challan Date"
        placeholder="To Date"
        col="2"
        :req="false"
        :disablePastDates="search_data.from_challan_date"
      ></date-picker>

      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Status') }}</label>
        <select v-model="search_data.status" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Statuses --') }}</option>
          <option value="1">{{ $t('Active') }}</option>
          <option value="0">{{ $t('Inactive') }}</option>
        </select>
      </div>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Challan No & Date -->
    <template v-slot:challan_info="{ item }">
      <td class="challan-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2">
          <div class="challan-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white" style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px;">
            <i class="fas fa-file-invoice"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important;">
              {{ item.challan_no || ('CH-' + String(item.id).padStart(5, '0')) }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-1">
              <i class="far fa-calendar-alt text-secondary" style="font-size: 11px;"></i>
              <span>{{ item.challan_date || 'N/A' }}</span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Client Info -->
    <template v-slot:client_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="fw-bold text-dark client-name">
          {{ (item.client && item.client.org_name) ? item.client.org_name : (item.client ? item.client.name : $t('Direct Customer')) }}
        </div>
        <div class="small text-muted" v-if="item.client && item.client.name && item.client.org_name && item.client.org_name !== item.client.name">
          <i class="fas fa-user text-secondary me-1" style="font-size: 10px;"></i>{{ item.client.name }}
        </div>
        <div class="small text-muted" v-if="item.client && item.client.mobile">
          <i class="fas fa-phone-alt text-success me-1" style="font-size: 10px;"></i>{{ item.client.mobile }}
        </div>
      </td>
    </template>

    <!-- Column 3: Delivery & Logistics -->
    <template v-slot:logistics_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <!-- Location -->
        <div v-if="item.delivery_location" class="small text-dark fw-semibold text-truncate d-flex align-items-center gap-1" style="max-width: 200px;" :title="item.delivery_location">
          <i class="fas fa-map-marker-alt text-danger flex-shrink-0" style="font-size: 11px;"></i>
          <span class="text-truncate">{{ item.delivery_location }}</span>
        </div>
        <!-- Receiver -->
        <div v-if="item.receive_by" class="small text-muted mt-0.5">
          <i class="fas fa-user-check text-info me-1" style="font-size: 10px;"></i>
          <span class="text-dark">{{ item.receive_by }}</span>
          <span v-if="item.receive_by_mobile" class="ms-1 font-monospace">({{ item.receive_by_mobile }})</span>
        </div>
        <!-- Delivered by -->
        <div v-if="item.delivery_by" class="small text-muted mt-0.5" style="font-size: 11px;">
          <i class="fas fa-truck text-primary me-1" style="font-size: 10px;"></i>
          <span>{{ item.delivery_by }}</span>
        </div>
        <div v-if="!item.delivery_location && !item.receive_by && !item.delivery_by" class="text-muted small fst-italic">--</div>
      </td>
    </template>

    <!-- Column 4: Dispatched Items Quantity -->
    <template v-slot:items_summary="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <div class="d-inline-flex flex-column align-items-center">
          <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 fw-bold">
            <i class="fas fa-boxes text-primary me-1"></i>
            {{ item.challan_details_count !== undefined ? item.challan_details_count : (item.challan_details ? item.challan_details.length : 0) }} {{ $t('Items') }}
          </span>
          <span class="small text-muted mt-0.5 font-monospace" v-if="calculateTotalQty(item) > 0">
            {{ $t('Total Qty') }}: <strong>{{ calculateTotalQty(item) }}</strong>
          </span>
        </div>
      </td>
    </template>

    <!-- Column 5: Fulfillment & Invoice Status -->
    <template v-slot:fulfillment_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <div class="d-flex flex-column align-items-center gap-1">
          <!-- Receive Status Badge -->
          <span
            v-if="item.receive_status === 'received'"
            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2 font-monospace"
          >
            <i class="fas fa-circle-check me-1"></i>{{ $t('Received') }}
          </span>
          <span
            v-else-if="item.receive_status === 'partial'"
            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 py-1 px-2 font-monospace"
          >
            <i class="fas fa-hourglass-half me-1"></i>{{ $t('Partial Received') }}
          </span>
          <span
            v-else
            class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2 font-monospace"
          >
            <i class="fas fa-clock me-1"></i>{{ $t('Pending Accept') }}
          </span>

          <!-- Invoiced Badge -->
          <router-link
            v-if="item.is_invoiced || item.invoice_id"
            :to="{ name: 'invoice.show', params: { id: (item.invoice_id || (item.invoice && item.invoice.id)) } }"
            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-1 px-2 text-decoration-none font-monospace d-inline-flex align-items-center gap-1"
            @click.stop
          >
            <i class="fas fa-file-invoice-dollar"></i>
            <span>{{ item.invoice ? ('#' + item.invoice.invoice_no) : $t('Invoiced') }}</span>
          </router-link>
          <span
            v-else
            class="badge bg-light text-muted border py-1 px-2 font-monospace"
          >
            <i class="far fa-circle me-1"></i>{{ $t('Uninvoiced') }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 6: Status -->
    <template v-slot:status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          class="badge px-2.5 py-1 text-uppercase fw-semibold"
          :class="(item.status == 1 || item.status === 'active' || item.status === 'Active') ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25'"
        >
          <i :class="(item.status == 1 || item.status === 'active' || item.status === 'Active') ? 'fas fa-check-circle me-1' : 'fas fa-pause-circle me-1'"></i>
          {{ (item.status == 1 || item.status === 'active' || item.status === 'Active') ? $t('ACTIVE') : $t('INACTIVE') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "challan";

const tableColumns = [
  { field: "challan_info", title: "Challan No & Date" },
  { field: "client_info", title: "Client / Organization" },
  { field: "logistics_info", title: "Delivery & Logistics" },
  { field: "items_summary", title: "Items Summary", align: "center" },
  { field: "fulfillment_status", title: "Fulfillment & Invoice", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Challan No": "challan_no",
  "Challan Date": "challan_date",
  "Client": "client.org_name",
  "Delivery Location": "delivery_location",
  "Receive By": "receive_by",
  "Receive By Mobile": "receive_by_mobile",
  "Delivery By": "delivery_by",
  "Delivery By Mobile": "delivery_by_mobile",
  "Receive Status": "receive_status",
  "Invoiced": "is_invoiced",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "Challan Management",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        challan_no: "Challan No",
        receive_by: "Receive By",
        receive_by_mobile: "Receiver Mobile",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        client_id: this.$route.query.client_id ?? "",
        receive_status: this.$route.query.receive_status ?? "",
        is_invoiced: this.$route.query.is_invoiced ?? "",
        from_challan_date: this.$route.query.from_challan_date ?? "",
        to_challan_date: this.$route.query.to_challan_date ?? "",
        status: this.$route.query.status ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
        summary: null,
      },
      localStats: {
        total_challans: 0,
        active_challans: 0,
        this_month: 0,
        total_items: 0,
      },
      clients: [],
    };
  },

  computed: {
    stats() {
      if (this.table && this.table.summary) {
        return this.table.summary;
      }
      return this.localStats;
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
      const cleanParams = {};
      for (const key in this.search_data) {
        const val = this.search_data[key];
        if (val !== "" && val !== null && val !== undefined) {
          cleanParams[key] = val;
        }
      }
      this.get_paginate(this.model, cleanParams);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.client_id = "";
      this.search_data.receive_status = "";
      this.search_data.is_invoiced = "";
      this.search_data.from_challan_date = "";
      this.search_data.to_challan_date = "";
      this.search_data.status = "";
    },

    getClients() {
      axios
        .get("client?allData=true")
        .then((response) => {
          this.clients = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },

    pushToViewRoute(id) {
      if (this.$root.checkPermission(this.model + ".show")) {
        this.$router.push({
          name: this.model + ".show",
          params: { id: id },
          query: { page: this.$route.query.page },
        });
      }
    },

    calculateTotalQty(item) {
      if (!item.challan_details || !Array.isArray(item.challan_details)) return 0;
      return item.challan_details.reduce((sum, d) => sum + Number(d.qty || 0), 0);
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} Management`;
    this.search();
    this.getClients();
  },

  validators: {},
};
</script>

<style scoped>
.challan_kpi_deck .kpi_card {
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background: #ffffff;
  border-left: 4px solid transparent !important;
}

.challan_kpi_deck .kpi_card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07) !important;
}

.challan_kpi_deck .kpi_blue {
  border-left-color: #112C47 !important;
}

.challan_kpi_deck .kpi_cyan {
  border-left-color: #06b6d4 !important;
}

.challan_kpi_deck .kpi_purple {
  border-left-color: #6366f1 !important;
}

.challan_kpi_deck .kpi_green {
  border-left-color: #10b981 !important;
}

.kpi_icon_box {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.tracking-wider {
  letter-spacing: 0.05em;
  font-size: 11px;
}

.challan-info-cell {
  cursor: pointer;
}

.challan-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>