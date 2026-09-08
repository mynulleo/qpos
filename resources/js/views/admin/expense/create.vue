<template>
  <create-form @onSubmit="submit">
    <!-- 🌟 Top Hero / Stat Summary Bar -->
    <div class="col-12 mb-2">
      <div class="card border-0 shadow-sm rounded-3 bg-light">
        <div class="card-body p-3">
          <div class="row g-3 align-items-center justify-content-between">
            <div class="col-md-6 d-flex align-items-center gap-3">
              <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                <i class="fas fa-file-invoice-dollar fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0 text-dark">Expense Voucher Entry (দৈনন্দিন খরচ ভাউচার)</h5>
                <p class="text-muted small mb-0">Record and track office, operational, staff, and maintenance expenses.</p>
              </div>
            </div>

            <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2">
              <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-end">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Total Amount</div>
                <div class="fs-4 fw-bold font-monospace text-success">
                  Tk. {{ $filter.formatBDT(data.total_amount || 0) }}
                </div>
              </div>
              <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-center">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Total Heads</div>
                <div class="fs-4 fw-bold font-monospace text-primary">
                  {{ (data.expense_details || []).length }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📋 Section 1: General Voucher Information -->
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-3 mb-2 expense-card">
        <div class="card-header bg-white py-2 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold small text-dark d-flex align-items-center gap-2">
            <i class="fas fa-calendar-alt text-primary"></i> General Information (ভাউচারের তথ্য)
          </span>
          <span class="badge bg-light text-secondary border font-monospace" v-if="data.expense_date">
            <i class="fas fa-clock me-1"></i> {{ data.expense_date }}
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <date-picker
              id="date1"
              v-model="data.expense_date"
              field="data.expense_date"
              title="Expense Date (খরচের তারিখ)"
              placeholder="Expense Date"
              col="3"
              :req="true"
            ></date-picker>

            <Select
              title="Workorder (ওয়ার্কঅর্ডার - Optional)"
              v-model="data.workorder_id"
              col="3"
              field="data.workorder_id"
              label="name"
              :reduce="(obj) => obj.id"
              :options="workorders"
              placeholder="-- Select Workorder --"
              :closeOnSelect="true"
              :appendToBody="true"
              :required="false"
            />

            <Select
              title="Employee In-Charge (দায়িত্বপ্রাপ্ত কর্মী)"
              v-model="data.employee_id"
              col="3"
              field="data.employee_id"
              label="full_name"
              :reduce="(obj) => obj.id"
              :options="$root.global.employees"
              placeholder="-- Select Employee --"
              :closeOnSelect="true"
              :appendToBody="true"
              :required="false"
            />

            <div class="col-md-3">
              <label class="form-label fw-bold small text-secondary mb-1">Status (স্ট্যাটাস)</label>
              <div class="pt-1">
                <Switch
                  v-model="data.status"
                  field="data.status"
                  title="status"
                  on-label="Active"
                  off-label="Inactive"
                  :req="true"
                ></Switch>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📊 Section 2: Expense Breakdown & Account Heads -->
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-3 expense-card">
        <div class="card-header bg-white py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-bold small text-dark d-flex align-items-center gap-2">
              <i class="fas fa-list-ol text-primary"></i> Expense Items & Account Breakdown (খরচের খাত ও বিবরণী)
            </span>
            <span class="badge bg-primary rounded-pill px-2">
              {{ (data.expense_details || []).length }} Items
            </span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button
              type="button"
              class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1"
              @click.prevent="addExpenseDetailsRow">
              <i class="fas fa-plus-circle"></i> Add Expense Item
            </button>
          </div>
        </div>

        <div class="card-body p-3">
          <!-- Quick Add Common Expense Category Chips -->
          <div class="mb-3 p-2 bg-light rounded-3 border d-flex flex-wrap align-items-center gap-2" v-if="quickExpenseOptions.length > 0">
            <span class="small fw-bold text-secondary d-flex align-items-center gap-1 me-1">
              <i class="fas fa-bolt text-warning"></i> Quick Add Heads:
            </span>
            <button
              type="button"
              v-for="opt in quickExpenseOptions"
              :key="opt.id"
              class="btn btn-xs btn-outline-secondary bg-white py-1 px-2 rounded-pill shadow-xs d-inline-flex align-items-center gap-1"
              style="font-size: 11px;"
              @click="addPredefinedExpenseRow(opt)">
              <i class="fas fa-plus text-success" style="font-size: 9px;"></i> {{ opt.name }}
            </button>
          </div>

          <!-- Expense Details Table -->
          <div class="table-responsive rounded-2 border expense-table-container">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                  <th style="width: 4%;" class="text-center">#</th>
                  <th style="width: 32%;">
                    <i class="fas fa-landmark text-primary me-1"></i> Expense Account Head (খাত) <span class="text-danger">*</span>
                  </th>
                  <th style="width: 40%;">
                    <i class="fas fa-comment-alt text-secondary me-1"></i> Narration / Description (বিবরণ)
                  </th>
                  <th style="width: 16%;" class="text-end">
                    <i class="fas fa-money-bill-wave text-success me-1"></i> Amount (টাকা) <span class="text-danger">*</span>
                  </th>
                  <th style="width: 8%;" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(expensedetail, index) in data.expense_details" :key="index">
                  <!-- Row Number -->
                  <td class="text-center font-monospace small text-muted">
                    {{ index + 1 }}
                  </td>

                  <!-- Account Select using v-select with appendToBody -->
                  <td>
                    <div class="table-vselect-wrapper">
                      <v-select
                        v-model="expensedetail.account_id"
                        label="name"
                        :reduce="(obj) => obj.id"
                        :options="accounts"
                        placeholder="-- Select Expense Account --"
                        :closeOnSelect="true"
                        :appendToBody="true"
                      />
                    </div>
                  </td>

                  <!-- Narration -->
                  <td>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      placeholder="Enter details / remarks (e.g. Office electricity bill)..."
                      v-model="expensedetail.narration"
                    />
                  </td>

                  <!-- Amount with Currency Styling -->
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light fw-bold font-monospace">Tk</span>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control text-end font-monospace fw-bold text-dark"
                        v-model.number="expensedetail.amount"
                        @input="calculateTotals"
                        placeholder="0.00"
                      />
                    </div>
                  </td>

                  <!-- Actions -->
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <button
                        type="button"
                        class="btn btn-outline-danger btn-sm p-1 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 28px; height: 28px;"
                        title="Delete Row"
                        @click.prevent="removeExpenseDetails(index)"
                        v-if="data.expense_details.length > 1">
                        <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                      </button>

                      <button
                        type="button"
                        class="btn btn-outline-primary btn-sm p-1 rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 28px; height: 28px;"
                        title="Add Next Row"
                        @click.prevent="addExpenseDetailsRow">
                        <i class="fas fa-plus" style="font-size: 11px;"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="3" class="text-end fw-bold py-2">
                    <span class="text-muted small me-2 text-uppercase">Total Expense (সর্বমোট খরচ):</span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-success fs-6 py-2">
                    Tk. {{ $filter.formatBDT(data.total_amount || 0) }}
                  </td>
                  <td class="text-center py-2">
                    <button
                      type="button"
                      class="btn btn-xs btn-link text-primary text-decoration-none fw-bold p-0"
                      @click="addExpenseDetailsRow">
                      + Add Row
                    </button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
import axios from "axios";

const model = "expense";

export default {
  data() {
    return {
      model: model,
      page_title: "",
      data: {
        expense_date: this.$filter.today(),
        workorder_id: null,
        employee_id: null,
        status: "active",
        total_amount: 0,
        expense_details: [
          {
            account_id: null,
            narration: "",
            amount: 0,
          },
        ],
      },
      accounts: [],
      workorders: [],
    };
  },

  computed: {
    quickExpenseOptions() {
      if (!this.accounts || this.accounts.length === 0) return [];
      return this.accounts.slice(0, 6);
    },
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  watch: {
    "data.expense_details": {
      handler() {
        this.calculateTotals();
      },
      deep: true,
    },
  },

  methods: {
    submit: function () {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          console.log(this.validation.allErrors());
          this.$toast(
            "You need to fill " + error + " more empty mandatory fields",
            "warning"
          );
          return false;
        }

        // Validate detail lines
        const invalidDetail = this.data.expense_details.find(
          (d) => !d.account_id || parseFloat(d.amount) <= 0
        );
        if (invalidDetail) {
          this.$toast(
            "Please select an account head and enter a valid amount for all expense rows",
            "warning"
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
      axios
        .get(`getworkorders/`)
        .then((response) => {
          this.workorders = response.data || [];
        })
        .catch((error) => {
          console.error(error);
        });
    },

    getAccountsByType() {
      const accounttype = "Expense";
      axios
        .get(`/accountsbytype/${accounttype}`)
        .then((response) => {
          this.accounts = response.data || [];
        })
        .catch((error) => {
          console.error(error);
        });
    },

    calculateTotals() {
      let totalAmt = 0;
      if (this.data.expense_details && Array.isArray(this.data.expense_details)) {
        this.data.expense_details.forEach((detail) => {
          totalAmt += parseFloat(detail.amount) || 0;
        });
      }
      this.data.total_amount = totalAmt;
    },

    addExpenseDetailsRow() {
      this.data.expense_details.push({
        account_id: null,
        narration: "",
        amount: 0,
      });
    },

    addPredefinedExpenseRow(acc) {
      if (
        this.data.expense_details.length === 1 &&
        !this.data.expense_details[0].account_id &&
        !this.data.expense_details[0].amount
      ) {
        this.data.expense_details[0].account_id = acc.id;
        return;
      }
      this.data.expense_details.push({
        account_id: acc.id,
        narration: "",
        amount: 0,
      });
    },

    removeExpenseDetails(index) {
      if (this.data.expense_details.length > 1) {
        this.data.expense_details.splice(index, 1);
        this.calculateTotals();
      }
    },
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + " Edit";
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + " Create";
    }
    this.getAccountsByType();
    this.getWorkorders();
  },

  validators: {
    "data.expense_date": function (value = null) {
      return Validator.value(value).required("Expense Date is required");
    },
    "data.total_amount": function (value = null) {
      return Validator.value(value)
        .required("Total Amount is required")
        .greaterThan(0, "Total Amount must be greater than 0");
    },
  },
};
</script>

<style scoped>
.expense-card {
  overflow: visible !important;
}

.expense-table-container {
  overflow: visible !important;
}

.table-vselect-wrapper {
  min-width: 220px;
}
</style>

<style>
/* Floating Dropdown Menu Above All Tables & Cards */
.vs__dropdown-menu {
  z-index: 999999 !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18) !important;
  border-radius: 6px !important;
  border: 1px solid #cbd5e1 !important;
  max-height: 280px !important;
  background-color: #ffffff !important;
}

.vs__dropdown-option {
  padding: 8px 12px !important;
  font-size: 13px !important;
  color: #1e293b !important;
}

.vs__dropdown-option--highlight {
  background-color: #112c47 !important;
  color: #ffffff !important;
}
</style>