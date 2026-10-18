<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Category" field="search_data.category_id" col="3">
        <v-select
          v-model="search_data.category_id"
          label="title"
          :reduce="(obj) => obj.id"
          :options="categories"
          :placeholder="$t('-- All Categories --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Brand" field="search_data.brand_id" col="3">
        <v-select
          v-model="search_data.brand_id"
          label="title"
          :reduce="(obj) => obj.id"
          :options="brands"
          :placeholder="$t('-- All Brands --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Series Title & ID -->
    <template v-slot:series_info="{ item }">
      <td class="series-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="series-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-layer-group"></i>
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

    <!-- Column 2: Category & Brand Badges -->
    <template v-slot:taxonomy_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span v-if="item.brand" class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #F6FFED; color: #389E0D; border: 1px solid #B7EB8F; font-size: 10.5px;">
            <i class="fas fa-certificate me-1.5"></i>{{ item.brand.title }}
          </span>
          <span v-if="item.category" class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 10.5px;">
            <i class="fas fa-tag me-1.5"></i>{{ item.category.title }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 3: Sorting Order -->
    <template v-slot:sorting_info="{ item }">
      <td class="text-center font-monospace text-muted" @click="pushToViewRoute(item.id)">
        {{ item.sorting ?? 0 }}
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:series_status="{ item }">
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
const model = "series";

const tableColumns = [
  { field: "series_info", title: "Series Title" },
  { field: "taxonomy_info", title: "Brand & Category" },
  { field: "sorting_info", title: "Sort Order", align: "center" },
  { field: "series_status", title: "Status", align: "center" },
];

const json_fields = {
  Category: "category.title",
  Brand: "brand.title",
  Title: "title",
  Sorting: "sorting",
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
        category_id: this.$route.query.category_id ?? "",
        brand_id: this.$route.query.brand_id ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      categories: [],
      brands: [],
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
    getCategories() {
      axios.get("category?allData=true").then((res) => {
        this.categories = res.data;
      });
    },

    getBrands() {
      axios.get("brand?allData=true").then((res) => {
        this.brands = res.data;
      });
    },

    search() {
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.category_id = "";
      this.search_data.brand_id = "";
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
    this.getCategories();
    this.getBrands();
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.series-info-cell {
  cursor: pointer;
}
.series-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>
