<template>
    <div class="layout-classic invoice-box bg-white p-3 p-md-4 shadow-sm rounded">
        <!-- ⭐️ LAYOUT 1: Classic Corporate (ক্লাসিক কর্পোরেট) -->
        
        <!-- 🏢 1. Organization Header Section (Logo, Org Name, Address, Contact & Memberships ONLY) -->
        <!-- When header is OFF (showHeaderInfo is false), it maintains minHeight & visibility:hidden for pre-printed pad printing -->
        <div class="org-header-section mb-2 pb-2" 
             :style="{ 
                 visibility: showHeaderInfo ? 'visible' : 'hidden', 
                 minHeight: '105px', 
                 borderBottom: showHeaderInfo ? '1.5px solid #112C47 !important' : '1.5px solid transparent !important' 
             }">
            <div class="row align-items-center g-2">
                <!-- Left: Store Logo & Contact Details -->
                <div class="col-7">
                    <div class="d-flex align-items-center gap-2">
                        <img v-if="storeLogo" :src="storeLogo" alt="Store Logo" class="img-fluid" style="max-height: 52px; max-width: 125px; object-fit: contain;" />
                        <div class="invoice-from">
                            <h2 class="fw-bold mb-0 text-black" style="font-size: 17px; text-transform: uppercase; line-height: 1.2; color: #000000 !important;">{{ currentSite?.title || 'QPOS STORE' }}</h2>
                            <p class="mb-0 text-black fw-medium" style="font-size: 10px; line-height: 1.25; max-width: 320px; color: #000000 !important;">{{ currentSite?.address }}</p>
                            <p class="mb-0 text-black fw-medium" style="font-size: 10px; line-height: 1.25; color: #000000 !important;"><strong>Phone:</strong> {{ currentSite?.mobile1 }} <span v-if="currentSite?.mobile2">/ {{ currentSite?.mobile2 }}</span></p>
                            <p class="mb-0 text-black fw-medium" style="font-size: 10px; line-height: 1.25; color: #000000 !important;" v-if="currentSite?.contact_email || currentSite?.email"><strong>Email:</strong> {{ currentSite?.contact_email || currentSite?.email }}</p>
                            <p class="mb-0 text-black fw-medium" style="font-size: 10px; line-height: 1.25; color: #000000 !important;" v-if="currentSite?.bin_no || currentSite?.vat_no"><strong>BIN / VAT Reg:</strong> {{ currentSite?.bin_no || currentSite?.vat_no }}</p>
                        </div>
                    </div>
                </div>
                <!-- Right: Organization Memberships -->
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

        <!-- 🧾 2. Invoice Details & Customer Info (No Section Borders) -->
        <div class="invoice-info-section mb-2">
            <!-- Centered INVOICE Heading -->
            <div class="text-center my-1">
                <h3 class="fw-bold text-uppercase mb-1" style="color: #000000 !important; font-weight: 900; font-size: 18px; letter-spacing: 2px;">
                    INVOICE
                </h3>
            </div>

            <!-- Meta Information Row (Clean, No Section Box Border) -->
            <div class="d-flex flex-wrap align-items-center justify-content-between py-1 mb-2" style="font-size: 11px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-black fw-bold" style="color: #000000 !important;">Invoice No:</span>
                    <span class="font-monospace fw-bold text-black" style="font-size: 12.5px; color: #000000 !important;">#{{ data.invoice_no }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 text-black">
                    <div style="color: #000000 !important;"><strong>Invoice Date:</strong> <span class="font-monospace text-nowrap fw-bold" style="color: #000000 !important;">{{ data.invoice_date }}</span></div>
                    <div v-if="Number(data.amount) <= Number(data.paid_amount) || data.payment_status === 'Paid'">
                        <span class="text-success fw-bold font-monospace" style="font-size: 10.5px; border: 1.5px solid #16a34a; padding: 1px 6px; border-radius: 3px; background: #f0fdf4;">
                            PAID
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer & Billing Details Grid (Clean, No Section Box Borders) -->
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <div class="py-1 h-100" style="font-size: 10.5px; line-height: 1.35; color: #000000 !important;">
                        <div class="text-uppercase fw-bold mb-1" style="color: #000000 !important; font-size: 10.5px;">Invoice To (গ্রাহক):</div>
                        <div class="fw-bold text-black mb-0" style="font-size: 12px; color: #000000 !important;">{{ data.client?.name || 'Walk-in Customer' }}</div>
                        <div class="text-black" style="color: #000000 !important;" v-if="data.client?.mobile"><strong>Phone:</strong> {{ data.client.mobile }}</div>
                        <div class="text-black" style="color: #000000 !important;" v-if="data.client?.address"><strong>Address:</strong> {{ data.client.address }}</div>
                        <div class="text-black" style="color: #000000 !important;" v-if="data.delivery_address"><strong>Delivery:</strong> {{ data.delivery_address }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="py-1 h-100 text-end d-flex flex-column justify-content-between" style="font-size: 10.5px; line-height: 1.35; color: #000000 !important;">
                        <div>
                            <div class="text-uppercase fw-bold mb-1" style="color: #000000 !important; font-size: 10.5px;">Billing Information:</div>
                            <div class="text-black" style="color: #000000 !important;"><strong>Sold By:</strong> {{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}</div>
                            <div class="text-black" style="color: #000000 !important;" v-if="data.vehicle_info"><strong>Vehicle / Info:</strong> {{ data.vehicle_info }}</div>
                        </div>
                        <div class="font-monospace text-black fw-semibold mt-1" style="font-size: 9.5px; color: #000000 !important;">
                            <span>Generated By: QPOS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📦 3. Items Table -->
        <div class="table-responsive mb-2">
            <table class="table table-bordered border-dark align-middle mb-0" style="font-size: 11px; width: 100%;">
                <thead style="background-color: #112C47; color: #fff;">
                    <tr>
                        <th width="4%" class="text-center text-white" style="background-color: #112C47; color: #fff; padding: 4px;">{{ $t('#') }}</th>
                        <th width="48%" class="text-white" style="background-color: #112C47; color: #fff; padding: 4px 6px;">{{ $t('Item Description & Specifications') }}</th>
                        <th width="10%" class="text-center text-white text-nowrap" style="background-color: #112C47; color: #fff; padding: 4px; white-space: nowrap !important;">{{ $t('Qty') }}</th>
                        <th width="19%" class="text-end text-white text-nowrap" style="background-color: #112C47; color: #fff; padding: 4px 6px; white-space: nowrap !important;">{{ $t('Unit Rate') }}</th>
                        <th width="19%" class="text-end text-white text-nowrap" style="background-color: #112C47; color: #fff; padding: 4px 6px; white-space: nowrap !important;">{{ $t('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(invd, index) in getItemList(data)" :key="index" style="color: #000000 !important;">
                        <td class="text-center text-black fw-bold" style="padding: 4px; color: #000000 !important;">{{ index + 1 }}</td>
                        <td style="padding: 4px 6px; color: #000000 !important;">
                            <div class="fw-bold text-black" style="font-size: 11.5px; line-height: 1.25; color: #000000 !important;">{{ getItemTitle(invd) }}</div>
                            <!-- ⭐️ Dynamically render Model, Brand, Color, Size, Serial No, Warranty ONLY if present -->
                            <div class="d-flex flex-wrap gap-1 mt-1" v-if="getItemSpecs(invd).length > 0">
                                <span v-for="(spec, sIdx) in getItemSpecs(invd)" :key="sIdx" 
                                      class="badge bg-light text-black border border-dark font-monospace" 
                                      style="font-size: 9.5px; font-weight: 600; padding: 1px 4px; color: #000000 !important;">
                                    <strong>{{ spec.label }}:</strong> {{ spec.value }}
                                </span>
                            </div>
                            <div v-if="getItemBarcode(invd)" class="text-black font-monospace mt-1" style="font-size: 9px; color: #000000 !important;">
                                Barcode: {{ getItemBarcode(invd) }}
                            </div>
                        </td>
                        <td class="text-center font-monospace fw-bold text-nowrap text-black" style="padding: 4px; white-space: nowrap !important; font-size: 11.5px; color: #000000 !important;">{{ invd.qty }}</td>
                        <td class="text-end font-monospace text-nowrap text-black fw-semibold" style="padding: 4px 6px; white-space: nowrap !important; font-size: 11px; color: #000000 !important;">৳ {{ formatPrice(invd.amount) }}</td>
                        <td class="text-end font-monospace fw-bold text-black text-nowrap" style="padding: 4px 6px; white-space: nowrap !important; font-size: 11.5px; color: #000000 !important;">৳ {{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 💰 4. Summary & Terms (Clean, No Section Box Borders) -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="py-1 mb-1" style="font-size: 10.5px; line-height: 1.35; color: #000000 !important;">
                    <strong class="text-black" style="color: #000000 !important;">In Words: </strong>
                    <span class="text-black fw-semibold" style="color: #000000 !important;">{{ numberToWords(data.amount) }}</span>
                </div>
                <div class="py-1" style="font-size: 9.5px; color: #000000 !important; line-height: 1.35;">
                    <strong class="text-black d-block mb-1" style="color: #000000 !important;">Terms & Conditions:</strong>
                    <div v-if="data.terms_conditions && data.terms_conditions.length > 0" class="text-black" style="color: #000000 !important;">
                        <div v-for="(tc, tcIdx) in data.terms_conditions" :key="tcIdx">{{ tcIdx + 1 }}. {{ tc }}</div>
                    </div>
                    <div v-else class="text-black" style="color: #000000 !important;">
                        <div>1. Goods sold are eligible for warranty replacement per policy.</div>
                        <div>2. Commercial claims require presenting this original invoice.</div>
                    </div>
                    
                    <!-- 🏦 Bank Details -->
                    <div v-if="currentSite?.bank_name" class="mt-2 pt-1">
                        <strong class="text-black d-block mb-1" style="font-size: 10px; color: #000000 !important;"><i class="fas fa-university me-1 text-primary"></i>Bank Details:</strong>
                        <div class="fw-bold text-black font-monospace" style="font-size: 10px; color: #000000 !important;">
                            Bank: {{ currentSite.bank_name }} <span v-if="currentSite.branch_name">({{ currentSite.branch_name }})</span> | A/C: {{ currentSite.account_number }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <table class="table table-sm table-bordered border-dark mb-0" style="font-size: 10.5px; width: 100% !important; border-collapse: collapse !important;">
                    <tbody>
                        <tr style="background-color: #112C47 !important; color: #ffffff !important;">
                            <th class="text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 52%; white-space: nowrap !important;">{{ $t('Subtotal:') }}</th>
                            <td class="text-end font-monospace text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 48%; white-space: nowrap !important;">৳ {{ formatPrice(data.original_amount) }}</td>
                        </tr>
                        <tr v-if="data.discount > 0">
                            <th class="text-danger text-nowrap fw-bold" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #dc2626 !important;">{{ $t('Special Discount:') }}</th>
                            <td class="text-end font-monospace text-danger fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #dc2626 !important;">- ৳ {{ formatPrice(data.discount) }}</td>
                        </tr>
                        <tr v-if="data.vat > 0" style="background-color: #112C47 !important; color: #ffffff !important;">
                            <th class="text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 52%; white-space: nowrap !important;">{{ $t('VAT / Tax:') }}</th>
                            <td class="text-end font-monospace text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 48%; white-space: nowrap !important;">+ ৳ {{ formatPrice(data.vat) }}</td>
                        </tr>
                        <tr style="background-color: #0c1f33 !important; color: #ffffff !important; font-weight: bold; border-top: 2px solid #0c1f33; border-bottom: 2px solid #0c1f33;">
                            <th class="fw-bold text-white text-nowrap" style="background-color: #0c1f33 !important; color: #ffffff !important; padding: 5px 6px; width: 52%; font-size: 11.5px; font-weight: 900; white-space: nowrap !important;">{{ $t('NET TOTAL PAYABLE:') }}</th>
                            <td class="text-end font-monospace fw-bold text-white text-nowrap" style="background-color: #0c1f33 !important; color: #ffffff !important; padding: 5px 6px; width: 48%; font-size: 11.5px; font-weight: 900; white-space: nowrap !important;">৳ {{ formatPrice(data.amount) }}</td>
                        </tr>
                        <tr style="background-color: #112C47 !important; color: #ffffff !important;">
                            <th class="text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 52%; white-space: nowrap !important;">{{ $t('Paid Amount:') }}</th>
                            <td class="text-end font-monospace text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 48%; white-space: nowrap !important;">৳ {{ formatPrice(data.paid_amount) }}</td>
                        </tr>
                        <tr v-if="data.due_amount > 0">
                            <th class="text-danger fw-bold text-nowrap" style="padding: 4px 6px; width: 52%; white-space: nowrap !important; color: #dc2626 !important;">{{ $t('Balance Due:') }}</th>
                            <td class="text-end font-monospace text-danger fw-bold text-nowrap" style="padding: 4px 6px; width: 48%; white-space: nowrap !important; color: #dc2626 !important;">৳ {{ formatPrice(data.due_amount) }}</td>
                        </tr>
                        <tr v-if="data.previous_due > 0" style="background-color: #112C47 !important; color: #ffffff !important;">
                            <th class="text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 52%; white-space: nowrap !important;">{{ $t('Previous Due:') }}</th>
                            <td class="text-end font-monospace text-white fw-bold text-nowrap" style="background-color: #112C47 !important; color: #ffffff !important; padding: 4px 6px; width: 48%; white-space: nowrap !important;">৳ {{ formatPrice(data.previous_due) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ✍️ 5. Signatures (Positioned at bottom of page for print preview) -->
        <div class="row signature-section" style="font-size: 10px; margin-top: auto !important; padding-top: 30px;">
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
    name: 'InvoiceLayout1',
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
    padding-top: 30px;
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
