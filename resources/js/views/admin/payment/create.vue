<template>
    <create-form @onSubmit='submit'>
        <!-- Main 2-Column Split: Left Side (Cards 1-4, 8 cols), Right Side (Cards 1-2, 4 cols) -->
        
        <!-- Left Column -->
        <div class="col-lg-8 col-12">
            <div class="d-flex flex-column gap-3">

                <!-- CARD 1: Transaction Setup & Entity Selection -->
                <div class="card payment_section_card shadow-sm border-0">
                    <div class="d-flex align-items-center mb-1">
                        <div class="card_capsule_badge">
                            <i class="far fa-id-card me-1"></i> Transaction Setup & Entity Selection
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mt-1">
                        <!-- 1. Payment Date -->
                        <date-picker id='date4' v-model='data.payment_date' field='data.payment_date' title='Payment Date'
                            placeholder='Payment Date' col='3' :req='false'></date-picker>

                        <!-- 2. Transaction Type Switch -->
                        <Switch v-model='data.payment_type' field='data.payment_type' title='Transaction Type' on-label='Receive'
                            off-label='Pay' onValue="Receive" offValue="Pay" col="3" :req='true'>
                        </Switch>

                        <!-- 3. Collection From / Payment To -->
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>{{ data.payment_type == 'Receive' ? 'Collection From:' : 'Payment To:' }}</span>
                                </label>
                                <select class="form-select custom_select_input" v-model="entity_type" @change="onEntityTypeChange">
                                    <template v-if="data.payment_type == 'Receive'">
                                        <option value="client">Client</option>
                                        <option value="supplier">Supplier / Scrap Buyer (Auction)</option>
                                        <option value="auction">All Open Auctions</option>
                                        <option value="employee">Employee (Loan / Adv.)</option>
                                    </template>
                                    <template v-else>
                                        <option value="supplier">Supplier</option>
                                        <option value="employee">Employee (Loan / Salary)</option>
                                        <option value="agent">Agent (Commission)</option>
                                        <option value="office_expense">Office Expense</option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- 4. Party Selector Dropdown (Dynamic based on Entity Type) -->
                        <template v-if="data.payment_type == 'Receive'">
                            <!-- Client -->
                            <Select v-if="entity_type == 'client'" title='Select Client' v-model='data.client_id' field='data.client_id'
                                label='name' :reduce='(obj) => obj.id' :options='clients' @change="onClientChange"
                                placeholder='-- Search / Select Client --' :readonly="$route.params.id ? true : false" :closeOnSelect='true' col="3"
                                :required='true' />

                            <!-- Supplier / Scrap Buyer -->
                            <Select v-else-if="entity_type == 'supplier'" title='Select Supplier' v-model='data.supplier_id' field='data.supplier_id' label='org_name'
                                :reduce='(obj) => obj.id' @change="onSupplierChange"
                                :options='$root.global.suppliers' placeholder='-- Search / Select Supplier --' :readonly="$route.params.id ? true : false"
                                :closeOnSelect='true' col="3" :required='true' />

                            <!-- All Open Auctions -->
                            <div v-else-if="entity_type == 'auction'" class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Open Auctions</label>
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1" @click="loadOpenAuctions" style="height: 38px;">
                                        <i class="fas fa-sync-alt"></i> Load All Open
                                    </button>
                                </div>
                            </div>

                            <!-- Employee -->
                            <Select v-else-if="entity_type == 'employee'" title='Select Employee' v-model='data.employee_id' field='data.employee_id' label='full_name'
                                :readonly="$route.params.id ? true : false" :reduce='(obj) => obj.id' :options='$root.global.employees'
                                placeholder='-- Search / Select Employee --' :closeOnSelect='true' @change="onEmployeeChange" col="3" :required='true' />
                        </template>

                        <template v-else>
                            <!-- Supplier -->
                            <Select v-if="entity_type == 'supplier'" title='Select Supplier' v-model='data.supplier_id' field='data.supplier_id' label='org_name'
                                :reduce='(obj) => obj.id' @change="onSupplierChange"
                                :options='$root.global.suppliers' placeholder='-- Search / Select Supplier --' :readonly="$route.params.id ? true : false"
                                :closeOnSelect='true' col="3" :required='true' />

                            <!-- Employee -->
                            <Select v-else-if="entity_type == 'employee'" title='Select Employee' v-model='data.employee_id' field='data.employee_id' label='full_name'
                                :readonly="$route.params.id ? true : false" :reduce='(obj) => obj.id' :options='$root.global.employees'
                                placeholder='-- Search / Select Employee --' :closeOnSelect='true' @change="onEmployeeChange" col="3" :required='true' />

                            <!-- Agent -->
                            <Select v-else-if="entity_type == 'agent'" title='Select Agent' v-model='data.agent_id' field='data.agent_id' label='full_name'
                                :reduce='(obj) => obj.id' @change="onAgentChange"
                                :options='agents' placeholder='-- Search / Select Agent --' :readonly="$route.params.id ? true : false"
                                :closeOnSelect='true' col="3" :required='true' />

                            <!-- Office Expense -->
                            <div v-else-if="entity_type == 'office_expense'" class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Office Expense</label>
                                    <div class="p-2 border rounded text-center small bg-light text-primary fw-semibold" style="height: 38px; line-height: 20px;">
                                        <i class="fas fa-building me-1"></i> Auto Loaded
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- CARD 2: Due Invoices & Receivable Bills -->
                <div class="card payment_section_card shadow-sm border-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="card_capsule_badge">
                            <i class="fas fa-list-ul me-1"></i>
                            {{ data.payment_type == 'Receive' ? 'Due Invoices & Receivable Bills' : 'Payable Invoices & Bills' }} ({{ invoices.length }})
                        </div>
                        <div class="form-check m-0 d-flex align-items-center gap-1 small fw-semibold text-secondary" v-if="invoices && invoices.length > 0">
                            <input type="checkbox" class="form-check-input mt-0" id="select_all_btn" :checked="isAllSelected" @change="toggleSelectAll" />
                            <label class="form-check-label cursor-pointer" for="select_all_btn" style="user-select: none;">Select All</label>
                        </div>
                    </div>

                    <div class="table-responsive custom_table_wrap" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 custom_payment_table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px; min-width: 44px;">
                                        <input type="checkbox" class="form-check-input" :checked="isAllSelected" @change="toggleSelectAll" title="Select / Deselect All" />
                                    </th>
                                    <th style="min-width: 160px;">Ref / Bill Info</th>
                                    <th style="min-width: 140px;">Account Head</th>
                                    <th class="text-end" style="min-width: 110px;">Total Bill</th>
                                    <th class="text-end" style="min-width: 100px;">Paid</th>
                                    <th class="text-end" style="min-width: 110px;">Due (৳)</th>
                                    <th class="text-end" style="width: 115px; min-width: 115px;">Adjust (৳)</th>
                                    <th class="text-center" style="width: 70px; min-width: 70px;">Closed?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="invoices && invoices.length > 0">
                                    <tr v-for="(inv, i) in invoices" :key="i" :class="{'table-row-selected': inv.checked}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input" v-model="inv.checked" @change="toggleInvoice(inv)" />
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border me-1">{{ inv.reference_type }}</span>
                                            <div class="ref_info_text d-inline-block small" v-html="inv.reference_info"></div>
                                        </td>
                                        <td>
                                            <span class="small text-muted font-monospace" v-if="$root.global && $root.global.allaccounts">
                                                {{ getAccountName(inv.account_id) }}
                                            </span>
                                            <span class="text-muted small" v-else>Loading...</span>
                                        </td>
                                        <td class="text-end font-monospace text-nowrap fw-semibold text-dark">{{ this.$filter.formatBDT(inv.amount) }}</td>
                                        <td class="text-end font-monospace text-nowrap text-secondary">{{ this.$filter.formatBDT(inv.paid_amount) }}</td>
                                        <td class="text-end font-monospace text-nowrap fw-bold text-danger">{{ this.$filter.formatBDT(inv.due_amount) }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                step="any"
                                                min="0"
                                                :max="inv.due_amount"
                                                class="form-control form-control-sm text-end fw-bold custom_adjust_input"
                                                v-model.number="inv.adjust_amount"
                                                :disabled="!inv.checked"
                                                @input="calculateTotals(inv)"
                                            />
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input" v-model="inv.is_closed" @change="onClosedToggle(inv)" title="Mark as fully closed" />
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="empty_table_state d-flex align-items-center justify-content-center gap-3">
                                                <div class="empty_icon_box">
                                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9 12H15M9 16H12M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M14 2V8H20" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <circle cx="16" cy="16" r="4" fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5"/>
                                                        <path d="M14.5 16L15.5 17L17.5 15" stroke="#0e2942" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </div>
                                                <div class="text-start">
                                                    <div class="fw-semibold text-secondary" style="font-size: 13.5px;">No outstanding bills found. Please select a party above.</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CARD 3: Fund Account & Transaction Amount -->
                <div class="card payment_section_card shadow-sm border-0">
                    <div class="d-flex align-items-center mb-1">
                        <div class="card_capsule_badge">
                            <i class="far fa-money-bill-alt me-1"></i> Fund Account & Transaction Amount
                        </div>
                    </div>
                    <div class="row g-3 align-items-start mt-1">
                        <!-- Fund Account -->
                        <div class="col-md-5">
                            <Select :title="data.payment_type == 'Receive' ? 'Deposit / Credit Fund Account' : 'Debit / Paid From Fund Account'"
                                v-model='data.fund_account_id' field='data.fund_account_id' label='name'
                                :reduce='(obj) => obj.id' :options='fundaccounts' placeholder='-- Select Fund Account --' :closeOnSelect='true'
                                col="12" :required='true' />
                            <div class="current_fund_balance small text-muted mt-1 font-monospace">
                                Current Fund Balance: <strong class="text-dark">৳ {{ this.$filter.money(fund) }}</strong>
                            </div>
                        </div>

                        <!-- Discount -->
                        <div class="col-md-2" v-if="data.payment_type == 'Receive'">
                            <Input v-model='data.discount' field='data.discount' title='Discount (-)'
                                @input="calculateTotals()" col="12" :req='false' />
                        </div>

                        <!-- Received / Paid Amount -->
                        <div :class="data.payment_type == 'Receive' ? 'col-md-3' : 'col-md-4'">
                            <Input v-model='data.amount' field='data.amount'
                                :title="data.payment_type == 'Receive' ? 'Received Amount (৳)' : 'Paid Amount (৳)'"
                                col="12" :req='true' />
                        </div>

                        <!-- Auto Adjust Button -->
                        <div :class="data.payment_type == 'Receive' ? 'col-md-2' : 'col-md-3'" class="pt-4">
                            <button type="button" class="btn auto_adjust_btn w-100 d-flex align-items-center justify-content-center gap-2" @click="reverseAdjustment" title="Auto distribute entered amount across due invoices">
                                <i class="fas fa-sliders-h"></i>
                                <span>Auto<br class="d-none d-sm-inline" /> Adjust</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: Payment Method & Channel Details -->
                <div class="card payment_section_card shadow-sm border-0">
                    <div class="d-flex align-items-center mb-2">
                        <div class="card_capsule_badge">
                            <i class="fas fa-credit-card me-1"></i> Payment Method & Channel Details
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <Select title='Payment Method' v-model='data.payment_method' field='data.payment_method' col="3"
                            label='name' :reduce='(obj) => obj.value' :options='$root.global.paymentmethods'
                            placeholder='-- Select One --' :closeOnSelect='true' :required='true' />

                        <Select title='Mobile Banking Type' v-model='data.mbanking_type' field='data.mbanking_type' col="3"
                            label='name' :reduce='(obj) => obj.value' :options='$root.global.mbankings'
                            placeholder='-- Select One --' :closeOnSelect='true' :required='false' />

                        <Input v-model='data.trxid' field='data.trxid' title='Transaction ID / TrxID' col="3" :req='false' />
                        <Input v-model='data.chequeno' field='data.chequeno' title='Cheque No' col="3" :req='false' />

                        <Select title='Bank' v-model='data.bank_id' field='data.bank_id' label='bank_name'
                            :reduce='(obj) => obj.id' col="3" :options='banks' placeholder='-- Select Bank --'
                            :closeOnSelect='true' :required='false' />

                        <Input v-model='data.branch' field='data.branch' title='Branch' col="3" :req='false' />
                        <Input v-model='data.account_name' field='data.account_name' title='Account Name' col="3" :req='false' />
                        <Input v-model='data.accountno' field='data.accountno' title='Account No' col="3" :req='false' />

                        <Switch v-model='data.status' field='data.status' title='Status' on-label='Active' off-label='Deactive'
                            col="3" :req='true'>
                        </Switch>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4 col-12">
            <div class="d-flex flex-column gap-3">

                <!-- RIGHT CARD 1: Party Details -->
                <div class="card payment_section_card shadow-sm border-0">
                    <div class="d-flex align-items-center mb-3">
                        <div class="card_capsule_badge">
                            <i class="far fa-building me-1"></i>
                            {{ entity_type == 'client' ? 'Client Details' : (entity_type == 'supplier' ? 'Supplier Details' : (entity_type == 'employee' ? 'Employee Details' : (entity_type == 'agent' ? 'Agent Details' : 'Party Details'))) }}
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!payto || !payto.name" class="empty_party_state py-3 d-flex align-items-center justify-content-center gap-3">
                        <div class="party_avatar_empty">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div class="fw-bold text-dark fs-6">No Party Selected</div>
                    </div>

                    <!-- Filled State -->
                    <div v-else class="party_details_box">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="party_avatar_filled">
                                {{ (payto.name || 'U').charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ payto.name }}</h6>
                                <div class="small text-muted font-monospace" v-if="payto.mobile">
                                    <i class="fas fa-phone-alt me-1 text-success"></i> {{ payto.mobile }}
                                </div>
                            </div>
                        </div>
                        <div class="party_meta_list small text-secondary">
                            <div class="d-flex align-items-start gap-2 mb-1" v-if="payto.address">
                                <i class="fas fa-map-marker-alt text-danger mt-1"></i>
                                <span>{{ payto.address }}</span>
                            </div>
                            <div class="mt-2" v-if="payto.info">
                                <div v-html="payto.info"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CARD 2: Transaction Summary -->
                <div class="card payment_section_card shadow-sm border-0">
                    <div class="d-flex align-items-center mb-3">
                        <div class="card_capsule_badge">
                            <i class="far fa-newspaper me-1"></i> Transaction Summary
                        </div>
                    </div>

                    <!-- Total Due Box (Pink / Red) -->
                    <div class="summary_box_due mb-3 p-3 rounded-3 d-flex justify-content-between align-items-center">
                        <div class="summary_label_due text-uppercase fw-bold">TOTAL DUE:</div>
                        <div class="summary_value_due fw-bold font-monospace">৳ {{ this.$filter.money(totals.total_due) }}</div>
                    </div>

                    <!-- Net Collected Amount Box (Navy) -->
                    <div class="summary_box_net mb-3 p-3 rounded-3 text-center">
                        <div class="summary_label_net text-uppercase fw-bold mb-1">
                            {{ data.payment_type == 'Receive' ? 'NET COLLECTED AMOUNT' : 'NET PAID AMOUNT' }}
                        </div>
                        <div class="summary_value_net fw-bold font-monospace">
                            ৳ {{ this.$filter.money(data.amount) }}
                        </div>
                    </div>

                    <!-- In Words Section -->
                    <div class="in_words_section pt-2 border-top">
                        <div class="d-flex align-items-center gap-1 text-muted small fw-bold mb-1">
                            <span class="in_words_icon">AB</span>
                            <span>IN WORDS:</span>
                        </div>
                        <div class="in_words_text fw-bold text-dark small">
                            {{ this.$filter.numberToEnglishBD(data.amount) }}.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </create-form>
</template>

<script>
const model = 'payment';

export default {
    data() {
        return {
            model: model,
            page_title: '',
            entity_type: 'client', // 'client', 'supplier', 'auction', 'employee', 'agent', 'office_expense'
            data: {
                payment_date: this.$filter.today(),
                payment_type: 'Receive',
                client_id: null,
                supplier_id: null,
                employee_id: null,
                agent_id: null,
                office_expense: false,
                fund_account_id: 24,
                payment_method: null,
                status: 'active',
                discount: 0,
                amount: 0,
                payment_details: [],
                bank_id: null,
                account_name: '',
                accountno: '',
                branch: '',
                chequeno: '',
                trxid: '',
                mbanking_type: null,
            },
            fund: 0,
            banks: [],
            clients: [],
            agents: [],
            fundaccounts: [],
            payto: {
                name: '',
                mobile: '',
                last_receive: 0,
                last_payment: 0,
                total_amount: 0,
                paid_amount: 0,
                address: '',
                info: ''
            },
            invoices: [],   // pending due invoices
            selectedInvoices: [],
            totals: {
                total_receive: 0,
                total_due: 0
            }
        };
    },

    provide() {
        return {
            validate: this.validation,
        };
    },
    watch: {
        'data.fund_account_id': function () {
            this.getFundBalance();
        },
        'data.payment_type': function (newVal, oldVal) {
            if (newVal !== oldVal) {
                if (newVal === 'Receive') {
                    this.entity_type = 'client';
                } else {
                    this.entity_type = 'supplier';
                }
                this.resetSelectionData();
            }
        }
    },
    computed: {
        allAccounts() {
            return this.$root?.global?.allaccounts || [];
        },
        isAllSelected() {
            return this.invoices && this.invoices.length > 0 && this.invoices.every(inv => inv.checked);
        }
    },
    methods: {
        onEntityTypeChange() {
            this.resetSelectionData();
            if (this.entity_type === 'office_expense') {
                this.data.office_expense = true;
                this.get_ofice_expenses();
            } else if (this.entity_type === 'auction') {
                this.loadOpenAuctions();
            }
        },

        resetSelectionData() {
            this.data.client_id = null;
            this.data.supplier_id = null;
            this.data.employee_id = null;
            this.data.agent_id = null;
            this.data.office_expense = false;
            this.payto = {
                name: '',
                mobile: '',
                last_receive: 0,
                last_payment: 0,
                total_amount: 0,
                paid_amount: 0,
                address: '',
                info: ''
            };
            this.invoices = [];
            this.selectedInvoices = [];
            this.data.discount = 0;
            this.data.amount = 0;
            this.data.payment_details = [];
            this.calculateTotals();
        },

        loadOpenAuctions() {
            // Load open auctions / scrap dues
            axios.get('getofficeexpenses').catch(() => {});
        },

        onClientChange(client_id) {
            this.data.agent_id = '';
            this.data.supplier_id = '';
            this.data.employee_id = '';
            this.get_client_info(client_id);
            this.get_client_due_invoices(client_id);
        },

        onSupplierChange(supplier_id) {
            this.data.agent_id = '';
            this.data.client_id = '';
            this.data.employee_id = '';
            this.get_supplier_info(supplier_id);
            this.get_purchase_invoices(supplier_id);
        },

        onEmployeeChange(empid) {
            this.data.agent_id = '';
            this.data.client_id = '';
            this.data.supplier_id = '';
            this.get_emp_info(empid);
            if (this.data.payment_type == 'Receive') {
                this.get_employee_loan_installment(empid);
            }
            if (this.data.payment_type == 'Pay') {
                this.get_employee_payable(empid);
            }
        },

        onAgentChange(agent_id) {
            this.data.client_id = '';
            this.data.supplier_id = '';
            this.data.employee_id = '';
            this.get_agent_info(agent_id);
            this.get_agent_payable_commissions(agent_id);
        },

        get_agent_info(agent_id) {
            axios.get(`/getagentinfo/${agent_id}`).then(res => {
                const ref = res.data;
                this.payto = {
                    name: ref.full_name,
                    mobile: ref.mobile,
                    address: ref.address,
                    info: (ref.organization ? 'Organization: ' + ref.organization : '') + (ref.designation ? '<br>Designation: ' + ref.designation : '')
                };
                this.data.payment_method = 'Bank';
                this.data.account_name = '';
                this.data.accountno = '';
                this.data.bank_id = null;
                this.data.branch = '';
            }).catch(() => {
                this.payto = {};
            });
        },

        get_agent_payable_commissions(agent_id) {
            axios.get(`getagentpayabledatas/${agent_id}`).then(res => {
                this.invoices = res.data.map(inv => ({
                    checked: false,
                    reference_type: inv.reference_type,
                    reference_id: inv.reference_id,
                    account_id: inv.account_id,
                    reference_info: inv.reference_info,
                    amount: inv.amount,
                    paid_amount: inv.paid_amount,
                    due_amount: inv.due_amount,
                    adjust_amount: 0,
                    is_closed: false
                }));
                this.calculateTotals();
            });
        },

        get_emp_info(empid) {
            axios.get(`/employeeinfo/${empid}`).then(res => {
                const ref = res.data;
                this.payto = {
                    name: ref.full_name,
                    mobile: ref.mobile,
                    address: ref.address,
                    info: ''
                };
                // Employee bank info
                this.data.payment_method = 'Bank';
                this.data.account_name = ref.account_name;
                this.data.accountno = ref.account_no;
                this.data.bank_id = ref.bank_id;
                this.data.branch = ref.branch;
            }).catch(() => {
                this.payto = {};
            });
        },

        get_supplier_info(id) {
            axios.get(`/getsupplierbyid/${id}`).then(res => {
                const ref = res.data;
                this.payto = {
                    name: (ref.supid ? ref.supid + ' - ' : '') + ref.org_name,
                    mobile: ref.mobile,
                    address: ref.address,
                    info: 'Contact Person: ' + (ref.name || '') + (ref.designation ? ' | Designation: ' + ref.designation : '')
                };
                // Supplier bank info
                this.data.payment_method = 'Bank';
                this.data.account_name = ref.account_name;
                this.data.accountno = ref.account_no;
                this.data.bank_id = ref.bank_id;
                this.data.branch = ref.branch;
            }).catch(() => {
                this.payto = {};
            });
        },

        get_client_info(id) {
            axios.get(`/getclientbyid/${id}`).then(res => {
                const ref = res.data;
                this.payto = {
                    name: (ref.clientid ? ref.clientid + ' - ' : '') + (ref.name || ref.org_name),
                    mobile: ref.mobile,
                    address: ref.address,
                    info: (ref.reg_date ? 'Reg Date: ' + ref.reg_date : '') + (ref.area ? ' | Area: ' + ref.area.area_name : '')
                };
                // client bank info
                this.data.payment_method = 'Bank';
                this.data.account_name = ref.account_name;
                this.data.accountno = ref.account_no;
                this.data.bank_id = ref.bank_id;
                this.data.branch = ref.branch;
                this.data.total_due = this.totals.total_due;
            }).catch(() => {
                this.payto = {};
            });
        },

        get_ofice_expenses() {
            axios.get(`getofficeexpenses`).then(res => {
                this.invoices = res.data.map(inv => ({
                    checked: false,
                    reference_type: inv.reference_type,
                    reference_id: inv.reference_id,
                    account_id: inv.account_id,
                    reference_info: inv.reference_info,
                    amount: inv.amount,
                    paid_amount: inv.paid_amount,
                    due_amount: inv.due_amount,
                    adjust_amount: 0,
                    is_closed: false
                }));
                this.payto = {
                    name: 'Office Expenses',
                    mobile: '',
                    address: '',
                    info: 'General office operational expenses and pending bills.'
                };
                this.calculateTotals();
            });
        },

        get_client_due_invoices(id) {
            axios.get(`getclientdueinvoices/${id}`).then(res => {
                this.invoices = res.data.map(inv => ({
                    checked: false,
                    reference_type: inv.reference_type,
                    reference_id: inv.reference_id,
                    account_id: inv.account_id,
                    reference_info: inv.reference_info,
                    amount: inv.amount,
                    paid_amount: inv.paid_amount,
                    due_amount: inv.due_amount,
                    adjust_amount: 0,
                    is_closed: false
                }));
                this.calculateTotals();
            });
        },

        get_purchase_invoices(supid) {
            axios.get(`getpurchaseinvoices/${supid}`).then(res => {
                this.invoices = res.data.map(inv => ({
                    checked: false,
                    reference_type: inv.reference_type,
                    reference_id: inv.reference_id,
                    account_id: inv.account_id,
                    reference_info: inv.reference_info,
                    amount: inv.amount,
                    paid_amount: inv.paid_amount,
                    due_amount: inv.due_amount,
                    adjust_amount: 0,
                    is_closed: false
                }));
                this.calculateTotals();
            });
        },

        get_employee_payable(empid) {
            axios.get(`getemppayabledatas/${empid}`).then(res => {
                this.invoices = res.data.map(inv => ({
                    checked: false,
                    reference_type: inv.reference_type,
                    reference_id: inv.reference_id,
                    account_id: inv.account_id,
                    reference_info: inv.reference_info,
                    amount: inv.amount,
                    paid_amount: inv.paid_amount,
                    due_amount: inv.due_amount,
                    adjust_amount: 0,
                    is_closed: false
                }));
                this.calculateTotals();
            });
        },

        get_employee_loan_installment(empid) {
            axios.get(`getemploaninstallment/${empid}`).then(res => {
                this.invoices = res.data.map(inv => ({
                    checked: false,
                    reference_type: inv.reference_type,
                    reference_id: inv.reference_id,
                    account_id: inv.account_id,
                    reference_info: inv.reference_info,
                    amount: inv.amount,
                    paid_amount: inv.paid_amount,
                    due_amount: inv.due_amount,
                    adjust_amount: 0,
                    is_closed: false
                }));
                this.calculateTotals();
            });
        },

        onClosedToggle(inv) {
            const row = this.data.payment_details.find(d =>
                d.reference_type === inv.reference_type &&
                d.reference_id === inv.reference_id
            );

            if (row) {
                row.is_closed = inv.is_closed;
            }
        },

        toggleInvoice(inv) {
            if (inv.checked) {
                inv.adjust_amount = inv.due_amount;

                const exists = this.data.payment_details.find(
                    d => d.reference_type === inv.reference_type &&
                         d.reference_id === inv.reference_id
                );

                if (!exists) {
                    this.data.payment_details.push({
                        reference_type: inv.reference_type,
                        reference_id: inv.reference_id,
                        account_id: inv.account_id,
                        amount: inv.adjust_amount,
                        is_closed: inv.is_closed
                    });
                }
            } else {
                this.data.payment_details = this.data.payment_details.filter(
                    d => !(d.reference_type === inv.reference_type &&
                           d.reference_id === inv.reference_id)
                );
                inv.adjust_amount = 0;
            }

            this.calculateTotals();
        },

        toggleSelectAll() {
            const willCheck = !this.isAllSelected;
            this.invoices.forEach(inv => {
                inv.checked = willCheck;
                if (willCheck) {
                    inv.adjust_amount = inv.due_amount;
                    const exists = this.data.payment_details.find(
                        d => d.reference_type === inv.reference_type && d.reference_id === inv.reference_id
                    );
                    if (!exists) {
                        this.data.payment_details.push({
                            reference_type: inv.reference_type,
                            reference_id: inv.reference_id,
                            account_id: inv.account_id,
                            amount: inv.adjust_amount,
                            is_closed: inv.is_closed
                        });
                    }
                } else {
                    inv.adjust_amount = 0;
                }
            });

            if (!willCheck) {
                this.data.payment_details = [];
            }
            this.calculateTotals();
        },

        calculateTotals() {
            let total = 0;
            let total_adjustment = 0;

            this.invoices.forEach(inv => {
                total += Number(inv.due_amount || 0);

                if (inv.checked) {
                    total_adjustment += Number(inv.adjust_amount || 0);

                    if (Number(inv.adjust_amount) === Number(inv.due_amount)) {
                        inv.is_closed = true;
                    } else {
                        inv.is_closed = false;
                    }

                    const row = this.data.payment_details.find(d =>
                        d.reference_type === inv.reference_type &&
                        d.reference_id === inv.reference_id
                    );

                    if (row) {
                        row.amount = inv.adjust_amount;
                        row.is_closed = inv.is_closed;
                    }
                }
            });

            this.totals.total_due = total;
            this.totals.total_receive = total_adjustment;
            this.data.amount = total_adjustment - (this.data.discount || 0);
        },

        reverseAdjustment() {
            let distributable = Number(this.data.amount || 0);

            if (distributable <= 0) {
                this.invoices.forEach(inv => {
                    inv.adjust_amount = 0;
                    inv.checked = false;
                    inv.is_closed = false;
                });
                this.data.payment_details = [];
                this.calculateTotals();
                return;
            }

            // 1. reset
            this.invoices.forEach(inv => {
                inv.adjust_amount = 0;
                inv.checked = false;
                inv.is_closed = false;
            });

            // 2. distribute sequentially
            this.invoices.forEach(inv => {
                if (distributable <= 0) return;

                const due = Number(inv.due_amount) || 0;
                if (due <= 0) return;

                const applied = Math.min(due, distributable);
                inv.adjust_amount = applied;
                distributable -= applied;
            });

            // 3. set checked & is_closed
            this.invoices.forEach(inv => {
                const due = Number(inv.due_amount) || 0;
                const adj = Number(inv.adjust_amount) || 0;

                inv.is_closed = adj === due && due > 0;
                inv.checked = adj > 0;
            });

            // 4. payment_details full sync
            this.invoices.forEach(inv => {
                const idx = this.data.payment_details.findIndex(d =>
                    d.reference_type === inv.reference_type &&
                    String(d.reference_id) === String(inv.reference_id)
                );

                if (inv.checked) {
                    if (idx === -1) {
                        this.data.payment_details.push({
                            reference_type: inv.reference_type,
                            reference_id: inv.reference_id,
                            account_id: inv.account_id,
                            amount: Number(inv.adjust_amount) || 0,
                            is_closed: inv.is_closed
                        });
                    } else {
                        this.data.payment_details[idx].amount = Number(inv.adjust_amount) || 0;
                        this.data.payment_details[idx].is_closed = inv.is_closed;
                    }
                } else {
                    if (idx !== -1) {
                        this.data.payment_details.splice(idx, 1);
                    }
                }
            });

            this.calculateTotals();
        },

        getAccountName(account_id) {
            const account = this.allAccounts.find(acc => acc.id == account_id);
            return account ? account.name : '';
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
                    var form = document.getElementById('form');
                    var formData = new FormData(form);

                    if (this.data.id) {
                        this.update(this.model, this.data, this.data.id);
                    } else {
                        this.store(this.model, this.data);
                    }
                }
            });
        },

        getClients() {
            axios.get(`clients/`)
                .then((response) => {
                    this.clients = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getFundAccounts() {
            axios.get(`getfundaccounts/`)
                .then((response) => {
                    this.fundaccounts = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getbanks: function () {
            this.areas = [];
            axios.get("/getbanks/").then((res) => {
                this.banks = res.data;
            });
        },

        getPaymentData() {
            axios.get(`${this.model}/${this.$route.params.id}`)
                .then((response) => {
                    this.data = response.data;
                    this.invoices = response.data.invoices || [];
                    this.data.payment_details = response.data.payment_details || [];
                    this.payto = response.data.payto || {};
                    this.totals = response.data.totals || { total_receive: 0, total_due: 0 };

                    // deduce entity_type
                    if (this.data.client_id) {
                        this.entity_type = 'client';
                    } else if (this.data.supplier_id) {
                        this.entity_type = 'supplier';
                    } else if (this.data.employee_id) {
                        this.entity_type = 'employee';
                    } else if (this.data.agent_id) {
                        this.entity_type = 'agent';
                    } else if (this.data.office_expense) {
                        this.entity_type = 'office_expense';
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getFundBalance() {
            let fund_account_id = this.data.fund_account_id;
            if (!fund_account_id) return;
            axios.get(`getfunds/${fund_account_id}`)
                .then((response) => {
                    this.fund = response.data;
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
        }
    },

    created() {
        if (this.$route.params.id) {
            this.page_title = this.headline(this.model) + ' Edit';
            this.getPaymentData();
        } else {
            this.page_title = this.headline(this.model) + ' Create';
            this.data.fund_account_id = 24;
            this.getFundBalance();
        }
        this.getClients();
        this.getFundAccounts();
        this.getbanks();
        this.getAgents();
    },

    validators: {
        'data.amount': function (value = null) {
            var vm = this;

            return Validator.value(value)
                .required('Amount is required')
                .custom(function () {
                    const amount = parseFloat(value);
                    const totalDue = parseFloat(vm.totals?.total_due || 0);
                    const fund = parseFloat(vm.fund || 0);

                    if (isNaN(amount) || amount <= 0) {
                        return 'Amount must be greater than 0';
                    }

                    if (!vm.data?.id && totalDue > 0 && amount > totalDue) {
                        return 'Amount cannot be greater than total selected amount';
                    }

                    if (!vm.data?.id && vm.data?.payment_type === 'Pay' && amount > fund) {
                        return 'Transaction failed due to insufficient fund balance.';
                    }
                });
        },

        'data.payment_type': function (value = null) { return Validator.value(value).required('Payment Type is required'); },
        'data.payment_method': function (value = null) { return Validator.value(value).required('Payment Method is required'); },
        'data.fund_account_id': function (value = null) { return Validator.value(value).required('Fund Account is required'); },

        'data.client_id': function (value = null) {
            var vm = this;
            if (vm.data?.payment_type === 'Receive' && vm.entity_type === 'client') {
                return Validator.value(value).required('Client is required');
            }
        },

        'data.supplier_id': function (value = null) {
            var vm = this;
            if ((vm.data?.payment_type === 'Pay' || vm.data?.payment_type === 'Receive') && vm.entity_type === 'supplier') {
                return Validator.value(value).required('Supplier is required');
            }
        },

        'data.employee_id': function (value = null) {
            var vm = this;
            if (vm.entity_type === 'employee') {
                return Validator.value(value).required('Employee is required');
            }
        },

        'data.agent_id': function (value = null) {
            var vm = this;
            if (vm.entity_type === 'agent') {
                return Validator.value(value).required('Agent is required');
            }
        },
    },
}
</script>

<style scoped>
.payment_section_card {
    border-radius: 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0 !important;
    padding: 18px 20px;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02) !important;
}
.payment_section_card:hover {
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04) !important;
}

/* Capsule badge on top left of each card */
.card_capsule_badge {
    display: inline-flex !important;
    width: fit-content !important;
    max-width: max-content !important;
    align-items: center;
    gap: 6px;
    border: 1.5px solid #0e2942;
    border-radius: 50px;
    padding: 3px 14px;
    font-size: 13px;
    font-weight: 700;
    color: #0e2942;
    background: #ffffff;
    letter-spacing: 0.2px;
    box-shadow: 0 1px 3px rgba(14, 41, 66, 0.05);
    white-space: nowrap !important;
    flex-shrink: 0 !important;
}

.custom_select_input {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    height: 38px;
    font-size: 13.5px;
    color: #1e293b;
    background-color: #ffffff;
}
.custom_select_input:focus {
    border-color: #0e2942;
    box-shadow: 0 0 0 2px rgba(14, 41, 66, 0.15);
}

/* Custom Payment Table */
.custom_table_wrap {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.custom_payment_table {
    min-width: 720px;
}
.custom_payment_table thead th {
    background-color: #0e2942 !important;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 700;
    padding: 10px 12px;
    letter-spacing: 0.3px;
    border: none;
    vertical-align: middle;
    white-space: nowrap !important;
}
.custom_payment_table tbody td {
    font-size: 13px;
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
}
.custom_payment_table tbody td.text-nowrap {
    white-space: nowrap !important;
}
.table-row-selected {
    background-color: rgba(14, 41, 66, 0.035) !important;
}
.custom_adjust_input {
    min-width: 85px;
    border-radius: 6px;
    border-color: #cbd5e1;
    font-size: 13px;
    background-color: #ffffff;
}
.custom_adjust_input:disabled {
    background-color: #f1f5f9 !important;
    color: #64748b !important;
    border-color: #e2e8f0 !important;
    cursor: not-allowed;
    opacity: 0.9;
}
.custom_adjust_input:focus {
    border-color: #0e2942;
    box-shadow: 0 0 0 2px rgba(14, 41, 66, 0.15);
}

/* Auto Adjust button */
.auto_adjust_btn {
    background-color: #0e2942 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    height: 38px;
    line-height: 1.15;
    padding: 4px 14px !important;
    transition: all 0.2s ease-in-out;
}
.auto_adjust_btn:hover {
    background-color: #183c5e !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(14, 41, 66, 0.25);
}

/* Party Details Component */
.party_avatar_empty {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #0e2942;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 2px 8px rgba(14, 41, 66, 0.2);
    flex-shrink: 0;
}
.party_avatar_filled {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0e2942, #1a4975);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(14, 41, 66, 0.25);
    flex-shrink: 0;
}

/* Summary Cards */
.summary_box_due {
    background-color: #fee2e2;
    border: 1px solid #fecdd3;
    border-radius: 10px;
}
.summary_label_due {
    color: #991b1b;
    font-size: 13px;
    letter-spacing: 0.5px;
}
.summary_value_due {
    color: #dc2626;
    font-size: 26px;
    font-weight: 800;
}

.summary_box_net {
    background-color: #0e2942;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(14, 41, 66, 0.2);
}
.summary_label_net {
    color: #94a3b8;
    font-size: 11.5px;
    letter-spacing: 0.6px;
}
.summary_value_net {
    color: #ffffff;
    font-size: 30px;
    font-weight: 800;
}

.in_words_icon {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 10px;
    font-weight: 700;
    color: #475569;
}
.in_words_text {
    color: #1e293b;
    line-height: 1.4;
}

.btn-xs {
    padding: 2px 8px;
    font-size: 11px;
    border-radius: 4px;
}
</style>
