<template>
  <index-page :show_status="false">
    <!-- 🔍 Advanced Search / Filter Drawer Panel -->
    <template v-slot:search-field>
      <Input
        v-model="search_data.audit_number"
        field="search_data.audit_number"
        title="Audit Number"
        placeholder="e.g. WST-..."
        col="2"
      />

      <v-select-container title="Auditor (Employee)" field="search_data.auditor_id" col="3">
        <v-select
          v-model="search_data.auditor_id"
          label="full_name"
          :reduce="(obj) => obj.id"
          :options="$root.global.employees"
          placeholder="-- Select Auditor --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Filter By Item" field="search_data.item_id" col="3">
        <v-select
          v-model="search_data.item_id"
          label="title"
          :reduce="(obj) => obj.id"
          :options="items"
          placeholder="-- Select Item --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <div class="col-md-2 col-sm-12">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Audit Status') }}</label>
        <select v-model="search_data.status" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Statuses --') }}</option>
          <option value="approved">{{ $t('Approved') }}</option>
          <option value="pending">{{ $t('Pending Review') }}</option>
        </select>
      </div>

      <date-picker
        id="searchfromauditdate"
        v-model="search_data.from_date"
        field="search_data.from_date"
        title="From Audit Date"
        placeholder="From Date"
        col="2"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtoauditdate"
        v-model="search_data.to_date"
        field="search_data.to_date"
        title="To Audit Date"
        placeholder="To Date"
        col="2"
        :req="false"
        :disablePastDates="search_data.from_date"
      ></date-picker>
    </template>

    <!-- 📋 High-Standard Informative Column Renderers -->
    <!-- Column 1: Audit Ref & Date -->
    <template v-slot:audit_info="{ item }">
      <td class="audit-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div class="audit-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3" style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;">
            <i class="fas fa-dumpster"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important;">
              {{ item.audit_number || ('WST-' + String(item.id).padStart(5, '0')) }}
            </div>
            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span><i class="far fa-calendar-alt text-secondary me-2" style="font-size: 11px;"></i>{{ item.audit_date || 'N/A' }}</span>
              <span v-if="item.branch" class="badge bg-light text-dark border py-0 px-1.5" style="font-size: 10px;">
                <i class="fas fa-code-branch me-1 text-primary"></i>{{ item.branch.title }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Auditor & Branch -->
    <template v-slot:auditor_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="fw-bold text-dark auditor-name">
          <i class="fas fa-user-tie text-secondary me-2" style="font-size: 11px;"></i>
          {{ item.auditor?.title || item.auditor?.full_name || item.audited_by || $t('Store Auditor') }}
        </div>
        <div class="small text-muted mt-0.5" v-if="item.auditor && item.audited_by && item.audited_by !== item.auditor.full_name">
          <i class="fas fa-users text-info me-2" style="font-size: 10px;"></i>{{ $t('Team') }}: {{ item.audited_by }}
        </div>
        <div class="small text-muted mt-0.5" v-if="item.creator">
          <i class="fas fa-user-edit text-muted me-2" style="font-size: 10px;"></i>{{ $t('Created by') }}: {{ item.creator.name }}
        </div>
      </td>
    </template>

    <!-- Column 3: Scrapped Items Summary -->
    <template v-slot:items_summary="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <div class="d-inline-flex flex-column align-items-center">
          <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 fw-bold">
            <i class="fas fa-boxes text-primary me-1"></i>
            {{ item.wastage_details_count !== undefined ? item.wastage_details_count : (item.wastage_details ? item.wastage_details.length : 0) }} {{ $t('Line(s)') }}
          </span>
          <span class="small text-muted mt-0.5 font-monospace">
            {{ $t('Total Qty') }}: <strong class="text-dark">{{ item.total_qty || 0 }}</strong>
          </span>
        </div>
      </td>
    </template>

    <!-- Column 4: Loss Valuation -->
    <template v-slot:loss_summary="{ item }">
      <td class="text-end" @click="pushToViewRoute(item.id)">
        <div class="fw-bold font-monospace text-danger fs-6">
          ৳ {{ formatNum(item.total_loss_amount || 0) }}
        </div>
        <div class="small text-muted" style="font-size: 11px;">
          <i class="fas fa-arrow-down-wide-short text-danger me-1"></i>{{ $t('Inventory Loss') }}
        </div>
      </td>
    </template>

    <!-- Column 5: Approval Status -->
    <template v-slot:approval_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <div class="d-flex flex-column align-items-center gap-1">
          <span
            v-if="item.approved_by || item.status === 'approved'"
            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2.5 font-monospace"
          >
            <i class="fas fa-check-circle me-1"></i>{{ $t('Approved') }}
          </span>
          <span
            v-else
            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 py-1 px-2.5 font-monospace"
          >
            <i class="fas fa-clock me-1"></i>{{ $t('Pending Review') }}
          </span>

          <small v-if="item.approved_admin" class="text-muted" style="font-size: 10px;">
            <i class="fas fa-user-check me-1"></i>{{ item.approved_admin.name }}
          </small>
        </div>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "wastage";

const tableColumns = [
  { field: "audit_info", title: "Audit Ref & Date" },
  { field: "auditor_info", title: "Auditor & Branch" },
  { field: "items_summary", title: "Scrapped Items", align: "center" },
  { field: "loss_summary", title: "Loss Valuation", align: "right" },
  { field: "approval_status", title: "Status", align: "center" },
];

const json_fields = {
  "Audit Ref": "audit_number",
  "Audit Date": "audit_date",
  "Auditor": "audited_by",
  "Total Qty": "total_qty",
  "Total Loss Amount": "total_loss_amount",
  "Status": "status",
  "Approved Date": "approved_date",
};

export default {
  data() {
    return {
      page_title: "Wastage Management",
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
        routes: {
          canEdit: (item) => !item.approved_by && item.status !== 'approved',
          editDisabledReason: () => "Wastage audit is already approved. Editing is locked.",
          canDestroy: (item) => !item.approved_by && item.status !== 'approved',
          deleteDisabledReason: () => "Wastage audit is already approved. Deletion is locked.",
        },
        datas: [],
        meta: [],
        links: [],
        summary: null,
      },
      localStats: {
        total_audits: 0,
        approved_count: 0,
        pending_count: 0,
        total_qty: 0,
        total_loss: 0,
        this_month_loss: 0,
      },
      items: [],
    };
  },

  computed: {
    stats() {
      if (this.table && this.table.summary) {
        return this.table.summary;
      }
      return this.localStats;
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
    formatNum(val) {
      const num = parseFloat(val);
      if (isNaN(num)) return "0.00";
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
      this.search_data.audit_number = "";
      this.search_data.auditor_id = "";
      this.search_data.item_id = "";
      this.search_data.from_date = "";
      this.search_data.to_date = "";
    },

    getItems() {
      axios.get("item?allData=true")
        .then((response) => {
          this.items = response.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },
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

<style scoped>
.wastage_kpi_deck .kpi_card {
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background: #ffffff;
  border-left: 4px solid transparent !important;
}

.wastage_kpi_deck .kpi_card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07) !important;
}

.wastage_kpi_deck .kpi_blue {
  border-left-color: #112C47 !important;
}

.wastage_kpi_deck .kpi_purple {
  border-left-color: #6366f1 !important;
}

.wastage_kpi_deck .kpi_red {
  border-left-color: #ef4444 !important;
}

.wastage_kpi_deck .kpi_cyan {
  border-left-color: #06b6d4 !important;
}

.kpi_icon_box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.audit-info-cell {
  cursor: pointer;
}

.auditor-name {
  color: #112C47;
}
</style>
