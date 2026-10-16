<template>
  <create-form @onSubmit='submit'>
    <!-- 🏢 Card 1: Customer Profile & Identity (Full Width) -->
    <div class="col-md-12 mb-3">
      <fieldset class="client-fieldset-card">
        <span class="legend">
          <i class="fas fa-id-card me-1.5 text-primary"></i>{{ $t('Customer Profile & Identity') }}
        </span>
        <div class="row g-3 pt-1">
          <Select title='Customer Type' v-model='data.customer_type' field='data.customer_type' label='title'
            :reduce='(obj) => obj.value' col="3" :options='customerTypes' :placeholder="$t('-- Select Type --')"
            :closeOnSelect='true' :required='true' />

          <Input v-model='data.name' field='data.name' title='Customer / Contact Name' placeholder='e.g. Mohammad Rahim' col="3" :req='true' />

          <Input v-model='data.org_name' field='data.org_name' title='Organization / Business Name' placeholder='e.g. Rahim Traders (Optional)' col="3"
            :req='false' />

          <date-picker id='date1' v-model='data.reg_date' field='data.reg_date' title='Registration Date'
            placeholder='Reg Date' col='3' :req='true'></date-picker>

          <Input v-model='data.mobile' field='data.mobile' title='Mobile Number' placeholder='e.g. 01712345678' col="3" :req='true' />

          <Input v-model='data.email' field='data.email' title='Email Address' placeholder='e.g. client@example.com' col="3" :req='false' />

          <Input v-model='data.nid' field='data.nid' title='NID / Trade License No' placeholder='e.g. 19901234567890' col="3" :req='false' />

          <Switch v-model='data.status' field='data.status' title='Account Status' on-label='Active'
            off-label='Deactive' col="3" :req='true'>
          </Switch>
        </div>
      </fieldset>
    </div>

    <!-- 📍 Card 2: Location & Address (Left Column) -->
    <div class="col-md-6 mb-3">
      <fieldset class="client-fieldset-card h-100">
        <span class="legend">
          <i class="fas fa-map-marked-alt me-1.5 text-success"></i>{{ $t('Location & Address') }}
        </span>
        <div class="row g-3 pt-1">
          <Select title='District' v-model='data.district_id' field='data.district_id' label='district_name'
            :reduce='(obj) => obj.id' col="6" :options='$root.global.districts' :placeholder="$t('-- Select District --')"
            :closeOnSelect='true' :required='false' @update:modelValue='onDistrictChange' />

          <Select title='Area / Thana' v-model='data.area_id' field='data.area_id' label='area_name'
            :reduce='(obj) => obj.id' :options='areas' col="6" :placeholder="$t('-- Select Area --')"
            :closeOnSelect='true' :required='false' />

          <Textarea v-model='data.address' field='data.address' title='Detailed Street Address' placeholder='House, Road, Block, Sector, Landmark...' col="12" :req='false' />

          <Textarea v-model='data.note' field='data.note' col="12" title='Remarks & Customer Notes' placeholder='Any customer preferences, delivery notes, or special instructions...' :req='false' />
        </div>
      </fieldset>
    </div>

    <!-- 💳 Card 3: Financial & Bank Settlement (Right Column) -->
    <div class="col-md-6 mb-3">
      <fieldset class="client-fieldset-card h-100">
        <span class="legend">
          <i class="fas fa-wallet me-1.5 text-warning"></i>{{ $t('Financial & Bank Settlement') }}
        </span>
        <div class="row g-3 pt-1">
          <div class="col-md-12">
            <Input v-model='data.previous_due' field='data.previous_due' title='Previous Due / Opening Balance (৳)' placeholder='0.00' col="12"
              :req='false' />
            <div class="small text-muted mt-1">
              <i class="fas fa-info-circle me-1 text-info"></i>{{ $t('Initial outstanding receivable balance from this customer before onboarding.') }}
            </div>
          </div>

          <Select title='Bank Name' v-model='data.bank_id' field='data.bank_id' label='bank_name'
            :reduce='(obj) => obj.id' col="6" :options='banks' :placeholder="$t('-- Select Bank --')"
            :closeOnSelect='true' :required='false' />

          <Input v-model='data.branch' field='data.branch' col="6" title='Branch Name' placeholder='e.g. Motijheel Branch' :req='false' />

          <Input v-model='data.account_name' field='data.account_name' col="6" title='Bank Account Name' placeholder='e.g. Mohammad Rahim'
            :req='false' />

          <Input v-model='data.account_no' field='data.account_no' col="6" title='Bank Account Number' placeholder='e.g. 102.120.3456' :req='false' />
        </div>
      </fieldset>
    </div>

    <!-- 🔘 Form Actions Footer -->
    <template v-slot:form_footer>
      <div class="col-md-12 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center gap-1.5"
          @click="$router.push({ name: model + '.index' })">
          <i class="fas fa-arrow-left"></i>
          <span>{{ $t('Back to Client List') }}</span>
        </button>

        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-light border px-3" @click="resetForm" v-if="!data.id">
            <i class="fas fa-undo me-1"></i> {{ $t('Reset Form') }}
          </button>
          <button type="submit" class="theme_btn px-4 d-inline-flex align-items-center gap-1.5" :disabled="$root.submit">
            <template v-if="$root.submit">
              <i class="fa fa-spinner fa-spin"></i>
              <span>{{ $t('Saving...') }}</span>
            </template>
            <template v-else>
              <i :class="data.id ? 'fas fa-save' : 'fas fa-check-circle'"></i>
              <span>{{ $t(data.id ? 'Update Client' : 'Save Customer') }}</span>
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>

