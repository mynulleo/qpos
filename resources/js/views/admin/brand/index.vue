<template>
  <index-page>
    <template v-slot:search-field>
      <v-select-container title="Category" field="search_data.category_id" col="3">
        <v-select v-model="search_data.category_id" label="title" :reduce="(obj) => obj.id" :options="categories"
          placeholder="--Select Category--" :closeOnSelect="true"></v-select>
      </v-select-container>
    </template>
  </index-page>
</template>

<script>
const model = "brand";

const tableColumns = [
  { field: "category_id", title: "Category", subfield: "category.title" },
  { field: "title", title: "Title" },
  {
    field: "sorting",
    title: "Sorting",
    sorting: true,
    namespace: "Brand",
    auto: "",
    align: "center",
  },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
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
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      categories: [],
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
        this.search_data.category_id = "";
    },
    getCategories() {
      let module = 'Item';
      axios.get(`getcategories/${module}`)
        .then((response) => {
          this.categories = response.data;
        });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
    this.getCategories();
  },

  validators: {},
};
</script>
