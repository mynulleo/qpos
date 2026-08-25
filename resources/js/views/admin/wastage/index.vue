<template>
  <index-page>
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

      <date-picker id='searchfromauditdate' v-model='search_data.from_date'
        field='search_data.from_date' title='From Audit Date' placeholder='From Date' col='3'
        :req='false'></date-picker>

      <date-picker id='searchtoauditdate' v-model='search_data.to_date' field='search_data.to_date'
        title='To Audit Date' placeholder='To Date' col='3' :req='false'
        :disablePastDates="search_data.from_date"></date-picker>
    </template>

    <template v-slot:summary-page>
      <div class="row mb-3 mt-3">
        <div class="col-12">
          <div class="row g-3">
            <!-- Total Audits -->
            <div class="col-xl-3 col-md-6 col-6">
              <div class="card shadow-sm border-0 bg-white border-start border-primary border-4">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-muted small text-uppercase mb-1 fw-bold">Total Audits</p>
                      <h4 class="mb-0 fw-bold text-primary">{{ table.datas ? table.datas.length : 0 }}</h4>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle d-none d-sm-block">
                      <i class="fas fa-clipboard-list fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Quantity Wasted -->
            <div class="col-xl-3 col-md-6 col-6">
              <div class="card shadow-sm border-0 bg-white border-start border-warning border-4">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-muted small text-uppercase mb-1 fw-bold">Total Wasted Qty</p>
                      <h4 class="mb-0 fw-bold text-warning">{{ totalWastageQty }}</h4>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle d-none d-sm-block">
                      <i class="fas fa-boxes fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Loss Amount -->
            <div class="col-xl-3 col-md-6 col-6">
              <div class="card shadow-sm border-0 bg-white border-start border-danger border-4">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-muted small text-uppercase mb-1 fw-bold">Total Loss Value</p>
                      <h4 class="mb-0 fw-bold text-danger">{{ $root.currency(totalLossAmount) }}</h4>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle d-none d-sm-block">
                      <i class="fas fa-money-bill-wave fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pending Approvals -->
            <div class="col-xl-3 col-md-6 col-6">
              <div class="card shadow-sm border-0 bg-white border-start border-info border-4">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="text-muted small text-uppercase mb-1 fw-bold">Pending Approval</p>
                      <h4 class="mb-0 fw-bold text-info">{{ pendingCount }}</h4>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle d-none d-sm-block">
                      <i class="fas fa-clock fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </index-page>
</template>

<script>
const model = "wastage";

const tableColumns = [
  { field: "audit_number", title: "Audit No" },
  { field: "audit_date", title: "Audit Date" },
  { field: "audited_by", title: "Auditor(s)" },
  { field: "auditor_id", title: "Lead Auditor", subfield: "auditor.full_name" },
  { field: "wastage_details_count", title: "Items Count", align: "center" },
  { field: "total_qty", title: "Total Qty", align: "right" },
  { field: "total_loss_amount", title: "Loss Value", align: "right" },
  { field: "status", title: "Status", align: "center" },
  { field: "approved_by", title: "Approved By", subfield: "approved_admin.full_name" },
  { field: "approved_date", title: "Approved Date" },
];

const json_fields = {
  "Audit No": "audit_number",
  "Audit Date": "audit_date",
  "Auditor(s)": "audited_by",
  "Lead Auditor": "auditor.full_name",
  "Total Qty": "total_qty",
  "Total Loss Amount": "total_loss_amount",
  "Status": "status",
  "Approved By": "approved_admin.full_name",
  "Approved Date": "approved_date",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
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

  computed: {
    totalWastageQty() {
      if (!this.table.datas || !this.table.datas.length) return 0;
      return this.table.datas.reduce((sum, row) => {
        return sum + Number(row.total_qty || 0);
      }, 0).toFixed(2);
    },

    totalLossAmount() {
      if (!this.table.datas || !this.table.datas.length) return 0;
      return this.table.datas.reduce((sum, row) => {
        return sum + Number(row.total_loss_amount || 0);
      }, 0);
    },

    pendingCount() {
      if (!this.table.datas || !this.table.datas.length) return 0;
      return this.table.datas.filter(row => !row.approved_by || row.status === 'pending').length;
    }
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
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
    this.getItems();
  },

  validators: {},
};
</script>
