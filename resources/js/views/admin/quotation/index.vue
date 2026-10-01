<template>
  <index-page :show_status="false">
    <!-- Advance Filter Fields -->
    <template v-slot:search-field>
      <Input v-model="search_data.quotation_no" field="search_data.quotation_no" title="Quotation No (কোটেশন নং)" placeholder="e.g. QT-..." col="3" />

      <v-select-container title="Customer / Client (গ্রাহক)" field="search_data.client_id" col="3">
        <v-select v-model="search_data.client_id" label="name" :reduce="(obj) => obj.id"
          :options="clients" placeholder="-- Select Client --" :closeOnSelect="true">
          <template #option="option">
            <div>
              <strong>{{ option.org_name || option.name }}</strong>
              <small class="text-muted d-block" v-if="option.mobile">📱 {{ option.mobile }}</small>
            </div>
          </template>
        </v-select>
      </v-select-container>

      <div class="col-md-3">
        <div class="form-group">
          <label class="form-label fw-bold small text-dark">Status (অবস্থা)</label>
          <select class="form-select shadow-sm" v-model="search_data.status">
            <option value="">-- All Status --</option>
            <option value="draft">Draft (খসড়া)</option>
            <option value="sent">Sent (প্রেরিত)</option>
            <option value="accepted">Accepted (গৃহীত)</option>
            <option value="declined">Declined (বাতিল)</option>
            <option value="converted">Converted to Invoice (ইনভয়েস হয়েছে)</option>
            <option value="expired">Expired (মেয়াদোত্তীর্ণ)</option>
          </select>
        </div>
      </div>

      <date-picker id='searchfromquotedate' v-model='search_data.from_date'
        field='search_data.from_date' title='From Date (শুরুর তারিখ)' placeholder='From Date' col='3'
        :req='false'></date-picker>

      <date-picker id='searchtoquotedate' v-model='search_data.to_date' field='search_data.to_date'
        title='To Date (শেষের তারিখ)' placeholder='To Date' col='3' :req='false'
        :disablePastDates="search_data.from_date"></date-picker>
    </template>

    <!-- Custom Column Slot: Quotation No -->
    <template v-slot:quotation_no="{ item }">
      <td>
        <router-link :to="{ name: 'quotation.show', params: { id: item.id } }" class="fw-bold font-monospace text-decoration-none" style="color: #112C47;">
          <i class="fas fa-file-alt me-1 opacity-75"></i> {{ item.quotation_no }}
        </router-link>
      </td>
    </template>

    <!-- Custom Column Slot: Date formatted as '23 March, 2026' -->
    <template v-slot:quotation_date="{ item }">
      <td>
        <span class="font-monospace fw-semibold text-dark">{{ formatCustomDate(item.quotation_date) }}</span>
      </td>
    </template>

    <!-- Custom Column Slot: Client Name & Contact -->
    <template v-slot:client_id="{ item }">
      <td>
        <div class="fw-bold text-dark">{{ item.client_name || (item.client ? (item.client.org_name || item.client.name) : 'Walk-in Customer') }}</div>
        <small class="text-muted" v-if="item.client_phone || (item.client && item.client.mobile)">
          <i class="fas fa-phone-alt me-1 text-primary small"></i> {{ item.client_phone || (item.client ? item.client.mobile : '') }}
        </small>
      </td>
    </template>

    <!-- Custom Column Slot: Total Items -->
    <template v-slot:total_items="{ item }">
      <td class="text-center">
        <span class="badge bg-light text-dark border font-monospace">{{ item.total_items || (item.quotation_details ? item.quotation_details.length : 0) }}</span>
      </td>
    </template>

    <!-- Custom Column Slot: Total Amount -->
    <template v-slot:total_amount="{ item }">
      <td class="text-end">
        <span class="font-monospace fw-bold text-dark">৳ {{ formatCurrency(item.total_amount) }}</span>
      </td>
    </template>

    <!-- Custom Column Slot: Status Badge with distinct colors -->
    <template v-slot:status="{ item }">
      <td class="text-center">
        <span class="badge px-3 py-1 font-monospace text-uppercase" :class="statusBadgeClass(item.status)">
          {{ getStatusLabel(item.status) }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
import axios from "axios";
import moment from "moment";

const model = "quotation";

const tableColumns = [
  { field: "quotation_no", title: "Quotation No" },
  { field: "quotation_date", title: "Date" },
  { field: "client_id", title: "Client" },
  { field: "subject", title: "Subject" },
  { field: "total_items", title: "Items", align: "center" },
  { field: "total_amount", title: "Total Amount (৳)", align: "right" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Quotation No": "quotation_no",
  "Quotation Date": "quotation_date",
  "Validity Date": "validity_date",
  "Client Name": "client_name",
  "Client Phone": "client_phone",
  "Client Email": "client_email",
  "Subject": "subject",
  "Sub Total": "sub_total",
  "Discount Amount": "discount_amount",
  "Tax Amount": "tax_amount",
  "Shipping Cost": "shipping_cost",
  "Total Amount": "total_amount",
  "Total Items": "total_items",
  "Status": "status",
};

export default {
  data() {
    return {
      page_title: "Quotation List",
      model: model,
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        quotation_no: "Quotation No",
        subject: "Subject",
        client_name: "Client Name",
        remarks: "Remarks",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        quotation_no: this.$route.query.quotation_no ?? "",
        client_id: this.$route.query.client_id ?? "",
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
      clients: [],
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
    formatCustomDate(val) {
      if (!val) return "N/A";
      return moment(val).format("D MMMM, YYYY");
    },

    formatCurrency(val) {
      const num = Number(val || 0);
      return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    statusBadgeClass(status) {
      const map = {
        draft: "bg-secondary text-white",
        sent: "bg-primary text-white",
        accepted: "bg-success text-white",
        declined: "bg-danger text-white",
        converted: "bg-info text-dark",
        expired: "bg-warning text-dark",
      };
      return map[status] || "bg-secondary text-white";
    },

    getStatusLabel(status) {
      const map = {
        draft: "Draft",
        sent: "Sent",
        accepted: "Accepted",
        declined: "Declined",
        converted: "Converted",
        expired: "Expired",
      };
      return map[status] || (status ? status.toUpperCase() : "DRAFT");
    },

    search() {
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.quotation_no = "";
      this.search_data.client_id = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
      this.search();
    },

    getClients() {
      axios.get('client?allData=true')
        .then((response) => {
          this.clients = response.data || [];
        })
        .catch(() => {
          this.clients = [];
        });
    }
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = "Quotation List (কোটেশন তালিকা)";
    this.search();
    this.getClients();
  },

  validators: {},
};
</script>
