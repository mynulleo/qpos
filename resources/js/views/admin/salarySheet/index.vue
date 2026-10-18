<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Month" field="search_data.month" col="3">
        <v-select
          v-model="search_data.month"
          label="name"
          :reduce="(obj) => obj.value"
          :options="$root.global.montharray"
          :placeholder="$t('-- All Months --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Year" field="search_data.year" col="3">
        <v-select
          v-model="search_data.year"
          label="name"
          :reduce="(obj) => obj.value"
          :options="$root.global.recentyears"
          :placeholder="$t('-- All Years --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfrominvoicedate"
        v-model="search_data.from_gen_date"
        field="search_data.from_gen_date"
        title="From Generated Date"
        placeholder="From Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoinvoicedate"
        v-model="search_data.to_gen_date"
        field="search_data.to_gen_date"
        title="To Generated Date"
        placeholder="To Date"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_gen_date"
      ></date-picker>
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Salary Sheet Title & Period -->
    <template v-slot:sheet_info="{ item }">
      <td class="sheet-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="sheet-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 36px; height: 36px; font-size: 14px; margin-right: 12px;"
          >
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important; font-size: 13.5px;">
              {{ item.title }}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span class="badge rounded-pill px-2 py-0.5 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 10.5px;">
                <i class="far fa-calendar-alt me-1"></i>{{ item.month }} - {{ item.year }}
              </span>
              <span class="small text-muted font-monospace" style="font-size: 11px;">
                Generated: {{ item.generated_date || 'N/A' }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Approval Status & Admin -->
    <template v-slot:approval_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div v-if="item.approved_by" class="d-flex flex-column gap-0.5">
          <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 rounded-pill font-monospace small align-self-start">
            <i class="fas fa-check-circle me-1"></i>Approved
          </span>
          <span class="small text-dark font-monospace fw-semibold mt-1">
            {{ item.approved_admin?.full_name || ('Admin #' + item.approved_by) }}
          </span>
          <span class="text-muted font-monospace" style="font-size: 10.5px;">
            {{ item.approved_date }}
          </span>
        </div>
        <div v-else>
          <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-0.5 rounded-pill font-monospace small">
            <i class="fas fa-clock me-1"></i>Pending Approval
          </span>
        </div>
      </td>
    </template>

    <!-- Column 3: Status -->
    <template v-slot:sheet_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          class="badge rounded-pill px-2.5 py-1 font-monospace"
          :class="item.status === 'active' || item.approved_by ? 'bg-success text-white' : 'bg-secondary text-white'"
          style="font-size: 11px;"
        >
          <i class="fas fa-circle me-1" style="font-size: 7px;"></i>
          {{ item.approved_by ? 'Approved' : (item.status === 'active' ? 'Active' : 'Draft') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "salarySheet";

const tableColumns = [
  { field: "sheet_info", title: "Payroll Title & Period" },
  { field: "approval_info", title: "Approval Status & Admin" },
  { field: "sheet_status", title: "Status", align: "center" },
];

const json_fields = {
  "Title": "title",
  "Month": "month",
  "Year": "year",
  "Generated Date": "generated_date",
  "Approved By": "approved_admin.full_name",
  "Approved Date": "approved_date",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", title: "Title" },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        month: this.$route.query.month ?? "",
        year: this.$route.query.year ?? "",
        from_gen_date: this.$route.query.from_gen_date ?? "",
        to_gen_date: this.$route.query.to_gen_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      }
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

  methods: {
    search() {
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.month = "";
      this.search_data.year = "";
      this.search_data.from_gen_date = "";
      this.search_data.to_gen_date = "";
      this.search();
    },

    pushToViewRoute(id) {
      if (!id) return;
      this.$router.push({ name: `${this.model}.show`, params: { id: id } });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.sheet-info-cell {
  cursor: pointer;
}
.sheet-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>