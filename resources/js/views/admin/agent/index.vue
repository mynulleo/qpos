<template>
  <index-page></index-page>
</template>

<script>
const model = "agent";

const tableColumns = [
  { field: "image", title: "Image", image: true, imgWidth: "40px" },
  { field: "full_name", title: "Full Name" },
  { field: "mobile", title: "Mobile" },
  { field: "organization", title: "Organization" },
  { field: "designation", title: "Designation" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Full Name": "full_name",
  "Mobile": "mobile",
  "Organization": "organization",
  "Designation": "designation",
  "Address": "address",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        full_name: "Full Name",
        mobile: "Mobile",
        organization: "Organization",
        designation: "Designation"
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
