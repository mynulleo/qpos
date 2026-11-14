<template>
  <div class="container-fluid p-3">
    <!-- Header Card -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-2.5 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <router-link to="/invoice" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-arrow-left me-1"></i> {{ $t('Back to Invoices') }}
            </router-link>
            <h5 class="mb-0 fw-bold text-dark">
              <i class="fas fa-edit text-warning me-2"></i>{{ $t('Edit Invoice') }}
              <span class="badge bg-light text-dark font-monospace fw-normal" v-if="form.invoice_no">#{{ form.invoice_no }}</span>
              <span class="badge" :class="statusBadgeClass" style="font-size: 12px;">{{ statusText }}</span>
            </h5>
          </div>

          <div class="d-flex align-items-center gap-2">
            <router-link
              v-if="form.id"
              :to="{ name: 'invoice.show', params: { id: form.id } }"
              class="btn btn-sm btn-outline-primary"
            >
              <i class="fas fa-eye me-1"></i> {{ $t('View Details') }}
            </router-link>

            <button
              type="button"
              class="btn btn-sm btn-warning fw-bold px-3 shadow-sm d-flex align-items-center gap-1.5"
              @click="saveInvoice"
              :disabled="saving || loading || form.items.length === 0"
            >
              <span v-if="saving" class="spinner-border spinner-border-sm" role="status"></span>
              <i v-else class="fas fa-save"></i>
              <span>{{ saving ? $t('Updating...') : $t('Update Invoice (সংরক্ষণ করুন)') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="card border-0 shadow-sm p-5 text-center">
      <div class="spinner-border text-primary mx-auto" role="status"></div>
      <div class="mt-2 text-muted small">{{ $t('Loading invoice details...') }}</div>
    </div>

    <!-- Main Edit Form -->
    <div v-else>
      <!-- Top Row: Customer & Date -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
          <div class="row g-3 align-items-center">
            <div class="col-md-5 col-sm-12">
              <label class="form-label small fw-bold text-dark mb-1">
                <i class="fas fa-user text-primary me-1"></i>{{ $t('Customer / Client') }}
              </label>
              <select class="form-select form-select-sm" v-model="form.client_id">
                <option value="">{{ $t('Walk-in Customer (ডিফল্ট সাধারণ ক্রেতা)') }}</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">
                  {{ c.name }} {{ c.mobile ? '(' + c.mobile + ')' : '' }} {{ c.org_name ? '- ' + c.org_name : '' }}
                </option>
              </select>
            </div>

            <div class="col-md-4 col-sm-6">
              <label class="form-label small fw-bold text-dark mb-1">
                <i class="far fa-calendar-alt text-primary me-1"></i>{{ $t('Invoice Date') }}
              </label>
              <input type="date" class="form-control form-control-sm" v-model="form.invoice_date">
            </div>

            <div class="col-md-3 col-sm-6 text-md-end">
              <label class="form-label small fw-bold text-muted mb-1 d-block">{{ $t('Issued By') }}</label>
              <span class="badge bg-secondary font-monospace py-1 px-2">
                <i class="fas fa-user-tie me-1"></i>{{ form.creator?.name || 'Cashier' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Product Search Card -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
          <label class="form-label small fw-bold text-dark mb-1">
            <i class="fas fa-barcode text-success me-1"></i>{{ $t('Add Product to Invoice (Search title, scan barcode, or SKU)') }}
          </label>
          <div class="position-relative">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                :placeholder="$t('Type product title, barcode, or SKU to add... (Press Enter)')"
                v-model="itemSearchQuery"
                @input="onItemSearchInput"
                @keydown.enter.prevent="onItemSearchEnter"
                @keydown.esc="clearItemSearch"
                autocomplete="off"
              >
              <button
                type="button"
                class="btn btn-outline-secondary"
                v-if="itemSearchQuery"
                @click="clearItemSearch"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>

            <!-- 🔄 Searching Indicator Dropdown -->
            <div
              v-if="searchingItem"
              class="position-absolute start-0 end-0 bg-white border rounded shadow p-2 text-center text-muted small mt-1"
              style="z-index: 1090;"
            >
              <i class="fas fa-spinner fa-spin text-primary me-1"></i> {{ $t('Searching products...') }}
            </div>

            <!-- 📦 Search Results Dropdown -->
            <div
              v-else-if="searchResults.length > 0"
              class="position-absolute start-0 end-0 bg-white border rounded shadow-lg mt-1 p-1"
              style="z-index: 1090; max-height: 280px; overflow-y: auto;"
            >
              <div
                v-for="item in searchResults"
                :key="item.id"
                class="search-result-item p-2 border-bottom d-flex align-items-center justify-content-between gap-2 cursor-pointer hover-bg-light"
                @click="selectProductToAdd(item)"
              >
                <div class="d-flex align-items-center gap-2">
                  <div class="bg-light text-primary rounded p-1.5 text-center d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="fas fa-box" style="font-size: 13px;"></i>
                  </div>
                  <div>
                    <div class="fw-bold text-dark small">{{ item.title }}</div>
                    <div class="small text-muted font-monospace" style="font-size: 11px;">
                      <span>Barcode: {{ item.barcode || 'N/A' }}</span>
                      <span class="ms-2" v-if="item.category">| {{ item.category.title }}</span>
                    </div>
                  </div>
                </div>

                <div class="text-end d-flex align-items-center gap-2">
                  <div>
                    <div class="fw-bold text-success font-monospace small">Tk. {{ formatPrice(getItemPrice(item)) }}</div>
                    <span class="badge bg-info text-dark font-monospace" style="font-size: 10px;">
                      Stock: {{ getItemTotalStock(item) }}
                    </span>
                  </div>
                  <button type="button" class="btn btn-xs btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> {{ $t('Add') }}
                  </button>
                </div>
              </div>
            </div>

            <!-- ⚠️ No Products Found Notice -->
            <div
              v-else-if="itemSearchQuery && !searchingItem && searchResults.length === 0 && searchAttempted"
              class="position-absolute start-0 end-0 bg-white border rounded shadow p-2 text-center text-muted small mt-1"
              style="z-index: 1090;"
            >
              <i class="fas fa-exclamation-circle text-warning me-1"></i> {{ $t('No products found matching') }} "<strong>{{ itemSearchQuery }}</strong>"
            </div>
          </div>

          <!-- In-line Variant Selector -->
          <div v-if="pendingVariantItem" class="alert alert-info border border-info mt-2 p-2.5 rounded d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <strong class="text-dark">{{ pendingVariantItem.title }}</strong> - {{ $t('Select Variant to Add') }}:
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <button
                v-for="(v, vIdx) in pendingVariantItem.variants"
                :key="vIdx"
                type="button"
                class="btn btn-xs btn-outline-dark font-monospace d-flex align-items-center gap-1"
                @click="confirmAddVariant(pendingVariantItem, v)"
              >
                <span>{{ v.color_title || 'No Color' }} / {{ v.size_title || 'No Size' }}</span>
                <span class="badge bg-secondary ms-1">Stock: {{ v.stock }}</span>
                <strong class="text-primary ms-1">Tk. {{ formatPrice(v.price) }}</strong>
              </button>
              <button type="button" class="btn btn-xs btn-secondary" @click="pendingVariantItem = null">{{ $t('Cancel') }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Line Items Table Card -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark small">
            <i class="fas fa-shopping-cart text-primary me-1"></i>{{ $t('Invoice Items') }} ({{ form.items.length }})
          </span>
          <span class="small text-muted font-monospace">{{ $t('Rates and quantities can be adjusted below') }}</span>
        </div>
        <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-hover table-sm align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light sticky-top" style="z-index: 1;">
              <tr>
                <th width="4%" class="text-center">{{ $t('#') }}</th>
                <th width="32%">{{ $t('Product Title & Barcode') }}</th>
                <th width="16%">{{ $t('Variant') }}</th>
                <th width="10%" class="text-center">{{ $t('Stock') }}</th>
                <th width="13%" class="text-end">{{ $t('Unit Rate (Tk)') }}</th>
                <th width="12%" class="text-center">{{ $t('Qty') }}</th>
                <th width="13%" class="text-end">{{ $t('Total (Tk)') }}</th>
                <th width="4%" class="text-center"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(it, idx) in form.items" :key="idx">
                <td class="text-center text-muted small">{{ idx + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ it.title }}</div>
                  <div class="small text-muted font-monospace" style="font-size: 11px;">
                    <span v-if="it.barcode"><i class="fas fa-barcode me-1"></i>{{ it.barcode }}</span>
                    <span v-if="it.serial_no" class="ms-2 badge bg-light text-dark border">SN: {{ it.serial_no }}</span>
                  </div>
                </td>
                <td>
                  <div v-if="it.color_title || it.size_title" class="d-flex flex-wrap gap-1">
                    <span v-if="it.color_title" class="badge bg-secondary bg-opacity-10 text-dark border font-monospace" style="font-size: 10px;">
                      {{ it.color_title }}
                    </span>
                    <span v-if="it.size_title" class="badge bg-secondary bg-opacity-10 text-dark border font-monospace" style="font-size: 10px;">
                      {{ it.size_title }}
                    </span>
                  </div>
                  <div v-else class="text-muted small">-</div>
                </td>
                <td class="text-center">
                  <span class="badge font-monospace" :class="getStockBadgeClass(it.present_stock)">
                    {{ it.present_stock !== null && it.present_stock !== undefined ? it.present_stock : 'N/A' }}
                  </span>
                </td>
                <td class="text-end">
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace py-0 px-1"
                    style="height: 28px; width: 105px; display: inline-block;"
                    v-model.number="it.amount"
                    @input="recalculateTotals"
                  >
                </td>
                <td class="text-center">
                  <div class="input-group input-group-sm d-inline-flex align-items-center" style="width: 105px;">
                    <button
                      type="button"
                      class="btn btn-outline-secondary px-2 py-0"
                      style="height: 28px;"
                      @click="changeItemQty(it, -1)"
                      :disabled="it.qty <= 1"
                    >-</button>
                    <input
                      type="number"
                      step="1"
                      min="1"
                      class="form-control text-center font-monospace py-0 px-1"
                      style="height: 28px;"
                      v-model.number="it.qty"
                      @input="recalculateTotals"
                    >
                    <button
                      type="button"
                      class="btn btn-outline-secondary px-2 py-0"
                      style="height: 28px;"
                      @click="changeItemQty(it, 1)"
                    >+</button>
                  </div>
                </td>
                <td class="text-end font-monospace fw-bold text-dark">
                  {{ formatPrice(it.qty * it.amount) }}
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    class="btn btn-xs btn-outline-danger border-0 p-1"
                    @click="removeItem(idx)"
                    :title="$t('Remove Item')"
                  >
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </td>
              </tr>

              <tr v-if="form.items.length === 0">
                <td colspan="8" class="text-center py-4 text-muted">
                  <i class="fas fa-shopping-basket fa-2x mb-1 text-secondary opacity-50"></i>
                  <p class="mb-0 small text-danger fw-bold">{{ $t('No items in this invoice. Please search and add at least one item.') }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Financial Calculations Strip & Remarks -->
      <div class="row g-3">
        <!-- Left: Notes -->
        <div class="col-md-6 col-sm-12">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <label class="form-label small fw-bold text-dark mb-1">
                <i class="far fa-sticky-note text-warning me-1"></i>{{ $t('Terms & Notes / Remarks') }}
              </label>
              <textarea
                class="form-control form-control-sm"
                rows="3"
                :placeholder="$t('Add any notes or special conditions for this invoice...')"
                v-model="form.terms_note"
              ></textarea>
              <div class="small text-muted mt-2">
                <i class="fas fa-shield-alt text-success me-1"></i>
                <span>{{ $t('Stock changes will automatically synchronize with your live inventory upon saving.') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Calculations Summary -->
        <div class="col-md-6 col-sm-12">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                <span class="text-muted fw-semibold">{{ $t('Original Amount (Subtotal):') }}</span>
                <span class="font-monospace fw-bold text-dark">Tk. {{ formatPrice(form.original_amount) }}</span>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                <span class="text-muted fw-semibold">{{ $t('Discount (Tk):') }}</span>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control form-control-sm text-end font-monospace py-0 px-2"
                  style="width: 110px; height: 26px;"
                  v-model.number="form.discount"
                  @input="recalculateTotals"
                >
              </div>

              <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                <div class="d-flex align-items-center gap-1">
                  <span class="text-muted fw-semibold">{{ $t('VAT (%):') }}</span>
                  <input
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    class="form-control form-control-sm text-center font-monospace py-0 px-1"
                    style="width: 55px; height: 26px;"
                    v-model.number="form.vat_percent"
                    @input="recalculateTotals"
                  >
                </div>
                <span class="font-monospace text-dark">+ Tk. {{ formatPrice(form.vat) }}</span>
              </div>

              <div class="border-top border-bottom py-1.5 my-1.5 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark fs-6">{{ $t('Net Total Amount:') }}</span>
                <span class="fw-bold text-dark fs-5 font-monospace">Tk. {{ formatPrice(form.amount) }}</span>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                <span class="text-success fw-bold">{{ $t('Paid Amount (Tk):') }}</span>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  :max="form.amount"
                  class="form-control form-control-sm text-end font-monospace fw-bold text-success py-0 px-2"
                  style="width: 120px; height: 28px;"
                  v-model.number="form.paid_amount"
                  @input="recalculateTotals"
                >
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <span class="text-danger fw-bold">{{ $t('Due Balance:') }}</span>
                <span class="font-monospace fw-bold text-danger fs-6">Tk. {{ formatPrice(form.due_amount) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'InvoiceEditPage',
  data() {
    return {
      loading: false,
      saving: false,
      clients: [],
      form: {
        id: null,
        invoice_no: '',
        invoice_date: '',
        client_id: '',
        creator: null,
        items: [],
        original_amount: 0,
        discount: 0,
        vat_percent: 0,
        vat: 0,
        amount: 0,
        paid_amount: 0,
        due_amount: 0,
        terms_note: '',
      },
      itemSearchQuery: '',
      searchResults: [],
      searchingItem: false,
      searchAttempted: false,
      searchDebounceTimer: null,
      pendingVariantItem: null,
    };
  },
  computed: {
    statusText() {
      const due = parseFloat(this.form.due_amount) || 0;
      const paid = parseFloat(this.form.paid_amount) || 0;
      if (due <= 0.01) return 'Paid';
      if (paid > 0) return 'Partial';
      return 'Due';
    },
    statusBadgeClass() {
      if (this.statusText === 'Paid') return 'bg-success';
      if (this.statusText === 'Partial') return 'bg-warning text-dark';
      return 'bg-danger';
    },
  },
  methods: {
    formatPrice(val) {
      const f = parseFloat(val);
      return isNaN(f) ? '0.00' : f.toFixed(2);
    },
    getStockBadgeClass(stock) {
      const s = parseFloat(stock);
      if (isNaN(s) || s <= 0) return 'bg-danger';
      if (s <= 5) return 'bg-warning text-dark';
      return 'bg-success';
    },
    fetchClients() {
      axios.get('client', { params: { allData: true } }).then((res) => {
        this.clients = res.data || [];
      });
    },
    fetchInvoice() {
      const id = this.$route.params.id;
      if (!id) return;
      this.loading = true;
      axios
        .get(`invoice/${id}`)
        .then((res) => {
          this.loading = false;
          const data = res.data;
          if (!data) return;

          // Check if paid
          const due = parseFloat(data.due_amount) || 0;
          if (data.payment_status === 'Paid' || (due <= 0.01 && parseFloat(data.paid_amount) >= parseFloat(data.amount))) {
            this.$toast('পরিশোধিত (Paid) ইনভয়েস এডিট করা সম্ভব নয়।', 'warning');
            this.$router.push({ name: 'invoice.show', params: { id: data.id } });
            return;
          }

          const items = (data.details || []).map((d) => ({
            id: d.id,
            item_id: d.item_id,
            title: d.title || (d.item ? d.item.title : 'Item'),
            barcode: d.barcode || '',
            color_id: d.color_id || null,
            color_title: d.color_title || '',
            size_id: d.size_id || null,
            size_title: d.size_title || '',
            serial_no: d.serial_no || '',
            description: d.description || '',
            warranty_type: d.warranty_type || 'none',
            warranty_period: d.warranty_period || '',
            present_stock: d.present_stock,
            qty: parseFloat(d.qty) || 1,
            amount: parseFloat(d.amount) || 0,
            total_amount: parseFloat(d.total_amount) || 0,
          }));

          let termsNote = '';
          if (Array.isArray(data.terms_conditions) && data.terms_conditions.length > 0) {
            termsNote = data.terms_conditions.join('\n');
          } else if (typeof data.terms_conditions === 'string') {
            termsNote = data.terms_conditions;
          }

          this.form = {
            id: data.id,
            invoice_no: data.invoice_no,
            invoice_date: data.raw_invoice_date || this.formatDateForInput(data.invoice_date),
            client_id: data.client_id || (data.client ? data.client.id : ''),
            creator: data.creator,
            items: items,
            original_amount: parseFloat(data.original_amount) || 0,
            discount: parseFloat(data.discount) || 0,
            vat_percent: parseFloat(data.vat_percent) || 0,
            vat: parseFloat(data.vat) || 0,
            amount: parseFloat(data.amount) || 0,
            paid_amount: parseFloat(data.paid_amount) || 0,
            due_amount: parseFloat(data.due_amount) || 0,
            terms_note: termsNote,
          };

          this.recalculateTotals();
        })
        .catch((err) => {
          this.loading = false;
          const msg = err.response?.data?.exception || err.response?.data?.message || 'Failed to load invoice';
          this.$toast(msg, 'error');
          this.$router.push('/invoice');
        });
    },
    formatDateForInput(dateStr) {
      if (!dateStr) return new Date().toISOString().split('T')[0];
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return new Date().toISOString().split('T')[0];
      return d.toISOString().split('T')[0];
    },
    changeItemQty(item, delta) {
      item.qty = Math.max(1, (parseInt(item.qty) || 1) + delta);
      this.recalculateTotals();
    },
    removeItem(idx) {
      this.form.items.splice(idx, 1);
      this.recalculateTotals();
    },
    recalculateTotals() {
      let subtotal = 0;
      for (const it of this.form.items) {
        it.qty = Math.max(1, parseFloat(it.qty) || 1);
        it.amount = Math.max(0, parseFloat(it.amount) || 0);
        it.total_amount = Math.round(it.qty * it.amount * 100) / 100;
        subtotal += it.total_amount;
      }
      this.form.original_amount = Math.round(subtotal * 100) / 100;

      const discount = Math.max(0, parseFloat(this.form.discount) || 0);
      this.form.discount = discount;

      const vatPercent = Math.max(0, parseFloat(this.form.vat_percent) || 0);
      this.form.vat_percent = vatPercent;

      const taxable = Math.max(0, this.form.original_amount - discount);
      this.form.vat = Math.round(((taxable * vatPercent) / 100) * 100) / 100;

      const net = Math.max(0, Math.round((this.form.original_amount - discount + this.form.vat) * 100) / 100);
      this.form.amount = net;

      const paid = Math.max(0, Math.min(net, parseFloat(this.form.paid_amount) || 0));
      this.form.paid_amount = paid;
      this.form.due_amount = Math.max(0, Math.round((net - paid) * 100) / 100);
    },
    onItemSearchInput() {
      clearTimeout(this.searchDebounceTimer);
      const q = this.itemSearchQuery.trim();
      if (!q) {
        this.searchResults = [];
        return;
      }
      this.searchDebounceTimer = setTimeout(() => {
        this.performItemSearch(q);
      }, 250);
    },
    onItemSearchEnter() {
      const q = this.itemSearchQuery.trim();
      if (q) {
        this.performItemSearch(q, true);
      }
    },
    clearItemSearch() {
      this.itemSearchQuery = '';
      this.searchResults = [];
      this.searchAttempted = false;
    },
    performItemSearch(term, autoSelectFirst = false) {
      const q = (term || '').trim();
      if (!q) {
        this.searchResults = [];
        this.searchAttempted = false;
        return;
      }
      this.searchingItem = true;
      this.searchAttempted = true;
      axios
        .get('pos/search-items', { params: { term: q } })
        .then((res) => {
          this.searchingItem = false;
          const items = Array.isArray(res.data)
            ? res.data
            : (res.data?.items && Array.isArray(res.data.items) ? res.data.items : []);
          this.searchResults = items;
          if (autoSelectFirst && this.searchResults.length === 1) {
            this.selectProductToAdd(this.searchResults[0]);
          }
        })
        .catch(() => {
          this.searchingItem = false;
          this.searchResults = [];
        });
    },
    getItemPrice(item) {
      if (!item) return 0;
      const candidates = [
        item.retail_price,
        item.sale_price,
        item.selling_price,
        item.price,
        item.wholesale_price,
        item.opening_rate
      ];
      for (const c of candidates) {
        const val = parseFloat(c);
        if (!isNaN(val) && val > 0) return val;
      }
      const itemPrices = item.item_prices || item.itemPrices || [];
      if (itemPrices.length > 0) {
        for (const ip of itemPrices) {
          const val = parseFloat(ip.retail_price || ip.selling_price || ip.price || ip.wholesale_price);
          if (!isNaN(val) && val > 0) return val;
        }
      }
      return 0;
    },
    getItemTotalStock(item) {
      if (!item) return 0;
      const stockSummaries = item.stock_summaries || item.stockSummaries || [];
      if (stockSummaries.length > 0) {
        return stockSummaries.reduce((sum, s) => sum + (parseFloat(s.current_stock) || 0), 0);
      }
      if (item.current_stock !== undefined) return parseFloat(item.current_stock) || 0;
      return 0;
    },
    selectProductToAdd(product) {
      const stockSummaries = product.stock_summaries || product.stockSummaries || [];
      const itemPrices = product.item_prices || product.itemPrices || [];
      const variants = [];

      // 1. Check stockSummaries for distinct color/size
      if (stockSummaries.length > 0) {
        for (const s of stockSummaries) {
          if (s.color_id || s.size_id) {
            const price = this.getVariantPrice(product, s.color_id, s.size_id);
            variants.push({
              color_id: s.color_id,
              color_title: s.color ? s.color.title : null,
              size_id: s.size_id,
              size_title: s.size ? s.size.title : null,
              stock: parseFloat(s.current_stock) || 0,
              price: price,
            });
          }
        }
      }

      // 2. Fallback to itemPrices if stockSummaries had no variants
      if (variants.length === 0 && itemPrices.length > 0) {
        for (const ip of itemPrices) {
          if (ip.color_id || ip.size_id) {
            const price = parseFloat(ip.retail_price || ip.selling_price || ip.price || ip.wholesale_price) || this.getItemPrice(product);
            variants.push({
              color_id: ip.color_id,
              color_title: ip.color ? ip.color.title : null,
              size_id: ip.size_id,
              size_title: ip.size ? ip.size.title : null,
              stock: this.getItemTotalStock(product),
              price: price,
            });
          }
        }
      }

      if (variants.length > 1) {
        this.pendingVariantItem = {
          ...product,
          variants: variants,
        };
        this.clearItemSearch();
        return;
      }

      const firstVar = variants.length === 1 ? variants[0] : null;
      this.addItemToForm(
        product,
        firstVar ? firstVar.color_id : null,
        firstVar ? firstVar.color_title : null,
        firstVar ? firstVar.size_id : null,
        firstVar ? firstVar.size_title : null,
        firstVar ? firstVar.price : this.getItemPrice(product),
        firstVar ? firstVar.stock : this.getItemTotalStock(product)
      );
      this.clearItemSearch();
    },
    getVariantPrice(product, colorId, sizeId) {
      const itemPrices = product.item_prices || product.itemPrices || [];
      if (itemPrices.length > 0) {
        const found = itemPrices.find(
          (ip) =>
            (ip.color_id == colorId || (!colorId && !ip.color_id)) &&
            (ip.size_id == sizeId || (!sizeId && !ip.size_id))
        );
        if (found) {
          const val = parseFloat(found.retail_price || found.selling_price || found.price || found.wholesale_price);
          if (!isNaN(val) && val > 0) return val;
        }
      }
      return this.getItemPrice(product);
    },
    confirmAddVariant(product, variant) {
      this.addItemToForm(
        product,
        variant.color_id,
        variant.color_title,
        variant.size_id,
        variant.size_title,
        variant.price,
        variant.stock
      );
      this.pendingVariantItem = null;
    },
    addItemToForm(product, colorId, colorTitle, sizeId, sizeTitle, price, stock) {
      const existing = this.form.items.find(
        (it) =>
          it.item_id === product.id &&
          (it.color_id == colorId || (!it.color_id && !colorId)) &&
          (it.size_id == sizeId || (!it.size_id && !sizeId))
      );

      if (existing) {
        existing.qty += 1;
      } else {
        this.form.items.push({
          item_id: product.id,
          title: product.title,
          barcode: product.barcode || '',
          color_id: colorId || null,
          color_title: colorTitle || '',
          size_id: sizeId || null,
          size_title: sizeTitle || '',
          serial_no: '',
          description: product.title,
          warranty_type: product.warranty_type || 'none',
          warranty_period: product.warranty_period || '',
          present_stock: stock,
          qty: 1,
          amount: parseFloat(price) || 0,
          total_amount: parseFloat(price) || 0,
        });
      }

      this.recalculateTotals();
    },
    saveInvoice() {
      if (this.form.items.length === 0) {
        this.$toast('ইনভয়েসে কমপক্ষে একটি পণ্য থাকতে হবে।', 'warning');
        return;
      }

      this.saving = true;

      const payload = {
        client_id: this.form.client_id || null,
        invoice_date: this.form.invoice_date,
        items: this.form.items,
        discount: this.form.discount,
        vat_percent: this.form.vat_percent,
        vat: this.form.vat,
        paid_amount: this.form.paid_amount,
        terms_conditions: this.form.terms_note ? [this.form.terms_note] : [],
      };

      axios
        .put(`invoice/${this.form.id}`, payload)
        .then((res) => {
          this.saving = false;
          if (res.data.success) {
            this.$toast(res.data.message || 'ইনভয়েস সফলভাবে আপডেট করা হয়েছে।', 'success');
            this.$router.push({ name: 'invoice.show', params: { id: this.form.id } });
          } else {
            this.$toast(res.data.message || 'আপডেট সম্পন্ন হতে পারেনি।', 'error');
          }
        })
        .catch((err) => {
          this.saving = false;
          const msg =
            err.response?.data?.exception ||
            err.response?.data?.message ||
            'ইনভয়েস আপডেট করতে ব্যর্থ হয়েছে।';
          this.$toast(msg, 'error');
        });
    },
  },
  mounted() {
    this.fetchClients();
    this.fetchInvoice();
  },
};
</script>

<style scoped>
.hover-bg-light:hover {
  background-color: #f0f7ff !important;
}
.cursor-pointer {
  cursor: pointer;
}
.btn-xs {
  padding: 0.2rem 0.45rem;
  font-size: 0.8rem;
  line-height: 1;
}
</style>
