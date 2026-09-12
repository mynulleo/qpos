<template>
    <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
        <div class="view_page site-settings-view">
            <!-- 🌟 Top Store Hero & Summary Banner -->
            <div class="card border-0 shadow-sm mb-4 hero-banner">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="store-logo-frame bg-white p-2 rounded shadow-sm border d-flex align-items-center justify-content-center">
                                <img
                                    :src="data?.logo_one === 'no_server_image' ? emptyServerImage() : data?.logo_one || emptyImage()"
                                    alt="Store Logo"
                                    class="img-fluid rounded"
                                    style="max-height: 55px; max-width: 140px; object-fit: contain;"
                                />
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h4 class="fw-bold mb-0 text-white">{{ data?.title || 'QPOS Store' }}</h4>
                                    <span class="badge bg-white text-dark font-monospace fw-bold">{{ data?.short_title || 'QPOS' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                    <!-- Shop Type Badge -->
                                    <span class="badge bg-success bg-opacity-75 text-white" v-if="data?.shop_type === 'grocery' || data?.shop_type === 'departmental'">
                                        <i class="fas fa-shopping-basket me-1"></i> Grocery & Departmental
                                    </span>
                                    <span class="badge bg-info bg-opacity-75 text-white" v-else-if="data?.shop_type === 'clothing'">
                                        <i class="fas fa-tshirt me-1"></i> Clothing & Fashion
                                    </span>
                                    <span class="badge bg-primary bg-opacity-75 text-white" v-else-if="data?.shop_type === 'electronics'">
                                        <i class="fas fa-tv me-1"></i> Electronics & Gadgets
                                    </span>
                                    <span class="badge bg-secondary bg-opacity-75 text-white" v-else>
                                        <i class="fas fa-boxes me-1"></i> {{ data?.shop_type || 'General Retail' }}
                                    </span>

                                    <!-- Printer Badge -->
                                    <span class="badge bg-light bg-opacity-25 text-white" v-if="data?.printer_type === 'normal'">
                                        <i class="fas fa-print me-1"></i> Normal Printer ({{ data?.normal_paper_size || 'A4' }})
                                    </span>
                                    <span class="badge bg-light bg-opacity-25 text-white" v-else>
                                        <i class="fas fa-receipt me-1"></i> Thermal Roll ({{ data?.thermal_paper_size || '80mm' }})
                                    </span>

                                    <!-- Currency Badge -->
                                    <span class="badge bg-light bg-opacity-25 text-white">
                                        <i class="fas fa-money-bill-wave me-1"></i> {{ data?.currency?.title || 'BDT' }} ({{ data?.currency?.short_name || '৳' }})
                                    </span>

                                    <!-- Mode Badge -->
                                    <span class="badge bg-light bg-opacity-25 text-white">
                                        <i class="fas fa-server me-1"></i> Mode: {{ getSystemModeName(data?.system_mode) }}
                                    </span>

                                    <!-- Loyalty Program Badge -->
                                    <span class="badge" :class="data?.coupon_enabled ? 'bg-warning text-dark' : 'bg-light bg-opacity-25 text-white'">
                                        <i class="fas fa-gift me-1"></i> Loyalty: {{ data?.coupon_enabled ? 'Active' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Button -->
                        <div>
                            <router-link
                                :to="{ name: 'siteSetting.edit', params: { id: data?.id || 1 } }"
                                class="btn btn-light fw-bold text-theme px-3 py-2 shadow-sm d-flex align-items-center gap-2"
                            >
                                <i class="fas fa-edit"></i> Edit Site Settings
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📋 Main Information Grid -->
            <div class="row g-3">
                <!-- 🏢 1. Store & Brand Identity Profile -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Store & Brand Identity</h6>
                                <small class="text-muted" style="font-size: 11px;">Primary naming and official contact channels</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-heading me-2 text-muted"></i>Store Title (প্রতিষ্ঠানের নাম)</td>
                                        <td class="spec-value fw-bold text-dark">{{ data?.title || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-tag me-2 text-muted"></i>Short Title (সংক্ষিপ্ত নাম)</td>
                                        <td class="spec-value font-monospace fw-semibold">
                                            <span class="badge bg-light text-dark border font-monospace">{{ data?.short_title || 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-envelope me-2 text-muted"></i>Contact Email</td>
                                        <td class="spec-value">
                                            <a v-if="data?.contact_email" :href="'mailto:' + data?.contact_email" class="text-decoration-none font-monospace text-primary">
                                                <i class="far fa-envelope me-1"></i>{{ data?.contact_email }}
                                            </a>
                                            <span v-else class="text-muted">N/A</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-paper-plane me-2 text-muted"></i>Feedback Email</td>
                                        <td class="spec-value">
                                            <a v-if="data?.feedback_email" :href="'mailto:' + data?.feedback_email" class="text-decoration-none font-monospace text-dark">
                                                <i class="far fa-envelope me-1"></i>{{ data?.feedback_email }}
                                            </a>
                                            <span v-else class="text-muted">N/A</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-phone-alt me-2 text-muted"></i>Primary Mobile</td>
                                        <td class="spec-value font-monospace fw-bold text-success">
                                            <a v-if="data?.mobile1" :href="'tel:' + data?.mobile1" class="text-decoration-none text-success">
                                                <i class="fas fa-phone me-1"></i>{{ data?.mobile1 }}
                                            </a>
                                            <span v-else class="text-muted">N/A</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-phone me-2 text-muted"></i>Secondary Mobile</td>
                                        <td class="spec-value font-monospace">
                                            <a v-if="data?.mobile2" :href="'tel:' + data?.mobile2" class="text-decoration-none text-dark">
                                                <i class="fas fa-phone me-1"></i>{{ data?.mobile2 }}
                                            </a>
                                            <span v-else class="text-muted">N/A</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ⚙️ 2. POS System & Shop Type Configuration -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">System & POS Configuration</h6>
                                <small class="text-muted" style="font-size: 11px;">Currency, system mode and business checkout workflow</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-money-bill-wave me-2 text-muted"></i>Default Currency</td>
                                        <td class="spec-value">
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold font-monospace px-2 py-1">
                                                {{ data?.currency?.title || 'BDT' }} ({{ data?.currency?.short_name || '৳' }})
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-server me-2 text-muted"></i>System Mode</td>
                                        <td class="spec-value">
                                            <span class="badge" :class="getSystemModeBadgeClass(data?.system_mode)">
                                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                {{ getSystemModeName(data?.system_mode) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-store me-2 text-muted"></i>Business Shop Type</td>
                                        <td class="spec-value">
                                            <div v-if="data?.shop_type === 'grocery' || data?.shop_type === 'departmental'" class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success"><i class="fas fa-shopping-basket me-1"></i> Grocery & Departmental</span>
                                            </div>
                                            <div v-else-if="data?.shop_type === 'clothing'" class="d-flex align-items-center gap-2">
                                                <span class="badge bg-info text-dark"><i class="fas fa-tshirt me-1"></i> Clothing & Fashion</span>
                                            </div>
                                            <div v-else-if="data?.shop_type === 'electronics'" class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary"><i class="fas fa-tv me-1"></i> Electronics & Gadgets</span>
                                            </div>
                                            <div v-else class="d-flex align-items-center gap-2">
                                                <span class="badge bg-secondary"><i class="fas fa-boxes me-1"></i> General Retail</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-bolt me-2 text-muted"></i>POS Checkout Behavior</td>
                                        <td class="spec-value">
                                            <span v-if="data?.shop_type === 'grocery' || data?.shop_type === 'departmental'" class="text-success fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i> Fast POS: Direct Add-to-Cart (0 popup when items have no color/size/serial)
                                            </span>
                                            <span v-else-if="data?.shop_type === 'clothing'" class="text-info fw-semibold">
                                                <i class="fas fa-tshirt me-1"></i> Apparel: Color & Size variation selection modal
                                            </span>
                                            <span v-else-if="data?.shop_type === 'electronics'" class="text-primary fw-semibold">
                                                <i class="fas fa-barcode me-1"></i> Serialized: Serial / IMEI & Warranty claim tracking
                                            </span>
                                            <span v-else class="text-secondary fw-semibold">
                                                <i class="fas fa-box me-1"></i> Standard: General barcode inventory management
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="p-3 bg-light m-3 rounded border">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle text-theme fs-5"></i>
                                    <div>
                                        <strong>Active Workflow:</strong>
                                        <span v-if="data?.shop_type === 'grocery' || data?.shop_type === 'departmental'">
                                            Items scanned or searched in POS will immediately add to cart without popups. If duplicate barcodes exist, a fast selection modal with keyboard shortcuts (<kbd>1</kbd>-<kbd>9</kbd>, <kbd>Enter</kbd>) will open.
                                        </span>
                                        <span v-else-if="data?.shop_type === 'clothing'">
                                            Apparel items will prompt for specific color and size variants during POS selection.
                                        </span>
                                        <span v-else-if="data?.shop_type === 'electronics'">
                                            Electronic products support serial number entry, warranty cards, and claim tracking.
                                        </span>
                                        <span v-else>
                                            Standard retail workflow with barcode and direct inventory tracking.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🖨️ 3. Printer & Print Paper Size Setup -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon-box bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center">
                                    <i class="fas fa-print"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Printer, Paper & Barcode Setup</h6>
                                    <small class="text-muted" style="font-size: 11px;">Hardware print format for POS receipts, invoices & barcodes</small>
                                </div>
                            </div>
                            <span class="badge" :class="data?.printer_type === 'normal' ? 'bg-primary' : 'bg-success'">
                                <i :class="data?.printer_type === 'normal' ? 'fas fa-print me-1' : 'fas fa-receipt me-1'"></i>
                                {{ data?.printer_type === 'normal' ? 'Normal Printer (' + (data?.normal_paper_size || 'A4') + ')' : 'Thermal Printer (' + (data?.thermal_paper_size || '80mm') + ')' }}
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-cog me-2 text-muted"></i>Hardware Printer Type</td>
                                        <td class="spec-value">
                                            <span class="badge" :class="data?.printer_type === 'normal' ? 'bg-primary' : 'bg-success'">
                                                {{ data?.printer_type === 'normal' ? 'Normal Printer (Laser / Inkjet)' : 'Thermal Receipt Printer (POS Roll)' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="data?.printer_type === 'normal'">
                                        <td class="spec-label"><i class="fas fa-file-alt me-2 text-muted"></i>Configured Paper Size</td>
                                        <td class="spec-value font-monospace fw-bold text-primary">
                                            {{ data?.normal_paper_size || 'A4' }} Paper ({{ data?.normal_paper_size === 'A5' ? '148mm × 210mm / Half Page' : '210mm × 297mm / Full Page' }})
                                        </td>
                                    </tr>
                                    <tr v-else>
                                        <td class="spec-label"><i class="fas fa-scroll me-2 text-muted"></i>Thermal Roll Width</td>
                                        <td class="spec-value font-monospace fw-bold text-success">
                                            {{ data?.thermal_paper_size || '80mm' }} ({{ data?.thermal_paper_size === '60mm' ? '2.5" / 58-60mm Compact Roll' : '3" / 80mm Standard POS Roll' }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-barcode me-2 text-muted"></i>Default Barcode Label Preset</td>
                                        <td class="spec-value font-monospace fw-bold text-dark">
                                            <span class="badge bg-light text-dark border font-monospace">
                                                {{ getLabelPresetName(data?.label_preset) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-check-double me-2 text-muted"></i>Application Targets</td>
                                        <td class="spec-value">
                                            <span class="badge bg-light text-dark border me-1">POS Sales Checkout</span>
                                            <span class="badge bg-light text-dark border me-1">Warranty Claim Slips</span>
                                            <span class="badge bg-light text-dark border">Barcode Tag Generator</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="p-3 bg-light m-3 rounded border">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle text-primary fs-5"></i>
                                    <div>
                                        <strong>Auto Print Workflow:</strong> Completing a POS sale or printing a Warranty Claim slip will automatically render using
                                        <strong class="text-dark" v-if="data?.printer_type === 'normal'">
                                            {{ data?.normal_paper_size || 'A4' }} Invoice Layout
                                        </strong>
                                        <strong class="text-dark" v-else>
                                            {{ data?.thermal_paper_size || '80mm' }} Thermal Receipt
                                        </strong>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🎁 4. Customer Loyalty & Coupon Reward Points -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon-box bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Customer Loyalty & Rewards</h6>
                                    <small class="text-muted" style="font-size: 11px;">Points accumulation on purchases and invoice discount conversions</small>
                                </div>
                            </div>
                            <span class="badge" :class="data?.coupon_enabled ? 'bg-success' : 'bg-secondary'">
                                <i :class="data?.coupon_enabled ? 'fas fa-check-circle me-1' : 'fas fa-times-circle me-1'"></i>
                                {{ data?.coupon_enabled ? 'Active / Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-toggle-on me-2 text-muted"></i>Loyalty Program Status</td>
                                        <td class="spec-value">
                                            <span class="badge" :class="data?.coupon_enabled ? 'bg-success' : 'bg-secondary'">
                                                {{ data?.coupon_enabled ? 'Customer Points Enabled' : 'Program Disabled' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="data?.coupon_enabled">
                                        <td class="spec-label"><i class="fas fa-coins me-2 text-muted"></i>Earning Rate (১ টাকা ক্রয়ে পয়েন্ট)</td>
                                        <td class="spec-value font-monospace fw-bold text-success">
                                            1 Tk Purchase = {{ data?.point_earn_rate || 1 }} Point(s)
                                        </td>
                                    </tr>
                                    <tr v-if="data?.coupon_enabled">
                                        <td class="spec-label"><i class="fas fa-hand-holding-usd me-2 text-muted"></i>Redemption Rate (পয়েন্ট কনভার্সন)</td>
                                        <td class="spec-value font-monospace fw-bold text-primary">
                                            {{ data?.point_redeem_rate || 10 }} Points = 1 Tk Discount
                                        </td>
                                    </tr>
                                    <tr v-if="data?.coupon_enabled">
                                        <td class="spec-label"><i class="fas fa-lock me-2 text-muted"></i>Minimum Redeem Threshold</td>
                                        <td class="spec-value font-monospace fw-bold text-dark">
                                            {{ data?.min_points_to_redeem || 10 }} Points
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-if="data?.coupon_enabled" class="p-3 bg-light m-3 rounded border">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fas fa-calculator text-warning fs-5"></i>
                                    <div>
                                        <strong>Live Calculation:</strong> A customer spending <strong>Tk. 1,000</strong> earns <strong>{{ (1000 * (data?.point_earn_rate || 1)).toLocaleString() }} points</strong>, which yields <strong>Tk. {{ ((1000 * (data?.point_earn_rate || 1)) / (data?.point_redeem_rate || 10)).toFixed(2) }}</strong> invoice discount.
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-3 bg-light m-3 rounded border">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle text-secondary fs-5"></i>
                                    <div>
                                        Loyalty point accumulation and redemption are currently turned off. To enable customer reward points, edit site settings.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 📍 5. Store Addresses & Physical Location with Maps -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Store Addresses & Map Locations</h6>
                                <small class="text-muted" style="font-size: 11px;">Primary outlet and secondary branch physical locations</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Primary Address (মূল ঠিকানা)</td>
                                        <td class="spec-value fw-semibold text-dark">{{ data?.address || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-map me-2 text-primary"></i>Primary Google Maps</td>
                                        <td class="spec-value">
                                            <div v-if="data?.map">
                                                <a :href="data?.map" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                    <i class="fas fa-external-link-alt"></i> View on Google Maps
                                                </a>
                                                <div class="font-monospace text-muted mt-1 text-truncate" style="max-width: 320px; font-size: 11px;">{{ data?.map }}</div>
                                            </div>
                                            <span v-else class="text-muted small">No map link configured</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-map-pin me-2 text-info"></i>Secondary Address (শাখা ঠিকানা)</td>
                                        <td class="spec-value text-dark">{{ data?.address_two || 'No secondary branch address' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-directions me-2 text-info"></i>Secondary Google Maps</td>
                                        <td class="spec-value">
                                            <div v-if="data?.map_two">
                                                <a :href="data?.map_two" target="_blank" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1">
                                                    <i class="fas fa-external-link-alt"></i> View Branch Map
                                                </a>
                                                <div class="font-monospace text-muted mt-1 text-truncate" style="max-width: 320px; font-size: 11px;">{{ data?.map_two }}</div>
                                            </div>
                                            <span v-else class="text-muted small">No secondary map link</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 💳 6. Banking, Invoicing & Tax Details -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Banking, Invoicing & Tax Info</h6>
                                <small class="text-muted" style="font-size: 11px;">Official invoice header credentials and banking</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-file-invoice me-2 text-muted"></i>VAT / BIN Registration No</td>
                                        <td class="spec-value font-monospace fw-bold text-dark">{{ data?.vat_no || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-barcode me-2 text-muted"></i>HS Code</td>
                                        <td class="spec-value font-monospace">{{ data?.hs_code || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-globe me-2 text-muted"></i>SWIFT Code</td>
                                        <td class="spec-value font-monospace">{{ data?.swift_code || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-landmark me-2 text-muted"></i>Bank Name</td>
                                        <td class="spec-value fw-semibold text-dark">{{ data?.bank_name || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-code-branch me-2 text-muted"></i>Branch Name</td>
                                        <td class="spec-value">{{ data?.branch_name || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-money-check me-2 text-muted"></i>Account Number</td>
                                        <td class="spec-value font-monospace fw-bold text-theme">{{ data?.account_number || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-route me-2 text-muted"></i>Routing Number</td>
                                        <td class="spec-value font-monospace">{{ data?.routing_number || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 🖼️ 7. Brand Media & Logos -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-images"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Brand Assets & Media Previews</h6>
                                <small class="text-muted" style="font-size: 11px;">Primary logos, invoice headers, sidebar icons, and browser tab favicons</small>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Main Logo -->
                                <div class="col-md-4 col-sm-6 text-center">
                                    <div class="media-preview-card p-3 rounded border bg-light h-100 d-flex flex-column align-items-center justify-content-between">
                                        <div class="media-img-container mb-2 d-flex align-items-center justify-content-center bg-white rounded p-2 w-100 border" style="height: 100px;">
                                            <img
                                                :src="data?.logo_one === 'no_server_image' ? emptyServerImage() : data?.logo_one || emptyImage()"
                                                alt="Main Logo"
                                                class="img-fluid"
                                                style="max-height: 80px; object-fit: contain;"
                                            />
                                        </div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark">Main Brand Logo (প্রাইমারি লোগো)</div>
                                            <span class="badge bg-light text-muted border mt-1" style="font-size: 11px;">Top Header & POS Invoices</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Small Logo -->
                                <div class="col-md-4 col-sm-6 text-center">
                                    <div class="media-preview-card p-3 rounded border bg-light h-100 d-flex flex-column align-items-center justify-content-between">
                                        <div class="media-img-container mb-2 d-flex align-items-center justify-content-center bg-white rounded p-2 w-100 border" style="height: 100px;">
                                            <img
                                                :src="data?.logo_small_one === 'no_server_image' ? emptyServerImage() : data?.logo_small_one || emptyImage()"
                                                alt="Small Logo"
                                                class="img-fluid"
                                                style="max-height: 80px; object-fit: contain;"
                                            />
                                        </div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark">Small Brand Logo (সংক্ষিপ্ত লোগো)</div>
                                            <span class="badge bg-light text-muted border mt-1" style="font-size: 11px;">Sidebar & Collapsed View</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Favicon -->
                                <div class="col-md-4 col-sm-6 text-center">
                                    <div class="media-preview-card p-3 rounded border bg-light h-100 d-flex flex-column align-items-center justify-content-between">
                                        <div class="media-img-container mb-2 d-flex align-items-center justify-content-center bg-white rounded p-2 w-100 border" style="height: 100px;">
                                            <img
                                                :src="data?.favicon === 'no_server_image' ? emptyServerImage() : data?.favicon || emptyImage()"
                                                alt="Favicon"
                                                class="img-fluid"
                                                style="max-height: 52px; max-width: 52px; object-fit: contain;"
                                            />
                                        </div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark">Browser Favicon (ট্যাব আইকন)</div>
                                            <span class="badge bg-light text-muted border mt-1" style="font-size: 11px;">Browser Tab & Bookmarks</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🕒 8. System Meta & Timestamps -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span><i class="far fa-calendar-plus me-1 text-primary"></i><strong>Created:</strong> {{ enFormat(data?.created_at) || 'N/A' }}</span>
                                <span><i class="far fa-calendar-check me-1 text-success"></i><strong>Last Updated:</strong> {{ enFormat(data?.updated_at) || 'N/A' }}</span>
                            </div>
                            <div class="font-monospace text-theme">
                                <i class="fas fa-shield-alt me-1"></i>QPOS Retail Enterprise Architecture
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </view-page>
</template>

<script>
const model = "siteSetting";

export default {
    name: "SiteSettingView",
    data() {
        return {
            page_title: "Site Settings",
            model: model,
            data: {},
            page_type: "view",
        };
    },
    methods: {
        getSystemModeName(val) {
            const match = this.$root.global?.systemmodes?.find((m) => m.value === val);
            return match ? match.name : val || "Production";
        },
        getSystemModeBadgeClass(val) {
            if (val === "live" || val === "production") return "bg-success";
            if (val === "staging") return "bg-warning text-dark";
            return "bg-secondary";
        },
        getLabelPresetName(val) {
            if (!val) return '4" × 2" (Standard Barcode Tag)';
            const match = this.$root.global?.label_presets?.find((p) => p.value === val);
            return match ? match.name : val;
        },
    },
    created() {
        this.$root.spinner = true;
        axios
            .get(this.model)
            .then((res) => {
                this.data = res.data || {};
            })
            .catch((error) => {
                console.log(error);
                this.$toast("Something went wrong");
                return false;
            })
            .finally(() => {
                this.$root.spinner = false;
            });
    },
};
</script>

<style scoped>
.site-settings-view {
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

/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, rgb(17, 44, 70) 0%, #1e3a5f 100%);
    border-radius: 8px;
}

.store-logo-frame {
    width: 140px;
    height: 65px;
    background-color: #ffffff;
}

/* Section Card */
.section-card {
    border-radius: 8px;
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.section-card:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06) !important;
}

.section-icon-box {
    width: 34px;
    height: 34px;
    min-width: 34px;
    font-size: 15px;
}

/* Specification Table */
.custom-spec-table tr td {
    padding: 11px 16px;
    font-size: 13px;
}

.custom-spec-table .spec-label {
    width: 40%;
    color: #64748b;
    font-weight: 500;
}

.custom-spec-table .spec-value {
    width: 60%;
    color: #1e293b;
}

.media-preview-card {
    transition: transform 0.15s ease-in-out;
}

.media-preview-card:hover {
    transform: translateY(-2px);
}
</style>
