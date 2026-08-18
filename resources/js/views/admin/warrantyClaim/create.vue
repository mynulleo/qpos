<template>
  <div class="main-content warranty-claim-create">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="page-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
          <i class="fas fa-shield-alt theme-text"></i>
          <span>{{ isEdit ? 'Edit Warranty Claim (ক্লেইম সংশোধন)' : 'New Warranty / Guarantee Claim (নতুন ক্লেইম এন্ট্রি)' }}</span>
        </h4>
        <small class="text-muted">Lookup serial number, verify warranty policy validity and register service ticket</small>
      </div>
      <router-link :to="{ name: 'warrantyClaim.index' }" class="btn btn-outline-secondary btn-sm px-3 fw-bold">
        <i class="fas fa-arrow-left me-1"></i>
        <span>Back to List</span>
      </router-link>
    </div>

    <!-- 🔍 Step 1: Serial Number Search Box -->
    <div class="search-step-wrapper mb-4" v-show="!isEdit">
      <div class="card border-0 shadow-sm search-card">
        <div class="card-header theme-bg text-white py-2 px-3">
          <span class="fw-bold fs-6">
            <i class="fas fa-search me-2 text-warning"></i>
            <span>Step 1: Serial Number Verification (সিরিয়াল নম্বর যাচাই)</span>
          </span>
        </div>
        <div class="card-body p-4 bg-light">
          <div class="row g-3 align-items-center">
            <div class="col-lg-7 col-md-12">
              <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                Enter or Scan Item Serial Number (সিরিয়াল / আইএমইআই নম্বর স্ক্যান করুন):
              </label>
              <div class="serial-search-box d-flex align-items-center shadow-sm">
                <div class="search-icon-prefix d-flex align-items-center justify-content-center">
                  <i class="fas fa-barcode fs-4 theme-text"></i>
                </div>
                <input
                  type="text"
                  class="form-control serial-search-input font-monospace fw-bold"
                  placeholder="Scan or type Serial No and press Enter..."
                  v-model="searchSerial"
                  @keyup.enter="checkSerialEligibility"
                />
                <button
                  type="button"
                  class="btn search-action-btn fw-bold d-flex align-items-center justify-content-center gap-2"
                  @click="checkSerialEligibility"
                  :disabled="searching"
                >
                  <i :class="searching ? 'fas fa-spinner fa-spin' : 'fas fa-search'"></i>
                  <span>Verify Serial</span>
                </button>
              </div>

              <small class="text-muted d-block mt-2" style="font-size: 12px;">
                <i class="fas fa-info-circle text-primary me-1"></i>
                <span>Type or scan serial number to automatically verify sales record & warranty validity.</span>
              </small>
            </div>

            <div class="col-lg-5 col-md-12">
              <div class="border rounded p-3 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="fas fa-certificate text-success fs-5"></i>
                  <strong class="small text-dark">Automated Policy Check</strong>
                </div>
                <p class="small text-muted mb-0" style="font-size: 12px; line-height: 1.5;">
                  The system checks invoice date and warranty duration. If eligible, customer and sale details will auto-fill. If expired or out-of-warranty, details will still be displayed with an override option.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📢 Step 2: Verification Banners -->
    <div class="results-step-wrapper mb-4" v-if="verificationResult">
      <!-- 1. ELIGIBLE BANNER -->
      <div class="alert alert-success border-2 shadow-sm d-flex align-items-start gap-3 p-3" v-if="verificationResult.eligible">
        <i class="fas fa-check-circle fs-1 text-success mt-1"></i>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="alert-heading fw-bold mb-1 text-success">
              <i class="fas fa-shield-alt me-1"></i>
              <span>Eligible for {{ verificationResult.warranty_type === 'guarantee' ? 'Guarantee (গ্যারান্টি)' : 'Warranty (ওয়ারেন্টি)' }}!</span>
            </h5>
            <span class="badge bg-success fs-6 px-3 py-1 font-monospace">
              {{ verificationResult.remaining_days }} Days Remaining
            </span>
          </div>
          <p class="mb-2 small text-dark">
            {{ verificationResult.message }}
          </p>
          <div class="row g-2 bg-white rounded p-2 border border-success-subtle small font-monospace text-dark">
            <div class="col-md-4"><strong>Invoice:</strong> {{ verificationResult.invoice_no }} ({{ verificationResult.sale_date }})</div>
            <div class="col-md-4"><strong>Item:</strong> {{ verificationResult.item_title }}</div>
            <div class="col-md-4"><strong>Policy:</strong> {{ verificationResult.warranty_period }} (Exp: {{ verificationResult.warranty_expiry_date }})</div>
          </div>
        </div>
      </div>

      <!-- 2. INELIGIBLE / EXPIRED BANNER -->
      <div class="alert alert-warning border-2 shadow-sm d-flex align-items-start gap-3 p-3" v-if="!verificationResult.eligible && verificationResult.found">
        <i class="fas fa-exclamation-triangle fs-1 text-warning mt-1"></i>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="alert-heading fw-bold mb-1 text-dark">
              <i class="fas fa-times-circle text-danger me-1"></i>
              <span>Not Eligible for Free Warranty Coverage</span>
            </h5>
            <span class="badge bg-danger fs-6 px-3 py-1 font-monospace" v-if="verificationResult.status_type === 'expired'">
              Expired {{ verificationResult.expired_days }} Days Ago
            </span>
            <span class="badge bg-secondary fs-6 px-3 py-1" v-if="verificationResult.status_type !== 'expired'">
              {{ verificationResult.status_type === 'unsold' ? 'Unsold Item' : 'No Warranty Policy' }}
            </span>
          </div>
          <p class="mb-2 small text-dark fw-bold">
            {{ verificationResult.message }}
          </p>

          <div class="bg-white rounded p-3 border shadow-sm small text-dark mt-2" v-if="verificationResult.sold">
            <h6 class="fw-bold border-bottom pb-1 mb-2 theme-text">
              <i class="fas fa-history me-1"></i>
              <span>Original Sale & Customer Information (বিক্রয় ও কাস্টমার তথ্য):</span>
            </h6>
            <div class="row g-2 font-monospace">
              <div class="col-md-4"><strong>Invoice No:</strong> {{ verificationResult.invoice_no }}</div>
              <div class="col-md-4"><strong>Sale Date:</strong> {{ verificationResult.sale_date }}</div>
              <div class="col-md-4"><strong>Customer:</strong> {{ verificationResult.customer_name }} ({{ verificationResult.customer_mobile }})</div>
              <div class="col-md-6"><strong>Product:</strong> {{ verificationResult.item_title }} <span v-if="verificationResult.color_title">({{ verificationResult.color_title }}/{{ verificationResult.size_title }})</span></div>
              <div class="col-md-6"><strong>Warranty Expiry Date:</strong> {{ verificationResult.warranty_expiry_date || 'No Warranty Policy' }}</div>
            </div>

            <div class="mt-2 pt-2 border-top" v-if="verificationResult.existing_claims && verificationResult.existing_claims.length > 0">
              <span class="text-danger fw-bold">
                <i class="fas fa-exclamation-circle me-1"></i>
                <span>Previous Claims on this Serial (পূর্ববর্তী ক্লেইম সমূহ):</span>
              </span>
              <ul class="mb-0 ps-3 mt-1 small">
                <li v-for="ec in verificationResult.existing_claims" :key="ec.id">
                  <strong>{{ ec.claim_no }}</strong> ({{ ec.claim_date }}): Status: <span class="badge bg-secondary">{{ ec.current_status }}</span> - {{ ec.problem_description }}
                </li>
              </ul>
            </div>

            <div class="form-check form-switch mt-3 pt-2 border-top">
              <input class="form-check-input cursor-pointer" type="checkbox" id="outOfWarrantyCheck" v-model="allowOutOfWarranty" style="transform: scale(1.2);">
              <label class="form-check-label fw-bold text-danger cursor-pointer ms-2" for="outOfWarrantyCheck">
                Proceed as Paid Repair / Out-of-Warranty Service Claim (পেইড সার্ভিস / ওয়ারেন্টি বহির্ভূত হিসেবে ক্লেইম গ্রহণ করুন)
              </label>
            </div>
          </div>

          <div class="bg-white rounded p-3 border shadow-sm small text-dark mt-2" v-if="verificationResult.purchase_info">
            <h6 class="fw-bold border-bottom pb-1 mb-2 text-info">
              <i class="fas fa-boxes me-1"></i>
              <span>Purchase Record Information (ক্রয় সংক্রান্ত তথ্য):</span>
            </h6>
            <div class="row g-2 font-monospace">
              <div class="col-md-6"><strong>Product:</strong> {{ verificationResult.purchase_info.item_title }}</div>
              <div class="col-md-6"><strong>Supplier:</strong> {{ verificationResult.purchase_info.supplier }}</div>
              <div class="col-md-6"><strong>Purchase Date:</strong> {{ verificationResult.purchase_info.purchase_date }}</div>
              <div class="col-md-6"><strong>Status:</strong> Available in Stock (Not yet sold to any customer)</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. NOT FOUND BANNER -->
      <div class="alert alert-danger border-2 shadow-sm d-flex align-items-center gap-3 p-3" v-if="!verificationResult.found">
        <i class="fas fa-times-circle fs-1 text-danger"></i>
        <div>
          <h5 class="alert-heading fw-bold mb-1">Serial Number Not Found!</h5>
          <p class="mb-0 small">
            {{ verificationResult.message || 'The specified serial number does not match any purchase or sales records in the database.' }}
          </p>
        </div>
      </div>
    </div>

    <!-- 📝 Step 3: Claim Submission Form -->
    <div class="form-step-wrapper" v-if="canShowForm">
      <form @submit.prevent="submitClaim">
        <div class="row g-3 align-items-start">
          <!-- Left Column (5 Col): Item & Policy Details + Customer Contact Details -->
          <div class="col-lg-5 col-md-12 d-flex flex-column gap-3">
            <!-- 1. Item & Policy Details Card -->
            <div class="card border-0 shadow-sm form-section-card">
              <div class="card-header theme-bg text-white py-2 px-3">
                <span class="fw-bold small">
                  <i class="fas fa-box me-2"></i>
                  <span>Item & Policy Details</span>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="mb-2">
                  <label class="form-label small fw-bold text-muted mb-0">Item Title:</label>
                  <div class="fw-bold text-dark fs-6">{{ form.item_title || 'Item' }}</div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Serial Number:</label>
                    <div class="font-monospace fw-bold theme-text">{{ form.serial_no || 'N/A' }}</div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Invoice No:</label>
                    <div class="font-monospace fw-bold text-dark">{{ form.invoice_no || 'N/A' }}</div>
                  </div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Coverage Type:</label>
                    <div>
                      <span class="badge" :class="form.warranty_type === 'guarantee' ? 'bg-success' : 'theme-bg text-white'">
                        {{ form.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}
                      </span>
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Warranty Period:</label>
                    <div class="small fw-bold text-dark">{{ form.warranty_period || 'N/A' }}</div>
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Sale Date:</label>
                    <div class="small">{{ form.sale_date || 'N/A' }}</div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">Expiry Date:</label>
                    <div class="small fw-bold text-danger">{{ form.warranty_expiry_date || 'N/A' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. Customer Contact Details Card (Directly Below Item Card) -->
            <div class="card border-0 shadow-sm form-section-card">
              <div class="card-header theme-bg text-white py-2 px-3">
                <span class="fw-bold small">
                  <i class="fas fa-user me-2"></i>
                  <span>Customer Contact Details</span>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="mb-2">
                  <label class="form-label small fw-bold text-dark mb-1">Customer Name *</label>
                  <input type="text" class="form-control form-control-sm fw-bold" v-model="form.customer_name" required placeholder="Customer Name">
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-bold text-dark mb-1">Mobile Number *</label>
                  <input type="text" class="form-control form-control-sm font-monospace" v-model="form.customer_mobile" required placeholder="017xxxxxxxx">
                </div>
                <div>
                  <label class="form-label small fw-bold text-dark mb-1">Address</label>
                  <textarea class="form-control form-control-sm" rows="2" v-model="form.customer_address" placeholder="Customer address..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column (7 Col): Claim & Problem Details -->
          <div class="col-lg-7 col-md-12">
            <div class="card border-0 shadow-sm form-section-card h-100">
              <div class="card-header theme-bg text-white py-2 px-3">
                <span class="fw-bold small">
                  <i class="fas fa-tools me-2"></i>
                  <span>Claim & Problem Details (সমস্যার বিবরণ)</span>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Claim Date *</label>
                    <input type="date" class="form-control form-control-sm" v-model="form.claim_date" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Expected Delivery Date (সম্ভাব্য ডেলিভারি তারিখ)</label>
                    <input type="date" class="form-control form-control-sm" v-model="form.expected_delivery_date">
                  </div>

                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Problem Description / Fault Reported (সমস্যার বিবরণ) *</label>
                    <textarea class="form-control" rows="3" v-model="form.problem_description" required placeholder="e.g. Display not working, Power issue, Sound buzzing..."></textarea>
                  </div>

                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Received Accessories / Items (সাথে যা যা জমা নেওয়া হয়েছে)</label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Original Box')">+ Original Box</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Power Adapter / Charger')">+ Adapter</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('USB / Power Cable')">+ Cable</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Warranty Card / Receipt')">+ Warranty Card</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Remote Controller')">+ Remote</span>
                    </div>
                    <input type="text" class="form-control form-control-sm" v-model="form.accessories_received" placeholder="e.g. Original Box, Power Adapter, Remote">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Initial Claim Status *</label>
                    <select class="form-select form-select-sm" v-model="form.current_status" required>
                      <option value="received">Received (পণ্য গ্রহণ করা হয়েছে)</option>
                      <option value="sent_to_vendor">Sent to Vendor / Service Center (ভেন্ডরে পাঠানো হয়েছে)</option>
                      <option value="in_service">In Service / Repairing (সার্ভিসিংয়ে আছে)</option>
                      <option value="repaired">Repaired (মেরামত সম্পন্ন)</option>
                      <option value="replaced">Replaced (নতুন পরিবর্তন দেওয়া হয়েছে)</option>
                      <option value="ready_for_delivery">Ready for Delivery (ডেলিভারির জন্য প্রস্তুত)</option>
                      <option value="delivered">Delivered (গ্রাহককে হস্তান্তর)</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-bold text-dark">Internal Service Cost</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light">Tk.</span>
                      <input type="number" step="0.01" min="0" class="form-control form-control-sm font-monospace text-end" v-model.number="form.service_cost" placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-bold text-dark">Customer Charge (বিল)</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light">Tk.</span>
                      <input type="number" step="0.01" min="0" class="form-control form-control-sm font-monospace text-end" v-model.number="form.customer_charge" placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-12" v-if="!isEdit">
                    <label class="form-label small fw-bold text-dark">Initial Tracking Note / Remarks (প্রাথমিক নোট)</label>
                    <textarea class="form-control form-control-sm" rows="2" v-model="form.initial_remarks" placeholder="Optional notes for tracking timeline..."></textarea>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center border-top mt-auto">
                <router-link :to="{ name: 'warrantyClaim.index' }" class="btn btn-outline-secondary btn-sm px-4">
                  <span>Cancel</span>
                </router-link>
                <button type="submit" class="btn btn-theme px-5 fw-bold shadow-sm" :disabled="submitting">
                  <i :class="submitting ? 'fas fa-spinner fa-spin' : 'fas fa-save'" class="me-1"></i>
                  <span>{{ isEdit ? 'Update Claim' : 'Save & Register Claim (ক্লেইম সংরক্ষণ করুন)' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
const model = 'warrantyClaim';

export default {
  name: 'WarrantyClaimCreate',
  data() {
    return {
      model: model,
      isEdit: false,
      searchSerial: '',
      searching: false,
      verificationResult: null,
      allowOutOfWarranty: false,
      submitting: false,
      form: {
        id: null,
        claim_no: '',
        serial_no: '',
        invoice_id: null,
        invoice_no: '',
        invoice_detail_id: null,
        item_id: null,
        item_title: '',
        color_id: null,
        size_id: null,
        client_id: null,
        customer_name: '',
        customer_mobile: '',
        customer_address: '',
        warranty_type: 'warranty',
        warranty_period: '',
        sale_date: '',
        claim_date: new Date().toISOString().split('T')[0],
        warranty_expiry_date: '',
        problem_description: '',
        accessories_received: '',
        current_status: 'received',
        expected_delivery_date: '',
        service_cost: 0,
        customer_charge: 0,
        initial_remarks: '',
      },
    };
  },
  computed: {
    canShowForm() {
      if (this.isEdit) return true;
      if (!this.verificationResult) return false;
      if (this.verificationResult.eligible) return true;
      if (this.allowOutOfWarranty) return true;
      return false;
    },
  },
  methods: {
    checkSerialEligibility() {
      if (!this.searchSerial || this.searchSerial.trim() === '') {
        this.$toast('Please enter or scan a serial number', 'warning');
        return;
      }

      this.searching = true;
      this.verificationResult = null;
      this.allowOutOfWarranty = false;

      axios.get('warrantyClaim/check-serial', { params: { serial_no: this.searchSerial.trim() } })
        .then(res => {
          const resData = res.data;
          this.searching = false;

          this.$nextTick(() => {
            this.verificationResult = resData;
            if (resData.found) {
              this.populateForm(resData);
            }
            if (resData.message) {
              this.$toast(resData.message, resData.eligible ? 'success' : (resData.found ? 'warning' : 'error'));
            }
          });
        })
        .catch(err => {
          this.searching = false;
          this.$toast('Error verifying serial number', 'error');
          console.error(err);
        });
    },
    populateForm(data) {
      this.form.serial_no = data.serial_no || '';
      this.form.invoice_id = data.invoice_id || null;
      this.form.invoice_no = data.invoice_no || '';
      this.form.invoice_detail_id = data.invoice_detail_id || null;
      this.form.item_id = data.item_id || null;
      this.form.item_title = data.item_title || 'Item';
      this.form.color_id = data.color_id || null;
      this.form.size_id = data.size_id || null;
      this.form.client_id = data.client_id || null;
      this.form.customer_name = data.customer_name || '';
      this.form.customer_mobile = data.customer_mobile || '';
      this.form.customer_address = data.customer_address || '';
      this.form.warranty_type = data.warranty_type || 'warranty';
      this.form.warranty_period = data.warranty_period || '';
      this.form.sale_date = data.sale_date || '';
      this.form.warranty_expiry_date = data.warranty_expiry_date || '';
    },
    appendAccessory(text) {
      if (!this.form.accessories_received || this.form.accessories_received.trim() === '') {
        this.form.accessories_received = text;
      } else if (!this.form.accessories_received.includes(text)) {
        this.form.accessories_received += ', ' + text;
      }
    },
    submitClaim() {
      if (!this.form.customer_name || !this.form.customer_mobile || !this.form.problem_description) {
        this.$toast('Please fill all required fields marked with *', 'warning');
        return;
      }

      this.submitting = true;
      const url = this.isEdit ? `${this.model}/${this.form.id}` : this.model;
      const method = this.isEdit ? 'put' : 'post';

      axios[method](url, this.form)
        .then(res => {
          const claimId = this.isEdit ? this.form.id : (res.data.data ? res.data.data.id : res.data.id);
          this.$toast(`Warranty Claim ${this.isEdit ? 'updated' : 'registered'} successfully!`, 'success');
          this.$router.push({ name: 'warrantyClaim.show', params: { id: claimId } });
        })
        .catch(err => {
          this.$toast(err.response?.data?.message || err.response?.data?.exception || 'Failed to save warranty claim', 'error');
          console.error(err);
        })
        .finally(() => {
          this.submitting = false;
        });
    },
    loadClaimForEdit(id) {
      this.$root.submit = true;
      axios.get(`${this.model}/${id}`)
        .then(res => {
          const d = res.data;
          this.form = {
            id: d.id,
            claim_no: d.claim_no,
            serial_no: d.serial_no,
            invoice_id: d.invoice_id,
            invoice_no: d.invoice ? d.invoice.invoice_no : '',
            invoice_detail_id: d.invoice_detail_id,
            item_id: d.item_id,
            item_title: d.item ? d.item.title : 'Item',
            color_id: d.color_id,
            size_id: d.size_id,
            client_id: d.client_id,
            customer_name: d.customer_name,
            customer_mobile: d.customer_mobile,
            customer_address: d.customer_address,
            warranty_type: d.warranty_type,
            warranty_period: d.warranty_period,
            sale_date: d.sale_date,
            claim_date: d.claim_date,
            warranty_expiry_date: d.warranty_expiry_date,
            problem_description: d.problem_description,
            accessories_received: d.accessories_received,
            current_status: d.current_status,
            expected_delivery_date: d.expected_delivery_date,
            service_cost: d.service_cost,
            customer_charge: d.customer_charge,
            initial_remarks: '',
          };
        })
        .catch(err => {
          this.$toast('Failed to load claim for editing', 'error');
          console.error(err);
        })
        .finally(() => {
          this.$root.submit = false;
        });
    },
  },
  created() {
    if (this.$route.params.id) {
      this.isEdit = true;
      this.loadClaimForEdit(this.$route.params.id);
    }
  },
};
</script>

<style scoped>
.warranty-claim-create {
  font-family: inherit;
}

.theme-bg {
  background-color: rgb(17, 44, 70) !important;
}

.theme-text {
  color: rgb(17, 44, 70) !important;
}

.text-theme {
  color: rgb(17, 44, 70) !important;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 70, 0.1) !important;
}

.btn-theme {
  background-color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.btn-theme:hover {
  background-color: #1a3d61 !important;
  color: #ffffff !important;
}

.cursor-pointer {
  cursor: pointer;
}

.serial-search-box {
  background-color: #ffffff;
  border: 2px solid rgb(17, 44, 70);
  border-radius: 8px;
  overflow: hidden;
  height: 48px;
  transition: box-shadow 0.2s ease-in-out;
}

.serial-search-box:focus-within {
  box-shadow: 0 0 0 3px rgba(17, 44, 70, 0.25) !important;
}

.search-icon-prefix {
  width: 48px;
  height: 100%;
  background-color: rgba(17, 44, 70, 0.05);
  border-right: 1px solid #e2e8f0;
}

.serial-search-input {
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
  height: 100% !important;
  font-size: 16px !important;
  padding: 0 14px !important;
  background: transparent !important;
}

.search-action-btn {
  height: 100% !important;
  border: none !important;
  border-radius: 0 !important;
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
  padding: 0 24px !important;
  font-size: 14px !important;
  white-space: nowrap;
  transition: background-color 0.2s ease;
}

.search-action-btn:hover {
  background-color: #1a3d61 !important;
}

.search-action-btn:disabled {
  opacity: 0.7;
}

.form-section-card {
  border-radius: 8px;
  overflow: hidden;
}

.accessory-chip {
  transition: all 0.15s ease-in-out;
}

.accessory-chip:hover {
  background-color: rgba(17, 44, 70, 0.1) !important;
  color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
}
</style>
