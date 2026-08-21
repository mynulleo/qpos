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
          <!-- Print Dynamic Receipt / Invoice -->
          <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 font-monospace" @click="printReceipt">
            <i class="fas fa-receipt"></i> Print Receipt / Invoice
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

    <!-- Hidden Printable Dynamic Invoice Area (Thermal 80mm, 60mm, Normal A5, Normal A4) -->
    <div id="invoiceViewPrintArea" class="d-none">
      <!-- 1. 🖨️ Thermal 80mm Layout (3-Inch Standard Receipt) -->
      <div v-if="effectivePrintFormat === 'thermal-80mm'" class="thermal-80mm-invoice" style="width: 78mm; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; line-height: 1.35; padding: 4px; margin: 0 auto; color: #000;">
        <div style="text-align: center; margin-bottom: 8px;">
          <h2 style="font-size: 16px; font-weight: bold; margin: 0 0 2px 0; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
          <div style="font-size: 10px;">{{ $root.site?.address || '' }}</div>
          <div style="font-size: 10px;">Mob: {{ $root.site?.mobile1 || '' }} <span v-if="$root.site?.mobile2">/ {{ $root.site?.mobile2 }}</span></div>
          <div style="font-size: 9px;" v-if="$root.site?.contact_email">Email: {{ $root.site?.contact_email }}</div>
          <div style="font-size: 9px;" v-if="$root.site?.bin_no">BIN: {{ $root.site?.bin_no }}</div>
          <div style="font-size: 11.5px; font-weight: bold; margin-top: 4px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 2px 0; letter-spacing: 1px;">
            SALES INVOICE
          </div>
        </div>

        <div style="margin-bottom: 6px; font-size: 10px; line-height: 1.3;">
          <div style="display: flex; justify-content: space-between;">
            <span><strong>Inv:</strong> #{{ data.invoice_no }}</span>
            <span><strong>Date:</strong> {{ data.invoice_date }}</span>
          </div>
          <div><strong>Customer:</strong> {{ data.client ? data.client.name : 'Walk-in Customer' }}</div>
          <div v-if="data.client && data.client.mobile"><strong>Mobile:</strong> {{ data.client.mobile }}</div>
          <div><strong>Payment:</strong> {{ data.payment_method || 'Cash' }}</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 10px;">
          <thead>
            <tr style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
              <th style="text-align: left; padding: 3px 0; width: 48%;">Item</th>
              <th style="text-align: center; padding: 3px 0; width: 14%;">Qty</th>
              <th style="text-align: right; padding: 3px 0; width: 18%;">Rate</th>
              <th style="text-align: right; padding: 3px 0; width: 20%;">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in data.details" :key="d.id" style="border-bottom: 1px dashed #ddd;">
              <td style="padding: 3px 0;">
                <div style="font-weight: 600;">{{ d.title }}</div>
                <div style="font-size: 9px; color: #444;" v-if="d.color_title || d.size_title">
                  {{ d.color_title ? d.color_title : '' }} {{ d.size_title ? '/' + d.size_title : '' }}
                </div>
                <div style="font-size: 9px; color: #333;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
              </td>
              <td style="text-align: center; padding: 3px 0; vertical-align: top;">{{ d.qty }}</td>
              <td style="text-align: right; padding: 3px 0; vertical-align: top;">{{ formatPrice(d.amount) }}</td>
              <td style="text-align: right; padding: 3px 0; vertical-align: top; font-weight: bold;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <div style="border-top: 1px solid #000; padding-top: 4px; font-size: 10px; line-height: 1.35;">
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
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 3px; border-top: 1px dashed #000; padding-top: 3px;">
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
        </div>

        <div style="text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px; font-size: 9px;">
          <div>Thank you for shopping with us!</div>
        </div>
      </div>

      <!-- 2. 🖨️ Thermal 60mm Layout (Compact 2-Inch Mini Receipt) -->
      <div v-else-if="effectivePrintFormat === 'thermal-60mm'" class="thermal-60mm-invoice" style="width: 56mm; font-family: monospace, Arial; font-size: 9.5px; line-height: 1.25; padding: 2px; margin: 0 auto; color: #000;">
        <div style="text-align: center; margin-bottom: 5px;">
          <h2 style="font-size: 13px; font-weight: bold; margin: 0 0 1px 0; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
          <div style="font-size: 8.5px;">{{ $root.site?.address || '' }}</div>
          <div style="font-size: 8.5px;">Mob: {{ $root.site?.mobile1 || '' }}</div>
          <div style="font-size: 10px; font-weight: bold; margin-top: 3px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 2px 0;">
            SALES RECEIPT
          </div>
        </div>

        <div style="margin-bottom: 4px; font-size: 8.5px; line-height: 1.2;">
          <div><strong>Inv:</strong> #{{ data.invoice_no }}</div>
          <div><strong>Date:</strong> {{ data.invoice_date }}</div>
          <div><strong>Cust:</strong> {{ data.client ? data.client.name : 'Walk-in' }}</div>
          <div v-if="data.client?.mobile"><strong>Ph:</strong> {{ data.client.mobile }}</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8.5px;">
          <thead>
            <tr style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
              <th style="text-align: left; padding: 2px 0;">Item</th>
              <th style="text-align: center; padding: 2px 0;">Qty</th>
              <th style="text-align: right; padding: 2px 0;">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in data.details" :key="d.id" style="border-bottom: 1px dashed #ddd;">
              <td style="padding: 2px 0;">
                <div>{{ d.title }}</div>
                <div style="font-size: 8px; color: #555;" v-if="d.serial_no">S/N: {{ d.serial_no }}</div>
              </td>
              <td style="text-align: center; padding: 2px 0; vertical-align: top;">{{ d.qty }}</td>
              <td style="text-align: right; padding: 2px 0; vertical-align: top;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <div style="border-top: 1px solid #000; padding-top: 3px; font-size: 8.5px; line-height: 1.2;">
          <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>{{ formatPrice(data.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="data.discount > 0">
            <span>Discount:</span>
            <span>-{{ formatPrice(data.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 10px; margin-top: 2px; border-top: 1px dashed #000; padding-top: 2px;">
            <span>Payable:</span>
            <span>Tk. {{ formatPrice(data.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid:</span>
            <span>{{ formatPrice(data.paid_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="data.due_amount > 0">
            <span>Due:</span>
            <span>{{ formatPrice(data.due_amount) }}</span>
          </div>
        </div>

        <div style="text-align: center; margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px; font-size: 8px;">
          <div>Thanks for visiting!</div>
        </div>
      </div>

      <!-- 3. 🖨️ Normal Printer A5 Layout (Compact Half-Page Invoice) -->
      <div v-else-if="effectivePrintFormat === 'normal-a5'" class="normal-a5-invoice" style="width: 100%; max-width: 138mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 10.5px; line-height: 1.35; color: #111; margin: 0 auto; padding: 6px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #112C47; padding-bottom: 8px; margin-bottom: 8px;">
          <div>
            <h2 style="font-size: 16px; font-weight: bold; margin: 0; color: #112C47;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
            <div style="font-size: 10px; color: #444;">{{ $root.site?.address || '' }}</div>
            <div style="font-size: 10px; color: #444;">Phone: {{ $root.site?.mobile1 || '' }} | Email: {{ $root.site?.contact_email || '' }}</div>
          </div>
          <div style="text-align: right;">
            <div style="display: inline-block; background: #112C47; color: #fff; font-size: 11px; font-weight: bold; padding: 2px 10px; border-radius: 3px;">
              SALES INVOICE
            </div>
            <div style="font-size: 11px; font-weight: bold; margin-top: 4px; font-family: monospace;">#{{ data.invoice_no }}</div>
            <div style="font-size: 9.5px; color: #555;">Date: {{ data.invoice_date }}</div>
          </div>
        </div>

        <!-- Customer Box -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-bottom: 8px; font-size: 10px;">
          <div>
            <strong>Bill To (গ্রাহক):</strong>
            <div style="font-weight: 600; font-size: 11px;">{{ data.client ? data.client.name : 'Walk-in Customer' }}</div>
            <div v-if="data.client?.mobile">Mobile: {{ data.client.mobile }}</div>
            <div v-if="data.client?.address">Address: {{ data.client.address }}</div>
          </div>
          <div style="text-align: right;">
            <div><strong>Payment Mode:</strong> {{ data.payment_method || 'Cash' }}</div>
            <div><strong>Status:</strong> <span style="font-weight: bold; color: #16a34a;">PAID</span></div>
          </div>
        </div>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10px;">
          <thead>
            <tr style="background: #112C47; color: #fff;">
              <th style="padding: 4px 6px; text-align: center; width: 25px;">#</th>
              <th style="padding: 4px 6px; text-align: left;">Item Description</th>
              <th style="padding: 4px 6px; text-align: center; width: 35px;">Qty</th>
              <th style="padding: 4px 6px; text-align: right; width: 55px;">Rate</th>
              <th style="padding: 4px 6px; text-align: right; width: 65px;">Total (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, idx) in data.details" :key="d.id" style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 4px; text-align: center;">{{ idx + 1 }}</td>
              <td style="padding: 4px 6px;">
                <div style="font-weight: 600;">{{ d.title }}</div>
                <div style="font-size: 9px; color: #475569;" v-if="d.color_title || d.size_title">
                  Variant: {{ d.color_title ? d.color_title : '' }} {{ d.size_title ? '/' + d.size_title : '' }}
                </div>
                <div style="font-size: 9px; color: #0284c7; font-family: monospace;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
              </td>
              <td style="padding: 4px; text-align: center; font-weight: bold;">{{ d.qty }}</td>
              <td style="padding: 4px 6px; text-align: right; font-family: monospace;">{{ formatPrice(d.amount) }}</td>
              <td style="padding: 4px 6px; text-align: right; font-weight: bold; font-family: monospace;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Totals & Terms -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
          <div style="width: 52%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; font-size: 8.5px; color: #64748b;">
            <div>* Goods once sold cannot be returned without original invoice.</div>
            <div>* Physical or liquid damage voids all warranty policies.</div>
          </div>

          <div style="width: 44%;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
              <tbody>
                <tr>
                  <td style="padding: 2px 4px;">Subtotal:</td>
                  <td style="padding: 2px 4px; text-align: right; font-family: monospace;">৳ {{ formatPrice(data.original_amount) }}</td>
                </tr>
                <tr v-if="data.discount > 0">
                  <td style="padding: 2px 4px; color: #dc2626;">Discount:</td>
                  <td style="padding: 2px 4px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(data.discount) }}</td>
                </tr>
                <tr style="border-top: 1px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 11px;">
                  <td style="padding: 4px;">Net Payable:</td>
                  <td style="padding: 4px; text-align: right; color: #112C47; font-family: monospace;">৳ {{ formatPrice(data.amount) }}</td>
                </tr>
                <tr>
                  <td style="padding: 2px 4px;">Paid Amount:</td>
                  <td style="padding: 2px 4px; text-align: right; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.paid_amount) }}</td>
                </tr>
                <tr v-if="data.due_amount > 0">
                  <td style="padding: 2px 4px; color: #dc2626; font-weight: bold;">Due Amount:</td>
                  <td style="padding: 2px 4px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.due_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Signatures -->
        <div style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 9px; color: #333;">
          <div style="border-top: 1px dashed #64748b; width: 35%; text-align: center; padding-top: 3px;">Customer's Signature</div>
          <div style="border-top: 1px dashed #64748b; width: 35%; text-align: center; padding-top: 3px;">Authorized Signature</div>
        </div>
      </div>

      <!-- 4. 🖨️ Normal Printer A4 Layout (Full Corporate Tax Invoice) -->
      <div v-else class="normal-a4-invoice" style="width: 100%; max-width: 190mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #111; margin: 0 auto; padding: 10px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #112C47; padding-bottom: 12px; margin-bottom: 14px;">
          <div>
            <h1 style="font-size: 24px; font-weight: bold; margin: 0 0 4px 0; color: #112C47; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h1>
            <div style="font-size: 11px; color: #475569; max-width: 380px;">{{ $root.site?.address || '' }}</div>
            <div style="font-size: 11px; color: #475569; margin-top: 2px;">
              <span><strong>Phone:</strong> {{ $root.site?.mobile1 || '' }} <span v-if="$root.site?.mobile2">/ {{ $root.site?.mobile2 }}</span></span>
              <span v-if="$root.site?.contact_email" style="margin-left: 10px;"><strong>Email:</strong> {{ $root.site?.contact_email }}</span>
            </div>
            <div style="font-size: 11px; color: #475569; margin-top: 2px;" v-if="$root.site?.bin_no">
              <strong>BIN / VAT Reg:</strong> {{ $root.site?.bin_no }}
            </div>
          </div>
          <div style="text-align: right;">
            <div style="display: inline-block; background: #112C47; color: #fff; font-size: 14px; font-weight: bold; padding: 4px 14px; border-radius: 4px; letter-spacing: 0.5px;">
              TAX INVOICE
            </div>
            <div style="font-size: 16px; font-weight: bold; margin-top: 6px; font-family: monospace; color: #112C47;">#{{ data.invoice_no }}</div>
            <div style="font-size: 11px; color: #64748b;"><strong>Date:</strong> {{ data.invoice_date }}</div>
            <div style="font-size: 11px; color: #64748b;"><strong>Payment Mode:</strong> {{ data.payment_method || 'Cash' }}</div>
          </div>
        </div>

        <!-- Customer Box -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
          <div style="width: 58%;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 3px;">Invoice To (গ্রাহক):</div>
            <div style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ data.client ? data.client.name : 'Walk-in Customer' }}</div>
            <div style="font-size: 11.5px; color: #334155; margin-top: 2px;" v-if="data.client?.mobile">
              <strong>Phone:</strong> {{ data.client.mobile }}
            </div>
            <div style="font-size: 11px; color: #475569; margin-top: 2px;" v-if="data.client?.address">
              <strong>Address:</strong> {{ data.client.address }}
            </div>
          </div>
          <div style="width: 38%; text-align: right; border-left: 1px solid #e2e8f0; padding-left: 12px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 3px;">Payment Status:</div>
            <div style="font-size: 13px; font-weight: bold; color: #16a34a;" v-if="data.amount <= data.paid_amount">PAID IN FULL</div>
            <div style="font-size: 13px; font-weight: bold; color: #dc2626;" v-else>DUE AMOUNT PENDING</div>
          </div>
        </div>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11.5px;">
          <thead>
            <tr style="background: #112C47; color: #fff;">
              <th style="padding: 6px 8px; text-align: center; width: 5%;">#</th>
              <th style="padding: 6px 8px; text-align: left; width: 45%;">Item Description</th>
              <th style="padding: 6px 8px; text-align: center; width: 12%;">Qty</th>
              <th style="padding: 6px 8px; text-align: right; width: 18%;">Unit Price</th>
              <th style="padding: 6px 8px; text-align: right; width: 20%;">Total (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in data.details" :key="item.id" style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 6px 8px; text-align: center; color: #64748b;">{{ idx + 1 }}</td>
              <td style="padding: 6px 8px;">
                <div style="font-weight: 600; color: #0f172a;">{{ item.title }}</div>
                <div style="font-size: 10px; color: #475569;" v-if="item.color_title || item.size_title">
                  Variant: {{ item.color_title ? item.color_title : '' }} {{ item.size_title ? '/' + item.size_title : '' }}
                </div>
                <div style="font-size: 10px; color: #0284c7; font-family: monospace;" v-if="item.serial_no">
                  Serial/IMEI: {{ item.serial_no }}
                </div>
              </td>
              <td style="padding: 6px 8px; text-align: center; font-weight: 600;">{{ item.qty }}</td>
              <td style="padding: 6px 8px; text-align: right; font-family: monospace;">{{ formatPrice(item.amount) }}</td>
              <td style="padding: 6px 8px; text-align: right; font-weight: bold; font-family: monospace;">{{ formatPrice(item.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Bottom Row -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
          <div style="width: 52%; font-size: 10.5px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 10px; color: #64748b; line-height: 1.35;">
              <div style="font-weight: bold; color: #334155; margin-bottom: 2px;">Terms & Conditions:</div>
              <div>1. Sold items are eligible for replacement within 7 days against manufacturing defects.</div>
              <div>2. Warranty claims require presenting this original commercial invoice.</div>
              <div>3. Physical damage, liquid exposure, or tampering invalidates all guarantees.</div>
            </div>
          </div>

          <div style="width: 42%;">
            <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
              <tbody>
                <tr>
                  <td style="padding: 4px 6px; color: #475569;">Gross Subtotal:</td>
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">৳ {{ formatPrice(data.original_amount) }}</td>
                </tr>
                <tr v-if="data.discount > 0">
                  <td style="padding: 4px 6px; color: #dc2626;">Special Discount:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(data.discount) }}</td>
                </tr>
                <tr v-if="data.vat > 0">
                  <td style="padding: 4px 6px; color: #475569;">VAT / Tax:</td>
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">+ ৳ {{ formatPrice(data.vat) }}</td>
                </tr>
                <tr style="border-top: 2px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 13px;">
                  <td style="padding: 6px 8px; color: #112C47;">TOTAL PAYABLE:</td>
                  <td style="padding: 6px 8px; text-align: right; color: #112C47; font-family: monospace;">৳ {{ formatPrice(data.amount) }}</td>
                </tr>
                <tr>
                  <td style="padding: 4px 6px; color: #166534; font-weight: bold;">Paid Amount:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #166534; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.paid_amount) }}</td>
                </tr>
                <tr v-if="data.due_amount > 0">
                  <td style="padding: 4px 6px; color: #dc2626; font-weight: bold;">Balance Due:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.due_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Signature Blocks -->
        <div style="display: flex; justify-content: space-between; margin-top: 40px; padding-top: 8px; font-size: 10.5px; color: #334155;">
          <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Customer's Acceptance</div>
          <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Prepared By (Cashier)</div>
          <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Authorized Signature & Seal</div>
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
  computed: {
    printerType() {
      return this.$root.site?.printer_type || 'thermal';
    },
    normalPaperSize() {
      return this.$root.site?.normal_paper_size || 'A4';
    },
    thermalPaperSize() {
      return this.$root.site?.thermal_paper_size || '80mm';
    },
    effectivePrintFormat() {
      const type = (this.printerType || 'thermal').toString().toLowerCase();
      if (type === 'normal') {
        const size = (this.normalPaperSize || 'A4').toString().toUpperCase();
        return size === 'A5' ? 'normal-a5' : 'normal-a4';
      } else {
        const size = (this.thermalPaperSize || '80mm').toString().toLowerCase();
        return size === '60mm' ? 'thermal-60mm' : 'thermal-80mm';
      }
    },
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
    },
    printReceipt() {
      if (!this.data || !this.data.id) return;
      const format = this.effectivePrintFormat;
      const invoiceNo = this.data.invoice_no || 'Invoice';

      let pageStyles = '';
      if (format === 'thermal-80mm') {
        pageStyles = `
          @page { size: 80mm auto; margin: 2mm 3mm; }
          html, body { margin: 0; padding: 0; width: 80mm; background: #fff; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; color: #000; }
          .invoice-print-wrapper { width: 78mm; margin: 0 auto; padding: 2px 0; }
        `;
      } else if (format === 'thermal-60mm') {
        pageStyles = `
          @page { size: 58mm auto; margin: 1mm 1mm; }
          html, body { margin: 0; padding: 0; width: 58mm; background: #fff; font-family: 'Courier New', Courier, monospace, Arial; font-size: 9.5px; color: #000; }
          .invoice-print-wrapper { width: 56mm; margin: 0 auto; padding: 1px 0; }
        `;
      } else if (format === 'normal-a5') {
        pageStyles = `
          @page { size: 148mm 210mm; margin: 5mm 6mm; }
          html, body { margin: 0; padding: 0; width: 148mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10.5px; color: #111; }
          .invoice-print-wrapper { width: 138mm; max-width: 138mm; margin: 0 auto; }
        `;
      } else { // normal-a4
        pageStyles = `
          @page { size: 210mm 297mm; margin: 10mm 12mm; }
          html, body { margin: 0; padding: 0; width: 210mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
          .invoice-print-wrapper { width: 190mm; max-width: 190mm; margin: 0 auto; }
        `;
      }

      const printContents = document.getElementById('invoiceViewPrintArea');
      if (!printContents) return;

      const WinPrint = window.open('', '', 'left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0');
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
      <head>
        <title>Sales Invoice - ${invoiceNo}</title>
        <meta charset="utf-8">
        <style>
          * { box-sizing: border-box; }
          ${pageStyles}
          @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          }
        </style>
      </head>
      <body>
        <div class="invoice-print-wrapper">
          ${printContents.innerHTML}
        </div>
      </body>
      </html>`);
      WinPrint.document.close();
      WinPrint.focus();
      setTimeout(() => {
        WinPrint.print();
      }, 350);
    }
  },
  mounted() {
    this.loadInvoice();
  }
};
</script>

<style scoped>
</style>