<template>
  <index-page :show_status="false">
    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Designation Title & ID -->
    <template v-slot:designation_info="{ item }">
      <td class="designation-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="desig-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-briefcase"></i>
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

    <!-- Column 2: Salary Breakdown Pills -->
    <template v-slot:salary_breakdown="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2 flex-wrap font-monospace" style="font-size: 11px;">
          <span class="badge bg-light text-dark border px-2 py-1">
            Basic: <strong>{{ $filter.formatBDT(item.basic) }}</strong>
          </span>
          <span class="badge bg-light text-dark border px-2 py-1">
            House: <strong>{{ $filter.formatBDT(item.house_rent) }}</strong>
          </span>
          <span class="badge bg-light text-dark border px-2 py-1">
            Medical: <strong>{{ $filter.formatBDT(item.medical) }}</strong>
          </span>
          <span v-if="item.other > 0" class="badge bg-light text-dark border px-2 py-1">
            Other: <strong>{{ $filter.formatBDT(item.other) }}</strong>
          </span>
        </div>
      </td>
    </template>

    <!-- Column 3: Total Salary -->
    <template v-slot:total_salary_info="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <span class="fw-bold font-monospace text-primary fs-6">
          {{ $filter.formatBDT(item.total_salary) }}
        </span>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:designation_status="{ item }">
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
const model = "designation";

const tableColumns = [
  { field: "designation_info", title: "Designation Title" },
  { field: "salary_breakdown", title: "Default Allowances Breakdown" },
  { field: "total_salary_info", title: "Total Scale", align: "right" },
  { field: "designation_status", title: "Status", align: "center" },
];

const json_fields = {
  "Title": "title",
  "Basic": "basic",
  "House Rent": "house_rent",
  "Medical": "medical",
  "Other": "other",
  "Total Salary": "total_salary",
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
.designation-info-cell {
  cursor: pointer;
}
.desig-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>