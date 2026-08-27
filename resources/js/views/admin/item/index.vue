<template>
  <index-page>
    <template v-slot:button>
      <div class="d-flex gap-2 align-items-center">
        <router-link :to="{ name: 'bulkdataimport.item' }" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 shadow-sm fw-semibold">
          <i class="fas fa-file-import"></i> Bulk Import
        </router-link>
        <router-link :to="{ name: 'pos.labelprint' }" class="btn btn-sm btn-info text-white d-inline-flex align-items-center gap-1 shadow-sm">
          <i class="fas fa-barcode"></i> Label Print
        </router-link>
        <AddOrBackButton :route="model + '.create'" :portion="model" :icon="'plus'" />
      </div>
    </template>

    <template v-slot:search-field>
      <v-select-container title="Category" field="search_data.category_id" col="3">
        <v-select v-model="search_data.category_id" label="title" :reduce="(obj) => obj.id" :options="categories"
          placeholder="--Select Category--" :closeOnSelect="true"></v-select>
      </v-select-container>
      <v-select-container title="Brand" field="search_data.brand_id" col="3">
        <v-select v-model="search_data.brand_id" label="title" :reduce="(obj) => obj.id" :options="brands"
          :placeholder="search_data.category_id ? '--Select Brand--' : '--All Brands--'" :closeOnSelect="true"></v-select>
      </v-select-container>
      <v-select-container title="Color" field="search_data.color_id" col="3">
        <v-select v-model="search_data.color_id" label="title" :reduce="(obj) => obj.id" :options="colors"
          placeholder="--Select Color--" :closeOnSelect="true"></v-select>
      </v-select-container>
      <v-select-container title="Size" field="search_data.size_id" col="3" v-if="!isElectronicsShop">
        <v-select v-model="search_data.size_id" label="title" :reduce="(obj) => obj.id" :options="sizes"
          placeholder="--Select Size--" :closeOnSelect="true"></v-select>
      </v-select-container>
    </template>
  </index-page>
</template>

<script>

const model = "item";

const tableColumns = [
  { field: "image", title: "Image", image: true, imgWidth: "30px", align: "center" },
  { field: "barcode", title: "Barcode" },
  { field: "category_id", title: "Category", subfield: "category.title" },
  { field: "brand_id", title: "Brand", subfield: "brand.title" },
  { field: "title", title: "Title" },
  { field: "unit_id", title: "Unit", subfield: "unit.title" },
  { field: "opening_qty", title: "Opening Qty" },
  { field: "opening_rate", title: "Opening Rate" },
  { field: "status", title: "Status", align: "center" },
];

const json_fields = {
  "Barcode": "barcode",
  "Category Id": "category_id",
  "Title": "title",
  "Unit Id": "unit_id",
  "Description": "description",
  "Opening Qty": "opening_qty",
  "Opening Rate": "opening_rate",
};

export default {
  computed: {
    isElectronicsShop() {
      const shopType = this.site?.shop_type || this.$root.site?.shop_type;
      return shopType === 'electronics';
    },
  },
  data() {
    return {
      model: model,
      page_title: "",
      json_fields: json_fields,
      fields_name: { default: "Select One", title: "Title" },
      search_data: {
        pagination: this.$route.query.pagination ?? 10,
        page: this.$route.query.page ?? 1,
        field_name: this.$route.query.field_name ?? "",
        value: this.$route.query.value ?? "",
        status: this.$route.query.status ?? "",
        category_id: this.$route.query.category_id ?? "",
        brand_id: this.$route.query.brand_id ?? "",
        color_id: this.$route.query.color_id ?? "",
        size_id: this.$route.query.size_id ?? "",
      },
      table: {
        columns: tableColumns,
        routes: {},
        datas: [],
        meta: [],
        links: []
      },
      categories: [],
      brands: [],
      colors: [],
      sizes: [],
    };
  },

  watch: {
    'search_data.category_id'(newVal, oldVal) {
      this.getBrands(newVal);
      if (oldVal && newVal !== oldVal) {
        this.search_data.brand_id = "";
      }
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
    search() {
      this.get_paginate(this.model, this.search_data);
    },

    resetSearchData() {
      this.search_data.pagination = 10;
      this.search_data.page = 1;
      this.search_data.field_name = "";
      this.search_data.value = "";
      this.search_data.status = "";
      this.search_data.category_id = "";
      this.search_data.brand_id = "";
      this.search_data.color_id = "";
      this.search_data.size_id = "";
      this.getBrands();
    },
    getCategories() {
      let module = 'Item';
      axios.get(`getcategories/${module}`)
        .then((response) => {
          this.categories = response.data;
        });
    },
    getBrands(categoryId = null) {
      const catId = categoryId || this.search_data.category_id;
      const url = catId ? `getbrands/${catId}` : 'getbrands';
      axios.get(url)
        .then((response) => {
          this.brands = response.data;
        });
    },
    getColorsAndSizes() {
      axios.get('color?allData=true').then(res => { this.colors = res.data; });
      if (!this.isElectronicsShop) {
        axios.get('size?allData=true').then(res => { this.sizes = res.data; });
      }
    },
  },

  created() {
    this.getRouteName(this.model);
    this.page_title = `${this.headline(this.model)} List`;
    this.search();
    this.getCategories();
    this.getBrands(this.search_data.category_id);
    this.getColorsAndSizes();
  },

  validators: {},
};
</script>