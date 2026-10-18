<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Account" field="search_data.account_id" col="3">
        <v-select
          v-model="search_data.account_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="fundaccounts"
          placeholder="-- All Accounts --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfrominvoicedate"
        v-model="search_data.from_withdraw_date"
        field="search_data.from_withdraw_date"
        title="From Withdrawal Date"
        placeholder="From Withdrawal Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoPaymentdate"
        v-model="search_data.to_withdraw_date"
        field="search_data.to_withdraw_date"
        title="To Withdrawal Date"
        placeholder="To Withdrawal Date"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_withdraw_date"
      ></date-picker>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Withdrawal Ref & Date -->
    <template v-slot:withdrawl_info="{ item }">
      <td class="withdrawl-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="wdl-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-hand-holding-usd"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important;">
              #WDL-{{ String(item.id).padStart(5, '0') }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span>
                <i class="far fa-calendar-alt text-secondary me-2" style="font-size: 11px;"></i>
                {{ item.withdrwal_date || 'N/A' }}
              </span>
              <span v-if="item.remarks" class="text-truncate d-inline-block" style="max-width: 160px; font-size: 11px;" :title="item.remarks">
                <i class="far fa-comment-dots me-1.5 text-muted"></i>{{ item.remarks }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Account Details -->
    <template v-slot:account_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2">
          <div
            class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-2"
            style="width: 28px; height: 28px; background-color: #FFF0F6; color: #EB2F96; border: 1px solid #FFADD2; margin-right: 8px;"
          >
            <i class="fas fa-university" style="font-size: 11px;"></i>
          </div>
          <div>
            <span class="fw-bold text-dark font-monospace" style="font-size: 13px;">
              {{ item.account ? item.account.account_name : ('#' + item.account_id) }}
            </span>
            <div v-if="item.account && item.account.account_code" class="text-muted small font-monospace" style="font-size: 11px;">
              Code: {{ item.account.account_code }}
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 3: Amount -->
    <template v-slot:amount_info="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <span class="fw-bold font-monospace text-danger fs-6">
          {{ $filter.formatBDT(item.amount) }}
        </span>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:withdrawl_status="{ item }">
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
const model = "withdrawl";

const tableColumns = [
  { field: "withdrawl_info", title: "Withdrawal Ref & Date" },
  { field: "account_info", title: "Account" },
  { field: "amount_info", title: "Amount", align: "right" },
  { field: "withdrawl_status", title: "Status", align: "center" },
];

const json_fields = {
  "Withdrawal Ref": "id",
  "Withdrawal Date": "withdrwal_date",
  "Account": "account.account_name",
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
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        account_id: this.$route.query.account_id ?? "",
        from_withdraw_date: this.$route.query.from_withdraw_date ?? "",
        to_withdraw_date: this.$route.query.to_withdraw_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      fundaccounts: []
    };
  },

  provide() {
    return {
      validate: this.validation,
      model: this.model,
      search_data: this.search_data,
      table: this.table,
      json_fields: this.json_fields,
      search: this.search,
      resetSearchData: this.resetSearchData,
    };
  },

  methods: {
    getFundAccounts() {
      axios.get(`/getfundaccounts`)
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
          console.error(error);
        });
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
      this.search_data.account_id = "";
      this.search_data.from_withdraw_date = "";
      this.search_data.to_withdraw_date = "";
      this.search();
    },

    pushToViewRoute(id) {
      if (!id) return;
      this.$router.push({ name: `${this.model}.show`, params: { id: id } });
    },
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
.withdrawl-info-cell {
  cursor: pointer;
}
.wdl-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>