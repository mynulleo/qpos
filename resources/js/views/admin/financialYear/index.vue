<template>
  <index-page :show_status="false">
    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Financial Year Title & ID -->
    <template v-slot:year_info="{ item }">
      <td class="year-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="fy-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div>
            <div class="fw-bold text-dark font-monospace" style="color: #112C47 !important; font-size: 13.5px;">
              {{ item.title }}
            </div>
            <div class="small text-muted font-monospace mt-0.5" style="font-size: 11px;">
              ID: #{{ item.id }}
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Date Duration -->
    <template v-slot:duration_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-1.5 font-monospace" style="font-size: 12px;">
          <span class="text-dark fw-semibold">{{ item.start_date || 'N/A' }}</span>
          <i class="fas fa-arrow-right text-muted small mx-1"></i>
          <span class="text-dark fw-semibold">{{ item.end_date || 'N/A' }}</span>
        </div>
      </td>
    </template>

    <!-- Column 3: Flags (Current / Closed) -->
    <template v-slot:flags_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-1.5 flex-wrap">
          <span
            v-if="item.is_current"
            class="badge rounded-pill px-2 py-0.5 font-monospace"
            style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 10.5px;"
          >
            <i class="fas fa-star text-warning me-1"></i>Current Active
          </span>
          <span
            class="badge rounded-pill px-2 py-0.5 font-monospace"
            :class="item.is_closed ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'"
            style="font-size: 10.5px;"
          >
            {{ item.is_closed ? 'Locked & Closed' : 'Open for Posting' }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:year_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          class="badge rounded-pill px-2.5 py-1 font-monospace"
          :class="item.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'"
          style="font-size: 11px;"
        >
          <i class="fas fa-circle me-1" style="font-size: 7px;"></i>
          {{ item.status === 'active' ? $t('Active') : $t('Inactive') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "financialYear";

const tableColumns = [
  { field: "year_info", title: "Financial Year Title" },
  { field: "duration_info", title: "Accounting Period Range" },
  { field: "flags_info", title: "Posting State" },
  { field: "year_status", title: "Status", align: "center" },
];

const json_fields = {
  "Title": "title",
  "Start Date": "start_date",
  "End Date": "end_date",
  "Is Current": "is_current",
  "Is Closed": "is_closed",
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
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
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
.year-info-cell {
  cursor: pointer;
}
.fy-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>