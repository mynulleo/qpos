<template>
  <index-page>
    <template v-slot:search-field>
      <v-select-container title="Supplier" field="search_data.supplier_id" col="3">
        <v-select v-model="search_data.supplier_id" label="org_name" :reduce="(obj) => obj.id"
          :options="$root.global.suppliers" placeholder="--Select Supplier--" :closeOnSelect="true"></v-select>
      </v-select-container>

      <v-select-container title="Category" field="search_data.category_id" col="3">
        <v-select v-model="search_data.category_id" label="title" :reduce="(obj) => obj.id"
          :options="categories" placeholder="--Select Category--" :closeOnSelect="true"></v-select>
      </v-select-container>

      <v-select-container title="Item" field="search_data.item_id" col="3">
        <v-select v-model="search_data.item_id" label="title" :reduce="(obj) => obj.id"
          :options="filteredItems" placeholder="--Select Item--" :closeOnSelect="true">
          <template #option="option">
            <div>
              <span>{{ option.title }}</span>
              <small class="text-muted d-block" v-if="option.barcode">Barcode: {{ option.barcode }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>
    </template>
  </index-page>
</template>

<script>

const model = "purchase";

const tableColumns = [
  { field: "invoiceno", title: "Invoiceno" },
  { field: "purchase_date", title: "Purchase Date" },
  { field: "supplier_id", title: "Supplier", subfield: "supplier.org_name" },
  { field: "amount", title: "Amount" },
  { field: "discount", title: "Discount" },
  { field: "tax", title: "Tax" },
  { field: "total_amount", title: "Total Amount" },
  { field: "receive_status", title: "Receive Status", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Invoiceno": "invoiceno",
  "Purchase Date": "purchase_date",
  "Supplier Id": "supplier_id",
  "Amount": "amount",
  "Discount": "discount",
  "Tax": "tax",
  "Total Amount": "total_amount",
};

export default {

  computed: {
    filteredItems() {
      if (this.search_data.category_id) {
        return this.allItems.filter(item => item.category_id == this.search_data.category_id);
      }
      return this.allItems;
    },
  },

  watch: {
    'search_data.category_id'(newVal, oldVal) {
      if (oldVal && newVal !== oldVal) {
        this.search_data.item_id = "";
      }
    },
  },

  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", invoiceno: "Invoice No" },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        supplier_id: this.$route.query.supplier_id ?? "",
        category_id: this.$route.query.category_id ?? "",
        item_id: this.$route.query.item_id ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      categories: [],
      allItems: [],
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
      this.search_data.supplier_id = "";
      this.search_data.category_id = "";
      this.search_data.item_id = "";
    },

    getCategories() {
      axios.get("getcategories/Item").then((response) => {
        this.categories = response.data || [];
      });
    },

    getItems() {
      axios.get("item?allData=true").then((response) => {
        this.allItems = response.data || [];
      });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
    this.getCategories();
    this.getItems();
  },

  validators: {},
};
</script>