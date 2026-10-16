<template>
  <index-page :show_status="false">
    <!-- 📊 Executive KPI Statistics Cards Deck -->
    <template v-slot:header-summary>
      <div class="row g-2 mb-3 client_kpi_deck">
        <!-- Card 1: Total Clients -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_blue">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Total Clients') }}</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.total_clients || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1 d-flex flex-wrap gap-1">
                  <span class="badge bg-success bg-opacity-10 text-success" :title="$t('Active Clients')">
                    <i class="fas fa-check-circle me-1"></i>{{ $t('Active') }}: {{ stats.active_clients || 0 }}
                  </span>
                  <span class="badge bg-danger bg-opacity-10 text-danger" :title="$t('Deactive Clients')">
                    <i class="fas fa-times-circle me-1"></i>{{ $t('Inactive') }}: {{ stats.deactive_clients || 0 }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(17, 44, 71, 0.1); color: #112C47;">
                <i class="fas fa-users fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Retail Customers -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_cyan">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Retail Clients') }}</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.retail_clients || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-info bg-opacity-10 text-dark">
                    <i class="fas fa-user me-1 text-info"></i> {{ $t('B2C / Walk-in') }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box bg-info bg-opacity-10 text-info">
                <i class="fas fa-shopping-bag fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Wholesale / Corporate -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_purple">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Wholesale Clients') }}</div>
                <div class="fs-4 fw-bolder text-dark font-monospace mt-1">
                  {{ Number(stats.wholesale_clients || 0).toLocaleString() }}
                </div>
                <div class="small text-muted mt-1">
                  <span class="badge bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-boxes me-1"></i> {{ $t('B2B / Organizations') }}
                  </span>
                </div>
              </div>
              <div class="kpi_icon_box" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fas fa-building fa-lg"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Total Opening Receivables -->
        <div class="col-xl-3 col-sm-6">
          <div class="card border-0 shadow-sm kpi_card h-100 kpi_amber">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <div class="text-muted text-uppercase fw-semibold small tracking-wider">{{ $t('Total Opening Due') }}</div>
                <div class="fs-4 fw-bolder text-danger font-monospace mt-1">
                  ৳ {{ formatAmount(stats.total_due || 0) }}
                </div>
                <div class="small text-muted mt-1">
                  <i class="fas fa-coins text-warning me-1"></i> {{ $t('Recorded Opening Balances') }}
                </div>
              </div>
              <div class="kpi_icon_box bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-hand-holding-usd fa-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🔍 Advanced Multi-Criteria Filter Controls -->
    <template v-slot:search-field>
      <!-- Row 1: Customer Type, District, Area, Status -->
      <v-select-container title="Customer Type" field="search_data.customer_type" col="3">
        <v-select v-model="search_data.customer_type" label="title" :reduce="(obj) => obj.value"
          :options="customerTypeOptions" :placeholder="$t('-- All Customer Types --')" :closeOnSelect="true"></v-select>
      </v-select-container>

      <v-select-container title="Select District" field="search_data.district_id" col="3">
        <v-select v-model="search_data.district_id" label="district_name" :reduce="(obj) => obj.id"
          :options="$root.global.districts" :placeholder="$t('-- Select District --')" :closeOnSelect="true"
          @update:modelValue="onDistrictChange"></v-select>
      </v-select-container>

      <v-select-container title="Select Area" field="search_data.area_id" col="3">
        <v-select v-model="search_data.area_id" label="area_name" :reduce="(obj) => obj.id" :options="areas"
          :placeholder="$t('-- Select Area --')" :closeOnSelect="true"></v-select>
      </v-select-container>

      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Account Status') }}</label>
        <select v-model="search_data.status" class="form-select form-select-sm">
          <option value="">{{ $t('-- All Statuses --') }}</option>
          <option value="active">{{ $t('Active') }}</option>
          <option value="deactive">{{ $t('Deactive') }}</option>
        </select>
      </div>

      <!-- Row 2: Date Filters & Quick Presets -->
      <date-picker id='date1' v-model='search_data.from_reg_date' field='search_data.from_reg_date' title='From Reg Date'
        placeholder='From Reg Date' col='3' :req='false'></date-picker>

      <date-picker id='date2' v-model='search_data.to_reg_date' field='search_data.to_reg_date' title='To Reg Date'
        placeholder='To Reg Date' col='3' :req='false'></date-picker>
    </template>

    <!-- 📋 High-Standard Custom Column Rendering -->
    <!-- Column 1: Client Profile (Avatar + Name + Org + Client ID) -->
    <template v-slot:client_profile="{ item }">
      <td class="client-profile-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-2.5">
          <div class="client-avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0"
            :style="{ background: getAvatarColor(item.name || item.org_name) }">
            {{ getInitials(item.name || item.org_name) }}
          </div>
          <div class="min-w-0">
            <div class="fw-bold text-dark client-name d-flex align-items-center gap-1.5 flex-wrap">
              <span class="text-truncate">{{ item.name || $t('Unnamed Client') }}</span>
              <span class="badge bg-light text-secondary border font-monospace px-1.5 py-0.5" style="font-size: 10px;">
                #{{ item.clientid || item.id }}
              </span>
            </div>
            <div class="text-muted small text-truncate mt-0.5" v-if="item.org_name && item.org_name !== item.name">
              <i class="fas fa-building text-secondary me-1"></i>{{ item.org_name }}
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Customer Type Badge -->
    <template v-slot:customer_type="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span class="badge px-2.5 py-1 text-uppercase fw-semibold"
          :class="item.customer_type === 'wholesale' ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-info bg-opacity-10 text-dark border border-info border-opacity-25'">
          <i :class="item.customer_type === 'wholesale' ? 'fas fa-boxes me-1' : 'fas fa-user me-1'"></i>
          {{ item.customer_type === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
        </span>
      </td>
    </template>

    <!-- Column 3: Contact Info (Mobile + Email) -->
    <template v-slot:contact_info="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div v-if="item.mobile">
          <a :href="'tel:' + item.mobile" @click.stop class="text-decoration-none fw-semibold text-dark small d-inline-flex align-items-center gap-1.5 hover-underline">
            <i class="fas fa-phone-alt text-success" style="font-size: 11px;"></i>
            <span>{{ item.mobile }}</span>
          </a>
        </div>
        <div v-if="item.email" class="text-muted small text-truncate d-inline-flex align-items-center gap-1.5 mt-0.5" style="max-width: 190px;" :title="item.email">
          <i class="fas fa-envelope text-primary" style="font-size: 11px;"></i>
          <span>{{ item.email }}</span>
        </div>
        <div v-if="!item.mobile && !item.email" class="text-muted small fst-italic">--</div>
      </td>
    </template>

    <!-- Column 4: Location / Area -->
    <template v-slot:location="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="fw-semibold small text-dark d-flex align-items-center gap-1" v-if="(item.area && item.area.area_name) || (item.district && item.district.district_name)">
          <i class="fas fa-map-marker-alt text-danger flex-shrink-0" style="font-size: 11px;"></i>
          <span class="text-truncate">{{ [item.area ? item.area.area_name : '', item.district ? item.district.district_name : ''].filter(Boolean).join(', ') }}</span>
        </div>
        <div class="text-muted small text-truncate mt-0.5" style="max-width: 220px;" v-if="item.address" :title="item.address">
          {{ item.address }}
        </div>
        <div v-if="!item.address && !(item.area && item.area.area_name) && !(item.district && item.district.district_name)" class="text-muted small fst-italic">--</div>
      </td>
    </template>

    <!-- Column 5: Previous / Opening Due -->
    <template v-slot:previous_due="{ item }">
      <td class="text-end font-monospace" @click="pushToViewRoute(item.id)">
        <span :class="Number(item.previous_due) > 0 ? 'text-danger fw-bold' : 'text-muted'">
          ৳ {{ Number(item.previous_due || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
        </span>
      </td>
    </template>

    <!-- Column 6: Reg Date -->
    <template v-slot:reg_date="{ item }">
      <td class="text-center small text-nowrap" @click="pushToViewRoute(item.id)">
        <span class="text-secondary">
          <i class="far fa-calendar-alt me-1 text-muted"></i>{{ item.reg_date || 'N/A' }}
        </span>
      </td>
    </template>

    <!-- Column 7: Status -->
    <template v-slot:status="{ item }">
      <td class="text-center" @click="pushToViewRoute(item.id)">
        <span class="status approved" v-if="item.status === 'active' || item.status == 1">{{ $t('ACTIVE') }}</span>
        <span class="status cancel" v-else>{{ $t('DEACTIVE') }}</span>
      </td>
    </template>
  </index-page>
</template>

<script>

const model = "client";

const tableColumns = [
  { field: "client_profile", title: "Customer Profile" },
  { field: "customer_type", title: "Type", align: "center" },
  { field: "contact_info", title: "Contact Details" },
  { field: "location", title: "Location / Address" },
  { field: "previous_due", title: "Opening Due", align: "right" },
  { field: "reg_date", title: "Reg Date", align: "center" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Client ID": "clientid",
  "Reg Date": "reg_date",
  "Name": "name",
  "Customer Type": "customer_type",
  "Org Name": "org_name",
  "Mobile": "mobile",
  "Email": "email",
  "Address": "address",
  "District": "district.district_name",
  "Area": "area.area_name",
  "Previous Due": "previous_due",
  "Status": "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: {
        default: "Search All Fields",
        clientid: "Client ID",
        name: "Customer Name",
        org_name: "Organization",
        mobile: "Mobile Number",
        email: "Email Address"
      },
      customerTypeOptions: [
        { title: "All Customer Types", value: "" },
        { title: "Retail", value: "retail" },
        { title: "Wholesale", value: "wholesale" },
      ],
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        customer_type: this.$route.query.customer_type ?? "",
        district_id: this.$route.query.district_id ? Number(this.$route.query.district_id) : "",
        area_id: this.$route.query.area_id ? Number(this.$route.query.area_id) : "",
        from_reg_date: this.$route.query.from_reg_date ?? "",
        to_reg_date: this.$route.query.to_reg_date ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      localStats: {
        total_clients: 0,
        active_clients: 0,
        deactive_clients: 0,
        retail_clients: 0,
        wholesale_clients: 0,
        total_due: 0,
      },
      areas: [],
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

  watch: {
    "search_data.district_id": "getareas",
  },

  methods: {
    getareas() {
      var district_id = this.search_data.district_id;
      if (!district_id) {
        this.areas = [];
        return;
      }
      axios.get("/getareas/" + district_id).then((res) => {
        this.areas = res.data;
      });
    },

    onDistrictChange(val) {
      this.search_data.area_id = "";
      if (val) {
        this.getareas();
      } else {
        this.areas = [];
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
      this.get_paginate(this.model, cleanParams);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.customer_type = "";
      this.search_data.district_id = "";
      this.search_data.area_id = "";
      this.search_data.from_reg_date = "";
      this.search_data.to_reg_date = "";
      this.areas = [];
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

    getInitials(name) {
      if (!name) return "C";
      const parts = name.trim().split(/\s+/);
      if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
      return (parts[0][0] + parts[1][0]).toUpperCase();
    },

    getAvatarColor(name) {
      if (!name) return "#112C47";
      const colors = [
        "#112C47", "#2563eb", "#0891b2", "#059669",
        "#7c3aed", "#d97706", "#dc2626", "#475569"
      ];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      const index = Math.abs(hash) % colors.length;
      return colors[index];
    },

    formatAmount(amount) {
      if (!amount || isNaN(amount)) return "0.00";
      return Number(amount).toLocaleString("en-BD", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} Management`;
    if (this.search_data.district_id) {
      this.getareas();
    }
    this.search();
  },

  validators: {},
};
</script>

<style scoped>
.client_kpi_deck .kpi_card {
  border-radius: 8px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background: #ffffff;
  border-left: 4px solid transparent !important;
}

.client_kpi_deck .kpi_card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07) !important;
}

.client_kpi_deck .kpi_blue {
  border-left-color: #112C47 !important;
}

.client_kpi_deck .kpi_cyan {
  border-left-color: #06b6d4 !important;
}

.client_kpi_deck .kpi_purple {
  border-left-color: #6366f1 !important;
}

.client_kpi_deck .kpi_amber {
  border-left-color: #f59e0b !important;
}

.kpi_icon_box {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tracking-wider {
  letter-spacing: 0.05em;
}

.client-profile-cell {
  cursor: pointer;
}

.client-avatar {
  width: 36px;
  height: 36px;
  font-size: 12px;
  letter-spacing: 0.5px;
}

.client-name {
  font-size: 13.5px;
}

.hover-underline:hover {
  text-decoration: underline !important;
}
</style>