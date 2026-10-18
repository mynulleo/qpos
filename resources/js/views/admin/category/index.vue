<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <StatusDropDown></StatusDropDown>
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Category Title & ID -->
    <template v-slot:category_info="{ item }">
      <td class="category-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="cat-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-tags"></i>
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

    <!-- Column 2: Module Name Badge -->
    <template v-slot:module_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 11px;">
          <i class="fas fa-cube me-1.5"></i>
          {{ item.module_name || 'General' }}
        </span>
      </td>
    </template>

    <!-- Column 3: Sorting Order -->
    <template v-slot:sorting_info="{ item }">
      <td class="text-center font-monospace text-muted" @click="pushToViewRoute(item.id)">
        {{ item.sorting ?? 0 }}
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:category_status="{ item }">
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
const model = "category";

const tableColumns = [
  { field: "category_info", title: "Category Title" },
  { field: "module_info", title: "Associated Module" },
  { field: "sorting_info", title: "Sort Order", align: "center" },
  { field: "category_status", title: "Status", align: "center" },
];

const json_fields = {
  "Module Name": "module_name",
  "Title": "title",
  "Sorting": "sorting",
  "Status": "status",
};

export default {
  data() {
    return {
      page_title: "Category List",
      model: model,
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        title: "Title",
        module_name: "Module",
      },
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
        links: [],
      },
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
    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search();
    },

    search() {
      this.get_paginate(this.model, this.search_data);
    },

    pushToViewRoute(id) {
      if (!id) return;
      this.$router.push({ name: `${this.model}.show`, params: { id: id } });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.setBreadcrumbs(this.model, "index");
    this.search();
  },

  validators: {
    "search_data.status": function (value = null) {
      return Validator.value(value);
    },
  },
};
</script>

<style scoped>
.category-info-cell {
  cursor: pointer;
}
.cat-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>
