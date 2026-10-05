<template>
    <div class="layout-compact invoice-box bg-white p-2 p-md-3 shadow-sm">
        <!-- ⭐️ LAYOUT 3: Compact Executive (কমপ্যাক্ট এক্সিকিউটিভ) -->
        
        <!-- Centered Header -->
        <div class="org-header-section text-center border-bottom border-dark pb-2 mb-2"
             :style="{ 
                 visibility: showHeaderInfo ? 'visible' : 'hidden', 
                 minHeight: '95px' 
             }">
            <div v-if="storeLogo" class="d-flex justify-content-center mb-1">
                <img :src="storeLogo" alt="Store Logo" class="img-fluid" style="max-height: 44px; max-width: 125px; object-fit: contain;" />
            </div>
            <h3 class="fw-bold text-black text-uppercase mb-0" style="font-size: 16px; line-height: 1.2; color: #000000 !important;">{{ currentSite.title || 'QPOS STORE' }}</h3>
            <p class="mb-0 text-black fw-medium" style="font-size: 9.5px; line-height: 1.25; color: #000000 !important;">{{ currentSite.address }}</p>
            <p class="mb-0 text-black fw-medium" style="font-size: 9.5px; line-height: 1.25; color: #000000 !important;">
                Phone: {{ currentSite.mobile1 }} <span v-if="currentSite.mobile2">/ {{ currentSite.mobile2 }}</span> | Email: {{ currentSite.contact_email || currentSite.email }}
                <span v-if="currentSite.bin_no || currentSite.vat_no"> | BIN: {{ currentSite.bin_no || currentSite.vat_no }}</span>
            </p>
            <div v-if="orgMemberships && orgMemberships.length > 0" class="d-flex justify-content-center align-items-center gap-2 mt-1">
                <span class="text-uppercase fw-bold text-black me-1" style="font-size: 9px; letter-spacing: 0.5px; color: #000000 !important;">Member of:</span>
                <template v-for="(m, mIdx) in orgMemberships" :key="mIdx">
                    <img v-if="m.logo || m.logo_url" 
                         :src="m.logo_url || m.logo" 
                         :alt="m.org_name || 'Organization Logo'"
                         :title="m.org_name"
                         style="max-height: 28px; max-width: 65px; object-fit: contain;" />
                    <span v-else class="badge bg-white text-black border border-dark font-monospace" style="font-size: 9px; padding: 2px 5px; color: #000000 !important;">{{ m.org_name }}</span>
                </template>
            </div>
        </div>

        <div class="text-center fw-bold text-black mb-1" style="font-size: 12px; letter-spacing: 2px; color: #000000 !important;">
            --- INVOICE ---
        </div>

        <!-- 4-Quadrant Info Table (Status removed, Sold By added) -->
        <table class="table table-sm table-bordered border-dark mb-2" style="font-size: 10.5px; color: #000000 !important;">
            <tbody>
                <tr style="color: #000000 !important;">
                    <td width="33%" class="text-black" style="padding: 3px 6px; color: #000000 !important;"><strong>Invoice No:</strong> <span class="font-monospace fw-bold" style="color: #000000 !important;">#{{ data.invoice_no }}</span></td>
                    <td width="33%" class="text-black" style="padding: 3px 6px; color: #000000 !important;"><strong>Date:</strong> <span class="text-nowrap" style="white-space: nowrap !important; color: #000000 !important;">{{ data.invoice_date }}</span></td>
                    <td width="34%" class="text-black" style="padding: 3px 6px; color: #000000 !important;">
                        <strong>Sold By:</strong> <span class="fw-semibold" style="color: #000000 !important;">{{ data.creator ? data.creator.name : ($root.user?.name || 'Cashier') }}</span>
                    </td>
                </tr>
                <tr style="color: #000000 !important;">
                    <td colspan="2" style="padding: 3px 6px; color: #000000 !important;">
                        <strong>Customer:</strong> {{ data.client?.name || 'Walk-in Customer' }}
                        <span v-if="data.client?.mobile" class="ms-1 text-black" style="color: #000000 !important;">({{ data.client.mobile }})</span>
                    </td>
                    <td style="padding: 3px 6px; color: #000000 !important;">
                        <strong>Address:</strong> {{ data.delivery_address || (data.client?.address || 'N/A') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Items Table -->
        <table class="table table-sm table-bordered border-dark align-middle mb-2" style="font-size: 10.5px; width: 100%;">
            <thead class="border-dark" style="background-color: #000000 !important; color: #ffffff !important;">
                <tr>
                    <th width="4%" class="text-center text-white" style="background-color: #000000 !important; color: #ffffff !important; padding: 3px 4px;">{{ $t('SL') }}</th>
                    <th width="48%" class="text-white" style="background-color: #000000 !important; color: #ffffff !important; padding: 3px 6px;">{{ $t('Item Description & Specifications') }}</th>
                    <th width="10%" class="text-center text-white text-nowrap" style="background-color: #000000 !important; color: #ffffff !important; padding: 3px 4px; white-space: nowrap !important;">{{ $t('Qty') }}</th>
                    <th width="19%" class="text-end text-white text-nowrap" style="background-color: #000000 !important; color: #ffffff !important; padding: 3px 6px; white-space: nowrap !important;">{{ $t('Rate') }}</th>
                    <th width="19%" class="text-end text-white text-nowrap" style="background-color: #000000 !important; color: #ffffff !important; padding: 3px 6px; white-space: nowrap !important;">{{ $t('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(invd, index) in getItemList(data)" :key="index" style="color: #000000 !important;">
                    <td class="text-center text-black fw-bold" style="padding: 3px 4px; color: #000000 !important;">{{ index + 1 }}</td>
                    <td style="padding: 3px 6px; color: #000000 !important;">
                        <div class="fw-bold text-black" style="font-size: 11px; line-height: 1.25; color: #000000 !important;">{{ getItemTitle(invd) }}</div>
                        <!-- ⭐️ Specifications -->
                        <div class="d-flex flex-wrap gap-1 mt-1" v-if="getItemSpecs(invd).length > 0">
                            <span v-for="(spec, sIdx) in getItemSpecs(invd)" :key="sIdx" 
                                  style="background: #f8fafc; border: 1px solid #000000; border-radius: 2px; padding: 1px 4px; font-size: 9px; color: #000; display: inline-block;">
                                <strong>{{ spec.label }}:</strong> {{ spec.value }}
                            </span>
                        </div>
                        <div v-if="getItemBarcode(invd)" class="text-black font-monospace mt-1" style="font-size: 9px; color: #000000 !important;">Barcode: {{ getItemBarcode(invd) }}</div>
                    </td>
                    <td class="text-center font-monospace fw-bold text-nowrap text-black" style="padding: 3px 4px; white-space: nowrap !important; font-size: 11px; color: #000000 !important;">{{ invd.qty }}</td>
                    <td class="text-end font-monospace text-nowrap text-black fw-semibold" style="padding: 3px 6px; white-space: nowrap !important; font-size: 10.5px; color: #000000 !important;">৳ {{ formatPrice(invd.amount) }}</td>
                    <td class="text-end font-monospace fw-bold text-black text-nowrap" style="padding: 3px 6px; white-space: nowrap !important; font-size: 11px; color: #000000 !important;">৳ {{ formatPrice(invd.total_amount || (invd.qty * invd.amount)) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Financial Summary Row (In Words & Bank on Left, Calculations on Right) -->
        <div class="row g-2 mb-2">
            <!-- Left Side: In Words (No Border) & Bank Details -->
            <div class="col-6">
                <!-- In Words (Clean, Outside Box Borders) -->
                <div class="py-1 mb-1" style="font-size: 10px; line-height: 1.35; color: #000000 !important;">
                    <strong style="color: #000000 !important;">In Words:</strong> 
                    <span style="color: #000000 !important;">{{ numberToWords(data.amount) }}</span>
                </div>
                
                <!-- Bank Details directly below In Words -->
                <div v-if="currentSite.bank_name" class="py-1" style="font-size: 9.5px; color: #000000 !important; line-height: 1.35;">
                    <strong style="color: #000000 !important;"><i class="fas fa-university me-1 text-black"></i>Bank Details:</strong> 
                    <div class="font-monospace fw-bold" style="color: #000000 !important;">
                        {{ currentSite.bank_name }} <span v-if="currentSite.branch_name">({{ currentSite.branch_name }})</span> | A/C: {{ currentSite.account_number }}
                    </div>
                </div>
            </div>

            <!-- Right Side: Financial Calculation Table -->
            <div class="col-6">
                <table class="table table-sm table-bordered border-dark mb-0" style="font-size: 10px; width: 100% !important; color: #000000 !important;">
                    <tbody>
                        <tr>
                            <td class="text-nowrap text-black fw-bold" style="padding: 2px 4px; width: 52%; white-space: nowrap !important; color: #000000 !important;">Subtotal:</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 2px 4px; width: 48%; white-space: nowrap !important; color: #000000 !important;">৳ {{ formatPrice(data.original_amount) }}</td>
                        </tr>
                        <tr v-if="data.discount > 0">
                            <td class="text-danger text-nowrap fw-bold" style="padding: 2px 4px; width: 52%; white-space: nowrap !important;">Discount:</td>
                            <td class="text-end font-monospace text-danger fw-bold text-nowrap" style="padding: 2px 4px; width: 48%; white-space: nowrap !important;">- ৳ {{ formatPrice(data.discount) }}</td>
                        </tr>
                        <tr v-if="data.vat > 0">
                            <td class="text-nowrap text-black fw-bold" style="padding: 2px 4px; width: 52%; white-space: nowrap !important; color: #000000 !important;">VAT / Tax:</td>
                            <td class="text-end font-monospace text-nowrap text-black fw-bold" style="padding: 2px 4px; width: 48%; white-space: nowrap !important;">+ ৳ {{ formatPrice(data.vat) }}</td>
                        </tr>
                        <tr style="background-color: #000000 !important; color: #ffffff !important; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">
                            <td style="font-weight: 900; color: #ffffff !important; padding: 4px 4px; width: 52%; white-space: nowrap !important; background-color: #000000 !important;" class="text-nowrap">NET TOTAL PAYABLE:</td>
                            <td class="text-end font-monospace text-nowrap" style="font-weight: 900; color: #ffffff !important; padding: 4px 4px; width: 48%; white-space: nowrap !important; background-color: #000000 !important;">৳ {{ formatPrice(data.amount) }}</td>
                        </tr>
                        <tr>
                            <td class="text-black fw-bold text-nowrap" style="padding: 2px 4px; width: 52%; white-space: nowrap !important; color: #000000 !important;">Paid Amount:</td>
                            <td class="text-end font-monospace text-black fw-bold text-nowrap" style="padding: 2px 4px; width: 48%; white-space: nowrap !important;">৳ {{ formatPrice(data.paid_amount) }}</td>
                        </tr>
                        <tr v-if="data.due_amount > 0">
                            <td class="text-danger fw-bold text-nowrap" style="padding: 2px 4px; width: 52%; white-space: nowrap !important;">Balance Due:</td>
                            <td class="text-end font-monospace text-danger fw-bold text-nowrap" style="padding: 2px 4px; width: 48%; white-space: nowrap !important;">৳ {{ formatPrice(data.due_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📜 Terms & Conditions (Full Width with Light Separator Line & Vertical List) -->
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="pt-2" style="border-top: 1px solid #cbd5e1 !important; font-size: 9.5px; color: #000000 !important; line-height: 1.4;">
                    <strong class="text-black d-block mb-1" style="font-size: 10px; color: #000000 !important;">Terms & Conditions:</strong>
                    <div v-if="data.terms_conditions && data.terms_conditions.length > 0" class="text-black" style="color: #000000 !important;">
                        <div v-for="(tc, tcIdx) in data.terms_conditions" :key="tcIdx" class="mb-1">
                            {{ tcIdx + 1 }}. {{ tc }}
                        </div>
                    </div>
                    <div v-else class="text-black" style="color: #000000 !important;">
                        <div class="mb-1">1. Goods once sold are subject to standard return policy.</div>
                        <div class="mb-1">2. Commercial claims require presenting this original invoice.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✍️ 3 Signatures in Compact Box (Bottom Pinned) -->
        <div class="row signature-section" style="font-size: 9.5px; margin-top: auto !important; padding-top: 30px;">
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 mx-2 fw-bold text-black" style="color: #000000 !important;">Received By</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 mx-2 fw-bold text-black" style="color: #000000 !important;">Prepared By</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-1 mx-2 fw-bold text-black" style="color: #000000 !important;">Authorized Signatory</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceLayout3',
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

            const brand = item.brand_title || item.brand?.title || item.item?.brand?.title;
            if (brand && typeof brand === 'string' && brand.trim() !== '') {
                specs.push({ label: 'Brand', value: brand.trim() });
            }

            const model = item.model_no || item.model || item.item?.model_no || item.series_title || item.series?.title || item.item?.series?.title;
            if (model && typeof model === 'string' && model.trim() !== '') {
                specs.push({ label: 'Model', value: model.trim() });
            }

            const color = item.color_title || item.color?.title || item.color?.name || item.item?.color?.title || item.item?.color?.name;
            if (color && typeof color === 'string' && color.trim() !== '') {
                specs.push({ label: 'Color', value: color.trim() });
            }

            const size = item.size_title || item.size?.title || item.size?.name || item.item?.size?.title || item.item?.size?.name;
            if (size && typeof size === 'string' && size.trim() !== '') {
                specs.push({ label: 'Size', value: size.trim() });
            }

            const serial = item.serial_no || item.serial || item.item_serial;
            if (serial && typeof serial === 'string' && serial.trim() !== '') {
                specs.push({ label: 'Serial No', value: serial.trim() });
            }

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
}
</style>
