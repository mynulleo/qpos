<template>
  <div class="main-content warranty-claim-create">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="page-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
          <i class="fas fa-shield-alt theme-text"></i>
          <span>{{ isEdit ? $t('Edit Warranty Claim') : $t('New Warranty / Guarantee Claim') }}</span>
        </h4>
        <small class="text-muted">{{ $t('Lookup serial number, verify warranty policy validity and register service ticket') }}</small>
      </div>
      <router-link :to="{ name: 'warrantyClaim.index' }" class="btn btn-outline-secondary btn-sm px-3 fw-bold">
        <i class="fas fa-arrow-left me-1"></i>
        <span>{{ $t('Back to List') }}</span>
      </router-link>
    </div>

    <!-- 🔍 Step 1: Serial Number Search Box -->
    <div class="search-step-wrapper mb-4" v-show="!isEdit">
      <div class="card border-0 shadow-sm search-card">
        <div class="card-header theme-bg text-white py-2 px-3">
          <span class="fw-bold fs-6">
            <i class="fas fa-search me-2 text-warning"></i>
            <span>{{ $t('Step 1: Serial / Invoice Verification') }}</span>
          </span>
        </div>
        <div class="card-body p-4 bg-light">
          <div class="row g-3 align-items-center">
            <div class="col-lg-7 col-md-12">
              <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                {{ $t('Enter or Scan Serial Number or Invoice Number') }}:
              </label>
              <div class="serial-search-box d-flex align-items-center shadow-sm">
                <div class="search-icon-prefix d-flex align-items-center justify-content-center">
                  <i class="fas fa-barcode fs-4 theme-text"></i>
                </div>
                <input
                  type="text"
                  class="form-control serial-search-input font-monospace fw-bold"
                  :placeholder="$t('Scan or type Serial No or Invoice No and press Enter...')"
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
                  <span>{{ $t('Verify') }}</span>
                </button>
              </div>

              <small class="text-muted d-block mt-2" style="font-size: 12px;">
                <i class="fas fa-info-circle text-primary me-1"></i>
                <span>{{ $t('Type or scan serial or invoice number to automatically verify sales record & warranty validity.') }}</span>
              </small>
            </div>

            <div class="col-lg-5 col-md-12">
              <div class="border rounded p-3 bg-white shadow-sm h-100">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="fas fa-certificate text-success fs-5"></i>
                  <strong class="small text-dark">{{ $t('Automated Policy Check') }}</strong>
                </div>
                <p class="small text-muted mb-0" style="font-size: 12px; line-height: 1.5;">
                  {{ $t('The system checks invoice date and warranty duration. If eligible, customer and sale details will auto-fill. If expired or out-of-warranty, details will still be displayed with an override option.') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📢 Step 2: Verification Banners -->
    <div class="results-step-wrapper mb-4" v-if="verificationResult">
      <!-- 📦 Multiple Items in Invoice Picker -->
      <div class="card border border-primary border-opacity-25 shadow-sm mb-3 bg-white" v-if="verificationResult.invoice_items && verificationResult.invoice_items.length > 1">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
          <span class="small fw-bold text-dark">
            <i class="fas fa-boxes text-primary me-2"></i>
            <span>{{ $t('Invoice Items') }} ({{ verificationResult.invoice_items.length }}) - {{ $t('Select Item for Claim') }}</span>
          </span>
          <span class="badge bg-primary text-white font-monospace">{{ $t('Inv') }} #{{ verificationResult.invoice_no }}</span>
        </div>
        <div class="card-body p-2">
          <div class="list-group list-group-flush">
            <button
              type="button"
              v-for="invItem in verificationResult.invoice_items"
              :key="invItem.id"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 rounded mb-1 border"
              :class="{ 'border-primary bg-primary bg-opacity-10 fw-bold': form.invoice_detail_id === invItem.id }"
              @click="selectInvoiceItem(invItem.id)"
            >
              <div>
                <span class="d-block text-dark">{{ invItem.title }}</span>
                <small class="text-muted font-monospace" v-if="invItem.serial_no">{{ $t('S/N') }}: {{ invItem.serial_no }}</small>
                <small class="text-muted" v-if="invItem.color_title || invItem.size_title"> ({{ invItem.color_title }} {{ invItem.size_title }})</small>
              </div>
              <div class="text-end">
                <span class="badge" :class="invItem.warranty_type === 'guarantee' ? 'bg-success' : (invItem.warranty_type !== 'none' && invItem.warranty_period ? 'bg-primary' : 'bg-secondary')">
                  {{ invItem.warranty_period || (invItem.warranty_type === 'none' ? $t('No Warranty') : $t(invItem.warranty_type)) }}
                </span>
                <i class="fas fa-check-circle text-primary ms-2" v-if="form.invoice_detail_id === invItem.id"></i>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- 1. ELIGIBLE BANNER -->
      <div class="alert alert-success border-2 shadow-sm d-flex align-items-start gap-3 p-3" v-if="verificationResult.eligible">
        <i class="fas fa-check-circle fs-1 text-success mt-1"></i>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="alert-heading fw-bold mb-1 text-success">
              <i class="fas fa-shield-alt me-1"></i>
              <span>{{ $t('Eligible for') }} {{ verificationResult.warranty_type === 'guarantee' ? $t('Guarantee') : $t('Warranty') }}!</span>
            </h5>
            <span class="badge bg-success fs-6 px-3 py-1 font-monospace">
              {{ verificationResult.remaining_days }} {{ $t('Days Remaining') }}
            </span>
          </div>
          <p class="mb-2 small text-dark">
            {{ verificationResult.message }}
          </p>
          <div class="row g-2 bg-white rounded p-2 border border-success-subtle small font-monospace text-dark">
            <div class="col-md-4"><strong>{{ $t('Invoice') }}:</strong> {{ verificationResult.invoice_no }} ({{ verificationResult.sale_date }})</div>
            <div class="col-md-4"><strong>{{ $t('Item') }}:</strong> {{ verificationResult.item_title }}</div>
            <div class="col-md-4"><strong>{{ $t('Policy') }}:</strong> {{ verificationResult.warranty_period }} ({{ $t('Exp') }}: {{ verificationResult.warranty_expiry_date }})</div>
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
              <span>{{ $t('Not Eligible for Free Warranty Coverage') }}</span>
            </h5>
            <span class="badge bg-danger fs-6 px-3 py-1 font-monospace" v-if="verificationResult.status_type === 'expired'">
              {{ $t('Expired') }} {{ verificationResult.expired_days }} {{ $t('Days Ago') }}
            </span>
            <span class="badge bg-secondary fs-6 px-3 py-1" v-if="verificationResult.status_type !== 'expired'">
              {{ verificationResult.status_type === 'unsold' ? $t('Unsold Item') : $t('No Warranty Policy') }}
            </span>
          </div>
          <p class="mb-2 small text-dark fw-bold">
            {{ verificationResult.message }}
          </p>

          <div class="bg-white rounded p-3 border shadow-sm small text-dark mt-2" v-if="verificationResult.sold">
            <h6 class="fw-bold border-bottom pb-1 mb-2 theme-text">
              <i class="fas fa-history me-1"></i>
              <span>{{ $t('Original Sale & Customer Information') }}:</span>
            </h6>
            <div class="row g-2 font-monospace">
              <div class="col-md-4"><strong>{{ $t('Invoice No') }}:</strong> {{ verificationResult.invoice_no }}</div>
              <div class="col-md-4"><strong>{{ $t('Sale Date') }}:</strong> {{ verificationResult.sale_date }}</div>
              <div class="col-md-4"><strong>{{ $t('Customer') }}:</strong> {{ verificationResult.customer_name }} ({{ verificationResult.customer_mobile }})</div>
              <div class="col-md-6"><strong>{{ $t('Product') }}:</strong> {{ verificationResult.item_title }} <span v-if="verificationResult.color_title">({{ verificationResult.color_title }}/{{ verificationResult.size_title }})</span></div>
              <div class="col-md-6"><strong>{{ $t('Expiry Date') }}:</strong> {{ verificationResult.warranty_expiry_date || $t('No Warranty Policy') }}</div>
            </div>

            <div class="mt-2 pt-2 border-top" v-if="verificationResult.existing_claims && verificationResult.existing_claims.length > 0">
              <span class="text-danger fw-bold">
                <i class="fas fa-exclamation-circle me-1"></i>
                <span>{{ $t('Previous Claims on this Serial') }}:</span>
              </span>
              <ul class="mb-0 ps-3 mt-1 small">
                <li v-for="ec in verificationResult.existing_claims" :key="ec.id">
                  <strong>{{ ec.claim_no }}</strong> ({{ ec.claim_date }}): {{ $t('Status') }}: <span class="badge bg-secondary">{{ $t(formatStatusLabel(ec.current_status)) }}</span> - {{ ec.problem_description }}
                </li>
              </ul>
            </div>

            <div class="form-check form-switch mt-3 pt-2 border-top">
              <input class="form-check-input cursor-pointer" type="checkbox" id="outOfWarrantyCheck" v-model="allowOutOfWarranty" style="transform: scale(1.2);">
              <label class="form-check-label fw-bold text-danger cursor-pointer ms-2" for="outOfWarrantyCheck">{{ $t('Proceed as Paid Repair / Out-of-Warranty Service Claim') }}</label>
            </div>
          </div>

          <div class="bg-white rounded p-3 border shadow-sm small text-dark mt-2" v-if="verificationResult.purchase_info">
            <h6 class="fw-bold border-bottom pb-1 mb-2 text-info">
              <i class="fas fa-boxes me-1"></i>
              <span>{{ $t('Purchase Record Information') }}:</span>
            </h6>
            <div class="row g-2 font-monospace">
              <div class="col-md-6"><strong>{{ $t('Product') }}:</strong> {{ verificationResult.purchase_info.item_title }}</div>
              <div class="col-md-6"><strong>{{ $t('Supplier') }}:</strong> {{ verificationResult.purchase_info.supplier }}</div>
              <div class="col-md-6"><strong>{{ $t('Purchase Date') }}:</strong> {{ verificationResult.purchase_info.purchase_date }}</div>
              <div class="col-md-6"><strong>{{ $t('Status') }}:</strong> {{ $t('Available in Stock (Not yet sold to any customer)') }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. NOT FOUND BANNER -->
      <div class="alert alert-danger border-2 shadow-sm d-flex align-items-center gap-3 p-3" v-if="!verificationResult.found">
        <i class="fas fa-times-circle fs-1 text-danger"></i>
        <div>
          <h5 class="alert-heading fw-bold mb-1">{{ $t('Serial Number Not Found!') }}</h5>
          <p class="mb-0 small">
            {{ verificationResult.message || $t('The specified serial number does not match any purchase or sales records in the database.') }}
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
                  <span>{{ $t('Item & Policy Details') }}</span>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="mb-2">
                  <label class="form-label small fw-bold text-muted mb-0">{{ $t('Item Title:') }}</label>
                  <div class="fw-bold text-dark fs-6">{{ form.item_title || $t('Item') }}</div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">{{ $t('Serial Number:') }}</label>
                    <div class="font-monospace fw-bold theme-text">{{ form.serial_no || $t('N/A') }}</div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">{{ $t('Invoice No:') }}</label>
                    <div class="font-monospace fw-bold text-dark">{{ form.invoice_no || $t('N/A') }}</div>
                  </div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">{{ $t('Coverage Type:') }}</label>
                    <div>
                      <span class="badge" :class="form.warranty_type === 'guarantee' ? 'bg-success' : 'theme-bg text-white'">
                        {{ form.warranty_type === 'guarantee' ? $t('Guarantee') : $t('Warranty') }}
                      </span>
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">{{ $t('Warranty Period:') }}</label>
                    <div class="small fw-bold text-dark">{{ form.warranty_period || $t('N/A') }}</div>
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">{{ $t('Sale Date:') }}</label>
                    <div class="small">{{ form.sale_date || $t('N/A') }}</div>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-0">{{ $t('Expiry Date:') }}</label>
                    <div class="small fw-bold text-danger">{{ form.warranty_expiry_date || $t('N/A') }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. Customer Contact Details Card (Directly Below Item Card) -->
            <div class="card border-0 shadow-sm form-section-card">
              <div class="card-header theme-bg text-white py-2 px-3">
                <span class="fw-bold small">
                  <i class="fas fa-user me-2"></i>
                  <span>{{ $t('Customer Contact Details') }}</span>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="mb-2">
                  <label class="form-label small fw-bold text-dark mb-1">{{ $t('Customer Name') }} *</label>
                  <input type="text" class="form-control form-control-sm fw-bold" v-model="form.customer_name" required :placeholder="$t('Customer Name')">
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-bold text-dark mb-1">{{ $t('Mobile Number') }} *</label>
                  <input type="text" class="form-control form-control-sm font-monospace" v-model="form.customer_mobile" required placeholder="017xxxxxxxx">
                </div>
                <div>
                  <label class="form-label small fw-bold text-dark mb-1">{{ $t('Address') }}</label>
                  <textarea class="form-control form-control-sm" rows="2" v-model="form.customer_address" :placeholder="$t('Customer address...')"></textarea>
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
                  <span>{{ $t('Claim & Problem Details') }}</span>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">{{ $t('Claim Date') }} *</label>
                    <input type="date" class="form-control form-control-sm" v-model="form.claim_date" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">{{ $t('Expected Delivery Date') }}</label>
                    <input type="date" class="form-control form-control-sm" v-model="form.expected_delivery_date">
                  </div>

                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">{{ $t('Problem Description') }} *</label>
                    <textarea class="form-control" rows="3" v-model="form.problem_description" required :placeholder="$t('e.g. Display not working, Power issue, Sound buzzing...')"></textarea>
                  </div>

                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">{{ $t('Received Accessories / Items') }}</label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Original Box')">+ {{ $t('Original Box') }}</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Power Adapter / Charger')">+ {{ $t('Adapter') }}</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('USB / Power Cable')">+ {{ $t('Cable') }}</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Warranty Card / Receipt')">+ {{ $t('Warranty Card') }}</span>
                      <span class="badge bg-light text-dark border cursor-pointer accessory-chip" @click="appendAccessory('Remote Controller')">+ {{ $t('Remote') }}</span>
                    </div>
                    <input type="text" class="form-control form-control-sm" v-model="form.accessories_received" :placeholder="$t('e.g. Original Box, Power Adapter, Remote')">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">{{ $t('Initial Claim Status') }} *</label>
                    <select class="form-select form-select-sm" v-model="form.current_status" required>
                      <option value="received">{{ $t('Received') }}</option>
                      <option value="sent_to_vendor">{{ $t('Sent to Vendor / Service Center') }}</option>
                      <option value="in_service">{{ $t('In Service / Repairing') }}</option>
                      <option value="repaired">{{ $t('Repaired') }}</option>
                      <option value="replaced">{{ $t('Replaced') }}</option>
                      <option value="ready_for_delivery">{{ $t('Ready for Delivery') }}</option>
                      <option value="delivered">{{ $t('Delivered') }}</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-bold text-dark">{{ $t('Internal Service Cost') }}</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light">Tk.</span>
                      <input type="number" step="0.01" min="0" class="form-control form-control-sm font-monospace text-end" v-model.number="form.service_cost" placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-bold text-dark">
                      <span>{{ $t('Customer Charge') }}</span>
                    </label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light">Tk.</span>
                      <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        class="form-control form-control-sm font-monospace text-end" 
                        :class="{ 'bg-light text-muted': (isEdit || !!form.payment_id) }"
                        :readonly="isEdit || !!form.payment_id"
                        v-model.number="form.customer_charge" 
                        placeholder="0.00"
                        :title="isEdit || form.payment_id ? $t('Payment Received (Locked)') : ''"
                      >
                      <span v-if="isEdit || form.payment_id" class="input-group-text bg-light text-secondary border-start-0 px-2" :title="$t('Payment Received (Locked)')">
                        <i class="fas fa-lock fa-xs"></i>
                      </span>
                    </div>
                  </div>

                  <div class="col-12" v-if="!isEdit">
                    <label class="form-label small fw-bold text-dark">{{ $t('Initial Tracking Note / Remarks') }}</label>
                    <textarea class="form-control form-control-sm" rows="2" v-model="form.initial_remarks" :placeholder="$t('Optional notes for tracking timeline...')"></textarea>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center border-top mt-auto">
                <router-link :to="{ name: 'warrantyClaim.index' }" class="btn btn-outline-secondary btn-sm px-4">
                  <span>{{ $t('Cancel') }}</span>
                </router-link>
                <button type="submit" class="btn btn-theme px-5 fw-bold shadow-sm" :disabled="submitting">
                  <i :class="submitting ? 'fas fa-spinner fa-spin' : 'fas fa-save'" class="me-1"></i>
                  <span>{{ isEdit ? $t('Update Claim') : $t('Save & Register Claim') }}</span>
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
        expense_id: null,
        payment_id: null,
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
        this.$toast(this.$t('Please enter or scan a serial number or invoice number'), 'warning');
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
          this.$toast(this.$t('Error verifying serial or invoice number'), 'error');
          console.error(err);
        });
    },
    selectInvoiceItem(detailId) {
      if (!detailId) return;
      this.searching = true;
      axios.get('warrantyClaim/check-serial', { 
        params: { 
          serial_no: this.searchSerial.trim(), 
          invoice_detail_id: detailId 
        } 
      })
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
          this.$toast(this.$t('Error selecting invoice item'), 'error');
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
        this.$toast(this.$t('Please fill all required fields marked with *'), 'warning');
        return;
      }

      this.submitting = true;
      const url = this.isEdit ? `${this.model}/${this.form.id}` : this.model;
      const method = this.isEdit ? 'put' : 'post';

      axios[method](url, this.form)
        .then(res => {
          const claimId = this.isEdit ? this.form.id : (res.data.data ? res.data.data.id : res.data.id);
          this.$toast(this.isEdit ? this.$t('Warranty Claim updated successfully!') : this.$t('Warranty Claim registered successfully!'), 'success');
          if (!this.isEdit) {
            this.$router.push({ name: 'warrantyClaim.show', params: { id: claimId }, query: { autoPrint: 1 } });
          } else {
            this.$router.push({ name: 'warrantyClaim.show', params: { id: claimId } });
          }
        })
        .catch(err => {
          this.$toast(err.response?.data?.message || err.response?.data?.exception || this.$t('Failed to save warranty claim'), 'error');
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
            expense_id: d.expense_id,
            payment_id: d.payment_id,
            initial_remarks: '',
          };
        })
        .catch(err => {
          this.$toast(this.$t('Failed to load claim for editing'), 'error');
          console.error(err);
        })
        .finally(() => {
          this.$root.submit = false;
        });
    },
    formatStatusLabel(status) {
      if (!status) return 'Received';
      const labels = {
        received: 'Received',
        in_progress: 'In Progress',
        sent_to_vendor: 'Sent To Vendor',
        in_service: 'In Service',
        repaired: 'Repaired',
        replaced: 'Replaced',
        ready_for_delivery: 'Ready For Delivery',
        delivered: 'Delivered',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
        pending: 'Pending',
      };
      if (labels[status]) return labels[status];
      return String(status).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    },
  },
  created() {
    if (this.$route.params.id) {
      this.isEdit = true;
      this.loadClaimForEdit(this.$route.params.id);
    }
  },
  mounted() {
    const qSerial = this.$route.query.serial || this.$route.query.serial_no || this.$route.query.invoice || this.$route.query.invoice_no;
    if (qSerial && !this.isEdit) {
      this.searchSerial = String(qSerial).trim();
      this.$nextTick(() => {
        this.checkSerialEligibility();
      });
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
