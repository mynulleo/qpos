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
                    :placeholder="search_data.category_id ? '-- Select Item --' : '-- All Items / Select --'" :closeOnSelect="true">
                    <template #option="option">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block">{{ option.title }}</strong>
                                <small class="text-muted" v-if="option.barcode">Barcode: {{ option.barcode }}</small>
                            </div>
                            <span v-if="option.brand" class="badge bg-light text-secondary border ms-2 small">{{ option.brand }}</span>
                        </div>
                    </template>
                </v-select>
            </v-select-container>

            <!-- Color Filter -->
            <v-select-container title="Color (কালার)" field="search_data.color_id" col="2 mb-3">
                <v-select v-model="search_data.color_id" label="title" :reduce="obj => obj.id" :options="colorOptions"
                    placeholder="-- All Colors --" :closeOnSelect="true" />
            </v-select-container>

            <!-- Size Filter (Hidden for Electronics Shops) -->
            <v-select-container v-if="showSizeColumn" title="Size (সাইজ)" field="search_data.size_id" col="2 mb-3">
                <v-select v-model="search_data.size_id" label="title" :reduce="obj => obj.id" :options="sizeOptions"
                    placeholder="-- All Sizes --" :closeOnSelect="true" />
            </v-select-container>

            <!-- Transaction Type Filter -->
            <div class="col-md-2 mb-3">
                <div class="form-group">
                    <label class="form-label fw-bold">Transaction Type</label>
                    <select class="form-select form-select-sm" v-model="search_data.transaction_type">
                        <option :value="null">-- All Types --</option>
                        <option value="Opening">Opening Balance</option>
                        <option value="Purchase">Purchase (ক্রয়)</option>
                        <option value="GRN">GRN (পণ্য গ্রহণ)</option>
                        <option value="Sale">Sale / Issue (বিক্রয়)</option>
                        <option value="SalesReturn">Sales Return (ফেরত)</option>
                        <option value="Wastage">Wastage (অপচয়)</option>
                    </select>
                </div>
            </div>

            <!-- Date Pickers -->
            <date-picker id="date1" field="search_data.start_date" name="start_date" v-model="search_data.start_date"
                title="Start Date (From)" placeholder="dd/mm/yyyy" col="3 mb-3"></date-picker>
            <date-picker id="date3" field="search_data.end_date" name="end_date" v-model="search_data.end_date"
                title="End Date (To)" placeholder="dd/mm/yyyy" col="3 mb-3"
                :disableToDates="search_data.start_date"></date-picker>
        </template>

        <!-- 📊 Main Content & Ledger Table Area -->
        <template v-slot:table-list>
            <!-- 🌟 Clean Action & Header Bar (Only Item Title & Print Button) -->
            <div class="col-12 mb-3 d-print-none">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between p-2 bg-white rounded border shadow-sm">
                    <!-- Fixed Title Area (Handles long titles gracefully across 2 lines) -->
                    <div class="d-flex flex-wrap align-items-center gap-2 min-w-0 flex-grow-1">
                        <div class="item-title-box">
                            <h5 class="fw-bold text-dark mb-0 item-title-heading" v-if="itemProfile">
                                <i class="fas fa-box-open text-primary me-2"></i>{{ itemProfile.title }}
                            </h5>
                            <h5 class="fw-bold text-dark mb-0" v-else>
                                <i class="fas fa-book-open text-primary me-2"></i>Item Ledger Statement
                            </h5>
                        </div>

                        <!-- Active Filter Chips -->
                        <div class="d-flex flex-wrap gap-1 align-items-center ms-2" v-if="search_data.color_id || search_data.size_id || search_data.transaction_type">
                            <span v-if="search_data.color_id && activeColorTitle" class="badge bg-light text-dark border px-2 py-1 small">
                                Color: <strong>{{ activeColorTitle }}</strong>
                                <i class="fas fa-times ms-1 text-danger cursor-pointer" @click="search_data.color_id = null; search()"></i>
                            </span>
                            <span v-if="search_data.size_id && activeSizeTitle && showSizeColumn" class="badge bg-light text-dark border px-2 py-1 small">
                                Size: <strong>{{ activeSizeTitle }}</strong>
                                <i class="fas fa-times ms-1 text-danger cursor-pointer" @click="search_data.size_id = null; search()"></i>
                            </span>
                            <span v-if="search_data.transaction_type" class="badge bg-light text-dark border px-2 py-1 small">
                                Type: <strong>{{ search_data.transaction_type }}</strong>
                                <i class="fas fa-times ms-1 text-danger cursor-pointer" @click="search_data.transaction_type = null; search()"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Right Action Button (Only Print) -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button class="btn btn-sm btn-primary shadow-xs d-inline-flex align-items-center gap-1 px-3"
                            @click="print('printArea', model)">
                            <i class="fas fa-print"></i> Print Statement
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🏢 Executive Item Profile & Metric Banner (When item selected) -->
            <div class="row g-3 mb-3 d-print-none" v-if="itemProfile">
                <!-- Item Meta Card (Without duplicate title) -->
                <div class="col-lg-4 col-md-12">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <div class="item-img-box rounded border p-1 bg-light text-center flex-shrink-0" style="width: 70px; height: 70px;">
                                    <img v-if="itemProfile.image" :src="itemProfile.image" class="img-fluid rounded h-100 w-100 object-fit-contain" alt="Item Image" />
                                    <div v-else class="h-100 d-flex align-items-center justify-content-center text-muted">
                                        <i class="fas fa-boxes fa-2x text-secondary opacity-50"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="badge bg-secondary-soft text-secondary border small" v-if="itemProfile.barcode">
                                            <i class="fas fa-barcode me-1"></i>{{ itemProfile.barcode }}
                                        </span>
                                        <span class="badge bg-primary-soft text-primary border small" v-if="itemProfile.category">
                                            {{ itemProfile.category.title }}
                                        </span>
                                        <span class="badge bg-info-soft text-info border small" v-if="itemProfile.brand">
                                            {{ itemProfile.brand.title }}
                                        </span>
                                    </div>
                                    <div class="small text-muted d-flex flex-wrap gap-3">
                                        <span>Unit: <strong class="text-dark">{{ itemProfile.unit ? itemProfile.unit.title : 'Pcs' }}</strong></span>
                                        <span>Reorder: <strong class="text-dark">{{ formatQty(itemProfile.reorder_level) }}</strong></span>
                                        <span v-if="itemProfile.item_type">Type: <strong class="text-dark text-capitalize">{{ itemProfile.item_type }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4 KPI Summary Cards -->
                <div class="col-lg-8 col-md-12">
                    <div class="row g-2 h-100">
                        <!-- Live Current Stock KPI -->
                        <div class="col-sm-3 col-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white border-top-primary">
                                <small class="text-muted fw-semibold text-uppercase tracking-wider">Current Live Stock</small>
                                <div class="fs-4 fw-bold text-primary my-1">
                                    {{ metrics ? formatQty(metrics.current_stock) : 0 }} <small class="fs-6 text-muted">{{ itemProfile.unit ? itemProfile.unit.title : 'Pcs' }}</small>
                                </div>
                                <span class="badge rounded-pill mx-auto px-2" :class="getStockBadgeClass(metrics ? metrics.current_stock : 0)">
                                    {{ getStockStatusLabel(metrics ? metrics.current_stock : 0) }}
                                </span>
                            </div>
                        </div>

                        <!-- Lifetime Stock In KPI -->
                        <div class="col-sm-3 col-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white border-top-success">
                                <small class="text-muted fw-semibold text-uppercase tracking-wider">Lifetime Inflow</small>
                                <div class="fs-4 fw-bold text-success my-1">
                                    +{{ metrics ? formatQty(metrics.total_qty_in) : 0 }}
                                </div>
                                <small class="text-muted">Total Received</small>
                            </div>
                        </div>

                        <!-- Lifetime Sold & Out KPI -->
                        <div class="col-sm-3 col-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white border-top-danger">
                                <small class="text-muted fw-semibold text-uppercase tracking-wider">Total Sales / Out</small>
                                <div class="fs-4 fw-bold text-danger my-1">
                                    -{{ metrics ? formatQty(metrics.total_sold) : 0 }}
                                </div>
                                <small class="text-muted">Wastage: {{ metrics ? formatQty(metrics.total_wastage) : 0 }}</small>
                            </div>
                        </div>

                        <!-- Stock Valuation KPI -->
                        <div class="col-sm-3 col-6">
                            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100 bg-white border-top-info">
                                <small class="text-muted fw-semibold text-uppercase tracking-wider">Stock Valuation</small>
                                <div class="fs-5 fw-bold text-dark my-1">
                                    ৳ {{ metrics ? formatNumber(metrics.stock_purchase_value) : '0.00' }}
                                </div>
                                <small class="text-success fw-semibold" title="Potential Gross Profit">
                                    Profit: ৳ {{ metrics ? formatNumber(metrics.potential_profit) : '0.00' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📅 Selected Period KPI Cards -->
            <div class="row g-2 mb-3 d-print-none" v-if="datas.length > 0">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm p-2 text-center bg-white rounded-3">
                        <small class="text-muted d-block fw-semibold">Period Opening Stock</small>
                        <span class="fs-5 fw-bold text-secondary">{{ summaryData.opening }}</span>
                        <small class="text-muted" style="font-size: 10px;">Before {{ search_data.start_date }}</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm p-2 text-center bg-white rounded-3">
                        <small class="text-muted d-block fw-semibold text-success">Period Stock In (+)</small>
                        <span class="fs-5 fw-bold text-success">+{{ summaryData.totalIn }}</span>
                        <small class="text-muted" style="font-size: 10px;">Purchases & Returns</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm p-2 text-center bg-white rounded-3">
                        <small class="text-muted d-block fw-semibold text-danger">Period Stock Out (-)</small>
                        <span class="fs-5 fw-bold text-danger">-{{ summaryData.totalOut }}</span>
                        <small class="text-muted" style="font-size: 10px;">Sales & Wastages</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm p-2 text-center bg-white rounded-3">
                        <small class="text-muted d-block fw-semibold text-primary">Period Closing Balance</small>
                        <span class="fs-5 fw-bold text-primary">{{ summaryData.closing }}</span>
                        <small class="text-muted" style="font-size: 10px;">Balance at {{ search_data.end_date }}</small>
                    </div>
                </div>
            </div>

            <!-- 📄 Printable Ledger Report Section -->
            <div class="my-2 bg-white rounded-3 shadow-sm p-3 border" id="printArea">
                <!-- 🧾 Company Header (Visible during Print) -->
                <div class="text-center mb-3 report-title d-none d-print-block">
                    <h2 class="fw-bold mb-0 text-dark">{{ $root.site ? $root.site.title : 'QPOS' }}</h2>
                    <p class="mb-0 text-muted small" v-if="$root.site && $root.site.address">{{ $root.site.address }}</p>
                    <p class="small text-muted mb-1" v-if="$root.site && ($root.site.contact_email || $root.site.mobile1)">
                        Email: {{ $root.site.contact_email }} | Phone: {{ $root.site.mobile1 }}
                    </p>
                    <div class="border-bottom my-2"></div>
                </div>

                <!-- 📋 Report Header -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <span>Item Ledger Statement</span>
                            <span v-if="itemProfile" class="badge bg-primary fs-6 fw-normal">{{ itemProfile.title }}</span>
                        </h5>
                        <div class="small text-muted d-flex flex-wrap gap-3">
                            <span><i class="fas fa-calendar-alt text-secondary me-1"></i> Period: <strong>{{ search_data.start_date }}</strong> to <strong>{{ search_data.end_date }}</strong></span>
                            <span v-if="itemProfile && itemProfile.barcode"><i class="fas fa-barcode text-secondary me-1"></i> Barcode: <strong>{{ itemProfile.barcode }}</strong></span>
                            <span v-if="itemProfile && itemProfile.category"><i class="fas fa-folder text-secondary me-1"></i> Category: <strong>{{ itemProfile.category.title }}</strong></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Report Date: <strong>{{ reportDate }}</strong></small>
                        <small class="text-muted d-block" v-if="itemProfile">Current Live Stock: <strong class="text-primary">{{ metrics ? metrics.current_stock : 0 }} {{ itemProfile.unit ? itemProfile.unit.title : 'Pcs' }}</strong></small>
                    </div>
                </div>

                <!-- 📊 Ledger Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr class="fw-bold text-center small text-secondary">
                                <th style="width: 40px;">#</th>
                                <th style="width: 120px;">Date & Time</th>
                                <th style="width: 110px;">Transaction Type</th>
                                <th>Reference No</th>
                                <th>Party / Contact</th>
                                <th>Warehouse</th>
                                <th>Color</th>
                                <!-- Size Column (Only for Non-Electronics Shops) -->
                                <th v-if="showSizeColumn">Size</th>
                                <!-- Serial No Column (Based on Shop Type / Serial availability) -->
                                <th v-if="showSerialColumn" style="width: 110px;">Serial No</th>
                                <th class="text-end" style="width: 100px;">Unit Rate</th>
                                <th class="text-end text-success" style="width: 100px;">Stock In (+)</th>
                                <th class="text-end text-danger" style="width: 100px;">Stock Out (-)</th>
                                <th class="text-end text-primary" style="width: 110px;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="datas.length > 0">
                                <tr v-for="(ledger, index) in datas" :key="index"
                                    :class="ledger.transaction_type === 'Opening Balance' ? 'table-secondary fw-semibold' : ''">
                                    <td class="text-center text-muted small">{{ index + 1 }}</td>
                                    
                                    <!-- Date & Time -->
                                    <td class="text-center">
                                        <div class="fw-semibold">{{ ledger.transaction_date_formatted || ledger.transaction_date }}</div>
                                        <small class="text-muted" style="font-size: 11px;" v-if="ledger.created_time">{{ ledger.created_time }}</small>
                                    </td>

                                    <!-- Transaction Type Badge -->
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-2 py-1 shadow-xs d-inline-flex align-items-center gap-1"
                                            :class="getTypeBadgeClass(ledger.transaction_type)">
                                            <i :class="getTypeIcon(ledger.transaction_type)" style="font-size: 10px;"></i>
                                            {{ ledger.transaction_type }}
                                        </span>
                                    </td>

                                    <!-- Reference No -->
                                    <td class="text-center">
                                        <span v-if="ledger.reference_no && ledger.reference_no !== '-'" class="fw-bold text-dark font-monospace">
                                            {{ ledger.reference_no }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>

                                    <!-- Party / Contact Name -->
                                    <td>
                                        <div v-if="ledger.party_name && ledger.party_name !== '-'" class="d-flex align-items-center gap-1">
                                            <i class="fas fa-user-circle text-muted small"></i>
                                            <span class="fw-semibold text-dark">{{ ledger.party_name }}</span>
                                        </div>
                                        <span v-else class="text-muted small">-</span>
                                    </td>

                                    <!-- Warehouse -->
                                    <td class="text-center">
                                        <span v-if="ledger.warehouse_name && ledger.warehouse_name !== '-'" class="badge bg-light text-secondary border small">
                                            <i class="fas fa-warehouse me-1"></i>{{ ledger.warehouse_name }}
                                        </span>
                                        <span v-else class="text-muted small">-</span>
                                    </td>

                                    <!-- Color -->
                                    <td class="text-center">
                                        <span v-if="ledger.color_title" class="badge bg-light text-dark border small d-inline-flex align-items-center gap-1">
                                            <span class="color-dot-xs rounded-circle" :style="getColorDotStyle(ledger.color_title)"></span>
                                            {{ ledger.color_title }}
                                        </span>
                                        <span v-else class="text-muted small">-</span>
                                    </td>

                                    <!-- Size (Only for Non-Electronics Shops) -->
                                    <td v-if="showSizeColumn" class="text-center">
                                        <span v-if="ledger.size_title" class="badge bg-secondary-soft text-secondary border small">
                                            {{ ledger.size_title }}
                                        </span>
                                        <span v-else class="text-muted small">-</span>
                                    </td>

                                    <!-- Serial Flag / Badge (Only if showSerialColumn) -->
                                    <td v-if="showSerialColumn" class="text-center">
                                        <button v-if="ledger.has_serial" type="button"
                                            class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 d-inline-flex align-items-center gap-1 shadow-xs"
                                            @click.stop="openSerialModal(ledger)"
                                            title="Click to view full serial numbers list">
                                            <i class="fas fa-barcode text-primary"></i>
                                            <span class="fw-bold">{{ ledger.serial_count }} Serials</span>
                                            <i class="fas fa-external-link-alt text-muted ms-1" style="font-size: 8px;"></i>
                                        </button>
                                        <span v-else class="text-muted small">-</span>
                                    </td>

                                    <!-- Rate -->
                                    <td class="text-end font-monospace text-muted small">
                                        {{ Number(ledger.rate) > 0 ? '৳' + formatNumber(ledger.rate) : '-' }}
                                    </td>

                                    <!-- Inflow (+) -->
                                    <td class="text-end font-monospace text-success fw-bold">
                                        <span v-if="Number(ledger.qty_in) > 0">+{{ formatQty(ledger.qty_in) }}</span>
                                        <span v-else class="text-muted opacity-50">-</span>
                                    </td>

                                    <!-- Outflow (-) -->
                                    <td class="text-end font-monospace text-danger fw-bold">
                                        <span v-if="Number(ledger.qty_out) > 0">-{{ formatQty(ledger.qty_out) }}</span>
                                        <span v-else class="text-muted opacity-50">-</span>
                                    </td>

                                    <!-- Running Balance -->
                                    <td class="text-end font-monospace fw-bold fs-6" :class="Number(ledger.balance) >= 0 ? 'text-primary' : 'text-danger'">
                                        {{ formatQty(ledger.balance) }}
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td :colspan="7 + (showSizeColumn ? 1 : 0) + (showSerialColumn ? 1 : 0) + 4" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                        <span v-if="search_data.item_id" class="fs-6">
                                            No stock transactions found for this item in the selected period.
                                        </span>
                                        <span v-else class="fs-6">
                                            Please select an item from above to generate the ledger statement.
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot v-if="datas.length > 0" class="table-light fw-bold">
                            <tr>
                                <td :colspan="7 + (showSizeColumn ? 1 : 0) + (showSerialColumn ? 1 : 0)" class="text-end text-uppercase text-secondary small">Period Summary Total:</td>
                                <td class="text-end text-success font-monospace">+{{ summaryData.totalIn }}</td>
                                <td class="text-end text-danger font-monospace">-{{ summaryData.totalOut }}</td>
                                <td class="text-end text-primary font-monospace fs-6">{{ summaryData.closing }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 📌 Printable Footer -->
                <div class="mt-4 pt-3 border-top small text-muted d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0">Generated by: <strong>{{ $root.user ? $root.user.name : 'Administrator' }}</strong></p>
                        <p class="mb-0" style="font-size: 11px;">Print Timestamp: {{ reportTimestamp }}</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0">This is an authorized system-generated stock ledger.</p>
                        <div class="d-none d-print-block mt-4">
                            <span class="border-top pt-1 px-4">Authorized Signature</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔍 Modal for Viewing Serial Numbers -->
            <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);" @click.self="closeSerialModal">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-3">
                        <div class="modal-header bg-primary text-white py-3">
                            <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                                <i class="fas fa-barcode"></i> Serial Numbers List
                            </h6>
                            <button type="button" class="btn-close btn-close-white" @click="closeSerialModal"></button>
                        </div>
                        <div class="modal-body p-4" v-if="activeSerialRow">
                            <!-- Metadata Header inside Modal -->
                            <div class="p-3 bg-light rounded border mb-3">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Item Name</small>
                                        <strong class="text-dark">{{ itemProfile ? itemProfile.title : 'Item' }}</strong>
                                    </div>
                                    <div class="col-sm-3">
                                        <small class="text-muted d-block">Transaction Type</small>
                                        <span class="badge" :class="getTypeBadgeClass(activeSerialRow.transaction_type)">
                                            {{ activeSerialRow.transaction_type }}
                                        </span>
                                    </div>
                                    <div class="col-sm-3">
                                        <small class="text-muted d-block">Reference No</small>
                                        <strong class="text-primary font-monospace">{{ activeSerialRow.reference_no || 'N/A' }}</strong>
                                    </div>
                                    <div class="col-sm-6" v-if="activeSerialRow.color_title || activeSerialRow.size_title">
                                        <small class="text-muted d-block">Variant</small>
                                        <span>Color: <strong>{{ activeSerialRow.color_title || 'N/A' }}</strong> <span v-if="activeSerialRow.size_title">| Size: <strong>{{ activeSerialRow.size_title }}</strong></span></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Total Serials</small>
                                        <span class="badge bg-dark rounded-pill px-2">{{ activeSerialRow.serial_count }} Serials</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Filter within Modal -->
                            <div class="input-group input-group-sm mb-3" v-if="activeSerialRow.serials && activeSerialRow.serials.length > 5">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control" v-model="serialSearchQuery" placeholder="Filter serial numbers..." />
                                <button v-if="serialSearchQuery" class="btn btn-outline-secondary" @click="serialSearchQuery = ''">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Serials Grid / Badges -->
                            <div class="serials-scroll-area p-3 border rounded bg-white" style="max-height: 280px; overflow-y: auto;">
                                <div class="d-flex flex-wrap gap-2">
                                    <span v-for="(sn, sIdx) in filteredModalSerials" :key="sIdx"
                                        class="badge bg-light text-dark border font-monospace p-2 d-inline-flex align-items-center gap-2 shadow-xs serial-chip"
                                        @click="copySingleSerial(sn)"
                                        title="Click to copy single serial">
                                        <span class="text-muted small">#{{ sIdx + 1 }}</span>
                                        <strong>{{ sn }}</strong>
                                        <i class="fas fa-copy text-secondary copy-icon"></i>
                                    </span>
                                </div>
                                <div v-if="filteredModalSerials.length === 0" class="text-center py-4 text-muted">
                                    No serial numbers match "{{ serialSearchQuery }}".
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1"
                                @click="copyAllSerials" :disabled="!activeSerialRow || !activeSerialRow.serials || activeSerialRow.serials.length === 0">
                                <i class="fas fa-copy"></i> Copy All Serials
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" @click="closeSerialModal">Close</button>
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

const tableColumns = [{ field: "status", title: "Status", align: "center" }];
const json_fields = {
    "Date": "transaction_date_formatted",
    "Time": "created_time",
    "Transaction Type": "transaction_type",
    "Reference No": "reference_no",
    "Party / Contact": "party_name",
    "Warehouse": "warehouse_name",
    "Color": "color_title",
    "Size": "size_title",
    "Has Serial": "has_serial",
    "Serial Count": "serial_count",
    "Serials": "serial_preview",
    "Unit Rate": "rate",
    "Total Value": "total_amount",
    "Stock In": "qty_in",
    "Stock Out": "qty_out",
    "Balance": "balance"
};
const model = "itemladger";

export default {
    data() {
        return {
            model: model,
            page_title: "Item Ledger Statement",
            reportDate: moment().format('D MMMM, YYYY'),
            reportTimestamp: moment().format('D MMMM, YYYY, h:mm:ss A'),
            json_fields: json_fields,
            search_data: {
                category_id: this.$route.query.category_id ? Number(this.$route.query.category_id) : null,
                item_id: this.$route.query.item_id ? Number(this.$route.query.item_id) : null,
                color_id: this.$route.query.color_id ? Number(this.$route.query.color_id) : null,
                size_id: this.$route.query.size_id ? Number(this.$route.query.size_id) : null,
                transaction_type: this.$route.query.transaction_type || null,
                start_date: this.$route.query.start_date || moment().startOf('month').format('D MMMM, YYYY'),
                end_date: this.$route.query.end_date || moment().endOf('month').format('D MMMM, YYYY')
            },
            table: {
                columns: tableColumns,
                routes: {},
                datas: [],
                meta: [],
                links: [],
            },
            datas: [],
            itemProfile: null,
            metrics: null,
            variantsBreakdown: [],
            categories: [],
            items: [],
            colors: [],
            sizes: [],

            // Serial numbers modal state
            showModal: false,
            activeSerialRow: null,
            serialSearchQuery: ""
        };
    },
    computed: {
        isElectronicsShop() {
            const shopType = this.site?.shop_type || this.$root.site?.shop_type;
            return shopType === 'electronics';
        },
        showSizeColumn() {
            return !this.isElectronicsShop;
        },
        hasAnySerials() {
            return this.datas.some(row => row.has_serial || (row.serials && row.serials.length > 0));
        },
        showSerialColumn() {
            if (this.isElectronicsShop) return true;
            if (this.itemProfile && (this.itemProfile.is_serialized == 1 || this.itemProfile.warranty_type !== 'none')) return true;
            return this.hasAnySerials;
        },
        activeColorTitle() {
            if (!this.search_data.color_id) return null;
            const c = this.colors.find(col => col.id == this.search_data.color_id);
            return c ? c.title : null;
        },
        activeSizeTitle() {
            if (!this.search_data.size_id) return null;
            const s = this.sizes.find(sz => sz.id == this.search_data.size_id);
            return s ? s.title : null;
        },
        colorOptions() {
            if (this.variantsBreakdown && this.variantsBreakdown.length > 0) {
                const map = {};
                this.variantsBreakdown.forEach(v => {
                    if (v.color_id && v.color_title) {
                        map[v.color_id] = { id: v.color_id, title: v.color_title };
                    }
                });
                const list = Object.values(map);
                if (list.length > 0) return list;
            }
            return this.colors;
        },
        sizeOptions() {
            if (this.variantsBreakdown && this.variantsBreakdown.length > 0) {
                const map = {};
                this.variantsBreakdown.forEach(v => {
                    if (v.size_id && v.size_title) {
                        map[v.size_id] = { id: v.size_id, title: v.size_title };
                    }
                });
                const list = Object.values(map);
                if (list.length > 0) return list;
            }
            return this.sizes;
        },
        filteredModalSerials() {
            if (!this.activeSerialRow || !this.activeSerialRow.serials) return [];
            if (!this.serialSearchQuery) return this.activeSerialRow.serials;
            const q = this.serialSearchQuery.toLowerCase().trim();
            return this.activeSerialRow.serials.filter(sn => sn.toLowerCase().includes(q));
        },
        summaryData() {
            if (!this.datas || this.datas.length === 0) {
                return { opening: 0, totalIn: 0, totalOut: 0, closing: 0 };
            }
            let opening = 0;
            let totalIn = 0;
            let totalOut = 0;

            this.datas.forEach(row => {
                if (row.transaction_type === 'Opening Balance') {
                    opening = Number(row.balance || 0);
                } else {
                    totalIn += Number(row.qty_in || 0);
                    totalOut += Number(row.qty_out || 0);
                }
            });

            const lastRow = this.datas[this.datas.length - 1];
            const closing = lastRow ? Number(lastRow.balance || 0) : 0;

            return {
                opening: this.formatQty(opening),
                totalIn: this.formatQty(totalIn),
                totalOut: this.formatQty(totalOut),
                closing: this.formatQty(closing)
            };
        }
    },
    watch: {
        'search_data.category_id'(newVal, oldVal) {
            if (oldVal !== undefined && oldVal !== newVal) {
                this.onCategoryChange(false);
            }
        },
        'search_data.transaction_type'() {
            this.search();
        },
        'search_data.color_id'() {
            this.search();
        },
        'search_data.size_id'() {
            this.search();
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

    methods: {
        search() {
            this.updateRouteQueryParams();
            this.getStockLadger();
        },

        updateRouteQueryParams() {
            this.$router.push({
                path: this.$route.path,
                query: {
                    category_id: this.search_data.category_id || undefined,
                    item_id: this.search_data.item_id || undefined,
                    color_id: this.search_data.color_id || undefined,
                    size_id: this.search_data.size_id || undefined,
                    transaction_type: this.search_data.transaction_type || undefined,
                    start_date: this.search_data.start_date || undefined,
                    end_date: this.search_data.end_date || undefined,
                }
            }).catch(() => {});
        },

        onCategoryChange(isInitial = false) {
            const category_id = this.search_data.category_id;
            const url = category_id ? `getitemsbycategory/${category_id}` : `getitemsbycategory`;

            axios.get(url)
                .then((response) => {
                    this.items = Array.isArray(response.data) ? response.data : [];
                    if (!isInitial) {
                        this.search_data.item_id = null;
                        this.datas = [];
                        this.itemProfile = null;
                        this.metrics = null;
                        this.variantsBreakdown = [];
                    }
                })
                .catch(() => {
                    this.items = [];
                });
        },

        getCategories() {
            axios.get(`getcategories/Item`)
                .then((response) => {
                    this.categories = Array.isArray(response.data) ? response.data : [];
                });
        },

        getColorsAndSizes() {
            axios.get('color?allData=true').then(res => { this.colors = Array.isArray(res.data) ? res.data : []; }).catch(() => {});
            if (!this.isElectronicsShop) {
                axios.get('size?allData=true').then(res => { this.sizes = Array.isArray(res.data) ? res.data : []; }).catch(() => {});
            }
        },

        getStockLadger() {
            if (!this.search_data.item_id) {
                this.datas = [];
                this.itemProfile = null;
                this.metrics = null;
                this.variantsBreakdown = [];
                this.$root.spinner = false;
                this.$root.tableSpinner = false;
                return;
            }

            this.$root.tableSpinner = true;
            axios
                .get(`report/itemladger`, { params: this.search_data })
                .then((res) => {
                    if (res.data && typeof res.data === 'object' && !Array.isArray(res.data)) {
                        this.itemProfile = res.data.item || null;
                        this.metrics = res.data.metrics || null;
                        this.variantsBreakdown = Array.isArray(res.data.variants_breakdown) ? res.data.variants_breakdown : [];
                        this.datas = Array.isArray(res.data.datas) ? res.data.datas : [];
                    } else if (Array.isArray(res.data)) {
                        this.datas = res.data;
                    } else {
                        this.datas = [];
                    }
                })
                .catch((err) => {
                    console.error("Error fetching item ledger:", err);
                    this.datas = [];
                })
                .finally(() => {
                    this.$root.spinner = false;
                    this.$root.tableSpinner = false;
                });
        },

        resetSearchData() {
            this.search_data.category_id = null;
            this.search_data.item_id = null;
            this.search_data.color_id = null;
            this.search_data.size_id = null;
            this.search_data.transaction_type = null;
            this.search_data.start_date = moment().startOf('month').format('D MMMM, YYYY');
            this.search_data.end_date = moment().endOf('month').format('D MMMM, YYYY');
            this.items = [];
            this.datas = [];
            this.itemProfile = null;
            this.metrics = null;
            this.variantsBreakdown = [];
            this.onCategoryChange(false);
            this.$router.push({ path: this.$route.path, query: {} }).catch(() => {});
        },

        openSerialModal(ledger) {
            this.activeSerialRow = ledger;
            this.serialSearchQuery = "";
            this.showModal = true;
        },

        closeSerialModal() {
            this.showModal = false;
            this.activeSerialRow = null;
            this.serialSearchQuery = "";
        },

        copyAllSerials() {
            if (!this.activeSerialRow || !this.activeSerialRow.serials || this.activeSerialRow.serials.length === 0) return;
            const text = this.activeSerialRow.serials.join(", ");
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    this.notification("All serial numbers copied to clipboard!", "success");
                });
            }
        },

        copySingleSerial(sn) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(sn).then(() => {
                    this.notification(`Copied: ${sn}`, "success");
                });
            }
        },

        getTypeBadgeClass(type) {
            switch (type) {
                case 'Opening Balance':
                case 'Opening':
                    return 'bg-secondary text-white';
                case 'Purchase':
                    return 'bg-success text-white';
                case 'GRN':
                    return 'bg-teal text-white';
                case 'Issue':
                case 'Sale':
                case 'Sales':
                    return 'bg-primary text-white';
                case 'SalesReturn':
                case 'Return':
                    return 'bg-warning text-dark';
                case 'Wastage':
                    return 'bg-danger text-white';
                default:
                    return 'bg-light text-dark border';
            }
        },

        getTypeIcon(type) {
            switch (type) {
                case 'Opening Balance':
                case 'Opening':
                    return 'fas fa-hourglass-start';
                case 'Purchase':
                    return 'fas fa-shopping-cart';
                case 'GRN':
                    return 'fas fa-truck-loading';
                case 'Issue':
                case 'Sale':
                case 'Sales':
                    return 'fas fa-receipt';
                case 'SalesReturn':
                case 'Return':
                    return 'fas fa-undo-alt';
                case 'Wastage':
                    return 'fas fa-trash-alt';
                default:
                    return 'fas fa-exchange-alt';
            }
        },

        getStockBadgeClass(stock) {
            const val = Number(stock || 0);
            if (val <= 0) return 'bg-danger text-white';
            if (val <= 5) return 'bg-warning text-dark';
            return 'bg-success text-white';
        },

        getStockStatusLabel(stock) {
            const val = Number(stock || 0);
            if (val <= 0) return 'Out of Stock';
            if (val <= 5) return 'Low Stock';
            return 'In Stock';
        },

        getColorDotStyle(colorName) {
            if (!colorName) return { backgroundColor: '#ccc' };
            const lower = colorName.toLowerCase().trim();
            const colorMap = {
                'black': '#111827',
                'white': '#f3f4f6',
                'red': '#ef4444',
                'blue': '#3b82f6',
                'green': '#10b981',
                'yellow': '#f59e0b',
                'orange': '#f97316',
                'purple': '#8b5cf6',
                'gray': '#6b7280',
                'grey': '#6b7280',
                'navy': '#1e3a8a',
                'maroon': '#800000',
                'pink': '#ec4899',
            };
            return {
                backgroundColor: colorMap[lower] || '#6366f1'
            };
        },

        formatNumber(val) {
            if (val === null || val === undefined || isNaN(val)) return '0.00';
            return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatQty(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            const num = Number(val);
            return num % 1 === 0 ? num.toString() : num.toFixed(2);
        }
    },

    created() {
        this.page_title = "Item Ledger Statement";
        this.getCategories();
        this.getColorsAndSizes();
        this.onCategoryChange(true);

        if (this.search_data.item_id) {
            this.getStockLadger();
        } else {
            this.$root.spinner = false;
            this.$root.tableSpinner = false;
        }
    },

    validators: {},
};
</script>

<style scoped>
.item-title-box {
    max-width: 650px;
    min-width: 220px;
}
.item-title-heading {
    font-size: 1.15rem;
    line-height: 1.35;
    word-break: break-word;
    overflow-wrap: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.border-top-primary {
    border-top: 3px solid #0d6efd !important;
}
.border-top-success {
    border-top: 3px solid #198754 !important;
}
.border-top-danger {
    border-top: 3px solid #dc3545 !important;
}
.border-top-info {
    border-top: 3px solid #0dcaf0 !important;
}
.bg-primary-soft {
    background-color: rgba(13, 110, 253, 0.08) !important;
}
.bg-secondary-soft {
    background-color: rgba(108, 117, 125, 0.1) !important;
}
.bg-info-soft {
    background-color: rgba(13, 202, 240, 0.1) !important;
}
.bg-teal {
    background-color: #20c997 !important;
}
.color-dot-xs {
    display: inline-block;
    width: 9px;
    height: 9px;
}
.serial-chip {
    cursor: pointer;
    transition: background-color 0.15s;
}
.serial-chip:hover {
    background-color: #e2e8f0 !important;
}
.serial-chip .copy-icon {
    font-size: 11px;
    opacity: 0.6;
}
.serial-chip:hover .copy-icon {
    opacity: 1;
    color: #0d6efd;
}
.shadow-xs {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.cursor-pointer {
    cursor: pointer;
}
.btn-xs {
    padding: 0.15rem 0.4rem;
    font-size: 0.75rem;
}

@media print {
    .d-print-none {
        display: none !important;
    }
    .d-print-block {
        display: block !important;
    }
    #printArea {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
    table {
        font-size: 11px !important;
    }
}
</style>
