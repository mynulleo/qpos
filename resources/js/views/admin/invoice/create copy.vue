<template>
  <div class="row"> <create-form @onSubmit="submit">
      <div class="col-md-8 mb-5">
        <div class="row">
          <div class="col-md-12">
            <fieldset> <span class="legend">Invoice</span>
              <div class="row g-3">
                <Input v-model="data.clientid" field="data.residenceno" title="Client ID" :req="true"
                  :disabled="this.$route.params.id ? true : false" @keyup.enter="fetchClientData" />
                <Select title='Client' v-model='data.client_id' field='data.client_id' col="6" label='org_name'
                  :reduce='(obj) => obj.id' :options='clients' placeholder='--Select One--' @change="fetchClientData"
                  :closeOnSelect='true' :required='false' :disabled="this.$route.params.id ? true : false" />
                <hr class="mt-2" />
                <date-picker id="date2" v-model="data.invoice_date" field="data.invoice_date" title="Invoice Date"
                  placeholder="Invoice Date" col="3" :req="false" /> <Input v-model="data.original_amount"
                  field="data.original_amount" title="Amount" col="3" :readonly="true" :req="true" />
                <Input v-model="data.discount" field="data.discount" @change="recalculateTotals()" title="Discount"
                  col="3" />
                <Input v-model="data.amount" field="data.amount" title="Total" col="3" :req="true" :readonly="true" />
                <hr class="mt-2" />
                <date-picker v-if="data.payment_status == 'paid'" id="date5" v-model="data.payment_date"
                  field="data.payment_date" title="Payment Date" placeholder="Payment Date" col="3" :req="false" />
                <Input v-if="data.payment_status == 'paid'" v-model="data.paid_amount" field="data.paid_amount" col="3"
                  title="Paid Amount" :req="false" />
                <Input v-if="data.payment_status == 'paid'" v-model="data.trxid" field="data.trxid" title="Trxid"
                  :req="false" />
                <Select v-if="data.payment_status == 'paid'" title="Payment Status" v-model="data.payment_status"
                  field="data.payment_status" label="name" :reduce="(obj) => obj.value"
                  :options="$root.global.payment_status" placeholder="--Select One--" :closeOnSelect="true"
                  :required="false" />
              </div>
            </fieldset>
          </div> <!-- Invoice Details -->
          <div class="col-md-12">
            <fieldset class="mt-4"> <span class="legend">Invoice Details</span>
              <div class="row">
                <div class="col-md-12">
                  <table class="table">
                    <thead>
                      <tr>
                        <th style="width:5%">Sl</th>
                        <th>Ref</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th style=" width:20%">Amount</th>
                        <th style="width:10%"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(invdetail, index) in data.invoice_details" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td></td>
                        <td> <Textarea v-model="invdetail.description" field="invdetail.description" :required="false"
                            col="12" /> </td>
                        <td>
                          <Input v-model="invdetail.qty" field="invdetail.qty" col="12"
                            @change="recalculateRow(invdetail)" />
                        </td>
                        <td>
                          <Input v-model="invdetail.total_amount" field="invdetail.total_amount" col="12"
                            :readonly="false" />
                        </td>
                        <td>
                          <div class="multiple_fields_actions_btn d-flex align-items-center gap-2">
                            <button type="button" class="btns delete_one" v-if="data.invoice_details.length > 1"
                              @click.prevent="removeInvoiceDetails(index)">
                              <span class="icon">
                                <i class="fas fa-trash"></i></span>
                            </button>
                            <button v-if="isLastItem(data.invoice_details, index)" type="button" class="btns add_more"
                              @click.prevent="addInvoiceDetailsRow()">
                              <span class="icon"><i class="fas fa-plus"></i></span>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </fieldset>
          </div>
          <div class="col-md-12 mt-4"></div>
          <Switch v-model="data.status" field="data.status" title="Status" on-label="Active" off-label="Deactive"
            :req="true" />
        </div>
      </div> <!-- Client Info -->
      <div class="col-md-4 mb-5">
        <div class="row g-3">
          <div class="col-md-12">
            <fieldset> <span class="legend">Client Info</span>
              <div class="table-responsive">
                <table class="table table-striped">
                  <tbody>
                    <tr>
                      <th width="45%">Client Name</th>
                      <th width="5">:</th>
                      <td width="50%">{{ data?.client?.org_name }}</td>
                    </tr>
                    <tr>
                      <th>Mobile</th>
                      <th>:</th>
                      <td>{{ data?.client?.mobile }}</td>
                    </tr>
                    <tr>
                      <th>Email</th>
                      <th>:</th>
                      <td>{{ data?.client?.email }}</td>
                    </tr>
                    <tr>
                      <th>Address</th>
                      <th>:</th>
                      <td>{{ data?.client?.address }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </fieldset>
          </div>
          <div class="col-md-12">
            <fieldset> <span class="legend">Workorders</span>
              <table class="table table-striped">
                <tbody>
                  <tr v-for="(record, index) in workorders" :key="index">
                    <td>
                      <input type="checkbox" :value="record.id" v-model="selectedWorkorders"
                        @change="handleWorkorderSelection(record)">
                    </td>
                    <td>
                      <strong>Order Date: </strong>{{ record.order_date }} / <strong>Order No:</strong> {{
                        record.order_no }} / <strong>UNO NO:</strong> {{ record.uno_no }} / <strong>Qty:</strong> {{
                        record.quantity }} / <strong>Amount:</strong> {{ record.due_amount }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </fieldset>
          </div>
        </div>
      </div>
    </create-form>
  </div>
</template>

<script>
const model = "invoice";
export default {
  data() {
    return {
      model,
      page_title: "",
      isEditMode: false,
      base_description: "",
      data: {
        id: null,
        clientid: "",
        client_id: null,
        invoice_date: this.$filter.today(),
        original_amount: 0,
        discount: 0,
        amount: 0,
        payment_status: "",
        payment_date: "",
        paid_amount: 0,
        trxid: "",
        status: true,
        client: {},
        invoice_details: [
          {
            description: "",
            amount: 0,
            user_count: null,
            total_amount: 0,
            account_id: null,
          },],
        savedMonths: [],
      },
      accounts: [],
      clients: [],
      workorders: []
    };
  },
  provide() {
    return {
      validate: this.validation || {
        errors: {}
      },
    };
  },
  watch: {
    "data.invoice_details": {
      handler() {
        this.recalculateTotals();
      },
      deep: true,
    },
  },
  methods: {
    submit() {
      this.$validate().then(() => {
        const error = this.validation.countErrors();
        if (error > 0) {
          this.$toast("You need to fill " + error + " more empty mandatory fields", "warning");
          return;
        }
        this.data.id ? this.update(this.model, this.data, this.data.id) : this.store(this.model, this.data);
      });
    },
    getClients() {
      axios.get(
        `clients/`
      )
        .then((response) => {
          this.clients = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    /* ------------------------- Row Calculations --------------------------*/
    recalculateRow(row) {
      const unit = parseFloat(row.amount) || 0;
      const months = parseInt(row.month_count) || 1;
      const userCount = parseInt(row.user_count) || null;
      if (userCount && userCount > 0) {
        row.total_amount = (unit * months * userCount).toFixed(2);
      } else {
        row.total_amount = (unit * months).toFixed(2);
      }
    },
    recalculateTotals() {
      const sum = this.data.invoice_details.reduce((s, r) => s + (parseFloat(r.total_amount) || 0), 0);
      this.data.original_amount = sum.toFixed(2);
      this.data.amount = (sum - (parseFloat(this.data.discount) || 0)).toFixed(2);
    },
    onDiscountChange() {
      this.recalculateTotals();
    },
    /* ------------------------- Invoice Rows --------------------------*/
    addInvoiceDetailsRow() {
      this.data.invoice_details.push({
        description: "",
        amount: 0,
        month_count: this.data.invoice_details[0]?.month_count || 1,
        total_amount: 0,
        account_id: null,
      });
    },
    removeInvoiceDetails(index) {
      if (this.data.invoice_details.length > 1) {
        this.data.invoice_details.splice(index, 1);
        this.recalculateTotals();
      }
    },
    isLastItem(items, index) {
      return index === items.length - 1;
    },
    /* ------------------------- Client Fetch --------------------------*/
    async fetchClientData() {
      const res = await axios.get('/clientinfo', {
        params: {
          client_id: this.data.client_id
        }
      });
      this.data.client = res.data?.clientinfo;
      this.workorders = res.data?.workorders;
    },
    /* ------------------------- Edit Mode Init --------------------------*/
    initEditMode() {
      this.isEditMode = true;
      this.page_title = this.headline(this.model) + " Edit"; // শুধু fetch করবে, কিছু return করবে না 
      this.get_data(`${this.model}/${this.$route.params.id}`); // data reactive ভাবে সেট হওয়ার পর পরবর্তী কাজগুলো করবো 
      this.$nextTick(() => {
        // backend থেকে আসা saved months apply 
        if (this.data.savedMonths?.length) {
          this.data.invoice_months.forEach(m => {
            m.checked = this.data.savedMonths.includes(m.value);
          });
        } // backend থেকে আসা row অনুযায়ী total recalc 
        if (this.data.invoice_details?.length) {
          this.data.invoice_details.forEach(row => {
            this.recalculateRow(row);
          });
        } this.recalculateTotals();
      });
    },
  },
  created() {
    if (this.$route.params.id) {
      this.initEditMode();
    } else {
      this.page_title = this.headline(this.model) + " Create";
    }
    this.getClients();
  },
  validators: {
    "data.amount": function (value = null) {
      return Validator.value(value).required("Amount is required");
    },
  },
};
</script>