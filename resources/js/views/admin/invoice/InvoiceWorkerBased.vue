<template>
    <div class="row">
        <create-form @onSubmit="submit">
            <div class="col-md-8 mb-5">
                <div class="row">
                    <div class="col-md-12">

                        <fieldset>
                            <span class="legend">Invoice</span>
                            <div class="row g-3">

                                <Select title='Client' v-model='data.client_id' field='data.client_id' col="6"
                                    label='org_name' :reduce='(obj) => obj.id' :options='clients'
                                    placeholder='--Select One--' @change="fetchClientData" :closeOnSelect='true'
                                    :required='false' :disabled="this.$route.params.id ? true : false" />

                                <hr class="mt-2" />

                                <date-picker id="date2" v-model="data.invoice_date" field="data.invoice_date"
                                    title="Invoice Date" placeholder="Invoice Date" col="3" :req="false" />

                                <Input v-model="data.original_amount" field="data.original_amount" title="Amount"
                                    col="3" :readonly="true" :req="true" />

                                <Input v-model="data.discount" field="data.discount" @change="recalculateTotals()"
                                    title="Discount" col="3" />

                                <Input v-model="data.amount" field="data.amount" title="Total" col="3" :req="true"
                                    :readonly="true" />

                                <hr class="mt-2" />

                                <date-picker v-if="data.payment_status == 'paid'" id="date5" v-model="data.payment_date"
                                    field="data.payment_date" title="Payment Date" col="3" />

                                <Input v-if="data.payment_status == 'paid'" v-model="data.paid_amount"
                                    field="data.paid_amount" col="3" title="Paid Amount" />

                                <Input v-if="data.payment_status == 'paid'" v-model="data.trxid" field="data.trxid"
                                    title="Trxid" />

                                <Select v-if="data.payment_status == 'paid'" title="Payment Status"
                                    v-model="data.payment_status" field="data.payment_status" label="name"
                                    :reduce="(obj) => obj.value" :options="$root.global.payment_status" />
                                <Switch v-model="data.status" field="data.status" title="Status" on-label="Active"
                                    off-label="Deactive" :req="true" />
                            </div>
                        </fieldset>
                    </div>

                    <!-- Invoice Details -->
                    <div class="col-md-12">
                        <fieldset class="mt-4">
                            <span class="legend">Invoice Details</span>

                            <table class="table table-squeeze">
                                <thead>
                                    <tr>
                                        <th style=" width:25%">Ref / Description</th>
                                        <th style=" width:10%">Qty</th>
                                        <th style=" width:15%">Amount</th>
                                        <th style=" width:15%">Currency</th>
                                        <th style=" width:15%">Curr. Rate</th>
                                        <th style=" width:15%">Total</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <template v-if="data.invoice_details.length > 0">
                                        <tr v-for="(invdetail, index) in data.invoice_details" :key="index">
                                            <td>
                                                <Input v-model="invdetail.reference" field="invdetail.reference"
                                                    col="12" placeholder="Reference" />
                                                <Textarea v-model="invdetail.description" field="invdetail.description"
                                                    col="12" placeholder="Description" />
                                            </td>
                                            <td>
                                                <Input v-model="invdetail.qty" field="invdetail.qty"
                                                    @change="recalculateRow(invdetail)" col="12" />
                                            </td>
                                            <td>
                                                <Input v-model="invdetail.amount" field="invdetail.amount" col="12" />
                                            </td>
                                            <td>
                                                <Select v-model='invdetail.currency_id' field='invdetail.currency_id'
                                                    col="12" label='title' :reduce='(obj) => obj.id'
                                                    :options='currencies' placeholder='--Select One--'
                                                    :closeOnSelect='true' />
                                            </td>
                                            <td>
                                                <Input v-model="invdetail.currency_rate" field="invdetail.currency_rate"
                                                    col="12" />
                                            </td>
                                            <td>
                                                <Input v-model="invdetail.total_amount" field="invdetail.total_amount"
                                                    col="12" />
                                            </td>
                                            <td>
                                                <div
                                                    class="multiple_fields_actions_btn d-flex align-items-center gap-2">
                                                    <button type="button" class="btns delete_one"
                                                        v-if="data.invoice_details.length > 1"
                                                        @click.prevent="removeInvoiceDetails(index)">
                                                        <span class="icon">
                                                            <i class="fas fa-trash"></i></span>
                                                    </button>
                                                    <button v-if="isLastItem(data.invoice_details, index)" type="button"
                                                        class="btns add_more" @click.prevent="addInvoiceDetailsRow()">
                                                        <span class="icon"><i class="fas fa-plus"></i></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td class="text-center" colspan="8">
                                                <div
                                                    class="multiple_fields_actions_btn d-flex align-items-center gap-2">

                                                    <strong>No records found. Please click the button to add a new row.
                                                    </strong> &nbsp;
                                                    &nbsp;
                                                    <button type="button" class="btns add_more"
                                                        @click.prevent="addInvoiceDetailsRow()">
                                                        <span class="icon"><i class="fas fa-plus"></i></span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </fieldset>
                    </div>
                </div>
            </div>

            <!-- Client Info -->
            <div class="col-md-4">
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
                    <div class="col-md-12" v-if="workorders.length > 0">
                        <fieldset>
                            <span class="legend">Workorders</span>
                            <table class="table table-striped">
                                <tbody>
                                    <tr v-for="(record, index) in workorders" :key="index">
                                        <td>
                                            <input type="checkbox" :value="record.id" v-model="selectedWorkorders"
                                                @change="handleWorkorderSelection(record)">
                                        </td>
                                        <td>
                                            <strong>Order Date: </strong>{{ record.order_date }} / <strong>Order
                                                No:</strong> {{
                                                    record.order_no }} / <strong>UNO NO:</strong> {{ record.uno_no }} /
                                            <strong>Qty:</strong> {{
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
            selectedWorkorders: [], // ✅ added
            model,
            page_title: "",
            data: {
                client_id: '',
                invoice_date: this.$filter.today(),
                original_amount: 0,
                discount: 0,
                amount: 0,
                client: {},
                invoice_details: [
                    {
                        workorder_id: '',
                        reference: '',
                        description: "",
                        qty: 0,
                        amount: 0,
                        currency_id: 0,
                        currency_rate: '',
                        total_amount: 0
                    },
                ],
                status: true
            },

            clients: [],
            currencies: [],
            workorders: []
        };
    },
    provide() {
        return {
            validate: {
                errors: {}
            }
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

        async fetchClientData() {
            const res = await axios.get('/clientinfo', {
                params: { client_id: this.data.client_id }
            });

            this.data.client = res.data?.clientinfo;
            this.workorders = res.data?.workorders;

            // reset
            this.selectedWorkorders = [];
            this.data.invoice_details = [];
        },

        // ✅ WORKORDER FEATURE
        handleWorkorderSelection(record) {

            const existsIndex = this.data.invoice_details.findIndex(
                item => item.workorder_id === record.id
            );

            if (this.selectedWorkorders.includes(record.id)) {

                if (existsIndex === -1) {
                    let totalAmount = record.due_amount;
                    if (record.currency_rate) {
                        totalAmount = parseFloat(record.due_amount) * parseFloat(record.currency_rate);
                    }
                    this.data.invoice_details.push({
                        workorder_id: record.id,
                        reference: record.order_no + ' / ' + record.uno_no,
                        description: `Order No: ${record.order_no}`,
                        qty: record.quantity || 1,
                        amount: record.due_amount,
                        currency_id: record.currency_id,
                        currency_rate: record.currency_rate,
                        total_amount: totalAmount || 0,
                    });
                }

            } else {

                if (existsIndex !== -1) {
                    this.data.invoice_details.splice(existsIndex, 1);
                }
            }

            this.recalculateTotals();
        },

        addInvoiceDetailsRow() {
            this.data.invoice_details.push({
                workorder_id: '',
                referance: '',
                description: "",
                qty: 0,
                total_amount: 0
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

        recalculateRow(row) {
            const unit = parseFloat(row.amount) || 0;
            const qty = parseFloat(row.qty) || 1;

            row.total_amount = (unit * qty).toFixed(2);
            this.recalculateTotals();
        },

        recalculateTotals() {

            const sum = this.data.invoice_details.reduce((s, r) => {

                const amount = parseFloat(r.amount) || 0;
                const rate = parseFloat(r.currency_rate);

                // ✅ condition applied
                if (!rate || rate === 0) {
                    r.total_amount = amount.toFixed(2);
                } else {
                    r.total_amount = (amount * rate).toFixed(2);
                }

                return s + parseFloat(r.total_amount);

            }, 0);

            this.data.original_amount = sum.toFixed(2);
            this.data.amount = (sum - (parseFloat(this.data.discount) || 0)).toFixed(2);
        },

        async getClients() {
            const res = await axios.get('/clients');
            this.clients = res.data;
        },

        async getCurrencies() {
            const res = await axios.get('/getcurrencies');
            this.currencies = res.data;
        }
    },

    created() {
        if (this.$route.params.id) {
            this.get_data(`${this.model}/${this.$route.params.id}`);
        } else {
            this.page_title = this.headline(this.model) + " Create";
        }

        this.getClients();
        this.getCurrencies();
    },
    validators: {
        "data.amount": function (value = null) {
            return Validator.value(value).required("Amount is required");
        },
    },
};
</script>
