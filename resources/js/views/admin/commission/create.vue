<template>
  <create-form @onSubmit='submit'>
    <div class="col-md-8">
      <fieldset>
        <span class="legend">Form</span>
        <div class="row g-3">
          <SwitchBoolean v-model='data.is_employee' field='data.is_employee' title='Is Employee' col="3" on-label='Yes'
            off-label='No' :req='true'></SwitchBoolean>
          <div class="col-md-12"></div>
          <Select v-if="data.is_employee" title='Employee' v-model='data.employee_id' field='data.employee_id'
            label='full_name' :reduce='(obj) => obj.id' :options='$root.global.employees' placeholder='--Select One--'
            :closeOnSelect='true' col="4" :required='false' />
          <Select v-if="!data.is_employee" title='Agent Name' v-model='data.agent_id' field='data.agent_id'
            label='full_name' :reduce='(obj) => obj.id' :options='agents' placeholder='--Select Agent--'
            :closeOnSelect='true' col="4" :required='false' />
          <Input v-if="!data.is_employee" v-model='data.reference_name' field='data.reference_name'
            title='Reference Name' col="4" :req='false' />
          <Input v-if="!data.is_employee" v-model='data.reference_mobile' field='data.reference_mobile'
            title='Reference Mobile' col="4" :req='false' />
          <Input v-if="!data.is_employee" v-model='data.reference_nid' field='data.reference_nid' title='Reference NID'
            col="4" :req='false' />
          <Textarea v-if="!data.is_employee" v-model='data.reference_address' field='data.reference_address'
            title='Reference Address' :req='false' col="12" />

          <div class="col-md-12"></div>
          <Select title='Workorder' v-model='data.workorder_id' field='data.workorder_id' label='name'
            :reduce='(obj) => obj.id' :options='workorders' placeholder='--Select One--' :closeOnSelect='true' col="6"
            :required='false' />
          <Input v-model='data.percentage' field='data.percentage' title='Percentage' :req='false' col="3" />
          <Input v-model='data.amount' field='data.amount' title='Amount' :req='false' col="3" />

          <Switch v-model='data.status' field='data.status' title='status' on-label='Active' col="3"
            off-label='Deactive' :req='true'>
          </Switch>
        </div>
      </fieldset>
    </div>
    <div class="col-md-4">
      <fieldset>
        <span class="legend">Package Info</span>
        <div class="table table-striped">
          <table class="table table-striped">
            <tbody>
              <tr>
                <td colspan="3">
                  <i>Order Information</i>
                </td>
              </tr>
              <tr>
                <th width="40%">Order No</th>
                <th width="5%">:</th>
                <td>{{ data.workorder?.order_no }}</td>
              </tr>
              <tr>
                <th>Order Date</th>
                <th>:</th>
                <td>{{ data.workorder?.order_date }}</td>
              </tr>
              <tr>
                <th>Client</th>
                <th>:</th>
                <td>{{ data.workorder?.client?.org_name }}</td>
              </tr>
              <tr>
                <th>Amount</th>
                <th>:</th>
                <td>{{ data.workorder?.amount }}</td>
              </tr>
              <tr>
                <th>Currency</th>
                <th>:</th>
                <td>{{ data.workorder?.currency?.short_name }}</td>
              </tr>
              <tr>
                <th>Currency Rate</th>
                <th>:</th>
                <td>{{ data.workorder?.currency_rate }}</td>
              </tr>
              <tr>
                <td colspan="3">
                  <i>Client/ Reference Information</i>
                </td>
              </tr>
              <tr>
                <th>Reference Name</th>
                <th>:</th>
                <td>{{ data.reference_name }}</td>
              </tr>
              <tr>
                <th>Reference Mobile</th>
                <th>:</th>
                <td>{{ data.reference_mobile }}</td>
              </tr>
              <tr>
                <th>Reference NID</th>
                <th>:</th>
                <td>{{ data.reference_nid }}</td>
              </tr>
              <tr>
                <th>Reference Address</th>
                <th>:</th>
                <td>{{ data.reference_address }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </fieldset>
    </div>
  </create-form>
</template>

<script>
const model = 'commission';

export default {

  data() {
    return {
      model: model,
      page_title: '',
      data: {
        is_employee: false,
        employee_id: null,
        agent_id: null,
        workorder_id: null,
        percentage: 0,
        amount: 0,
        workorder: {},
        client_id: null,
        client: null
      },
      clients: [],
      workorders: [],
      agents: []
    };
  },

  provide() {
    return {
      validate: this.validation,

    };
  },
  watch: {
    'data.employee_id': function (e) {
      this.getEmployeeInfo();
    },
    'data.agent_id': function (e) {
      this.getAgentInfo();
    },
    'data.workorder_id': function (e) {
      this.getWorkOrderInfo();
    },
    'data.percentage': function (e) {
      this.calculateCommissionAmount();
    }
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
    getWorkorders() {
      axios.get(
        `getworkorders/`
      )
        .then((response) => {
          this.workorders = response.data;

        })
        .catch((error) => {
          console.error(error);
        });
    },
    getAgents() {
      if (this.$root.global && this.$root.global.agents && this.$root.global.agents.length > 0) {
        this.agents = this.$root.global.agents;
      } else {
        axios.get(`getagents`)
          .then((response) => {
            this.agents = response.data;
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },
    getEmployeeInfo() {
      let empid = this.data.employee_id;
      if (!empid) return;
      axios.get(
        `employeeinfo/` + empid
      )
        .then((response) => {
          this.data.reference_name = response.data.full_name;
          this.data.reference_mobile = response.data.mobile;
          this.data.reference_nid = response.data.nid;
          this.data.reference_address = response.data.present_address;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    getAgentInfo() {
      let agentId = this.data.agent_id;
      if (!agentId) return;
      axios.get(`getagentinfo/` + agentId)
        .then((response) => {
          if (response.data) {
            this.data.reference_name = response.data.full_name;
            this.data.reference_mobile = response.data.mobile;
            this.data.reference_address = response.data.address;
          }
        })
        .catch((error) => {
          console.error(error);
        });
    },
    getWorkOrderInfo() {
      let workorderid = this.data.workorder_id ? this.data.workorder_id : 0;
      axios.get(`getworkorderinfo` + '/' + workorderid)
        .then((response) => {
          this.data.workorder = response.data?.workorder;
          this.data.client_id = response.data?.workorder.client_id;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    calculateCommissionAmount() {
      const workorderAmount = Number(this.data?.workorder?.amount) || 0;
      const percentage = Number(this.data?.percentage) || 0;

      this.data.amount = ((workorderAmount * percentage) / 100).toFixed(2);
    }
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);

    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }
    this.getWorkorders();
    this.getAgents();
  },

  validators: {

  },
}

</script>