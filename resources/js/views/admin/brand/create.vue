<template>
  <create-form @onSubmit='submit'>
    <Select title='Category' v-model='data.category_id' field='data.category_id' label='title'
      :reduce='(obj) => obj.id' :options='categories' placeholder='--Select Category--' :closeOnSelect='true'
      :required='false' />
    <Input v-model='data.title' field='data.title' title='Title' :req='true' />
    <Input v-model='data.sorting' field='data.sorting' title='Sorting' :req='true' type="number" />
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
const model = 'brand';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: { category_id: null, status: 'active', sorting: 0 },
      categories: [],
    };
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
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + ' Create';
      this.get_sorting(this.ucfirst(this.model));
    }
    this.getCategories();
  },

  validators: {
    'data.title': function (value = null) { return Validator.value(value).required('Title is required');},
    'data.sorting': function (value = null) {
      return Validator.value(value)
        .digit()
        .regex("^[0-9]*$")
        .required("Sorting is required");
    },
  },
}
</script>
