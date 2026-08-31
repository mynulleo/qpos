<template>
  <index-page>
    <template v-slot:search-field>
      <v-select-container title="Supplier" field="search_data.supplier_id" col="3">
        <v-select v-model="search_data.supplier_id" label="org_name" :reduce="(obj) => obj.id"
          :options="$root.global.suppliers" placeholder="--Select Supplier--" :closeOnSelect="true"></v-select>
      </v-select-container>
      <v-select-container title="Warehouse" field="search_data.warehouse_id" col="3">
        <v-select v-model="search_data.warehouse_id" label="name" :reduce="(obj) => obj.id"
          :options="$root.global.warehouses" placeholder="--Select Warehouse--" :closeOnSelect="true"></v-select>
      </v-select-container>
    </template>
  </index-page>
</template>

<script>
const model = "grn";

const tableColumns = [
  { field: "grn_no", title: "GRN No" },
  { field: "grn_date", title: "GRN Date" },
  { field: "purchase_id", title: "PO Invoice", subfield: "purchase.invoiceno" },
  { field: "supplier_id", title: "Supplier", subfield: "supplier.org_name" },
  { field: "warehouse_id", title: "Warehouse", subfield: "warehouse.name" },
  { field: "total_qty", title: "Received Qty", align: "center" },
  { field: "total_amount", title: "Total Amount" },
  { field: "paid_amount", title: "Paid Amount" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "GRN No": "grn_no",
  "GRN Date": "grn_date",
  "PO Invoice": "purchase.invoiceno",
  "Supplier": "supplier.org_name",
  "Warehouse": "warehouse.name",
  "Received Qty": "total_qty",
  "Total Amount": "total_amount",
  "Paid Amount": "paid_amount",
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
        grn_no: "GRN No",
        challan_no: "Challan No",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        supplier_id: this.$route.query.supplier_id ?? "",
        warehouse_id: this.$route.query.warehouse_id ?? "",
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
      this.search_data.supplier_id = "";
      this.search_data.warehouse_id = "";
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `Goods Receive (GRN) List`;
    this.search();
  },

  validators: {},
};
</script>
