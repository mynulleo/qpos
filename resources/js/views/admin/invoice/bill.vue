<template>
    <view-page printArea="invoice_print">
        <div class="container my-3">
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
                                    <i class="fas fa-store me-1 text-primary"></i>Header Info (প্যাড প্রিন্ট): 
                                    <span :class="showHeaderInfo ? 'text-success' : 'text-danger'">{{ showHeaderInfo ? 'ON' : 'OFF' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-light text-muted border font-monospace">
                            Printer: Normal Printer ({{ normalPaperSize }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Invoice Printable Area -->
            <div class="invoice-wrapper mx-auto my-2" id="invoice_print">
                <invoice-layout-1 
                    v-if="selectedLayout === 'layout1' || !isNormalPrinter"
                    :data="data"
                    :site-setting="siteSetting"
                    :show-header-info="showHeaderInfo"
                />
                <invoice-layout-2 
                    v-else-if="selectedLayout === 'layout2'"
                    :data="data"
                    :site-setting="siteSetting"
                    :show-header-info="showHeaderInfo"
                />
                <invoice-layout-3 
                    v-else-if="selectedLayout === 'layout3'"
                    :data="data"
                    :site-setting="siteSetting"
                    :show-header-info="showHeaderInfo"
                />
            </div>
        </div>
    </view-page>
</template>

<script>
import InvoiceLayout1 from './components/InvoiceLayout1.vue';
import InvoiceLayout2 from './components/InvoiceLayout2.vue';
import InvoiceLayout3 from './components/InvoiceLayout3.vue';

const model = "invoice";

export default {
    components: {
        InvoiceLayout1,
        InvoiceLayout2,
        InvoiceLayout3,
    },
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
        normalPaperSize() {
            return this.$root.site?.normal_paper_size || 'A5';
        },
        siteSetting() {
            return this.data?.site_setting || this.site || this.$root.site || {};
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
    },
};
</script>

<style scoped>
.invoice-wrapper {
    max-width: 780px;
    margin: 0 auto;
}

@media print {
    @page {
        size: auto;
        margin: 4mm 5mm;
    }
    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .invoice-wrapper {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
