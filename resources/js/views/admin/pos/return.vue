<template>
  <div class="container-fluid p-3">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-dark text-white py-2 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="fas fa-undo me-2"></i>Sales Return System (পণ্য ফেরত ব্যবস্থাপনা)</h5>
        <router-link to="/pos" class="btn btn-sm btn-warning font-monospace fw-bold">
          <i class="fas fa-cash-register me-1"></i> Back to POS
        </router-link>
      </div>
      <div class="card-body p-4">
        <!-- Invoice Search Bar -->
        <div class="row mb-4">
          <div class="col-md-6 mx-auto">
            <label class="form-label fw-bold text-muted">Search Invoice No / Client Mobile (ইনভয়েস বা মোবাইল নম্বর দিয়ে খুঁজুন)</label>
            <div class="input-group input-group-lg">
              <input type="text" class="form-control fw-bold" placeholder="e.g. POS-20260817-0001 or 017xxxxxxxx" v-model="searchTerm" @keyup.enter="searchInvoice">
              <button type="button" class="btn btn-primary px-4 fw-bold" @click="searchInvoice">
                <i class="fas fa-search me-1"></i> Search Invoice
              </button>
            </div>
          </div>
        </div>

        <!-- Invoice Results List -->
        <div v-if="invoices.length > 0 && !selectedInvoice" class="row g-3 mb-4">
          <div class="col-12">
            <h6 class="fw-bold text-dark border-bottom pb-2">Matching Invoices ({{ invoices.length }})</h6>
          </div>
          <div v-for="inv in invoices" :key="inv.id" class="col-md-6">
            <div class="card border shadow-sm h-100 hover-border-primary cursor-pointer" @click="selectInvoice(inv)">
              <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="fw-bold text-primary font-monospace mb-1">{{ inv.invoice_no }}</h6>
                  <div class="small text-dark fw-bold mb-1">{{ inv.client ? inv.client.name : 'Walk-in Customer' }} ({{ inv.client ? inv.client.mobile : 'N/A' }})</div>
                  <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>Date: {{ inv.invoice_date }}</small>
                </div>
                <div class="text-end">
                  <div class="fw-bold text-success font-monospace fs-5">Tk. {{ formatPrice(inv.amount) }}</div>
                  <button type="button" class="btn btn-sm btn-outline-primary mt-2">Select for Return</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Invoice Return Processing Screen -->
        <div v-if="selectedInvoice" class="card border-primary shadow-sm mb-4">
          <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Invoice Details: {{ selectedInvoice.invoice_no }}</span>
            <button type="button" class="btn btn-sm btn-outline-light" @click="selectedInvoice = null">Change Invoice</button>
          </div>
          <div class="card-body p-3">
            <div class="row mb-3 p-2 bg-light rounded border">
              <div class="col-md-4"><strong>Client:</strong> {{ selectedInvoice.client ? selectedInvoice.client.name : 'Walk-in' }} ({{ selectedInvoice.client ? selectedInvoice.client.mobile : 'N/A' }})</div>
              <div class="col-md-4"><strong>Date:</strong> {{ selectedInvoice.invoice_date }}</div>
              <div class="col-md-4 text-end"><strong>Total Invoice Amount:</strong> <span class="fw-bold text-success font-monospace">Tk. {{ formatPrice(selectedInvoice.amount) }}</span></div>
            </div>

            <!-- Return Item Table -->
            <div class="table-responsive mb-3">
              <table class="table table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th width="5%" class="text-center">Select</th>
                    <th>Item Title</th>
                    <th>Color / Size</th>
                    <th>Serial No</th>
                    <th width="12%" class="text-center">Sold Qty</th>
                    <th width="15%" class="text-end">Rate</th>
                    <th width="15%" class="text-center">Return Qty</th>
                    <th width="15%" class="text-end">Refund Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in returnList" :key="idx">
                    <td class="text-center">
                      <input type="checkbox" class="form-check-input" v-model="item.selected" style="transform: scale(1.2);">
                    </td>
                    <td>
                      <div class="fw-bold text-dark">{{ item.title }}</div>
                      <small class="text-muted font-monospace">{{ item.barcode }}</small>
                    </td>
                    <td>
                      <span class="badge bg-info text-dark me-1" v-if="item.color_title">{{ item.color_title }}</span>
                      <span class="badge bg-secondary" v-if="item.size_title">{{ item.size_title }}</span>
                    </td>
                    <td class="font-monospace small">{{ item.serial_no || 'N/A' }}</td>
                    <td class="text-center font-monospace fw-bold">{{ item.sold_qty }}</td>
                    <td class="text-end font-monospace">Tk. {{ formatPrice(item.rate) }}</td>
                    <td>
                      <input type="number" min="0" :max="item.sold_qty" class="form-control form-control-sm text-center fw-bold" v-model.number="item.return_qty" :disabled="!item.selected">
                    </td>
                    <td class="text-end font-monospace fw-bold text-danger">
                      Tk. {{ formatPrice(item.selected ? (item.return_qty * item.rate) : 0) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Total Refund Summary -->
            <div class="d-flex justify-content-between align-items-center p-3 bg-light border rounded mb-3">
              <span class="fw-bold fs-5 text-dark">Total Refund Amount (ফেরত মূল্য):</span>
              <span class="fw-bold fs-4 text-danger font-monospace">Tk. {{ formatPrice(totalRefundAmount) }}</span>
            </div>

            <button type="button" class="btn btn-danger btn-lg w-100 py-3 fw-bold shadow" @click="submitReturn" :disabled="totalRefundAmount <= 0 || isSubmitting">
              <i class="fas fa-check-circle me-1"></i> Process Sales Return & Restore Stock
            </button>
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
          return sum + (item.return_qty * item.rate);
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
      this.returnList = (inv.details || []).map(d => ({
        invoice_detail_id: d.id,
        item_id: d.item_id,
        title: d.item ? d.item.title : 'Item',
        barcode: d.item ? d.item.barcode : '',
        color_id: d.color_id,
        color_title: d.color ? d.color.title : null,
        size_id: d.size_id,
        size_title: d.size ? d.size.title : null,
        serial_no: d.serial_no,
        sold_qty: d.qty,
        rate: d.amount,
        return_qty: 0,
        selected: false,
      }));
    },
    submitReturn() {
      if (this.totalRefundAmount <= 0) {
        this.$toast('Please select at least one item to return', 'warning');
        return;
      }

      const returnItems = this.returnList
        .filter(i => i.selected && i.return_qty > 0)
        .map(i => ({
          item_id: i.item_id,
          color_id: i.color_id,
          size_id: i.size_id,
          qty: i.return_qty,
          rate: i.rate,
        }));

      this.isSubmitting = true;

      axios.post('pos/process-return', {
        invoice_id: this.selectedInvoice.id,
        return_items: returnItems,
      })
      .then(res => {
        this.isSubmitting = false;
        if (res.data && res.data.success) {
          this.$toast('Sales return processed successfully!', 'success');
          this.selectedInvoice = null;
          this.invoices = [];
          this.searchTerm = '';
        }
      })
      .catch(err => {
        this.isSubmitting = false;
        this.$toast(err.response?.data?.exception || 'Failed to process return', 'danger');
      });
    }
  }
};
</script>

<style scoped>
.hover-border-primary:hover {
  border-color: #0d6efd !important;
}
</style>
