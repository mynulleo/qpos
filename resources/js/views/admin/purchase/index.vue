<template>
  <index-page :show_status="false">
    <!-- 📊 Modern KPI Statistics Cards Deck -->
    <template v-slot:header-summary>
      <div class="row g-2 mb-3 purchase_kpi_deck">
        <!-- Card 1: Total Purchase Orders -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_blue">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Total Purchase Orders</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.total_pos || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1 d-flex flex-wrap gap-1">
                  <span class="badge bg-success bg-opacity-10 text-success" title="Goods Received">
                    <i class="fas fa-check-circle me-1"></i>Rec: {{ stats.received_count || 0 }}
                  </span>
                  <span class="badge bg-warning bg-opacity-10 text-dark" title="Partially Received">
                    <i class="fas fa-adjust me-1 text-warning"></i>Part: {{ stats.partial_count || 0 }}
                  </span>
                  <span class="badge bg-info bg-opacity-10 text-info" title="Pending GRN">
                    <i class="fas fa-clock me-1"></i>Pend: {{ stats.pending_count || 0 }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(17, 44, 71, 0.1); color: #112C47;">
                <i class="fas fa-file-invoice-dollar fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Total Purchase Valuation -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_green">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Purchase Valuation</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  ৳ {{ formatNumber(stats.total_amount) }}
                </div>
                <div class="small text-muted mt-1">
                  <i class="fas fa-coins text-success me-1"></i> Net Order Invoiced Value
                </div>
              </div>
              <div class="kpi_icon_box bg-success bg-opacity-10 text-success">
                <i class="fas fa-money-bill-wave fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Total Supplier Discount -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_amber">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">Total Discounts</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  ৳ {{ formatNumber(stats.total_discount) }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-warning bg-opacity-10 text-dark">
                    <i class="fas fa-tags text-warning me-1"></i> Vendor Trade Discounts
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-percentage fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Total Tax / VAT -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_purple">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">VAT / Tax Amount</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  ৳ {{ formatNumber(stats.total_tax) }}
                </div>
                <div class="small text-muted mt-1">
                  <i class="fas fa-receipt text-primary me-1"></i> Input Tax Breakdown
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fas fa-receipt fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🔍 Advanced & Professional Filter Controls -->
    <template v-slot:search-field>
      <!-- Row 1: Supplier, Category, Item, Receive Status, Status -->
      <v-select-container title="Supplier" field="search_data.supplier_id" col="3">
        <v-select v-model="search_data.supplier_id" label="org_name" :reduce="(obj) => obj.id"
          :options="suppliersList" placeholder="-- Select Supplier --" :closeOnSelect="true"
          @update:modelValue="onFilterChange"></v-select>
      </v-select-container>

      <v-select-container title="Category" field="search_data.category_id" col="3">
        <v-select v-model="search_data.category_id" label="title" :reduce="(obj) => obj.id"
          :options="categories" placeholder="-- Select Category --" :closeOnSelect="true"
          @update:modelValue="onFilterChange"></v-select>
      </v-select-container>

      <v-select-container title="Product / Item" field="search_data.item_id" col="3">
        <v-select v-model="search_data.item_id" label="title" :reduce="(obj) => obj.id"
          :options="filteredItems" placeholder="-- Select Item --" :closeOnSelect="true"
          @update:modelValue="onFilterChange">
          <template #option="option">
            <div>
              <span>{{ option.title }}</span>
              <small class="text-muted d-block" v-if="option.barcode">Barcode: {{ option.barcode }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">Receive Status (GRN)</label>
        <select v-model="search_data.receive_status" class="form-select form-select-sm" @change="onFilterChange">
          <option value="">-- All Receive Status --</option>
          <option value="Pending">Pending (Awaiting GRN)</option>
          <option value="Partial">Partial (Partially Received)</option>
          <option value="Received">Received (Goods Received)</option>
        </select>
      </div>

      <!-- Row 2: Date Filters & Quick Presets & Status -->
      <date-picker id="purchase_from_date" v-model="search_data.from_date" field="search_data.from_date"
        title="From PO Date" placeholder="From Date" col="3" :req="false" @change="onFilterChange"></date-picker>

      <date-picker id="purchase_to_date" v-model="search_data.to_date" field="search_data.to_date"
        title="To PO Date" placeholder="To Date" col="3" :req="false"
        :disablePastDates="search_data.from_date" @change="onFilterChange"></date-picker>

      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted mb-1">Record Status</label>
        <select v-model="search_data.status" class="form-select form-select-sm" @change="onFilterChange">
          <option value="">-- All Status --</option>
          <option value="active">Active</option>
          <option value="deactive">Deactive</option>
        </select>
      </div>

      <!-- Quick Date Preset Pills -->
      <div class="col-md-4 d-flex align-items-end mb-2">
        <div class="w-100">
          <label class="form-label small fw-semibold text-muted mb-1 d-block">Quick Date Presets</label>
          <div class="btn-group btn-group-sm w-100 flex-wrap" role="group">
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('today') }" @click="applyDatePreset('today')">Today</button>
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('yesterday') }" @click="applyDatePreset('yesterday')">Yesterday</button>
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('last7') }" @click="applyDatePreset('last7')">Last 7 Days</button>
            <button type="button" class="btn btn-outline-secondary" :class="{ 'active': isDateActive('thisMonth') }" @click="applyDatePreset('thisMonth')">This Month</button>
            <button type="button" class="btn btn-outline-danger" v-if="search_data.from_date || search_data.to_date" @click="applyDatePreset('clear')">
              <i class="fas fa-times me-1"></i> Clear
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- 📋 Compact & Optimized Table Column Slots -->
    <template v-slot:invoiceno="{ item }">
      <td>
        <div class="d-flex align-items-center gap-1">
          <router-link :to="{ name: 'purchase.show', params: { id: item.id } }" class="fw-bold font-monospace text-primary text-decoration-none" title="View Purchase Details">
            <i class="fas fa-file-invoice me-1 text-primary"></i>{{ item.invoiceno }}
          </router-link>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-1 mt-1 small text-muted font-monospace" style="font-size: 11px;">
          <span><i class="far fa-clock me-1 text-muted"></i>{{ item.created_at ? $filter.enFormat(item.created_at) : '' }}</span>
        </div>
      </td>
    </template>

    <template v-slot:purchase_date="{ item }">
      <td>
        <div class="d-flex align-items-center gap-1 font-monospace text-dark">
          <i class="far fa-calendar-alt text-secondary me-1"></i>{{ item.purchase_date }}
        </div>
      </td>
    </template>

    <template v-slot:supplier_id="{ item }">
      <td>
        <div v-if="item.supplier">
          <div class="fw-semibold text-dark">{{ item.supplier.org_name }}</div>
          <div v-if="item.supplier.mobile" class="text-muted small font-monospace">
            <i class="fas fa-phone-alt me-1 text-secondary" style="font-size: 10px;"></i>{{ item.supplier.mobile }}
          </div>
        </div>
        <span v-else class="text-muted small">N/A</span>
      </td>
    </template>

    <template v-slot:total_amount="{ item }">
      <td class="text-end">
        <div class="fw-bold font-monospace text-dark">
          <small class="text-muted fw-normal me-1">Net:</small>৳ {{ formatNumber(item.total_amount) }}
        </div>
        <div class="small font-monospace mt-1 text-muted" style="font-size: 11px;">
          <span class="me-2" title="Gross Subtotal">Sub: ৳ {{ formatNumber(item.amount) }}</span>
          <span v-if="Number(item.discount) > 0" class="text-danger me-2" title="Discount">-৳ {{ formatNumber(item.discount) }}</span>
          <span v-if="Number(item.tax) > 0" class="text-primary" title="Tax">+৳ {{ formatNumber(item.tax) }}</span>
        </div>
      </td>
    </template>

    <template v-slot:receive_status="{ item }">
      <td class="text-center">
        <span class="badge rounded-pill px-2 py-1 shadow-none" :class="getReceiveBadgeClass(item.receive_status)">
          <i :class="getReceiveIcon(item.receive_status)" class="me-1"></i>
          {{ item.receive_status || 'Pending' }}
        </span>
      </td>
    </template>

    <template v-slot:grn_info="{ item }">
      <td class="text-center">
        <template v-if="item.grns_count > 0 || (item.grns && item.grns.length > 0)">
          <router-link
            :to="{ name: 'grn.index', query: { purchase_id: item.id } }"
            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 text-decoration-none d-inline-flex align-items-center gap-1 font-monospace"
            title="View Goods Receive Notes"
          >
            <i class="fas fa-boxes"></i>
            <span>{{ item.grns_count || (item.grns ? item.grns.length : 0) }} GRN(s)</span>
          </router-link>
        </template>
        <template v-else>
          <router-link
            :to="{ name: 'grn.create', query: { purchase_id: item.id } }"
            class="btn btn-xs btn-outline-success shadow-none font-monospace py-0 px-2 d-inline-flex align-items-center gap-1"
            title="Receive Goods against this PO"
          >
            <i class="fas fa-plus"></i> Receive GRN
          </router-link>
        </template>
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

