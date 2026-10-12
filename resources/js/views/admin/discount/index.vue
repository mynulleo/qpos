<template>
  <index-page :show_status="false">
    <template v-slot:button>
      <AddOrBackButton :route="model + '.create'" :portion="model" :icon="'plus'" />
      <router-link
        v-if="!$root.checkPermission(model + '.create')"
        :to="{ name: model + '.create' }"
        class="back_or_add_btn"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        :data-bs-title="$t('Add')"
        v-x-tooltip
      >
        <span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="icon icon-tabler icons-tabler-outline icon-tabler-plus"
          >
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M12 5l0 14" />
            <path d="M5 12l14 0" />
          </svg>
        </span>
        {{ $t('Add') }}
      </router-link>
    </template>
    <template v-slot:search-field>
      <v-select-container :title="$t('Discount Scope')" field="search_data.discount_type" col="3">
        <v-select
          v-model="search_data.discount_type"
          label="title"
          :reduce="(obj) => obj.value"
          :options="discountTypeOptions"
          :placeholder="$t('-- All Scopes --')"
          :closeOnSelect="true"
        />
      </v-select-container>

      <v-select-container :title="$t('Applicable On')" field="search_data.applicable_on" col="3">
        <v-select
          v-model="search_data.applicable_on"
          label="title"
          :reduce="(obj) => obj.value"
          :options="applicableOptions"
          :placeholder="$t('-- All Price Natures --')"
          :closeOnSelect="true"
        />
      </v-select-container>

      <v-select-container :title="$t('Status')" field="search_data.status" col="2">
        <v-select
          v-model="search_data.status"
          label="title"
          :reduce="(obj) => obj.value"
          :options="statusOptions"
          :placeholder="$t('-- All Status --')"
          :closeOnSelect="true"
        />
      </v-select-container>
    </template>

    <!-- Column Slots for translated and beautifully formatted table cells -->
    <template v-slot:discount_type="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span v-if="item.discount_type === 'category' || item.scope === 'category'" class="badge bg-warning text-dark border">
          <i class="fas fa-folder me-1"></i>{{ $t('Category Wise') }}
        </span>
        <span v-else class="badge bg-info text-dark border">
          <i class="fas fa-boxes me-1"></i>{{ $t('Item Wise') }}
        </span>
      </td>
    </template>

    <template v-slot:target_title="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <template v-if="item.scope === 'category' || item.discount_type === 'category'">
          <strong>{{ item.category ? item.category.title : $t('N/A') }}</strong>
          <span class="text-muted small ms-1">({{ $t('Category') }})</span>
        </template>
        <template v-else-if="item.scope === 'item' || item.discount_type === 'item'">
          <template v-if="item.items && item.items.length === 1">
            <strong>{{ item.target_title }}</strong>
          </template>
          <template v-else-if="item.items && item.items.length > 1">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary font-monospace">
              {{ $locale === 'bn' ? $bnNum(item.items.length) : item.items.length }} {{ $t('Items Selected') }}
            </span>
          </template>
          <template v-else>
            {{ item.target_title || $t('N/A') }}
          </template>
        </template>
        <template v-else>
          {{ item.target_title || $t('All') }}
        </template>
      </td>
    </template>

    <template v-slot:applicable_on="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span class="badge bg-light text-dark border px-2 py-1">
          {{ item.applicable_on === 'both' ? $t('Retail & Wholesale') : (item.applicable_on === 'retail' ? $t('Retail') : (item.applicable_on === 'wholesale' ? $t('Wholesale') : $t('Both'))) }}
        </span>
      </td>
    </template>

    <template v-slot:discount_display="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold font-monospace">
          {{ formatDiscountDisplay(item) }}
        </span>
      </td>
    </template>

    <template v-slot:validity="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span class="small font-monospace">
          {{ formatValidity(item) }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "discount";

const tableColumns = [
  { field: "name", title: "Campaign Name" },
  { field: "discount_type", title: "Scope", align: "center" },
  { field: "target_title", title: "Category / Item" },
  { field: "applicable_on", title: "Applicable On", align: "center" },
  { field: "discount_display", title: "Discount", align: "center" },
  { field: "validity", title: "Validity Period", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Campaign Name": "name",
  "Scope": "discount_type",
  "Category / Item": "target_title",
  "Applicable On": "applicable_on",
  "Discount": "discount_display",
  "Validity Period": "validity",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "Discount Management",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        name: "Campaign Name",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        discount_type: this.$route.query.discount_type ?? "",
        applicable_on: this.$route.query.applicable_on ?? "",
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
  computed: {
    discountTypeOptions() {
      return [
        { title: this.$t("Category Wise"), value: "category" },
        { title: this.$t("Item Wise"), value: "item" },
      ];
    },
    applicableOptions() {
      return [
        { title: this.$t("Retail"), value: "retail" },
        { title: this.$t("Wholesale"), value: "wholesale" },
        { title: this.$t("Both (Retail & Wholesale)"), value: "both" },
      ];
    },
    statusOptions() {
      return [
        { title: this.$t("Active"), value: "active" },
        { title: this.$t("Deactive"), value: "deactive" },
      ];
    },
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
    pushToViewRoute(id) {
      if (this.$root.checkPermission(this.model + ".show")) {
        this.$router.push({
          name: this.model + ".show",
          params: { id: id },
          query: { page: this.$route.query.page },
        });
      }
    },
    formatDiscountDisplay(item) {
      if (!item) return "-";
      const type = item.discount_type || item.unit;
      const val = item.discount_value;
      if (type === "percentage") {
        const numStr = String(parseFloat(val));
        return (this.$locale === "bn" ? this.$bnNum(numStr) : numStr) + "%";
      }
      const numFormatted = Number(val || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      return "৳ " + (this.$locale === "bn" ? this.$bnNum(numFormatted) : numFormatted);
    },
    formatValidity(item) {
      if (!item || (!item.valid_from && !item.valid_to)) return "-";
      const formatDateStr = (dateStr) => {
        if (!dateStr) return "-";
        try {
          const d = new Date(dateStr);
          const dd = String(d.getDate()).padStart(2, "0");
          const mm = String(d.getMonth() + 1).padStart(2, "0");
          const yyyy = d.getFullYear();
          const str = `${dd}-${mm}-${yyyy}`;
          return this.$locale === "bn" ? this.$bnNum(str) : str;
        } catch (e) {
          return dateStr;
        }
      };
      const from = formatDateStr(item.valid_from);
      const to = formatDateStr(item.valid_to);
      return `${from} ${this.$t("to")} ${to}`;
    },
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
      this.search_data.discount_type = "";
      this.search_data.applicable_on = "";
    },
  },
  created() {
    this.getRouteName(this.model);
    this.setBreadcrumbs(this.model, "index", "Discount");
    this.page_title = "Discount Management";
    this.search();
  },
};
</script>
