<template>
    <index-page :defaultTable="false" :show_status="false">
        <!-- 🔍 Search & Filter Section -->
        <template v-slot:search-field>
            <!-- Category Filter -->
            <v-select-container title="Category (ক্যাটাগরি)" field="search_data.category_id" col="3 mb-3">
                <v-select v-model="search_data.category_id" label="title" :reduce="obj => obj.id" :options="categories"
                    placeholder="-- All Categories --" :closeOnSelect="true" />
            </v-select-container>

            <!-- Item Filter -->
            <v-select-container title="Item (পণ্য)" field="search_data.item_id" col="3 mb-3">
                <v-select v-model="search_data.item_id" label="title" :reduce="obj => obj.id" :options="items"
                    placeholder="-- All Items --" :closeOnSelect="true">
                    <template #option="option">
                        <div>
                            <strong>{{ option.title }}</strong>
                            <small class="text-muted d-block" v-if="option.barcode">Barcode: {{ option.barcode }}</small>
                        </div>
                    </template>
                </v-select>
            </v-select-container>

            <!-- Color Filter -->
            <v-select-container title="Color (কালার)" field="search_data.color_id" col="3 mb-3">
                <v-select v-model="search_data.color_id" label="title" :reduce="obj => obj.id" :options="colors"
                    placeholder="-- All Colors --" :closeOnSelect="true" />
            </v-select-container>

            <!-- Size Filter -->
            <v-select-container title="Size (সাইজ)" field="search_data.size_id" col="3 mb-3">
                <v-select v-model="search_data.size_id" label="title" :reduce="obj => obj.id" :options="sizes"
                    placeholder="-- All Sizes --" :closeOnSelect="true" />
            </v-select-container>

            <!-- Stock Status Filter -->
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label class="form-label fw-bold">Stock Status (অবস্থা)</label>
                    <select class="form-select form-select-sm" v-model="search_data.stock_status">
                        <option value="all">-- All Stocks --</option>
                        <option value="in_stock">In Stock (> 0)</option>
                        <option value="out_of_stock">Out of Stock (<= 0)</option>
                        <option value="low_stock">Low Stock (1-5)</option>
                    </select>
                </div>
            </div>

            <!-- Keyword / Barcode Search -->
            <Input v-model="search_data.keyword" field="search_data.keyword" title="Barcode / Item Name"
                placeholder="Type barcode or name..." col="2 mb-3" :req="false" />

            <!-- From Qty -->
            <Input v-model="search_data.from_qty" field="search_data.from_qty" type="number" title="Min Qty (From)"
                placeholder="Min Qty" col="2 mb-3" :req="false" />

            <!-- To Qty -->
            <Input v-model="search_data.to_qty" field="search_data.to_qty" type="number" title="Max Qty (To)"
                placeholder="Max Qty" col="2 mb-3" :req="false" />

            <!-- Is Zero Qty Item? Switch -->
            <SwitchBoolean v-model="search_data.is_zero" field="search_data.is_zero" title="Only Zero Qty?"
                on-label="On" off-label="Off" :req="false" col="3 mb-3"></SwitchBoolean>
        </template>

        <!-- 📊 Table & Content List -->
        <template v-slot:table-list>
            <!-- Top Action Bar -->
            <div class="col-md-12 mb-3">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-secondary">
                            <i class="fas fa-boxes me-1 text-primary"></i> Available Stock Overview (কালার ও সাইজ ভিত্তিক স্টক)
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{ datas.length }} Variants</span>
                    </div>

                    <!-- Print & Export Buttons -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                            @click="resetSearchData">
                            <i class="fas fa-sync-alt"></i> Reset Filter
                        </button>
                        <download-excel
                            v-if="exportData.length > 0"
                            class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
                            :data="exportData"
                            :fields="json_fields"
                            :name="exportFileName">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </download-excel>
                        <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Print Report"
                            v-x-tooltip @click="print('printArea', model)">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🌟 KPI Metric Summary Cards -->
            <div class="col-12 mb-4 d-print-none">
                <div class="row g-3">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-primary text-white h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">Total Variants / Items</div>
                                    <div class="fs-4 fw-bold mt-1 font-monospace">{{ formatNumber(datas.length) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-layer-group"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">Listed Product Varieties</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-info text-white h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">Total Stock In (মোট প্রবেশ)</div>
                                    <div class="fs-4 fw-bold mt-1 font-monospace">{{ formatNumber(summaryData.total_in) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-arrow-circle-down"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">All Time Inflow Quantity</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-danger text-white h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">Total Stock Out (মোট বিক্রয়/নিষ্ক্রান্ত)</div>
                                    <div class="fs-4 fw-bold mt-1 font-monospace">{{ formatNumber(summaryData.total_out) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-arrow-circle-up"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">All Time Outflow Quantity</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-success text-white h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase">Net Available Stock (বর্তমান মজুদ)</div>
                                    <div class="fs-4 fw-bold mt-1 font-monospace">{{ formatNumber(summaryData.total_current_stock) }}</div>
                                </div>
                                <div class="metric-icon"><i class="fas fa-cubes"></i></div>
                            </div>
                            <small class="text-white-50 mt-2 d-block">Current In-Hand Stock</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🖨️ Printable Area -->
            <div class="my-3" id="printArea">
                <!-- 🧾 Report Header (Visible in Print & Screen) -->
                <div class="text-center mb-3 report-title">
                    <h3 class="fw-bold mb-1">{{ $root.site ? $root.site.title : 'QPOS ERP' }}.</h3>
                    <p class="mb-1 text-muted" v-if="$root.site && $root.site.address">{{ $root.site.address }}</p>
                    <p class="small text-muted mb-0" v-if="$root.site">
                        Email: {{ $root.site.contact_email || 'N/A' }} | Phone: {{ $root.site.mobile1 || 'N/A' }}
                    </p>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Available Stock Report (কালার ও সাইজ ভিত্তিক স্টক বিবরণী)</h5>
                        <small class="text-muted">Report Generated: <strong>{{ reportDate }}</strong></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark border font-monospace">
                            Total Records: <strong>{{ datas.length }}</strong>
                        </span>
                    </div>
                </div>

                <!-- 📊 Stock Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr class="fw-bold">
                                <th style="width: 4%;">#</th>
                                <th style="width: 14%;">Category (ক্যাটাগরি)</th>
                                <th style="width: 12%;">Barcode</th>
                                <th style="width: 22%;">Item Name (পণ্যের নাম)</th>
                                <th style="width: 10%;">Color (কালার)</th>
                                <th style="width: 8%;">Size (সাইজ)</th>
                                <th style="width: 8%;">Total In</th>
                                <th style="width: 8%;">Total Out</th>
                                <th style="width: 10%;">Current Stock</th>
                                <th style="width: 4%;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="datas.length > 0">
                                <tr v-for="(stock, index) in datas" :key="index">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="text-center">{{ stock.item && stock.item.category ? stock.item.category.title : 'N/A' }}</td>
                                    <td class="text-center font-monospace">{{ stock.item ? stock.item.barcode : 'N/A' }}</td>
                                    <td class="fw-bold text-dark">{{ stock.item ? stock.item.title : 'N/A' }}</td>
                                    <td class="text-center">
                                        <span v-if="stock.color" class="badge bg-secondary px-2 py-1">
                                            {{ stock.color.title }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="stock.size" class="badge bg-info text-dark font-monospace px-2 py-1">
                                            {{ stock.size.title }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center font-monospace">{{ formatNumber(stock.total_qty_in) }}</td>
                                    <td class="text-center font-monospace">{{ formatNumber(stock.total_qty_out) }}</td>
                                    <td class="text-center font-monospace fw-bold"
                                        :class="Number(stock.current_stock) > 0 ? 'text-success' : 'text-danger'">
                                        {{ formatNumber(stock.current_stock) }}
                                    </td>
                                    <td class="text-center">{{ stock.item && stock.item.unit ? stock.item.unit.title : 'Pcs' }}</td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-boxes fa-3x mb-3 text-secondary opacity-25 d-block"></i>
                                        <h6 class="fw-bold text-secondary">No Stock Records Found</h6>
                                        <p class="small text-muted mb-0">Try changing or clearing your search filters above.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <!-- 📊 Table Footer Totals -->
                        <tfoot class="table-dark font-monospace fw-bold" v-if="datas.length > 0">
                            <tr>
                                <td colspan="6" class="text-end text-uppercase">Total Stock Summary (মোট সর্বমোট):</td>
                                <td class="text-center">{{ formatNumber(summaryData.total_in) }}</td>
                                <td class="text-center">{{ formatNumber(summaryData.total_out) }}</td>
                                <td class="text-center text-warning">{{ formatNumber(summaryData.total_current_stock) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 📌 Footer Note (Visible in Print) -->
                <div class="mt-4 pt-3 border-top small text-muted d-none d-print-block">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <p class="mb-0">Report Generated By: <strong>{{ $root.admin ? $root.admin.name : 'System Admin' }}</strong></p>
                            <p class="mb-0">This is a system generated available stock report and does not require manual signature.</p>
                        </div>
                        <div class="text-center" style="min-width: 160px;">
                            <div style="border-bottom: 1px solid #000; width: 140px; margin: 0 auto 5px auto;"></div>
                            <div>Authorized Signature</div>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </index-page>
</template>

<script>
import axios from "axios";
import moment from "moment";
import JsonExcel from "vue-json-excel3";

const tableColumns = [{ field: "status", title: "Status", align: "center" }];
const model = "availableStock";

export default {
    components: {
        downloadExcel: JsonExcel,
    },
    data() {
        return {
            model: model,
            page_title: "Available Stock Report",
            reportDate: moment().format('D MMMM, YYYY h:mm A'),
            search_data: {
                category_id: null,
                item_id: null,
                color_id: null,
                size_id: null,
                stock_status: "all",
                keyword: "",
                is_zero: 0,
                from_qty: null,
                to_qty: null
            },
            json_fields: {
                "SL": "sl",
                "Category": "category",
                "Barcode": "barcode",
                "Item Name": "item_title",
                "Color": "color",
                "Size": "size",
                "Total In": "total_qty_in",
                "Total Out": "total_qty_out",
                "Current Stock": "current_stock",
                "Unit": "unit"
            },
            table: {
                columns: tableColumns,
                routes: {},
                datas: [],
                meta: [],
                links: [],
            },
            datas: [], // Store available stock records
            categories: [], // Store categories for dropdown
            items: [], // Store items for dropdown
            colors: [], // Store colors for dropdown
            sizes: [], // Store sizes for dropdown
        };
    },

    watch: {
        'search_data.category_id': {
            handler() {
                this.onCategoryChange();
            },
            deep: true
        }
    },

    provide() {
        return {
            validate: this.validation,
            model: this.model,
            search_data: this.search_data,
            table: this.table,
            json_fields: this.json_fields,
            search: this.search,
            resetSearchData: this.resetSearchData,
        };
    },

    computed: {
        summaryData() {
            let total_in = 0;
            let total_out = 0;
            let total_current_stock = 0;

            this.datas.forEach((item) => {
                total_in += Number(item.total_qty_in || 0);
                total_out += Number(item.total_qty_out || 0);
                total_current_stock += Number(item.current_stock || 0);
            });

            return {
                total_in,
                total_out,
                total_current_stock
            };
        },

        exportFileName() {
            return `Available_Stock_Report_${moment().format('YYYY_MM_DD_HHmm')}.xls`;
        },

        exportData() {
            return this.datas.map((stock, index) => {
                return {
                    sl: index + 1,
                    category: stock.item && stock.item.category ? stock.item.category.title : 'N/A',
                    barcode: stock.item ? stock.item.barcode : 'N/A',
                    item_title: stock.item ? stock.item.title : 'N/A',
                    color: stock.color ? stock.color.title : '-',
                    size: stock.size ? stock.size.title : '-',
                    total_qty_in: stock.total_qty_in || 0,
                    total_qty_out: stock.total_qty_out || 0,
                    current_stock: stock.current_stock || 0,
                    unit: stock.item && stock.item.unit ? stock.item.unit.title : 'Pcs'
                };
            });
        }
    },

    methods: {
        search() {
            this.getAvailableStock();
        },

        resetSearchData() {
            this.search_data = {
                category_id: null,
                item_id: null,
                color_id: null,
                size_id: null,
                stock_status: "all",
                keyword: "",
                is_zero: 0,
                from_qty: null,
                to_qty: null
            };
            this.getAvailableStock();
        },

        onCategoryChange() {
            const category_id = this.search_data.category_id;
            if (!category_id) {
                this.fetchAllItems();
                return;
            }

            axios.get(`getitemsbycategory/${category_id}`)
                .then((response) => {
                    this.items = response.data || [];
                    this.search_data.item_id = null;
                })
                .catch(() => {
                    this.items = [];
                });
        },

        getCategories() {
            axios.get(`getcategories/Item`)
                .then((response) => {
                    this.categories = response.data || [];
                });
        },

        fetchAllItems() {
            axios.get(`item?allData=true`)
                .then((response) => {
                    this.items = response.data || [];
                });
        },

        getColors() {
            axios.get(`color?allData=true`)
                .then((response) => {
                    this.colors = response.data || [];
                });
        },

        getSizes() {
            axios.get(`size?allData=true`)
                .then((response) => {
                    this.sizes = response.data || [];
                });
        },

        getAvailableStock() {
            this.$root.spinner = true;
            this.reportDate = moment().format('D MMMM, YYYY h:mm A');

            axios
                .get(`report/availablestock`, { params: this.search_data })
                .then((res) => {
                    this.datas = Array.isArray(res.data) ? res.data : (res.data.datas || []);
                })
                .catch((err) => {
                    console.error("Error loading available stock report:", err);
                    this.$toast("Failed to load available stock report", "error");
                })
                .finally(() => {
                    this.$root.spinner = false;
                });
        },

        formatNumber(val) {
            const num = Number(val || 0);
            return num.toLocaleString();
        }
    },

    created() {
        this.getCategories();
        this.fetchAllItems();
        this.getColors();
        this.getSizes();
        this.getAvailableStock();
    },

    validators: {},
};
</script>

<style scoped>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
}

.metric-icon {
    font-size: 26px;
    opacity: 0.35;
}

@media print {
    .d-print-none {
        display: none !important;
    }
    .p_btn {
        display: none !important;
    }
}
</style>
