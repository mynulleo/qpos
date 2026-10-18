<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <Input
        v-model="search_data.adjustment_no"
        field="search_data.adjustment_no"
        title="Adjustment No"
        placeholder="e.g. ADJ-..."
        col="3"
      />

      <v-select-container title="Adjustment Type" field="search_data.adjustment_type" col="3">
        <v-select
          v-model="search_data.adjustment_type"
          :options="adjustmentTypes"
          placeholder="-- Select Type --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Conducted By (Employee)" field="search_data.conducted_by" col="3">
        <v-select
          v-model="search_data.conducted_by"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="$root.global.employees"
          placeholder="-- Select Employee --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Filter By Item" field="search_data.item_id" col="3">
        <v-select
          v-model="search_data.item_id"
          label="title"
          :reduce="(obj) => obj.id"
          :options="items"
          placeholder="-- Select Item --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfromadjdate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date"
        placeholder="From Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoadjdate"
        v-model="search_data.to_date"
        field="search_data.to_date"
        title="To Date"
        placeholder="To Date"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_date"
      ></date-picker>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Adjustment Ref & Date -->
    <template v-slot:adjustment_info="{ item }">
      <td class="adjustment-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div class="adj-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3" style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;">
            <i class="fas fa-sliders-h"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important;">
              {{ item.adjustment_no || ('ADJ-' + String(item.id).padStart(5, '0')) }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span><i class="far fa-calendar-alt text-secondary me-2" style="font-size: 11px;"></i>{{ item.adjustment_date || 'N/A' }}</span>
              <span v-if="item.branch" class="badge bg-light text-dark border py-0 px-1.5" style="font-size: 10px;">
                <i class="fas fa-code-branch me-1 text-primary"></i>{{ item.branch.title }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Conducted By & Type -->
    <template v-slot:conducted_by_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="fw-bold text-dark">
          <i class="fas fa-user-check text-secondary me-2" style="font-size: 11px;"></i>
          {{ item.conducted_by?.full_name || item.conducted_by?.name || $t('Store Manager') }}
        </div>
        <div class="small text-muted mt-0.5 d-flex align-items-center gap-1 flex-wrap">
          <span class="badge bg-light text-dark border py-0 px-1.5" style="font-size: 10px;">
            <i class="fas fa-tag me-1 text-info"></i>{{ item.adjustment_type || 'Reconciliation' }}
          </span>
          <span v-if="item.reason" class="text-truncate text-secondary" style="max-width: 180px;" :title="item.reason">
            - {{ item.reason }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 3: Adjusted Items Summary -->
    <template v-slot:items_summary="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <div class="d-inline-flex flex-column align-items-center">
          <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 fw-bold">
            <i class="fas fa-boxes text-primary me-1"></i>
            {{ item.total_items || (item.details ? item.details.length : 0) }} {{ $t('Lines') }}
          </span>
          <span class="small text-muted mt-0.5 font-monospace">
            {{ $t('Qty') }}: <strong class="text-dark">{{ item.total_adjusted_qty || 0 }}</strong>
          </span>
        </div>
      </td>
    </template>

    <!-- Column 4: Net Valuation Impact -->
    <template v-slot:total_valuation="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <div class="fw-bold font-monospace text-dark fs-6">
          ৳ {{ formatNum(item.total_amount || 0) }}
        </div>
        <div class="small text-muted" style="font-size: 11px;">
          <i class="fas fa-coins text-warning me-1"></i>{{ $t('Inventory Value') }}
        </div>
      </td>
    </template>

    <!-- Column 5: Status -->
    <template v-slot:adjustment_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          v-if="item.status === 'active' || item.status == 1"
          class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2.5 font-monospace"
        >
          <i class="fas fa-check-circle me-1"></i>{{ $t('Active') }}
        </span>
        <span
          v-else
          class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2.5 font-monospace"
        >
          <i class="fas fa-archive me-1"></i>{{ $t('Inactive') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "stockAdjustment";

const tableColumns = [
  { field: "adjustment_info", title: "Adjustment Ref & Date" },
  { field: "conducted_by_info", title: "Conducted By & Type" },
  { field: "items_summary", title: "Adjusted Items", align: "center" },
  { field: "total_valuation", title: "Net Valuation", align: "right" },
  { field: "adjustment_status", title: "Status", align: "center" },
];

const json_fields = {
  "Adjustment No": "adjustment_no",
  "Adjustment Date": "adjustment_date",
  "Adjustment Type": "adjustment_type",
  "Conducted By": "conducted_by.full_name",
  "Total Items": "total_items",
  "Adjusted Qty": "total_adjusted_qty",
  "Total Amount": "total_amount",
  "Status": "status",
};

export default {
  data() {
    return {
      page_title: "Stock Adjustment List",
      model: model,
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        adjustment_no: "Adjustment No",
        reason: "Reason",
        remarks: "Remarks",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        adjustment_no: this.$route.query.adjustment_no ?? "",
        adjustment_type: this.$route.query.adjustment_type ?? "",
        conducted_by: this.$route.query.conducted_by ?? "",
        item_id: this.$route.query.item_id ?? "",
        from_date: this.$route.query.from_date ?? "",
        to_date: this.$route.query.to_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      items: [],
      adjustmentTypes: ["Physical Count", "Damage Correction", "Reconciliation", "Other"],
    };
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
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    search() {
      const cleanParams = {};
      for (const key in this.search_data) {
        const val = this.search_data[key];
        if (val !== "" && val !== null && val !== undefined) {
          cleanParams[key] = val;
        }
      }

      this.$router.push({
        name: this.model + ".index",
        query: cleanParams,
      }).catch(() => {});

      this.get_paginate(this.model, cleanParams);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.adjustment_no = "";
      this.search_data.adjustment_type = "";
      this.search_data.conducted_by = "";
      this.search_data.item_id = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
    },

    getItems() {
      axios.get("item?allData=true")
        .then((response) => {
          this.items = response.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.setBreadcrumbs(this.model, "index");
    this.search();
    this.getItems();
  },

  validators: {},
};
</script>

<style scoped>
.adjustment-info-cell {
  cursor: pointer;
}
</style>
