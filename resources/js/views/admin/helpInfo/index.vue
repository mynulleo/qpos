<template>
  <index-page :show_status="false">
    <template #search-field>
      <Select
        field="search_data.page_type"
        title="Page Type"
        col="4"
        v-model="search_data.page_type"
        label="name"
        :reduce="(obj) => obj.value"
        :options="page_type"
        placeholder="-- Select Page Type --"
        :closeOnSelect="true"
      />
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Model Name & ID -->
    <template v-slot:model_name="{ item }">
      <td class="help-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="help-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-question-circle"></i>
          </div>
          <div>
            <div class="fw-bold text-dark font-monospace" style="color: #112C47 !important; font-size: 13.5px;">
              {{ item.model_name }}
            </div>
            <div class="small text-muted font-monospace mt-0.5" style="font-size: 11px;">
              ID: #{{ item.id }}
            </div>
          </div>
        </div>
      </td>
    </template>

    <!-- Column 2: Page Type -->
    <template v-slot:page_type="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge rounded-pill px-2.5 py-1 font-monospace"
          :class="item.page_type === 'index' ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : (item.page_type === 'create' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-info bg-opacity-10 text-info border border-info border-opacity-25')"
          style="font-size: 11px;">
          <i class="fas fa-file-code me-1"></i>
          {{ item.page_type }}
        </span>
      </td>
    </template>

    <!-- Column 3: Description -->
    <template v-slot:plain_description="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <div class="text-truncate text-muted small" style="max-width: 320px;">
          {{ item.plain_description || '-' }}
        </div>
      </td>
    </template>

    <!-- Column 4: Status -->
    <template v-slot:status="{ item }">
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
const model = "helpInfo";

const tableColumns = [
  { field: "model_name", title: "Model Name" },
  { field: "page_type", title: "Page Type" },
  { field: "plain_description", title: "Help Instructions" },
  {
    field: "sorting",
    title: "Sorting",
    sorting: true,
    namespace: "HelpInfo",
    auto: "",
    align: "center",
  },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Model Name": "model_name",
  "Page Type": "page_type",
  Description: "description",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", model_name: "Model Name" },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        page_type: this.$route.query.page_type ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      page_type: [
        { name: "Index Page", value: "index" },
        { name: "Create Page", value: "create" },
        { name: "View Page", value: "view" },
      ],
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
    pushToViewRoute(id) {
      if (this.table.routes.view) {
        this.$router.push({
          name: this.table.routes.view,
          params: { id: id },
        });
      } else {
        this.$router.push(`/${this.model}/${id}`);
      }
    },

    search() {
      this.get_data(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.page_type = "";
      this.search();
    },
  },

  created() {
    this.get_data(this.model, this.search_data);
  },

  validators: {},
};
</script>

<style scoped>
.help-info-cell {
  cursor: pointer;
}
.gap-2\.5 {
  gap: 0.625rem;
}
</style>
