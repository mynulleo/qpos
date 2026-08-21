<template>
  <div class="container-fluid p-3">
    <!-- Top Header Bar: Title, Search, Advance Filter Toggle & Actions -->
    <div class="card border-0 shadow-sm mb-2">
      <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Left: Page Title -->
          <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark text-nowrap">
              <i class="fas fa-file-invoice-dollar text-primary me-1"></i> Invoices (ইনভয়েস)
            </h5>
            <span class="badge bg-secondary font-monospace">{{ pagination.total }}</span>
          </div>

          <!-- Center: Default Quick Search Bar -->
          <div class="flex-grow-1 mx-md-3" style="max-width: 460px;">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Search Invoice No, Customer Mobile, Name... (Press Enter)"
                v-model="filter.keyword"
                @keyup.enter="fetchInvoices(1)"
              >
              <button type="button" class="btn btn-sm px-3 theme_search_btn" @click="fetchInvoices(1)">
                <i class="fas fa-search me-1"></i> Search
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" v-if="filter.keyword" @click="filter.keyword = ''; fetchInvoices(1)">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Right: Advance Filter Toggle & Action Buttons -->
          <div class="d-flex align-items-center gap-2">
            <!-- Advance Filter Toggle Button (Icon only, like help button, theme color #112C47) -->
            <button
              type="button"
              class="advance_filter_btn position-relative"
              @click="showAdvanced = !showAdvanced"
              title="Advance Filter"
            >
              <i class="fas fa-sliders-h"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 4px;" v-if="activeFilterCount > 0">
                {{ activeFilterCount }}
              </span>
            </button>

            <!-- Export & Print Dropdown / Buttons -->
            <download-excel
              v-if="invoices.length > 0"
              class="btn btn-sm btn-outline-success cursor-pointer"
              :data="exportData"
              :fields="exportFields"
              name="invoices.xls"
              title="Export to Excel"
            >
              <i class="fas fa-file-excel"></i>
            </download-excel>

            <button
              type="button"
              class="btn btn-sm btn-outline-dark"
              @click="print('invoiceTablePrintArea', 'Invoice List')"
              title="Print Table"
            >
              <i class="fas fa-print"></i>
            </button>

            <router-link to="/pos/return" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1 font-monospace" title="Sales Return">
              <i class="fas fa-undo"></i> Return
            </router-link>

            <!-- New POS Sale Button (Theme Color #112C47) -->
            <router-link to="/pos" class="btn-pos-sale shadow-sm" title="New POS Sale">
              <span>
                <i class="fas fa-cash-register"></i>
              </span>
              New Sale (POS)
            </router-link>
          </div>
        </div>

        <!-- Collapsible Advance Filter Drawer Panel -->
        <div v-show="showAdvanced" class="mt-2 pt-2 border-top advance-filter-panel transition-all">
          <div class="row g-2 align-items-end">
            <!-- Customer Filter -->
            <div class="col-md-3 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Customer (গ্রাহক)</label>
              <select class="form-select form-select-sm" v-model="filter.client_id" @change="fetchInvoices(1)">
                <option value="">-- All Customers --</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }} ({{ c.mobile }})</option>
              </select>
            </div>

            <!-- Payment Status Filter -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">Payment Status</label>
              <select class="form-select form-select-sm" v-model="filter.payment_status" @change="fetchInvoices(1)">
                <option value="">-- All Statuses --</option>
                <option value="paid">Paid (পরিশোধিত)</option>
                <option value="partial">Partial (আংশিক)</option>
                <option value="due">Due (বকেয়া)</option>
              </select>
            </div>

            <!-- From Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">From Date</label>
              <input type="date" class="form-control form-control-sm" v-model="filter.from_invoice_date" @change="fetchInvoices(1)">
            </div>

            <!-- To Date -->
            <div class="col-md-2 col-sm-6">
              <label class="form-label small fw-bold text-muted mb-1">To Date</label>
              <input type="date" class="form-control form-control-sm" v-model="filter.to_invoice_date" @change="fetchInvoices(1)">
            </div>

            <!-- Action Buttons in Filter -->
            <div class="col-md-3 col-sm-12 d-flex gap-2">
              <button type="button" class="btn btn-sm theme_search_btn flex-grow-1 fw-bold" @click="fetchInvoices(1)">
                <i class="fas fa-filter me-1"></i> Apply Filter
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary px-3" @click="resetFilter">
                <i class="fas fa-undo me-1"></i> Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Invoices Table Card (Expanded Vertical Real Estate) -->
    <div class="card border-0 shadow-sm mb-2">
      <div class="card-body p-0 table-responsive" id="invoiceTablePrintArea" style="min-height: 420px; max-height: calc(100vh - 230px); overflow-y: auto;">
        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 13px;">
          <thead class="table-dark sticky-top" style="z-index: 2;">
            <tr>
              <th width="4%" class="text-center">#</th>
              <th width="14%">Invoice No</th>
              <th width="12%">Date</th>
              <th width="24%">Customer (গ্রাহক)</th>
              <th width="11%" class="text-end">Original (Tk)</th>
              <th width="9%" class="text-end">Discount</th>
              <th width="12%" class="text-end">Net Total</th>
              <th width="11%" class="text-end">Paid (পরিশোধ)</th>
              <th width="10%" class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(inv, index) in invoices"
              :key="inv.id"
              class="invoice-table-row"
            >
              <td class="text-center text-muted">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
              <td>
                <router-link :to="{ name: 'invoice.show', params: { id: inv.id } }" class="fw-bold font-monospace text-primary text-decoration-none" title="Click to View Details">
                  {{ inv.invoice_no }}
                </router-link>
              </td>
              <td>
                <span class="text-nowrap">{{ inv.invoice_date }}</span>
              </td>
              <td>
                <div v-if="inv.client">
                  <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" :title="inv.client.name">{{ inv.client.name }}</div>
                  <small class="text-muted font-monospace"><i class="fas fa-phone-alt me-1" style="font-size: 10px;"></i>{{ inv.client.mobile }}</small>
                </div>
                <div v-else class="text-muted small">
                  <i class="fas fa-walking me-1"></i>Walk-in Customer
                </div>
              </td>
              <td class="text-end font-monospace">{{ formatPrice(inv.original_amount) }}</td>
              <td class="text-end font-monospace text-muted">{{ formatPrice(inv.discount) }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">{{ formatPrice(inv.amount) }}</td>
              <td class="text-end font-monospace fw-bold text-success">{{ formatPrice(inv.paid_amount) }}</td>
              <td class="text-center position-relative">
                <span class="badge font-monospace" :class="getPaymentStatusBadge(inv)">
                  {{ getPaymentStatusText(inv) }}
                </span>

                <!-- ⭐️ Floating Hover Action Buttons on this specific row (No Action Column Header) -->
                <div class="hover-floating-actions">
                  <div class="btn-group btn-group-sm shadow-sm bg-white border rounded px-1 py-1">
                    <!-- View Details (Icon only) -->
                    <router-link
                      :to="{ name: 'invoice.show', params: { id: inv.id } }"
                      class="btn btn-xs btn-outline-primary border-0"
                      title="View Invoice Details"
                    >
                      <i class="fas fa-eye"></i>
                    </router-link>

                    <!-- Direct POS 80mm Receipt Print -->
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-secondary border-0"
                      @click.stop="printReceipt(inv)"
                      title="Print POS Thermal Receipt (80mm)"
                    >
                      <i class="fas fa-receipt"></i>
                    </button>

                    <!-- Full A4 Invoice Bill -->
                    <router-link
                      :to="{ name: 'invoice.bill', params: { id: inv.id } }"
                      class="btn btn-xs btn-outline-dark border-0"
                      title="Print Standard A4 Invoice Bill"
                    >
                      <i class="fas fa-print"></i>
                    </router-link>
                  </div>
                </div>
              </td>
            </tr>

            <tr v-if="invoices.length === 0 && !loading">
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="fas fa-file-invoice fa-3x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0">No invoices found matching the selected filters.</p>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="9" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 small text-muted">Loading invoices...</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ⭐️ Bottom Sleek Footer: Compact KPI Summary Strip & Pagination -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <!-- Small KPI Summary Information at the bottom -->
          <div class="d-flex flex-wrap align-items-center gap-3" style="font-size: 12px;">
            <div class="d-flex align-items-center gap-1">
              <span class="text-muted fw-bold">Invoices:</span>
              <span class="badge bg-primary font-monospace">{{ kpi.total_invoices }}</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <span class="text-muted fw-bold">Total Sales:</span>
              <span class="badge bg-info text-dark font-monospace">Tk. {{ formatPrice(kpi.total_sales) }}</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <span class="text-muted fw-bold">Collected:</span>
              <span class="badge bg-success font-monospace">Tk. {{ formatPrice(kpi.total_paid) }}</span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <span class="text-muted fw-bold">Total Due:</span>
              <span class="badge bg-danger font-monospace">Tk. {{ formatPrice(kpi.total_due) }}</span>
            </div>
          </div>

          <!-- Pagination & Per Page Selector -->
          <div class="d-flex align-items-center gap-2" v-if="pagination.total > 0">
            <span class="small text-muted font-monospace d-none d-md-inline" style="font-size: 11px;">
              {{ pagination.from || 0 }}-{{ pagination.to || 0 }} of {{ pagination.total }}
            </span>

            <div class="d-flex align-items-center gap-1">
              <button
                type="button"
                class="btn btn-xs btn-outline-secondary py-1 px-2"
                :disabled="pagination.current_page <= 1"
                @click="fetchInvoices(pagination.current_page - 1)"
                title="Previous Page"
              >
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="small text-dark font-monospace px-1">
                {{ pagination.current_page }}/{{ pagination.last_page }}
              </span>
              <button
                type="button"
                class="btn btn-xs btn-outline-secondary py-1 px-2"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="fetchInvoices(pagination.current_page + 1)"
                title="Next Page"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>

            <select class="form-select form-select-sm py-0 font-monospace" style="width: 70px; height: 28px; font-size: 11px;" v-model.number="pagination.per_page" @change="fetchInvoices(1)">
              <option :value="15">15</option>
              <option :value="30">30</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden Printable Dynamic Invoice Area (Thermal 80mm, 60mm, Normal A5, Normal A4) -->
    <div id="invoicePrintArea" class="d-none" v-if="selectedPrintInvoice">
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
            <span><strong>Inv:</strong> #{{ selectedPrintInvoice.invoice_no }}</span>
            <span><strong>Date:</strong> {{ selectedPrintInvoice.invoice_date }}</span>
          </div>
          <div><strong>Customer:</strong> {{ selectedPrintInvoice.client ? selectedPrintInvoice.client.name : 'Walk-in Customer' }}</div>
          <div v-if="selectedPrintInvoice.client?.mobile"><strong>Mobile:</strong> {{ selectedPrintInvoice.client.mobile }}</div>
          <div><strong>Payment:</strong> {{ selectedPrintInvoice.payment_method || 'Cash' }}</div>
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
            <tr v-for="d in selectedPrintInvoice.details" :key="d.id" style="border-bottom: 1px dashed #ddd;">
              <td style="padding: 3px 0;">
                <div style="font-weight: 600;">{{ d.item ? d.item.title : 'Product' }}</div>
                <div style="font-size: 9px; color: #444;" v-if="d.color || d.size">
                  {{ d.color ? d.color.title : '' }} {{ d.size ? '/' + d.size.title : '' }}
                </div>
                <div style="font-size: 9px; color: #333;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
                <div style="font-size: 8.5px; color: #16a34a;" v-if="d.item && d.item.warranty_type && d.item.warranty_type !== 'none'">
                  * {{ d.item.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}: {{ d.item.warranty_period }}
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
            <span>Tk. {{ formatPrice(selectedPrintInvoice.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="selectedPrintInvoice.discount > 0">
            <span>Discount:</span>
            <span>- Tk. {{ formatPrice(selectedPrintInvoice.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="selectedPrintInvoice.vat > 0">
            <span>VAT/Tax:</span>
            <span>+ Tk. {{ formatPrice(selectedPrintInvoice.vat) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 3px; border-top: 1px dashed #000; padding-top: 3px;">
            <span>Net Payable:</span>
            <span>Tk. {{ formatPrice(selectedPrintInvoice.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid Amount:</span>
            <span>Tk. {{ formatPrice(selectedPrintInvoice.paid_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) > 0">
            <span>Due Amount:</span>
            <span>Tk. {{ formatPrice(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) }}</span>
          </div>
        </div>

        <div style="text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px; font-size: 9px; line-height: 1.25;">
          <div>Thank you for shopping with us!</div>
          <div>Please keep this invoice for warranty and returns.</div>
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
          <div><strong>Inv:</strong> #{{ selectedPrintInvoice.invoice_no }}</div>
          <div><strong>Date:</strong> {{ selectedPrintInvoice.invoice_date }}</div>
          <div><strong>Cust:</strong> {{ selectedPrintInvoice.client ? selectedPrintInvoice.client.name : 'Walk-in' }}</div>
          <div v-if="selectedPrintInvoice.client?.mobile"><strong>Ph:</strong> {{ selectedPrintInvoice.client.mobile }}</div>
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
            <tr v-for="d in selectedPrintInvoice.details" :key="d.id" style="border-bottom: 1px dashed #ddd;">
              <td style="padding: 2px 0;">
                <div>{{ d.item ? d.item.title : 'Item' }}</div>
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
            <span>{{ formatPrice(selectedPrintInvoice.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="selectedPrintInvoice.discount > 0">
            <span>Discount:</span>
            <span>-{{ formatPrice(selectedPrintInvoice.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 10px; margin-top: 2px; border-top: 1px dashed #000; padding-top: 2px;">
            <span>Payable:</span>
            <span>Tk. {{ formatPrice(selectedPrintInvoice.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid:</span>
            <span>{{ formatPrice(selectedPrintInvoice.paid_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) > 0">
            <span>Due:</span>
            <span>{{ formatPrice(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) }}</span>
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
            <div style="font-size: 11px; font-weight: bold; margin-top: 4px; font-family: monospace;">#{{ selectedPrintInvoice.invoice_no }}</div>
            <div style="font-size: 9.5px; color: #555;">Date: {{ selectedPrintInvoice.invoice_date }}</div>
          </div>
        </div>

        <!-- Customer Box -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-bottom: 8px; font-size: 10px;">
          <div>
            <strong>Bill To (গ্রাহক):</strong>
            <div style="font-weight: 600; font-size: 11px;">{{ selectedPrintInvoice.client ? selectedPrintInvoice.client.name : 'Walk-in Customer' }}</div>
            <div v-if="selectedPrintInvoice.client?.mobile">Mobile: {{ selectedPrintInvoice.client.mobile }}</div>
            <div v-if="selectedPrintInvoice.client?.address">Address: {{ selectedPrintInvoice.client.address }}</div>
          </div>
          <div style="text-align: right;">
            <div><strong>Payment Mode:</strong> {{ selectedPrintInvoice.payment_method || 'Cash' }}</div>
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
            <tr v-for="(d, idx) in selectedPrintInvoice.details" :key="d.id" style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 4px; text-align: center;">{{ idx + 1 }}</td>
              <td style="padding: 4px 6px;">
                <div style="font-weight: 600;">{{ d.item ? d.item.title : 'Item' }}</div>
                <div style="font-size: 9px; color: #475569;" v-if="d.color || d.size">
                  Variant: {{ d.color ? d.color.title : '' }} {{ d.size ? '/' + d.size.title : '' }}
                </div>
                <div style="font-size: 9px; color: #0284c7; font-family: monospace;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
                <div style="font-size: 9px; color: #16a34a;" v-if="d.item && d.item.warranty_type && d.item.warranty_type !== 'none'">
                  Warranty: {{ d.item.warranty_period }}
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
                  <td style="padding: 2px 4px; text-align: right; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.original_amount) }}</td>
                </tr>
                <tr v-if="selectedPrintInvoice.discount > 0">
                  <td style="padding: 2px 4px; color: #dc2626;">Discount:</td>
                  <td style="padding: 2px 4px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(selectedPrintInvoice.discount) }}</td>
                </tr>
                <tr style="border-top: 1px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 11px;">
                  <td style="padding: 4px;">Net Payable:</td>
                  <td style="padding: 4px; text-align: right; color: #112C47; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.amount) }}</td>
                </tr>
                <tr>
                  <td style="padding: 2px 4px;">Paid Amount:</td>
                  <td style="padding: 2px 4px; text-align: right; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.paid_amount) }}</td>
                </tr>
                <tr v-if="(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) > 0">
                  <td style="padding: 2px 4px; color: #dc2626; font-weight: bold;">Due Amount:</td>
                  <td style="padding: 2px 4px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) }}</td>
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
            <div style="font-size: 16px; font-weight: bold; margin-top: 6px; font-family: monospace; color: #112C47;">#{{ selectedPrintInvoice.invoice_no }}</div>
            <div style="font-size: 11px; color: #64748b;"><strong>Date:</strong> {{ selectedPrintInvoice.invoice_date }}</div>
            <div style="font-size: 11px; color: #64748b;"><strong>Payment Mode:</strong> {{ selectedPrintInvoice.payment_method || 'Cash' }}</div>
          </div>
        </div>

        <!-- Customer Box -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
          <div style="width: 58%;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 3px;">Invoice To (গ্রাহক):</div>
            <div style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ selectedPrintInvoice.client ? selectedPrintInvoice.client.name : 'Walk-in Customer' }}</div>
            <div style="font-size: 11.5px; color: #334155; margin-top: 2px;" v-if="selectedPrintInvoice.client?.mobile">
              <strong>Phone:</strong> {{ selectedPrintInvoice.client.mobile }}
            </div>
            <div style="font-size: 11px; color: #475569; margin-top: 2px;" v-if="selectedPrintInvoice.client?.address">
              <strong>Address:</strong> {{ selectedPrintInvoice.client.address }}
            </div>
          </div>
          <div style="width: 38%; text-align: right; border-left: 1px solid #e2e8f0; padding-left: 12px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #112C47; margin-bottom: 3px;">Payment Status:</div>
            <div style="font-size: 13px; font-weight: bold; color: #16a34a;" v-if="selectedPrintInvoice.amount <= selectedPrintInvoice.paid_amount">PAID IN FULL</div>
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
            <tr v-for="(item, idx) in selectedPrintInvoice.details" :key="item.id" style="border-bottom: 1px solid #e2e8f0;">
              <td style="padding: 6px 8px; text-align: center; color: #64748b;">{{ idx + 1 }}</td>
              <td style="padding: 6px 8px;">
                <div style="font-weight: 600; color: #0f172a;">{{ item.item ? item.item.title : 'Product' }}</div>
                <div style="font-size: 10px; color: #475569;" v-if="item.color || item.size">
                  Variant: {{ item.color ? item.color.title : '' }} {{ item.size ? '/' + item.size.title : '' }}
                </div>
                <div style="font-size: 10px; color: #0284c7; font-family: monospace;" v-if="item.serial_no">
                  Serial/IMEI: {{ item.serial_no }}
                </div>
                <div style="font-size: 10px; color: #16a34a;" v-if="item.item && item.item.warranty_type && item.item.warranty_type !== 'none'">
                  Warranty: {{ item.item.warranty_type }} ({{ item.item.warranty_period }})
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
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.original_amount) }}</td>
                </tr>
                <tr v-if="selectedPrintInvoice.discount > 0">
                  <td style="padding: 4px 6px; color: #dc2626;">Special Discount:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(selectedPrintInvoice.discount) }}</td>
                </tr>
                <tr v-if="selectedPrintInvoice.vat > 0">
                  <td style="padding: 4px 6px; color: #475569;">VAT / Tax:</td>
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">+ ৳ {{ formatPrice(selectedPrintInvoice.vat) }}</td>
                </tr>
                <tr style="border-top: 2px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 13px;">
                  <td style="padding: 6px 8px; color: #112C47;">TOTAL PAYABLE:</td>
                  <td style="padding: 6px 8px; text-align: right; color: #112C47; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.amount) }}</td>
                </tr>
                <tr>
                  <td style="padding: 4px 6px; color: #166534; font-weight: bold;">Paid Amount:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #166534; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.paid_amount) }}</td>
                </tr>
                <tr v-if="(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) > 0">
                  <td style="padding: 4px 6px; color: #dc2626; font-weight: bold;">Balance Due:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(selectedPrintInvoice.amount - selectedPrintInvoice.paid_amount) }}</td>
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
      invoices: [],
      clients: [],
      loading: false,
      showAdvanced: false,
      filter: {
        keyword: '',
        client_id: '',
        payment_status: '',
        from_invoice_date: '',
        to_invoice_date: '',
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0,
      },
      kpi: {
        total_invoices: 0,
        total_sales: 0,
        total_paid: 0,
        total_due: 0,
      },
      selectedPrintInvoice: null,
      exportFields: {
        'Invoice No': 'invoice_no',
        'Date': 'invoice_date',
        'Customer Name': 'client_name',
        'Mobile': 'client_mobile',
        'Original Amount': 'original_amount',
        'Discount': 'discount',
        'Net Amount': 'amount',
        'Paid Amount': 'paid_amount',
        'Due Amount': 'due_amount',
        'Status': 'status',
      }
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
    activeFilterCount() {
      let count = 0;
      if (this.filter.client_id) count++;
      if (this.filter.payment_status) count++;
      if (this.filter.from_invoice_date) count++;
      if (this.filter.to_invoice_date) count++;
      return count;
    },
    exportData() {
      return this.invoices.map(inv => ({
        invoice_no: inv.invoice_no,
        invoice_date: inv.invoice_date,
        client_name: inv.client ? inv.client.name : 'Walk-in Customer',
        client_mobile: inv.client ? inv.client.mobile : '',
        original_amount: inv.original_amount,
        discount: inv.discount,
        amount: inv.amount,
        paid_amount: inv.paid_amount,
        due_amount: Math.max(0, floatval(inv.amount) - floatval(inv.paid_amount)),
        status: this.getPaymentStatusText(inv),
      }));
    }
  },
  methods: {
    formatPrice(val) {
      const f = parseFloat(val);
      return isNaN(f) ? '0.00' : f.toFixed(2);
    },
    getPaymentStatusText(inv) {
      const due = floatval(inv.amount) - floatval(inv.paid_amount);
      if (due <= 0) return 'Paid';
      if (floatval(inv.paid_amount) > 0) return 'Partial';
      return 'Due';
    },
    getPaymentStatusBadge(inv) {
      const status = this.getPaymentStatusText(inv);
      if (status === 'Paid') return 'bg-success';
      if (status === 'Partial') return 'bg-warning text-dark';
      return 'bg-danger';
    },
    fetchInvoices(page = 1) {
      this.loading = true;
      this.pagination.current_page = page;

      const params = {
        page: page,
        pagination: this.pagination.per_page,
        keyword: this.filter.keyword,
        client_id: this.filter.client_id,
        payment_status: this.filter.payment_status,
        from_invoice_date: this.filter.from_invoice_date,
        to_invoice_date: this.filter.to_invoice_date,
      };

      axios.get('invoice', { params })
        .then(res => {
          this.loading = false;
          if (res.data) {
            this.invoices = res.data.data || [];
            this.pagination.total = res.data.total || 0;
            this.pagination.last_page = res.data.last_page || 1;
            this.pagination.from = res.data.from || 0;
            this.pagination.to = res.data.to || 0;

            if (res.data.kpi) {
              this.kpi = res.data.kpi;
            }
          }
        })
        .catch(err => {
          this.loading = false;
          console.error(err);
        });
    },
    fetchClients() {
      axios.get('client', { params: { allData: true } })
        .then(res => {
          this.clients = res.data || [];
        });
    },
    resetFilter() {
      this.filter = {
        keyword: '',
        client_id: '',
        payment_status: '',
        from_invoice_date: '',
        to_invoice_date: '',
      };
      this.fetchInvoices(1);
    },
    printReceipt(inv) {
      this.selectedPrintInvoice = inv;
      this.$nextTick(() => {
        const format = this.effectivePrintFormat;
        const invoiceNo = inv.invoice_no || 'Invoice';

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

        const printContents = document.getElementById('invoicePrintArea');
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
      });
    }
  },
  mounted() {
    this.fetchInvoices(1);
    this.fetchClients();
  }
};

function floatval(val) {
  const f = parseFloat(val);
  return isNaN(f) ? 0 : f;
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
.btn-xs {
  padding: 0.15rem 0.4rem;
  font-size: 0.75rem;
}
.advance-filter-panel {
  background-color: #f8f9fa;
  padding: 10px;
  border-radius: 6px;
}
.advance_filter_btn {
  background-color: #112C47;
  color: #ffffff;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(17, 44, 71, 0.25);
  transition: all 0.2s ease-in-out;
}
.advance_filter_btn:hover {
  background-color: #1a3d61;
  color: #ffffff;
  transform: scale(1.05);
}
.theme_search_btn {
  background-color: #112C47 !important;
  border-color: #112C47 !important;
  color: #ffffff !important;
}
.theme_search_btn:hover {
  background-color: #1a3d61 !important;
  border-color: #1a3d61 !important;
  color: #ffffff !important;
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
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* ⭐️ Seamless Floating Row Hover Action Buttons (No visible action column) */
.invoice-table-row {
  position: relative !important;
  transition: background-color 0.15s ease-in-out;
}
.invoice-table-row:hover {
  background-color: #eef6ff !important;
}
.invoice-table-row:hover td {
  background-color: #eef6ff !important;
}

.hover-floating-actions {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  z-index: 1000;
  white-space: nowrap;
  transition: opacity 0.15s ease-in-out, visibility 0.15s ease-in-out;
}

.invoice-table-row:hover .hover-floating-actions {
  opacity: 1 !important;
  visibility: visible !important;
  pointer-events: auto !important;
}

.btn-xs {
  padding: 0.2rem 0.45rem;
  font-size: 0.8rem;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
