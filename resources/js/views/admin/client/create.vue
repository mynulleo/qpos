<template>
    <create-form @onSubmit='submit'>
        <div class="col-md-12 mb-3">
            <fieldset>
                <span class="legend">Office Info</span>
                <div class="row g-3">
                    <date-picker id='date1' v-model='data.reg_date' field='data.reg_date' title='Reg Date'
                        placeholder='Reg Date' col='2' :req='true'></date-picker>
                    <Input v-model='data.org_name' field='data.org_name' title='Organization Name' col="6"
                        :req='false' />
                    <Select title='District' v-model='data.district_id' field='data.district_id' label='district_name'
                        :reduce='(obj) => obj.id' col="2" :options='$root.global.districts' placeholder='--Select One--'
                        :closeOnSelect='true' :required='false' />
                    <Select title='Area' v-model='data.area_id' field='data.area_id' label='area_name'
                        :reduce='(obj) => obj.id' :options='areas' col="2" placeholder='--Select One--'
                        :closeOnSelect='true' :required='false' />
                    <Textarea v-model='data.address' field='data.address' title='Address' col="6" :req='false' />
                    <Textarea v-model='data.note' field='data.note' col="6" title='Remarks' :req='false' />
                    <Input v-model='data.previous_due' field='data.previous_due' title='Previous Due' col="3"
                        :req='false' />

                    <Switch v-model='data.status' field='data.status' title='Status' on-label='Active'
                        off-label='Deactive' col="3" :req='true'>
                    </Switch>
                </div>
            </fieldset>
        </div>

        <div class="col-md-6 mb-3">
            <fieldset>
                <span class="legend">Contact Info</span>
                <div class="row g-3">
                    <Input v-model='data.name' field='data.name' title='Name' col="6" :req='true' />
                    <Input v-model='data.nid' field='data.nid' title='NID' col="6" :req='false' />
                    <Input v-model='data.mobile' field='data.mobile' title='Mobile' col="6" :req='true' />
                    <Input v-model='data.email' field='data.email' title='Email' col="6" :req='false' />

                </div>
            </fieldset>
        </div>
        <div class="col-md-6 mb-3">
            <fieldset>
                <span class="legend">Bank Information</span>
                <div class="row g-3">
                    <Input v-model='data.account_name' field='data.account_name' col="6" title='Account Name'
                        :req='false' />
                    <Input v-model='data.account_no' field='data.account_no' col="6" title='Account No' :req='false' />
                    <Select title='Bank' v-model='data.bank_id' field='data.bank_id' label='bank_name'
                        :reduce='(obj) => obj.id' col="6" :options='banks' placeholder='--Select One--'
                        :closeOnSelect='true' :required='false' />
                    <Input v-model='data.branch' field='data.branch' col="6" title='Branch' :req='false' />
                </div>
            </fieldset>
        </div>
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
                type: 'New',
                reg_date: this.$filter.today(),
                previous_due: 0,
                account_name: '',
                account_no: '',
                bank_id: null,
                branch: '',
                vat: 0,
                total_bandwidth: 0,
                total_amount: 0,
                total_include_amount: 0
            },
            packages: [],
            banks: [],
            areas: [],
            categories: [],

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
        getareas: function () {
            this.areas = [];
            var district_id = this.data.district_id;
            axios.get("/getareas/" + district_id).then((res) => {
                this.areas = res.data;
            });
        },
        getbanks: function () {
            this.areas = [];
            axios.get("/getbanks/").then((res) => {
                this.banks = res.data;
            });
        },
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
        getCurrentMonthDays(date) {
            const d = new Date(date);
            return new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
        },

    },
    created() {
        if (this.$route.params.id) {
            this.page_title = this.headline(this.model) + ' Edit';
            this.get_data(`${this.model}/${this.$route.params.id}`);
        } else {
            this.page_title = this.headline(this.model) + ' Create';
        }
        this.getbanks();
    },

    validators: {
        'data.name': function (value = null) { return Validator.value(value).required('Name is required'); },
        'data.mobile': function (value = null) { return Validator.value(value).required('Mobile is required'); },
    },
}

</script>
