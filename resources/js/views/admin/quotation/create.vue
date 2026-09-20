<template>
  <create-form @onSubmit="submit">
    <!-- 1. Quotation Header & Client Information -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border-0 bg-white theme-card">
        <div class="card-header theme-card-header py-2 d-flex justify-content-between align-items-center">
          <span class="fw-bold text-white d-flex align-items-center">
            <i class="fas fa-file-invoice-dollar me-2 text-warning fs-5"></i>
            <span>Quotation Information (কোটেশনের সাধারণ তথ্য ও গ্রাহক বিবরণ)</span>
          </span>
          <span class="badge bg-light text-dark px-3 py-1 font-monospace fw-bold" v-if="data.quotation_no">
            <i class="fas fa-hashtag me-1 text-primary"></i> {{ data.quotation_no }}
          </span>
        </div>
        <div class="card-body p-2 px-3">
          <div class="row g-2">
            <!-- Row 1: Quotation Metadata -->
            <!-- Quotation No -->
            <Input v-model="data.quotation_no" field="data.quotation_no" col="3" title="Quotation No (কোটেশন নং)"
              placeholder="Auto Generated" :req="false" :readonly="true" />

            <!-- Quotation Date -->
            <date-picker id="quotation_date" v-model="data.quotation_date" field="data.quotation_date"
              title="Quotation Date (তারিখ)" placeholder="Select Date" col="3" :req="true" />

            <!-- Validity Date -->
            <date-picker id="validity_date" v-model="data.validity_date" field="data.validity_date"
              title="Valid Until (মেয়াদ শেষ)" placeholder="Select Validity" col="3" :req="false" />

            <!-- Status -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme mb-1">
                <i class="fas fa-flag me-1"></i> Status (অবস্থা)
              </label>
              <select class="form-select form-select-sm shadow-sm" v-model="data.status">
                <option value="draft">Draft (খসড়া)</option>
                <option value="sent">Sent to Client (প্রেরিত)</option>
                <option value="accepted">Accepted (গৃহীত)</option>
                <option value="declined">Declined (বাতিল)</option>
                <option value="converted">Converted to Invoice (ইনভয়েস)</option>
                <option value="expired">Expired (মেয়াদোত্তীর্ণ)</option>
              </select>
            </div>

            <!-- Row 2: Customer & Contacts -->
            <!-- Client Selector -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-theme mb-1 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-tie me-1"></i> Customer / Client (গ্রাহক):</span>
                <span class="text-muted small" v-if="selectedClientInfo">📱 {{ selectedClientInfo.mobile }}</span>
              </label>
              <v-select
                v-model="data.client_id"
                :options="clients"
                label="org_name"
                :reduce="(obj) => obj.id"
                placeholder="-- Select Client --"
                @option:selected="onClientSelect"
                class="shadow-sm vs-compact"
              >
                <template #option="option">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>{{ option.org_name || option.name }}</strong>
                      <span class="text-muted small d-block" v-if="option.org_name && option.name">Attn: {{ option.name }}</span>
                    </div>
                    <small class="text-primary font-monospace" v-if="option.mobile">📱 {{ option.mobile }}</small>
                  </div>
                </template>
              </v-select>
            </div>

            <!-- Client / Org Name -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme mb-1">
                <i class="fas fa-building me-1"></i> Client / Org Name
              </label>
              <input type="text" class="form-control form-control-sm shadow-sm" v-model="data.client_name" placeholder="Client Name..." />
            </div>

            <!-- Client Phone -->
            <div class="col-md-2">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-phone-alt me-1"></i> Phone No
              </label>
              <input type="text" class="form-control form-control-sm" v-model="data.client_phone" placeholder="017xxxxxxxx" />
            </div>

            <!-- Client Email -->
            <div class="col-md-3">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-envelope me-1"></i> Email Address
              </label>
              <input type="email" class="form-control form-control-sm" v-model="data.client_email" placeholder="client@example.com" />
            </div>

            <!-- Row 3: Subject, Reference, Address -->
            <!-- Quotation Subject -->
            <div class="col-md-5">
              <label class="form-label fw-bold small text-theme mb-1">
                <i class="fas fa-heading me-1"></i> Quotation Subject (কোটেশনের বিষয়)
              </label>
              <input type="text" class="form-control form-control-sm shadow-sm" v-model="data.subject"
                placeholder="e.g. Commercial Quotation for Products & Services" />
            </div>

            <!-- Reference / RFQ No -->
            <div class="col-md-3">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-bookmark me-1"></i> Reference / RFQ No
              </label>
              <input type="text" class="form-control form-control-sm" v-model="data.reference_no" placeholder="e.g. RFQ-2026-88" />
            </div>

            <!-- Billing / Delivery Address -->
            <div class="col-md-4">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-map-marker-alt me-1 text-danger"></i> Delivery / Billing Address
              </label>
              <input type="text" class="form-control form-control-sm" v-model="data.client_address" placeholder="Address..." />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Product Search, Category Filter & Custom Service Item Addition -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border-0 theme-card">
        <div class="card-header theme-card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span class="fw-bold text-white d-flex align-items-center">
            <i class="fas fa-cart-plus me-2 text-warning fs-5"></i>
            <span>Add Items to Quotation (পণ্য ও সার্ভিস যোগ করুন)</span>
          </span>
          <div class="d-flex align-items-center gap-2">
            <button
              type="button"
              class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm fw-semibold px-3"
              @click="addCustomItem('service')"
            >
              <i class="fas fa-tools"></i> + Add Service (সার্ভিস যোগ)
            </button>
            <button
              type="button"
              class="btn btn-sm btn-warning text-dark d-inline-flex align-items-center gap-1 shadow-sm fw-bold px-3"
              @click="addCustomItem('custom')"
            >
              <i class="fas fa-plus-circle"></i> + Add Custom Item / Software
            </button>
          </div>
        </div>
        <div class="card-body p-3 bg-light-subtle">
          <div class="row g-2 align-items-end">
            <!-- Category Filter -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-theme d-flex justify-content-between align-items-center">
                <span><i class="fas fa-layer-group me-1 text-primary"></i> 1. Filter Category:</span>
                <span class="text-muted small" v-if="categories.length">({{ categories.length }} Categories)</span>
              </label>
              <select class="form-select shadow-sm" v-model="selectedCategoryId" @change="onCategoryChange">
                <option :value="null">-- All Categories (সকল ক্যাটাগরি) --</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.title }}
                </option>
              </select>
            </div>

            <!-- Barcode Scanner -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme">
                <i class="fas fa-barcode me-1 text-danger"></i> 2. Barcode Scan:
              </label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-white text-danger border-end-0">
                  <i class="fas fa-barcode"></i>
                </span>
                <input
                  ref="barcodeInput"
                  type="text"
                  class="form-control border-start-0"
                  v-model.trim="barcodeScanInput"
                  @keyup.enter="handleBarcodeScan"
                  placeholder="Scan barcode & Enter..."
                  autocomplete="off"
                />
              </div>
            </div>

            <!-- Product Dropdown Search -->
            <div class="col-md-5">
              <label class="form-label fw-bold small text-theme">
                <i class="fas fa-box-open me-1 text-primary"></i> 3. Search & Add Catalog Item (ক্যাটালগ পণ্য):
              </label>
              <v-select
                v-model="selectedItem"
                :options="itemList"
                label="title"
                placeholder="Type item name or barcode to select..."
                @option:selected="onItemSelect"
                @search="onSearchItems"
                class="shadow-sm"
              >
                <template #no-options="{ search, searching }">
                  <template v-if="searching">
                    <span class="text-muted small">No items matching "<em>{{ search }}</em>"</span>
                  </template>
                  <em v-else class="text-muted small">Type product name or barcode...</em>
                </template>
                <template #option="option">
                  <div class="d-flex justify-content-between align-items-center py-1">
                    <div>
                      <strong>{{ option.title }}</strong>
                      <div class="small text-muted">
                        <span v-if="option.barcode">Barcode: <code>{{ option.barcode }}</code></span>
                        <span v-if="option.unit" class="ms-2">Unit: {{ option.unit.title }}</span>
                      </div>
                    </div>
                    <div class="text-end font-monospace">
                      <span class="badge bg-success text-white">৳ {{ formatCurrencyNumber(option.selling_price) }}</span>
                    </div>
                  </div>
                </template>
              </v-select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Quotation Line Items Table -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border-0 theme-card">
        <div class="card-header theme-card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span class="fw-bold text-white d-flex align-items-center">
            <i class="fas fa-list-ol me-2 text-warning fs-5"></i>
            <span>Quotation Item Matrix (আইটেম তালিকা ও রেট)</span>
          </span>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark font-monospace px-3 py-1 fw-bold">
              Total Lines: {{ details.length }}
            </span>
            <button
              type="button"
              class="btn btn-xs btn-outline-light py-1 px-2"
              v-if="details.length > 0"
              @click="clearAllDetails"
            >
              <i class="fas fa-trash me-1"></i> Clear All
            </button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 matrix-table">
              <thead class="theme-matrix-header text-center small text-uppercase">
                <tr>
                  <th style="width: 4%;">#</th>
                  <th style="width: 10%;">Type</th>
                  <th style="width: 32%;">Item / Service Name & Specification</th>
                  <th style="width: 10%;">Unit</th>
                  <th style="width: 10%;">Qty</th>
                  <th style="width: 14%;">Unit Price (৳)</th>
                  <th style="width: 15%;">Total (৳)</th>
                  <th style="width: 5%;">Action</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="details.length > 0">
                  <tr v-for="(row, index) in details" :key="index" :class="{ 'bg-service-row': row.item_type !== 'product' }">
                    <!-- SL -->
                    <td class="text-center font-monospace text-muted fw-bold">{{ index + 1 }}</td>

                    <!-- Type Badge -->
                    <td class="text-center">
                      <span class="badge bg-primary" v-if="row.item_type === 'product'">
                        <i class="fas fa-box me-1"></i> Product
                      </span>
                      <span class="badge bg-success" v-else-if="row.item_type === 'service'">
                        <i class="fas fa-tools me-1"></i> Service
                      </span>
                      <span class="badge bg-info text-dark" v-else>
                        <i class="fas fa-cube me-1"></i> Custom
                      </span>
                    </td>

                    <!-- Item Name & Description -->
                    <td>
                      <input
                        type="text"
                        class="form-control form-control-sm fw-bold mb-1"
                        v-model="row.item_name"
                        placeholder="Item or Service Name..."
                      />
                      <textarea
                        class="form-control form-control-sm text-muted small"
                        rows="2"
                        v-model="row.description"
                        placeholder="Technical specifications / details description..."
                      ></textarea>
                    </td>

                    <!-- Unit -->
                    <td>
                      <input
                        type="text"
                        class="form-control form-control-sm text-center"
                        v-model="row.unit_name"
                        placeholder="Pcs/Job/Set"
                      />
                    </td>

                    <!-- Quantity -->
                    <td>
                      <input
                        type="number"
                        step="any"
                        min="0.01"
                        class="form-control form-control-sm text-center font-monospace fw-bold"
                        v-model.number="row.qty"
                        @input="recalculateRow(row)"
                      />
                    </td>

                    <!-- Unit Price (Selling Price - Editable Up/Down) -->
                    <td>
                      <div class="input-group input-group-sm">
                        <span class="input-group-text font-monospace">৳</span>
                        <input
                          type="number"
                          step="any"
                          min="0"
                          class="form-control form-control-sm text-end font-monospace fw-bold"
                          v-model.number="row.unit_price"
                          @input="recalculateRow(row)"
                        />
                      </div>
                    </td>

                    <!-- Row Total Amount -->
                    <td class="text-end font-monospace fw-bold fs-6" style="color: #112C47;">
                      ৳ {{ formatCurrencyNumber(row.total_price) }}
                    </td>

                    <!-- Action -->
                    <td class="text-center">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger p-1"
                        @click="removeRow(index)"
                        title="Remove Item"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fas fa-file-invoice-dollar fa-3x mb-2 text-secondary opacity-25 d-block"></i>
                      <h6 class="fw-bold text-secondary">No Items Added Yet</h6>
                      <p class="small text-muted mb-0">
                        Please select an item from catalog above or click <strong>+ Add Custom Item / Service</strong>.
                      </p>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Financial Calculations, Summary & Amount In Words -->
    <div class="col-12 mb-3">
      <div class="row g-3">
        <!-- 4.1 Terms & Notes Card -->
        <div class="col-lg-7 col-md-12">
          <div class="card shadow-sm border-0 bg-white h-100 theme-card">
            <div class="card-header theme-card-header py-2">
              <span class="fw-bold text-white d-flex align-items-center">
                <i class="fas fa-clipboard-list me-2 text-warning fs-5"></i>
                <span>Commercial Terms & Conditions (পেমেন্ট ও ডেলিভারি শর্তাবলী)</span>
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <!-- Payment Terms -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme d-flex justify-content-between">
                    <span><i class="fas fa-credit-card me-1"></i> Payment Terms (পেমেন্ট শর্ত):</span>
                    <span class="small text-muted">
                      <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.payment_terms = '50% Advance along with Work Order, remaining 50% upon delivery/completion.'">Preset 1</button> |
                      <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.payment_terms = '100% Cash/Cheque on Delivery.'">Preset 2</button>
                    </span>
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.payment_terms" placeholder="e.g. 50% Advance, 50% on Delivery..." />
                </div>

                <!-- Delivery Terms -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme d-flex justify-content-between">
                    <span><i class="fas fa-truck me-1"></i> Delivery Terms & Lead Time (ডেলিভারি সময়সীমা):</span>
                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.delivery_terms = 'Within 3-5 working days after receiving confirmed work order.'">Preset</button>
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.delivery_terms" placeholder="e.g. Within 3-5 working days..." />
                </div>

                <!-- Warranty Terms -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme d-flex justify-content-between">
                    <span><i class="fas fa-shield-alt me-1"></i> Warranty / Support Terms (ওয়ারেন্টি ও সাপোর্ট):</span>
                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.warranty_terms = '1 Year official warranty & free technical support.'">Preset</button>
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.warranty_terms" placeholder="e.g. 1 Year service warranty..." />
                </div>

                <!-- Terms & Conditions Multiline -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme">
                    <i class="fas fa-file-contract me-1"></i> Terms & Conditions (অন্যান্য সাধারণ শর্তাবলী):
                  </label>
                  <textarea class="form-control form-control-sm" rows="3" v-model="data.terms_conditions" placeholder="1. Quotation validity is 15 days.&#10;2. Prices are inclusive/exclusive of VAT.&#10;3. Physical signature required upon acceptance."></textarea>
                </div>

                <!-- Note -->
                <div class="col-12">
                  <label class="form-label small fw-semibold text-theme">
                    <i class="fas fa-sticky-note me-1"></i> Special Client Note (কোটেশনে বিশেষ নোট):
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.note" placeholder="Note shown on quotation..." />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 4.2 Financial Summary Calculation Box -->
        <div class="col-lg-5 col-md-12">
          <div class="card shadow-sm border-0 bg-white h-100 theme-card">
            <div class="card-header theme-card-header py-2">
              <span class="fw-bold text-white d-flex align-items-center">
                <i class="fas fa-calculator me-2 text-warning fs-5"></i>
                <span>Quotation Financial Summary (মোট হিসাব)</span>
              </span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 align-middle">
                <tbody>
                  <!-- Sub Total -->
                  <tr class="border-bottom">
                    <td class="fw-bold text-secondary">Sub Total (উপ-মোট):</td>
                    <td class="text-end font-monospace fw-bold text-dark fs-6">
                      ৳ {{ formatCurrencyNumber(calculatedSubTotal) }}
                    </td>
                  </tr>

                  <!-- Discount -->
                  <tr class="border-bottom">
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <span class="fw-bold text-secondary">Discount:</span>
                        <select class="form-select form-select-sm py-0 px-1" style="width: 75px;" v-model="data.discount_type" @change="recalculateSummary">
                          <option value="fixed">Fixed ৳</option>
                          <option value="percentage">% Percent</option>
                        </select>
                      </div>
                    </td>
                    <td class="text-end">
                      <div class="input-group input-group-sm justify-content-end" style="max-width: 140px; margin-left: auto;">
                        <input
                          type="number"
                          step="any"
                          min="0"
                          class="form-control form-control-sm text-end font-monospace"
                          v-model.number="data.discount"
                          @input="recalculateSummary"
                          placeholder="0"
                        />
                        <span class="input-group-text">{{ data.discount_type === 'percentage' ? '%' : '৳' }}</span>
                      </div>
                      <small class="text-muted d-block mt-1 font-monospace" v-if="data.discount_type === 'percentage'">
                        - ৳ {{ formatCurrencyNumber(calculatedDiscountAmount) }}
                      </small>
                    </td>
                  </tr>

                  <!-- Tax / VAT -->
                  <tr class="border-bottom">
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <span class="fw-bold text-secondary">VAT / Tax (%):</span>
                      </div>
                    </td>
                    <td class="text-end">
                      <div class="input-group input-group-sm justify-content-end" style="max-width: 140px; margin-left: auto;">
                        <input
                          type="number"
                          step="any"
                          min="0"
                          class="form-control form-control-sm text-end font-monospace"
                          v-model.number="data.tax_percent"
                          @input="recalculateSummary"
                          placeholder="0"
                        />
                        <span class="input-group-text">%</span>
                      </div>
                      <small class="text-muted d-block mt-1 font-monospace" v-if="data.tax_percent > 0">
                        + ৳ {{ formatCurrencyNumber(calculatedTaxAmount) }}
                      </small>
                    </td>
                  </tr>

                  <!-- Shipping / Delivery Charge -->
                  <tr class="border-bottom">
                    <td class="fw-bold text-secondary">Shipping / Handling:</td>
                    <td class="text-end">
                      <div class="input-group input-group-sm justify-content-end" style="max-width: 140px; margin-left: auto;">
                        <span class="input-group-text">৳</span>
                        <input
                          type="number"
                          step="any"
                          min="0"
                          class="form-control form-control-sm text-end font-monospace"
                          v-model.number="data.shipping_cost"
                          @input="recalculateSummary"
                          placeholder="0"
                        />
                      </div>
                    </td>
                  </tr>

                  <!-- Net Grand Total Box -->
                  <tr class="theme-grand-total-box rounded">
                    <td class="fw-bold fs-5 py-2" style="color: #112C47;">Grand Total (সর্বমোট):</td>
                    <td class="text-end font-monospace fw-bold fs-4 py-2" style="color: #112C47;">
                      ৳ {{ formatCurrencyNumber(calculatedGrandTotal) }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- 🌟 IN WORDS LIVE SECTION (Requirement 4) -->
              <div class="mt-3 p-3 bg-light border rounded-3" style="border-left: 4px solid #112C47 !important;">
                <div class="d-flex align-items-center gap-1 small fw-bold mb-1" style="color: #112C47;">
                  <i class="fas fa-money-bill-wave text-success"></i>
                  <span>AMOUNT IN WORDS (টাকায় কথায়):</span>
                </div>
                <div class="fw-bold text-dark small font-monospace text-capitalize">
                  {{ $filter.numberToEnglishBD(calculatedGrandTotal) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
import axios from "axios";
import moment from "moment";

const model = "quotation";

export default {
  data() {
    return {
      model: model,
      page_title: "Create Quotation",
      data: {
        quotation_no: "",
        quotation_date: moment().format("YYYY-MM-DD"),
        validity_date: moment().add(15, "days").format("YYYY-MM-DD"),
        client_id: null,
        client_name: "",
        client_phone: "",
        client_email: "",
        client_address: "",
        subject: "Commercial Quotation for Products & Services",
        reference_no: "",
        status: "draft",
        branch_id: null,
        currency_id: null,
        currency_rate: 1,
        sub_total: 0,
        discount_type: "fixed",
        discount: 0,
        discount_amount: 0,
        tax_percent: 0,
        tax_amount: 0,
        shipping_cost: 0,
        total_amount: 0,
        payment_terms: "50% Advance along with Work Order, remaining 50% upon delivery/completion.",
        delivery_terms: "Within 3-5 working days after receiving confirmed work order.",
        warranty_terms: "1 Year standard warranty & technical support.",
        terms_conditions: "1. Quotation is valid for 15 days from the date of issue.\n2. Prices are subject to revision after validity period.\n3. Physical signature and acceptance required.",
        note: "",
        remarks: "",
      },
      details: [],
      clients: [],
      categories: [],
      itemList: [],
      selectedCategoryId: null,
      selectedItem: null,
      barcodeScanInput: "",
      selectedClientInfo: null,
    };
  },

  computed: {
    calculatedSubTotal() {
      let sum = 0;
      this.details.forEach((row) => {
        sum += Number(row.total_price || 0);
      });
      return Math.max(0, sum);
    },

    calculatedDiscountAmount() {
      const sub = this.calculatedSubTotal;
      const disc = Number(this.data.discount || 0);
      if (this.data.discount_type === "percentage") {
        return Math.max(0, (sub * disc) / 100);
      }
      return Math.max(0, disc);
    },

    calculatedTaxAmount() {
      const afterDisc = Math.max(0, this.calculatedSubTotal - this.calculatedDiscountAmount);
      const taxPct = Number(this.data.tax_percent || 0);
      return Math.max(0, (afterDisc * taxPct) / 100);
    },

    calculatedGrandTotal() {
      const afterDisc = Math.max(0, this.calculatedSubTotal - this.calculatedDiscountAmount);
      const tax = this.calculatedTaxAmount;
      const shipping = Number(this.data.shipping_cost || 0);
      return Math.max(0, Math.round((afterDisc + tax + shipping) * 100) / 100);
    },
  },

  methods: {
    formatCurrencyNumber(val) {
      const num = Number(val || 0);
      return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    recalculateRow(row) {
      const qty = Number(row.qty || 0);
      const unitPrice = Number(row.unit_price || 0);
      const discPct = Number(row.discount_percent || 0);
      let discAmt = Number(row.discount_amount || 0);

      if (discPct > 0 && discAmt === 0) {
        discAmt = (qty * unitPrice * discPct) / 100;
      }
      row.total_price = Math.max(0, Math.round((qty * unitPrice - discAmt) * 100) / 100);
      this.recalculateSummary();
    },

    recalculateSummary() {
      this.data.sub_total = this.calculatedSubTotal;
      this.data.discount_amount = this.calculatedDiscountAmount;
      this.data.tax_amount = this.calculatedTaxAmount;
      this.data.total_amount = this.calculatedGrandTotal;
    },

    onClientSelect(client) {
      if (!client) return;
      this.selectedClientInfo = client;
      this.data.client_name = client.org_name || client.name || "";
      this.data.client_phone = client.mobile || client.phone || "";
      this.data.client_email = client.email || "";
      this.data.client_address = client.address || "";
    },

    onCategoryChange() {
      if (this.selectedCategoryId) {
        axios.get(`getitemsbycategory/${this.selectedCategoryId}`).then((res) => {
          this.itemList = res.data || [];
        });
      } else {
        this.fetchItems();
      }
    },

    fetchItems(query = "") {
      const url = query ? `item?allData=true&value=${query}&field_name=title` : `item?allData=true`;
      axios.get(url).then((res) => {
        this.itemList = res.data || [];
      });
    },

    onSearchItems(search, loading) {
      if (search && search.length > 1) {
        loading(true);
        axios
          .get(`item?allData=true&value=${search}&field_name=title`)
          .then((res) => {
            this.itemList = res.data || [];
            loading(false);
          })
          .catch(() => {
            loading(false);
          });
      }
    },

    onItemSelect(item) {
      if (!item) return;

      const existingIndex = this.details.findIndex(
        (d) => d.item_type === "product" && d.item_id === item.id
      );

      if (existingIndex > -1) {
        this.details[existingIndex].qty += 1;
        this.recalculateRow(this.details[existingIndex]);
        this.$toast(`Incremented quantity for ${item.title}`, "info");
      } else {
        const unitPrice = Number(item.selling_price || 0);
        const newRow = {
          item_type: "product",
          category_id: item.category_id || null,
          item_id: item.id,
          brand_id: item.brand_id || null,
          color_id: null,
          size_id: null,
          item_name: item.title,
          description: item.description || "",
          unit_id: item.unit_id || null,
          unit_name: item.unit ? item.unit.title : "Pcs",
          qty: 1,
          unit_price: unitPrice,
          discount_percent: 0,
          discount_amount: 0,
          total_price: unitPrice,
        };
        this.details.push(newRow);
        this.recalculateSummary();
        this.$toast(`Added ${item.title} to quotation`, "success");
      }

      this.selectedItem = null;
    },

    addCustomItem(type = "custom") {
      const defaultTitle = type === "service" ? "Service / Maintenance Work" : "Custom Software / Unlisted Item";
      const defaultUnit = type === "service" ? "Job" : "Pcs";

      const newRow = {
        item_type: type,
        category_id: null,
        item_id: null,
        brand_id: null,
        color_id: null,
        size_id: null,
        item_name: defaultTitle,
        description: "",
        unit_id: null,
        unit_name: defaultUnit,
        qty: 1,
        unit_price: 0,
        discount_percent: 0,
        discount_amount: 0,
        total_price: 0,
      };

      this.details.push(newRow);
      this.recalculateSummary();
      this.$toast(`Added new ${type} item row`, "info");
    },

    handleBarcodeScan() {
      const barcode = this.barcodeScanInput.trim();
      if (!barcode) return;

      axios
        .get(`item?allData=true&value=${barcode}&field_name=barcode`)
        .then((res) => {
          const items = res.data || [];
          if (items.length > 0) {
            this.onItemSelect(items[0]);
            this.barcodeScanInput = "";
          } else {
            this.$toast(`No product found with barcode "${barcode}"`, "warning");
          }
        })
        .catch(() => {
          this.$toast(`Error searching for barcode "${barcode}"`, "error");
        });
    },

    removeRow(index) {
      this.details.splice(index, 1);
      this.recalculateSummary();
    },

    clearAllDetails() {
      if (confirm("Are you sure you want to remove all items from this quotation?")) {
        this.details = [];
        this.recalculateSummary();
      }
    },

    getClients() {
      axios.get("client?allData=true").then((res) => {
        this.clients = res.data || [];
      });
    },

    getCategories() {
      axios.get("getcategories/Item").then((res) => {
        this.categories = res.data || [];
      });
    },

    loadQuotationData(id) {
      this.$root.spinner = true;
      axios
        .get(`quotation/${id}`)
        .then((res) => {
          const q = res.data;
          if (q) {
            this.data = {
              id: q.id,
              quotation_no: q.quotation_no,
              quotation_date: q.quotation_date ? moment(q.quotation_date).format("YYYY-MM-DD") : moment().format("YYYY-MM-DD"),
              validity_date: q.validity_date ? moment(q.validity_date).format("YYYY-MM-DD") : "",
              client_id: q.client_id,
              client_name: q.client_name || (q.client ? q.client.org_name || q.client.name : ""),
              client_phone: q.client_phone || (q.client ? q.client.mobile : ""),
              client_email: q.client_email || (q.client ? q.client.email : ""),
              client_address: q.client_address || (q.client ? q.client.address : ""),
              subject: q.subject || "",
              reference_no: q.reference_no || "",
              status: q.status || "draft",
              branch_id: q.branch_id,
              currency_id: q.currency_id,
              currency_rate: q.currency_rate || 1,
              sub_total: Number(q.sub_total || 0),
              discount_type: q.discount_type || "fixed",
              discount: Number(q.discount || 0),
              discount_amount: Number(q.discount_amount || 0),
              tax_percent: Number(q.tax_percent || 0),
              tax_amount: Number(q.tax_amount || 0),
              shipping_cost: Number(q.shipping_cost || 0),
              total_amount: Number(q.total_amount || 0),
              payment_terms: q.payment_terms || "",
              delivery_terms: q.delivery_terms || "",
              warranty_terms: q.warranty_terms || "",
              terms_conditions: q.terms_conditions || "",
              note: q.note || "",
              remarks: q.remarks || "",
            };

            if (q.client) {
              this.selectedClientInfo = q.client;
            }

            if (q.quotation_details && Array.isArray(q.quotation_details)) {
              this.details = q.quotation_details.map((d) => ({
                id: d.id,
                item_type: d.item_type || "product",
                category_id: d.category_id,
                item_id: d.item_id,
                brand_id: d.brand_id,
                color_id: d.color_id,
                size_id: d.size_id,
                item_name: d.item_name || (d.item ? d.item.title : ""),
                description: d.description || "",
                unit_id: d.unit_id,
                unit_name: d.unit_name || (d.unit ? d.unit.title : "Pcs"),
                qty: Number(d.qty || 1),
                unit_price: Number(d.unit_price || 0),
                discount_percent: Number(d.discount_percent || 0),
                discount_amount: Number(d.discount_amount || 0),
                total_price: Number(d.total_price || 0),
              }));
            }
            this.recalculateSummary();
          }
        })
        .catch((err) => {
          console.error("Error loading quotation:", err);
          this.$toast("Failed to load quotation details", "error");
        })
        .finally(() => {
          this.$root.spinner = false;
        });
    },

    submit() {
      if (this.details.length === 0) {
        this.$toast("Please add at least one item or service to the quotation!", "error");
        return;
      }

      this.recalculateSummary();

      const payload = {
        ...this.data,
        quotation_details: this.details,
      };

      if (this.$route.params.id) {
        this.update(this.model, payload, this.$route.params.id);
      } else {
        this.store(this.model, payload);
      }
    },
  },

  created() {
    this.getClients();
    this.getCategories();
    this.fetchItems();

    if (this.$route.params.id) {
      this.page_title = "Edit Quotation (কোটেশন সংশোধন)";
      this.loadQuotationData(this.$route.params.id);
    } else {
      this.page_title = "Create New Quotation (নতুন কোটেশন তৈরি)";
    }
  },

  validators: {
    "data.quotation_date": function (value = null) {
      return Validator.value(value).required("Quotation Date is required");
    },
  },
};
</script>

<style scoped>
.theme-card {
  border-radius: 8px;
  overflow: visible !important;
  box-shadow: 0 2px 8px rgba(17, 44, 71, 0.08) !important;
}

.theme-card-header {
  background-color: #112C47 !important;
  color: #ffffff !important;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  border-bottom: 2px solid #0d2238;
}

.text-theme {
  color: #112C47 !important;
}

.theme-matrix-header th {
  background-color: #112C47 !important;
  color: #ffffff !important;
  border-color: #112C47 !important;
  font-weight: 600;
  letter-spacing: 0.5px;
  padding: 10px 8px;
}

.bg-service-row {
  background-color: #f8fafc !important;
}

.theme-grand-total-box {
  background-color: #f1f5f9 !important;
  border-top: 2px solid #112C47 !important;
  border-bottom: 2px solid #112C47 !important;
}

.btn-xs {
  font-size: 0.75rem;
  padding: 0.15rem 0.4rem;
}

/* Global dropdown menu styling to prevent card clipping */
:global(.vs__dropdown-menu) {
  z-index: 999999 !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18) !important;
  border-radius: 6px !important;
  border: 1px solid #cbd5e1 !important;
  max-height: 280px !important;
  background-color: #ffffff !important;
  overflow-y: auto !important;
}

:global(.vs__dropdown-option) {
  padding: 8px 12px !important;
  font-size: 13px !important;
  color: #1e293b !important;
  white-space: normal !important;
}

:global(.vs__dropdown-option--highlight) {
  background-color: #112C47 !important;
  color: #ffffff !important;
}
</style>
