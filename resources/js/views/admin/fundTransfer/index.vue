<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="From (Source) Account" field="search_data.from_account_id" col="3">
        <v-select
          v-model="search_data.from_account_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="fundaccounts"
          placeholder="-- All Source Accounts --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="To (Destination) Account" field="search_data.to_account_id" col="3">
        <v-select
          v-model="search_data.to_account_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="fundaccounts"
          placeholder="-- All Destination Accounts --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfrominvoicedate"
        v-model="search_data.from_transfer_date"
        field="search_data.from_transfer_date"
        title="From Transfer Date"
        placeholder="From Transfer Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoPaymentdate"
        v-model="search_data.to_transfer_date"
        field="search_data.to_transfer_date"
        title="To Transfer Date"
        placeholder="To Transfer Date"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_transfer_date"
      ></date-picker>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Transfer Info & Date -->
    <template v-slot:transfer_info="{ item }">
      <td class="transfer-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="transfer-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-exchange-alt"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important;">
              #TRX-{{ String(item.id).padStart(5, '0') }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span>
                <i class="far fa-calendar-alt text-secondary me-2" style="font-size: 11px;"></i>
                {{ item.transfer_date || 'N/A' }}
              </span>
              <span v-if="item.remarks" class="text-truncate d-inline-block" style="max-width: 150px; font-size: 11px;" :title="item.remarks">
                <i class="far fa-comment-dots me-1.5 text-muted"></i>{{ item.remarks }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Source (From) Account -->
    <template v-slot:from_account_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill px-2 py-0.5 font-monospace fw-bold" style="background-color: #FFF1F0; color: #CF1322; border: 1px solid #FFA39E; font-size: 10px;">
            <i class="fas fa-arrow-up me-1"></i>From
          </span>
          <span class="fw-bold text-dark font-monospace" style="font-size: 13px;">
            {{ item.from_account ? item.from_account.account_name : ('#' + item.from_account_id) }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 3: Destination (To) Account -->
    <template v-slot:to_account_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill px-2 py-0.5 font-monospace fw-bold" style="background-color: #F6FFED; color: #389E0D; border: 1px solid #B7EB8F; font-size: 10px;">
            <i class="fas fa-arrow-down me-1"></i>To
          </span>
          <span class="fw-bold text-dark font-monospace" style="font-size: 13px;">
            {{ item.to_account ? item.to_account.account_name : ('#' + item.to_account_id) }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 4: Amount -->
    <template v-slot:amount_info="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <span class="fw-bold font-monospace text-primary fs-6">
          {{ $filter.formatBDT(item.amount) }}
        </span>
      </td>
    </template>

    <!-- Column 5: Status -->
    <template v-slot:transfer_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          class="badge rounded-pill px-2 py-1 font-monospace"
          :class="item.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'"
          style="font-size: 11px;"
        >
          <i class="fas fa-circle me-1" style="font-size: 7px;"></i>
          {{ item.status === 'active' ? 'Active' : 'Deactive' }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "fundTransfer";

const tableColumns = [
  { field: "transfer_info", title: "Transfer Ref & Date" },
  { field: "from_account_info", title: "Source (From) Account" },
  { field: "to_account_info", title: "Destination (To) Account" },
  { field: "amount_info", title: "Amount", align: "right" },
  { field: "transfer_status", title: "Status", align: "center" },
];

const json_fields = {
  "Transfer Ref": "id",
  "Transfer Date": "transfer_date",
  "From Account": "from_account.account_name",
  "To Account": "to_account.account_name",
  "Amount": "amount",
  "Status": "status",
  "Remarks": "remarks",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fundaccounts: [],
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        from_transfer_date: this.$route.query.from_transfer_date ?? "",
        to_transfer_date: this.$route.query.to_transfer_date ?? "",
        from_account_id: this.$route.query.from_account_id ?? "",
        to_account_id: this.$route.query.to_account_id ?? "",
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
      this.search_data.from_transfer_date = "";
      this.search_data.to_transfer_date = "";
      this.search_data.from_account_id = "";
      this.search_data.to_account_id = "";
      this.search();
    },

    pushToViewRoute(id) {
      if (!id) return;
      this.$router.push({ name: `${this.model}.show`, params: { id: id } });
    },

    getFundAccounts() {
      axios
        .get("/getfundaccounts")
        .then((response) => {
          const list = Array.isArray(response.data) ? response.data : [];
          this.fundaccounts = list.map((item) => {
            const formattedName =
              item.name && item.name.trim() !== ""
                ? item.name
                : item.account_code
                ? `${item.account_code} - ${item.account_name}`
                : item.account_name;
            return {
              ...item,
              name: formattedName || `Account #${item.id}`
            };
          });
        })
        .catch((error) => {
          console.error("Error fetching fund accounts:", error);
        });
    }
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.getFundAccounts();
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.transfer-info-cell {
  cursor: pointer;
}
.transfer-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>