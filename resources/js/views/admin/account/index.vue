<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Select Group Account" field="search_data.parent_id" col="3">
        <v-select
          v-model="search_data.parent_id"
          label="name"
          :reduce="(obj) => obj.id"
          :options="$root.global.groupaccounts"
          placeholder="-- Select Group Account --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Account Type" field="search_data.account_type" col="3">
        <v-select
          v-model="search_data.account_type"
          label="name"
          :reduce="(obj) => obj.value"
          :options="$root.global.accounttypes"
          placeholder="-- Select Account Type --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Reference Type" field="search_data.reference_type" col="3">
        <v-select
          v-model="search_data.reference_type"
          label="name"
          :reduce="(obj) => obj.value"
          :options="$root.global.referencetypes"
          placeholder="-- Select Reference Type --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <div class="col-md-3 col-sm-12">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Status') }}</label>
        <select v-model="search_data.status" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Statuses --') }}</option>
          <option value="active">{{ $t('Active') }}</option>
          <option value="deactive">{{ $t('Deactive') }}</option>
        </select>
      </div>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Account Info -->
    <template v-slot:account_info="{ item }">
      <td class="account-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div class="acc-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3" style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;">
            <i :class="item.is_group ? 'fas fa-folder' : 'fas fa-book'"></i>
          </div>
          <div>
            <div class="fw-bold text-dark fs-6" style="color: #112C47 !important;">
              {{ item.account_name }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mt-0.5 font-monospace">
              <span>Code: <strong>{{ item.account_code || ('ACC-' + item.id) }}</strong></span>
              <span v-if="item.is_group" class="badge bg-warning bg-opacity-10 text-dark border py-0 px-1.5" style="font-size: 10px;">
                <i class="fas fa-layer-group me-1 text-warning"></i>Group
              </span>
              <span v-if="item.is_fund_account" class="badge bg-info bg-opacity-10 text-info border py-0 px-1.5" style="font-size: 10px;">
                <i class="fas fa-wallet me-1 text-info"></i>Fund
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Parent Group Head -->
    <template v-slot:parent_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="text-dark fw-semibold" v-if="item.parent">
          <i class="fas fa-folder-tree text-secondary me-2" style="font-size: 11px;"></i>
          {{ item.parent.account_name }}
        </div>
        <div class="text-muted small fst-italic" v-else>
          <i class="fas fa-crown text-warning me-2" style="font-size: 11px;"></i>{{ $t('Primary / Top Level') }}
        </div>
      </td>
    </template>

    <!-- Column 3: Account Classification / Type -->
    <template v-slot:type_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge" :class="getAccountTypeBadge(item.account_type)">
          {{ item.account_type ? item.account_type.toUpperCase() : 'GENERAL' }}
        </span>
        <div class="small text-muted mt-0.5" v-if="item.reference_type">
          <i class="fas fa-link text-secondary me-2" style="font-size: 10px;"></i>{{ item.reference_type }}
        </div>
      </td>
    </template>

    <!-- Column 4: Opening Balance -->
    <template v-slot:balance_info="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <div class="fw-bold font-monospace text-dark fs-6">
          ৳ {{ formatNum(item.opening_balance || 0) }}
        </div>
        <div class="small" style="font-size: 10px;">
          <span class="badge" :class="item.opening_balance_type === 'debit' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success'">
            {{ item.opening_balance_type ? item.opening_balance_type.toUpperCase() : 'DEBIT' }}
          </span>
        </div>
      </td>
    </template>

    <!-- Column 5: Status -->
    <template v-slot:account_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          v-if="item.status === 'active' || item.status == 1"
          class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2.5 font-monospace"
        >
          <i class="fas fa-check-circle me-1"></i>{{ $t('Active') }}
        </span>
        <span
          v-else
          class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2.5 font-monospace"
        >
          <i class="fas fa-ban me-1"></i>{{ $t('Deactive') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "account";

const tableColumns = [
  { field: "account_info", title: "Account Name & Code" },
  { field: "parent_info", title: "Parent Group Head" },
  { field: "type_info", title: "Account Classification" },
  { field: "balance_info", title: "Opening Balance", align: "right" },
  { field: "account_status", title: "Status", align: "center" },
];

const json_fields = {
  "Account Code": "account_code",
  "Account Name": "account_name",
  "Parent": "parent.account_name",
  "Account Type": "account_type",
  "Reference Type": "reference_type",
  "Opening Balance": "opening_balance",
  "Opening Balance Type": "opening_balance_type",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "Chart of Accounts",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        account_code: "Account Code",
        account_name: "Account Name",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        parent_id: this.$route.query.parent_id ?? "",
        account_type: this.$route.query.account_type ?? "",
        reference_type: this.$route.query.reference_type ?? "",
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
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    getAccountTypeBadge(type) {
      if (!type) return "bg-secondary text-white";
      const t = type.toLowerCase();
      if (t === "asset") return "bg-success text-white";
      if (t === "liability") return "bg-warning text-dark";
      if (t === "equity") return "bg-purple text-white";
      if (t === "revenue") return "bg-info text-dark";
      if (t === "expense") return "bg-danger text-white";
      return "bg-secondary text-white";
    },

    pushToViewRoute(id) {
      if (this.$root.checkPermission(this.model + ".show")) {
        this.$router.push({
          name: this.model + ".show",
          params: { id: id },
          query: { page: this.$route.query.page },
        });
      }
    },

    search() {
      const cleanParams = {};
      for (const key in this.search_data) {
        const val = this.search_data[key];
        if (val !== "" && val !== null && val !== undefined) {
          cleanParams[key] = val;
        }
      }

      this.$router.push({
        name: this.model + ".index",
        query: cleanParams,
      }).catch(() => {});

      this.get_paginate(this.model, cleanParams);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.parent_id = "";
      this.search_data.account_type = "";
      this.search_data.reference_type = "";
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} Management`;
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.account-info-cell {
  cursor: pointer;
}

.bg-purple {
  background-color: #8b5cf6 !important;
}
</style>