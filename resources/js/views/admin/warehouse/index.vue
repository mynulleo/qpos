<template>
  <index-page>
    <template v-slot:search-field>
      <v-select-container title="Branch" field="search_data.branch_id" col="3">
        <v-select v-model="search_data.branch_id" label="title" :reduce="(obj) => obj.id"
          :options="$root.global.branches" placeholder="--Select Branch--" :closeOnSelect="true"></v-select>
      </v-select-container>
    </template>
  </index-page>
</template>

<script>
const model = "warehouse";

const tableColumns = [
  { field: "name", title: "Warehouse Name" },
  { field: "code", title: "Code", align: "center" },
  { field: "contact_person", title: "Contact Person" },
  { field: "contact_person_phone", title: "Mobile" },
  { field: "branch_id", title: "Branch", subfield: "branch.title" },
  { field: "capacity", title: "Capacity" },
  {
    field: "sorting",
    title: "Sorting",
    sorting: true,
    namespace: "Warehouse",
    auto: "",
    align: "center",
  },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Warehouse Name": "name",
  "Code": "code",
  "Contact Person": "contact_person",
  "Mobile": "contact_person_phone",
  "Phone": "phone",
  "Email": "email",
  "Address": "address",
  "Capacity": "capacity",
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
        name: "Warehouse Name",
        code: "Code",
        contact_person: "Contact Person",
        phone: "Phone",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        branch_id: this.$route.query.branch_id ?? "",
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
    search() {
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.branch_id = "";
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
