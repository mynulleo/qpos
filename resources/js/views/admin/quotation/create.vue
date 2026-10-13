<template>
  <create-form @onSubmit="submit">
    <!-- 1. Quotation Header & Client Information -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border-0 bg-white theme-card">
        <div class="card-header theme-card-header py-2 d-flex justify-content-between align-items-center">
          <span class="fw-bold text-white d-flex align-items-center">
            <i class="fas fa-file-invoice-dollar me-2 text-warning fs-5"></i>
            <span>{{ $t('Quotation Information') }}</span>
          </span>
          <span class="badge bg-light text-dark px-3 py-1 font-monospace fw-bold" v-if="data.quotation_no">
            <i class="fas fa-hashtag me-1 text-primary"></i> {{ data.quotation_no }}
          </span>
        </div>
        <div class="card-body p-2 px-3">
          <div class="row g-2">
            <!-- Row 1: Quotation Metadata -->
            <!-- Quotation No -->
            <Input v-model="data.quotation_no" field="data.quotation_no" col="3" title="Quotation No"
              placeholder="Auto Generated" :req="false" :readonly="true" />

            <!-- Quotation Date -->
            <date-picker id="quotation_date" v-model="data.quotation_date" field="data.quotation_date"
              title="Quotation Date" placeholder="Select Date" col="3" :req="true" />

            <!-- Validity Date -->
            <date-picker id="validity_date" v-model="data.validity_date" field="data.validity_date"
              title="Valid Until" placeholder="Select Validity" col="3" :req="false" />

            <!-- Status -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme mb-1">
                <i class="fas fa-flag me-1"></i>{{ $t('Status') }}</label>
              <select class="form-select form-select-sm shadow-sm" v-model="data.status">
                <option value="draft">{{ $t('Draft') }}</option>
                <option value="sent">{{ $t('Sent to Client') }}</option>
                <option value="accepted">{{ $t('Accepted') }}</option>
                <option value="declined">{{ $t('Declined') }}</option>
                <option value="converted">{{ $t('Converted to Invoice') }}</option>
                <option value="expired">{{ $t('Expired') }}</option>
              </select>
            </div>

            <!-- Row 2: Customer & Contacts -->
            <!-- Client Selector -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme mb-1 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-tie me-1"></i> {{ $t('Customer / Client') }}:</span>
                <span class="text-muted small" v-if="selectedClientInfo">📱 {{ selectedClientInfo.mobile }}</span>
              </label>
              <v-select
                v-model="data.client_id"
                :options="clients"
                label="org_name"
                :reduce="(obj) => obj.id"
                :placeholder="$t('-- Select Client --')"
                @option:selected="onClientSelect"
                class="shadow-sm vs-compact"
              >
                <template #option="option">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>{{ option.org_name || option.name }}</strong>
                      <span class="text-muted small d-block" v-if="option.org_name && option.name">{{ $t('Attn:') }} {{ option.name }}</span>
                    </div>
                    <div class="text-end">
                      <small class="badge bg-secondary-subtle text-secondary font-monospace d-block" v-if="option.customer_type">{{ option.customer_type }}</small>
                      <small class="text-primary font-monospace" v-if="option.mobile">📱 {{ option.mobile }}</small>
                    </div>
                  </div>
                </template>
              </v-select>
            </div>

            <!-- Customer Type -->
            <div class="col-md-2">
              <label class="form-label fw-bold small text-theme mb-1 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users-cog me-1 text-primary"></i> {{ $t('Customer Type') }}:</span>
                <span class="badge" :class="data.customer_type === 'wholesale' ? 'bg-warning text-dark' : 'bg-primary text-white'" style="font-size: 10px;">
                  {{ data.customer_type === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                </span>
              </label>
              <select class="form-select form-select-sm shadow-sm fw-semibold" v-model="data.customer_type" @change="onCustomerTypeChange">
                <option value="retail">{{ $t('Retail') }}</option>
                <option value="wholesale">{{ $t('Wholesale') }}</option>
              </select>
            </div>

            <!-- Client / Org Name -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme mb-1">
                <i class="fas fa-building me-1"></i> {{ $t('Client / Org Name') }}
              </label>
              <input type="text" class="form-control form-control-sm shadow-sm" v-model="data.client_name" :placeholder="$t('Client Name...')" />
            </div>

            <!-- Client Phone -->
            <div class="col-md-2">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-phone-alt me-1"></i> {{ $t('Phone No') }}
              </label>
              <input type="text" class="form-control form-control-sm" v-model="data.client_phone" placeholder="017xxxxxxxx" />
            </div>

            <!-- Client Email -->
            <div class="col-md-2">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-envelope me-1"></i> {{ $t('Email Address') }}
              </label>
              <input type="email" class="form-control form-control-sm" v-model="data.client_email" placeholder="client@example.com" />
            </div>

            <!-- Row 3: Subject, Reference, Address -->
            <!-- Quotation Subject -->
            <div class="col-md-5">
              <label class="form-label fw-bold small text-theme mb-1">
                <i class="fas fa-heading me-1"></i>{{ $t('Quotation Subject') }}</label>
              <input type="text" class="form-control form-control-sm shadow-sm" v-model="data.subject"
                :placeholder="$t('Commercial Quotation for Products & Services')" />
            </div>

            <!-- Reference / RFQ No -->
            <div class="col-md-3">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-bookmark me-1"></i> {{ $t('Reference / RFQ No') }}
              </label>
              <input type="text" class="form-control form-control-sm" v-model="data.reference_no" placeholder="e.g. RFQ-2026-88" />
            </div>

            <!-- Billing / Delivery Address -->
            <div class="col-md-4">
              <label class="form-label fw-semibold small text-theme mb-1">
                <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $t('Delivery / Billing Address') }}
              </label>
              <input type="text" class="form-control form-control-sm" v-model="data.client_address" :placeholder="$t('Address...')" />
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
            <span>{{ $t('Add Items to Quotation') }}</span>
          </span>
          <div class="d-flex align-items-center gap-2">
            <button
              type="button"
              class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm fw-semibold px-3"
              @click="addCustomItem('service')"
            >
              <i class="fas fa-tools"></i>{{ $t('+ Add Service') }}</button>
            <button
              type="button"
              class="btn btn-sm btn-warning text-dark d-inline-flex align-items-center gap-1 shadow-sm fw-bold px-3"
              @click="addCustomItem('custom')"
            >
              <i class="fas fa-plus-circle"></i> {{ $t('+ Add Custom Item / Software') }}
            </button>
          </div>
        </div>
        <div class="card-body p-3 bg-light-subtle">
          <div class="row g-2 align-items-end">
            <!-- Category Filter -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-theme d-flex justify-content-between align-items-center">
                <span><i class="fas fa-layer-group me-1 text-primary"></i> 1. {{ $t('Filter Category:') }}</span>
                <span class="text-muted small" v-if="categories.length">({{ $bnNum(categories.length) }} {{ $t('Categories') }})</span>
              </label>
              <select class="form-select shadow-sm" v-model="selectedCategoryId" @change="onCategoryChange">
                <option :value="null">{{ $t('-- All Categories --') }}</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.title }}
                </option>
              </select>
            </div>

            <!-- Barcode Scanner -->
            <div class="col-md-3">
              <label class="form-label fw-bold small text-theme">
                <i class="fas fa-barcode me-1 text-danger"></i> 2. {{ $t('Barcode Scan:') }}
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
                  :placeholder="$t('Scan barcode & Enter...')"
                  autocomplete="off"
                />
              </div>
            </div>

            <!-- Product Dropdown Search -->
            <div class="col-md-5">
              <label class="form-label fw-bold small text-theme">
                <i class="fas fa-box-open me-1 text-primary"></i> 3. {{ $t('Search & Add Catalog Item:') }}
              </label>
              <v-select
                v-model="selectedItem"
                :options="itemList"
                label="title"
                :placeholder="$t('Type product name or barcode...')"
                @option:selected="onItemSelect"
                @search="onSearchItems"
                class="shadow-sm"
              >
                <template #no-options="{ search, searching }">
                  <template v-if="searching">
                    <span class="text-muted small">{{ $t('No items matching') }} "<em>{{ search }}</em>"</span>
                  </template>
                  <em v-else class="text-muted small">{{ $t('Type product name or barcode...') }}</em>
                </template>
                <template #option="option">
                  <div class="d-flex justify-content-between align-items-center py-1">
                    <div>
                      <strong>{{ option.title }}</strong>
                      <div class="small text-muted">
                        <span v-if="option.barcode">{{ $t('Barcode') }}: <code>{{ option.barcode }}</code></span>
                        <span v-if="option.unit" class="ms-2">{{ $t('Unit') }}: {{ option.unit.title }}</span>
                      </div>
                    </div>
                    <div class="text-end font-monospace">
                      <span class="badge bg-success text-white">{{ $t('Tk.') }} {{ $bnNum(formatCurrencyNumber(getItemPriceByCustomerType(option))) }}</span>
                      <small class="d-block text-muted" style="font-size: 10px;">
                        {{ data.customer_type === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                      </small>
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
            <span>{{ $t('Quotation Item Matrix') }}</span>
          </span>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark font-monospace px-3 py-1 fw-bold">
              {{ $t('Total Lines:') }} {{ $bnNum(details.length) }}
            </span>
            <button
              type="button"
              class="btn btn-xs btn-outline-light py-1 px-2"
              v-if="details.length > 0"
              @click="clearAllDetails"
            >
              <i class="fas fa-trash me-1"></i> {{ $t('Clear All') }}
            </button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 matrix-table">
              <thead class="theme-matrix-header text-center small text-uppercase">
                <tr>
                  <th style="width: 4%;">{{ $t('#') }}</th>
                  <th style="width: 10%;">{{ $t('Type') }}</th>
                  <th style="width: 32%;">{{ $t('Item / Service Name & Specification') }}</th>
                  <th style="width: 10%;">{{ $t('Unit') }}</th>
                  <th style="width: 10%;">{{ $t('Qty') }}</th>
                  <th style="width: 14%;">{{ $t('Unit Price') }}</th>
                  <th style="width: 15%;">{{ $t('Total') }}</th>
                  <th style="width: 5%;">{{ $t('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="details.length > 0">
                  <tr v-for="(row, index) in details" :key="index" :class="{ 'bg-service-row': row.item_type !== 'product' }">
                    <!-- SL -->
                    <td class="text-center font-monospace text-muted fw-bold">{{ $bnNum(index + 1) }}</td>

                    <!-- Type Badge -->
                    <td class="text-center">
                      <span class="badge bg-primary" v-if="row.item_type === 'product'">
                        <i class="fas fa-box me-1"></i> {{ $t('Product') }}
                      </span>
                      <span class="badge bg-success" v-else-if="row.item_type === 'service'">
                        <i class="fas fa-tools me-1"></i> {{ $t('Service') }}
                      </span>
                      <span class="badge bg-info text-dark" v-else>
                        <i class="fas fa-cube me-1"></i> {{ $t('Custom') }}
                      </span>
                    </td>

                    <!-- Item Name & Description -->
                    <td>
                      <input
                        type="text"
                        class="form-control form-control-sm fw-bold mb-1"
                        v-model="row.item_name"
                        :placeholder="$t('Item or Service Name...')"
                      />
                      <textarea
                        class="form-control form-control-sm text-muted small"
                        rows="2"
                        v-model="row.description"
                        :placeholder="$t('Technical specifications / details description...')"
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
                        <span class="input-group-text font-monospace">{{ $t('Tk.') }}</span>
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
                      {{ $t('Tk.') }} {{ $bnNum(formatCurrencyNumber(row.total_price)) }}
                    </td>

                    <!-- Action -->
                    <td class="text-center">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger p-1"
                        @click="removeRow(index)"
                        :title="$t('Remove Item')"
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
                      <h6 class="fw-bold text-secondary">{{ $t('No Items Added Yet') }}</h6>
                      <p class="small text-muted mb-0">
                        {{ $t('Please select an item from catalog above or click') }} <strong>{{ $t('+ Add Custom Item / Service') }}</strong>.
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
                <span>{{ $t('Commercial Terms & Conditions') }}</span>
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <!-- Payment Terms -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme d-flex justify-content-between">
                    <span><i class="fas fa-credit-card me-1"></i> {{ $t('Payment Terms') }}:</span>
                    <span class="small text-muted">
                      <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.payment_terms = '50% Advance along with Work Order, remaining 50% upon delivery/completion.'">{{ $t("Preset 1") }}</button> |
                      <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.payment_terms = '100% Cash/Cheque on Delivery.'">{{ $t("Preset 2") }}</button>
                    </span>
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.payment_terms" :placeholder="$t('e.g. 50% Advance, 50% on Delivery...')" />
                </div>

                <!-- Delivery Terms -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme d-flex justify-content-between">
                    <span><i class="fas fa-truck me-1"></i> {{ $t('Delivery Terms') }}:</span>
                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.delivery_terms = 'Within 3-5 working days after receiving confirmed work order.'">{{ $t("Preset") }}</button>
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.delivery_terms" :placeholder="$t('e.g. Within 3-5 working days...')" />
                </div>

                <!-- Warranty Terms -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-theme d-flex justify-content-between">
                    <span><i class="fas fa-shield-alt me-1"></i> {{ $t('Warranty Terms') }}:</span>
                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" @click="data.warranty_terms = '1 Year official warranty & free technical support.'">{{ $t("Preset") }}</button>
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.warranty_terms" :placeholder="$t('e.g. 1 Year service warranty...')" />
                </div>

                <!-- Terms & Conditions from terms_conditions table with Checkbox & Editable Input -->
                <div class="col-12">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-theme mb-0">
                      <i class="fas fa-file-contract me-1 text-primary"></i> {{ $t('Terms & Conditions') }}
                      <span class="badge bg-primary-subtle text-primary border ms-1 font-monospace" v-if="termsList.length">
                        {{ termsList.filter(t => t.is_checked).length }} / {{ termsList.length }} {{ $t('Active') }}
                      </span>
                    </label>
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-xs btn-outline-secondary py-0" @click="toggleSelectAllTerms(true)" :title="$t('Select All')">
                        <i class="fas fa-check-double me-1"></i>{{ $t('All') }}
                      </button>
                      <button type="button" class="btn btn-xs btn-outline-secondary py-0" @click="toggleSelectAllTerms(false)" :title="$t('Deselect All')">
                        <i class="fas fa-times me-1"></i>{{ $t('None') }}
                      </button>
                      <button type="button" class="btn btn-xs btn-outline-primary py-0" @click="addCustomTerm" :title="$t('Add New Condition')">
                        <i class="fas fa-plus me-1"></i>{{ $t('Add Condition') }}
                      </button>
                    </div>
                  </div>

                  <!-- Checklist Container -->
                  <div class="terms-conditions-container border rounded p-2 bg-light-subtle" style="max-height: 250px; overflow-y: auto;">
                    <div v-if="termsList.length === 0" class="text-center py-3 text-muted small">
                      <i class="fas fa-spinner fa-spin me-1" v-if="isLoadingTerms"></i>
                      <span>{{ isLoadingTerms ? $t('Loading Terms & Conditions...') : $t('No conditions found for Quotation module.') }}</span>
                      <button type="button" class="btn btn-xs btn-link d-block mx-auto mt-1" @click="addCustomTerm">{{ $t('+ Add Custom Condition') }}</button>
                    </div>

                    <div
                      v-for="(term, tIdx) in termsList"
                      :key="tIdx"
                      class="d-flex align-items-center gap-2 mb-2 p-1 rounded bg-white border term-item-row"
                      :class="{ 'border-primary shadow-sm': term.is_checked, 'opacity-75': !term.is_checked }"
                    >
                      <!-- Checkbox -->
                      <div class="form-check form-check-inline m-0 ps-3">
                        <input
                          type="checkbox"
                          class="form-check-input"
                          :id="'term_chk_' + tIdx"
                          v-model="term.is_checked"
                          style="cursor: pointer; width: 1.15em; height: 1.15em;"
                        />
                      </div>

                      <!-- Index / Serial Number Badge -->
                      <span class="badge font-monospace text-muted small" style="min-width: 28px;">
                        {{ tIdx + 1 }}.
                      </span>

                      <!-- Condition Input Box -->
                      <input
                        type="text"
                        class="form-control form-control-sm border-0 flex-grow-1"
                        :class="{ 'fw-semibold text-dark': term.is_checked, 'text-muted': !term.is_checked }"
                        v-model="term.condition_text"
                        :placeholder="$t('Type condition statement...')"
                      />

                      <!-- Default Tag if applicable -->
                      <span class="badge bg-secondary-subtle text-secondary small px-2 py-1" v-if="term.is_default" style="font-size: 10px;">
                        {{ $t('Default') }}
                      </span>

                      <!-- Remove Row Button -->
                      <button
                        type="button"
                        class="btn btn-link text-danger btn-xs p-1 me-1"
                        @click="removeTerm(tIdx)"
                        :title="$t('Remove condition')"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="small text-muted mt-1 fst-italic" style="font-size: 11px;">
                    <i class="fas fa-info-circle me-1 text-info"></i> {{ $t('Note: Only checked conditions will be saved with this quotation.') }}
                  </div>
                </div>

                <!-- Note -->
                <div class="col-12">
                  <label class="form-label small fw-semibold text-theme">
                    <i class="fas fa-sticky-note me-1"></i> {{ $t('Special Client Note') }}:
                  </label>
                  <input type="text" class="form-control form-control-sm" v-model="data.note" :placeholder="$t('Note shown on quotation...')" />
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
                <span>{{ $t('Quotation Financial Summary') }}</span>
              </span>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 align-middle">
                <tbody>
                  <!-- Sub Total -->
                  <tr class="border-bottom">
                    <td class="fw-bold text-secondary">{{ $t('Subtotal') }}:</td>
                    <td class="text-end font-monospace fw-bold text-dark fs-6">
                      {{ $t('Tk.') }} {{ $bnNum(formatCurrencyNumber(calculatedSubTotal)) }}
                    </td>
                  </tr>

                  <!-- Discount -->
                  <tr class="border-bottom">
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <span class="fw-bold text-secondary">{{ $t('Discount') }}:</span>
                        <select class="form-select form-select-sm py-0 px-1" style="width: 85px;" v-model="data.discount_type" @change="recalculateSummary">
                          <option value="fixed">{{ $t('Fixed') }} ৳</option>
                          <option value="percentage">{{ $t('Percent') }} %</option>
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
                        - {{ $t('Tk.') }} {{ $bnNum(formatCurrencyNumber(calculatedDiscountAmount)) }}
                      </small>
                    </td>
                  </tr>

                  <!-- Tax / VAT -->
                  <tr class="border-bottom">
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <span class="fw-bold text-secondary">{{ $t('VAT / Tax') }} (%):</span>
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
                        + {{ $t('Tk.') }} {{ $bnNum(formatCurrencyNumber(calculatedTaxAmount)) }}
                      </small>
                    </td>
                  </tr>

                  <!-- Shipping / Delivery Charge -->
                  <tr class="border-bottom">
                    <td class="fw-bold text-secondary">{{ $t('Shipping Cost') }}:</td>
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
                    <td class="fw-bold fs-5 py-2" style="color: #112C47;">{{ $t('Grand Total') }}:</td>
                    <td class="text-end font-monospace fw-bold fs-4 py-2" style="color: #112C47;">
                      {{ $t('Tk.') }} {{ $bnNum(formatCurrencyNumber(calculatedGrandTotal)) }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- 🌟 IN WORDS LIVE SECTION (Requirement 4) -->
              <div class="mt-3 p-3 bg-light border rounded-3" style="border-left: 4px solid #112C47 !important;">
                <div class="d-flex align-items-center gap-1 small fw-bold mb-1" style="color: #112C47;">
                  <i class="fas fa-money-bill-wave text-success"></i>
                  <span>{{ $t('AMOUNT IN WORDS:') }}</span>
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
        customer_type: "retail",
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
        terms_conditions: [],
        note: "",
        remarks: "",
      },
      details: [],
      clients: [],
      categories: [],
      itemList: [],
      termsList: [],
      isLoadingTerms: false,
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

  provide() {
    return {
      validate: this.validation,
    };
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

    getItemPriceByCustomerType(item, customerType = this.data.customer_type) {
      if (!item) return 0;
      const wholesale = Number(item.wholesale_price || 0);
      const retail = Number(item.retail_price || item.selling_price || 0);
      const selling = Number(item.selling_price || 0);

      if (customerType === "wholesale") {
        if (wholesale > 0) return wholesale;
        if (retail > 0) return retail;
        return selling;
      } else {
        // 'retail' is default
        if (retail > 0) return retail;
        if (wholesale > 0) return wholesale;
        return selling;
      }
    },

    onCustomerTypeChange() {
      let updatedCount = 0;
      this.details.forEach((row) => {
        if (row.item_type === "product") {
          const newPrice = this.getItemPriceByCustomerType(row);
          if (newPrice > 0 && row.unit_price !== newPrice) {
            row.unit_price = newPrice;
            this.recalculateRow(row);
            updatedCount++;
          }
        }
      });
      this.recalculateSummary();
      if (updatedCount > 0) {
        this.$toast(
          `Customer type set to ${this.data.customer_type.toUpperCase()}. Updated unit prices for ${updatedCount} item(s).`,
          "info"
        );
      }
    },

    onClientSelect(client) {
      if (!client) return;
      this.selectedClientInfo = client;
      this.data.client_name = client.org_name || client.name || "";
      this.data.client_phone = client.mobile || client.phone || "";
      this.data.client_email = client.email || "";
      this.data.client_address = client.address || "";

      if (client.customer_type) {
        const prevType = this.data.customer_type;
        this.data.customer_type = client.customer_type.toLowerCase();
        if (prevType !== this.data.customer_type) {
          this.onCustomerTypeChange();
        }
      }
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
        const unitPrice = this.getItemPriceByCustomerType(item);
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
          retail_price: Number(item.retail_price || item.selling_price || 0),
          wholesale_price: Number(item.wholesale_price || 0),
          selling_price: Number(item.selling_price || 0),
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

    fetchTermsConditions(existingSavedTerms = null) {
      this.isLoadingTerms = true;
      axios
        .get("termsCondition/by-module/Quotation")
        .then((res) => {
          const dbTerms = res.data || [];

          if (existingSavedTerms && Array.isArray(existingSavedTerms) && existingSavedTerms.length > 0) {
            // Reconcile with saved quotation terms
            const mappedDbTerms = dbTerms.map((item) => {
              const isMatched = existingSavedTerms.some(
                (saved) => typeof saved === "string" && saved.trim().toLowerCase() === item.condition_text.trim().toLowerCase()
              );
              return {
                id: item.id,
                condition_text: item.condition_text,
                is_checked: isMatched,
                is_default: Boolean(item.is_default),
              };
            });

            // Add any saved terms that weren't in db terms
            const additionalTerms = existingSavedTerms
              .filter((saved) => typeof saved === "string" && saved.trim() !== "" && !dbTerms.some((d) => d.condition_text.trim().toLowerCase() === saved.trim().toLowerCase()))
              .map((saved) => ({
                id: null,
                condition_text: saved.trim(),
                is_checked: true,
                is_default: false,
              }));

            this.termsList = [...mappedDbTerms, ...additionalTerms];
          } else {
            // Create mode: Pre-check default conditions
            this.termsList = dbTerms.map((item) => ({
              id: item.id,
              condition_text: item.condition_text || "",
              is_checked: Boolean(item.is_default),
              is_default: Boolean(item.is_default),
            }));
          }
        })
        .catch((err) => {
          console.error("Error fetching terms & conditions:", err);
        })
        .finally(() => {
          this.isLoadingTerms = false;
        });
    },

    toggleSelectAllTerms(status) {
      this.termsList.forEach((t) => {
        t.is_checked = status;
      });
    },

    addCustomTerm() {
      this.termsList.push({
        id: null,
        condition_text: "",
        is_checked: true,
        is_default: false,
      });
    },

    removeTerm(index) {
      this.termsList.splice(index, 1);
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
              customer_type: q.customer_type || (q.client ? q.client.customer_type : "retail") || "retail",
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
              terms_conditions: q.terms_conditions || [],
              note: q.note || "",
              remarks: q.remarks || "",
            };

            let savedTerms = [];
            if (Array.isArray(q.terms_conditions)) {
              savedTerms = q.terms_conditions;
            } else if (typeof q.terms_conditions === "string") {
              try {
                const parsed = JSON.parse(q.terms_conditions);
                if (Array.isArray(parsed)) savedTerms = parsed;
              } catch (e) {
                savedTerms = q.terms_conditions.split("\n").map((s) => s.trim()).filter(Boolean);
              }
            }
            this.fetchTermsConditions(savedTerms);

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
                retail_price: Number((d.item ? d.item.retail_price : 0) || d.unit_price || 0),
                wholesale_price: Number((d.item ? d.item.wholesale_price : 0) || 0),
                selling_price: Number((d.item ? d.item.selling_price : 0) || d.unit_price || 0),
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
      this.$validate().then((success) => {
        const error = this.validation ? this.validation.countErrors() : 0;
        if (error > 0) {
          this.$toast("Please fill in the required fields!", "warning");
          return false;
        }

        if (this.details.length === 0) {
          this.$toast("Please add at least one item or service to the quotation!", "error");
          return;
        }

        this.recalculateSummary();

        const checkedTerms = this.termsList
          .filter((t) => t.is_checked && t.condition_text && t.condition_text.trim() !== "")
          .map((t) => t.condition_text.trim());

        const payload = {
          ...this.data,
          terms_conditions: checkedTerms,
          quotation_details: this.details,
        };

        if (this.$route.params.id) {
          this.update(this.model, payload, this.$route.params.id);
        } else {
          this.store(this.model, payload);
        }
      });
    },
  },

  created() {
    this.getClients();
    this.getCategories();
    this.fetchItems();

    if (this.$route.params.id) {
      this.page_title = "Edit Quotation";
      this.loadQuotationData(this.$route.params.id);
    } else {
      this.page_title = "Create Quotation";
      this.fetchTermsConditions();
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
