<template>
  <index-page :show_status="false">
    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Title & ID -->
    <template v-slot:parameter_info="{ item }">
      <td class="parameter-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="param-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-sliders-h"></i>
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

    <!-- Column 2: Is Basic Type -->
    <template v-slot:type_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span
          class="badge rounded-pill px-2.5 py-1 font-monospace"
          :class="item.is_basic ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-light text-secondary border'"
          style="font-size: 11px;"
        >
          <i class="fas" :class="item.is_basic ? 'fa-check me-1' : 'fa-minus me-1'"></i>
          {{ item.is_basic ? 'Core Basic' : 'Allowance / Component' }}
        </span>
      </td>
    </template>

    <!-- Column 3: Percentage of Basic -->
    <template v-slot:percentage_info="{ item }">
      <td class="text-center font-monospace" @click="pushToViewRoute(item.id)">
        <span v-if="!item.is_basic && item.percentage_of_basic" class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
          {{ item.percentage_of_basic }}% of Basic
        </span>
        <span v-else class="text-muted small">
          —
        </span>
      </td>
    </template>

    <!-- Column 4: Sorting -->
    <template v-slot:sorting_info="{ item }">
      <td class="text-center font-monospace text-muted" @click="pushToViewRoute(item.id)">
        {{ item.sorting ?? '0' }}
      </td>
    </template>

    <!-- Column 5: Status -->
    <template v-slot:parameter_status="{ item }">
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
const model = "salaryParameter";

const tableColumns = [
  { field: "parameter_info", title: "Parameter Title" },
  { field: "type_info", title: "Parameter Type" },
  { field: "percentage_info", title: "Calculation Basis", align: "center" },
  { field: "sorting_info", title: "Sort Order", align: "center" },
  { field: "parameter_status", title: "Status", align: "center" },
];

const json_fields = {
  "Title": "title",
  "Is Basic": "is_basic",
  "Percentage of Basic": "percentage_of_basic",
  "Sorting": "sorting",
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
.parameter-info-cell {
  cursor: pointer;
}
.param-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>