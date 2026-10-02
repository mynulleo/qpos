<template>
  <div class="container-fluid p-3">
    <!-- Top Action Navigation Bar (Title on Left, Action Buttons on Right) -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Left Side: Invoice Title & Meta -->
        <div class="d-flex align-items-center gap-3">
          <div>
            <h4 class="mb-0 fw-bold text-dark font-monospace d-flex align-items-center">
              <i class="fas fa-file-invoice text-primary me-2"></i>Invoice #{{ data.invoice_no }}
            </h4>
            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>Date: {{ data.invoice_date }} | Created: {{ data.created_at }}</small>
          </div>
        </div>

        <!-- Right Side: Print Invoice, Full Bill Preview, Mushak 6.3 Buttons -->
        <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
          <!-- Print Dynamic Receipt / Invoice Button (with dropdown for direct layout printing) -->
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1 font-monospace shadow-sm" @click="printReceipt()">
              <i :class="isNormalPrinter ? 'fas fa-print' : 'fas fa-receipt'"></i> 
              {{ isNormalPrinter ? 'Print Invoice (' + getLayoutLabel(selectedLayout) + ')' : 'Print Receipt' }}
            </button>
            <button v-if="isNormalPrinter" type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split shadow-sm" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul v-if="isNormalPrinter" class="dropdown-menu dropdown-menu-end shadow">
              <li><h6 class="dropdown-header"><i class="fas fa-print me-1"></i>Print with Specific Layout</h6></li>
              <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between py-2" href="javascript:void(0)" @click="printReceipt('layout1')">
                  <span><i class="fas fa-file-alt text-primary me-2"></i><strong>Layout 1:</strong> Classic Corporate</span>
                  <i class="fas fa-check text-success ms-2" v-show="selectedLayout === 'layout1'"></i>
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between py-2" href="javascript:void(0)" @click="printReceipt('layout2')">
                  <span><i class="fas fa-file-invoice text-info me-2"></i><strong>Layout 2:</strong> Modern Minimal</span>
                  <i class="fas fa-check text-success ms-2" v-show="selectedLayout === 'layout2'"></i>
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between py-2" href="javascript:void(0)" @click="printReceipt('layout3')">
                  <span><i class="fas fa-file-lines text-warning me-2"></i><strong>Layout 3:</strong> Compact Executive</span>
                  <i class="fas fa-check text-success ms-2" v-show="selectedLayout === 'layout3'"></i>
                </a>
              </li>
            </ul>
          </div>

          <!-- Print Full A4 Bill -->
          <router-link v-if="data.id" :to="{ name: 'invoice.bill', params: { id: data.id }, query: { layout: selectedLayout, header: showHeaderInfo ? '1' : '0' } }" class="btn btn-dark btn-sm d-flex align-items-center gap-1 font-monospace shadow-sm">
            <i class="fas fa-eye"></i> Full Bill Preview
          </router-link>

          <!-- Print Mushak 6.3 Button -->
          <router-link v-if="data.id" :to="{ name: 'invoice.mushak', params: { id: data.id } }" class="btn btn-success btn-sm d-flex align-items-center gap-1 font-monospace fw-bold shadow-sm">
            <i class="fas fa-file-invoice-dollar"></i> [মূসক-৬.৩]
          </router-link>
        </div>
      </div>
    </div>

    <!-- 🖨️ Normal Printer Layout Selector & Header Toggle Bar (Visible when Normal Printer is configured) -->
    <div class="card border-0 shadow-sm mb-3 bg-white" v-if="isNormalPrinter">
      <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-dark"><i class="fas fa-layer-group me-1 text-primary"></i>Print Layout:</span>
            <div class="btn-group btn-group-sm" role="group">
              <button 
                type="button" 
                class="btn py-1 px-3 font-monospace" 
                :class="selectedLayout === 'layout1' ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                @click="setLayout('layout1')"
                title="Layout 1: Classic Corporate Invoice"
              >
                <i class="fas fa-file-alt me-1"></i> Classic (L1)
              </button>
              <button 
                type="button" 
                class="btn py-1 px-3 font-monospace" 
                :class="selectedLayout === 'layout2' ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                @click="setLayout('layout2')"
                title="Layout 2: Modern Minimal Invoice"
              >
                <i class="fas fa-file-invoice me-1"></i> Modern (L2)
              </button>
              <button 
                type="button" 
                class="btn py-1 px-3 font-monospace" 
                :class="selectedLayout === 'layout3' ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                @click="setLayout('layout3')"
                title="Layout 3: Compact Executive Invoice"
              >
                <i class="fas fa-file-lines me-1"></i> Compact (L3)
              </button>
            </div>
          </div>

          <!-- Toggle for Shop Name, Address, Mobile (Header Info) -->
          <div class="d-flex align-items-center bg-light p-1 px-2 border rounded">
            <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
              <input 
                class="form-check-input mt-0 cursor-pointer" 
                type="checkbox" 
                role="switch" 
                id="toggleHeaderView" 
                v-model="showHeaderInfo"
                @change="toggleHeaderSetting"
              >
              <label class="form-check-label small fw-bold text-dark cursor-pointer mb-0" for="toggleHeaderView" style="font-size: 12px;">
                <i class="fas fa-store me-1 text-primary"></i>Header Info (প্যাড প্রিন্ট): 
                <span :class="showHeaderInfo ? 'text-success' : 'text-danger'">{{ showHeaderInfo ? 'ON (দোকানের তথ্য সহ)' : 'OFF (লেটারহেড প্যাড)' }}</span>
              </label>
            </div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-light text-muted border font-monospace">
            Printer: Normal Printer ({{ normalPaperSize }})
          </span>
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
                  <tr class="border-bottom" style="background-color: #f1f5f9 !important; border-top: 2px solid #112C47; border-bottom: 2px solid #112C47;">
                    <th class="fs-6 fw-bold" style="color: #112C47 !important;">Net Total Payable:</th>
                    <td class="font-monospace fs-5 fw-bold" style="color: #112C47 !important;">Tk. {{ formatPrice(data.amount) }}</td>
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
                <div class="d-flex flex-wrap gap-1 mt-1" v-if="getItemSpecs(item).length > 0">
                  <span v-for="(spec, sIdx) in getItemSpecs(item)" :key="sIdx" 
                        class="badge bg-light text-dark border font-monospace" 
                        style="font-size: 10.5px; font-weight: 500; padding: 2px 6px;">
                    <strong class="text-secondary">{{ spec.label }}:</strong> {{ spec.value }}
                  </span>
                </div>
                <small class="text-muted font-monospace d-block mt-1" v-if="item.barcode"><i class="fas fa-barcode me-1"></i>{{ item.barcode }}</small>
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
      <div v-if="effectivePrintFormat === 'thermal-80mm'" key="print-thermal-80mm" class="thermal-80mm-invoice" style="width: 78mm; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; line-height: 1.35; padding: 4px; margin: 0 auto; color: #000;">
        <!-- 1. Thermal 80mm Layout (3-Inch Standard Receipt) -->
        <div style="text-align: center; margin-bottom: 8px;">
          <div v-if="storeLogo" style="margin-bottom: 4px;">
            <img :src="storeLogo" alt="Store Logo" style="max-height: 40px; max-width: 120px; object-fit: contain;" />
          </div>
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
                <div v-if="getItemSpecs(d).length > 0" style="font-size: 8.5px; color: #333; margin-top: 1px;">
                  <span v-for="(spec, sIdx) in getItemSpecs(d)" :key="sIdx" style="margin-right: 4px; display: inline-block;">
                    <strong>{{ spec.label }}:</strong> {{ spec.value }}
                  </span>
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
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 11.5px; margin-top: 3px; border-top: 1px dashed #000; padding: 2px 4px; background: #f1f5f9; color: #000;">
            <span style="color: #000; font-weight: bold;">Net Payable:</span>
            <span style="color: #000; font-weight: bold;">Tk. {{ formatPrice(data.amount) }}</span>
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

      <div v-else-if="effectivePrintFormat === 'thermal-60mm'" key="print-thermal-60mm" class="thermal-60mm-invoice" style="width: 56mm; font-family: monospace, Arial; font-size: 9.5px; line-height: 1.25; padding: 2px; margin: 0 auto; color: #000;">
        <!-- 2. Thermal 60mm Layout (Compact 2-Inch Mini Receipt) -->
        <div style="text-align: center; margin-bottom: 5px;">
          <div v-if="storeLogo" style="margin-bottom: 3px;">
            <img :src="storeLogo" alt="Store Logo" style="max-height: 32px; max-width: 100px; object-fit: contain;" />
          </div>
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
                <div v-if="getItemSpecs(d).length > 0" style="font-size: 7.5px; color: #444;">
                  <span v-for="(spec, sIdx) in getItemSpecs(d)" :key="sIdx" style="margin-right: 3px; display: inline-block;">
                    {{ spec.label }}: {{ spec.value }}
                  </span>
                </div>
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
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 9.5px; margin-top: 2px; border-top: 1px dashed #000; padding: 2px 3px; background: #f1f5f9; color: #000;">
            <span style="color: #000; font-weight: bold;">Payable:</span>
            <span style="color: #000; font-weight: bold;">Tk. {{ formatPrice(data.amount) }}</span>
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

      <div v-else-if="effectivePrintFormat === 'normal-a5'" key="print-normal-a5" class="normal-a5-invoice" style="width: 100%; max-width: 138mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 10.5px; line-height: 1.35; color: #111; margin: 0 auto; padding: 6px;">
        <!-- 3. Normal Printer A5 Layout (Compact Half-Page Invoice) -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #112C47; padding-bottom: 8px; margin-bottom: 8px; min-height: 35px;">
          <div v-if="showHeaderInfo" style="display: flex; align-items: center; gap: 10px;">
            <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" style="max-height: 44px; max-width: 110px; object-fit: contain;" />
            <div>
              <h2 style="font-size: 15px; font-weight: bold; margin: 0; color: #112C47;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
              <div style="font-size: 9.5px; color: #444;">{{ $root.site?.address || '' }}</div>
              <div style="font-size: 9.5px; color: #444;">Phone: {{ $root.site?.mobile1 || '' }} | Email: {{ $root.site?.contact_email || '' }}</div>
            </div>
          </div>
          <div v-else></div>
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
            <div><strong>Status:</strong> <span style="font-weight: bold;" :style="{ color: data.amount <= data.paid_amount ? '#16a34a' : '#dc2626' }">{{ data.amount <= data.paid_amount ? 'PAID' : (data.paid_amount > 0 ? 'PARTIAL' : 'DUE') }}</span></div>
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
                <div v-if="getItemSpecs(d).length > 0" style="font-size: 8.5px; color: #334155; margin-top: 1px;">
                  <span v-for="(spec, sIdx) in getItemSpecs(d)" :key="sIdx" style="margin-right: 5px; display: inline-block;">
                    <strong>{{ spec.label }}:</strong> {{ spec.value }}
                  </span>
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
                <tr style="border-top: 2px solid #112C47; border-bottom: 2px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 11px;">
                  <td style="padding: 4px; color: #112C47 !important; font-weight: bold;">Net Payable:</td>
                  <td style="padding: 4px; text-align: right; color: #112C47 !important; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.amount) }}</td>
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

      <div v-else key="print-normal-a4" class="normal-a4-invoice-container">
        <div v-if="selectedLayout === 'layout1'" key="a4-layout1" class="layout-classic" style="width: 100%; max-width: 190mm; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11.5px; line-height: 1.35; color: #111; margin: 0 auto; padding: 4px;">
          <!-- ⭐️ LAYOUT 1: Classic Corporate (ক্লাসিক কর্পোরেট) -->
          <!-- Header -->
          <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2.5px solid #112C47; padding-bottom: 10px; margin-bottom: 12px; min-height: 40px;">
            <div v-if="showHeaderInfo" style="display: flex; align-items: center; gap: 14px;">
              <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" style="max-height: 58px; max-width: 140px; object-fit: contain;" />
              <div>
                <h1 style="font-size: 20px; font-weight: 800; margin: 0 0 2px 0; color: #112C47; text-transform: uppercase; letter-spacing: 0.5px;">{{ site?.title || $root.site?.title || 'QPOS STORE' }}</h1>
                <div style="font-size: 11px; color: #475569; max-width: 380px; line-height: 1.3;">{{ site?.address || $root.site?.address || '' }}</div>
                <div style="font-size: 11px; color: #475569; margin-top: 2px;">
                  <span><strong>Phone:</strong> {{ site?.mobile1 || $root.site?.mobile1 || '' }} <span v-if="site?.mobile2 || $root.site?.mobile2">/ {{ site?.mobile2 || $root.site?.mobile2 }}</span></span>
                  <span v-if="site?.contact_email || $root.site?.contact_email" style="margin-left: 10px;"><strong>Email:</strong> {{ site?.contact_email || $root.site?.contact_email }}</span>
                </div>
                <div style="font-size: 11px; color: #475569; margin-top: 2px;" v-if="site?.bin_no || $root.site?.bin_no">
                  <strong>BIN / VAT Reg:</strong> {{ site?.bin_no || $root.site?.bin_no }}
                </div>
              </div>
            </div>
            <div v-else style="padding-top: 8px;"></div>
            <div style="text-align: right;">
              <div style="display: inline-block; background: #112C47; color: #fff; font-size: 14px; font-weight: bold; padding: 4px 16px; border-radius: 4px; letter-spacing: 1px;">
                INVOICE
              </div>
              <div style="font-size: 15px; font-weight: bold; margin-top: 5px; font-family: monospace; color: #112C47;">#{{ data.invoice_no }}</div>
              <div style="font-size: 11px; color: #64748b;"><strong>Date:</strong> {{ data.invoice_date }}</div>
              <div style="font-size: 11px; color: #64748b;"><strong>Payment Mode:</strong> {{ data.payment_method || 'Cash' }}</div>
            </div>
          </div>

          <!-- Customer & Info Box -->
          <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 11px;">
            <div style="width: 58%;">
              <div style="font-size: 10.5px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 2px;">Invoice To (গ্রাহক):</div>
              <div style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ data.client ? data.client.name : 'Walk-in Customer' }}</div>
              <div style="color: #334155; margin-top: 1px;" v-if="data.client?.mobile">
                <strong>Phone:</strong> {{ data.client.mobile }}
              </div>
              <div style="color: #475569; margin-top: 1px;" v-if="data.client?.address">
                <strong>Address:</strong> {{ data.client.address }}
              </div>
              <div style="color: #64748b; margin-top: 1px;" v-if="data.delivery_address">
                <strong>Delivery Address:</strong> {{ data.delivery_address }}
              </div>
            </div>
            <div style="width: 38%; text-align: right; border-left: 1px solid #e2e8f0; padding-left: 10px;">
              <div style="font-size: 10.5px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 2px;">Payment Status:</div>
              <div style="font-size: 12px; font-weight: bold; color: #16a34a;" v-if="data.amount <= data.paid_amount">PAID IN FULL</div>
              <div style="font-size: 12px; font-weight: bold; color: #dc2626;" v-else>DUE AMOUNT PENDING</div>
              <div style="color: #64748b; margin-top: 4px;" v-if="data.vehicle_info">
                <strong>Vehicle / Note:</strong> {{ data.vehicle_info }}
              </div>
              <div style="color: #64748b; margin-top: 2px;">
                <strong>Sold By:</strong> {{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px;">
            <thead>
              <tr style="background: #112C47; color: #fff;">
                <th style="padding: 5px 6px; text-align: center; width: 5%;">#</th>
                <th style="padding: 5px 6px; text-align: left; width: 59%;">Item Description & Specifications</th>
                <th style="padding: 5px 6px; text-align: center; width: 8%;">Qty</th>
                <th style="padding: 5px 6px; text-align: right; width: 13%;">Unit Price</th>
                <th style="padding: 5px 6px; text-align: right; width: 15%;">Total (৳)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in getItemList(data)" :key="idx" style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 5px 6px; text-align: center; color: #64748b;">{{ idx + 1 }}</td>
                <td style="padding: 5px 6px;">
                  <div style="font-weight: 600; color: #0f172a;">{{ getItemTitle(item) }}</div>
                  <div v-if="getItemSpecs(item).length > 0" style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px;">
                    <span v-for="(spec, sIdx) in getItemSpecs(item)" :key="sIdx" 
                          style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 3px; padding: 1px 5px; font-size: 9.5px; color: #1e293b;">
                      <strong style="color: #475569;">{{ spec.label }}:</strong> {{ spec.value }}
                    </span>
                  </div>
                  <div style="font-size: 9px; color: #64748b; margin-top: 1px;" v-if="getItemBarcode(item)">Barcode: {{ getItemBarcode(item) }}</div>
                </td>
                <td style="padding: 5px 6px; text-align: center; font-weight: 600;">{{ item.qty }}</td>
                <td style="padding: 5px 6px; text-align: right; font-family: monospace;">{{ formatPrice(item.amount) }}</td>
                <td style="padding: 5px 6px; text-align: right; font-weight: bold; font-family: monospace;">{{ formatPrice(item.total_amount || (item.qty * item.amount)) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Summary & Bottom Row -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <div style="width: 54%; font-size: 10px;">
              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-bottom: 6px;">
                <strong style="color: #334155;">In Words: </strong>
                <span style="color: #0f172a;">{{ numberToWords(data.amount) }}</span>
              </div>
              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; color: #64748b; line-height: 1.35;">
                <div style="font-weight: bold; color: #334155; margin-bottom: 2px;">Terms & Conditions:</div>
                <div>1. Sold items are eligible for replacement within 7 days against manufacturing defects.</div>
                <div>2. Warranty claims require presenting this original commercial invoice.</div>
                <div v-if="$root.site?.bank_name" style="margin-top: 3px; font-weight: 600; color: #1e293b;">
                  Bank: {{ $root.site?.bank_name }} | A/C: {{ $root.site?.account_number }}
                </div>
              </div>
            </div>

            <div style="width: 42%;">
              <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <tbody>
                  <tr>
                    <td style="padding: 3px 5px; color: #475569;">Gross Subtotal:</td>
                    <td style="padding: 3px 5px; text-align: right; font-family: monospace;">৳ {{ formatPrice(data.original_amount) }}</td>
                  </tr>
                  <tr v-if="data.discount > 0">
                    <td style="padding: 3px 5px; color: #dc2626;">Special Discount:</td>
                    <td style="padding: 3px 5px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(data.discount) }}</td>
                  </tr>
                  <tr v-if="data.vat > 0">
                    <td style="padding: 3px 5px; color: #475569;">VAT / Tax:</td>
                    <td style="padding: 3px 5px; text-align: right; font-family: monospace;">+ ৳ {{ formatPrice(data.vat) }}</td>
                  </tr>
                  <tr style="border-top: 2px solid #112C47; border-bottom: 2px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 12px;">
                    <td style="padding: 5px 6px; color: #112C47 !important; font-weight: bold;">TOTAL PAYABLE:</td>
                    <td style="padding: 5px 6px; text-align: right; color: #112C47 !important; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.amount) }}</td>
                  </tr>
                  <tr>
                    <td style="padding: 3px 5px; color: #166534; font-weight: bold;">Paid Amount:</td>
                    <td style="padding: 3px 5px; text-align: right; color: #166534; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.paid_amount) }}</td>
                  </tr>
                  <tr v-if="data.due_amount > 0">
                    <td style="padding: 3px 5px; color: #dc2626; font-weight: bold;">Balance Due:</td>
                    <td style="padding: 3px 5px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.due_amount) }}</td>
                  </tr>
                  <tr v-if="data.previous_due > 0">
                    <td style="padding: 3px 5px; color: #475569;">Previous Due:</td>
                    <td style="padding: 3px 5px; text-align: right; font-family: monospace;">৳ {{ formatPrice(data.previous_due) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Signatures -->
          <div style="display: flex; justify-content: space-between; margin-top: 36px; padding-top: 6px; font-size: 10px; color: #334155;">
            <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 3px;">Customer's Acceptance</div>
            <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 3px;">Prepared By (Cashier)</div>
            <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 3px;">Authorized Signature & Seal</div>
          </div>
        </div>

        <div v-else-if="selectedLayout === 'layout2'" key="a4-layout2" class="layout-modern" style="width: 100%; max-width: 190mm; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11.5px; line-height: 1.35; color: #0f172a; margin: 0 auto; padding: 4px;">
          <!-- ⭐️ LAYOUT 2: Modern Minimal (মডার্ন মিনিমাল) -->
          <!-- Modern Gradient Top Accent -->
          <div style="height: 4px; background: linear-gradient(90deg, #0284c7 0%, #0369a1 60%, #0f172a 100%); margin-bottom: 12px; border-radius: 2px;"></div>

          <!-- Header Row -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; min-height: 40px;">
            <div v-if="showHeaderInfo" style="display: flex; align-items: center; gap: 14px;">
              <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" style="max-height: 58px; max-width: 140px; object-fit: contain;" />
              <div>
                <h1 style="font-size: 22px; font-weight: 900; margin: 0 0 2px 0; color: #0f172a; letter-spacing: -0.5px;">{{ site?.title || $root.site?.title || 'QPOS STORE' }}</h1>
                <div style="font-size: 11px; color: #64748b; max-width: 380px; line-height: 1.3;">{{ site?.address || $root.site?.address || '' }}</div>
                <div style="font-size: 11px; color: #475569; margin-top: 3px; display: flex; gap: 12px; flex-wrap: wrap;">
                  <span><i class="fas fa-phone-alt text-primary me-1 print-icon" style="width: 11px; height: 11px; font-size: 11px;"></i>{{ site?.mobile1 || $root.site?.mobile1 || '' }}</span>
                  <span v-if="site?.contact_email || $root.site?.contact_email"><i class="fas fa-envelope text-primary me-1 print-icon" style="width: 11px; height: 11px; font-size: 11px;"></i>{{ site?.contact_email || $root.site?.contact_email }}</span>
                  <span v-if="site?.bin_no || $root.site?.bin_no"><strong>BIN:</strong> {{ site?.bin_no || $root.site?.bin_no }}</span>
                </div>
              </div>
            </div>
            <div v-else style="padding-top: 8px;"></div>
            <div style="text-align: right;">
              <div style="font-size: 26px; font-weight: 900; color: #0284c7; letter-spacing: 1px; line-height: 1;">INVOICE</div>
              <div style="font-size: 14px; font-weight: bold; margin-top: 4px; font-family: monospace; color: #0f172a;">#{{ data.invoice_no }}</div>
              <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">Date: <strong>{{ data.invoice_date }}</strong></div>
              <div style="margin-top: 3px;">
                <span v-if="data.amount <= data.paid_amount" style="background: #dcfce7; color: #166534; font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 12px; display: inline-block;">PAID</span>
                <span v-else style="background: #fee2e2; color: #991b1b; font-size: 10px; font-weight: bold; padding: 2px 8px; border-radius: 12px; display: inline-block;">DUE</span>
              </div>
            </div>
          </div>

          <!-- Modern Dual Card Grid (Bill To & Meta) -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
            <!-- Bill To Card -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px;">
              <div style="font-size: 10px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Bill To (ক্রেতা)</div>
              <div style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ data.client ? data.client.name : 'Walk-in Customer' }}</div>
              <div style="font-size: 11px; color: #475569; margin-top: 1px;" v-if="data.client?.mobile">Phone: {{ data.client.mobile }}</div>
              <div style="font-size: 10.5px; color: #64748b; margin-top: 1px;" v-if="data.client?.address">Address: {{ data.client.address }}</div>
              <div style="font-size: 10.5px; color: #64748b; margin-top: 1px;" v-if="data.delivery_address">Delivery: {{ data.delivery_address }}</div>
            </div>

            <!-- Meta Card -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px;">
              <div style="font-size: 10px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Invoice Details (তথ্য)</div>
              <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569; margin-bottom: 2px;">
                <span>Payment Method:</span>
                <strong style="color: #0f172a;">{{ data.payment_method || 'Cash' }}</strong>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569; margin-bottom: 2px;" v-if="data.vehicle_info">
                <span>Vehicle / Info:</span>
                <strong style="color: #0f172a;">{{ data.vehicle_info }}</strong>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569;">
                <span>Sold By:</span>
                <strong style="color: #0f172a;">{{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}</strong>
              </div>
            </div>
          </div>

          <!-- Modern Table -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px;">
            <thead>
              <tr style="background: #f1f5f9; border-bottom: 2px solid #0284c7; color: #334155;">
                <th style="padding: 6px 8px; text-align: center; width: 5%;">#</th>
                <th style="padding: 6px 8px; text-align: left; width: 59%;">Item / Description</th>
                <th style="padding: 6px 8px; text-align: center; width: 8%;">Qty</th>
                <th style="padding: 6px 8px; text-align: right; width: 13%;">Price (৳)</th>
                <th style="padding: 6px 8px; text-align: right; width: 15%;">Total (৳)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in getItemList(data)" :key="idx" style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 6px 8px; text-align: center; color: #94a3b8;">{{ idx + 1 }}</td>
                <td style="padding: 6px 8px;">
                  <div style="font-weight: 600; color: #0f172a;">{{ getItemTitle(item) }}</div>
                  <div v-if="getItemSpecs(item).length > 0" style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px;">
                    <span v-for="(spec, sIdx) in getItemSpecs(item)" :key="sIdx" 
                          style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 1px 6px; font-size: 9.5px; color: #1e293b; display: inline-block;">
                      <strong style="color: #0284c7;">{{ spec.label }}:</strong> {{ spec.value }}
                    </span>
                  </div>
                  <div style="font-size: 9px; color: #64748b; margin-top: 1px;" v-if="getItemBarcode(item)">Barcode: {{ getItemBarcode(item) }}</div>
                </td>
                <td style="padding: 6px 8px; text-align: center; font-weight: 700; color: #0f172a;">{{ item.qty }}</td>
                <td style="padding: 6px 8px; text-align: right; font-family: monospace;">{{ formatPrice(item.amount) }}</td>
                <td style="padding: 6px 8px; text-align: right; font-weight: bold; font-family: monospace; color: #0f172a;">{{ formatPrice(item.total_amount || (item.qty * item.amount)) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Modern Financial Summary -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <div style="width: 52%; font-size: 10.5px;">
              <div style="background: #f0f9ff; border-left: 3px solid #0284c7; border-radius: 0 4px 4px 0; padding: 6px 10px; margin-bottom: 8px;">
                <strong style="color: #0369a1;">In Words: </strong>
                <span style="color: #0f172a; font-weight: 500;">{{ numberToWords(data.amount) }}</span>
              </div>
              <div style="font-size: 10px; color: #64748b; line-height: 1.4;">
                <div style="font-weight: 700; color: #334155; margin-bottom: 1px;">Customer Notes:</div>
                <div>• Please preserve this original invoice for any warranty claims and post-sales support.</div>
                <div>• Goods once sold can be exchanged within 7 days in pristine unused condition.</div>
                <div v-if="$root.site?.bank_name" style="margin-top: 3px; font-weight: 600; color: #0f172a;">
                  Bank Transfer: {{ $root.site?.bank_name }} | A/C: {{ $root.site?.account_number }}
                </div>
              </div>
            </div>

            <div style="width: 44%;">
              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569; margin-bottom: 3px;">
                  <span>Subtotal:</span>
                  <span style="font-family: monospace;">৳ {{ formatPrice(data.original_amount) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #dc2626; margin-bottom: 3px;" v-if="data.discount > 0">
                  <span>Discount:</span>
                  <span style="font-family: monospace;">- ৳ {{ formatPrice(data.discount) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569; margin-bottom: 3px;" v-if="data.vat > 0">
                  <span>VAT / Tax:</span>
                  <span style="font-family: monospace;">+ ৳ {{ formatPrice(data.vat) }}</span>
                </div>
                <!-- Highlight Card for Net Payable -->
                <div style="background: #0284c7; color: #ffffff !important; border-radius: 4px; padding: 6px 10px; display: flex; justify-content: space-between; align-items: center; margin: 5px 0;">
                  <span style="font-weight: 700; font-size: 11.5px; letter-spacing: 0.5px; color: #ffffff !important;">NET PAYABLE:</span>
                  <span style="font-size: 14px; font-weight: 900; font-family: monospace; color: #ffffff !important;">৳ {{ formatPrice(data.amount) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #166534; font-weight: 600; margin-top: 4px;">
                  <span>Paid Amount:</span>
                  <span style="font-family: monospace;">৳ {{ formatPrice(data.paid_amount) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #dc2626; font-weight: bold; margin-top: 2px;" v-if="data.due_amount > 0">
                  <span>Balance Due:</span>
                  <span style="font-family: monospace;">৳ {{ formatPrice(data.due_amount) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Signatures -->
          <div style="display: flex; justify-content: space-between; margin-top: 36px; padding-top: 6px; font-size: 10px; color: #475569;">
            <div style="border-top: 1px dashed #94a3b8; width: 35%; text-align: center; padding-top: 3px;">Customer's Signature</div>
            <div style="border-top: 1px dashed #94a3b8; width: 35%; text-align: center; padding-top: 3px;">For {{ $root.site?.title || 'Company' }} (Authorized)</div>
          </div>
        </div>

        <div v-else key="a4-layout3" class="layout-compact" style="width: 100%; max-width: 190mm; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.3; color: #000; margin: 0 auto; padding: 8px; border: 2px solid #334155; border-radius: 4px; box-sizing: border-box;">
          <!-- ⭐️ LAYOUT 3: Compact Executive (কমপ্যাক্ট এক্সিকিউটিভ) -->
          <!-- Centered Header -->
          <div v-if="showHeaderInfo" style="text-align: center; border-bottom: 2px solid #334155; padding-bottom: 6px; margin-bottom: 8px;">
            <div v-if="storeLogo" style="display: flex; justify-content: center; margin-bottom: 4px;">
              <img :src="storeLogo" alt="Store Logo" style="max-height: 48px; max-width: 140px; object-fit: contain;" />
            </div>
            <h2 style="font-size: 19px; font-weight: bold; margin: 0 0 2px 0; text-transform: uppercase;">{{ site?.title || $root.site?.title || 'QPOS STORE' }}</h2>
            <div style="font-size: 10.5px; color: #333;">{{ site?.address || $root.site?.address || '' }}</div>
            <div style="font-size: 10.5px; color: #333;">
              <span>Phone: {{ site?.mobile1 || $root.site?.mobile1 || '' }} <span v-if="site?.mobile2 || $root.site?.mobile2">/ {{ site?.mobile2 || $root.site?.mobile2 }}</span></span>
              <span v-if="site?.contact_email || $root.site?.contact_email" style="margin-left: 8px;">Email: {{ site?.contact_email || $root.site?.contact_email }}</span>
              <span v-if="site?.bin_no || $root.site?.bin_no" style="margin-left: 8px;">BIN: {{ site?.bin_no || $root.site?.bin_no }}</span>
            </div>
            <div style="margin-top: 4px; font-size: 12px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">
              --- INVOICE ---
            </div>
          </div>
          <div v-else style="text-align: center; border-bottom: 2px solid #334155; padding-bottom: 4px; margin-bottom: 8px;">
            <div style="font-size: 13px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">
              --- INVOICE ---
            </div>
          </div>

          <!-- 4-Quadrant Info Table -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10.5px; border: 1px solid #64748b;">
            <tbody>
              <tr>
                <td style="width: 25%; padding: 4px 6px; border: 1px solid #64748b; background: #f8fafc;">
                  <strong>Invoice No:</strong> <span style="font-family: monospace; font-weight: bold;">#{{ data.invoice_no }}</span>
                </td>
                <td style="width: 25%; padding: 4px 6px; border: 1px solid #64748b;">
                  <strong>Date:</strong> {{ data.invoice_date }}
                </td>
                <td style="width: 25%; padding: 4px 6px; border: 1px solid #64748b; background: #f8fafc;">
                  <strong>Payment Mode:</strong> {{ data.payment_method || 'Cash' }}
                </td>
                <td style="width: 25%; padding: 4px 6px; border: 1px solid #64748b;">
                  <strong>Status:</strong> <span :style="{ color: data.amount <= data.paid_amount ? '#166534' : '#dc2626', fontWeight: 'bold' }">{{ data.amount <= data.paid_amount ? 'PAID' : 'DUE' }}</span>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="padding: 4px 6px; border: 1px solid #64748b;">
                  <strong>Customer:</strong> {{ data.client ? data.client.name : 'Walk-in Customer' }}
                  <span v-if="data.client?.mobile" style="margin-left: 6px;">(Ph: {{ data.client.mobile }})</span>
                </td>
                <td colspan="2" style="padding: 4px 6px; border: 1px solid #64748b;">
                  <strong>Address:</strong> {{ data.delivery_address || (data.client?.address || 'N/A') }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Items Grid Table -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10.5px; border: 1px solid #64748b;">
            <thead>
              <tr style="background: #e2e8f0; color: #0f172a;">
                <th style="padding: 4px 5px; text-align: center; width: 5%; border: 1px solid #64748b;">SL</th>
                <th style="padding: 4px 5px; text-align: left; width: 59%; border: 1px solid #64748b;">Item Description & Specifications</th>
                <th style="padding: 4px 5px; text-align: center; width: 8%; border: 1px solid #64748b;">Qty</th>
                <th style="padding: 4px 5px; text-align: right; width: 13%; border: 1px solid #64748b;">Rate (৳)</th>
                <th style="padding: 4px 5px; text-align: right; width: 15%; border: 1px solid #64748b;">Amount (৳)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in getItemList(data)" :key="idx">
                <td style="padding: 4px 5px; text-align: center; border: 1px solid #cbd5e1;">{{ idx + 1 }}</td>
                <td style="padding: 4px 5px; border: 1px solid #cbd5e1;">
                  <div style="font-weight: bold;">{{ getItemTitle(item) }}</div>
                  <div v-if="getItemSpecs(item).length > 0" style="display: flex; flex-wrap: wrap; gap: 3px; margin-top: 2px;">
                    <span v-for="(spec, sIdx) in getItemSpecs(item)" :key="sIdx" 
                          style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 3px; padding: 1px 4px; font-size: 9px; color: #1e293b; display: inline-block;">
                      <strong style="color: #475569;">{{ spec.label }}:</strong> {{ spec.value }}
                    </span>
                  </div>
                  <div style="font-size: 9px; color: #555; margin-top: 1px;" v-if="getItemBarcode(item)">Barcode: {{ getItemBarcode(item) }}</div>
                </td>
                <td style="padding: 4px 5px; text-align: center; font-weight: bold; border: 1px solid #cbd5e1;">{{ item.qty }}</td>
                <td style="padding: 4px 5px; text-align: right; font-family: monospace; border: 1px solid #cbd5e1;">{{ formatPrice(item.amount) }}</td>
                <td style="padding: 4px 5px; text-align: right; font-weight: bold; font-family: monospace; border: 1px solid #cbd5e1;">{{ formatPrice(item.total_amount || (item.qty * item.amount)) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Bottom Grid -->
          <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; border: 1px solid #64748b; margin-bottom: 24px;">
            <tbody>
              <tr>
                <td style="width: 58%; padding: 6px 8px; vertical-align: top; border-right: 1px solid #64748b;">
                  <div><strong>In Words:</strong> {{ numberToWords(data.amount) }}</div>
                  <div style="margin-top: 6px; font-size: 9.5px; color: #555; line-height: 1.3;">
                    <div>* Goods once sold can only be replaced within 7 days against manufacturer warranty.</div>
                    <div v-if="$root.site?.bank_name" style="margin-top: 2px;">
                      Bank: {{ $root.site?.bank_name }} | A/C: {{ $root.site?.account_number }}
                    </div>
                  </div>
                </td>
                <td style="width: 42%; padding: 0; vertical-align: top;">
                  <table style="width: 100%; border-collapse: collapse; font-size: 10.5px;">
                    <tbody>
                      <tr>
                        <td style="padding: 3px 6px; border-bottom: 1px solid #e2e8f0;">Subtotal:</td>
                        <td style="padding: 3px 6px; text-align: right; font-family: monospace; border-bottom: 1px solid #e2e8f0;">৳ {{ formatPrice(data.original_amount) }}</td>
                      </tr>
                      <tr v-if="data.discount > 0">
                        <td style="padding: 3px 6px; color: #dc2626; border-bottom: 1px solid #e2e8f0;">Discount:</td>
                        <td style="padding: 3px 6px; text-align: right; color: #dc2626; font-family: monospace; border-bottom: 1px solid #e2e8f0;">- ৳ {{ formatPrice(data.discount) }}</td>
                      </tr>
                      <tr v-if="data.vat > 0">
                        <td style="padding: 3px 6px; border-bottom: 1px solid #e2e8f0;">VAT / Tax:</td>
                        <td style="padding: 3px 6px; text-align: right; font-family: monospace; border-bottom: 1px solid #e2e8f0;">+ ৳ {{ formatPrice(data.vat) }}</td>
                      </tr>
                      <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #334155; border-bottom: 2px solid #334155;">
                        <td style="padding: 4px 6px; color: #0f172a !important; font-weight: bold;">Grand Total:</td>
                        <td style="padding: 4px 6px; text-align: right; font-family: monospace; color: #0f172a !important; font-weight: bold;">৳ {{ formatPrice(data.amount) }}</td>
                      </tr>
                      <tr>
                        <td style="padding: 3px 6px; color: #166534; font-weight: bold; border-bottom: 1px solid #e2e8f0;">Paid Amount:</td>
                        <td style="padding: 3px 6px; text-align: right; color: #166534; font-weight: bold; font-family: monospace; border-bottom: 1px solid #e2e8f0;">৳ {{ formatPrice(data.paid_amount) }}</td>
                      </tr>
                      <tr v-if="data.due_amount > 0">
                        <td style="padding: 3px 6px; color: #dc2626; font-weight: bold;">Net Due:</td>
                        <td style="padding: 3px 6px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(data.due_amount) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- 3 Signatures in Compact Box -->
          <div style="display: flex; justify-content: space-between; font-size: 9.5px; color: #333; padding: 0 10px 4px 10px;">
            <div style="border-top: 1px dashed #64748b; width: 28%; text-align: center; padding-top: 2px;">Received By</div>
            <div style="border-top: 1px dashed #64748b; width: 28%; text-align: center; padding-top: 2px;">Prepared By</div>
            <div style="border-top: 1px dashed #64748b; width: 28%; text-align: center; padding-top: 2px;">Authorized Signatory</div>
          </div>
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
      selectedLayout: localStorage.getItem('qpos_invoice_layout') || 'layout1',
      showHeaderInfo: localStorage.getItem('qpos_invoice_show_header') !== 'false',
    };
  },
  computed: {
    printerType() {
      return this.$root.site?.printer_type || 'thermal';
    },
    isNormalPrinter() {
      const pt = (this.printerType || '').toString().toLowerCase();
      return pt === 'normal' || pt.includes('normal');
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
    storeLogo() {
      const s = this.site || this.$root.site;
      if (!s) return '';
      const l1 = s.logo_one;
      if (l1 && typeof l1 === 'string' && l1 !== 'no_server_image' && !l1.includes('no_server_image')) {
        return l1;
      }
      const orig = s.original_logo;
      if (orig && typeof orig === 'string' && orig !== 'no_server_image' && !orig.includes('no_server_image')) {
        return orig;
      }
      if (s.logo && typeof s.logo === 'string' && s.logo.startsWith('http')) {
        return s.logo;
      }
      return '';
    },
  },
  methods: {
    setLayout(layout) {
      this.selectedLayout = layout;
      try {
        localStorage.setItem('qpos_invoice_layout', layout);
      } catch (e) {}
    },
    toggleHeaderSetting() {
      try {
        localStorage.setItem('qpos_invoice_show_header', this.showHeaderInfo ? 'true' : 'false');
      } catch (e) {}
    },
    getLayoutLabel(layout) {
      if (layout === 'layout1') return 'Classic';
      if (layout === 'layout2') return 'Modern';
      if (layout === 'layout3') return 'Compact';
      return 'Classic';
    },
    getItemList(inv) {
      return inv?.details || inv?.invoice_details || [];
    },
    getItemTitle(item) {
      return item.title || item.item?.title || item.description || item.reference || 'Item';
    },
    getItemBarcode(item) {
      return item.barcode || item.item?.barcode || '';
    },
    getItemWarranty(item) {
      const wType = item.warranty_type || item.item?.warranty_type;
      const wPeriod = item.warranty_period || item.item?.warranty_period;
      if (wType && wType !== 'none' && wPeriod && typeof wPeriod === 'string' && wPeriod.trim() !== '') {
        const label = wType === 'guarantee' ? 'Guarantee' : 'Warranty';
        return `${label}: ${wPeriod.trim()}`;
      }
      return '';
    },
    getItemSpecs(item) {
      if (!item) return [];
      const specs = [];

      // 1. Brand
      const brand = item.brand_title || item.brand?.title || item.item?.brand?.title;
      if (brand && typeof brand === 'string' && brand.trim() !== '') {
        specs.push({ label: 'Brand', value: brand.trim() });
      }

      // 2. Model
      const model = item.model_no || item.model || item.item?.model_no || item.series_title || item.series?.title || item.item?.series?.title;
      if (model && typeof model === 'string' && model.trim() !== '') {
        specs.push({ label: 'Model', value: model.trim() });
      }

      // 3. Color
      const color = item.color_title || item.color?.title || item.color?.name || item.item?.color?.title || item.item?.color?.name;
      if (color && typeof color === 'string' && color.trim() !== '') {
        specs.push({ label: 'Color', value: color.trim() });
      }

      // 4. Size
      const size = item.size_title || item.size?.title || item.size?.name || item.item?.size?.title || item.item?.size?.name;
      if (size && typeof size === 'string' && size.trim() !== '') {
        specs.push({ label: 'Size', value: size.trim() });
      }

      // 5. Serial No
      const serial = item.serial_no || item.serial || item.item_serial;
      if (serial && typeof serial === 'string' && serial.trim() !== '') {
        specs.push({ label: 'Serial No', value: serial.trim() });
      }

      // 6. Warranty / Guarantee
      const warranty = this.getItemWarranty(item);
      if (warranty && typeof warranty === 'string' && warranty.trim() !== '') {
        const isGuarantee = warranty.toLowerCase().startsWith('guarantee');
        const label = isGuarantee ? 'Guarantee' : 'Warranty';
        const val = warranty.replace(/^(Warranty|Guarantee):\s*/i, '').trim();
        if (val !== '') {
          specs.push({ label, value: val });
        }
      }

      return specs;
    },
    getItemVariant(item) {
      const c = item.color_title || item.color?.title || item.color?.name || '';
      const s = item.size_title || item.size?.title || item.size?.name || '';
      if (c && s) return `${c} / ${s}`;
      return c || s || '';
    },
    numberToWords(val) {
      const n = parseFloat(val);
      if (isNaN(n) || n === 0) return 'Zero Taka Only';
      
      const a = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
      const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
      
      function convertGroup(num) {
        let str = '';
        if (num >= 100) {
          str += a[Math.floor(num / 100)] + ' Hundred ';
          num %= 100;
        }
        if (num >= 20) {
          str += b[Math.floor(num / 10)] + (num % 10 !== 0 ? ' ' + a[num % 10] : '') + ' ';
        } else if (num > 0) {
          str += a[num] + ' ';
        }
        return str.trim();
      }

      let crore = Math.floor(n / 10000000);
      let rem = n % 10000000;
      let lakh = Math.floor(rem / 100000);
      rem %= 100000;
      let thousand = Math.floor(rem / 1000);
      rem %= 1000;
      let hundreds = Math.floor(rem);
      let paisa = Math.round((n - Math.floor(n)) * 100);

      let res = '';
      if (crore > 0) res += convertGroup(crore) + ' Crore ';
      if (lakh > 0) res += convertGroup(lakh) + ' Lakh ';
      if (thousand > 0) res += convertGroup(thousand) + ' Thousand ';
      if (hundreds > 0) res += convertGroup(hundreds) + ' ';

      res = res.trim() + ' Taka';
      if (paisa > 0) {
        res += ' and ' + convertGroup(paisa) + ' Paisa';
      }
      return res + ' Only';
    },
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
    printReceipt(layoutOverride) {
      if (!this.data || !this.data.id) return;
      if (layoutOverride && typeof layoutOverride === 'string') {
        this.setLayout(layoutOverride);
      }
      
      this.$nextTick(() => {
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
            @page { size: 210mm 297mm; margin: 8mm 10mm; }
            html, body { margin: 0; padding: 0; width: 210mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11.5px; color: #111; }
            .invoice-print-wrapper { width: 190mm; max-width: 190mm; margin: 0 auto; }
          `;
        }

        const printContents = document.getElementById('invoiceViewPrintArea');
        if (!printContents) return;

        const WinPrint = window.open('', '', 'left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0');
        WinPrint.document.write(`<!DOCTYPE html>
        <html>
        <head>
          <title>Invoice - ${invoiceNo}</title>
          <meta charset="utf-8">
          <style>
            * { box-sizing: border-box; }
            ${pageStyles}
            .print-icon {
              width: 11px !important;
              height: 11px !important;
              max-width: 12px !important;
              max-height: 12px !important;
              font-size: 11px !important;
              vertical-align: -1px !important;
              display: inline-block !important;
            }
            i, svg, .svg-inline--fa {
              width: 11px !important;
              height: 11px !important;
              max-width: 12px !important;
              max-height: 12px !important;
              font-size: 11px !important;
              vertical-align: -1px !important;
              display: inline-block !important;
            }
            @media print {
              body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
              }
              i, svg, .svg-inline--fa {
                width: 11px !important;
                height: 11px !important;
                max-width: 12px !important;
                max-height: 12px !important;
                font-size: 11px !important;
                vertical-align: -1px !important;
                display: inline-block !important;
              }
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
      });
    }
  },
  mounted() {
    this.loadInvoice();
  }
};
</script>

<style scoped>
.print-icon {
  width: 11px !important;
  height: 11px !important;
  max-width: 12px !important;
  max-height: 12px !important;
  font-size: 11px !important;
  vertical-align: -1px !important;
  display: inline-block !important;
}

@media print {
  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
  i.fas, i.far, i.fab, i.fa, svg, .svg-inline--fa {
    width: 11px !important;
    height: 11px !important;
    max-width: 12px !important;
    max-height: 12px !important;
    font-size: 11px !important;
    vertical-align: -1px !important;
    display: inline-block !important;
  }
}
</style>