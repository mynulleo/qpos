<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Select Client" field="search_data.client_id" col="3">
        <v-select v-model="search_data.client_id" label="name" :reduce="(obj) => obj.id" :options="clients"
          placeholder="--Select Client--" :closeOnSelect="true"></v-select>
      </v-select-container>
      <v-select-container title="Select Workorder" field="search_data.workorder_id" col="4">
        <v-select v-model="search_data.workorder_id" label="name" :reduce="(obj) => obj.id" :options="workorders"
          placeholder="--Select Workorder--" :closeOnSelect="true"></v-select>
      </v-select-container>
      <Input v-model="search_data.order_no" field="search_data.order_no" title="Order No" col="2" />
      <Input v-model="search_data.uno_no" field="search_data.uno_no" col="2" title="UNO No" />
      <date-picker id='searchfromorderdate' v-model='search_data.order_date' field='search_data.order_date'
        title='Order Date' placeholder='Order Date' col='3' :req='false'></date-picker>

      <date-picker id='searchfromchallandate' v-model='search_data.from_challan_date'
        field='search_data.from_challan_date' title='From Challan Date' placeholder='From Challan Date' col='3'
        :req='false'></date-picker>
      <date-picker id='searchtoChallandate' v-model='search_data.to_challan_date' field='search_data.to_challan_date'
        title='To Challan Date' placeholder='To Challan Date' col='3' :req='false'
        :disablePastDates="search_data.from_challan_date"></date-picker>

    </template>
  </index-page>
</template>

<script>

const model = "challan";

const tableColumns = [
  { field: "work_order_id", title: "Order No", subfield: "workorder.order_no" },
  { field: "uno_no", title: "Uno No", subfield: "workorder.uno_no" },
  { field: "challan_no", title: "Challan No" },
  { field: "challan_date", title: "Challan Date" },
  { field: "client_id", title: "Client", subfield: "client.org_name" },
  { field: "delivery_location", title: "Delivery Location" },
  { field: "receive_by", title: "Receive By" },
  { field: "delivery_by", title: "Delivery By" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Work Order Id": "work_order_id", "Challan No": "challan_no", "Challan Date": "challan_date", "Client Id": "client_id", "Delivery Location": "delivery_location", "Receive By": "receive_by", "Receive By Designation": "receive_by_designation", "Receive By Mobile": "receive_by_mobile", "Delivery By": "delivery_by", "Delivery By Mobile": "delivery_by_mobile",
};

export default {

  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", challan_no: "Challan No", receive_by: "Receive By", receive_by_mobile: "Receiver Mobille" },
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
      },
      workorders: [],
      clients: []
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
    getWorkorders() {
      axios.get(
        `getworkorders/`
      )
        .then((response) => {
          this.workorders = response.data;

        })
        .catch((error) => {
          console.error(error);
        });
    },
    getClients() {
      axios.get(
        `clients/`
      )
        .then((response) => {
          this.clients = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
    this.getWorkorders();
    this.getClients();
  },

  validators: {},
};
</script>