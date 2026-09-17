<template>
  <index-page :show_status="false">
    <!-- 📊 Modern KPI Statistics Cards Deck -->
    <template v-slot:header-summary>
      <div class="row g-2 mb-3 grn_kpi_deck">
        <!-- Card 1: Total GRNs -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_blue">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Total GRN Received</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.total_grns || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-primary bg-opacity-10 text-primary me-1">PO: {{ stats.po_count || 0 }}</span>
                  <span class="badge bg-info bg-opacity-10 text-info me-1">Sup: {{ stats.supplier_count || 0 }}</span>
                  <span class="badge bg-warning bg-opacity-10 text-dark">Dir: {{ stats.direct_count || 0 }}</span>
                </div>
              </div>
              <div class="kpi_icon_box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-clipboard-check fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Total Received Quantity -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_green">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Total Received Qty</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.total_received_qty || 0).toLocaleString() }} <span class="fs-6 fw-normal text-muted">Units</span>
                </div>
                <div class="small text-muted mt-1">
                  <i class="fas fa-arrow-down text-success me-1"></i> Inventory Stock In
                </div>
              </div>
              <div class="kpi_icon_box bg-success bg-opacity-10 text-success">
                <i class="fas fa-boxes fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Total Valuation Amount -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_purple">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Stock Valuation</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  ৳ {{ Number(stats.total_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
                <div class="small text-muted mt-1">
                  <i class="fas fa-shield-alt text-primary me-1"></i> Cumulative Purchase Value
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fas fa-money-bill-wave fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Direct Paid Settlement -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_amber">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Direct Settlement</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  ৳ {{ Number(stats.total_paid || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-warning bg-opacity-10 text-dark">
                    <i class="fas fa-bolt text-warning me-1"></i>{{ stats.direct_count || 0 }} Direct Purchases
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-cash-register fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🔍 Advanced & Professional Filter Controls -->
    <template v-slot:search-field>
      <!-- Row 1: Mode Type, Supplier, Warehouse, Status -->
      <v-select-container title="GRN Mode / Type" field="search_data.grn_type" col="3">
        <select v-model="search_data.grn_type" class="form-select" @change="onFilterChange">
          <option value="">-- All GRN Types --</option>
          <option value="po">1. PO Based (From Purchase Order)</option>
          <option value="supplier">2. Supplier Based (Manual GRN)</option>
          <option value="direct">3. Direct Purchase (Instant Pay)</option>
        </select>
      </v-select-container>

      <v-select-container title="Supplier" field="search_data.supplier_id" col="3">
        <v-select v-model="search_data.supplier_id" label="org_name" :reduce="(obj) => obj.id"
          :options="suppliersList" placeholder="-- Select Supplier --" :closeOnSelect="true"
          @update:modelValue="onFilterChange"></v-select>
      </v-select-container>

      <v-select-container title="Warehouse" field="search_data.warehouse_id" col="3">
        <v-select v-model="search_data.warehouse_id" label="name" :reduce="(obj) => obj.id"
          :options="warehousesList" placeholder="-- Select Warehouse --" :closeOnSelect="true"
          @update:modelValue="onFilterChange"></v-select>
      </v-select-container>

      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
        <select v-model="search_data.status" class="form-select form-select-sm" @change="onFilterChange">
          <option value="">-- All Status --</option>
          <option value="active">Active</option>
          <option value="deactive">Deactive</option>
        </select>
      </div>

      <!-- Row 2: Date Filters & Quick Presets -->
      <date-picker id="grn_from_date" v-model="search_data.from_date" field="search_data.from_date"
        title="From GRN Date" placeholder="From Date" col="3" :req="false" @change="onFilterChange"></date-picker>

      <date-picker id="grn_to_date" v-model="search_data.to_date" field="search_data.to_date"
        title="To GRN Date" placeholder="To Date" col="3" :req="false"
        :disablePastDates="search_data.from_date" @change="onFilterChange"></date-picker>

      <!-- Quick Date Preset Pills -->
      <div class="col-md-6 d-flex align-items-end mb-2">
        <div class="w-100">
          <label class="form-label small fw-semibold text-muted mb-1 d-block">Quick Date Presets</label>
          <div class="btn-group btn-group-sm w-100 flex-wrap" role="group">
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('today') }" @click="applyDatePreset('today')">Today</button>
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('yesterday') }" @click="applyDatePreset('yesterday')">Yesterday</button>
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('last7') }" @click="applyDatePreset('last7')">Last 7 Days</button>
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('thisMonth') }" @click="applyDatePreset('thisMonth')">This Month</button>
            <button type="button" class="btn btn-outline-danger" v-if="search_data.from_date || search_data.to_date" @click="applyDatePreset('clear')">
              <i class="fas fa-times me-1"></i> Clear Dates
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- 📋 Compact & Optimized Table Column Slots (Fits within viewport without horizontal scrolling) -->
    <template v-slot:grn_no="{ item }">
      <td>
        <div class="d-flex align-items-center gap-1">
          <router-link :to="{ name: 'grn.show', params: { id: item.id } }" class="fw-bold font-monospace text-primary text-decoration-none" title="View GRN Details">
            <i class="fas fa-file-invoice me-1 text-primary"></i>{{ item.grn_no }}
          </router-link>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-1 mt-1 small text-muted">
          <span><i class="far fa-calendar-alt me-1 text-muted"></i>{{ item.grn_date }}</span>
          <span v-if="item.challan_no" class="badge bg-light text-secondary border py-0 px-1 font-monospace">
            Ch: {{ item.challan_no }}
          </span>
        </div>
      </td>
    </template>

    <template v-slot:grn_type="{ item }">
      <td class="text-center">
        <span class="badge rounded-pill px-2 py-1 shadow-none" :class="getGrnTypeBadgeClass(item.grn_type)">
          <i :class="getGrnTypeIcon(item.grn_type)" class="me-1"></i>
          {{ formatGrnType(item.grn_type) }}
        </span>
      </td>
    </template>

    <template v-slot:purchase_id="{ item }">
      <td>
        <span v-if="item.purchase" class="badge bg-light text-dark border font-monospace px-2 py-1">
          <i class="fas fa-file-invoice-dollar me-1 text-primary"></i>{{ item.purchase.invoiceno }}
        </span>
        <span v-else-if="item.grn_type === 'direct'" class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1">
          <i class="fas fa-bolt me-1 text-warning"></i> Direct Purchase
        </span>
        <span v-else class="text-muted small fst-italic">
          <i class="fas fa-layer-group me-1"></i> Direct GRN
        </span>
      </td>
    </template>

    <template v-slot:supplier_id="{ item }">
      <td>
        <div v-if="item.supplier">
          <div class="fw-semibold text-dark">{{ item.supplier.org_name }}</div>
          <div v-if="item.supplier.mobile" class="text-muted small">
            <i class="fas fa-phone-alt me-1 text-secondary" style="font-size: 10px;"></i>{{ item.supplier.mobile }}
          </div>
        </div>
        <div v-else-if="item.grn_type === 'direct'">
          <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
            <i class="fas fa-wallet me-1"></i>{{ item.fund_account ? item.fund_account.account_name : 'Direct Cash / Fund' }}
          </span>
        </div>
        <span v-else class="text-muted small">N/A</span>
      </td>
    </template>

    <template v-slot:warehouse_id="{ item }">
      <td>
        <span v-if="item.warehouse" class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1">
          <i class="fas fa-warehouse me-1 text-primary"></i>{{ item.warehouse.name }}
        </span>
        <span v-else class="text-muted small">N/A</span>
      </td>
    </template>

    <template v-slot:total_qty="{ item }">
      <td class="text-center">
        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold font-monospace px-2 py-1 fs-7">
          {{ Number(item.total_qty || 0).toLocaleString() }} Units
        </span>
      </td>
    </template>

    <template v-slot:total_amount="{ item }">
      <td class="text-end">
        <div class="fw-bold font-monospace text-dark">
          <small class="text-muted fw-normal me-1">Total:</small>৳ {{ Number(item.total_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
        </div>
        <div class="small font-monospace mt-1">
          <span v-if="Number(item.paid_amount) > 0" class="text-success fw-semibold">
            <small class="text-muted fw-normal me-1">Paid:</small>৳ {{ Number(item.paid_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
          </span>
          <span v-else class="text-muted">
            <small class="text-muted fw-normal me-1">Paid:</small>৳ 0.00
          </span>
        </div>
      </td>
    </template>

    <template v-slot:status="{ item }">
      <td class="text-center">
        <span class="status approved" v-if="item.status === 'active' || item.status === 1">ACTIVE</span>
        <span class="status cancel" v-else>DEACTIVE</span>
      </td>
    </template>
  </index-page>
</template>

<script>
import moment from "moment";

const model = "grn";

const tableColumns = [
  { field: "grn_no", title: "GRN Details" },
  { field: "grn_type", title: "GRN Mode", align: "center" },
  { field: "purchase_id", title: "PO / Ref" },
  { field: "supplier_id", title: "Supplier / Party" },
  { field: "warehouse_id", title: "Warehouse" },
  { field: "total_qty", title: "Received Qty", align: "center" },
  { field: "total_amount", title: "Financials (Total / Paid)", align: "right" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "GRN No": "grn_no",
  "Challan No": "challan_no",
  "GRN Date": "grn_date",
  "Type": "grn_type",
  "PO Invoice": "purchase.invoiceno",
  "Supplier": "supplier.org_name",
  "Warehouse": "warehouse.name",
  "Received Qty": "total_qty",
  "Total Amount": "total_amount",
  "Paid Amount": "paid_amount",
  "Status": "status",
  "Remarks": "note"
};

export default {
  data() {
    const defaultFrom = this.$route.query.from_date !== undefined 
      ? this.$route.query.from_date 
      : moment().startOf("month").format("YYYY-MM-DD");
    const defaultTo = this.$route.query.to_date !== undefined 
      ? this.$route.query.to_date 
      : moment().endOf("month").format("YYYY-MM-DD");

    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      extraData: {
        suppliers: [],
        warehouses: [],
      },
      fields_name: {
        default: "Search All Fields",
        grn_no: "GRN No",
        challan_no: "Challan No",
        invoiceno: "PO Invoice No",
        supplier: "Supplier Name / Mobile",
        warehouse: "Warehouse Name / Code",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        grn_type: this.$route.query.grn_type ?? "",
        supplier_id: this.$route.query.supplier_id ? (isNaN(this.$route.query.supplier_id) ? this.$route.query.supplier_id : Number(this.$route.query.supplier_id)) : "",
        warehouse_id: this.$route.query.warehouse_id ? (isNaN(this.$route.query.warehouse_id) ? this.$route.query.warehouse_id : Number(this.$route.query.warehouse_id)) : "",
        from_date: defaultFrom,
        to_date: defaultTo,
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      localStats: {
        total_grns: 0,
        total_received_qty: 0,
        total_amount: 0,
        total_paid: 0,
        po_count: 0,
        supplier_count: 0,
        direct_count: 0,
      },
      active_preset: "",
    };
  },

  computed: {
    stats() {
      if (this.table && this.table.summary) {
        return this.table.summary;
      }
      return this.localStats;
    },
    suppliersList() {
      if (this.extraData && this.extraData.suppliers && this.extraData.suppliers.length > 0) {
        return this.extraData.suppliers;
      }
      return (this.$root.global && this.$root.global.suppliers) ? this.$root.global.suppliers : [];
    },
    warehousesList() {
      if (this.extraData && this.extraData.warehouses && this.extraData.warehouses.length > 0) {
        return this.extraData.warehouses;
      }
      return (this.$root.global && this.$root.global.warehouses) ? this.$root.global.warehouses : [];
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
    formatGrnType(type) {
      if (type === 'supplier') return 'Supplier Based';
      if (type === 'direct') return 'Direct Purchase';
      return 'PO Based';
    },

    getGrnTypeBadgeClass(type) {
      if (type === 'supplier') return 'bg-info bg-opacity-10 text-info border border-info border-opacity-25';
      if (type === 'direct') return 'bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50';
      return 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
    },

    getGrnTypeIcon(type) {
      if (type === 'supplier') return 'fas fa-truck-loading';
      if (type === 'direct') return 'fas fa-bolt';
      return 'fas fa-file-invoice-dollar';
    },

    cleanSearchData() {
      const cleanParams = {};
      for (const key in this.search_data) {
        const val = this.search_data[key];
        if (val !== '' && val !== null && val !== undefined) {
          cleanParams[key] = val;
        }
      }
      return cleanParams;
    },

    search() {
      const cleanParams = this.cleanSearchData();
      this.get_paginate(this.model, cleanParams);
      this.fetchStatistics(cleanParams);
    },

    onFilterChange() {
      this.search_data.page = 1;
      this.$root.tableSpinner = true;
      this.$router.push({
        query: this.cleanSearchData(),
      }).catch(() => {});
      this.search();
    },

    fetchStatistics(params = null) {
      const queryParams = params || this.cleanSearchData();
      axios.get('grn/statistics', { params: queryParams })
        .then((res) => {
          if (res.data) {
            this.localStats = res.data;
          }
        })
        .catch((err) => {
          console.error("Error fetching GRN statistics:", err);
        });
    },

    isDateActive(preset) {
      const f = this.search_data.from_date;
      const t = this.search_data.to_date;
      if (preset === 'clear') return !f && !t;
      if (preset === 'today') return f === moment().format('YYYY-MM-DD') && t === moment().format('YYYY-MM-DD');
      if (preset === 'yesterday') {
        const yStr = moment().subtract(1, 'days').format('YYYY-MM-DD');
        return f === yStr && t === yStr;
      }
      if (preset === 'last7') {
        return f === moment().subtract(6, 'days').format('YYYY-MM-DD') && t === moment().format('YYYY-MM-DD');
      }
      if (preset === 'thisMonth') {
        const start = moment().startOf('month').format('YYYY-MM-DD');
        const end = moment().endOf('month').format('YYYY-MM-DD');
        const today = moment().format('YYYY-MM-DD');
        return f === start && (t === end || t === today);
      }
      return false;
    },

    applyDatePreset(preset) {
      this.active_preset = preset;

      if (preset === 'today') {
        const todayStr = moment().format('YYYY-MM-DD');
        this.search_data.from_date = todayStr;
        this.search_data.to_date = todayStr;
      } else if (preset === 'yesterday') {
        const yStr = moment().subtract(1, 'days').format('YYYY-MM-DD');
        this.search_data.from_date = yStr;
        this.search_data.to_date = yStr;
      } else if (preset === 'last7') {
        this.search_data.from_date = moment().subtract(6, 'days').format('YYYY-MM-DD');
        this.search_data.to_date = moment().format('YYYY-MM-DD');
      } else if (preset === 'thisMonth') {
        this.search_data.from_date = moment().startOf('month').format('YYYY-MM-DD');
        this.search_data.to_date = moment().endOf('month').format('YYYY-MM-DD');
      } else if (preset === 'clear') {
        this.active_preset = "";
        this.search_data.from_date = "";
        this.search_data.to_date = "";
      }

      this.onFilterChange();
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.grn_type = "";
      this.search_data.supplier_id = "";
      this.search_data.warehouse_id = "";
      this.search_data.from_date = moment().startOf('month').format('YYYY-MM-DD');
      this.search_data.to_date = moment().endOf('month').format('YYYY-MM-DD');
      this.active_preset = "thisMonth";
      this.$router.push({
        query: this.cleanSearchData(),
      }).catch(() => {});
      this.search();
    },

    fetchDropdownOptions() {
      this.get_paginate("supplier", { allData: true }, "suppliers", false);
      this.get_paginate("warehouse", { allData: true }, "warehouses", false);
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `Goods Receive (GRN) Management`;
    this.fetchDropdownOptions();
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.grn_kpi_deck .kpi_card {
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background: #ffffff;
}
.grn_kpi_deck .kpi_card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
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
  letter-spacing: 0.5px;
  font-size: 11px;
}
.kpi_blue {
  border-left: 4px solid #3b82f6 !important;
}
.kpi_green {
  border-left: 4px solid #10b981 !important;
}
.kpi_purple {
  border-left: 4px solid #6366f1 !important;
}
.kpi_amber {
  border-left: 4px solid #f59e0b !important;
}
</style>
