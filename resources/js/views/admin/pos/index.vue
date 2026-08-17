<template>
  <div class="pos-container p-3 bg-light min-vh-100">
    <!-- POS Top Header Bar -->
    <div class="card border-0 shadow-sm mb-3 bg-dark text-white">
      <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <h4 class="mb-0 fw-bold text-warning"><i class="fas fa-cash-register me-2"></i>QPOS Clothing Terminal</h4>
          <span class="badge bg-secondary font-monospace">{{ currentDate }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <router-link to="/pos/return" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1">
            <i class="fas fa-undo"></i> Sales Return (পণ্য ফেরত)
          </router-link>
          <router-link to="/admin/dashboard" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <i class="fas fa-tachometer-alt"></i> Dashboard
          </router-link>
        </div>
      </div>
    </div>

    <div class="row g-3 align-items-start">
      <!-- Left Panel: Client Information -->
      <div class="col-xl-3 col-lg-3 col-md-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-primary text-white py-2 d-flex align-items-center justify-content-between">
            <span class="fw-bold"><i class="fas fa-user me-2"></i>Client Information (F4)</span>
          </div>
          <div class="card-body p-3">
            <!-- Mobile Search Input -->
            <div class="mb-3">
              <label class="form-label fw-bold small text-muted">Customer Mobile (মোবাইল নম্বর)</label>
              <div class="input-group">
                <input ref="clientMobileInput" type="text" class="form-control form-control-lg font-monospace fw-bold" placeholder="017xxxxxxxx" v-model="client.mobile" @keyup.enter="searchCustomer" @blur="searchCustomer">
                <button type="button" class="btn btn-primary" @click="searchCustomer">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>

            <!-- Customer Details Card -->
            <div v-if="client.id" class="p-3 border rounded bg-light mb-3">
              <div class="fw-bold text-dark fs-6 mb-1">{{ client.name }}</div>
              <div class="small text-muted mb-1"><i class="fas fa-phone-alt me-1"></i>{{ client.mobile }}</div>
              <div class="small text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i>{{ client.address || 'N/A' }}</div>
              <div class="p-2 bg-white rounded border d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-danger">Previous Due:</span>
                <span class="fw-bold font-monospace text-danger">Tk. {{ formatPrice(client.current_due || 0) }}</span>
              </div>
            </div>

            <!-- Quick Create Customer Form if Not Found -->
            <div v-else-if="showNewClientForm" class="p-3 border border-warning rounded bg-warning bg-opacity-10 mb-3">
              <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-user-plus me-1"></i>New Client Registration</h6>
              <div class="mb-2">
                <input type="text" class="form-control form-control-sm" placeholder="Client Full Name" v-model="newClient.name">
              </div>
              <div class="mb-2">
                <input type="text" class="form-control form-control-sm" placeholder="Address" v-model="newClient.address">
              </div>
              <button type="button" class="btn btn-sm btn-warning w-100 fw-bold" @click="createQuickCustomer">
                Save & Select Client
              </button>
            </div>

            <div v-else class="text-center p-3 text-muted border rounded bg-white">
              <i class="fas fa-id-card fa-2x mb-2 text-secondary opacity-50"></i>
              <p class="small mb-0">Enter mobile number to select or add client.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Center Panel: Item Search & Cart -->
      <div class="col-xl-6 col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body p-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <label class="form-label fw-bold small text-muted">Search Item / Scan Barcode (F2)</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-white"><i class="fas fa-barcode text-primary"></i></span>
                <input ref="itemSearchInput" type="text" class="form-control fw-bold" placeholder="Type item name or scan barcode..." v-model="searchTerm" @input="onSearchInput" @keyup.enter="handleSearchEnter">
                <button type="button" class="btn btn-outline-secondary" @click="clearSearch" v-if="searchTerm">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Search Results Dropdown -->
              <div v-if="searchResults.length > 0" class="position-absolute w-100 bg-white border rounded shadow-lg mt-1" style="max-height: 350px; overflow-y: auto; z-index: 9999;">
                <div v-for="item in searchResults" :key="item.id" class="p-2 border-bottom hover-bg-light cursor-pointer d-flex align-items-center justify-content-between" @click="openItemModal(item)">
                  <div>
                    <div class="fw-bold text-dark">{{ item.title }}</div>
                    <small class="text-muted font-monospace me-2">Barcode: {{ item.barcode }}</small>
                    <small class="badge bg-secondary" v-if="item.category">{{ item.category.title }}</small>
                  </div>
                  <button type="button" class="btn btn-sm btn-primary">Select Item</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Summary Table -->
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-dark text-white py-2 d-flex align-items-center justify-content-between">
            <span class="fw-bold"><i class="fas fa-shopping-cart me-2"></i>Cart Items ({{ cart.length }})</span>
            <button type="button" class="btn btn-sm btn-outline-light py-0" @click="clearCart" v-if="cart.length > 0">Clear All</button>
          </div>
          <div class="card-body p-0 table-responsive" style="min-height: 280px; max-height: 450px;">
            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 13px;">
              <thead class="table-light sticky-top" style="z-index: 2;">
                <tr>
                  <th width="24%">Item Title</th>
                  <th width="18%">Color / Size</th>
                  <th width="16%">Serial No</th>
                  <th width="12%">Qty</th>
                  <th width="12%" class="text-end">Price</th>
                  <th width="12%" class="text-end">Total</th>
                  <th width="6%" class="text-center">Act</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(cItem, idx) in cart" :key="idx">
                  <td>
                    <div class="fw-bold text-dark text-truncate" style="max-width: 140px;" :title="cItem.title">{{ cItem.title }}</div>
                    <small class="text-muted font-monospace" style="font-size: 11px;">{{ cItem.barcode }}</small>
                  </td>
                  <td>
                    <span class="badge bg-info text-dark me-1" v-if="cItem.color_title" style="font-size: 10px;">{{ cItem.color_title }}</span>
                    <span class="badge bg-secondary me-1" v-if="cItem.size_title" style="font-size: 10px;">{{ cItem.size_title }}</span>
                    <span v-if="!cItem.color_title && !cItem.size_title" class="text-muted small">Standard</span>
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm font-monospace p-1" style="max-width: 95px; font-size: 11px;" v-model="cItem.serial_no" placeholder="Optional">
                  </td>
                  <td>
                    <input type="number" min="1" class="form-control form-control-sm text-center fw-bold p-1" style="max-width: 55px; font-size: 12px;" v-model.number="cItem.qty">
                  </td>
                  <td class="text-end font-monospace">
                    <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace p-1" style="max-width: 70px; font-size: 12px;" v-model.number="cItem.rate">
                  </td>
                  <td class="text-end font-monospace fw-bold text-primary">
                    {{ formatPrice(cItem.qty * cItem.rate) }}
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" @click="removeCartItem(idx)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="cart.length === 0">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-shopping-basket fa-3x mb-2 text-secondary opacity-50"></i>
                    <p class="mb-0">Cart is empty. Search items above or scan barcode to add products.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Panel: Payment & Checkout Summary -->
      <div class="col-xl-3 col-lg-3 col-md-12">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-success text-white py-2">
            <span class="fw-bold"><i class="fas fa-calculator me-2"></i>Payment & Checkout (F8)</span>
          </div>
          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
              <!-- Calculations Breakdown -->
              <div class="p-3 bg-light rounded border mb-3">
                <div class="d-flex justify-content-between mb-2 fs-6">
                  <span class="text-muted">Subtotal:</span>
                  <span class="fw-bold font-monospace">Tk. {{ formatPrice(cartSubtotal) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-muted small">Discount (ছাড়):</span>
                  <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace w-50" v-model.number="discount" placeholder="0.00">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-muted small">VAT / Tax:</span>
                  <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace w-50" v-model.number="vat" placeholder="0.00">
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-dark fs-5">Net Payable:</span>
                  <span class="fw-bold font-monospace fs-4 text-success">Tk. {{ formatPrice(netPayable) }}</span>
                </div>
              </div>

              <!-- Payment Method Selection -->
              <div class="mb-3">
                <label class="form-label fw-bold small text-muted">Payment Method (পেমেন্ট পদ্ধতি)</label>
                <select class="form-select form-select-sm font-monospace fw-bold" v-model="payment_method">
                  <option value="Cash">Cash (নগদ)</option>
                  <option value="Card">Credit/Debit Card</option>
                  <option value="bKash">bKash (বিকাশ)</option>
                  <option value="Nagad">Nagad (নগদ অ্যাপ)</option>
                  <option value="Rocket">Rocket (রকেট)</option>
                  <option value="Bank">Bank Transfer</option>
                </select>
              </div>

              <!-- MFS TrxID if digital payment -->
              <div class="mb-3" v-if="payment_method !== 'Cash'">
                <input type="text" class="form-control form-control-sm font-monospace" placeholder="TrxID / Reference No." v-model="trxid">
              </div>

              <!-- Paid Amount Input -->
              <div class="mb-3">
                <label class="form-label fw-bold small text-muted">Paid Amount (প্রদত্ত টাকা)</label>
                <input type="number" step="0.01" class="form-control form-control-lg text-end font-monospace fw-bold text-primary" v-model.number="paid_amount">
              </div>

              <!-- Change Return Amount -->
              <div class="p-2 bg-light border rounded text-center mb-3">
                <small class="text-muted d-block fw-bold">Change Amount (ফেরত দিতে হবে):</small>
                <div class="fw-bold font-monospace fs-5" :class="changeAmount >= 0 ? 'text-success' : 'text-danger'">
                  Tk. {{ formatPrice(changeAmount) }}
                </div>
              </div>
            </div>

            <!-- Complete Sale Button -->
            <div>
              <button type="button" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow d-flex align-items-center justify-content-center gap-2" @click="submitCheckout" :disabled="cart.length === 0 || isSubmitting">
                <i class="fas fa-print fa-lg"></i> Complete Sale & Print (F8)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Popup for Item Color, Size & Serial Selection -->
    <div v-if="showItemModal" class="modal fade show d-block tab-modal-backdrop" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark text-white py-2">
            <h5 class="modal-title fw-bold fs-6"><i class="fas fa-box-open me-2"></i>Select Color, Size & Serial</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeItemModal"></button>
          </div>
          <div class="modal-body p-3" v-if="activeItem">
            <div class="d-flex align-items-center gap-3 mb-3 p-2 border rounded bg-light">
              <img v-if="activeItem.image" :src="activeItem.image" class="img-fluid rounded border" style="height: 60px;" alt="Product">
              <div>
                <h6 class="fw-bold text-dark mb-1">{{ activeItem.title }}</h6>
                <small class="text-muted font-monospace me-2">Barcode: {{ activeItem.barcode }}</small>
                <span class="badge bg-dark" v-if="activeItem.unit">{{ activeItem.unit.title }}</span>
              </div>
            </div>

            <div class="row g-3">
              <!-- Color Selection -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Color (রং)</label>
                <select class="form-select form-select-sm" v-model="modalSelection.color_id" @change="onVariantChange">
                  <option :value="null">-- Standard / Any Color --</option>
                  <option v-for="c in availableColors" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <!-- Size Selection -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Size (সাইজ)</label>
                <select class="form-select form-select-sm" v-model="modalSelection.size_id" @change="onVariantChange">
                  <option :value="null">-- Standard / Any Size --</option>
                  <option v-for="s in availableSizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>

              <!-- Stock & Price Info -->
              <div class="col-12">
                <div class="p-2 border rounded d-flex align-items-center justify-content-between" :class="modalSelection.available_stock > 0 ? 'bg-white' : 'bg-danger bg-opacity-10 border-danger'">
                  <span class="small font-monospace">Available Stock: <strong :class="modalSelection.available_stock > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'">{{ modalSelection.available_stock }} {{ modalSelection.available_stock <= 0 ? '(Out of Stock)' : '' }}</strong></span>
                  <span class="small font-monospace">Selling Price: <strong class="text-success">Tk. {{ formatPrice(modalSelection.rate) }}</strong></span>
                </div>
              </div>

              <!-- Serial No (For Electronics / Serialized items) -->
              <div class="col-12">
                <label class="form-label fw-bold small text-muted">Serial No. (ইলেকট্রনিক্স পণ্যের জন্য সিরিয়াল নং)</label>
                <input type="text" class="form-control form-control-sm font-monospace" placeholder="Enter Serial No if applicable" v-model="modalSelection.serial_no">
              </div>

              <!-- Selling Price (Editable) -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Unit Rate (দর)</label>
                <input type="number" step="0.01" class="form-control form-control-sm font-monospace text-end" v-model.number="modalSelection.rate">
              </div>

              <!-- Quantity -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Quantity (পরিমাণ)</label>
                <input type="number" min="1" :max="modalSelection.available_stock" class="form-control form-control-sm font-monospace text-center fw-bold" v-model.number="modalSelection.qty">
              </div>
            </div>
          </div>
          <div class="modal-footer py-2 bg-light">
            <button type="button" class="btn btn-sm btn-secondary" @click="closeItemModal">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary px-4" @click="addToCartFromModal" :disabled="modalSelection.available_stock <= 0">
              <i class="fas fa-cart-plus me-1"></i>Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden Thermal POS Invoice Printable Template -->
    <div id="posInvoicePrintArea" style="display: none;">
      <div v-if="completedInvoice" style="width: 280px; margin: 0 auto; padding: 10px; font-family: monospace, Arial; font-size: 11px; color: #000; background: #fff;">
        <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
          <h3 style="margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase;">QPOS Clothing Store</h3>
          <div style="font-size: 10px;">Dhaka, Bangladesh | Contact: 01700000000</div>
          <div style="font-size: 11px; font-weight: bold; margin-top: 4px;">POS INVOICE</div>
        </div>

        <div style="margin-bottom: 8px; font-size: 10px;">
          <div><strong>Invoice No:</strong> {{ completedInvoice.invoice_no }}</div>
          <div><strong>Date:</strong> {{ completedInvoice.invoice_date }}</div>
          <div><strong>Client:</strong> {{ completedInvoice.client ? completedInvoice.client.name : 'Walk-in Customer' }}</div>
          <div><strong>Mobile:</strong> {{ completedInvoice.client ? completedInvoice.client.mobile : 'N/A' }}</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10px;">
          <thead>
            <tr style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
              <th style="text-align: left; padding: 3px 0;">Item</th>
              <th style="text-align: center; padding: 3px 0;">Qty</th>
              <th style="text-align: right; padding: 3px 0;">Price</th>
              <th style="text-align: right; padding: 3px 0;">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in completedInvoice.details" :key="d.id" style="border-bottom: 1px dashed #ccc;">
              <td style="padding: 3px 0;">
                <div>{{ d.item ? d.item.title : 'Item' }}</div>
                <div style="font-size: 9px; color: #444;" v-if="d.color || d.size">
                  {{ d.color ? d.color.title : '' }} {{ d.size ? '/' + d.size.title : '' }}
                </div>
                <div style="font-size: 9px; color: #444;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
              </td>
              <td style="text-align: center; padding: 3px 0;">{{ d.qty }}</td>
              <td style="text-align: right; padding: 3px 0;">{{ formatPrice(d.amount) }}</td>
              <td style="text-align: right; padding: 3px 0;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <div style="border-top: 1px solid #000; padding-top: 4px; font-size: 10px;">
          <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>Tk. {{ formatPrice(completedInvoice.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.discount > 0">
            <span>Discount:</span>
            <span>- Tk. {{ formatPrice(completedInvoice.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.vat > 0">
            <span>VAT:</span>
            <span>+ Tk. {{ formatPrice(completedInvoice.vat) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 3px;">
            <span>Net Payable:</span>
            <span>Tk. {{ formatPrice(completedInvoice.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid Amount:</span>
            <span>Tk. {{ formatPrice(completedInvoice.paid_amount) }}</span>
          </div>
        </div>

        <div style="text-align: center; margin-top: 12px; border-top: 1px dashed #000; padding-top: 6px; font-size: 9px;">
          <div>Thank you for shopping with us!</div>
          <div>Please preserve this receipt for returns within 7 days.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      currentDate: new Date().toLocaleDateString('en-GB'),
      client: { id: null, name: '', mobile: '', address: '', current_due: 0 },
      showNewClientForm: false,
      newClient: { name: '', address: '' },

      searchTerm: '',
      searchResults: [],
      allColors: [],
      allSizes: [],

      showItemModal: false,
      activeItem: null,
      modalSelection: {
        color_id: null,
        size_id: null,
        serial_no: '',
        qty: 1,
        rate: 0,
        available_stock: 0
      },

      cart: [],
      discount: 0,
      vat: 0,
      payment_method: 'Cash',
      mbanking_type: '',
      trxid: '',
      paid_amount: 0,

      isSubmitting: false,
      completedInvoice: null,
    };
  },
  computed: {
    cartSubtotal() {
      return this.cart.reduce((sum, i) => sum + (floatval(i.qty) * floatval(i.rate)), 0);
    },
    netPayable() {
      const net = (this.cartSubtotal - floatval(this.discount)) + floatval(this.vat);
      return Math.max(0, net);
    },
    changeAmount() {
      return floatval(this.paid_amount) - this.netPayable;
    },
    availableColors() {
      if (this.activeItem && this.activeItem.item_prices) {
        const colorIds = this.activeItem.item_prices.map(p => p.color_id).filter(id => id !== null);
        if (colorIds.length > 0) {
          return this.allColors.filter(c => colorIds.includes(c.id));
        }
      }
      return this.allColors;
    },
    availableSizes() {
      if (this.activeItem && this.activeItem.item_prices) {
        const sizeIds = this.activeItem.item_prices.map(p => p.size_id).filter(id => id !== null);
        if (sizeIds.length > 0) {
          return this.allSizes.filter(s => sizeIds.includes(s.id));
        }
      }
      return this.allSizes;
    }
  },
  watch: {
    netPayable(val) {
      this.paid_amount = val;
    }
  },
  methods: {
    formatPrice(val) {
      return floatval(val).toFixed(2);
    },
    searchCustomer() {
      if (!this.client.mobile || this.client.mobile.trim() === '') return;
      axios.get(`pos/search-customer`, { params: { mobile: this.client.mobile.trim() } })
        .then(res => {
          if (res.data && res.data.id) {
            this.client = res.data;
            this.showNewClientForm = false;
            this.$toast(`Client found: ${res.data.name}`, 'success');
          } else {
            this.showNewClientForm = true;
            this.$toast('Client not found. Register a new client below.', 'info');
          }
        })
        .catch(err => {
          this.showNewClientForm = true;
          this.$toast('Client lookup failed. Fill details to register.', 'info');
        });
    },
    createQuickCustomer() {
      if (!this.newClient.name) {
        this.$toast('Client name is required', 'warning');
        return;
      }
      axios.post('pos/quick-customer', {
        mobile: this.client.mobile,
        name: this.newClient.name,
        address: this.newClient.address
      }).then(res => {
        if (res.data) {
          this.client = res.data;
          this.showNewClientForm = false;
          this.$toast('Client registered successfully', 'success');
        }
      });
    },
    onSearchInput() {
      if (!this.searchTerm || this.searchTerm.length < 1) {
        this.searchResults = [];
        return;
      }
      axios.get('pos/search-items', { params: { term: this.searchTerm } })
        .then(res => {
          this.searchResults = res.data.items || [];
          this.allColors = res.data.colors || [];
          this.allSizes = res.data.sizes || [];
        });
    },
    handleSearchEnter() {
      if (this.searchResults.length === 1) {
        this.openItemModal(this.searchResults[0]);
      }
    },
    clearSearch() {
      this.searchTerm = '';
      this.searchResults = [];
    },
    openItemModal(item) {
      this.activeItem = item;
      this.searchResults = [];
      this.searchTerm = '';

      // Default rate from itemPrices or opening_rate
      let defaultRate = item.opening_rate || 0;
      if (item.item_prices && item.item_prices.length > 0) {
        defaultRate = item.item_prices[0].selling_price || defaultRate;
      }

      this.modalSelection = {
        color_id: null,
        size_id: null,
        serial_no: '',
        qty: 1,
        rate: defaultRate,
        available_stock: 0
      };

      this.onVariantChange();
      this.showItemModal = true;
    },
    closeItemModal() {
      this.showItemModal = false;
      this.activeItem = null;
    },
    onVariantChange() {
      if (!this.activeItem) return;

      // Price lookup
      if (this.activeItem.item_prices) {
        const match = this.activeItem.item_prices.find(p => p.color_id == this.modalSelection.color_id && p.size_id == this.modalSelection.size_id);
        if (match && match.selling_price > 0) {
          this.modalSelection.rate = match.selling_price;
        }
      }

      // Stock lookup
      let stock = 0;
      if (this.activeItem.stock_summaries) {
        const matches = this.activeItem.stock_summaries.filter(s => {
          const colorMatch = !this.modalSelection.color_id || s.color_id == this.modalSelection.color_id;
          const sizeMatch = !this.modalSelection.size_id || s.size_id == this.modalSelection.size_id;
          return colorMatch && sizeMatch;
        });
        stock = matches.reduce((acc, curr) => acc + floatval(curr.current_stock), 0);
      }
      this.modalSelection.available_stock = stock;
    },
    async addToCartFromModal() {
      if (!this.activeItem) return;

      // 1. Stock Check: Available stock must be > 0
      if (this.modalSelection.available_stock <= 0) {
        this.$toast('স্টক খালি! স্টক ছাড়া পণ্য কার্টে যোগ করা সম্ভব নয়।', 'warning');
        return;
      }

      if (this.modalSelection.qty > this.modalSelection.available_stock) {
        this.$toast(`পর্যাপ্ত স্টক নেই! সর্বোচ্চ প্রাপ্য স্টক: ${this.modalSelection.available_stock}`, 'warning');
        return;
      }

      // 2. Serial Number Validation (Purchase existence & Prior sales check)
      if (this.modalSelection.serial_no && this.modalSelection.serial_no.trim() !== '') {
        const serialNo = this.modalSelection.serial_no.trim();
        try {
          const checkRes = await axios.get('pos/validate-serial', {
            params: {
              item_id: this.activeItem.id,
              color_id: this.modalSelection.color_id,
              size_id: this.modalSelection.size_id,
              serial_no: serialNo
            }
          });

          if (checkRes.data && checkRes.data.valid === false) {
            this.$toast(checkRes.data.message, 'warning');
            return;
          }
        } catch (err) {
          console.error(err);
        }
      }

      const colorObj = this.allColors.find(c => c.id == this.modalSelection.color_id);
      const sizeObj = this.allSizes.find(s => s.id == this.modalSelection.size_id);

      this.cart.push({
        item_id: this.activeItem.id,
        title: this.activeItem.title,
        barcode: this.activeItem.barcode,
        color_id: this.modalSelection.color_id,
        color_title: colorObj ? colorObj.title : null,
        size_id: this.modalSelection.size_id,
        size_title: sizeObj ? sizeObj.title : null,
        serial_no: this.modalSelection.serial_no,
        qty: this.modalSelection.qty || 1,
        rate: this.modalSelection.rate || 0,
      });

      this.closeItemModal();
      this.$toast('Item added to cart', 'success');
    },
    removeCartItem(index) {
      this.cart.splice(index, 1);
    },
    clearCart() {
      this.cart = [];
    },
    submitCheckout() {
      if (this.cart.length === 0) {
        this.$toast('Cart is empty', 'warning');
        return;
      }

      this.isSubmitting = true;

      const payload = {
        client_id: this.client.id,
        client_mobile: this.client.mobile,
        client_name: this.client.name,
        client_address: this.client.address,
        cart: this.cart,
        discount: this.discount,
        vat: this.vat,
        payment_method: this.payment_method,
        mbanking_type: this.mbanking_type,
        trxid: this.trxid,
        paid_amount: this.paid_amount,
      };

      axios.post('pos/checkout', payload)
        .then(res => {
          this.isSubmitting = false;
          if (res.data && res.data.success) {
            this.completedInvoice = res.data.invoice;
            this.$toast('Sale completed successfully!', 'success');

            // Trigger POS Print
            this.$nextTick(() => {
              this.print('posInvoicePrintArea', 'POS Invoice - ' + this.completedInvoice.invoice_no);
              this.resetPOS();
            });
          }
        })
        .catch(err => {
          this.isSubmitting = false;
          this.$toast(err.response?.data?.exception || 'Failed to complete checkout', 'danger');
        });
    },
    resetPOS() {
      this.client = { id: null, name: '', mobile: '', address: '', current_due: 0 };
      this.showNewClientForm = false;
      this.cart = [];
      this.discount = 0;
      this.vat = 0;
      this.paid_amount = 0;
      this.trxid = '';
      this.payment_method = 'Cash';
      this.searchTerm = '';
      this.searchResults = [];
    },
    handleKeydown(e) {
      if (e.key === 'F2' || (e.ctrlKey && e.key === 'f')) {
        e.preventDefault();
        this.$refs.itemSearchInput.focus();
      } else if (e.key === 'F4' || (e.ctrlKey && e.key === 'm')) {
        e.preventDefault();
        this.$refs.clientMobileInput.focus();
      } else if (e.key === 'F8' || (e.ctrlKey && e.key === 'p')) {
        e.preventDefault();
        this.submitCheckout();
      } else if (e.key === 'Escape') {
        this.closeItemModal();
        this.searchResults = [];
      }
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
  }
};

function floatval(val) {
  const f = parseFloat(val);
  return isNaN(f) ? 0 : f;
}
</script>

<style scoped>
.hover-bg-light:hover {
  background-color: #f8f9fa;
}
.tab-modal-backdrop {
  z-index: 1055;
}
</style>
