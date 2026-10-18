<template>
  <create-form @onSubmit="submit">
    <!-- Top Context Header Card (Unified bg-gradient-primary-subtle) -->
    <div class="col-12 mb-3">
      <!-- Edit Mode Header Card -->
      <div v-if="$route.params.id" class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-gradient-primary-subtle border-bottom py-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="position-relative d-inline-block">
                <img
                  :src="data.image ? data.image : '/images/avatar.png'"
                  alt="Employee Avatar"
                  class="rounded-circle img-thumbnail shadow-sm"
                  style="width: 52px; height: 52px; object-fit: cover;"
                  @error="$event.target.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(data.full_name || 'User') + '&background=0284c7&color=fff&size=128'"
                />
                <span
                  class="position-absolute bottom-0 end-0 badge rounded-pill shadow-xs"
                  :class="data.status === 'active' ? 'bg-success' : 'bg-danger'"
                  style="font-size: 8px; padding: 3px 5px;"
                >
                  <i :class="data.status === 'active' ? 'fas fa-check' : 'fas fa-ban'"></i>
                </span>
              </div>
              <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                  <h5 class="fw-bold text-dark mb-0">{{ data.full_name || $t('Loading Employee...') }}</h5>
                  <span class="badge bg-white text-primary border shadow-xs px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.75rem;">
                    <i class="fas fa-id-badge me-1"></i> ID: {{ data.empid || '---' }}
                  </span>
                  <span
                    class="badge rounded-pill px-2.5 py-1"
                    :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                    style="font-size: 0.75rem;"
                  >
                    <i :class="data.status === 'active' ? 'fas fa-check-circle' : 'fas fa-pause-circle'" class="me-1"></i>
                    {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                  </span>
                </div>
                <small class="text-muted d-flex flex-wrap align-items-center gap-3">
                  <span><i class="fas fa-briefcase me-1 text-info"></i> {{ getDesignationTitle(data.designation_id) || $t('Designation N/A') }}</span>
                  <span><i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ getBranchTitle(data.branch_id) || $t('Main Branch') }}</span>
                  <span v-if="data.mobile"><i class="fas fa-phone me-1 text-success"></i> {{ data.mobile }}</span>
                </small>
              </div>
            </div>
            <router-link :to="{ name: 'employee.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Staff') }}
            </router-link>
          </div>
        </div>
      </div>

      <!-- Create Mode Header Card -->
      <div v-else class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-gradient-primary-subtle border-bottom py-3 px-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="header-icon-box text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm">
                <i class="fas fa-user-plus fs-4"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark mb-0">{{ $t('New Employee Registration') }}</h5>
                <small class="text-muted">{{ $t('Configure official profile, compensation breakdown, and emergency contacts') }}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-white text-primary border shadow-xs px-3 py-1.5 rounded-pill fw-semibold">
                <i class="fas fa-shield-alt me-1 text-success"></i> {{ $t('Human Resources') }}
              </span>
              <router-link :to="{ name: 'employee.index' }" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 shadow-xs fw-medium bg-white">
                <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Staff') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8 col-md-12">
      <div class="card border shadow-sm rounded-3 h-100 bg-white">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-user-circle text-primary me-2"></i> {{ $t('Official & Employment Particulars') }}
          </h6>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">
            {{ $t('Step 1 of 4') }}
          </span>
        </div>

        <div class="card-body p-3">
          <div class="row g-3">
            <Input
              v-model="data.full_name"
              field="data.full_name"
              col="8"
              title="Full Name"
              placeholder="e.g. Mohammad Rahim"
              :req="true"
            />

            <Select
              title="Designation"
              v-model="data.designation_id"
              field="data.designation_id"
              label="title"
              :reduce="(obj) => obj.id"
              col="4"
              :options="$root.global.designations"
              placeholder="--Select Designation--"
              :closeOnSelect="true"
              :required="true"
            />

            <Select
              title="Office Branch"
              v-model="data.branch_id"
              field="data.branch_id"
              label="branch_name"
              :reduce="(obj) => obj.id"
              :options="$root.global.branches"
              col="6"
              placeholder="--Select Branch--"
              :closeOnSelect="true"
              :required="true"
            />

            <date-picker
              id="date1"
              v-model="data.joining_date"
              field="data.joining_date"
              title="Joining Date"
              placeholder="Select joining date"
              col="6"
              :req="true"
            ></date-picker>

            <Input
              v-model="data.mobile"
              field="data.mobile"
              col="6"
              title="Mobile Number"
              placeholder="01XXXXXXXXX"
              :req="true"
            />

            <Input
              v-model="data.email"
              field="data.email"
              col="6"
              title="Email Address"
              placeholder="name@example.com"
              :req="false"
            />

            <date-picker
              id="date6"
              v-model="data.dob"
              col="6"
              field="data.dob"
              title="Date of Birth"
              placeholder="Select birth date"
              :req="false"
            ></date-picker>

            <Input
              v-model="data.nid"
              field="data.nid"
              col="6"
              title="National ID (NID)"
              placeholder="NID number"
              :req="true"
            />

            <Textarea
              v-model="data.address"
              field="data.address"
              :required="true"
              title="Present / Permanent Address"
              placeholder="Enter full residential address..."
              col="12"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Profile Photo & Account Status (Col 4) -->
    <div class="col-lg-4 col-md-12">
      <div class="card border shadow-sm rounded-3 h-100 bg-white d-flex flex-column justify-content-between">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-id-badge text-info me-2"></i> {{ $t('Profile Photo & Status') }}
          </h6>
          <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 small">
            {{ $t('Step 2 of 4') }}
          </span>
        </div>

        <div class="card-body p-3">
          <div class="row g-2 text-center">
            <div class="col-12">
              <div class="upload-panel p-3 rounded border bg-light">
                <File
                  title="Profile Photo"
                  field="data.image"
                  mime="img"
                  fileClassName="data.image"
                  col="12"
                  :req="false"
                />
                <small class="text-muted d-block mt-1 fw-semibold">{{ $t('Upload Photo (JPG/PNG)') }}</small>
              </div>
            </div>
          </div>

          <!-- Account Status Toggle Box -->
          <div class="mt-3 pt-3 border-top">
            <div class="p-3 rounded-3 border transition-all" :class="data.status === 'active' ? 'bg-success-subtle border-success-subtle' : 'bg-danger-subtle border-danger-subtle'">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-1.5">
                    <span class="fw-bold text-dark fs-6">{{ $t('Employment Status') }}</span>
                    <span
                      class="badge rounded-pill px-2 py-0.5"
                      :class="data.status === 'active' ? 'bg-success text-white' : 'bg-danger text-white'"
                    >
                      {{ data.status === 'active' ? $t('Active') : $t('Inactive') }}
                    </span>
                  </div>
                  <p class="text-muted small mb-0 mt-0.5">
                    {{ data.status === 'active' ? $t('Active in payroll & attendance') : $t('Suspended / Deactivated') }}
                  </p>
                </div>
                <Switch
                  v-model="data.status"
                  field="data.status"
                  title=""
                  on-label="Active"
                  off-label="Inactive"
                  :req="true"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer bg-light border-top p-2.5 text-muted small text-center">
          <i class="fas fa-info-circle text-primary me-1"></i> {{ $t('Ensure photo is clear and professional.') }}
        </div>
      </div>
    </div>

    <!-- Row 2: Salary Structure (Col 6) + Bank Info (Col 6) -->
    <!-- Salary Structure (Col 6) -->
    <div class="col-lg-6 col-md-12 mt-3">
      <div class="card border shadow-sm rounded-3 h-100 bg-white">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-money-bill-wave text-success me-2"></i> {{ $t('Compensation & Salary Structure') }}
          </h6>
          <span class="badge bg-success text-white px-2.5 py-1 fw-bold rounded-pill shadow-xs" style="font-size: 0.8rem;">
            {{ $t('Gross') }}: ৳ {{ formatCurrency(totalSalary) }}
          </span>
        </div>

        <div class="card-body p-3">
          <div class="table-responsive rounded-3 border salary_table_box">
            <table class="table table-hover table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3 py-2 text-muted fw-semibold">{{ $t('Salary Parameter') }}</th>
                  <th class="text-center py-2 text-muted fw-semibold" style="width: 120px;">{{ $t('Rule / Type') }}</th>
                  <th class="text-end pe-3 py-2 text-muted fw-semibold" style="width: 150px;">{{ $t('Amount') }} (৳)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(empsalary, index) in data.employee_salaries" :key="index">
                  <td class="ps-3 fw-semibold text-dark">
                    {{ empsalary.title }}
                    <span v-if="empsalary.is_basic == 1" class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.7rem;">
                      {{ $t('Base Salary') }}
                    </span>
                  </td>
                  <td class="text-center text-muted small">
                    <span v-if="empsalary.is_basic == 1" class="text-primary fw-bold">{{ $t('Base') }}</span>
                    <span v-else-if="empsalary.percentage_of_basic" class="badge bg-info-subtle text-info border border-info-subtle">
                      {{ empsalary.percentage_of_basic }}% {{ $t('of Basic') }}
                    </span>
                    <span v-else class="text-muted">{{ $t('Fixed') }}</span>
                  </td>
                  <td class="pe-3 py-1.5">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted border-end-0 small">৳</span>
                      <input
                        type="number"
                        step="any"
                        class="form-control text-end fw-bold"
                        v-model="empsalary.amount"
                        placeholder="0.00"
                      />
                    </div>
                  </td>
                </tr>
                <tr v-if="!data.employee_salaries || data.employee_salaries.length === 0">
                  <td colspan="3" class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Loading salary parameters...') }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light border-top">
                <tr>
                  <th colspan="2" class="ps-3 py-2 text-dark">{{ $t('Total Gross Salary') }}</th>
                  <th class="text-end pe-3 py-2 text-success fw-bold fs-6">
                    ৳ {{ formatCurrency(totalSalary) }}
                  </th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="p-2 mt-2.5 rounded bg-light border text-muted small d-flex align-items-center gap-2">
            <i class="fas fa-lightbulb text-warning fs-5"></i>
            <span>{{ $t('Changing Base salary will auto-recalculate percentage allowances.') }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bank Information (Col 6) -->
    <div class="col-lg-6 col-md-12 mt-3">
      <div class="card border shadow-sm rounded-3 h-100 bg-white">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-university text-warning me-2"></i> {{ $t('Bank & Payment Disbursement') }}
          </h6>
          <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-0.5 small">
            {{ $t('Step 3 of 4') }}
          </span>
        </div>

        <div class="card-body p-3">
          <div class="row g-3">
            <Select
              title="Bank Name"
              v-model="data.bank_id"
              field="data.bank_id"
              label="bank_name"
              :reduce="(obj) => obj.id"
              col="6"
              :options="banks"
              placeholder="--Select Bank--"
              :closeOnSelect="true"
              :required="false"
            />
            <Input
              v-model="data.branch"
              field="data.branch"
              title="Branch Name"
              col="6"
              placeholder="e.g. Motijheel Branch"
              :req="false"
            />
            <Input
              v-model="data.account_name"
              field="data.account_name"
              title="Account Holder Name"
              col="6"
              placeholder="e.g. Mohammad Rahim"
              :req="false"
            />
            <Input
              v-model="data.account_no"
              field="data.account_no"
              title="Account Number"
              col="6"
              placeholder="e.g. 2050123456789"
              :req="false"
            />

            <div class="col-12 mt-2">
              <div class="p-2.5 rounded bg-light border text-muted small d-flex align-items-center gap-2">
                <i class="fas fa-info-circle text-primary fs-5"></i>
                <span>{{ $t('Monthly salary disbursements and reimbursements will be deposited to this account.') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Row 3: Family & Emergency Information (Col 12) -->
    <div class="col-12 mt-3">
      <div class="card border shadow-sm rounded-3 bg-white">
        <div class="card-header bg-light py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 fw-bold text-dark">
            <i class="fas fa-users text-danger me-2"></i> {{ $t('Family & Emergency Contacts') }}
          </h6>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 small">
            {{ $t('Step 4 of 4') }}
          </span>
        </div>

        <div class="card-body p-3">
          <div class="row g-3">
            <!-- Father Information -->
            <div class="col-lg-4 col-md-6">
              <div class="p-3 border rounded-3 bg-light h-100 family_box shadow-xs">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                  <div class="icon-circle bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center">
                    <i class="fas fa-male fs-5"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ $t("Father's Information") }}</h6>
                    <small class="text-muted">{{ $t('Primary family contact') }}</small>
                  </div>
                </div>
                <div class="row g-2">
                  <Input
                    v-model="data.father_name"
                    field="data.father_name"
                    col="12"
                    title="Father Name"
                    placeholder="Father's full name"
                    :req="false"
                  />
                  <Input
                    v-model="data.father_occupation"
                    field="data.father_occupation"
                    col="12"
                    title="Occupation"
                    placeholder="Father's occupation"
                    :req="false"
                  />
                  <Input
                    v-model="data.father_mobile"
                    field="data.father_mobile"
                    col="12"
                    title="Mobile Number"
                    placeholder="01XXXXXXXXX"
                    :req="false"
                  />
                  <Input
                    v-model="data.father_nid"
                    field="data.father_nid"
                    col="12"
                    title="Father NID"
                    placeholder="Father's NID number"
                    :req="false"
                  />
                </div>
              </div>
            </div>

            <!-- Mother Information -->
            <div class="col-lg-4 col-md-6">
              <div class="p-3 border rounded-3 bg-light h-100 family_box shadow-xs">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                  <div class="icon-circle bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center">
                    <i class="fas fa-female fs-5"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ $t("Mother's Information") }}</h6>
                    <small class="text-muted">{{ $t('Primary family contact') }}</small>
                  </div>
                </div>
                <div class="row g-2">
                  <Input
                    v-model="data.mother_name"
                    field="data.mother_name"
                    col="12"
                    title="Mother Name"
                    placeholder="Mother's full name"
                    :req="false"
                  />
                  <Input
                    v-model="data.mother_occupation"
                    field="data.mother_occupation"
                    col="12"
                    title="Occupation"
                    placeholder="Mother's occupation"
                    :req="false"
                  />
                  <Input
                    v-model="data.mother_mobile"
                    field="data.mother_mobile"
                    col="12"
                    title="Mobile Number"
                    placeholder="01XXXXXXXXX"
                    :req="false"
                  />
                  <Input
                    v-model="data.mother_nid"
                    field="data.mother_nid"
                    col="12"
                    title="Mother NID"
                    placeholder="Mother's NID number"
                    :req="false"
                  />
                </div>
              </div>
            </div>

            <!-- Spouse Information -->
            <div class="col-lg-4 col-md-12">
              <div class="p-3 border rounded-3 bg-light h-100 family_box shadow-xs d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="icon-circle bg-danger-subtle text-danger rounded-circle p-2 d-flex align-items-center justify-content-center">
                      <i class="fas fa-heart fs-5"></i>
                    </div>
                    <div>
                      <h6 class="mb-0 fw-bold text-dark">{{ $t('Spouse & Emergency') }}</h6>
                      <small class="text-muted">{{ $t('Next of kin / spouse') }}</small>
                    </div>
                  </div>
                  <div class="row g-2">
                    <Input
                      v-model="data.spouse_name"
                      field="data.spouse_name"
                      col="12"
                      title="Spouse Name"
                      placeholder="Spouse full name"
                      :req="false"
                    />
                    <Input
                      v-model="data.spouse_mobile"
                      field="data.spouse_mobile"
                      col="12"
                      title="Spouse Mobile"
                      placeholder="01XXXXXXXXX"
                      :req="false"
                    />
                  </div>
                </div>
                <div class="p-2.5 mt-3 rounded bg-white border text-muted small">
                  <i class="fas fa-shield-alt text-success me-1"></i>
                  {{ $t('Emergency contacts will be reached during urgent or medical situations.') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Action Footer -->
    <template v-slot:form_footer>
      <div class="col-12 mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="text-muted small">
          <span class="text-danger fw-bold">*</span> {{ $t('Marked fields are required for payroll & compliance.') }}
        </div>
        <div class="d-flex gap-2">
          <router-link :to="{ name: 'employee.index' }" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> {{ $t('Cancel / Back') }}
          </router-link>
          <button type="submit" class="theme_btn px-4 py-2 rounded-pill shadow-sm" :disabled="$root.submit">
            <template v-if="$root.submit">
              <i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Processing...') }}
            </template>
            <template v-else>
              <i :class="$route.params.id ? 'fas fa-save' : 'fas fa-check-circle'" class="me-1"></i>
              {{ $route.params.id ? $t('Update Employee Details') : $t('Save & Register Employee') }}
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = 'employee';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        image: '',
        status: 'active',
        salary: 0,
        bank_id: null,
        branch_id: null,
        designation_id: null,
        employee_salaries: [],
      },
      image: {},
      banks: [],
    };
  },
  watch: {
    'data.employee_salaries': {
      handler(newVal) {
        if (!newVal || newVal.length === 0) return;

        // Basic Salary find
        let basic = newVal.find((item) => item.is_basic == 1);

        if (!basic) {
          this.data.salary = this.totalSalary;
          return;
        }

        let basicAmount = parseFloat(basic.amount) || 0;

        // Auto calculate percentage of basic
        newVal.forEach((item) => {
          if (item.is_basic == 0 && item.percentage_of_basic) {
            let percent = parseFloat(item.percentage_of_basic) || 0;
            let calculated = (basicAmount * percent) / 100;
            item.amount = calculated.toFixed(2);
          }
        });
        this.data.salary = this.totalSalary;
      },
      deep: true,
    },
  },
  provide() {
    return {
      validate: this.validation,
      data: () => this.data,
      image: this.image,
    };
  },
  computed: {
    totalSalary() {
      let total = 0;
      if (this.data.employee_salaries && this.data.employee_salaries.length > 0) {
        this.data.employee_salaries.forEach((item) => {
          let amt = parseFloat(item.amount) || 0;
          total += amt;
        });
      }
      return total;
    },
  },
  methods: {
    formatCurrency(val) {
      const num = parseFloat(val) || 0;
      return num.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    getDesignationTitle(id) {
      if (!id || !this.$root.global?.designations) return '';
      const des = this.$root.global.designations.find((d) => d.id == id);
      return des ? des.title : '';
    },

    getBranchTitle(id) {
      if (!id || !this.$root.global?.branches) return '';
      const br = this.$root.global.branches.find((b) => b.id == id);
      return br ? br.branch_name : '';
    },

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
          formData.append('joining_date', this.data.joining_date);
          formData.append('dob', this.data.dob || '');
          formData.append('designation_id', this.data.designation_id);
          formData.append('branch_id', this.data.branch_id);
          formData.append('branch', this.data.branch || '');
          formData.append('bank_id', this.data.bank_id || '');
          formData.append('address', this.data.address || '');
          formData.append('nid', this.data.nid || '');
          formData.append('email', this.data.email || '');
          formData.append('employee_salaries', JSON.stringify(this.data.employee_salaries));
          formData.append('status', this.data.status);
          formData.append('salary', this.data.salary);
          if (this.data.id) {
            this.update(this.model, formData, this.data.id, true);
          } else {
            this.store(this.model, formData);
          }
        }
      });
    },

    getBanks: function () {
      this.banks = [];
      axios.get('/getbanks/').then((res) => {
        this.banks = res.data;
      });
    },

    getSalaryParameters() {
      axios
        .get('getsalaryparameters')
        .then((response) => {
          this.data.employee_salaries = response.data.map((item) => {
            return {
              title: item.title,
              salary_parameter_id: item.id,
              is_basic: item.is_basic,
              percentage_of_basic: item.percentage_of_basic,
              amount: 0,
            };
          });
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + ' Create';
      this.getSalaryParameters();
    }
    this.getBanks();
  },

  validators: {
    'data.joining_date': function (value = null) {
      return Validator.value(value).required('Joining Date is required');
    },
    'data.designation_id': function (value = null) {
      return Validator.value(value).required('Designation is required');
    },
    'data.full_name': function (value = null) {
      return Validator.value(value).required('Full Name is required');
    },
    'data.mobile': function (value = null) {
      return Validator.value(value).required('Mobile Number is required');
    },
    'data.nid': function (value = null) {
      return Validator.value(value).required('NID is required');
    },
    'data.branch_id': function (value = null) {
      return Validator.value(value).required('Branch is required');
    },
    'data.address': function (value = null) {
      return Validator.value(value).required('Address is required');
    },
  },
};
</script>

<style scoped>
.employee-hero-banner {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  color: #fff;
  border-radius: 14px;
}

.employee-avatar {
  width: 95px;
  height: 95px;
  object-fit: cover;
  border: 3px solid #38bdf8;
  background: #fff;
}

.status-pill {
  font-size: 0.72rem;
  border: 2px solid #fff;
  padding: 0.25rem 0.5rem;
}

.bg-gradient-primary-subtle {
  background: linear-gradient(135deg, rgba(238, 242, 255, 0.95) 0%, rgba(243, 244, 246, 0.95) 100%);
}

.header-icon-box {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
}

.icon-circle {
  width: 36px;
  height: 36px;
}

.upload-panel {
  transition: all 0.2s ease-in-out;
}

.upload-panel:hover {
  background-color: #f1f5f9 !important;
  border-color: #cbd5e1 !important;
}

.salary_table_box {
  background: #fff;
}

.salary_table_box input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 0.15rem rgba(16, 185, 129, 0.15);
}

.family_box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease-in-out;
}

.family_box:hover {
  background: #ffffff;
  border-color: #cbd5e1 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
