<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Module" field="search_data.module" col="3">
        <v-select
          v-model="search_data.module"
          label="name"
          :reduce="(obj) => obj.value"
          :options="$root.global.modules"
          :placeholder="$t('-- All Modules --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Unit Title & ID -->
    <template v-slot:unit_info="{ item }">
      <td class="unit-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="unit-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-balance-scale"></i>
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

    <!-- Column 2: Module Name -->
    <template v-slot:module_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 11px;">
          <i class="fas fa-cube me-1.5"></i>
          {{ item.module || 'General' }}
        </span>
      </td>
    </template>

    <!-- Column 3: Status -->
    <template v-slot:unit_status="{ item }">
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
const model = "unit";

const tableColumns = [
  { field: "unit_info", title: "Unit Measurement Title" },
  { field: "module_info", title: "Module" },
  { field: "unit_status", title: "Status", align: "center" },
];

const json_fields = {
  Module: "module",
  Title: "title",
  Status: "status",
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
        module: this.$route.query.module ?? "",
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
      this.search_data.module = "";
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
.unit-info-cell {
  cursor: pointer;
}
.unit-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>