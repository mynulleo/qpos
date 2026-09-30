<template>
    <view-page printArea="invoice_print">
        <div class="container my-4">
            <!-- Top Controls (Layout Switcher when Normal Printer is configured) -->
            <!-- Top Controls (Layout Switcher & Header Toggle when Normal Printer is configured) -->
            <div class="card border-0 shadow-sm mb-3" v-if="isNormalPrinter">
                <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-dark"><i class="fas fa-layer-group me-1 text-primary"></i>Layout:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <button 
                                    type="button" 
                                    class="btn py-1 px-3 font-monospace" 
                                    :class="selectedLayout === 'layout1' ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                                    @click="setLayout('layout1')"
                                    title="Layout 1: Classic Corporate Invoice"
                                >
                                    <i class="fas fa-file-alt me-1"></i> Layout 1 (Classic)
                                </button>
                                <button 
                                    type="button" 
                                    class="btn py-1 px-3 font-monospace" 
                                    :class="selectedLayout === 'layout2' ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                                    @click="setLayout('layout2')"
                                    title="Layout 2: Modern Minimal Invoice"
                                >
                                    <i class="fas fa-file-invoice me-1"></i> Layout 2 (Modern)
                                </button>
                                <button 
                                    type="button" 
                                    class="btn py-1 px-3 font-monospace" 
                                    :class="selectedLayout === 'layout3' ? 'btn-primary active fw-bold shadow-sm' : 'btn-outline-secondary'"
                                    @click="setLayout('layout3')"
                                    title="Layout 3: Compact Executive Invoice"
                                >
                                    <i class="fas fa-file-lines me-1"></i> Layout 3 (Compact)
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
                                    id="toggleHeaderBill" 
                                    v-model="showHeaderInfo"
                                    @change="toggleHeaderSetting"
                                >
                                <label class="form-check-label small fw-bold text-dark cursor-pointer mb-0" for="toggleHeaderBill" style="font-size: 12px;">
                                    <i class="fas fa-store me-1 text-primary"></i>Header Info: 
                                    <span :class="showHeaderInfo ? 'text-success' : 'text-danger'">{{ showHeaderInfo ? 'ON' : 'OFF' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-light text-muted border font-monospace">
                            Printer: Normal Printer (A4)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoice Printable Area -->
            <div class="container my-2 invoice-wrapper" id="invoice_print">
                <div v-if="selectedLayout === 'layout1' || !isNormalPrinter" key="bill-layout1" class="layout-classic invoice-box bg-white p-4 shadow-sm rounded">
                    <!-- ⭐️ LAYOUT 1: Classic Corporate (ক্লাসিক কর্পোরেট) -->
                    <!-- Header -->
                    <div class="row mb-3 align-items-start border-bottom pb-3" style="border-bottom: 2.5px solid #112C47 !important; min-height: 40px;">
                        <div class="col-md-7 col-sm-7" v-if="showHeaderInfo">
                            <div class="d-flex align-items-center gap-3">
                                <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" class="img-fluid" style="max-height: 58px; max-width: 140px; object-fit: contain;" />
                                <div class="invoice-from">
                                    <h2 class="fw-bold mb-1" style="color: #112C47; font-size: 22px; text-transform: uppercase;">{{ site?.title || 'QPOS STORE' }}</h2>
                                    <p class="mb-0 text-muted small" style="max-width: 450px;">{{ site?.address }}</p>
                                    <p class="mb-0 text-muted small">Phone: {{ site?.mobile1 }} <span v-if="site?.mobile2">/ {{ site?.mobile2 }}</span></p>
                                    <p class="mb-0 text-muted small" v-if="site?.contact_email || site?.email">Email: {{ site?.contact_email || site?.email }}</p>
                                    <p class="mb-0 text-muted small" v-if="site?.bin_no || site?.vat_no"><strong>BIN / VAT Reg:</strong> {{ site?.bin_no || site?.vat_no }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 col-sm-7" v-else>
                            <!-- Empty space for pre-printed letterhead pad -->
                        </div>
                        <div class="col-md-5 col-sm-5 text-end">
                            <div class="d-inline-block text-white px-3 py-1 rounded fw-bold mb-2" style="background-color: #112C47; font-size: 15px; letter-spacing: 1px;">
                                INVOICE
                            </div>
                            <div class="font-monospace fw-bold fs-5" style="color: #112C47;">#{{ data.invoice_no }}</div>
                            <div class="text-muted small"><strong>Date:</strong> {{ data.invoice_date }}</div>
                            <div class="text-muted small"><strong>Payment Mode:</strong> {{ data.payment_method || 'Cash' }}</div>
                        </div>
                    </div>

                    <!-- Customer & Info Card -->
                    <div class="row mb-3 g-2">
                        <div class="col-md-7 col-sm-7">
                            <div class="p-3 bg-light rounded border h-100">
                                <div class="text-uppercase fw-bold text-muted small mb-1" style="color: #112C47 !important; font-size: 11px;">Invoice To (গ্রাহক):</div>
                                <h6 class="fw-bold text-dark mb-1">{{ data.client?.name || 'Walk-in Customer' }}</h6>
                                <p class="mb-0 text-muted small" v-if="data.client?.mobile"><i class="fas fa-phone-alt me-1 text-primary"></i>Phone: {{ data.client.mobile }}</p>
                                <p class="mb-0 text-muted small" v-if="data.client?.address"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Address: {{ data.client.address }}</p>
                                <p class="mb-0 text-muted small" v-if="data.delivery_address"><i class="fas fa-truck me-1 text-info"></i>Delivery Address: {{ data.delivery_address }}</p>
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-5">
                            <div class="p-3 bg-light rounded border h-100 text-end">
                                <div class="text-uppercase fw-bold text-muted small mb-1" style="color: #112C47 !important; font-size: 11px;">Payment Status:</div>
                                <div class="mb-2">
                                    <span class="badge bg-success font-monospace fs-6 px-3 py-1" v-if="Number(data.amount) <= Number(data.paid_amount)">PAID IN FULL</span>
                                    <span class="badge bg-danger font-monospace fs-6 px-3 py-1" v-else>DUE AMOUNT PENDING</span>
                                </div>
                                <div class="text-muted small" v-if="data.vehicle_info"><strong>Vehicle / Info:</strong> {{ data.vehicle_info }}</div>
                                <div class="text-muted small"><strong>Served By:</strong> {{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 12px;">
                            <thead style="background-color: #112C47; color: #fff;">
                                <tr>
                                    <th width="4%" class="text-center text-white">#</th>
                                    <th width="44%" class="text-white">Item Description & Specifications</th>
                                    <th width="16%" class="text-center text-white">Variant / Serial</th>
                                    <th width="8%" class="text-center text-white">Qty</th>
                                    <th width="14%" class="text-end text-white">Unit Rate (৳)</th>
                                    <th width="14%" class="text-end text-white">Total Amount (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(invd, index) in getItemList(data)" :key="index">
                                    <td class="text-center text-muted">{{ index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ getItemTitle(invd) }}</div>
                                        <small class="text-muted font-monospace" v-if="getItemBarcode(invd)">Barcode: {{ getItemBarcode(invd) }}</small>
                                        <div v-if="getItemWarranty(invd)" class="small text-success fw-bold" style="font-size: 11px;">
                                            <i class="fas fa-shield-alt me-1"></i>{{ getItemWarranty(invd) }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border me-1" v-if="getItemVariant(invd)">{{ getItemVariant(invd) }}</span>
                                        <div v-if="invd.serial_no" class="small text-primary font-monospace" style="font-size: 10px;">S/N: {{ invd.serial_no }}</div>
                                        <span v-if="!getItemVariant(invd) && !invd.serial_no" class="text-muted small">-</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold fs-6">{{ invd.qty }}</td>
                                    <td class="text-end font-monospace">{{ formatPrice(invd.amount) }}</td>
                                    <td class="text-end font-monospace fw-bold text-dark">{{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Terms -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-7 col-sm-7">
                            <div class="p-2 bg-light rounded border mb-2" style="font-size: 12px;">
                                <strong class="text-dark">In Words: </strong>
                                <span>{{ numberToWords(data.amount) }}</span>
                            </div>
                            <div class="p-2 bg-light rounded border" style="font-size: 11px; color: #475569;">
                                <strong class="text-dark d-block mb-1">Terms & Conditions:</strong>
                                <div>1. Goods sold are eligible for warranty replacement per policy.</div>
                                <div>2. Commercial claims require presenting this original invoice.</div>
                                <div v-if="site.bank_name" class="mt-1 fw-bold text-dark">
                                    Bank: {{ site.bank_name }} ({{ site.branch_name }}) | A/C: {{ site.account_number }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-5">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                <tbody>
                                    <tr>
                                        <th class="text-muted">Subtotal:</th>
                                        <td class="text-end font-monospace">৳ {{ formatPrice(data.original_amount) }}</td>
                                    </tr>
                                    <tr v-if="data.discount > 0">
                                        <th class="text-danger">Special Discount:</th>
                                        <td class="text-end font-monospace text-danger">- ৳ {{ formatPrice(data.discount) }}</td>
                                    </tr>
                                    <tr v-if="data.vat > 0">
                                        <th class="text-muted">VAT / Tax:</th>
                                        <td class="text-end font-monospace">+ ৳ {{ formatPrice(data.vat) }}</td>
                                    </tr>
                                    <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #112C47;">
                                        <th class="fs-6" style="color: #112C47;">TOTAL PAYABLE:</th>
                                        <td class="text-end font-monospace fs-6" style="color: #112C47;">৳ {{ formatPrice(data.amount) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-success fw-bold">Paid Amount:</th>
                                        <td class="text-end font-monospace text-success fw-bold">৳ {{ formatPrice(data.paid_amount) }}</td>
                                    </tr>
                                    <tr v-if="data.due_amount > 0">
                                        <th class="text-danger fw-bold">Balance Due:</th>
                                        <td class="text-end font-monospace text-danger fw-bold">৳ {{ formatPrice(data.due_amount) }}</td>
                                    </tr>
                                    <tr v-if="data.previous_due > 0">
                                        <th class="text-muted">Previous Due:</th>
                                        <td class="text-end font-monospace">৳ {{ formatPrice(data.previous_due) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div class="row pt-4 mt-3" style="font-size: 11px;">
                        <div class="col-4 text-center">
                            <div class="border-top border-dark pt-1 mx-2">Customer's Acceptance</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border-top border-dark pt-1 mx-2">Prepared By (Cashier)</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border-top border-dark pt-1 mx-2">Authorized Signature & Seal</div>
                        </div>
                    </div>
                </div>

                <div v-else-if="selectedLayout === 'layout2'" key="bill-layout2" class="layout-modern invoice-box bg-white p-4 shadow-sm rounded">
                    <!-- ⭐️ LAYOUT 2: Modern Minimal (মডার্ন মিনিমাল) -->
                    <!-- Modern Accent Top Line -->
                    <div style="height: 4px; background: linear-gradient(90deg, #0284c7 0%, #0369a1 60%, #0f172a 100%); margin-bottom: 16px; border-radius: 2px;"></div>

                    <!-- Header Row -->
                    <div class="row mb-3 align-items-start" style="min-height: 40px;">
                        <div class="col-md-7 col-sm-7" v-if="showHeaderInfo">
                            <div class="d-flex align-items-center gap-3">
                                <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" class="img-fluid" style="max-height: 58px; max-width: 140px; object-fit: contain;" />
                                <div>
                                    <h2 class="fw-bold mb-1" style="font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">{{ site?.title || 'QPOS STORE' }}</h2>
                                    <p class="mb-1 text-muted small" style="max-width: 440px;">{{ site?.address }}</p>
                                    <div class="d-flex gap-3 flex-wrap text-muted small">
                                        <span><i class="fas fa-phone-alt text-primary me-1"></i>{{ site?.mobile1 }}</span>
                                        <span v-if="site?.contact_email || site?.email"><i class="fas fa-envelope text-primary me-1"></i>{{ site?.contact_email || site?.email }}</span>
                                        <span v-if="site?.bin_no || site?.vat_no"><strong>BIN:</strong> {{ site?.bin_no || site?.vat_no }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 col-sm-7" v-else>
                            <!-- Empty space for pre-printed letterhead pad -->
                        </div>
                        <div class="col-md-5 col-sm-5 text-end">
                            <div style="font-size: 28px; font-weight: 900; color: #0284c7; letter-spacing: 1px; line-height: 1;">INVOICE</div>
                            <div class="font-monospace fw-bold fs-5 mt-1 text-dark">#{{ data.invoice_no }}</div>
                            <div class="text-muted small">Date: <strong>{{ data.invoice_date }}</strong></div>
                            <div class="mt-1">
                                <span v-if="Number(data.amount) <= Number(data.paid_amount)" class="badge bg-success bg-opacity-25 text-success font-monospace px-3 py-1">PAID IN FULL</span>
                                <span v-else class="badge bg-danger bg-opacity-25 text-danger font-monospace px-3 py-1">DUE AMOUNT</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2 Cards Grid -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <div class="text-uppercase fw-bold small mb-1" style="color: #0284c7; font-size: 11px;">Bill To (ক্রেতা)</div>
                                <h6 class="fw-bold text-dark mb-1">{{ data.client?.name || 'Walk-in Customer' }}</h6>
                                <div class="small text-muted" v-if="data.client?.mobile"><i class="fas fa-phone-alt me-1 text-primary"></i>Phone: {{ data.client.mobile }}</div>
                                <div class="small text-muted" v-if="data.client?.address"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Address: {{ data.client.address }}</div>
                                <div class="small text-muted" v-if="data.delivery_address"><i class="fas fa-truck me-1 text-info"></i>Delivery: {{ data.delivery_address }}</div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <div class="text-uppercase fw-bold small mb-1" style="color: #0284c7; font-size: 11px;">Invoice Details (তথ্য)</div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Payment Method:</span>
                                    <strong class="text-dark">{{ data.payment_method || 'Cash' }}</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1" v-if="data.vehicle_info">
                                    <span>Vehicle / Info:</span>
                                    <strong class="text-dark">{{ data.vehicle_info }}</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Prepared By:</span>
                                    <strong class="text-dark">{{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Table -->
                    <div class="table-responsive mb-3">
                        <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                            <thead style="background-color: #f8fafc; border-bottom: 2px solid #0284c7; color: #334155;">
                                <tr>
                                    <th width="4%" class="text-center">#</th>
                                    <th width="44%">Item / Description</th>
                                    <th width="16%" class="text-center">Variant / Serial</th>
                                    <th width="8%" class="text-center">Qty</th>
                                    <th width="14%" class="text-end">Price (৳)</th>
                                    <th width="14%" class="text-end">Total (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(invd, index) in getItemList(data)" :key="index" style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="text-center text-muted">{{ index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ getItemTitle(invd) }}</div>
                                        <small class="text-muted font-monospace" v-if="getItemBarcode(invd)">Barcode: {{ getItemBarcode(invd) }}</small>
                                        <div v-if="getItemWarranty(invd)" class="small text-primary fw-bold" style="font-size: 11px;">
                                            <i class="fas fa-shield-alt me-1"></i>{{ getItemWarranty(invd) }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-25 text-dark me-1" v-if="getItemVariant(invd)">{{ getItemVariant(invd) }}</span>
                                        <div v-if="invd.serial_no" class="small text-primary font-monospace" style="font-size: 10px;">S/N: {{ invd.serial_no }}</div>
                                        <span v-if="!getItemVariant(invd) && !invd.serial_no" class="text-muted small">-</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold fs-6">{{ invd.qty }}</td>
                                    <td class="text-end font-monospace">{{ formatPrice(invd.amount) }}</td>
                                    <td class="text-end font-monospace fw-bold text-dark">{{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Modern Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-7 col-sm-7">
                            <div class="p-2 rounded border-start border-primary border-3 bg-info bg-opacity-10 mb-2" style="font-size: 12px;">
                                <strong class="text-primary">In Words: </strong>
                                <span class="text-dark">{{ numberToWords(data.amount) }}</span>
                            </div>
                            <div class="small text-muted" style="font-size: 11px;">
                                <strong>Customer Notice:</strong>
                                <div>• Preserve this invoice for warranty and support verification.</div>
                                <div v-if="site.bank_name" class="mt-1 fw-bold text-dark">
                                    Bank Transfer: {{ site.bank_name }} | A/C: {{ site.account_number }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-5">
                            <div class="p-3 bg-light rounded border">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Subtotal:</span>
                                    <span class="font-monospace text-dark">৳ {{ formatPrice(data.original_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-danger mb-1" v-if="data.discount > 0">
                                    <span>Discount:</span>
                                    <span class="font-monospace">- ৳ {{ formatPrice(data.discount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-2" v-if="data.vat > 0">
                                    <span>VAT / Tax:</span>
                                    <span class="font-monospace text-dark">+ ৳ {{ formatPrice(data.vat) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-2 rounded text-white mb-2" style="background-color: #0284c7;">
                                    <span class="fw-bold" style="font-size: 12px;">NET PAYABLE:</span>
                                    <span class="fs-5 fw-bold font-monospace">৳ {{ formatPrice(data.amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-success fw-bold mb-1">
                                    <span>Paid Amount:</span>
                                    <span class="font-monospace">৳ {{ formatPrice(data.paid_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-danger fw-bold" v-if="data.due_amount > 0">
                                    <span>Balance Due:</span>
                                    <span class="font-monospace">৳ {{ formatPrice(data.due_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div class="row pt-4 mt-3" style="font-size: 11px;">
                        <div class="col-6 text-center">
                            <div class="border-top border-secondary pt-1 mx-4">Customer Signature</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border-top border-secondary pt-1 mx-4">For {{ site.title || 'Company' }} (Authorized)</div>
                        </div>
                    </div>
                </div>

                <div v-else key="bill-layout3" class="layout-compact invoice-box bg-white p-3 shadow-sm rounded border border-2 border-dark">
                    <!-- ⭐️ LAYOUT 3: Compact Executive (কমপ্যাক্ট এক্সিকিউটিভ) -->
                    <!-- Centered Header -->
                    <div v-if="showHeaderInfo" class="text-center border-bottom border-2 border-dark pb-2 mb-2">
                        <div v-if="storeLogo" class="d-flex justify-content-center mb-1">
                            <img :src="storeLogo" alt="Store Logo" class="img-fluid" style="max-height: 48px; max-width: 140px; object-fit: contain;" />
                        </div>
                        <h3 class="fw-bold text-dark text-uppercase mb-1" style="font-size: 20px;">{{ site.title || 'QPOS STORE' }}</h3>
                        <p class="mb-0 text-muted small">{{ site.address }}</p>
                        <p class="mb-0 text-muted small">
                            Phone: {{ site.mobile1 }} <span v-if="site.mobile2">/ {{ site.mobile2 }}</span> | Email: {{ site.contact_email || site.email }}
                            <span v-if="site.bin_no || site.vat_no"> | BIN: {{ site.bin_no || site.vat_no }}</span>
                        </p>
                        <div class="fw-bold text-dark mt-1" style="font-size: 13px; letter-spacing: 2px;">
                            --- INVOICE ---
                        </div>
                    </div>
                    <div v-else class="text-center border-bottom border-2 border-dark pb-2 mb-2">
                        <div class="fw-bold text-dark" style="font-size: 13px; letter-spacing: 2px;">
                            --- INVOICE ---
                        </div>
                    </div>

                    <!-- 4-Quadrant Info Table -->
                    <table class="table table-sm table-bordered border-dark mb-2" style="font-size: 11px;">
                        <tbody>
                            <tr>
                                <td width="25%" class="bg-light"><strong>Invoice No:</strong> <span class="font-monospace fw-bold">#{{ data.invoice_no }}</span></td>
                                <td width="25%"><strong>Date:</strong> {{ data.invoice_date }}</td>
                                <td width="25%" class="bg-light"><strong>Payment Mode:</strong> {{ data.payment_method || 'Cash' }}</td>
                                <td width="25%">
                                    <strong>Status:</strong> 
                                    <span :class="Number(data.amount) <= Number(data.paid_amount) ? 'text-success fw-bold' : 'text-danger fw-bold'">
                                        {{ Number(data.amount) <= Number(data.paid_amount) ? 'PAID' : 'DUE' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <strong>Customer:</strong> {{ data.client?.name || 'Walk-in Customer' }}
                                    <span v-if="data.client?.mobile" class="ms-1 text-muted">({{ data.client.mobile }})</span>
                                </td>
                                <td colspan="2">
                                    <strong>Address:</strong> {{ data.delivery_address || (data.client?.address || 'N/A') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Items Table -->
                    <table class="table table-sm table-bordered border-dark align-middle mb-2" style="font-size: 11px;">
                        <thead class="table-secondary border-dark">
                            <tr>
                                <th width="4%" class="text-center">SL</th>
                                <th width="44%">Item Description & Specifications</th>
                                <th width="16%" class="text-center">Spec / Serial</th>
                                <th width="8%" class="text-center">Qty</th>
                                <th width="14%" class="text-end">Rate (৳)</th>
                                <th width="14%" class="text-end">Amount (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(invd, index) in getItemList(data)" :key="index">
                                <td class="text-center">{{ index + 1 }}</td>
                                <td>
                                    <div class="fw-bold">{{ getItemTitle(invd) }}</div>
                                    <small class="text-muted" v-if="getItemBarcode(invd)">Barcode: {{ getItemBarcode(invd) }}</small>
                                    <div v-if="getItemWarranty(invd)" class="text-success small" style="font-size: 10px;">{{ getItemWarranty(invd) }}</div>
                                </td>
                                <td class="text-center">
                                    <span v-if="getItemVariant(invd)">{{ getItemVariant(invd) }}</span>
                                    <div v-if="invd.serial_no" class="small font-monospace" style="font-size: 10px;">S/N: {{ invd.serial_no }}</div>
                                    <span v-if="!getItemVariant(invd) && !invd.serial_no" class="text-muted">-</span>
                                </td>
                                <td class="text-center font-monospace fw-bold">{{ invd.qty }}</td>
                                <td class="text-end font-monospace">{{ formatPrice(invd.amount) }}</td>
                                <td class="text-end font-monospace fw-bold">{{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Bottom Grid -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <div class="border border-dark p-2 rounded h-100" style="font-size: 11px;">
                                <div><strong>In Words:</strong> {{ numberToWords(data.amount) }}</div>
                                <div class="text-muted mt-2" style="font-size: 10px;">
                                    <div>* Goods once sold are subject to standard return policy.</div>
                                    <div v-if="site.bank_name" class="mt-1">
                                        Bank: {{ site.bank_name }} | A/C: {{ site.account_number }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-5">
                            <table class="table table-sm table-bordered border-dark mb-0" style="font-size: 11px;">
                                <tbody>
                                    <tr>
                                        <td>Subtotal:</td>
                                        <td class="text-end font-monospace">৳ {{ formatPrice(data.original_amount) }}</td>
                                    </tr>
                                    <tr v-if="data.discount > 0">
                                        <td class="text-danger">Discount:</td>
                                        <td class="text-end font-monospace text-danger">- ৳ {{ formatPrice(data.discount) }}</td>
                                    </tr>
                                    <tr v-if="data.vat > 0">
                                        <td>VAT / Tax:</td>
                                        <td class="text-end font-monospace">+ ৳ {{ formatPrice(data.vat) }}</td>
                                    </tr>
                                    <tr class="table-secondary fw-bold">
                                        <td>Grand Total:</td>
                                        <td class="text-end font-monospace">৳ {{ formatPrice(data.amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-success fw-bold">Paid Amount:</td>
                                        <td class="text-end font-monospace text-success fw-bold">৳ {{ formatPrice(data.paid_amount) }}</td>
                                    </tr>
                                    <tr v-if="data.due_amount > 0">
                                        <td class="text-danger fw-bold">Net Due:</td>
                                        <td class="text-end font-monospace text-danger fw-bold">৳ {{ formatPrice(data.due_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3 Signatures in Compact Box -->
                    <div class="row pt-3" style="font-size: 10px;">
                        <div class="col-4 text-center">
                            <div class="border-top border-dark pt-1 mx-2">Received By</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border-top border-dark pt-1 mx-2">Prepared By</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border-top border-dark pt-1 mx-2">Authorized Signatory</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </view-page>
</template>

<script>
const model = "invoice";

export default {
    data() {
        return {
            page_title: "Invoice",
            model,
            data: {},
            print_area: "invoice_print",
            selectedLayout: this.$route.query.layout || localStorage.getItem('qpos_invoice_layout') || 'layout1',
            showHeaderInfo: this.$route.query.header !== undefined ? this.$route.query.header === '1' : (localStorage.getItem('qpos_invoice_show_header') !== 'false'),
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
    created() {
        this.page_title = "Invoice";
        this.get_data(`${this.model}/bill/${this.$route.params.id}`);
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
            if (item.warranty_type && item.warranty_type !== 'none') {
                const label = item.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty';
                return `${label}: ${item.warranty_period}`;
            }
            if (item.item?.warranty_type && item.item?.warranty_type !== 'none') {
                const label = item.item.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty';
                return `${label}: ${item.item.warranty_period}`;
            }
            return '';
        },
        getItemVariant(item) {
            const c = item.color_title || item.color?.title || '';
            const s = item.size_title || item.size?.title || '';
            if (c && s) return `${c} / ${s}`;
            return c || s || '';
        },
        formatPrice(val) {
            const f = parseFloat(val);
            return isNaN(f) ? '0.00' : f.toFixed(2);
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
    },
};
</script>

<style scoped>
.invoice-wrapper {
    max-width: 1000px;
}

@media print {
    .invoice-box {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }
}
</style>
