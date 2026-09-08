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
                    <select class="form-select form-select-sm" v-model="search_data.stock_status" @change="onStockStatusFilterChange">
                        <option value="low_stock">Low Stock (কম স্টক)</option>
                        <option value="out_of_stock">Out of Stock (মজুদ শূন্য)</option>
                        <option value="all">All Stocks (সকল পণ্য)</option>
                    </select>
                </div>
            </div>

            <!-- Limit Selector -->
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label class="form-label fw-bold">Display Limit (রেকর্ড সংখ্যা)</label>
                    <select class="form-select form-select-sm" v-model="search_data.limit">
                        <option value="50">Top 50 (ডিফল্ট)</option>
                        <option value="100">Top 100</option>
                        <option value="250">Top 250</option>
                        <option value="500">Top 500</option>
                        <option value="all">All Records (সকল)</option>
                    </select>
                </div>
            </div>

            <!-- Sort By Selector -->
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label class="form-label fw-bold">Sort By (সাজানো)</label>
                    <select class="form-select form-select-sm" v-model="search_data.sort_by">
                        <option value="stock_asc">Stock: Low to High (কম থেকে বেশি)</option>
                        <option value="stock_desc">Stock: High to Low (বেশি থেকে কম)</option>
                        <option value="item_id">Item ID (আইটেম অনুযায়ী)</option>
                    </select>
                </div>
            </div>

            <!-- Low Stock Threshold -->
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label class="form-label fw-bold">Low Threshold (সতর্ক সীমা)</label>
                    <input type="number" class="form-control form-control-sm" v-model.number="search_data.low_threshold"
                        placeholder="Default: 5" min="1" max="100" />
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
                on-label="On" off-label="Off" :req="false" col="2 mb-3"></SwitchBoolean>
        </template>

        <!-- 📊 Table & Content List -->
        <template v-slot:table-list>
            <!-- 🌟 Clean Theme Quick Status Tabs Bar -->
            <div class="col-12 mb-3 d-print-none">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between p-2 bg-white rounded border shadow-sm">
                    <ul class="nav nav-pills custom-theme-tabs gap-2 align-items-center mb-0">
                        <li class="nav-item">
                            <button class="nav-link btn-theme-tab" :class="{ 'active tab-danger': activeTab === 'low_stock' }"
                                @click="switchTab('low_stock')">
                                <i class="fas fa-exclamation-triangle me-1 text-danger"></i> Low Stock Alert (কম স্টক - শীর্ষ ৫০)
                                <span class="badge bg-danger ms-2 rounded-pill">{{ formatNumber(tabCounts.low_stock) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-theme-tab" :class="{ 'active tab-warning': activeTab === 'out_of_stock' }"
                                @click="switchTab('out_of_stock')">
                                <i class="fas fa-times-circle me-1 text-warning"></i> Out of Stock (স্টক শেষ)
                                <span class="badge bg-secondary ms-2 rounded-pill">{{ formatNumber(tabCounts.out_of_stock) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-theme-tab" :class="{ 'active tab-primary': activeTab === 'all' }"
                                @click="switchTab('all')">
                                <i class="fas fa-boxes me-1 text-primary"></i> All Stock (সকল পণ্য)
                                <span class="badge bg-primary ms-2 rounded-pill">{{ formatNumber(tabCounts.all) }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-2">
                        <button v-if="search_data.limit === '50' && tabCounts.all > 50" type="button"
                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                            @click="loadAllStock">
                            <i class="fas fa-list"></i> View All ({{ tabCounts.all }})
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                            @click="resetSearchData">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                        <download-excel
                            v-if="exportData.length > 0"
                            class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1"
                            :data="exportData"
                            :fields="json_fields"
                            :name="exportFileName">
                            <i class="fas fa-file-excel"></i> Excel
                        </download-excel>
                        <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Print Report"
                            v-x-tooltip @click="print('printArea', model)">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🌟 Clean Corporate KPI Summary Cards -->
            <div class="col-12 mb-3 d-print-none">
                <div class="row g-3">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border border-start-3 border-danger shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer"
                            @click="switchTab('low_stock')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase">Low Stock Alert (কম স্টক)</div>
                                    <div class="fs-4 fw-bold mt-1 text-danger font-monospace">{{ formatNumber(tabCounts.low_stock) }} Items</div>
                                </div>
                                <div class="metric-icon text-danger opacity-50"><i class="fas fa-exclamation-triangle"></i></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Stock &le; {{ search_data.low_threshold || 5 }} (Reorder Urgently)</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border border-start-3 border-warning shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer"
                            @click="switchTab('out_of_stock')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase">Out of Stock (স্টক শেষ)</div>
                                    <div class="fs-4 fw-bold mt-1 text-dark font-monospace">{{ formatNumber(tabCounts.out_of_stock) }} Items</div>
                                </div>
                                <div class="metric-icon text-warning opacity-50"><i class="fas fa-times-circle"></i></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Quantity &le; 0 (Unavailable)</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border border-start-3 border-success shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer"
                            @click="switchTab('all')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase">Net In-Hand Stock (বর্তমান মজুদ)</div>
                                    <div class="fs-4 fw-bold mt-1 text-success font-monospace">{{ formatNumber(summaryData.total_current_stock) }} Pcs</div>
                                </div>
                                <div class="metric-icon text-success opacity-50"><i class="fas fa-cubes"></i></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Inflow: {{ formatNumber(summaryData.total_in) }} | Outflow: {{ formatNumber(summaryData.total_out) }}</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card border border-start-3 border-primary shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer"
                            @click="switchTab('all')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase">Total Variants (মোট ভ্যারিয়েন্ট)</div>
                                    <div class="fs-4 fw-bold mt-1 text-primary font-monospace">{{ formatNumber(tabCounts.all) }}</div>
                                </div>
                                <div class="metric-icon text-primary opacity-50"><i class="fas fa-boxes"></i></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Total Registered Variants</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💡 Performance & Limit Notice Banner -->
            <div v-if="search_data.limit !== 'all' && datas.length >= Number(search_data.limit)"
                class="alert alert-light border py-2 px-3 small d-flex justify-content-between align-items-center mb-3 d-print-none rounded-3">
                <div class="text-secondary">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Showing top <strong>{{ datas.length }}</strong> records for fast performance.
                    <span v-if="activeTab === 'low_stock'">Showing products with lowest stock first.</span>
                </div>
                <button class="btn btn-xs btn-outline-primary fw-bold px-3 py-1" @click="loadAllStock">
                    <i class="fas fa-sync-alt me-1"></i> Show All {{ tabCounts.all }} Records
                </button>
            </div>

            <!-- 🖨️ Printable Area -->
            <div class="my-2" id="printArea">
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
                        <h5 class="fw-bold mb-0 text-dark">
                            <span v-if="activeTab === 'low_stock'">Low Stock Warning Report (কম মজুদ সতর্কবার্তা রিপোর্ট)</span>
                            <span v-else-if="activeTab === 'out_of_stock'">Out of Stock Report (মজুদ শূন্য পণ্য রিপোর্ট)</span>
                            <span v-else>Available Stock Report (কালার ও সাইজ ভিত্তিক মজুদ রিপোর্ট)</span>
                        </h5>
                        <small class="text-muted">Report Generated: <strong>{{ reportDate }}</strong> | View: <strong class="text-capitalize">{{ activeTab.replace('_', ' ') }}</strong></small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark border font-monospace">
                            Showing Records: <strong>{{ datas.length }}</strong> / Total: <strong>{{ tabCounts.all }}</strong>
                        </span>
                    </div>
                </div>

                <!-- 📊 Stock Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr class="fw-bold">
                                <th style="width: 4%;">#</th>
                                <th style="width: 14%;">Category (ক্যাটাগরি)</th>
                                <th style="width: 12%;">Barcode</th>
                                <th style="width: 23%;">Item Name (পণ্যের নাম)</th>
                                <th style="width: 9%;">Color (কালার)</th>
                                <th style="width: 8%;">Size (সাইজ)</th>
                                <th style="width: 8%;">Total In</th>
                                <th style="width: 8%;">Total Out</th>
                                <th style="width: 8%;">Current Stock</th>
                                <th style="width: 6%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="datas.length > 0">
                                <tr v-for="(stock, index) in datas" :key="index">
                                    <td class="text-center text-muted">{{ index + 1 }}</td>
                                    <td class="text-center">{{ stock.item && stock.item.category ? stock.item.category.title : 'N/A' }}</td>
                                    <td class="text-center font-monospace">{{ stock.item ? stock.item.barcode : 'N/A' }}</td>
                                    <td class="fw-bold text-dark">
                                        {{ stock.item ? stock.item.title : 'N/A' }}
                                        <small class="text-muted d-block fw-normal" v-if="stock.item && stock.item.unit">
                                            Unit: {{ stock.item.unit.title }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="stock.color" class="badge bg-light text-dark border px-2 py-1">
                                            {{ stock.color.title }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="stock.size" class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                            {{ stock.size.title }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center font-monospace">{{ formatNumber(stock.total_qty_in) }}</td>
                                    <td class="text-center font-monospace">{{ formatNumber(stock.total_qty_out) }}</td>
                                    <td class="text-center font-monospace fw-bold"
                                        :class="Number(stock.current_stock) > 0 ? (Number(stock.current_stock) <= Number(search_data.low_threshold || 5) ? 'text-danger' : 'text-success') : 'text-danger'">
                                        {{ formatNumber(stock.current_stock) }}
                                    </td>
                                    <td class="text-center">
                                        <span v-if="Number(stock.current_stock) <= 0" class="badge bg-danger">
                                            Out of Stock
                                        </span>
                                        <span v-else-if="Number(stock.current_stock) <= Number(search_data.low_threshold || 5)" class="badge bg-warning text-dark">
                                            Low Stock
                                        </span>
                                        <span v-else class="badge bg-light text-success border">
                                            In Stock
                                        </span>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fas fa-boxes fa-3x mb-3 text-secondary opacity-25 d-block"></i>
                                        <h6 class="fw-bold text-secondary">No Stock Records Found</h6>
                                        <p class="small text-muted mb-0">Try changing or clearing your search filters or switching tabs.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <!-- 📊 Table Footer Totals -->
                        <tfoot class="table-dark font-monospace fw-bold" v-if="datas.length > 0">
                            <tr>
                                <td colspan="6" class="text-end text-uppercase">Displayed Rows Total:</td>
                                <td class="text-center">{{ formatNumber(displayedTotals.total_in) }}</td>
                                <td class="text-center">{{ formatNumber(displayedTotals.total_out) }}</td>
                                <td class="text-center text-warning">{{ formatNumber(displayedTotals.total_current_stock) }}</td>
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
            activeTab: "low_stock", // Default to low_stock for high speed and immediate low stock alert
            search_data: {
                category_id: null,
                item_id: null,
                color_id: null,
                size_id: null,
                stock_status: "low_stock", // Default low stock filter
                limit: "50", // Default limit 50 items
                sort_by: "stock_asc", // Default sort lowest stock first
                low_threshold: 5,
                keyword: "",
                is_zero: 0,
                from_qty: null,
                to_qty: null
            },
            tabCounts: {
                low_stock: 0,
                out_of_stock: 0,
                in_stock: 0,
                negative_stock: 0,
                all: 0,
            },
            backendSummary: {
                total_in: 0,
                total_out: 0,
                total_current_stock: 0,
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
                "Status": "status",
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
        displayedTotals() {
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

        summaryData() {
            return {
                total_in: this.backendSummary.total_in || this.displayedTotals.total_in,
                total_out: this.backendSummary.total_out || this.displayedTotals.total_out,
                total_current_stock: this.backendSummary.total_current_stock || this.displayedTotals.total_current_stock
            };
        },

        exportFileName() {
            return `Available_Stock_Report_${this.activeTab}_${moment().format('YYYY_MM_DD_HHmm')}.xls`;
        },

        exportData() {
            return this.datas.map((stock, index) => {
                let statusText = 'In Stock';
                const currentStock = Number(stock.current_stock || 0);
                if (currentStock <= 0) {
                    statusText = 'Out of Stock';
                } else if (currentStock <= Number(this.search_data.low_threshold || 5)) {
                    statusText = 'Low Stock';
                }

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
                    status: statusText,
                    unit: stock.item && stock.item.unit ? stock.item.unit.title : 'Pcs'
                };
            });
        }
    },

    methods: {
        switchTab(tab) {
            this.activeTab = tab;
            this.search_data.stock_status = tab;
            if (tab === 'low_stock') {
                this.search_data.sort_by = 'stock_asc';
                this.search_data.limit = '50';
            } else if (tab === 'all') {
                this.search_data.limit = 'all';
            }
            this.getAvailableStock();
        },

        onStockStatusFilterChange() {
            this.activeTab = this.search_data.stock_status;
        },

        loadAllStock() {
            this.activeTab = 'all';
            this.search_data.stock_status = 'all';
            this.search_data.limit = 'all';
            this.getAvailableStock();
        },

        search() {
            this.activeTab = this.search_data.stock_status;
            this.getAvailableStock();
        },

        resetSearchData() {
            this.activeTab = "low_stock";
            this.search_data = {
                category_id: null,
                item_id: null,
                color_id: null,
                size_id: null,
                stock_status: "low_stock",
                limit: "50",
                sort_by: "stock_asc",
                low_threshold: 5,
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
                this.fetchDropdownItems();
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

        fetchDropdownItems() {
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
                    if (res.data && res.data.datas) {
                        this.datas = res.data.datas || [];
                        if (res.data.counts) {
                            this.tabCounts = res.data.counts;
                        }
                        if (res.data.summary) {
                            this.backendSummary = res.data.summary;
                        }
                    } else if (Array.isArray(res.data)) {
                        this.datas = res.data;
                    }
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
        this.fetchDropdownItems();
        this.getColors();
        this.getSizes();
        this.getAvailableStock();
    },

    validators: {},
};
</script>

<style scoped>
.custom-theme-tabs .btn-theme-tab {
    background-color: #f8f9fa;
    color: #495057;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.custom-theme-tabs .btn-theme-tab:hover {
    background-color: #edf2f7;
    border-color: #cbd5e1;
    color: #1e293b;
}

.custom-theme-tabs .btn-theme-tab.active.tab-danger {
    background-color: #dc3545 !important;
    color: #ffffff !important;
    border-color: #dc3545 !important;
}

.custom-theme-tabs .btn-theme-tab.active.tab-danger i {
    color: #ffffff !important;
}

.custom-theme-tabs .btn-theme-tab.active.tab-warning {
    background-color: #334155 !important;
    color: #ffffff !important;
    border-color: #334155 !important;
}

.custom-theme-tabs .btn-theme-tab.active.tab-warning i {
    color: #f59e0b !important;
}

.custom-theme-tabs .btn-theme-tab.active.tab-primary {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    border-color: #0d6efd !important;
}

.custom-theme-tabs .btn-theme-tab.active.tab-primary i {
    color: #ffffff !important;
}

.border-start-3 {
    border-left-width: 4px !important;
}

.cursor-pointer {
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.cursor-pointer:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.metric-icon {
    font-size: 24px;
}

.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.2rem;
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
