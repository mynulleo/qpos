<template>
    <div class="layout-modern invoice-box bg-white p-3 p-md-4 shadow-sm rounded">
        <!-- ⭐️ LAYOUT 2: Modern Executive (আধুনিক ও এক্সিকিউটিভ) -->
        
        <!-- Top Executive Accent Bar -->
        <div class="top-accent-bar mb-2" :style="{ visibility: showHeaderInfo ? 'visible' : 'hidden' }">
            <div style="height: 3px; background: #000000; border-radius: 2px;"></div>
        </div>

        <!-- 🏢 1. Organization Header Section (Store Info & Memberships ONLY) -->
        <!-- When header is OFF (showHeaderInfo is false), it maintains minHeight & visibility:hidden for pre-printed pad printing -->
        <div class="org-header-section mb-2 pb-2" 
             :style="{ 
                 visibility: showHeaderInfo ? 'visible' : 'hidden', 
                 minHeight: '105px', 
                 borderBottom: showHeaderInfo ? '1.5px solid #000000 !important' : '1.5px solid transparent !important' 
             }">
            <div class="row align-items-center g-2">
                <!-- Left: Company Logo & Organization Contact Details -->
                <div class="col-7">
                    <div class="d-flex align-items-start gap-3">
                        <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" class="img-fluid flex-shrink-0" style="max-height: 58px; max-width: 135px; object-fit: contain;" />
                        <div class="store-info">
                            <h2 class="fw-bold mb-1 text-black" style="font-size: 18px; font-weight: 900; letter-spacing: -0.3px; line-height: 1.15; color: #000000 !important; text-transform: uppercase;">
                                {{ currentSite?.title || 'QPOS STORE' }}
                            </h2>
                            <p class="mb-0 text-black fw-medium" style="font-size: 10px; line-height: 1.3; max-width: 340px; color: #000000 !important;">
                                {{ currentSite?.address }}
                            </p>
                            <div class="d-flex flex-wrap gap-x-2 text-black fw-medium mt-1" style="font-size: 10px; line-height: 1.3; color: #000000 !important;">
                                <span class="me-2"><strong style="color: #000000 !important;">Phone:</strong> {{ currentSite?.mobile1 }} <span v-if="currentSite?.mobile2">/ {{ currentSite?.mobile2 }}</span></span>
                                <span v-if="currentSite?.contact_email || currentSite?.email" class="me-2"><strong style="color: #000000 !important;">Email:</strong> {{ currentSite?.contact_email || currentSite?.email }}</span>
                                <span v-if="currentSite?.bin_no || currentSite?.vat_no"><strong style="color: #000000 !important;">BIN / VAT:</strong> {{ currentSite?.bin_no || currentSite?.vat_no }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Organization Memberships ONLY -->
                <div class="col-5 text-end">
                    <div v-if="orgMemberships && orgMemberships.length > 0" class="d-flex flex-column align-items-end gap-1">
                        <span class="text-uppercase fw-bold text-black" style="font-size: 9.5px; letter-spacing: 0.5px; color: #000000 !important;">Member of:</span>
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                            <template v-for="(m, mIdx) in orgMemberships" :key="mIdx">
                                <img v-if="m.logo || m.logo_url" 
                                     :src="m.logo_url || m.logo" 
                                     :alt="m.org_name || 'Organization Logo'"
                                     :title="m.org_name"
                                     style="max-height: 38px; max-width: 80px; object-fit: contain;" />
                                <span v-else class="fw-bold text-black font-monospace border px-1" style="font-size: 10px; color: #000000 !important;">{{ m.org_name }}</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🧾 2. Invoice Details Section (Placed BELOW the Header - Clean, Borderless Header Meta) -->
        <div class="invoice-info-section mb-3">
            <!-- Modern Executive INVOICE Header & Invoice No (No Box Border, Invoice No under INVOICE) -->
            <div class="d-flex flex-wrap align-items-end justify-content-between pb-1 mb-2">
                <div>
                    <div class="text-uppercase fw-bold text-black" style="font-size: 20px; font-weight: 900; letter-spacing: 2px; color: #000000 !important; line-height: 1.1;">
                        INVOICE
                    </div>
                    <div class="text-black fw-bold mt-1" style="font-size: 12px; color: #000000 !important;">
                        <strong>Invoice No:</strong> <span class="font-monospace text-black" style="font-size: 12.5px; color: #000000 !important;">#{{ data.invoice_no }}</span>
                    </div>
                </div>
                <div class="text-end text-black">
                    <div style="font-size: 11px; color: #000000 !important;">
                        <strong>Invoice Date:</strong> <span class="font-monospace text-nowrap fw-bold" style="color: #000000 !important;">{{ data.invoice_date }}</span>
                    </div>
                    <div v-if="Number(data.amount) <= Number(data.paid_amount) || data.payment_status === 'Paid'" class="mt-1">
                        <span class="text-success fw-bold font-monospace" style="font-size: 10.5px; border: 1.5px solid #16a34a; padding: 1px 6px; border-radius: 3px; background: #f0fdf4;">
                            PAID
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer & Billing Details 2-Card Grid -->
            <div class="row g-2">
                <!-- Left Card: Customer Details -->
                <div class="col-7">
                    <div class="p-2 bg-light rounded border border-dark h-100" style="font-size: 10.5px; line-height: 1.4; color: #000000 !important; border-left: 3.5px solid #000000 !important;">
                        <div class="d-flex align-items-center justify-content-between border-bottom border-dark pb-1 mb-1">
                            <span class="text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px; color: #000000 !important;">
                                <i class="fas fa-user me-1 text-black print-icon"></i>Invoice To (গ্রাহক)
                            </span>
                            <span class="badge bg-white text-black border border-dark font-monospace" style="font-size: 9px; color: #000000 !important;">
                                {{ data.client?.clientid ? 'ID: ' + data.client.clientid : 'Customer' }}
                            </span>
                        </div>
                        <div class="fw-bold text-black" style="font-size: 12px; color: #000000 !important;">
                            {{ data.client?.name || 'Walk-in Customer' }}
                        </div>
                        <div class="text-black" style="color: #000000 !important;" v-if="data.client?.mobile">
                            <strong>Mobile:</strong> {{ data.client.mobile }}
                        </div>
                        <div class="text-black" style="color: #000000 !important;" v-if="data.client?.address">
                            <strong>Address:</strong> {{ data.client.address }}
                        </div>
                        <div class="text-black" style="color: #000000 !important;" v-if="data.delivery_address">
                            <strong>Delivery:</strong> {{ data.delivery_address }}
                        </div>
                    </div>
                </div>

                <!-- Right Card: Sales & Billing Meta -->
                <div class="col-5">
                    <div class="p-2 bg-light rounded border border-dark h-100 d-flex flex-column justify-content-between" style="font-size: 10.5px; line-height: 1.4; color: #000000 !important; border-left: 3.5px solid #000000 !important;">
                        <div>
                            <div class="border-bottom border-dark pb-1 mb-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px; color: #000000 !important;">
                                <i class="fas fa-info-circle me-1 text-black print-icon"></i>Billing Details
                            </div>
                            <div class="d-flex justify-content-between text-black" style="color: #000000 !important;">
                                <span>Sold By:</span>
                                <strong style="color: #000000 !important;">{{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-black" style="color: #000000 !important;" v-if="data.vehicle_info">
                                <span>Vehicle / Info:</span>
                                <strong style="color: #000000 !important;">{{ data.vehicle_info }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-black" style="color: #000000 !important;" v-if="data.payment_method">
                                <span>Payment:</span>
                                <strong style="color: #000000 !important;">{{ data.payment_method }}</strong>
                            </div>
                        </div>
                        <div class="font-monospace text-black fw-medium pt-1 mt-1 border-top border-dark d-flex justify-content-between" style="font-size: 9px; color: #000000 !important;">
                            <span>System: QPOS</span>
                            <span>Ref: #{{ data.id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📦 3. Items Table (No '৳' Symbol in Body Cells) -->
        <div class="table-responsive mb-3">
            <table class="table table-bordered border-dark align-middle mb-0" style="font-size: 11px; width: 100%;">
                <thead style="background-color: #000000 !important; color: #ffffff !important;">
                    <tr>
                        <th width="4%" class="text-center text-white" style="background-color: #000000 !important; color: #ffffff !important; padding: 5px 4px;">#</th>
                        <th width="48%" class="text-white" style="background-color: #000000 !important; color: #ffffff !important; padding: 5px 6px;">Item Description & Specifications</th>
                        <th width="10%" class="text-center text-white text-nowrap" style="background-color: #000000 !important; color: #ffffff !important; padding: 5px 4px; white-space: nowrap !important;">Qty</th>
                        <th width="19%" class="text-end text-white text-nowrap" style="background-color: #000000 !important; color: #ffffff !important; padding: 5px 6px; white-space: nowrap !important;">Unit Rate (৳)</th>
                        <th width="19%" class="text-end text-white text-nowrap" style="background-color: #000000 !important; color: #ffffff !important; padding: 5px 6px; white-space: nowrap !important;">Total (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(invd, index) in getItemList(data)" :key="index" style="color: #000000 !important;">
                        <td class="text-center text-black fw-bold" style="padding: 5px 4px; color: #000000 !important;">{{ index + 1 }}</td>
                        <td style="padding: 5px 6px; color: #000000 !important;">
                            <div class="fw-bold text-black" style="font-size: 11.5px; line-height: 1.25; color: #000000 !important;">{{ getItemTitle(invd) }}</div>
                            <!-- ⭐️ Specifications -->
                            <div class="d-flex flex-wrap gap-1 mt-1" v-if="getItemSpecs(invd).length > 0">
                                <span v-for="(spec, sIdx) in getItemSpecs(invd)" :key="sIdx" 
                                      class="badge bg-white text-black border border-dark font-monospace" 
                                      style="font-size: 9.5px; font-weight: 600; padding: 1px 4px; color: #000000 !important;">
                                    <strong>{{ spec.label }}:</strong> {{ spec.value }}
                                </span>
                            </div>
                            <div v-if="getItemBarcode(invd)" class="text-black font-monospace mt-1" style="font-size: 9px; color: #000000 !important;">
                                Barcode: {{ getItemBarcode(invd) }}
                            </div>
                        </td>
                        <td class="text-center font-monospace fw-bold text-nowrap text-black" style="padding: 5px 4px; white-space: nowrap !important; font-size: 11.5px; color: #000000 !important;">{{ invd.qty }}</td>
                        <!-- Pure numbers without ৳ symbol in table body -->
                        <td class="text-end font-monospace text-nowrap text-black fw-semibold" style="padding: 5px 6px; white-space: nowrap !important; font-size: 11px; color: #000000 !important;">{{ formatPrice(invd.amount) }}</td>
                        <td class="text-end font-monospace fw-bold text-black text-nowrap" style="padding: 5px 6px; white-space: nowrap !important; font-size: 11.5px; color: #000000 !important;">{{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 💰 4. Financial Summary & Bank Details (In Words & Bank on Left, Calculations on Right) -->
        <div class="row g-2 mb-2">
            <!-- Left Side: In Words (No Border) & Bank Details (Above Terms) -->
            <div class="col-6">
                <!-- In Words Section (Clean, No Box Border) -->
                <div class="py-1 mb-2" style="font-size: 10.5px; line-height: 1.35; color: #000000 !important;">
                    <strong class="text-black" style="color: #000000 !important;">In Words: </strong>
                    <span class="text-black fw-semibold" style="color: #000000 !important;">{{ numberToWords(data.amount) }}</span>
                </div>
                
                <!-- 🏦 Bank Payment Details (Placed Above Terms & Conditions) -->
                <div v-if="currentSite?.bank_name" class="p-2 bg-light rounded border border-dark" style="font-size: 10px; color: #000000 !important; border-left: 3.5px solid #000000 !important;">
                    <strong class="text-black d-block mb-1" style="font-size: 10px; color: #000000 !important;">
                        <i class="fas fa-university me-1 text-black print-icon"></i>Bank Payment Details:
                    </strong>
                    <div class="fw-bold text-black font-monospace" style="font-size: 10px; color: #000000 !important;">
                        Bank: {{ currentSite.bank_name }} <span v-if="currentSite.branch_name">({{ currentSite.branch_name }})</span>
                    </div>
                    <div class="fw-bold text-black font-monospace" style="font-size: 10px; color: #000000 !important;">
                        A/C No: {{ currentSite.account_number }}
                    </div>
                </div>
            </div>

            <!-- Right Side: Financial Calculation Table (Clean Without Backgrounds) -->
            <div class="col-6">
                <table class="table table-sm table-bordered border-dark mb-0" style="font-size: 10.5px; width: 100% !important; border-collapse: collapse !important; background: transparent !important;">
                    <tbody>
                        <tr style="background: transparent !important;">
                            <th class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">Subtotal:</th>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">৳ {{ formatPrice(data.original_amount) }}</td>
                        </tr>
                        <tr v-if="data.discount > 0" style="background: transparent !important;">
                            <th class="text-black text-nowrap fw-bold" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">Discount:</th>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">- ৳ {{ formatPrice(data.discount) }}</td>
                        </tr>
                        <tr v-if="data.vat > 0" style="background: transparent !important;">
                            <th class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">VAT / Tax:</th>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">+ ৳ {{ formatPrice(data.vat) }}</td>
                        </tr>
                        <!-- NET TOTAL PAYABLE Row: Clean Transparent Background with Distinct Top & Bottom Border -->
                        <tr style="background: transparent !important; border-top: 2px solid #000000 !important; border-bottom: 2px solid #000000 !important;">
                            <th class="fw-bold text-black text-nowrap" style="background: transparent !important; color: #000000 !important; padding: 6px 6px; width: 52%; font-size: 11.5px; font-weight: 900; white-space: nowrap !important;">NET TOTAL PAYABLE:</th>
                            <td class="text-end font-monospace fw-bold text-black text-nowrap" style="background: transparent !important; color: #000000 !important; padding: 6px 6px; width: 48%; font-size: 12px; font-weight: 900; white-space: nowrap !important;">৳ {{ formatPrice(data.amount) }}</td>
                        </tr>
                        <tr style="background: transparent !important;">
                            <th class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">Paid Amount:</th>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">৳ {{ formatPrice(data.paid_amount) }}</td>
                        </tr>
                        <tr v-if="data.due_amount > 0" style="background: transparent !important;">
                            <th class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">Balance Due:</th>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">৳ {{ formatPrice(data.due_amount) }}</td>
                        </tr>
                        <tr v-if="data.previous_due > 0" style="background: transparent !important;">
                            <th class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">Previous Due:</th>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: transparent !important;">৳ {{ formatPrice(data.previous_due) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📜 5. Terms & Conditions (Full Width Under Total Amount Table) -->
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="pt-2" style="border-top: 1px solid #cbd5e1 !important; font-size: 10.5px; color: #000000 !important; line-height: 1.45;">
                    <strong class="text-black d-block mb-1" style="font-size: 11px; color: #000000 !important;">Terms & Conditions:</strong>
                    <div v-if="data.terms_conditions && data.terms_conditions.length > 0" class="text-black" style="color: #000000 !important;">
                        <div v-for="(tc, tcIdx) in data.terms_conditions" :key="tcIdx" class="mb-1" style="line-height: 1.45;">
                            {{ tcIdx + 1 }}. {{ tc }}
                        </div>
                    </div>
                    <div v-else class="text-black" style="color: #000000 !important;">
                        <div class="mb-1" style="line-height: 1.45;">1. Goods sold are subject to standard return & warranty policy.</div>
                        <div class="mb-1" style="line-height: 1.45;">2. Please preserve this invoice for future support and claim.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✍️ 6. Signatures (Positioned strictly at the bottom of the page in print preview) -->
        <div class="row signature-section" style="font-size: 10px; margin-top: auto !important; padding-top: 35px;">
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 mx-2 fw-bold text-black" style="color: #000000 !important;">Customer's Acceptance</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 mx-2 fw-bold text-black" style="color: #000000 !important;">Prepared By (Cashier)</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 mx-2 fw-bold text-black" style="color: #000000 !important;">Authorized Signature & Seal</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceLayout2',
    props: {
        data: {
            type: Object,
            required: true,
            default: () => ({}),
        },
        showHeaderInfo: {
            type: Boolean,
            default: true,
        },
        siteSetting: {
            type: Object,
            default: () => null,
        },
    },
    computed: {
        currentSite() {
            return this.siteSetting || this.data?.site_setting || this.$root?.site || this.data?.site || {};
        },
        orgMemberships() {
            const s = this.currentSite;
            let list = s?.memberships || s?.organization_memberships;
            if (!list) return [];
            if (typeof list === 'string') {
                try {
                    list = JSON.parse(list);
                    if (typeof list === 'string') {
                        list = JSON.parse(list);
                    }
                } catch (e) {
                    list = [];
                }
            }
            if (!Array.isArray(list)) return [];
            return list.filter(m => m && (m.show_in_invoice === 1 || m.show_in_invoice === true || m.show_in_invoice === '1' || m.show_in_invoice === 'true'));
        },
        storeLogo() {
            const s = this.currentSite;
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
.print-icon {
    width: 10px !important;
    height: 10px !important;
    max-width: 11px !important;
    max-height: 11px !important;
    font-size: 10px !important;
    vertical-align: -1px !important;
    display: inline-block !important;
}

.text-nowrap {
    white-space: nowrap !important;
}

.invoice-box {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    color: #000000 !important;
}

.signature-section {
    margin-top: auto !important;
    padding-top: 35px;
}

@media print {
    @page {
        size: auto;
        margin: 4mm 5mm;
    }
    html, body {
        height: 100%;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .invoice-box {
        min-height: 98vh;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .signature-section {
        margin-top: auto !important;
        padding-top: 25px;
        page-break-inside: avoid;
    }
    table {
        border-collapse: collapse !important;
    }
    i.fas, i.far, i.fab, i.fa, svg, .svg-inline--fa {
        width: 10px !important;
        height: 10px !important;
        max-width: 11px !important;
        max-height: 11px !important;
        font-size: 10px !important;
        vertical-align: -1px !important;
        display: inline-block !important;
    }
}
</style>
