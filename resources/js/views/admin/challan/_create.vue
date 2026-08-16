<template>
  <create-form @onSubmit='submit'>
    <div class="col-md-8">
      <div class="row g-4">
        <div class="col-md-12">
          <fieldset>
            <span class="legend">Challan</span>
            <div class="row g-3">
              <Select title='Work Order' v-model='data.workorder_id' field='data.workorder_id' label='name'
                :reduce='(obj) => obj.id' :options='workorders' placeholder='--Select One--' :closeOnSelect='true'
                :required='true' col="7" @change="getWorkorderInfo" />
              <date-picker id='date2' v-model='data.challan_date' field='data.challan_date' title='Challan Date'
                placeholder='Challan Date' col='3' :req='true'></date-picker>
              <Input v-model='data.delivery_location' field='data.delivery_location' title='Delivery Location'
                :req='false' col="12" />
              <Switch v-model='data.status' field='data.status' title='Status' on-label='Active' off-label='Deactive'
                :req='true'>
              </Switch>
            </div>
          </fieldset>
        </div>
        <div class="col-md-8">
          <fieldset>
            <span class="legend">Receiver Info</span>
            <div class="row">
              <Input v-model='data.receive_by' field='data.receive_by' title='Name' :req='false' col="4" />
              <Input v-model='data.receive_by_designation' field='data.receive_by_designation' title='Designation'
                :req='false' col="4" />
              <Input v-model='data.receive_by_mobile' field='data.receive_by_mobile' title='Mobile' :req='false'
                col="4" />
            </div>
          </fieldset>
        </div>
        <div class="col-md-4">
          <fieldset>
            <span class="legend">Delivered By</span>
            <div class="row">
              <Input v-model='data.delivery_by' field='data.delivery_by' title='Name' :req='false' />
              <Input v-model='data.delivery_by_mobile' field='data.delivery_by_mobile' title='Mobile' :req='false' />
            </div>
          </fieldset>
        </div>

      </div>
    </div>
    <div class="col-md-4">
      <fieldset>
        <span class="legend">Workorder Info</span>
        <table class="table table-striped">
          <tbody>
            <tr>
              <th width="40%">Order No</th>
              <th width="5%">:</th>
              <td></td>
            </tr>
            <tr>
              <th>UNO No</th>
              <th>:</th>
              <td>{{ data.workorder?.order_no }}</td>
            </tr>
            <tr>
              <th>Oorder Date</th>
              <th>:</th>
              <td>{{ data.workorder?.order_date }}</td>
            </tr>
            <tr>
              <th>Client</th>
              <th>:</th>
              <td>{{ data.workorder?.client?.org_name }}</td>
            </tr>
            <tr>
              <th>Delivery Date</th>
              <th>:</th>
              <td>{{ data.workorder?.delivery_date }}</td>
            </tr>
            <tr>
              <th>Shipping</th>
              <th>:</th>
              <td>{{ data.workorder?.shipping }}</td>
            </tr>
            <tr>
              <th>Remarks</th>
              <th>:</th>
              <td>{{ data.workorder?.remarks }}</td>
            </tr>
          </tbody>
        </table>
      </fieldset>
    </div>
    <div class="col-md-12">
      <fieldset>
        <span class="legend">Challan Details</span>
        <table class="table">
          <thead>
            <tr>
              <th>Description</th>
              <th style="width:15%">Order Qty</th>
              <th style="width:15%">Receive Qty</th>
              <th style="width:25%">Remarks</th>
              <th style="width:10%"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data.challan_details" :key="index">
              <td>
                <Textarea v-model="item.description" field="item.description" col="12" />
              </td>
              <td>
                <Input v-model="item.qty" field="item.qty" col="12" />
              </td>
              <td>
                <Input v-model="item.receive_qty" field="item.receive_qty" :readonly="!$route.params.id" col="12" />
              </td>
              <td>
                <Textarea v-model="item.remark" field="item.remark" col="12" />
              </td>
              <td>
                <div class="multiple_fields_actions_btn d-flex align-items-center gap-2">
                  <button type="button" class="btns delete_one" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-title="Delete" v-x-tooltip @click.prevent="
                      removeChallanDetails(index)
                      " v-if="
                        Object.keys(
                          data.challan_details
                        ).length > 1
                      ">
                    <i class="fas fa-trash"></i>
                  </button>
                  <button v-if="isLastItem(data.challan_details, index)" type="button" class="btns add_more"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add" v-x-tooltip
                    @click.prevent="addChallanDetailsRow()">

                    <i class="fas fa-plus-square"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </fieldset>
    </div>
  </create-form>
</template>

<script>


const model = 'challan';

export default {

  data() {
    return {
      model: model,
      page_title: '',
      data: {
        workorder_id: '',
        challan_date: this.$filter.today(),
        challan_details: [
          {
            description: '',
            qty: '',
            receive_qty: ''
          }
        ],
        workorder: {}
      },
      workorders: []
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
          var form = document.getElementById('form');
          var formData = new FormData(form);
          formData.append('challan_date', this.data.challan_date);
          formData.append('challan_details', JSON.stringify(this.data.challan_details));

          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },
    addChallanDetailsRow() {
      this.data.challan_details.push({
        workorder_detail_id: '',
        description: '',
        qty: '',
        receive_qty: ''
      });
    },
    removeChallanDetails(index) {
      if (Object.keys(this.data.challan_details).length > 1) {
        this.data.challan_details.splice(index, 1)
      }
    },
    isLastItem(items, index) {
      return index === items.length - 1;
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
    getWorkorderInfo(workorder_id) {
      axios.get(
        `getworkorderinfo/` + workorder_id
      )
        .then((response) => {
          this.data.workorder = response.data.workorder;
          this.data.challan_details = response.data.workorder_details;
          this.data.client_id = response.data.workorder?.client_id;
          this.data.delivery_location = response.data.workorder?.shipping ?? response.data.workorder?.client?.address;
          this.data.receive_by = response.data.workorder?.client?.name;
          this.data.receive_by_mobile = response.data.workorder?.client?.mobile;

        })
        .catch((error) => {
          console.error(error);
        });
    }
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }
    this.getWorkorders()
  },

  validators: {
    'data.workorder_id': function (value = null) { return Validator.value(value).required('Work Order Id is required'); },
    'data.challan_date': function (value = null) { return Validator.value(value).required('Challan Date is required'); },
    'data.client_id': function (value = null) { return Validator.value(value).required('Client Id is required'); },

  },
}

</script>