<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <Input v-model="search_data.audit_number" field="search_data.audit_number" title="Audit Number" placeholder="e.g. WST-..." col="3" />

      <v-select-container title="Auditor (Employee)" field="search_data.auditor_id" col="3">
        <v-select v-model="search_data.auditor_id" label="full_name" :reduce="(obj) => obj.id"
          :options="$root.global.employees" placeholder="-- Select Auditor --" :closeOnSelect="true"></v-select>
      </v-select-container>

      <v-select-container title="Filter By Item" field="search_data.item_id" col="3">
        <v-select v-model="search_data.item_id" label="title" :reduce="(obj) => obj.id"
          :options="items" placeholder="-- Select Item --" :closeOnSelect="true"></v-select>
      </v-select-container>

      <StatusDropDown col="3"></StatusDropDown>

      <date-picker id='searchfromauditdate' v-model='search_data.from_date'
        field='search_data.from_date' title='From Audit Date' placeholder='From Date' col='3'
        :req='false'></date-picker>

      <date-picker id='searchtoauditdate' v-model='search_data.to_date' field='search_data.to_date'
        title='To Audit Date' placeholder='To Date' col='3' :req='false'
        :disablePastDates="search_data.from_date"></date-picker>
    </template>
  </index-page>
</template>

<script>
const model = "wastage";

const tableColumns = [
  { field: "audit_number", title: "Audit No" },
  { field: "audit_date", title: "Audit Date" },
  { field: "audited_by", title: "Auditor" },
  { field: "total_qty", title: "Total Qty", align: "right" },
  { field: "total_loss_amount", title: "Loss Value", align: "right" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Audit No": "audit_number",
  "Audit Date": "audit_date",
  "Auditor": "audited_by",
  "Total Qty": "total_qty",
  "Total Loss Amount": "total_loss_amount",
  "Status": "status",
};

export default {
  data() {
    return {
      page_title: "Wastage List",
      model: model,
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        audit_number: "Audit No",
        audited_by: "Auditor Name",
        note: "Note / Remarks",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        audit_number: this.$route.query.audit_number ?? "",
        auditor_id: this.$route.query.auditor_id ?? "",
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
      items: []
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
      this.search_data.audit_number = "";
      this.search_data.auditor_id = "";
      this.search_data.item_id = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
    },

    getItems() {
      axios.get('item?allData=true')
        .then(response => {
          this.items = response.data;
        })
        .catch(err => {
          console.error(err);
        });
    }
  },

  created() {
    this.getRouteName(this.model);
    this.setBreadcrumbs(this.model, "index");
    this.search();
    this.getItems();
  },

  validators: {
    "search_data.status": function (value = null) {
      return Validator.value(value);
    },
  },
};
</script>
