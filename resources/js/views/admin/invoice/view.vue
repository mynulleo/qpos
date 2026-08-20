<template>
  <div class="container-fluid p-3">
    <!-- Top Action Navigation Bar -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <router-link to="/invoice" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Invoices
          </router-link>
          <div>
            <h4 class="mb-0 fw-bold text-dark font-monospace">
              <i class="fas fa-file-invoice text-primary me-2"></i>Invoice #{{ data.invoice_no }}
            </h4>
            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>Date: {{ data.invoice_date }} | Created: {{ data.created_at }}</small>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Print Thermal 80mm Receipt -->
          <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 font-monospace" @click="print('posInvoiceViewPrintArea', 'Invoice - ' + data.invoice_no)">
            <i class="fas fa-receipt"></i> Print Thermal Receipt (80mm)
          </button>

          <!-- Print Full A4 Bill -->
          <router-link v-if="data.id" :to="{ name: 'invoice.bill', params: { id: data.id } }" class="btn btn-dark btn-sm d-flex align-items-center gap-1 font-monospace">
            <i class="fas fa-print"></i> Print Full A4 Bill
          </router-link>
        </div>
      </div>
    </div>

    <!-- Invoice Main Overview Row -->
    <div class="row g-3 mb-3">
      <!-- Invoice Summary Card -->
      <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header text-white py-2 d-flex align-items-center justify-content-between" style="background-color: #112C47;">
            <span class="fw-bold"><i class="fas fa-info-circle me-2"></i>Invoice Details (ইনভয়েস বিবরণী)</span>
            <span class="badge font-monospace" :class="getPaymentStatusBadge(data)">{{ data.payment_status }}</span>
          </div>
          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 13px;">
                <tbody>
                  <tr class="border-bottom">
                    <th width="40%">Invoice No:</th>
                    <td class="font-monospace fw-bold text-primary">{{ data.invoice_no }}</td>
                  </tr>
                  <tr class="border-bottom">
                    <th>Invoice Date:</th>
                    <td>{{ data.invoice_date }}</td>
                  </tr>
                  <tr class="border-bottom">
                    <th>Subtotal Amount:</th>
                    <td class="font-monospace">Tk. {{ formatPrice(data.original_amount) }}</td>
                  </tr>
                  <tr class="border-bottom" v-if="data.discount > 0">
                    <th>Discount (ছাড়):</th>
                    <td class="font-monospace text-danger">- Tk. {{ formatPrice(data.discount) }}</td>
                  </tr>
                  <tr class="border-bottom" v-if="data.vat > 0">
                    <th>VAT / Tax:</th>
                    <td class="font-monospace">+ Tk. {{ formatPrice(data.vat) }}</td>
                  </tr>
                  <tr class="border-bottom bg-light">
                    <th class="text-dark fs-6 fw-bold">Net Total Payable:</th>
                    <td class="font-monospace fs-6 fw-bold text-dark">Tk. {{ formatPrice(data.amount) }}</td>
                  </tr>
                  <tr class="border-bottom">
                    <th class="text-success fw-bold">Paid Amount (পরিশোধ):</th>
                    <td class="font-monospace fw-bold text-success">Tk. {{ formatPrice(data.paid_amount) }}</td>
                  </tr>
                  <tr v-if="data.due_amount > 0">
                    <th class="text-danger fw-bold">Due Amount (বকেয়া):</th>
                    <td class="font-monospace fw-bold text-danger">Tk. {{ formatPrice(data.due_amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Client Information & Short Lifetime History Card -->
      <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header text-white py-2 d-flex align-items-center justify-content-between" style="background-color: #112C47;">
            <span class="fw-bold"><i class="fas fa-user me-2"></i>Customer & History (গ্রাহকের বিবরণী)</span>
            <span class="badge bg-secondary font-monospace" v-if="data.client">{{ data.client.clientid || 'Registered' }}</span>
            <span class="badge bg-secondary font-monospace" v-else>Walk-in</span>
          </div>
          <div class="card-body p-3">
            <div v-if="data.client">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                  <h5 class="fw-bold text-dark mb-0">{{ data.client.name }}</h5>
                  <div class="text-muted small font-monospace"><i class="fas fa-phone-alt me-1 text-primary"></i>{{ data.client.mobile }}</div>
                </div>
                <div class="text-end" v-if="data.client.address && data.client.address !== 'N/A'">
                  <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i>{{ data.client.address }}</small>
                </div>
              </div>

              <!-- ⭐️ Customer Lifetime History Grid -->
              <div class="p-2 bg-light rounded border mb-2" v-if="data.client_history">
                <div class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 11px;">
                  <i class="fas fa-history me-1 text-primary"></i>Client Lifetime History (গ্রাহকের মোট ইতিহাস)
                </div>
                <div class="row g-2 text-center" style="font-size: 12px;">
                  <div class="col-3 border-end">
                    <span class="text-muted d-block" style="font-size: 10px;">Total Invoices</span>
                    <strong class="font-monospace fs-6 text-primary">{{ data.client_history.total_orders }}</strong>
                  </div>
                  <div class="col-3 border-end">
                    <span class="text-muted d-block" style="font-size: 10px;">Lifetime Sales</span>
                    <strong class="font-monospace text-dark">Tk. {{ formatPrice(data.client_history.lifetime_sales) }}</strong>
                  </div>
                  <div class="col-3 border-end">
                    <span class="text-muted d-block" style="font-size: 10px;">Lifetime Paid</span>
                    <strong class="font-monospace text-success">Tk. {{ formatPrice(data.client_history.lifetime_paid) }}</strong>
                  </div>
                  <div class="col-3">
                    <span class="text-muted d-block" style="font-size: 10px;">Current Due</span>
                    <strong class="font-monospace" :class="data.client_history.current_due > 0 ? 'text-danger' : 'text-muted'">
                      Tk. {{ formatPrice(data.client_history.current_due) }}
                    </strong>
                  </div>
                </div>
              </div>

              <!-- ⭐️ Customer Loyalty Reward Points (If Coupon System is Enabled) -->
              <div v-if="data.loyalty_points && data.loyalty_points.coupon_enabled" class="p-2 bg-warning bg-opacity-10 border border-warning rounded">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-bold text-dark d-flex align-items-center gap-1">
                    <i class="fas fa-gift text-warning"></i> Loyalty Reward Points
                  </span>
                  <span class="badge bg-warning text-dark font-monospace fw-bold">
                    Balance: {{ formatPrice(data.loyalty_points.points_balance) }} Pts (≈ Tk. {{ formatPrice(data.loyalty_points.points_value_in_tk) }})
                  </span>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                  <span>Earned in this invoice: <strong class="text-success font-monospace">+{{ data.loyalty_points.points_earned_this_invoice }} Pts</strong></span>
                  <span>Redeemed: <strong class="text-danger font-monospace">-{{ data.loyalty_points.points_redeemed_this_invoice }} Pts</strong></span>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-4 text-muted">
              <i class="fas fa-walking fa-2x mb-2 text-secondary opacity-50"></i>
              <p class="mb-0 small">This invoice was issued to a Walk-in Customer (ডিফল্ট ক্রেতা).</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ⭐️ Item Details Table with Requested Live Stock & Lifetime Sold Analytics -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header text-white py-2 d-flex align-items-center justify-content-between" style="background-color: #112C47;">
        <span class="fw-bold"><i class="fas fa-box-open me-2 text-warning"></i>Purchased Items & Live Stock Analytics (পণ্যের বিবরণ ও বর্তমান স্টক)</span>
        <span class="badge bg-secondary font-monospace">{{ data.details ? data.details.length : 0 }} Items</span>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped align-middle mb-0" style="font-size: 13px;">
          <thead class="table-light">
            <tr>
              <th width="4%" class="text-center">#</th>
              <th width="24%">Product & Barcode</th>
              <th width="14%">Variant / Spec</th>
              <th width="12%">Category / Unit</th>
              <th width="8%" class="text-center">Sold Qty</th>
              <th width="11%" class="text-end">Unit Rate (দর)</th>
              <th width="11%" class="text-end">Total Price</th>
              <!-- ⭐️ Requested Item Insights -->
              <th width="8%" class="text-center">Present Stock</th>
              <th width="8%" class="text-center">Total Sold</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in data.details" :key="item.id">
              <td class="text-center text-muted">{{ idx + 1 }}</td>
              <td>
                <div class="fw-bold text-dark">{{ item.title }}</div>
                <small class="text-muted font-monospace"><i class="fas fa-barcode me-1"></i>{{ item.barcode }}</small>
                <div v-if="item.serial_no" class="small text-primary font-monospace">S/N: {{ item.serial_no }}</div>
                <div v-if="item.warranty_type && item.warranty_type !== 'none'" class="small text-success fw-bold">
                  <i class="fas fa-shield-alt me-1"></i>{{ item.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}: {{ item.warranty_period }}
                </div>
              </td>
              <td>
                <span class="badge bg-info text-dark me-1" v-if="item.color_title">{{ item.color_title }}</span>
                <span class="badge bg-secondary me-1" v-if="item.size_title">{{ item.size_title }}</span>
                <span v-if="!item.color_title && !item.size_title" class="text-muted small">Standard</span>
              </td>
              <td>
                <div>{{ item.category_title }}</div>
                <small class="text-muted font-monospace">{{ item.unit_title }}</small>
              </td>
              <td class="text-center font-monospace fw-bold fs-6">{{ item.qty }}</td>
              <td class="text-end font-monospace">Tk. {{ formatPrice(item.amount) }}</td>
              <td class="text-end font-monospace fw-bold text-primary fs-6">Tk. {{ formatPrice(item.total_amount) }}</td>

              <!-- ⭐️ 1. Item Present Stock -->
              <td class="text-center">
                <span class="badge font-monospace" :class="'bg-' + item.stock_badge">
                  {{ item.present_stock }} {{ item.unit_title }}
                </span>
                <small class="text-muted d-block" style="font-size: 10px;" v-if="item.overall_stock !== item.present_stock">
                  (All: {{ item.overall_stock }})
                </small>
              </td>

              <!-- ⭐️ 2. Lifetime Total Sold Units -->
              <td class="text-center">
                <span class="badge bg-dark font-monospace">
                  {{ item.total_sold_qty }} Sold
                </span>
              </td>
            </tr>

            <tr v-if="!data.details || data.details.length === 0">
              <td colspan="9" class="text-center py-4 text-muted">No item details found for this invoice.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sales Returns Section (If items were returned against this invoice) -->
    <div class="card border-0 shadow-sm mb-3" v-if="data.returns && data.returns.length > 0">
      <div class="card-header bg-danger text-white py-2">
        <span class="fw-bold"><i class="fas fa-undo me-2"></i>Processed Sales Returns (পণ্য ফেরত সংক্রান্ত তথ্য)</span>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
          <thead class="table-light">
            <tr>
              <th>Return Date</th>
              <th>Product</th>
              <th>Color / Size</th>
              <th class="text-center">Returned Qty</th>
              <th>Reference</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ret in data.returns" :key="ret.id">
              <td>{{ ret.transaction_date }}</td>
              <td class="fw-bold">{{ ret.item ? ret.item.title : 'Item' }}</td>
              <td>
                <span class="badge bg-info text-dark me-1" v-if="ret.color">{{ ret.color.title }}</span>
                <span class="badge bg-secondary" v-if="ret.size">{{ ret.size.title }}</span>
              </td>
              <td class="text-center font-monospace fw-bold text-danger">{{ ret.qty_in }} Pcs</td>
              <td class="text-muted small">Sales Return Restored to Inventory</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Hidden Printable POS Thermal Receipt Area -->
    <div id="posInvoiceViewPrintArea" class="d-none">
      <div style="width: 80mm; font-family: monospace; font-size: 11px; line-height: 1.3; padding: 5px; margin: 0 auto; color: #000;">
        <div style="text-align: center; margin-bottom: 8px;">
          <h2 style="font-size: 16px; font-weight: bold; margin: 0 0 3px 0;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
          <div style="font-size: 10px;">{{ $root.site?.address || '' }}</div>
          <div style="font-size: 10px;">Mob: {{ $root.site?.mobile1 || '' }}</div>
          <div style="font-size: 11px; font-weight: bold; margin-top: 4px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 2px 0;">
            SALES RECEIPT
          </div>
        </div>

        <div style="margin-bottom: 6px; font-size: 10px;">
          <div><strong>Inv #:</strong> {{ data.invoice_no }}</div>
          <div><strong>Date:</strong> {{ data.invoice_date }}</div>
          <div><strong>Customer:</strong> {{ data.client ? data.client.name : 'Walk-in Customer' }}</div>
          <div v-if="data.client && data.client.mobile"><strong>Mobile:</strong> {{ data.client.mobile }}</div>
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
            <tr v-for="d in data.details" :key="d.id" style="border-bottom: 1px dashed #ccc;">
              <td style="padding: 3px 0;">
                <div>{{ d.title }}</div>
                <div style="font-size: 9px; color: #444;" v-if="d.color_title || d.size_title">
                  {{ d.color_title ? d.color_title : '' }} {{ d.size_title ? '/' + d.size_title : '' }}
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
            <span>Tk. {{ formatPrice(data.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="data.discount > 0">
            <span>Discount:</span>
            <span>- Tk. {{ formatPrice(data.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="data.vat > 0">
            <span>VAT:</span>
            <span>+ Tk. {{ formatPrice(data.vat) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 3px;">
            <span>Net Payable:</span>
            <span>Tk. {{ formatPrice(data.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid Amount:</span>
            <span>Tk. {{ formatPrice(data.paid_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="data.due_amount > 0">
            <span>Due Amount:</span>
            <span>Tk. {{ formatPrice(data.due_amount) }}</span>
          </div>

          <!-- Loyalty Points in Receipt if Enabled -->
          <div v-if="data.loyalty_points && data.loyalty_points.coupon_enabled" style="margin-top: 4px; border-top: 1px dashed #000; padding-top: 3px; font-size: 9px;">
            <div style="display: flex; justify-content: space-between;" v-if="data.loyalty_points.points_redeemed_this_invoice > 0">
              <span>Points Redeemed:</span>
              <span>- {{ data.loyalty_points.points_redeemed_this_invoice }} Pts</span>
            </div>
            <div style="display: flex; justify-content: space-between;" v-if="data.loyalty_points.points_earned_this_invoice > 0">
              <span>Points Earned:</span>
              <span>+ {{ data.loyalty_points.points_earned_this_invoice }} Pts</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
              <span>Points Balance:</span>
              <span>{{ formatPrice(data.loyalty_points.points_balance) }} Pts</span>
            </div>
          </div>
        </div>

        <div style="text-align: center; margin-top: 12px; border-top: 1px dashed #000; padding-top: 6px; font-size: 9px;">
          <div>Thank you for shopping with us!</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      data: {},
      loading: true,
    };
  },
  methods: {
    formatPrice(val) {
      const f = parseFloat(val);
      return isNaN(f) ? '0.00' : f.toFixed(2);
    },
    getPaymentStatusBadge(inv) {
      if (inv.payment_status === 'Paid') return 'bg-success';
      if (inv.payment_status === 'Partial') return 'bg-warning text-dark';
      return 'bg-danger';
    },
    loadInvoice() {
      const id = this.$route.params.id;
      if (!id) return;

      this.loading = true;
      axios.get(`invoice/${id}`)
        .then(res => {
          this.loading = false;
          this.data = res.data || {};
        })
        .catch(err => {
          this.loading = false;
          this.$toast(err.response?.data?.message || 'Failed to load invoice', 'danger');
        });
    }
  },
  mounted() {
    this.loadInvoice();
  }
};
</script>

<style scoped>
</style>