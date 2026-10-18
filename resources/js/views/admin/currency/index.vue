<template>
  <index-page :show_status="false">
    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Currency Title & Symbol -->
    <template v-slot:currency_info="{ item }">
      <td class="currency-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="curr-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 14px; margin-right: 12px;"
          >
            <span>{{ item.symbol || '$' }}</span>
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

    <!-- Column 2: Short Code -->
    <template v-slot:short_name_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 11px;">
          {{ item.short_name }}
        </span>
      </td>
    </template>

    <!-- Column 3: Is Default -->
    <template v-slot:default_info="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          class="badge px-2 py-1 font-monospace rounded-pill"
          :class="item.is_default_text === 'Yes' ? 'bg-success text-white' : 'bg-light text-secondary border'"
          style="font-size: 10.5px;"
        >
          <i class="fas" :class="item.is_default_text === 'Yes' ? 'fa-check-circle me-1' : 'fa-minus me-1'"></i>
          {{ item.is_default_text === 'Yes' ? 'Default Currency' : 'Standard' }}
        </span>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:currency_status="{ item }">
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
const model = "currency";

const tableColumns = [
  { field: "currency_info", title: "Currency & Symbol" },
  { field: "short_name_info", title: "ISO Code" },
  { field: "default_info", title: "System Default", align: "center" },
  { field: "currency_status", title: "Status", align: "center" },
];

const json_fields = {
  "Title": "title",
  "Short Name": "short_name",
  "Symbol": "symbol",
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
.currency-info-cell {
  cursor: pointer;
}
.curr-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>