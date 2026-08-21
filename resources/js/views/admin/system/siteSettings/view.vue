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
                                    <span class="badge bg-light bg-opacity-25 text-white" v-if="data?.shop_type === 'clothing'">
                                        <i class="fas fa-tshirt me-1"></i> Clothing & Fashion
                                    </span>
                                    <span class="badge bg-light bg-opacity-25 text-white" v-else-if="data?.shop_type === 'electronics'">
                                        <i class="fas fa-tv me-1"></i> Electronics & Gadgets
                                    </span>
                                    <span class="badge bg-light bg-opacity-25 text-white" v-else>
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
                                        <i class="fas fa-server me-1"></i> Mode: {{ $root.global.systemmodes?.find(m => m.value === data?.system_mode)?.name || 'Production' }}
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
                <!-- 🏢 1. Company & Store Profile -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Company & Store Profile</h6>
                                <small class="text-muted" style="font-size: 11px;">Basic company identifiers and contact addresses</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 custom-spec-table">
                                <tbody>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-heading me-2 text-muted"></i>Store Title</td>
                                        <td class="spec-value fw-bold text-dark">{{ data?.title || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-tag me-2 text-muted"></i>Short Title</td>
                                        <td class="spec-value font-monospace fw-semibold">{{ data?.short_title || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-envelope me-2 text-muted"></i>Contact Email</td>
                                        <td class="spec-value font-monospace text-primary">{{ data?.contact_email || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-paper-plane me-2 text-muted"></i>Feedback Email</td>
                                        <td class="spec-value font-monospace">{{ data?.feedback_email || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-phone-alt me-2 text-muted"></i>Primary Mobile</td>
                                        <td class="spec-value font-monospace fw-bold text-success">{{ data?.mobile1 || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-phone me-2 text-muted"></i>Secondary Mobile</td>
                                        <td class="spec-value font-monospace">{{ data?.mobile2 || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="spec-label"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Main Store Address</td>
                                        <td class="spec-value">{{ data?.address || 'N/A' }}</td>
                                    </tr>
                                    <tr v-if="data?.address_two">
                                        <td class="spec-label"><i class="fas fa-map-pin me-2 text-muted"></i>Secondary Address</td>
                                        <td class="spec-value">{{ data?.address_two }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 🖼️ 2. Brand Media & Logos -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-images"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Brand Assets & Media</h6>
                                <small class="text-muted" style="font-size: 11px;">Primary logos, invoice headers, and browser icons</small>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <!-- Main Logo -->
                                <div class="col-md-4 col-sm-6 text-center">
                                    <div class="media-preview-card p-3 rounded border bg-light h-100 d-flex flex-column align-items-center justify-content-between">
                                        <div class="media-img-container mb-2 d-flex align-items-center justify-content-center bg-white rounded p-2 w-100 border" style="height: 90px;">
                                            <img
                                                :src="data?.logo_one === 'no_server_image' ? emptyServerImage() : data?.logo_one || emptyImage()"
                                                alt="Main Logo"
                                                class="img-fluid"
                                                style="max-height: 70px; object-fit: contain;"
                                            />
                                        </div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark small">Main Logo</div>
                                            <span class="badge bg-light text-muted border mt-1" style="font-size: 10px;">Primary Header</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Small Logo -->
                                <div class="col-md-4 col-sm-6 text-center">
                                    <div class="media-preview-card p-3 rounded border bg-light h-100 d-flex flex-column align-items-center justify-content-between">
                                        <div class="media-img-container mb-2 d-flex align-items-center justify-content-center bg-white rounded p-2 w-100 border" style="height: 90px;">
                                            <img
                                                :src="data?.logo_small_one === 'no_server_image' ? emptyServerImage() : data?.logo_small_one || emptyImage()"
                                                alt="Small Logo"
                                                class="img-fluid"
                                                style="max-height: 70px; object-fit: contain;"
                                            />
                                        </div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark small">Small Logo</div>
                                            <span class="badge bg-light text-muted border mt-1" style="font-size: 10px;">Sidebar & Collapsed</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Favicon -->
                                <div class="col-md-4 col-sm-6 text-center">
                                    <div class="media-preview-card p-3 rounded border bg-light h-100 d-flex flex-column align-items-center justify-content-between">
                                        <div class="media-img-container mb-2 d-flex align-items-center justify-content-center bg-white rounded p-2 w-100 border" style="height: 90px;">
                                            <img
                                                :src="data?.favicon === 'no_server_image' ? emptyServerImage() : data?.favicon || emptyImage()"
                                                alt="Favicon"
                                                class="img-fluid"
                                                style="max-height: 48px; max-width: 48px; object-fit: contain;"
                                            />
                                        </div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark small">Favicon</div>
                                            <span class="badge bg-light text-muted border mt-1" style="font-size: 10px;">Browser Tab Icon</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🎁 3. Customer Loyalty Points & Rewards -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon-box bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Coupon & Customer Loyalty Points</h6>
                                    <small class="text-muted" style="font-size: 11px;">Points accumulation and discount redemption rules</small>
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
                                        <td class="spec-label"><i class="fas fa-coins me-2 text-muted"></i>Earning Rate</td>
                                        <td class="spec-value font-monospace fw-bold text-success">
                                            1 Tk Purchase = {{ data?.point_earn_rate || 1 }} Point(s)
                                        </td>
                                    </tr>
                                    <tr v-if="data?.coupon_enabled">
                                        <td class="spec-label"><i class="fas fa-hand-holding-usd me-2 text-muted"></i>Redemption Rate</td>
                                        <td class="spec-value font-monospace fw-bold text-primary">
                                            {{ data?.point_redeem_rate || 10 }} Points = 1 Tk Discount
                                        </td>
                                    </tr>
                                    <tr v-if="data?.coupon_enabled">
                                        <td class="spec-label"><i class="fas fa-lock me-2 text-muted"></i>Minimum Redeem Threshold</td>
                                        <td class="spec-value font-monospace">
                                            {{ data?.min_points_to_redeem || 10 }} Points
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-if="data?.coupon_enabled" class="p-3 bg-light m-3 rounded border">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle text-primary fs-5"></i>
                                    <div>
                                        <strong>Live Rule:</strong> Spending <strong>Tk. 1,000</strong> earns <strong>{{ (1000 * (data?.point_earn_rate || 1)).toLocaleString() }} points</strong>, which yields <strong>Tk. {{ ((1000 * (data?.point_earn_rate || 1)) / (data?.point_redeem_rate || 10)).toFixed(2) }}</strong> discount.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🖨️ 4. Printer & Print Layout Setup -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon-box bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center">
                                    <i class="fas fa-print"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Printer & Paper Size Setup</h6>
                                    <small class="text-muted" style="font-size: 11px;">Default hardware print format for POS & warranty tickets</small>
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
                                        <td class="spec-label"><i class="fas fa-check-double me-2 text-muted"></i>Application Targets</td>
                                        <td class="spec-value">
                                            <span class="badge bg-light text-dark border me-1">POS Sales Checkout</span>
                                            <span class="badge bg-light text-dark border">Warranty Claim Slips</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="p-3 bg-light m-3 rounded border">
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <i class="fas fa-info-circle text-primary fs-5"></i>
                                    <div>
                                        <strong>Auto Print Behavior:</strong> Completing a POS sale or printing a Warranty Claim slip will automatically render using
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

                <!-- 💳 5. Banking, Invoicing & Tax Details -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card border-0 shadow-sm h-100 section-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-university"></i>
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
                                        <td class="spec-value fw-semibold">{{ data?.bank_name || 'N/A' }}</td>
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

                <!-- 🕒 5. System Meta & Timestamps -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small">
                            <div class="d-flex align-items-center gap-3">
                                <span><i class="far fa-calendar-plus me-1 text-primary"></i><strong>Created:</strong> {{ enFormat(data?.created_at) || 'N/A' }}</span>
                                <span><i class="far fa-calendar-check me-1 text-success"></i><strong>Last Updated:</strong> {{ enFormat(data?.updated_at) || 'N/A' }}</span>
                            </div>
                            <div class="font-monospace">
                                <i class="fas fa-shield-alt me-1 text-theme"></i>QPOS Enterprise Configuration
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
    created() {
        this.$root.spinner = true;
        axios
            .get(this.model)
            .then((res) => {
                this.data = res.data;
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
