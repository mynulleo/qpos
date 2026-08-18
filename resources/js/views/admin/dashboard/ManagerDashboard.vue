<template>
    <div class="row g-3 dashboard-wrapper">
        <!-- Loader -->
        <div class="component_loader" :class="{ 'z-top': $root.initialLoader }"
            v-if="$root.spinner && !$root.tableSpinner">
            <div class="loader"></div>
        </div>

        <template v-else>
            <!-- ⚡ Quick Action & POS Shortcut Bar -->
            <div class="col-12">
                <div class="card border-0 shadow-sm quick-action-card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge theme-bg text-white px-2 py-1"><i class="fas fa-bolt me-1"></i>Quick POS Actions</span>
                                <span class="text-muted small fw-semibold">Frequently used terminal tools & shortcuts</span>
                            </div>
                            <span class="text-muted small font-monospace d-none d-md-inline"><i class="far fa-clock me-1"></i>{{ currentDate }}</span>
                        </div>

                        <div class="row g-2 quick-links-grid">
                            <!-- 1. New Sale (POS) -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/pos" class="quick-btn btn-pos-primary d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-cash-register text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">New Sale (POS)</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Sell & Print (F8)</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- 2. Available Stock -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/report/availablestock" class="quick-btn btn-pos-emerald d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-boxes text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">Available Stock</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Real-time Inventory</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- 3. Sales Return -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/pos/return" class="quick-btn btn-pos-amber d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-undo-alt text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">Sales Return</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Invoice Refund</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- 4. Barcode / Label Print -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/pos/labelprint" class="quick-btn btn-pos-purple d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-barcode text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">Label Print</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Barcode Generator</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- Secondary Shortcuts -->
                            <!-- 5. Invoices -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/invoice" class="quick-btn btn-pos-blue d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-file-invoice-dollar text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">Invoices List</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Order Records</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- 6. New Purchase -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/purchase/create" class="quick-btn btn-pos-teal d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-cart-plus text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">New Purchase</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Stock Inward</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- 7. Warranty Claims -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/warrantyClaim" class="quick-btn btn-pos-rose d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-shield-alt text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">Warranty Claims</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Tracking & Logs</div>
                                    </div>
                                </router-link>
                            </div>

                            <!-- 8. Sales Report -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-6">
                                <router-link to="/report/sales" class="quick-btn btn-pos-indigo d-flex align-items-center gap-2 p-2 rounded text-decoration-none">
                                    <div class="quick-icon-box bg-white bg-opacity-25 rounded d-flex align-items-center justify-content-center">
                                        <i class="fas fa-chart-line text-white fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 13px;">Daily Sales Report</div>
                                        <div class="text-white-50 small" style="font-size: 11px;">Analytics & Summary</div>
                                    </div>
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📊 Top Metric KPI Counter Cards -->
            <div class="col-12">
                <div class="row g-3">
                    <!-- Today Sales -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold small text-uppercase">Today's Sales</span>
                                    <div class="stat-icon-wrapper theme-bg-soft text-theme rounded-circle">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-dark">৳ {{ formatNum(stats.today_sales) }}</h3>
                                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                                    <span><i class="fas fa-file-invoice text-success me-1"></i>{{ stats.today_sales_count || 0 }} Invoices</span>
                                    <span class="text-success fw-semibold">Received: ৳ {{ formatNum(stats.today_received) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Month Sales -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold small text-uppercase">Monthly Sales</span>
                                    <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success rounded-circle">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-dark">৳ {{ formatNum(stats.month_sales) }}</h3>
                                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                                    <span><i class="fas fa-receipt text-primary me-1"></i>{{ stats.month_sales_count || 0 }} Invoices</span>
                                    <span class="text-primary fw-semibold">Received: ৳ {{ formatNum(stats.month_received) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Customer Due -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold small text-uppercase">Total Receivables (Due)</span>
                                    <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger rounded-circle">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-danger">৳ {{ formatNum(clientSummary.total_outstanding) }}</h3>
                                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                                    <span>This Month Due: ৳ {{ formatNum(clientSummary.cm_due) }}</span>
                                    <router-link to="/report/receivable" class="text-decoration-none small text-danger fw-semibold">Details &rarr;</router-link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory & Low Stock -->
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-bold small text-uppercase">Total Items in Stock</span>
                                    <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning rounded-circle">
                                        <i class="fas fa-cubes"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-dark">{{ stats.total_items || 0 }}</h3>
                                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                                    <span class="text-danger fw-semibold"><i class="fas fa-exclamation-triangle me-1"></i>{{ zeroStockList.length }} Zero Stock</span>
                                    <router-link to="/report/availablestock" class="text-decoration-none small text-muted">Stock Report &rarr;</router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📈 Middle Section: Current Month Sales Line Chart (8-col) + Stock Status (4-col) -->
            <div class="col-xl-8 col-lg-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h5 class="fw-bold mb-0 text-theme d-flex align-items-center gap-2">
                                    <i class="fas fa-chart-line theme-text"></i>
                                    <span>Current Month Sales Trend ({{ barChartDataMonth }})</span>
                                </h5>
                                <span class="text-muted small">Daily trajectory of Sales, Collections & Due</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border"><strong class="text-primary">Sales:</strong> ৳ {{ formatNum(totalMonthSales) }}</span>
                                <span class="badge bg-light text-dark border"><strong class="text-success">Received:</strong> ৳ {{ formatNum(totalMonthReceived) }}</span>
                                <span class="badge bg-light text-dark border"><strong class="text-danger">Due:</strong> ৳ {{ formatNum(totalMonthDue) }}</span>
                            </div>
                        </div>

                        <div style="height: 340px; position: relative;">
                            <LineChart :chartData="lineChartData" :chartOptions="lineChartOptions" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📦 Stock Inventory Status (4-col) -->
            <div class="col-xl-4 col-lg-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-warehouse fs-5 theme-text"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Stock Summary</h6>
                                    <small class="text-muted" style="font-size: 11px;">Active product quantities</small>
                                </div>
                            </div>
                            <router-link to="/report/availablestock" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px;">
                                Details
                            </router-link>
                        </div>

                        <div class="table-responsive flex-grow-1" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Item Name</th>
                                        <th class="text-end">Stock Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(itemstock, index) in stockList" :key="index">
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 170px;">{{ itemstock.item?.title || 'Unknown' }}</div>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">{{ itemstock.item?.barcode || '' }}</small>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge" :class="itemstock.current_stock > 5 ? 'bg-success' : (itemstock.current_stock > 0 ? 'bg-warning text-dark' : 'bg-danger')">
                                                {{ itemstock.current_stock }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!stockList || stockList.length === 0">
                                        <td colspan="2" class="text-center text-muted py-3">No stock data available</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🚨 Out of Stock - High Demand (6-col) -->
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-exclamation-triangle text-danger fs-5"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">🚨 Top Selling Out of Stock (জিরো স্টক পণ্য)</h6>
                                    <small class="text-muted" style="font-size: 11px;">Most sold items currently at 0 stock (Priority Restock)</small>
                                </div>
                            </div>
                            <router-link to="/report/availablestock" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px;">
                                Stock Report
                            </router-link>
                        </div>

                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th class="text-center">Total Sold</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(s, idx) in zeroStockList" :key="idx">
                                        <td class="font-monospace text-muted">{{ s.barcode || 'N/A' }}</td>
                                        <td class="fw-bold text-dark">{{ s.title || 'Unknown' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ s.category_title || 'General' }}</span></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold font-monospace">
                                                {{ s.total_sold_qty }} Sold
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger text-white">
                                                0 (Empty)
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <router-link to="/purchase/create" class="btn btn-xs btn-outline-danger" title="Purchase Reorder">
                                                <i class="fas fa-cart-plus"></i> Restock
                                            </router-link>
                                        </td>
                                    </tr>
                                    <tr v-if="!zeroStockList || zeroStockList.length === 0">
                                        <td colspan="6" class="text-center text-success py-3">
                                            <i class="fas fa-check-circle me-1"></i> No zero stock items! All products have stock available.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent POS Invoices Table (6-col) -->
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-history theme-text fs-5"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Recent Invoices</h6>
                                    <small class="text-muted" style="font-size: 11px;">Latest completed sales orders</small>
                                </div>
                            </div>
                            <router-link to="/invoice" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px;">
                                View All
                            </router-link>
                        </div>

                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(inv, idx) in recentInvoicesList" :key="idx">
                                        <td class="font-monospace fw-bold text-dark">{{ inv.invoice_no }}</td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 130px;">{{ inv.client_name || 'Walk-in' }}</div>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">{{ inv.client_mobile || '' }}</small>
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-dark">৳ {{ formatNum(inv.amount) }}</td>
                                        <td class="text-end font-monospace text-success">৳ {{ formatNum(inv.paid_amount) }}</td>
                                        <td class="text-center">
                                            <span class="badge" :class="inv.is_closed ? 'bg-success' : 'bg-danger'">
                                                {{ inv.is_closed ? 'Paid' : 'Due' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <router-link :to="'/invoice/' + inv.id" class="btn btn-xs btn-light border" title="View Invoice">
                                                <i class="fas fa-eye text-muted"></i>
                                            </router-link>
                                        </td>
                                    </tr>
                                    <tr v-if="!recentInvoicesList || recentInvoicesList.length === 0">
                                        <td colspan="6" class="text-center text-muted py-3">No recent invoices recorded</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import LineChart from "../../../components/chart/line.vue";

export default {
    name: "ManagerDashboard",
    components: { LineChart },
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            currentDate: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
            lineChartData: {
                labels: [],
                datasets: [],
            },
            lineChartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11, weight: 'bold' } }
                    },
                    tooltip: {
                        padding: 10,
                        boxPadding: 4,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        beginAtZero: true,
                        ticks: { font: { size: 10 } }
                    }
                }
            },
        };
    },
    computed: {
        stats() {
            return this.data?.dashboard?.statisticsData || {};
        },
        clientSummary() {
            return this.data?.dashboard?.clientSummary || {};
        },
        stockList() {
            return this.data?.dashboard?.stockData || [];
        },
        zeroStockList() {
            return this.data?.dashboard?.zeroStockData || [];
        },
        recentInvoicesList() {
            return this.data?.dashboard?.recentInvoices || [];
        },
        barChartDataMonth() {
            return this.data?.dashboard?.barData?.month_name || 'This Month';
        },
        totalMonthSales() {
            return this.data?.dashboard?.barData?.total_sales || this.stats.month_sales || 0;
        },
        totalMonthReceived() {
            return this.data?.dashboard?.barData?.total_receive || this.stats.month_received || 0;
        },
        totalMonthDue() {
            return this.data?.dashboard?.barData?.total_due || 0;
        }
    },
    watch: {
        data: {
            handler(newData) {
                if (newData) {
                    this.generateLineChart(newData);
                }
            },
            deep: true,
            immediate: true,
        },
    },
    methods: {
        formatNum(val) {
            const num = parseFloat(val);
            if (isNaN(num)) return '0.00';
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        generateLineChart(data) {
            if (!data?.dashboard?.barData) return;
            const { labels, data: chartData } = data.dashboard.barData;
            this.lineChartData = {
                labels: labels || [],
                datasets: [
                    {
                        label: "Invoice (Tk.)",
                        borderColor: "#112C47",
                        backgroundColor: "rgba(17, 44, 70, 0.08)",
                        borderWidth: 2.5,
                        pointBackgroundColor: "#112C47",
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        fill: true,
                        data: Object.values(chartData?.invoice || {})
                    },
                    {
                        label: "Received (Tk.)",
                        borderColor: "#10b981",
                        backgroundColor: "rgba(16, 185, 129, 0.08)",
                        borderWidth: 2.5,
                        pointBackgroundColor: "#10b981",
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        fill: true,
                        data: Object.values(chartData?.receive || {})
                    },
                    {
                        label: "Due (Tk.)",
                        borderColor: "#ef4444",
                        backgroundColor: "rgba(239, 68, 68, 0.08)",
                        borderWidth: 2,
                        pointBackgroundColor: "#ef4444",
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        borderDash: [4, 4],
                        tension: 0.35,
                        fill: false,
                        data: Object.values(chartData?.due || {})
                    },
                ],
            };
        }
    },
};
</script>

<style scoped>
.dashboard-wrapper {
    font-family: inherit;
}

.theme-bg {
    background-color: rgb(17, 44, 70) !important;
}

.theme-text {
    color: rgb(17, 44, 70) !important;
}

.text-theme {
    color: rgb(17, 44, 70) !important;
}

.theme-bg-soft {
    background-color: rgba(17, 44, 70, 0.1) !important;
}

.btn-theme {
    background-color: rgb(17, 44, 70) !important;
    border-color: rgb(17, 44, 70) !important;
    color: #ffffff !important;
}

.btn-theme:hover {
    background-color: #1a3d61 !important;
    color: #ffffff !important;
}

/* Quick Action Buttons Styling */
.quick-btn {
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.quick-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.quick-icon-box {
    width: 36px;
    height: 36px;
    min-width: 36px;
}

.btn-pos-primary {
    background: linear-gradient(135deg, rgb(17, 44, 70) 0%, #1e3a5f 100%);
}

.btn-pos-emerald {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
}

.btn-pos-amber {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
}

.btn-pos-purple {
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
}

.btn-pos-blue {
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
}

.btn-pos-teal {
    background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
}

.btn-pos-rose {
    background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
}

.btn-pos-indigo {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
}

/* Stat Cards */
.stat-card {
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon-wrapper {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.btn-xs {
    padding: 2px 8px !important;
    font-size: 11px !important;
    border-radius: 4px !important;
}
</style>