const model = 'client';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        customer_type: 'retail',
        reg_date: this.$filter.today(),
        previous_due: 0,
        account_name: '',
        account_no: '',
        bank_id: null,
        branch: '',
        status: 'active',
        name: '',
        org_name: '',
        mobile: '',
        email: '',
        nid: '',
        district_id: null,
        area_id: null,
        address: '',
        note: '',
      },
      customerTypes: [
        { title: 'Retail', value: 'retail' },
        { title: 'Wholesale', value: 'wholesale' },
      ],
      banks: [],
      areas: [],
    };
  },

  watch: {
    'data.district_id': 'getareas',
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    getareas() {
      this.areas = [];
      var district_id = this.data.district_id;
      if (!district_id) return;
      axios.get("/getareas/" + district_id).then((res) => {
        this.areas = res.data;
      });
    },

    onDistrictChange(val) {
      this.data.area_id = null;
      if (val) {
        this.getareas();
      } else {
        this.areas = [];
      }
    },

    getbanks() {
      axios.get("/getbanks/").then((res) => {
        this.banks = res.data;
      });
    },

    resetForm() {
      this.data = {
        customer_type: 'retail',
        reg_date: this.$filter.today(),
        previous_due: 0,
        account_name: '',
        account_no: '',
        bank_id: null,
        branch: '',
        status: 'active',
        name: '',
        org_name: '',
        mobile: '',
        email: '',
        nid: '',
        district_id: null,
        area_id: null,
        address: '',
        note: '',
      };
      this.areas = [];
      if (this.validation) {
        this.validation.reset();
      }
    },

    submit() {
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
      this.get_data(`${this.model}/${this.$route.params.id}`).then(() => {
        if (this.data.district_id) {
          this.getareas();
        }
      });
    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }
    this.getbanks();
  },

  validators: {
    'data.name': function (value = null) {
      return Validator.value(value).required('Customer Name is required');
    },
    'data.mobile': function (value = null) {
      return Validator.value(value).required('Mobile Number is required');
    },
    'data.customer_type': function (value = null) {
      return Validator.value(value).required('Customer Type is required');
    },
  },
};
</script>

<style scoped>
.client-fieldset-card {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background-color: #ffffff;
  padding: 1.25rem 1.25rem 1rem 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  position: relative;
}

.client-fieldset-card .legend {
  font-weight: 600;
  font-size: 14.5px;
  color: #1e293b;
  background-color: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 20px;
  padding: 4px 14px;
}
</style>
