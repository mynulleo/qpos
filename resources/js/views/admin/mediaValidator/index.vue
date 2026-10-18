<template>
  <index-page :show_status="false">
    <template #search-field>
      <Select
        field="search_data.field_type"
        title="Field Type"
        col="4"
        v-model="search_data.field_type"
        label="name"
        :reduce="(obj) => obj.value"
        :options="field_types"
        placeholder="-- Select Field Type --"
        :closeOnSelect="true"
      />
    </template>

    <!-- 📋 Informative Table Column Renderers -->
    <!-- Column 1: Model Name & ID -->
    <template v-slot:model_name="{ item }">
      <td class="model-info-cell" @click="pushToViewRoute(item.id)">
        <div class="d-flex align-items-center gap-3">
          <div
            class="model-icon-badge rounded-2 d-flex align-items-center justify-content-center text-white flex-shrink-0 me-3"
            style="background-color: #112C47; width: 34px; height: 34px; font-size: 13px; margin-right: 12px;"
          >
            <i class="fas fa-file-shield"></i>
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

    <!-- Column 2: Field Name -->
    <template v-slot:field_name="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-25 font-monospace px-2 py-1">
          <i class="fas fa-code me-1 text-primary"></i>{{ item.field_name }}
        </span>
      </td>
    </template>

    <!-- Column 3: Field Type -->
    <template v-slot:field_type_html="{ item }">
      <td @click="pushToViewRoute(item.id)">
        <span class="badge rounded-pill px-2.5 py-1 font-monospace"
          :class="item.field_type === 'image' ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-info bg-opacity-10 text-info border border-info border-opacity-25'"
          style="font-size: 11px;">
          <i class="fas me-1" :class="item.field_type === 'image' ? 'fa-image' : 'fa-file-alt'"></i>
          {{ item.field_type_html || item.field_type }}
        </span>
      </td>
    </template>

    <!-- Column 4: Max Size -->
    <template v-slot:max_size="{ item }">
      <td class="font-monospace text-dark fw-semibold" @click="pushToViewRoute(item.id)">
        <i class="fas fa-weight-hanging text-muted me-1"></i>
        {{ item.max_size ? item.max_size + ' KB' : '-' }}
      </td>
    </template>

    <!-- Column 5: Status -->
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
const model = "mediaValidator";

const tableColumns = [
  { field: "model_name", title: "Model Name" },
  { field: "field_name", title: "Field Name" },
  { field: "field_type_html", title: "Field Type" },
  { field: "max_size", title: "Max Size" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Model Name": "model_name",
  "Field Name": "field_name",
  "Field Type": "field_type_html",
  "Max Size (KB)": "max_size",
  Status: "status",
};

export default {
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: {
        default: "Select One",
        model_name: "Model Name",
        field_name: "Field Name",
        max_size: "Max Size",
      },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        field_type: this.$route.query.field_type ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: [],
      },
      field_types: [
        { name: "Image", value: "image" },
        { name: "File", value: "file" },
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
      this.search_data.field_type = "";
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
.model-info-cell {
  cursor: pointer;
}
.gap-2\.5 {
  gap: 0.625rem;
}
</style>
