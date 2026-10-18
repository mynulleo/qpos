<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Branch" field="search_data.branch_id" col="3">
        <v-select
          v-model="search_data.branch_id"
          label="title"
          :reduce="(obj) => obj.id"
          :options="$root.global.branches"
          :placeholder="$t('-- All Branches --')"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Warehouse Name & Code -->
    <template v-slot:warehouse_info="{ item }">
      <td class="warehouse-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="wh-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-warehouse"></i>
          </div>
          <div>
            <div class="fw-bold font-monospace text-dark" style="color: #112C47 !important; font-size: 13.5px;">
              {{ item.name }}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span class="badge bg-light text-secondary border font-monospace py-0 px-1.5" style="font-size: 10.5px;">
                Code: {{ item.code || ('#WH-' + item.id) }}
              </span>
              <span v-if="item.capacity" class="small text-muted font-monospace" style="font-size: 11px;">
                <i class="fas fa-boxes me-1.5 text-secondary"></i>Cap: {{ item.capacity }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Contact Person & Mobile -->
    <template v-slot:contact_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex flex-column font-monospace" style="font-size: 12px;">
          <div class="fw-semibold text-dark">
            <i class="fas fa-user-circle text-primary me-2" style="font-size: 11px;"></i>
            {{ item.contact_person || 'N/A' }}
          </div>
          <div v-if="item.contact_person_phone || item.phone" class="text-muted small mt-0.5">
            <i class="fas fa-phone text-success me-2" style="font-size: 10px;"></i>
            {{ item.contact_person_phone || item.phone }}
          </div>
        </div>
      </td>
    </template>

    <!-- Column 3: Branch & Location -->
    <template v-slot:branch_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div>
          <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 11px;">
            <i class="fas fa-code-branch me-1.5"></i>
            {{ item.branch ? item.branch.title : 'All Branches' }}
          </span>
          <div v-if="item.address" class="text-muted small text-truncate mt-1" style="max-width: 180px; font-size: 11px;" :title="item.address">
            <i class="fas fa-map-marker-alt text-secondary me-2"></i>{{ item.address }}
          </div>
        </div>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:warehouse_status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span
          class="badge rounded-pill px-2.5 py-1 font-monospace"
          :class="item.status === 'active' ? 'bg-success text-white' : 'bg-secondary text-white'"
          style="font-size: 11px;"
        >
          <i class="fas fa-circle me-1" style="font-size: 7px;"></i>
          {{ item.status === 'active' ? $t('Active') : $t('Inactive') }}
        </span>
      </td>
    </template>
  </index-page>
</template>

<script>
const model = "warehouse";

const tableColumns = [
  { field: "warehouse_info", title: "Warehouse Name & Code" },
  { field: "contact_info", title: "Contact Person" },
  { field: "branch_info", title: "Branch Location" },
  { field: "warehouse_status", title: "Status", align: "center" },
];

const json_fields = {
  "Warehouse Name": "name",
  "Code": "code",
  "Contact Person": "contact_person",
  "Mobile": "contact_person_phone",
  "Phone": "phone",
  "Email": "email",
  "Address": "address",
  "Capacity": "capacity",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        name: "Warehouse Name",
        code: "Code",
        contact_person: "Contact Person",
        phone: "Phone",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        branch_id: this.$route.query.branch_id ?? "",
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
    search() {
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.branch_id = "";
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
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.warehouse-info-cell {
  cursor: pointer;
}
.wh-icon-badge {
  box-shadow: 0 2px 4px rgba(17, 44, 71, 0.2);
}
</style>
