<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <Input
        v-model="search_data.voucherno"
        field="search_data.voucherno"
        title="Voucher No"
        placeholder="e.g. VCH-..."
        col="3"
      />

      <div class="col-md-3 col-sm-12">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Voucher Type') }}</label>
        <select v-model="search_data.voucher_type" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Types --') }}</option>
          <option value="Credit">{{ $t('Credit (Receipt)') }}</option>
          <option value="Debit">{{ $t('Debit (Payment)') }}</option>
          <option value="Journal">{{ $t('Journal') }}</option>
          <option value="Contra">{{ $t('Contra') }}</option>
        </select>
      </div>

      <date-picker
        id="searchfromvchdate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Date"
        placeholder="From Date"
        col="2"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtovchdate"
        v-model="search_data.to_date"
        field="search_data.to_date"
        title="To Date"
        placeholder="To Date"
        col="2"
        :req="false"
        :disablePastDates="search_data.from_date"
      ></date-picker>

      <div class="col-md-2 col-sm-12">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Status') }}</label>
        <select v-model="search_data.status" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Statuses --') }}</option>
          <option value="active">{{ $t('Active') }}</option>
          <option value="deactive">{{ $t('Deactive') }}</option>
        </select>
      </div>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Voucher Ref & Date -->
    <template v-slot:voucher_info="{ item }">
      <td class="voucher-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div class="vch-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3" style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important;">
              {{ item.voucherno || ('VCH-' + String(item.id).padStart(5, '0')) }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-1.5 mt-0.5">
              <i class="far fa-calendar-alt text-secondary me-1" style="font-size: 11px;"></i>
              <span>{{ item.voucher_date || 'N/A' }}</span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Voucher Type & Narration -->
    <template v-slot:type_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge" :class="getVoucherTypeBadge(item.voucher_type)">
          {{ item.voucher_type ? item.voucher_type.toUpperCase() : 'JOURNAL' }}
        </span>
        <div class="small text-muted mt-1 text-truncate" style="max-width: 220px;" :title="item.narration" v-if="item.narration">
          <i class="fas fa-comment-alt text-secondary me-2" style="font-size: 10px;"></i>{{ item.narration }}
        </div>
      </td>
    </template>

    <!-- Column 3: Financial Year & Ref -->
    <template v-slot:financial_year_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="text-dark small fw-semibold" v-if="item.financial_year">
          <i class="fas fa-calendar-check text-info me-2" style="font-size: 11px;"></i>
          {{ item.financial_year.title }}
        </div>
        <div class="text-muted small" v-else>-</div>
        <div class="small text-muted font-monospace" v-if="item.payment">
          <i class="fas fa-receipt text-success me-2" style="font-size: 10px;"></i>{{ item.payment.payslipno }}
        </div>
      </td>
    </template>

    <!-- Column 4: Total Amount (Debit / Credit) -->
    <template v-slot:amount_info="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <div class="fw-bold font-monospace text-dark fs-6">
          ৳ {{ formatNum(item.total_debit || item.total_credit || 0) }}
        </div>
        <div class="small text-muted font-monospace" style="font-size: 10px;">
          Dr: ৳{{ formatNum(item.total_debit || 0) }} | Cr: ৳{{ formatNum(item.total_credit || 0) }}
        </div>
      </td>
    </template>

    <!-- Column 5: Status -->
    <template v-slot:voucher_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          v-if="item.status === 'active' || item.status == 1 || item.approved_by"
          class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2.5 font-monospace"
        >
          <i class="fas fa-check-circle me-1"></i>{{ $t('Active') }}
        </span>
        <span
          v-else
          class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2.5 font-monospace"
        >
          <i class="fas fa-clock me-1"></i>{{ $t('Pending') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "voucher";

const tableColumns = [
  { field: "voucher_info", title: "Voucher No & Date" },
  { field: "type_info", title: "Type & Narration" },
  { field: "financial_year_info", title: "Financial Year & Ref" },
  { field: "amount_info", title: "Total Amount", align: "right" },
  { field: "voucher_status", title: "Status", align: "center" },
];

const json_fields = {
  "Voucherno": "voucherno",
  "Voucher Type": "voucher_type",
  "Voucher Date": "voucher_date",
  "Total Debit": "total_debit",
  "Total Credit": "total_credit",
  "Narration": "narration",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "Accounting Vouchers",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        voucherno: "Voucher No",
        narration: "Narration",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        voucherno: this.$route.query.voucherno ?? "",
        voucher_type: this.$route.query.voucher_type ?? "",
        from_date: this.$route.query.from_date ?? "",
        to_date: this.$route.query.to_date ?? "",
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

    getVoucherTypeBadge(type) {
      if (!type) return "bg-secondary text-white";
      const t = type.toLowerCase();
      if (t === "credit") return "bg-success text-white";
      if (t === "debit") return "bg-danger text-white";
      if (t === "journal") return "bg-primary text-white";
      if (t === "contra") return "bg-warning text-dark";
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
      this.search_data.voucherno = "";
      this.search_data.voucher_type = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
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
.voucher-info-cell {
  cursor: pointer;
}
</style>