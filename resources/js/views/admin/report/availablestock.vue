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
                placeholder="Type barcode or name..." col="3 mb-3" :req="false" />

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
            <!-- 🌟 Clean Quick Status Tabs Bar & Action Buttons -->
            <div class="col-12 mb-3 d-print-none">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between p-2 bg-white rounded border shadow-sm">
                    <ul class="nav nav-pills custom-theme-tabs gap-2 align-items-center mb-0">
                        <li class="nav-item">
                            <button class="nav-link btn-theme-tab" :class="{ 'active tab-danger': activeTab === 'low_stock' }"
                                @click="switchTab('low_stock')">
                                <i class="fas fa-exclamation-triangle me-1 text-danger"></i> Low Stock Alert (কম স্টক)
                                <span class="badge bg-danger ms-2 rounded-pill">{{ formatNumber(tabCounts.low_stock) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn-theme-tab" :class="{ 'active tab-warning': activeTab === 'out_of_stock' }"
                                @click="switchTab('out_of_stock')">
                                <i class="fas fa-times-circle me-1 text-warning"></i> Out of Stock (শূন্য মজুদ)
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
                            <i class="fas fa-file-excel"></i> Export Excel ({{ datas.length }})
                        </download-excel>
                        <button class="p_btn btn btn-sm btn-dark d-inline-flex align-items-center gap-1"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Print Report"
                            v-x-tooltip @click="print('printArea', model)">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🌟 EXECUTIVE STOCK VALUATION & KPI SUMMARY CARDS -->
            <div class="col-12 mb-3 d-print-none">
                <div class="row g-3">
                    <!-- 1. Total Stock Purchase Value (দোকানের মোট মালের ক্রয়মূল্য) -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-navy text-white h-100 position-relative overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase tracking-wide">
                                        Total Stock Value (ক্রয়মূল্য)
                                    </div>
                                    <div class="fs-4 fw-bold mt-1 text-white font-monospace">
                                        {{ formatCurrency(summaryData.global_total_purchase_value) }}
                                    </div>
                                </div>
                                <div class="card-icon-bg">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top border-white-10 d-flex justify-content-between align-items-center small text-white-50">
                                <span>দোকানের মোট মালের ইনভেস্টমেন্ট</span>
                                <span class="badge bg-white text-dark font-monospace">{{ formatNumber(summaryData.global_total_stock_qty) }} Pcs</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Total Retail Selling Value (মোট বিক্রয়মূল্য) -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-blue text-white h-100 position-relative overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase tracking-wide">
                                        Total Retail Value (বিক্রয়মূল্য)
                                    </div>
                                    <div class="fs-4 fw-bold mt-1 text-white font-monospace">
                                        {{ formatCurrency(summaryData.global_total_selling_value) }}
                                    </div>
                                </div>
                                <div class="card-icon-bg">
                                    <i class="fas fa-tags"></i>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top border-white-10 d-flex justify-content-between align-items-center small text-white-50">
                                <span>সর্বমোট সম্ভাব্য বিক্রয়মূল্য (MRP)</span>
                                <span class="badge bg-info text-white">Expected Revenue</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Potential Gross Profit (সম্ভাব্য মুনাফা) -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-emerald text-white h-100 position-relative overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase tracking-wide">
                                        Potential Profit (সম্ভাব্য লাভ)
                                    </div>
                                    <div class="fs-4 fw-bold mt-1 text-white font-monospace">
                                        {{ formatCurrency(summaryData.global_potential_profit) }}
                                    </div>
                                </div>
                                <div class="card-icon-bg">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top border-white-10 d-flex justify-content-between align-items-center small text-white-50">
                                <span>প্রত্যাশিত মোট গ্রস প্রফিট</span>
                                <span class="badge bg-white text-success fw-bold">{{ summaryData.global_potential_margin }}% Margin</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Total In-Stock Variants & Units -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-gradient-purple text-white h-100 position-relative overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase tracking-wide">
                                        In-Hand Stock (মজুদ আইটেম)
                                    </div>
                                    <div class="fs-4 fw-bold mt-1 text-white font-monospace">
                                        {{ formatNumber(summaryData.total_current_stock) }} Pcs
                                    </div>
                                </div>
                                <div class="card-icon-bg">
                                    <i class="fas fa-cubes"></i>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top border-white-10 d-flex justify-content-between align-items-center small text-white-50">
                                <span>In: {{ formatNumber(summaryData.total_in) }} | Out: {{ formatNumber(summaryData.total_out) }}</span>
                                <span class="badge bg-white text-dark">{{ formatNumber(tabCounts.all) }} Variants</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💡 Filtered Summary Sub-banner when filter/search is active -->
            <div v-if="isFiltered" class="alert alert-primary bg-light border-primary py-2 px-3 small d-flex justify-content-between align-items-center mb-3 d-print-none rounded-3">
                <div>
                    <i class="fas fa-filter text-primary me-1"></i>
                    <strong>Filtered Results:</strong> Total <strong>{{ datas.length }}</strong> items matching filter |
                    Qty: <strong>{{ formatNumber(displayedTotals.total_current_stock) }} Pcs</strong> |
                    Stock Cost: <strong class="text-dark">{{ formatCurrency(displayedTotals.total_purchase_value) }}</strong> |
                    Retail Value: <strong class="text-primary">{{ formatCurrency(displayedTotals.total_selling_value) }}</strong> |
                    Potential Profit: <strong class="text-success">{{ formatCurrency(displayedTotals.total_potential_profit) }}</strong>
                </div>
                <button class="btn btn-xs btn-outline-secondary" @click="resetSearchData">
                    <i class="fas fa-times me-1"></i> Clear Filters
                </button>
            </div>

            <!-- 📄 TOP PAGINATION BAR -->
            <div class="col-12 mb-2 d-print-none" v-if="datas.length > 0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 bg-light border rounded">
                    <!-- Pagination info -->
                    <div class="text-muted small">
                        Showing <strong class="text-dark">{{ paginationStart }}</strong> to <strong class="text-dark">{{ paginationEnd }}</strong> of <strong class="text-dark">{{ datas.length }}</strong> items
                        <span v-if="totalPages > 1">(Page <strong>{{ currentPage }}</strong> of <strong>{{ totalPages }}</strong>)</span>
                    </div>

                    <!-- Page Size Selector & Controls -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center gap-1 small text-muted">
                            <span>Per Page:</span>
                            <select class="form-select form-select-sm py-0 px-2" style="width: auto; height: 30px;" v-model="perPage" @change="onPerPageChange">
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                                <option :value="250">250</option>
                                <option :value="500">500</option>
                                <option value="all">All ({{ datas.length }})</option>
                            </select>
                        </div>

                        <!-- Page Nav Buttons -->
                        <nav v-if="totalPages > 1" aria-label="Table pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="changePage(1)" title="First Page">
                                        <i class="fas fa-angle-double-left"></i>
                                    </button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="changePage(currentPage - 1)" title="Previous Page">
                                        <i class="fas fa-angle-left"></i> Prev
                                    </button>
                                </li>

                                <li v-for="page in visiblePageNumbers" :key="page" class="page-item" :class="{ active: currentPage === page, disabled: page === '...' }">
                                    <button class="page-link" @click="page !== '...' && changePage(page)">{{ page }}</button>
                                </li>

                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="changePage(currentPage + 1)" title="Next Page">
                                        Next <i class="fas fa-angle-right"></i>
                                    </button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="changePage(totalPages)" title="Last Page">
                                        <i class="fas fa-angle-double-right"></i>
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
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
                            Available Stock & Valuation Report (দোকানের বর্তমান মজুদ ও ক্রয়মূল্য ভিত্তিক রিপোর্ট)
                        </h5>
                        <small class="text-muted">Report Generated: <strong>{{ reportDate }}</strong> | Filter: <strong class="text-primary">{{ activeFilterTitle }}</strong></small>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-dark text-white p-2 font-monospace">
                            দোকানে মোট মালের ক্রয়মূল্য: <strong>{{ formatCurrency(summaryData.global_total_purchase_value) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- 📊 Stock Data Table with Integrated Unit, Color, Size in Item Name -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-dark text-center">
                            <tr class="fw-bold text-uppercase small">
                                <th style="width: 3%;">#</th>
                                <th style="width: 12%;">Category</th>
                                <th style="width: 10%;">Barcode</th>
                                <th style="width: 25%;">Item Details (নাম, সাইজ ও কালার)</th>
                                <th style="width: 8%;">Current Stock</th>
                                <th style="width: 9%;">Unit Cost (ক্রয়মূল্য)</th>
                                <th style="width: 11%;">Total Cost (মোট ক্রয়মূল্য)</th>
                                <th style="width: 9%;">Unit MRP (বিক্রয়মূল্য)</th>
                                <th style="width: 11%;">Total MRP (মোট বিক্রয়মূল্য)</th>
                                <th style="width: 10%;">Potential Profit (সম্ভাব্য লাভ)</th>
                                <th style="width: 6%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="paginatedDatas.length > 0">
                                <tr v-for="(stock, index) in paginatedDatas" :key="index">
                                    <td class="text-center text-muted small">{{ (currentPage - 1) * (perPage === 'all' ? datas.length : Number(perPage)) + index + 1 }}</td>
                                    <td class="text-center small">{{ stock.category_title || (stock.item && stock.item.category ? stock.item.category.title : 'N/A') }}</td>
                                    <td class="text-center font-monospace small">{{ stock.barcode || (stock.item ? stock.item.barcode : 'N/A') }}</td>

                                    <!-- Item Name + Unit + Color + Size Merged Under Item Name -->
                                    <td>
                                        <div class="fw-bold text-dark">{{ stock.item_title || (stock.item ? stock.item.title : 'N/A') }}</div>
                                        <div class="d-flex flex-wrap gap-1 mt-1 align-items-center">
                                            <span class="badge bg-light text-secondary border px-2 py-1 small">
                                                <i class="fas fa-box-open me-1 text-muted"></i>{{ stock.unit_title || (stock.item && stock.item.unit ? stock.item.unit.title : 'Pcs') }}
                                            </span>
                                            <span v-if="stock.color_title || (stock.color && stock.color.title)" class="badge bg-light text-dark border px-2 py-1 small">
                                                <i class="fas fa-palette me-1 text-primary"></i>{{ stock.color_title || (stock.color ? stock.color.title : '') }}
                                            </span>
                                            <span v-if="stock.size_title || (stock.size && stock.size.title)" class="badge bg-light text-dark border font-monospace px-2 py-1 small">
                                                <i class="fas fa-ruler me-1 text-info"></i>{{ stock.size_title || (stock.size ? stock.size.title : '') }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Current Stock Quantity -->
                                    <td class="text-center font-monospace fw-bold"
                                        :class="Number(stock.current_stock) > 0 ? (Number(stock.current_stock) <= Number(search_data.low_threshold || 5) ? 'text-danger' : 'text-success') : 'text-danger'">
                                        {{ Number(stock.current_stock) > 0 ? formatNumber(stock.current_stock) : '0' }}
                                    </td>

                                    <!-- Purchase Price (Unit Cost) -->
                                    <td class="text-end font-monospace text-secondary">
                                        {{ formatCurrency(stock.purchase_price) }}
                                    </td>

                                    <!-- Total Purchase Value (Stock Cost - Never Negative) -->
                                    <td class="text-end font-monospace fw-bold text-dark bg-light-primary">
                                        {{ formatCurrency(stock.total_purchase_value) }}
                                    </td>

                                    <!-- Selling Price (Unit MRP) -->
                                    <td class="text-end font-monospace text-secondary">
                                        {{ formatCurrency(stock.selling_price) }}
                                    </td>

                                    <!-- Total Selling Value (Retail Value - Never Negative) -->
                                    <td class="text-end font-monospace fw-bold text-primary">
                                        {{ formatCurrency(stock.total_selling_value) }}
                                    </td>

                                    <!-- Potential Profit & Margin (Never Negative) -->
                                    <td class="text-end font-monospace">
                                        <div class="fw-bold" :class="Number(stock.potential_profit) > 0 ? 'text-success' : 'text-muted'">
                                            {{ formatCurrency(stock.potential_profit) }}
                                        </div>
                                        <small class="text-muted" v-if="Number(stock.total_selling_value) > 0">
                                            {{ stock.margin_percent }}%
                                        </small>
                                    </td>

                                    <!-- Status Badge -->
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
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="fas fa-boxes fa-3x mb-3 text-secondary opacity-25 d-block"></i>
                                        <h6 class="fw-bold text-secondary">No Stock Records Found</h6>
                                        <p class="small text-muted mb-0">Try changing your search filters or switching tabs.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>

                        <!-- 📊 Table Footer Totals -->
                        <tfoot class="table-dark font-monospace fw-bold" v-if="datas.length > 0">
                            <tr>
                                <td colspan="4" class="text-end text-uppercase">Total (সর্বমোট মজুদ ও ভ্যালু):</td>
                                <td class="text-center text-warning">{{ formatNumber(displayedTotals.total_current_stock) }}</td>
                                <td class="text-center">-</td>
                                <td class="text-end text-warning">{{ formatCurrency(displayedTotals.total_purchase_value) }}</td>
                                <td class="text-center">-</td>
                                <td class="text-end text-info">{{ formatCurrency(displayedTotals.total_selling_value) }}</td>
                                <td class="text-end text-success">{{ formatCurrency(displayedTotals.total_potential_profit) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 📄 BOTTOM PAGINATION BAR -->
                <div class="col-12 mt-3 d-print-none" v-if="totalPages > 1">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 bg-light border rounded">
                        <div class="text-muted small">
                            Showing <strong class="text-dark">{{ paginationStart }}</strong> to <strong class="text-dark">{{ paginationEnd }}</strong> of <strong class="text-dark">{{ datas.length }}</strong> items
                        </div>
                        <nav aria-label="Table pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="changePage(1)" title="First Page">
                                        <i class="fas fa-angle-double-left"></i>
                                    </button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="changePage(currentPage - 1)" title="Previous Page">
                                        <i class="fas fa-angle-left"></i> Prev
                                    </button>
                                </li>

                                <li v-for="page in visiblePageNumbers" :key="page" class="page-item" :class="{ active: currentPage === page, disabled: page === '...' }">
                                    <button class="page-link" @click="page !== '...' && changePage(page)">{{ page }}</button>
                                </li>

                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="changePage(currentPage + 1)" title="Next Page">
                                        Next <i class="fas fa-angle-right"></i>
                                    </button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                    <button class="page-link" @click="changePage(totalPages)" title="Last Page">
                                        <i class="fas fa-angle-double-right"></i>
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- 📌 Footer Note (Visible in Print) -->
                <div class="mt-4 pt-3 border-top small text-muted d-none d-print-block">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <p class="mb-0">Report Generated By: <strong>{{ $root.admin ? $root.admin.name : 'System Admin' }}</strong></p>
                            <p class="mb-0">This is a system generated available stock valuation report and does not require manual signature.</p>
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
            page_title: "Available Stock & Valuation Report",
            reportDate: moment().format('D MMMM, YYYY h:mm A'),
            activeTab: "low_stock", // Default to 'low_stock' on page load
            currentPage: 1, // Current pagination page
            perPage: 50, // Rows per page
            search_data: {
                category_id: null,
                item_id: null,
                color_id: null,
                size_id: null,
                stock_status: "low_stock",
                sort_by: "stock_asc",
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
                global_total_stock_qty: 0,
                global_total_purchase_value: 0,
                global_total_selling_value: 0,
                global_potential_profit: 0,
                global_potential_margin: 0,
            },
            json_fields: {
                "SL": "sl",
                "Category": "category",
                "Barcode": "barcode",
                "Item Name": "item_title",
                "Color": "color",
                "Size": "size",
                "Unit": "unit",
                "Current Stock": "current_stock",
                "Unit Cost (ক্রয়মূল্য)": "purchase_price",
                "Total Cost (মোট ক্রয়মূল্য)": "total_purchase_value",
                "Unit MRP (বিক্রয়মূল্য)": "selling_price",
                "Total MRP (মোট বিক্রয়মূল্য)": "total_selling_value",
                "Potential Profit (সম্ভাব্য লাভ)": "potential_profit",
                "Profit Margin (%)": "margin_percent",
                "Status": "status"
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
        activeFilterTitle() {
            const titles = {
                'low_stock': 'Low Stock Alert (কম স্টক)',
                'out_of_stock': 'Out of Stock (শূন্য মজুদ)',
                'all': 'All Stock (সকল পণ্য)',
            };
            return titles[this.activeTab] || (this.activeTab ? this.activeTab.replace(/_/g, ' ') : 'All Stock');
        },

        isFiltered() {
            return !!(
                this.search_data.category_id ||
                this.search_data.item_id ||
                this.search_data.color_id ||
                this.search_data.size_id ||
                this.search_data.keyword ||
                this.search_data.from_qty ||
                this.search_data.to_qty ||
                this.search_data.is_zero
            );
        },

        totalPages() {
            if (this.perPage === 'all' || !this.datas.length) return 1;
            return Math.ceil(this.datas.length / Number(this.perPage));
        },

        paginatedDatas() {
            if (this.perPage === 'all') return this.datas;
            const start = (this.currentPage - 1) * Number(this.perPage);
            const end = start + Number(this.perPage);
            return this.datas.slice(start, end);
        },

        paginationStart() {
            if (!this.datas.length) return 0;
            if (this.perPage === 'all') return 1;
            return (this.currentPage - 1) * Number(this.perPage) + 1;
        },

        paginationEnd() {
            if (!this.datas.length) return 0;
            if (this.perPage === 'all') return this.datas.length;
            return Math.min(this.currentPage * Number(this.perPage), this.datas.length);
        },

        visiblePageNumbers() {
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 7) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }
            if (current <= 4) {
                return [1, 2, 3, 4, 5, '...', total];
            }
            if (current >= total - 3) {
                return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            }
            return [1, '...', current - 1, current, current + 1, '...', total];
        },

        displayedTotals() {
            let total_current_stock = 0;
            let total_purchase_value = 0;
            let total_selling_value = 0;
            let total_potential_profit = 0;

            this.datas.forEach((item) => {
                const stock = Number(item.current_stock || 0);
                if (stock > 0) {
                    total_current_stock += stock;
                    total_purchase_value += Number(item.total_purchase_value || 0);
                    total_selling_value += Number(item.total_selling_value || 0);
                    total_potential_profit += Number(item.potential_profit || 0);
                }
            });

            return {
                total_current_stock,
                total_purchase_value,
                total_selling_value,
                total_potential_profit
            };
        },

        summaryData() {
            return {
                total_in: this.backendSummary.total_in || 0,
                total_out: this.backendSummary.total_out || 0,
                total_current_stock: this.backendSummary.total_current_stock || this.displayedTotals.total_current_stock,
                global_total_stock_qty: this.backendSummary.global_total_stock_qty || this.displayedTotals.total_current_stock,
                global_total_purchase_value: this.backendSummary.global_total_purchase_value || this.displayedTotals.total_purchase_value,
                global_total_selling_value: this.backendSummary.global_total_selling_value || this.displayedTotals.total_selling_value,
                global_potential_profit: this.backendSummary.global_potential_profit || this.displayedTotals.total_potential_profit,
                global_potential_margin: this.backendSummary.global_potential_margin || 0,
            };
        },

        exportFileName() {
            return `Stock_Valuation_Report_${this.activeTab}_${moment().format('YYYY_MM_DD_HHmm')}.xls`;
        },

        exportData() {
            return this.datas.map((stock, index) => {
                let statusText = 'In Stock';
                const currentStock = Math.max(0, Number(stock.current_stock || 0));
                if (currentStock <= 0) {
                    statusText = 'Out of Stock';
                } else if (currentStock <= Number(this.search_data.low_threshold || 5)) {
                    statusText = 'Low Stock';
                }

                return {
                    sl: index + 1,
                    category: stock.category_title || (stock.item && stock.item.category ? stock.item.category.title : 'N/A'),
                    barcode: stock.barcode || (stock.item ? stock.item.barcode : 'N/A'),
                    item_title: stock.item_title || (stock.item ? stock.item.title : 'N/A'),
                    color: stock.color_title || (stock.color ? stock.color.title : '-'),
                    size: stock.size_title || (stock.size ? stock.size.title : '-'),
                    unit: stock.unit_title || (stock.item && stock.item.unit ? stock.item.unit.title : 'Pcs'),
                    current_stock: currentStock,
                    purchase_price: stock.purchase_price || 0,
                    total_purchase_value: stock.total_purchase_value || 0,
                    selling_price: stock.selling_price || 0,
                    total_selling_value: stock.total_selling_value || 0,
                    potential_profit: stock.potential_profit || 0,
                    margin_percent: stock.margin_percent || 0,
                    status: statusText,
                };
            });
        }
    },

    methods: {
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                // Smooth scroll to table top if needed
                const el = document.getElementById('printArea');
                if (el && window.scrollY > el.offsetTop) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }
        },

        onPerPageChange() {
            this.currentPage = 1;
        },

        switchTab(tab) {
            this.activeTab = tab;
            this.search_data.stock_status = tab;
            this.currentPage = 1;
            if (tab === 'low_stock') {
                this.search_data.sort_by = 'stock_asc';
            } else if (tab === 'all') {
                this.search_data.sort_by = 'stock_desc';
            }
            this.getAvailableStock();
        },

        onStockStatusFilterChange() {
            this.activeTab = this.search_data.stock_status;
            this.currentPage = 1;
        },

        search() {
            this.activeTab = this.search_data.stock_status;
            this.currentPage = 1;
            this.getAvailableStock();
        },

        resetSearchData() {
            this.activeTab = "low_stock";
            this.currentPage = 1;
            this.search_data = {
                category_id: null,
                item_id: null,
                color_id: null,
                size_id: null,
                stock_status: "low_stock",
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
        },

        formatCurrency(val) {
            const num = Number(val || 0);
            // Non-negative formatting
            const positiveVal = Math.max(0, num);
            return 'Tk ' + positiveVal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
.bg-gradient-navy {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
}
.bg-gradient-blue {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}
.bg-gradient-emerald {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}
.bg-gradient-purple {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
}

.card-icon-bg {
    font-size: 28px;
    opacity: 0.3;
}

.border-white-10 {
    border-color: rgba(255, 255, 255, 0.15) !important;
}

.tracking-wide {
    letter-spacing: 0.05em;
}

.bg-light-primary {
    background-color: #f8fafc;
}

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

.custom-theme-tabs .btn-theme-tab.active.tab-primary {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    border-color: #0d6efd !important;
}
.custom-theme-tabs .btn-theme-tab.active.tab-primary i {
    color: #ffffff !important;
}

.custom-theme-tabs .btn-theme-tab.active.tab-success {
    background-color: #10b981 !important;
    color: #ffffff !important;
    border-color: #10b981 !important;
}
.custom-theme-tabs .btn-theme-tab.active.tab-success i {
    color: #ffffff !important;
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

.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.2rem;
}

.pagination .page-item .page-link {
    color: #334155;
    border-color: #cbd5e1;
    padding: 4px 10px;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #ffffff;
}

.pagination .page-item.disabled .page-link {
    color: #94a3b8;
    background-color: #f8fafc;
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
