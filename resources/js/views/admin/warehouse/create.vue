<template>
  <create-form @onSubmit='submit'>
    <Input v-model='data.name' field='data.name' title='Warehouse Name' col="4" :req='true' />
    <Input v-model='data.code' field='data.code' title='Warehouse Code (Ex: WH-01)' col="2" :req='false' />
    <Input v-model='data.contact_person' field='data.contact_person' title='Contact Person (In-Charge)' col="3" :req='false' />
    <Input v-model='data.contact_person_phone' field='data.contact_person_phone' title='Contact Person Mobile' col="3" :req='false' />
    
    <Input v-model='data.phone' field='data.phone' title='Warehouse Phone' col="3" :req='false' />
    <Input v-model='data.email' field='data.email' title='Warehouse Email' col="3" :req='false' />
    <Input v-model='data.capacity' field='data.capacity' title='Capacity (Ex: 5000 Units / 2000 sq ft)' col="3" :req='false' />
    <Select title='Branch' v-model='data.branch_id' field='data.branch_id' label='title'
      :reduce='(obj) => obj.id' :options='$root.global.branches' col="3" placeholder='--Select Branch--'
      :closeOnSelect='true' :required='false' />
    
    <Textarea v-model='data.address' field='data.address' :required='false' title="Physical Address" col="12" />
    
    <Input v-model='data.sorting' field='data.sorting' title='Sorting' col="2" :req='true' type="number" />
    <Switch
      v-model='data.status'
      field='data.status'
      title='Status'
      on-label='Active'
      off-label='Deactive'
      col="2"
      :req='true'
    ></Switch>

  </create-form>
</template>

<script>
const model = 'warehouse';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        name: '',
        code: '',
        contact_person: '',
        contact_person_phone: '',
        phone: '',
        email: '',
        capacity: '',
        branch_id: null,
        address: '',
        sorting: 0,
        status: 'active',
      },
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
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + ' Create';
      this.get_sorting(this.ucfirst(this.model));
    }
  },

  validators: {
    'data.name': function (value = null) {
      return Validator.value(value).required('Warehouse Name is required');
    },
    'data.sorting': function (value = null) {
      return Validator.value(value)
        .digit()
        .regex("^[0-9]*$")
        .required("Sorting is required");
    },
  },
};
</script>
