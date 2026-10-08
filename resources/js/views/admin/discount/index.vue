<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container :title="$t('Discount Scope')" field="search_data.discount_type" col="3">
        <v-select
          v-model="search_data.discount_type"
          label="title"
          :reduce="(obj) => obj.value"
          :options="discountTypeOptions"
          :placeholder="$t('-- All Scopes --')"
          :closeOnSelect="true"
        />
      </v-select-container>

      <v-select-container :title="$t('Applicable On')" field="search_data.applicable_on" col="3">
        <v-select
          v-model="search_data.applicable_on"
          label="title"
          :reduce="(obj) => obj.value"
          :options="applicableOptions"
          :placeholder="$t('-- All Price Natures --')"
          :closeOnSelect="true"
        />
      </v-select-container>

      <v-select-container :title="$t('Status')" field="search_data.status" col="2">
        <v-select
          v-model="search_data.status"
          label="title"
          :reduce="(obj) => obj.value"
          :options="statusOptions"
          :placeholder="$t('-- All Status --')"
          :closeOnSelect="true"
        />
      </v-select-container>
    </template>
  </index-page>
</template>

<script>
const model = "discount";

const tableColumns = [
  { field: "name", title: "Campaign Name" },
  { field: "discount_type", title: "Scope", align: "center" },
  { field: "target_title", title: "Category / Item" },
  { field: "applicable_on", title: "Applicable On", align: "center" },
  { field: "discount_display", title: "Discount", align: "center" },
  { field: "validity", title: "Validity Period", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Campaign Name": "name",
  "Scope": "discount_type",
  "Category / Item": "target_title",
  "Applicable On": "applicable_on",
  "Discount": "discount_display",
  "Validity Period": "validity",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "Discount Management",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        name: "Campaign Name",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        discount_type: this.$route.query.discount_type ?? "",
        applicable_on: this.$route.query.applicable_on ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      discountTypeOptions: [
        { title: "Category Wise", value: "category" },
        { title: "Item Wise", value: "item" },
      ],
      applicableOptions: [
        { title: "Retail", value: "retail" },
        { title: "Wholesale", value: "wholesale" },
        { title: "Both (Retail & Wholesale)", value: "both" },
      ],
      statusOptions: [
        { title: "Active", value: "active" },
        { title: "Inactive", value: "inactive" },
      ],
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
      this.$router.push({
        name: this.model + ".index",
        query: { ...this.search_data },
      });
      this.get_paginate(this.model, this.search_data);
    },
    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.discount_type = "";
      this.search_data.applicable_on = "";
    },
  },
  created() {
    this.getRouteName(this.model);
    this.setBreadcrumbs(this.model, "index");
    this.page_title = "Discount Management";
    this.search();
  },
};
</script>
