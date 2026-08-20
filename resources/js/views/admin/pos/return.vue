<template>
  <div class="container-fluid p-3">
    <!-- Top Header Card -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white py-3 border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
          <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-undo me-2 text-warning"></i>Sales Return Management (পণ্য ফেরত ব্যবস্থাপনা)</h5>
          <small class="text-muted">Search previous invoice, select returned items, and restore inventory stock</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          <router-link to="/invoice" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 font-monospace">
            <i class="fas fa-file-invoice"></i> Invoices
          </router-link>
          <router-link to="/pos" class="btn-pos-sale shadow-sm">
            <span>
              <i class="fas fa-cash-register"></i>
            </span>
            Back to POS
          </router-link>
        </div>
      </div>

      <div class="card-body p-4 pt-1">
        <!-- Invoice Search Bar -->
        <div class="row mb-4">
          <div class="col-md-7 mx-auto">
            <label class="form-label fw-bold text-muted small mb-1">
              <i class="fas fa-search me-1"></i> Search Invoice No / Customer Mobile (ইনভয়েস বা গ্রাহকের মোবাইল নম্বর)
            </label>
            <div class="search-box-wrapper d-flex align-items-stretch">
              <input
                type="text"
                class="form-control search-input"
                placeholder="e.g. POS-20260817-0001 or 017xxxxxxxx"
                v-model="searchTerm"
                @keyup.enter="searchInvoice"
              >
              <button
                type="button"
                class="btn search-btn d-inline-flex align-items-center justify-content-center gap-2"
                @click="searchInvoice"
              >
                <i class="fas fa-search"></i> Search
              </button>
            </div>
          </div>
        </div>

        <!-- Invoice Results List -->
        <div v-if="invoices.length > 0 && !selectedInvoice" class="row g-3 mb-4">
          <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
            <h6 class="fw-bold text-dark mb-0">Matching Invoices found: <span class="badge bg-primary">{{ invoices.length }}</span></h6>
            <small class="text-muted">Click an invoice below to process return</small>
          </div>
          <div v-for="inv in invoices" :key="inv.id" class="col-md-6">
            <div class="card border shadow-sm h-100 invoice-select-card cursor-pointer" @click="selectInvoice(inv)">
              <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="fw-bold text-primary font-monospace mb-1">{{ inv.invoice_no }}</h6>
                  <div class="small text-dark fw-bold mb-1">
                    <i class="fas fa-user me-1 text-muted"></i>{{ inv.client ? inv.client.name : 'Walk-in Customer' }}
                    <span class="text-muted ms-1" v-if="inv.client && inv.client.mobile">({{ inv.client.mobile }})</span>
                  </div>
                  <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>Date: {{ inv.invoice_date }}</small>
                </div>
                <div class="text-end">
                  <div class="fw-bold text-success font-monospace fs-5">Tk. {{ formatPrice(inv.amount) }}</div>
                  <button type="button" class="btn btn-sm btn-primary mt-2 shadow-sm d-inline-flex align-items-center gap-1">
                    <i class="fas fa-arrow-right"></i> Select
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Invoice Return Processing Screen -->
        <div v-if="selectedInvoice" class="card border-0 shadow-sm mb-4">
          <div class="card-header text-white py-2 px-3 d-flex justify-content-between align-items-center" style="background-color: #112C47;">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-file-invoice fs-5"></i>
              <span class="fw-bold fs-6">Selected Invoice: {{ selectedInvoice.invoice_no }}</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" @click="selectedInvoice = null">
              <i class="fas fa-exchange-alt"></i> Change Invoice
            </button>
          </div>
          <div class="card-body p-3">
            <div class="row mb-3 p-3 bg-light rounded border g-2">
              <div class="col-md-4">
                <span class="text-muted small d-block">Customer (গ্রাহক):</span>
                <strong class="text-dark">{{ selectedInvoice.client ? selectedInvoice.client.name : 'Walk-in' }}</strong>
                <span class="text-muted small ms-1" v-if="selectedInvoice.client && selectedInvoice.client.mobile">({{ selectedInvoice.client.mobile }})</span>
              </div>
              <div class="col-md-4">
                <span class="text-muted small d-block">Invoice Date:</span>
                <strong class="text-dark">{{ selectedInvoice.invoice_date }}</strong>
              </div>
              <div class="col-md-4 text-md-end">
                <span class="text-muted small d-block">Total Invoice Amount:</span>
                <span class="fw-bold text-success font-monospace fs-5">Tk. {{ formatPrice(selectedInvoice.amount) }}</span>
              </div>
            </div>

            <!-- Return Item Table with Sold, Returned & Max Returnable Validation -->
            <div class="table-responsive mb-3 border rounded">
              <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th width="4%" class="text-center">Select</th>
                    <th>Item Title</th>
                    <th>Color / Size</th>
                    <th>Serial No</th>
                    <th width="10%" class="text-center">Sold Qty</th>
                    <th width="10%" class="text-center">Prev Return</th>
                    <th width="11%" class="text-center">Returnable</th>
                    <th width="12%" class="text-end">Unit Rate</th>
                    <th width="13%" class="text-center">Return Qty</th>
                    <th width="14%" class="text-end">Refund Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in returnList" :key="idx" :class="{ 'table-secondary opacity-75': item.remaining_returnable_qty <= 0 }">
                    <td class="text-center">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        v-model="item.selected"
                        :disabled="item.remaining_returnable_qty <= 0"
                        @change="onToggleItemSelect(item)"
                        style="transform: scale(1.2); cursor: pointer;"
                      >
                    </td>
                    <td>
                      <div class="fw-bold text-dark">{{ item.title }}</div>
                      <small class="text-muted font-monospace">{{ item.barcode }}</small>
                    </td>
                    <td>
                      <span class="badge bg-info text-dark me-1" v-if="item.color_title">{{ item.color_title }}</span>
                      <span class="badge bg-secondary" v-if="item.size_title">{{ item.size_title }}</span>
                      <span v-if="!item.color_title && !item.size_title" class="text-muted small">Standard</span>
                    </td>
                    <td class="font-monospace small">{{ item.serial_no || 'N/A' }}</td>
                    <td class="text-center font-monospace fw-bold">{{ item.sold_qty }}</td>
                    <td class="text-center font-monospace">
                      <span class="badge bg-warning text-dark" v-if="item.already_returned_qty > 0">{{ item.already_returned_qty }} Returned</span>
                      <span class="text-muted" v-else>0</span>
                    </td>
                    <td class="text-center font-monospace">
                      <span class="badge bg-success" v-if="item.remaining_returnable_qty > 0">{{ item.remaining_returnable_qty }} Pcs</span>
                      <span class="badge bg-danger" v-else>Fully Returned</span>
                    </td>
                    <td class="text-end font-monospace">Tk. {{ formatPrice(item.rate) }}</td>
                    <td>
                      <input
                        type="number"
                        min="0"
                        :max="item.remaining_returnable_qty"
                        class="form-control form-control-sm text-center fw-bold"
                        :class="{ 'border-danger': item.return_qty > item.remaining_returnable_qty }"
                        v-model.number="item.return_qty"
                        :disabled="!item.selected || item.remaining_returnable_qty <= 0"
                        @input="onReturnQtyInput(item)"
                        @change="onReturnQtyInput(item)"
                      >
                    </td>
                    <td class="text-end font-monospace fw-bold text-danger">
                      Tk. {{ formatPrice(item.selected ? (item.return_qty * item.rate) : 0) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Total Refund Summary & Action Buttons -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 bg-light border rounded gap-3">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-bold fs-5 text-dark">Total Refund Amount (ফেরত মূল্য):</span>
                <span class="fw-bold fs-4 text-danger font-monospace">Tk. {{ formatPrice(totalRefundAmount) }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary px-4 fw-bold" @click="selectedInvoice = null">
                  <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" @click="submitReturn" :disabled="totalRefundAmount <= 0 || isSubmitting">
                  <i class="fas fa-check-circle"></i>
                  <span>{{ isSubmitting ? 'Processing...' : 'Process Sales Return & Restore Stock' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      searchTerm: '',
      invoices: [],
      selectedInvoice: null,
      returnList: [],
      isSubmitting: false,
    };
  },
  computed: {
    totalRefundAmount() {
      return this.returnList.reduce((sum, item) => {
        if (item.selected && item.return_qty > 0) {
          return sum + (floatval(item.return_qty) * floatval(item.rate));
        }
        return sum;
      }, 0);
    }
  },
  methods: {
    formatPrice(val) {
      const f = parseFloat(val);
      return isNaN(f) ? '0.00' : f.toFixed(2);
    },
    searchInvoice() {
      if (!this.searchTerm || this.searchTerm.trim() === '') return;
      axios.get('pos/search-invoices-return', { params: { term: this.searchTerm.trim() } })
        .then(res => {
          this.invoices = res.data || [];
          this.selectedInvoice = null;
          if (this.invoices.length === 0) {
            this.$toast('No matching invoice found', 'warning');
          }
        });
    },
    selectInvoice(inv) {
      this.selectedInvoice = inv;
      this.returnList = (inv.details || []).map(d => {
        const soldQty = floatval(d.qty);
        const alreadyReturned = floatval(d.already_returned_qty || 0);
        const maxReturnable = d.remaining_returnable_qty !== undefined ? floatval(d.remaining_returnable_qty) : Math.max(0, soldQty - alreadyReturned);

        return {
          invoice_detail_id: d.id,
          item_id: d.item_id,
          title: d.item ? d.item.title : 'Item',
          barcode: d.item ? d.item.barcode : '',
          color_id: d.color_id,
          color_title: d.color ? d.color.title : null,
          size_id: d.size_id,
          size_title: d.size ? d.size.title : null,
          serial_no: d.serial_no,
          sold_qty: soldQty,
          already_returned_qty: alreadyReturned,
          remaining_returnable_qty: maxReturnable,
          rate: floatval(d.amount),
          return_qty: 0,
          selected: false,
        };
      });
    },
    onToggleItemSelect(item) {
      if (item.selected) {
        if (item.return_qty <= 0) {
          item.return_qty = item.remaining_returnable_qty;
        }
      } else {
        item.return_qty = 0;
      }
    },
    onReturnQtyInput(item) {
      let qty = floatval(item.return_qty);
      if (isNaN(qty) || qty < 0) {
        item.return_qty = 0;
        return;
      }

      if (qty > item.remaining_returnable_qty) {
        this.$toast(`সর্বোচ্চ ফেরতযোগ্য পরিমাণ ${item.remaining_returnable_qty}! ক্রয়কৃত বা অবশিষ্ট পরিমাণের চেয়ে বেশি ফেরত দেওয়া সম্ভব নয়।`, 'warning');
        item.return_qty = item.remaining_returnable_qty;
      }
    },
    submitReturn() {
      // Validate item selections and quantities
      const selectedItems = this.returnList.filter(i => i.selected);

      if (selectedItems.length === 0) {
        this.$toast('Please select at least one item to return', 'warning');
        return;
      }

      for (const item of selectedItems) {
        const qty = floatval(item.return_qty);
        if (qty <= 0) {
          this.$toast(`'${item.title}' পণ্যের জন্য ফেরত পরিমাণ ১ বা তার বেশি হতে হবে`, 'warning');
          return;
        }
        if (qty > item.remaining_returnable_qty) {
          this.$toast(`ভুল পরিমাণ! '${item.title}' পণ্যের ফেরতযোগ্য সর্বোচ্চ পরিমাণ ${item.remaining_returnable_qty}`, 'danger');
          item.return_qty = item.remaining_returnable_qty;
          return;
        }
      }

      if (this.totalRefundAmount <= 0) {
        this.$toast('Please specify a valid return quantity', 'warning');
        return;
      }

      const returnItems = selectedItems.map(i => ({
        item_id: i.item_id,
        color_id: i.color_id,
        size_id: i.size_id,
        qty: floatval(i.return_qty),
        rate: floatval(i.rate),
      }));

      this.isSubmitting = true;

      axios.post('pos/process-return', {
        invoice_id: this.selectedInvoice.id,
        return_items: returnItems,
      })
      .then(res => {
        this.isSubmitting = false;
        if (res.data && res.data.success) {
          this.$toast(res.data.message || 'Sales return processed successfully!', 'success');
          this.selectedInvoice = null;
          this.invoices = [];
          this.searchTerm = '';
        }
      })
      .catch(err => {
        this.isSubmitting = false;
        this.$toast(err.response?.data?.exception || err.response?.data?.message || 'Failed to process return', 'danger');
      });
    }
  }
};

function floatval(val) {
  const f = parseFloat(val);
  return isNaN(f) ? 0 : f;
}
</script>

<style scoped>
.search-box-wrapper {
  position: relative;
  display: flex;
  width: 100%;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(17, 44, 71, 0.08);
}

.search-input {
  height: 44px !important;
  border-radius: 8px 0 0 8px !important;
  border: 1.5px solid #112C47 !important;
  border-right: none !important;
  font-size: 15px !important;
  font-weight: 600;
  padding: 0 16px !important;
  flex: 1;
  color: #112C47 !important;
}

.search-input:focus {
  border-color: #112C47 !important;
  box-shadow: none !important;
}

.search-btn {
  height: 44px !important;
  border-radius: 0 8px 8px 0 !important;
  background-color: #112C47 !important;
  border: 1.5px solid #112C47 !important;
  color: #ffffff !important;
  font-weight: 700;
  font-size: 14px;
  padding: 0 26px !important;
  white-space: nowrap;
  transition: all 0.2s ease-in-out;
}

.search-btn:hover {
  background-color: #1a3d61 !important;
  border-color: #1a3d61 !important;
  color: #ffffff !important;
}

.invoice-select-card {
  transition: all 0.2s ease-in-out;
}
.invoice-select-card:hover {
  border-color: #112C47 !important;
  box-shadow: 0 4px 12px rgba(17, 44, 71, 0.15) !important;
  transform: translateY(-2px);
}
.btn-pos-sale {
  background-color: #112C47;
  color: #ffffff;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  padding: 3px 16px 3px 4px;
  text-decoration: none;
  box-shadow: 0 2px 5px rgba(17, 44, 71, 0.2);
  transition: all 0.2s ease-in-out;
}
.btn-pos-sale span {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background-color: #ffffff;
  color: #112C47;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 8px;
  font-size: 13px;
}
.btn-pos-sale:hover {
  background-color: #1a3d61;
  color: #ffffff;
  transform: translateY(-1px);
}
</style>
