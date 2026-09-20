<template>
  <create-form @onSubmit='submit'>
    <Select title='Category' v-model='data.category_id' field='data.category_id' label='title'
      :reduce='(obj) => obj.id' :options='categories' placeholder='--Select Category--' :closeOnSelect='true'
      :required='false' />
    
    <Select title='Brand' v-model='data.brand_id' field='data.brand_id' label='title'
      :reduce='(obj) => obj.id' :options='brands'
      :placeholder="data.category_id ? (brands.length ? '--Select Brand--' : 'No Brand in Category') : '--Select Brand--'"
      :closeOnSelect='true'
      :required='true' />

    <Input v-model='data.title' field='data.title' title='Series Title' :req='true' />
    <Input v-model='data.sorting' field='data.sorting' title='Sorting' :req='true' type="number" />
    <Textarea v-model='data.description' field='data.description' :required='false' title="Description" col="12" />

    <Switch
        v-model='data.status'
        field='data.status'
        title='status'
        on-label='Active'
        off-label='Deactive'
        :req='true'
    ></Switch>
  </create-form>
</template>

<script>
const model = 'series';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: { category_id: null, brand_id: null, title: '', description: '', status: 'active', sorting: 0 },
      categories: [],
      brands: [],
    };
  },

  watch: {
    'data.category_id'(newVal, oldVal) {
      this.getBrands(newVal);
      if (oldVal && newVal !== oldVal) {
        this.data.brand_id = null;
      }
    },
  },

  provide() {
    return {
      validate: this.validation,
    };
  },
  methods: {
    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          console.log(this.validation.allErrors());
          this.$toast(
            'You need to fill ' + error + ' more empty mandatory fields',
            'warning'
          );
          return false;
        }

        if (res) {
          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },
    getCategories() {
      let module = 'Item';
      axios.get(`getcategories/${module}`)
        .then((response) => {
          this.categories = response.data;
        });
    },
    getBrands(categoryId = null) {
      const catId = categoryId || this.data.category_id;
      const url = catId ? `getbrands/${catId}` : `getbrands`;
      axios.get(url)
        .then((response) => {
          this.brands = response.data;
        });
    },
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`).then(() => {
        this.getBrands(this.data.category_id);
      });
    } else {
      this.page_title = this.headline(this.model) + ' Create';
      this.get_sorting(this.ucfirst(this.model));
      this.getBrands();
    }
    this.getCategories();
  },

  validators: {
    'data.brand_id': function (value = null) { return Validator.value(value).required('Brand is required'); },
    'data.title': function (value = null) { return Validator.value(value).required('Title is required'); },
    'data.sorting': function (value = null) {
      return Validator.value(value)
        .digit()
        .regex("^[0-9]*$")
        .required("Sorting is required");
    },
  },
}
</script>
