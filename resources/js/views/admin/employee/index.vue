<template>
  <index-page :show_status="false">
    <template v-slot:search-field>
      <v-select-container title="Select Designation" field="search_data.designation_id" col="3">
        <v-select
          v-model="search_data.designation_id"
          label="title"
          :reduce="(obj) => obj.id"
          :options="$root.global.designations"
          placeholder="-- All Designations --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <v-select-container title="Select Branch" field="search_data.branch_id" col="3">
        <v-select
          v-model="search_data.branch_id"
          label="branch_name"
          :reduce="(obj) => obj.id"
          :options="$root.global.branches"
          placeholder="-- All Branches --"
          :closeOnSelect="true"
        ></v-select>
      </v-select-container>

      <date-picker
        id="searchfromjoiningdate"
        v-model="search_data.from_joining_date"
        field="search_data.from_joining_date"
        title="From Joining Date"
        placeholder="From Joining Date"
        col="3"
        :req="false"
      ></date-picker>

      <date-picker
        id="searchtojoiningdate"
        v-model="search_data.to_joining_date"
        field="search_data.to_joining_date"
        title="To Joining Date"
        placeholder="To Joining Date"
        col="3"
        :req="false"
        :disablePastDates="search_data.from_joining_date"
      ></date-picker>
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Employee Identity & Designation -->
    <template v-slot:employee_info="{ item }">
      <td class="employee-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar-box rounded-circle overflow-hidden flex-shrink-0 border me-3" style="width: 38px; height: 38px; background-color: #f0f4f8; margin-right: 12px;">
            <img v-if="item.image" :src="item.image" :alt="item.full_name" class="w-100 h-100 object-fit-cover" />
            <div v-else class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold font-monospace" style="background-color: #112C47; font-size: 14px;">
              {{ (item.full_name || 'E').charAt(0).toUpperCase() }}
            </div>
          </div>
          <div>
            <div class="fw-bold text-dark text-hover-primary" style="font-size: 13.5px; color: #112C47 !important;">
              {{ item.full_name }}
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap mt-0.5">
              <span class="badge bg-light text-secondary border font-monospace py-0 px-1.5" style="font-size: 10.5px;">
                #{{ item.empid || item.id }}
              </span>
              <span v-if="item.designation" class="badge rounded-pill px-2 py-0.5 font-monospace" style="background-color: #E6F7FF; color: #0050B3; border: 1px solid #91D5FF; font-size: 10.5px;">
                {{ item.designation.title }}
              </span>
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Contact Details -->
    <template v-slot:contact_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="d-flex flex-column gap-0.5 font-monospace" style="font-size: 12px;">
          <div v-if="item.mobile" class="text-dark fw-semibold">
            <i class="fas fa-phone-alt text-primary me-2" style="font-size: 10px;"></i>
            {{ item.mobile }}
          </div>
          <div v-if="item.email" class="text-muted small">
            <i class="far fa-envelope me-2" style="font-size: 10px;"></i>
            {{ item.email }}
          </div>
          <div v-if="item.nid" class="text-muted small" style="font-size: 10.5px;">
            <i class="far fa-id-card me-2 text-secondary"></i>
            NID: {{ item.nid }}
          </div>
        </div>
      </td>
    </template>

    <!-- Column 3: Branch & Joining Date -->
    <template v-slot:branch_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div>
          <div class="fw-bold text-dark font-monospace" style="font-size: 12.5px;">
            <i class="fas fa-code-branch text-primary me-2" style="font-size: 11px;"></i>
            {{ item.offic_branch ? item.offic_branch.branch_name : 'Main Branch' }}
          </div>
          <div class="small text-muted font-monospace mt-0.5" style="font-size: 11px;">
            <i class="far fa-calendar-alt text-secondary me-2"></i>
            Joined: {{ item.joining_date || 'N/A' }}
          </div>
        </div>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:employee_status="{ item }">
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
const model = "employee";

const tableColumns = [
  { field: "employee_info", title: "Employee & Designation" },
  { field: "contact_info", title: "Contact Information" },
  { field: "branch_info", title: "Branch & Joining Date" },
  { field: "employee_status", title: "Status", align: "center" },
];

const json_fields = {
  "Emp ID": "empid",
  "Full Name": "full_name",
  "Designation": "designation.title",
  "Branch": "offic_branch.branch_name",
  "Joining Date": "joining_date",
  "Mobile": "mobile",
  "Email": "email",
  "NID": "nid",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", full_name: "Name", mobile: "Mobile", email: "Email", nid: "NID" },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        designation_id: this.$route.query.designation_id ?? "",
        branch_id: this.$route.query.branch_id ?? "",
        from_joining_date: this.$route.query.from_joining_date ?? "",
        to_joining_date: this.$route.query.to_joining_date ?? "",
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
      this.search_data.designation_id = "";
      this.search_data.branch_id = "";
      this.search_data.from_joining_date = "";
      this.search_data.to_joining_date = "";
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
.employee-info-cell {
  cursor: pointer;
}
.avatar-box {
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>