const model = "purchase";

const tableColumns = [
  { field: "invoiceno", title: "Invoice / Bill No" },
  { field: "purchase_date", title: "PO Date" },
  { field: "supplier_id", title: "Supplier / Party" },
  { field: "total_amount", title: "Order Financials", align: "right" },
  { field: "receive_status", title: "Receive Status", align: "center" },
  { field: "grn_info", title: "GRN Link", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Invoice No": "invoiceno",
  "Purchase Date": "purchase_date",
  "Supplier": "supplier.org_name",
  "Sub Total": "amount",
  "Discount": "discount",
  "Tax": "tax",
  "Total Amount": "total_amount",
  "Receive Status": "receive_status",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: {
        default: "Search All Fields",
        invoiceno: "Invoice No",
        supplier: "Supplier Name / Mobile",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        receive_status: this.$route.query.receive_status ?? "",
        supplier_id: this.$route.query.supplier_id ? (isNaN(this.$route.query.supplier_id) ? this.$route.query.supplier_id : Number(this.$route.query.supplier_id)) : "",
        category_id: this.$route.query.category_id ? (isNaN(this.$route.query.category_id) ? this.$route.query.category_id : Number(this.$route.query.category_id)) : "",
        item_id: this.$route.query.item_id ? (isNaN(this.$route.query.item_id) ? this.$route.query.item_id : Number(this.$route.query.item_id)) : "",
        from_date: this.$route.query.from_date ?? "",
        to_date: this.$route.query.to_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      localStats: {
        total_pos: 0,
        total_amount: 0,
        total_discount: 0,
        total_tax: 0,
        pending_count: 0,
        partial_count: 0,
        received_count: 0,
      },
      categories: [],
      allItems: [],
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

    filteredItems() {
      if (this.search_data.category_id) {
        return this.allItems.filter(item => item.category_id == this.search_data.category_id);
      }
      return this.allItems;
    },

    suppliersList() {
      return (this.$root.global && this.$root.global.suppliers) ? this.$root.global.suppliers : [];
    },
  },

  watch: {
    'search_data.category_id'(newVal, oldVal) {
      if (oldVal && newVal !== oldVal) {
        this.search_data.item_id = "";
      }
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

    onFilterChange() {
      this.search_data.page = 1;
      this.$root.tableSpinner = true;
      this.updateQueryParams();
    },

    updateQueryParams() {
      const cleanParams = {};
      for (const key in this.search_data) {
        const val = this.search_data[key];
        if (val !== "" && val !== null && val !== undefined) {
          cleanParams[key] = val;
        }
      }
      this.$router.push({
        query: cleanParams,
      }).catch(() => {});

      this.search();
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.receive_status = "";
      this.search_data.supplier_id = "";
      this.search_data.category_id = "";
      this.search_data.item_id = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
      this.active_preset = "";
    },

    applyDatePreset(preset) {
      this.active_preset = preset;
      if (preset === "today") {
        this.search_data.from_date = moment().format("YYYY-MM-DD");
        this.search_data.to_date = moment().format("YYYY-MM-DD");
      } else if (preset === "yesterday") {
        this.search_data.from_date = moment().subtract(1, "days").format("YYYY-MM-DD");
        this.search_data.to_date = moment().subtract(1, "days").format("YYYY-MM-DD");
      } else if (preset === "last7") {
        this.search_data.from_date = moment().subtract(6, "days").format("YYYY-MM-DD");
        this.search_data.to_date = moment().format("YYYY-MM-DD");
      } else if (preset === "thisMonth") {
        this.search_data.from_date = moment().startOf("month").format("YYYY-MM-DD");
        this.search_data.to_date = moment().endOf("month").format("YYYY-MM-DD");
      } else if (preset === "clear") {
        this.search_data.from_date = "";
        this.search_data.to_date = "";
        this.active_preset = "";
      }
      this.onFilterChange();
    },

    isDateActive(preset) {
      if (this.active_preset === preset) return true;
      if (preset === "today") {
        return this.search_data.from_date === moment().format("YYYY-MM-DD") &&
               this.search_data.to_date === moment().format("YYYY-MM-DD");
      }
      if (preset === "yesterday") {
        const y = moment().subtract(1, "days").format("YYYY-MM-DD");
        return this.search_data.from_date === y && this.search_data.to_date === y;
      }
      if (preset === "last7") {
        const f = moment().subtract(6, "days").format("YYYY-MM-DD");
        const t = moment().format("YYYY-MM-DD");
        return this.search_data.from_date === f && this.search_data.to_date === t;
      }
      if (preset === "thisMonth") {
        const f = moment().startOf("month").format("YYYY-MM-DD");
        const t = moment().endOf("month").format("YYYY-MM-DD");
        return this.search_data.from_date === f && this.search_data.to_date === t;
      }
      return false;
    },

    formatNumber(val) {
      if (!val && val !== 0) return "0.00";
      return Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    getReceiveBadgeClass(status) {
      if (status === "Received") return "bg-success bg-opacity-10 text-success border border-success border-opacity-25";
      if (status === "Partial") return "bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25";
      return "bg-info bg-opacity-10 text-info border border-info border-opacity-25";
    },

    getReceiveIcon(status) {
      if (status === "Received") return "fas fa-check-circle";
      if (status === "Partial") return "fas fa-adjust text-warning";
      return "fas fa-clock";
    },

    getCategories() {
      axios.get("getcategories/Item").then((response) => {
        this.categories = response.data || [];
      });
    },

    getItems() {
      axios.get("item?allData=true").then((response) => {
        this.allItems = response.data || [];
      });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
    this.getCategories();
    this.getItems();
  },

  validators: {},
};
</script>

<style scoped>
.purchase_kpi_deck .kpi_card {
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background: #ffffff;
  border-left: 4px solid transparent !important;
}

.purchase_kpi_deck .kpi_card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07) !important;
}

.purchase_kpi_deck .kpi_blue {
  border-left-color: #112C47 !important;
}

.purchase_kpi_deck .kpi_green {
  border-left-color: #10b981 !important;
}

.purchase_kpi_deck .kpi_amber {
  border-left-color: #f59e0b !important;
}

.purchase_kpi_deck .kpi_purple {
  border-left-color: #6366f1 !important;
}

.kpi_icon_box {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

.btn-xs {
  padding: 0.15rem 0.4rem;
  font-size: 0.75rem;
  border-radius: 4px;
}
</style>