<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <Input v-model="search_data.adjustment_no" field="search_data.adjustment_no" title="Adjustment No" placeholder="e.g. ADJ-..." col="3" />

      <v-select-container title="Adjustment Type" field="search_data.adjustment_type" col="3">
        <v-select v-model="search_data.adjustment_type" :options="adjustmentTypes" placeholder="-- Select Type --" :closeOnSelect="true"></v-select>
      </v-select-container>

      <v-select-container title="Conducted By (Employee)" field="search_data.conducted_by" col="3">
        <v-select v-model="search_data.conducted_by" label="full_name" :reduce="(obj) => obj.id"
          :options="$root.global.employees" placeholder="-- Select Employee --" :closeOnSelect="true"></v-select>
      </v-select-container>

      <v-select-container title="Filter By Item" field="search_data.item_id" col="3">
        <v-select v-model="search_data.item_id" label="title" :reduce="(obj) => obj.id"
          :options="items" placeholder="-- Select Item --" :closeOnSelect="true"></v-select>
      </v-select-container>

      <date-picker id='searchfromadjdate' v-model='search_data.from_date'
        field='search_data.from_date' title='From Date' placeholder='From Date' col='3'
        :req='false'></date-picker>

      <date-picker id='searchtoadjdate' v-model='search_data.to_date' field='search_data.to_date'
        title='To Date' placeholder='To Date' col='3' :req='false'
        :disablePastDates="search_data.from_date"></date-picker>
    </template>
  </index-page>
</template>

<script>
const model = "stockAdjustment";

const tableColumns = [
  { field: "adjustment_no", title: "Adjustment No" },
  { field: "adjustment_date", title: "Date" },
  { field: "adjustment_type", title: "Type" },
  { field: "conducted_by", title: "Conducted By", subfield: "conducted_by.full_name" },
  { field: "total_items", title: "Total Items", align: "center" },
  { field: "total_adjusted_qty", title: "Adjusted Qty", align: "right" },
  { field: "total_amount", title: "Total Value", align: "right" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Adjustment No": "adjustment_no",
  "Adjustment Date": "adjustment_date",
  "Adjustment Type": "adjustment_type",
  "Conducted By": "conducted_by.full_name",
  "Total Items": "total_items",
  "Adjusted Qty": "total_adjusted_qty",
  "Total Amount": "total_amount",
  "Status": "status",
};

export default {
  data() {
    return {
      page_title: "Stock Adjustment List",
      model: model,
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        adjustment_no: "Adjustment No",
        reason: "Reason",
        remarks: "Remarks",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        adjustment_no: this.$route.query.adjustment_no ?? "",
        adjustment_type: this.$route.query.adjustment_type ?? "",
        conducted_by: this.$route.query.conducted_by ?? "",
        item_id: this.$route.query.item_id ?? "",
        from_date: this.$route.query.from_date ?? "",
        to_date: this.$route.query.to_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      items: [],
      adjustmentTypes: ['Physical Count', 'Damage Correction', 'Reconciliation', 'Other'],
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
      this.search_data.adjustment_no = "";
      this.search_data.adjustment_type = "";
      this.search_data.conducted_by = "";
      this.search_data.item_id = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
      this.search();
    },

    getItems() {
      axios.get('item?allData=true')
        .then((response) => {
          this.items = response.data;
        });
    }
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = "Stock Adjustment List";
    this.search();
    this.getItems();
  },

  validators: {},
};
</script>
