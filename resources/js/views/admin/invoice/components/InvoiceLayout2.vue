<template>
    <div class="layout-modern invoice-box bg-white p-3 p-md-4 shadow-sm rounded">
        <!-- ⭐️ LAYOUT 2: Modern Executive (আধুনিক ও এক্সিকিউটিভ) -->

        <!-- 🏢 1. Organization Header Section (Store Info & Memberships ONLY) -->
        <!-- When header is OFF (showHeaderInfo is false), it maintains minHeight & visibility:hidden for pre-printed pad printing -->
        <div class="org-header-section mb-2 pb-2" 
             :style="{ 
                 visibility: showHeaderInfo ? 'visible' : 'hidden', 
                 minHeight: '105px', 
                 borderBottom: showHeaderInfo ? '1.5px solid #000000 !important' : '1.5px solid transparent !important' 
             }">
            <div class="row align-items-center g-2">
                <!-- Left: Company Logo & Organization Contact Details (Vertically Middle Aligned) -->
                <div class="col-7">
                    <div class="d-flex align-items-center gap-3">
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

                <!-- Right: Organization Memberships ONLY (Member of: centered horizontally above logos) -->
                <div class="col-5 text-end">
                    <div v-if="orgMemberships && orgMemberships.length > 0" class="d-inline-flex flex-column align-items-center gap-1">
                        <span class="text-uppercase fw-bold text-black text-center" style="font-size: 9.5px; letter-spacing: 0.5px; color: #000000 !important;">Member of:</span>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
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

        <!-- 🧾 2. Invoice Details Section (INVOICE Centered, Large & Bold) -->
        <div class="invoice-info-section mb-3">
            <!-- Modern Executive INVOICE Header (Horizontally Centered, Large & Bold) -->
            <div class="text-center my-2">
                <h2 class="text-uppercase text-black fw-bold mb-1" style="font-size: 26px; font-weight: 900; letter-spacing: 4px; color: #000000 !important; line-height: 1.1;">
                    INVOICE
                </h2>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between pb-1 mb-2">
                <div class="text-black fw-bold" style="font-size: 12px; color: #000000 !important;">
                    <strong>Invoice No:</strong> <span class="font-monospace text-black" style="font-size: 12.5px; color: #000000 !important;">#{{ data.invoice_no }}</span>
                </div>
                <div class="text-end text-black">
                    <div style="font-size: 11px; color: #000000 !important;">
                        <strong>Invoice Date:</strong> <span class="font-monospace text-nowrap fw-bold" style="color: #000000 !important;">{{ formatInvoiceDateTime(data) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Seller Details 2-Card Grid -->
            <div class="row g-2">
                <!-- Left Card: Customer Details -->
                <div class="col-7">
                    <div class="p-2 bg-white rounded border border-dark h-100" style="font-size: 10.5px; line-height: 1.4; color: #000000 !important; border-left: 3.5px solid #000000 !important;">
                        <div class="border-bottom border-dark pb-1 mb-1">
                            <span class="text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px; color: #000000 !important;">
                                <i class="fas fa-user me-1 text-black print-icon"></i>{{ $t('Invoice To') }}
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

                <!-- Right Card: Seller Information -->
                <div class="col-5">
                    <div class="p-2 bg-white rounded border border-dark h-100 d-flex flex-column justify-content-between" style="font-size: 10.5px; line-height: 1.4; color: #000000 !important; border-left: 3.5px solid #000000 !important;">
                        <div>
                            <div class="border-bottom border-dark pb-1 mb-1 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px; color: #000000 !important;">
                                <i class="fas fa-user-tie me-1 text-black print-icon"></i>Seller Information
                            </div>
                            <div class="text-black" style="color: #000000 !important;">
                                <strong>Sold By:</strong> {{ getSellerName(data) }}
                            </div>
                            <div class="text-black" style="color: #000000 !important;" v-if="getSellerId(data)">
                                <strong>Seller ID:</strong> {{ getSellerId(data) }}
                            </div>
                            <div class="text-black" style="color: #000000 !important;" v-if="getSellerMobile(data)">
                                <strong>Mobile:</strong> {{ getSellerMobile(data) }}
                            </div>
                            <div class="text-black" style="color: #000000 !important;" v-if="data.vehicle_info">
                                <strong>Vehicle / Info:</strong> {{ data.vehicle_info }}
                            </div>
                            <div class="text-black" style="color: #000000 !important;" v-if="data.payment_method">
                                <strong>Payment:</strong> {{ data.payment_method }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📦 3. Items Table (Clean Table Header Without Black Background, Bold & Larger Header Text) -->
        <div class="table-responsive mb-3">
            <table class="table table-bordered border-dark align-middle mb-0" style="font-size: 11px; width: 100%;">
                <thead style="background-color: transparent !important; color: #000000 !important; border-bottom: 2.5px solid #000000 !important;">
                    <tr style="background-color: transparent !important;">
                        <th width="4%" class="text-center text-black fw-bold" style="background-color: transparent !important; color: #000000 !important; padding: 8px 4px; font-size: 14px; font-weight: 900; letter-spacing: 0.3px;">{{ $t('#') }}</th>
                        <th width="48%" class="text-black fw-bold" style="background-color: transparent !important; color: #000000 !important; padding: 8px 6px; font-size: 14px; font-weight: 900; letter-spacing: 0.3px;">{{ $t('Item Description & Specifications') }}</th>
                        <th width="10%" class="text-center text-black text-nowrap fw-bold" style="background-color: transparent !important; color: #000000 !important; padding: 8px 4px; font-size: 14px; font-weight: 900; letter-spacing: 0.3px; white-space: nowrap !important;">{{ $t('Qty') }}</th>
                        <th width="19%" class="text-end text-black text-nowrap fw-bold" style="background-color: transparent !important; color: #000000 !important; padding: 8px 6px; font-size: 14px; font-weight: 900; letter-spacing: 0.3px; white-space: nowrap !important;">{{ $t('Unit Price') }}</th>
                        <th width="19%" class="text-end text-black text-nowrap fw-bold" style="background-color: transparent !important; color: #000000 !important; padding: 8px 6px; font-size: 14px; font-weight: 900; letter-spacing: 0.3px; white-space: nowrap !important;">{{ $t('Total Price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(invd, index) in getItemList(data)" :key="index" style="color: #000000 !important;">
                        <td class="text-center text-black fw-bold" style="padding: 5px 4px; color: #000000 !important;">{{ index + 1 }}</td>
                        <td style="padding: 5px 6px; color: #000000 !important; word-break: break-word;">
                            <div class="fw-bold text-black" style="font-size: 11.5px; line-height: 1.25; color: #000000 !important;">{{ getItemTitle(invd) }}</div>
                            <!-- ⭐️ Barcode right below Item Title -->
                            <div v-if="getItemBarcode(invd)" class="text-black font-monospace mt-1" style="font-size: 9.5px; line-height: 1.3; color: #000000 !important;">
                                <strong style="color: #000000 !important;">Barcode:</strong> {{ getItemBarcode(invd) }}
                            </div>
                            <!-- ⭐️ Specifications (Brand, Model, Color, Size, Warranty) -->
                            <div class="d-flex flex-wrap gap-1 mt-1" v-if="getItemSpecs(invd).length > 0">
                                <span v-for="(spec, sIdx) in getItemSpecs(invd)" :key="sIdx" 
                                      class="badge bg-white text-black border border-dark font-monospace" 
                                      style="font-size: 9.5px; font-weight: 600; padding: 1px 4px; color: #000000 !important;">
                                    <strong>{{ spec.label }}:</strong> {{ spec.value }}
                                </span>
                            </div>
                            <!-- ⭐️ All Serial Numbers (S/N) starting directly next to S/N: -->
                            <div v-if="getItemSerials(invd).length > 0" class="text-black mt-1" style="font-size: 11px; line-height: 1.35; color: #000000 !important; word-break: break-word;">
                                <span class="font-monospace text-black fw-bold" style="font-size: 11px; color: #000000 !important;"><strong style="font-weight: 800; color: #000000 !important;">S/N: </strong>{{ getItemSerials(invd).join(', ') }}</span>
                            </div>
                        </td>
                        <td class="text-center font-monospace fw-bold text-nowrap text-black" style="padding: 5px 4px; white-space: nowrap !important; font-size: 12px; color: #000000 !important;">{{ formatQty(invd.qty) }}</td>
                        <!-- Pure numbers without ৳ symbol in table body -->
                        <td class="text-end font-monospace text-nowrap text-black fw-semibold" style="padding: 5px 6px; white-space: nowrap !important; font-size: 11px; color: #000000 !important;">{{ formatPrice(invd.amount) }}</td>
                        <td class="text-end font-monospace fw-bold text-black text-nowrap" style="padding: 5px 6px; white-space: nowrap !important; font-size: 11.5px; color: #000000 !important;">{{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 💰 4. Financial Summary & Bank Details (In Words & Bank on Left, Calculations on Right) -->
        <div class="row g-2 mb-2 align-items-stretch">
            <!-- Left Side: In Words at Top & Bank Details at Bottom (aligned with Previous Due / Calculation bottom) -->
            <div class="col-6 d-flex flex-column justify-content-between">
                <!-- In Words Section (Clean, No Box Border) -->
                <div class="py-1 mb-2" style="font-size: 11px; line-height: 1.4; color: #000000 !important;">
                    <strong class="text-black" style="color: #000000 !important; font-weight: 800;">In Words: </strong>
                    <span class="text-black fw-semibold" style="color: #000000 !important;">{{ numberToWords(data.amount) }}</span>
                </div>
                
                <!-- 🏦 Bank Payment Details (Placed at bottom, aligned with Previous Due / Totals) -->
                <div v-if="currentSite?.bank_name" class="p-2 bg-white rounded border border-dark mt-auto" style="font-size: 10px; color: #000000 !important; border-left: 3.5px solid #000000 !important;">
                    <strong class="text-black d-block mb-1" style="font-size: 10px; font-weight: 800; color: #000000 !important;">
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

            <!-- Right Side: Financial Calculation Table (Pure White Backgrounds, No Blue) -->
            <div class="col-6">
                <table class="table table-sm table-bordered border-dark mb-0 table-calc" style="font-size: 10.5px; width: 100% !important; border-collapse: collapse !important; background: #ffffff !important; background-color: #ffffff !important;">
                    <tbody>
                        <tr style="background: #ffffff !important; background-color: #ffffff !important;">
                            <td class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">{{ $t('Subtotal:') }}</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">৳ {{ formatPrice(data.original_amount) }}</td>
                        </tr>
                        <tr v-if="data.discount > 0" style="background: #ffffff !important; background-color: #ffffff !important;">
                            <td class="text-danger text-nowrap fw-bold" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #dc2626 !important; background: #ffffff !important; background-color: #ffffff !important;">{{ $t('Discount:') }}</td>
                            <td class="text-end font-monospace text-danger fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #dc2626 !important; background: #ffffff !important; background-color: #ffffff !important;">- ৳ {{ formatPrice(data.discount) }}</td>
                        </tr>
                        <tr v-if="data.vat > 0" style="background: #ffffff !important; background-color: #ffffff !important;">
                            <td class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">{{ $t('VAT / Tax:') }}</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">৳ {{ formatPrice(data.vat) }}</td>
                        </tr>
                        <!-- NET TOTAL PAYABLE Row: Distinctly larger font size than VAT / Tax, bold with Top & Bottom Border -->
                        <tr style="background: #ffffff !important; background-color: #ffffff !important; border-top: 2px solid #000000 !important; border-bottom: 2px solid #000000 !important;">
                            <td class="fw-bold text-black text-nowrap" style="background: #ffffff !important; background-color: #ffffff !important; color: #000000 !important; padding: 6px 6px; width: 52%; font-size: 13.5px; font-weight: 900; white-space: nowrap !important;">{{ $t('Net Total Payable:') }}</td>
                            <td class="text-end font-monospace fw-bold text-black text-nowrap" style="background: #ffffff !important; background-color: #ffffff !important; color: #000000 !important; padding: 6px 6px; width: 48%; font-size: 14px; font-weight: 900; white-space: nowrap !important;">৳ {{ formatPrice(data.amount) }}</td>
                        </tr>
                        <tr style="background: #ffffff !important; background-color: #ffffff !important;">
                            <td class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">{{ $t('Paid Amount:') }}</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">৳ {{ formatPrice(data.paid_amount) }}</td>
                        </tr>
                        <tr v-if="data.due_amount > 0" style="background: #ffffff !important; background-color: #ffffff !important;">
                            <td class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">{{ $t('Balance Due:') }}</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">৳ {{ formatPrice(data.due_amount) }}</td>
                        </tr>
                        <tr v-if="data.previous_due > 0" style="background: #ffffff !important; background-color: #ffffff !important;">
                            <td class="text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">{{ $t('Previous Due:') }}</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #000000 !important; background: #ffffff !important; background-color: #ffffff !important;">৳ {{ formatPrice(data.previous_due) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📜 5. Terms & Conditions (Full Width with margin-top gap above separator line) -->
        <div class="row g-2 mt-3 mb-3">
            <div class="col-12">
                <div class="pt-2" style="border-top: 1px solid #000000 !important; font-size: 10px; color: #000000 !important; line-height: 1.45;">
                    <strong class="text-black d-block mb-1" style="font-size: 10.5px; font-weight: 800; color: #000000 !important;">Terms & Conditions:</strong>
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

            // 5. Warranty / Guarantee
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
        getItemSerials(item) {
            if (!item) return [];
            let list = [];
            const extractSerialStr = (s) => {
                if (!s) return '';
                if (typeof s === 'string') return s.trim();
                if (typeof s === 'object') return (s.serial_no || s.serial_number || s.serial || s.number || '').toString().trim();
                return String(s).trim();
            };
            if (Array.isArray(item.serials) && item.serials.length > 0) {
                list = item.serials.map(extractSerialStr).filter(Boolean);
            } else if (Array.isArray(item.serial_numbers) && item.serial_numbers.length > 0) {
                list = item.serial_numbers.map(extractSerialStr).filter(Boolean);
            } else {
                const raw = item.serial_no || item.serial || item.item_serial || '';
                if (typeof raw === 'string' && raw.trim() !== '') {
                    list = raw.split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
                } else if (Array.isArray(raw)) {
                    list = raw.map(extractSerialStr).filter(Boolean);
                }
            }
            return list;
        },
        getSellerName(inv) {
            if (!inv) return 'Cashier';
            return inv.creator?.name || inv.creator?.full_name || this.$root.user?.name || 'Cashier';
        },
        getSellerId(inv) {
            if (!inv) return '';
            return inv.creator?.employee_id || inv.creator?.id || inv.created_by || this.$root.user?.employee_id || this.$root.user?.id || '';
        },
        getSellerMobile(inv) {
            if (!inv) return '';
            return inv.creator?.mobile || inv.creator?.phone || this.$root.user?.mobile || '';
        },
        formatInvoiceDateTime(inv) {
            if (!inv) return '';
            const dateStr = inv.invoice_date || '';
            if (inv.created_at) {
                try {
                    const d = new Date(inv.created_at);
                    if (!isNaN(d.getTime())) {
                        let hours = d.getHours();
                        const minutes = String(d.getMinutes()).padStart(2, '0');
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12;
                        hours = hours ? String(hours).padStart(2, '0') : '12';
                        const timeStr = `${hours}:${minutes} ${ampm}`;
                        
                        if (dateStr) {
                            return `${dateStr} ${timeStr}`;
                        }
                        const day = String(d.getDate()).padStart(2, '0');
                        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        const month = months[d.getMonth()];
                        const year = d.getFullYear();
                        return `${day} ${month}, ${year} ${timeStr}`;
                    }
                } catch (e) {}
            }
            return dateStr;
        },
        formatQty(val) {
            if (val === null || val === undefined || val === '') return '0';
            const num = Number(val);
            if (isNaN(num)) return val;
            if (Number.isInteger(num)) {
                return num.toString();
            }
            return parseFloat(num.toFixed(3)).toString();
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

.invoice-box table,
.invoice-box table > :not(caption) > * > *,
.invoice-box table th,
.invoice-box table td,
.invoice-box table tr {
    background-color: #ffffff !important;
    background: #ffffff !important;
    color: #000000 !important;
    box-shadow: none !important;
}

.invoice-box table thead th {
    background-color: #ffffff !important;
    background: #ffffff !important;
    color: #000000 !important;
}

.invoice-box {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    color: #000000 !important;
    background-color: #ffffff !important;
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
