<template>
    <create-form @onSubmit="submit">
        <div class="row g-3 site-settings-edit">
            <!-- 🏢 1. General Store & Identity Profile -->
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100 form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Store & Brand Identity</h6>
                            <small class="text-muted" style="font-size: 11px;">Primary naming and contact information</small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <Input title="Store Title (প্রতিষ্ঠানের নাম)" field="data.title" v-model="data.title" :req="true" col="12" placeholder="e.g. QPOS Clothing" />
                            </div>
                            <div class="col-md-6">
                                <Input title="Short Title (সংক্ষিপ্ত নাম)" field="data.short_title" v-model="data.short_title" :req="true" col="12" placeholder="e.g. QPOS" />
                            </div>
                            <div class="col-md-6">
                                <Input title="Contact Email" field="data.contact_email" v-model="data.contact_email" type="email" :req="false" col="12" placeholder="info@example.com" />
                            </div>
                            <div class="col-md-6">
                                <Input title="Feedback Email" field="data.feedback_email" v-model="data.feedback_email" type="email" :req="false" col="12" placeholder="support@example.com" />
                            </div>
                            <div class="col-md-6">
                                <x-tel-input title="Primary Mobile" field="data.mobile1" v-model="data.mobile1" @phoneValidate="x_tel_validates.mobile1 = $event" :req="false" col="12" />
                            </div>
                            <div class="col-md-6">
                                <x-tel-input title="Secondary Mobile" field="data.mobile2" v-model="data.mobile2" @phoneValidate="x_tel_validates.mobile2 = $event" :req="false" col="12" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⚙️ 2. POS System & Shop Type Configuration -->
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100 form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">System & POS Configuration</h6>
                            <small class="text-muted" style="font-size: 11px;">Currency, system environment and business workflow mode</small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <Select title="Default Currency" v-model="data.default_currency_id"
                                    field="data.default_currency_id" col="12" label="short_name" :reduce="(obj) => obj.id"
                                    :options="$root.global.currencies" placeholder="--Select Currency--" :closeOnSelect="true"
                                    :required="true" />
                            </div>
                            <div class="col-md-6">
                                <Select title="System Mode" v-model="data.system_mode" field="data.system_mode" col="12"
                                    label="name" :reduce="(obj) => obj.value" :options="$root.global.systemmodes"
                                    placeholder="--Select Mode--" :closeOnSelect="true" :required="true" />
                            </div>

                            <!-- Shop Type Selector -->
                            <div class="col-12">
                                <label class="form-label fw-bold small text-dark mb-2">
                                    <i class="fas fa-store text-theme me-1"></i> Shop Type / Business Category (দোকানের ধরন):
                                </label>
                                <div class="row g-2">
                                    <!-- Clothing -->
                                    <div class="col-md-4">
                                        <div class="shop-type-option p-2 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'clothing' }"
                                            @click="data.shop_type = 'clothing'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopClothing" value="clothing" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopClothing">
                                                    <div class="fw-bold small"><i class="fas fa-tshirt text-info me-1"></i> Clothing & Fashion</div>
                                                    <div class="text-muted" style="font-size: 11px;">Color & Size variants</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Electronics -->
                                    <div class="col-md-4">
                                        <div class="shop-type-option p-2 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'electronics' }"
                                            @click="data.shop_type = 'electronics'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopElectronics" value="electronics" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopElectronics">
                                                    <div class="fw-bold small"><i class="fas fa-tv text-primary me-1"></i> Electronics</div>
                                                    <div class="text-muted" style="font-size: 11px;">Warranty & Serial tracking</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- General Retail -->
                                    <div class="col-md-4">
                                        <div class="shop-type-option p-2 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'others' }"
                                            @click="data.shop_type = 'others'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopOthers" value="others" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopOthers">
                                                    <div class="fw-bold small"><i class="fas fa-boxes text-secondary me-1"></i> General Retail</div>
                                                    <div class="text-muted" style="font-size: 11px;">Standard inventory</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🎁 3. Customer Loyalty & Coupon Reward Points -->
            <div class="col-12">
                <div class="card border-0 shadow-sm form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon-box bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Customer Loyalty & Reward Points Program</h6>
                                <small class="text-muted" style="font-size: 11px;">Points accumulation on purchase and discount conversions</small>
                            </div>
                        </div>
                        <div class="form-check form-switch m-0 p-0 d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" id="couponSwitchEdit"
                                v-model="data.coupon_enabled" :true-value="1" :false-value="0"
                                style="cursor: pointer; transform: scale(1.2);">
                            <label class="form-check-label fw-bold text-dark cursor-pointer small" for="couponSwitchEdit">
                                {{ data.coupon_enabled ? 'Enabled' : 'Disabled' }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-3" v-if="data.coupon_enabled">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Earning Rate (১ টাকা ক্রয়ে পয়েন্ট):</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold small">1 Tk =</span>
                                    <input type="number" step="0.01" min="0" class="form-control fw-bold font-monospace" v-model.number="data.point_earn_rate" placeholder="1.00">
                                    <span class="input-group-text bg-light small">Points</span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">1 Tk purchase earns 1 Point</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Redeem Rate (পয়েন্ট থেকে টাকা কনভার্সন):</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" class="form-control fw-bold font-monospace" v-model.number="data.point_redeem_rate" placeholder="10.00">
                                    <span class="input-group-text bg-light fw-bold small">Points = 1 Tk</span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">10 Points = 1 Tk discount</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Minimum Points to Redeem:</label>
                                <input type="number" min="0" class="form-control fw-bold font-monospace" v-model.number="data.min_points_to_redeem" placeholder="10">
                                <small class="text-muted" style="font-size: 11px;">Minimum balance needed for discount</small>
                            </div>

                            <!-- Live preview banner -->
                            <div class="col-12">
                                <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center gap-2 border-0 shadow-sm" style="font-size: 12px;">
                                    <i class="fas fa-calculator text-primary fs-5"></i>
                                    <div>
                                        <strong>Live Calculation:</strong> A customer purchasing <strong>Tk. 1,000</strong> worth of products will receive <strong>{{ Number(1000 * (data.point_earn_rate || 1)).toLocaleString() }} loyalty points</strong>.
                                        Redeeming <strong>{{ Number(1000 * (data.point_earn_rate || 1)).toLocaleString() }} points</strong> will grant <strong>Tk. {{ (Number(1000 * (data.point_earn_rate || 1)) / (data.point_redeem_rate || 10)).toFixed(2) }}</strong> invoice discount.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🖼️ 4. Brand Media & Logo Upload -->
            <div class="col-12">
                <div class="card border-0 shadow-sm form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                            <i class="fas fa-images"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Brand Media & Logos</h6>
                            <small class="text-muted" style="font-size: 11px;">Upload store logo, small sidebar logo, and browser favicon</small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- Main Logo -->
                            <div class="col-xl-4 col-md-6">
                                <div class="p-3 border rounded bg-light h-100">
                                    <File title="Main Logo (প্রাইমারি লোগো)" cropModalId="logo_crop_modal" field="data.original_logo" mime="img"
                                        fileClassName="file2" accept=".jpg, .jpeg, .png" :showCrop="true"
                                        :vHeight="$root.media_validators?.logo?.min_height ?? 100"
                                        :vWidth="$root.media_validators?.logo?.min_width ?? 300"
                                        :vSizeInKb="$root.media_validators?.logo?.max_size ?? 5000" col="12" />
                                    <GlobalCrop id="logo_crop_modal" field="data.original_logo"
                                        v-on:update:modelValue="data.original_logo = $event" :image="image.original_logo"
                                        :aspectRatio="{
                                            aspectRatio: ($root.media_validators?.logo?.min_width ?? 300) / ($root.media_validators?.logo?.min_height ?? 100),
                                        }"
                                        :minWidth="$root.media_validators?.logo?.min_width ?? 300"
                                        :minHeight="$root.media_validators?.logo?.min_height ?? 100"></GlobalCrop>
                                </div>
                            </div>

                            <!-- Small Logo -->
                            <div class="col-xl-4 col-md-6">
                                <div class="p-3 border rounded bg-light h-100">
                                    <File title="Small Logo (সংক্ষিপ্ত লোগো)" cropModalId="logo_small_crop_modal"
                                        field="data.original_logo_small" mime="img" fileClassName="file2"
                                        accept=".jpg, .jpeg, .png" :showCrop="true"
                                        :vHeight="$root.media_validators?.logo_small?.min_height ?? 100"
                                        :vWidth="$root.media_validators?.logo_small?.min_width ?? 300"
                                        :vSizeInKb="$root.media_validators?.logo_small?.max_size ?? 5000" col="12" />
                                    <GlobalCrop id="logo_small_crop_modal" field="data.original_logo_small"
                                        v-on:update:modelValue="data.original_logo_small = $event" :image="image.original_logo_small"
                                        :aspectRatio="{
                                            aspectRatio: ($root.media_validators?.logo_small?.min_width ?? 300) / ($root.media_validators?.logo_small?.min_height ?? 100),
                                        }"
                                        :minWidth="$root.media_validators?.logo_small?.min_width ?? 300"
                                        :minHeight="$root.media_validators?.logo_small?.min_height ?? 100"></GlobalCrop>
                                </div>
                            </div>

                            <!-- Favicon -->
                            <div class="col-xl-4 col-md-6">
                                <div class="p-3 border rounded bg-light h-100">
                                    <File title="Favicon (ট্যাব আইকন)" field="data.favicon" mime="img" fileClassName="file3"
                                        vHeight="50" vWidth="50" vSizeInKb="300" :deleteButton="false" col="12" :req="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📍 5. Store Address & Physical Location -->
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100 form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Store Addresses & Map Links</h6>
                            <small class="text-muted" style="font-size: 11px;">Primary and secondary outlet locations</small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <Textarea title="Primary Store Address (মূল ঠিকানা)" field="data.address" v-model="data.address" :req="false" col="12" rows="2" placeholder="Street, City, Post Code" />
                            </div>
                            <div class="col-12">
                                <Textarea title="Primary Google Maps Embed Link (ঐচ্ছিক)" field="data.map" v-model="data.map" :req="false" col="12" rows="2" placeholder="https://maps.google.com/..." />
                            </div>
                            <div class="col-12">
                                <Textarea title="Secondary Address (শাখা ঠিকানা - ঐচ্ছিক)" field="data.address_two" v-model="data.address_two" :req="false" col="12" rows="2" placeholder="Branch Address" />
                            </div>
                            <div class="col-12">
                                <Textarea title="Secondary Google Maps Link (ঐচ্ছিক)" field="data.map_two" v-model="data.map_two" :req="false" col="12" rows="2" placeholder="https://maps.google.com/..." />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💳 6. Banking, Invoicing & Tax Info -->
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100 form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                        <div class="section-icon-box theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Banking, Invoicing & Tax Details</h6>
                            <small class="text-muted" style="font-size: 11px;">Invoice footer banking credentials and tax numbers</small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <Input title="VAT / BIN Registration No" field="data.vat_no" v-model="data.vat_no" type="text" :req="false" col="12" placeholder="e.g. 001234567-0101" />
                            </div>
                            <div class="col-md-6">
                                <Input title="HS Code" field="data.hs_code" v-model="data.hs_code" type="text" :req="false" col="12" placeholder="e.g. 6109.10.00" />
                            </div>
                            <div class="col-md-6">
                                <Input title="SWIFT Code" field="data.swift_code" v-model="data.swift_code" type="text" :req="false" col="12" placeholder="e.g. DBBLBDDH" />
                            </div>
                            <div class="col-md-6">
                                <Input title="Bank Name" field="data.bank_name" v-model="data.bank_name" type="text" :req="false" col="12" placeholder="e.g. Dutch-Bangla Bank" />
                            </div>
                            <div class="col-md-6">
                                <Input title="Branch Name" field="data.branch_name" v-model="data.branch_name" type="text" :req="false" col="12" placeholder="e.g. Gulshan Branch" />
                            </div>
                            <div class="col-md-6">
                                <Input title="Account Number" field="data.account_number" v-model="data.account_number" type="text" :req="false" col="12" placeholder="e.g. 115.120.98765" />
                            </div>
                            <div class="col-md-12">
                                <Input title="Routing Number" field="data.routing_number" v-model="data.routing_number" type="text" :req="false" col="12" placeholder="e.g. 090271234" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </create-form>
</template>

<script>
const model = "siteSetting";
import { mapMutations, mapState } from "vuex";

export default {
    name: "SiteSettingsEdit",
    computed: {
        ...mapState("setting", ["colors"]),
    },
    data() {
        return {
            page_title: "Site Settings Edit",
            model: model,
            data: {
                logo: "",
                logo_small: "",
                favicon: "",
                default_currency_id: 1,
                shop_type: "clothing",
                coupon_enabled: 0,
                point_earn_rate: 1,
                point_redeem_rate: 10,
                min_points_to_redeem: 10,
            },
            image: {},
            x_tel_validates: {},
        };
    },

    provide() {
        return {
            validate: this.validation,
            data: () => this.data,
            image: this.image,
        };
    },
    methods: {
        submit: function (e) {
            this.$validate().then((res) => {
                const error = this.validation.countErrors();
                if (error > 0) {
                    console.log(this.validation.allErrors());
                    this.$toast(
                        "You need to fill " +
                        error +
                        " more empty mandatory fields",
                        "warning"
                    );
                    return false;
                }

                if (res) {
                    var form = document.getElementById("form");
                    var formData = new FormData(form);
                    formData.append("logo_base64", this.data.original_logo || "");
                    formData.append("system_mode", this.data.system_mode || "live");
                    formData.append("shop_type", this.data.shop_type || "clothing");
                    formData.append("default_currency_id", this.data.default_currency_id);
                    formData.append("coupon_enabled", this.data.coupon_enabled ? 1 : 0);
                    formData.append("point_earn_rate", this.data.point_earn_rate ?? 1);
                    formData.append("point_redeem_rate", this.data.point_redeem_rate ?? 10);
                    formData.append("min_points_to_redeem", this.data.min_points_to_redeem ?? 10);
                    formData.append(
                        "logo_small_base64",
                        this.data.original_logo_small || ""
                    );
                    formData.append(
                        "logo_resize_value",
                        this.$root.media_validators?.logo?.resize_value ?? ""
                    );
                    formData.append(
                        "logo_small_resize_value",
                        this.$root.media_validators?.logo_small?.resize_value ?? ""
                    );
                    this.store(this.model, formData);
                }
            });
        },

        getSiteSetting() {
            this.$root.submit = true;
            axios
                .get(`${this.model}`)
                .then((res) => {
                    this.data = res.data;
                })
                .catch((error) => {
                    this.$toast(
                        error.response?.data?.message ?? "Something went wrong!",
                        "error"
                    );
                    console.log(error);
                })
                .finally(() => {
                    this.$root.submit = false;
                });
        },

        store(model, formData) {
            this.$root.submit = true;
            axios
                .post(`/${model}`, formData)
                .then((response) => {
                    this.$toast("Site Settings updated successfully", "success");
                    this.$router.push({ name: "siteSetting.show" });
                })
                .catch((error) => {
                    this.$toast("Error updating site settings", "error");
                    console.log(error);
                })
                .finally(() => {
                    this.$root.submit = false;
                });
        }
    },
    created() {
        this.getSiteSetting();
        this.getMediaValidators("SiteSetting");
    },
    validators: {
        "data.title": function (value = null) {
            return Validator.value(value)
                .maxLength(191)
                .required("Title is required");
        },
        "data.short_title": function (value = null) {
            return Validator.value(value)
                .maxLength(191)
                .required("Short Title is required");
        },
        "data.favicon": function (value = null) {
            return Validator.value(value).required("Favicon is required");
        },
        "data.contact_email": function (value = null) {
            return Validator.value(value).email();
        },
        "data.feedback_email": function (value = null) {
            return Validator.value(value).email();
        },
        "data.mobile1, x_tel_validates.mobile1": function (
            value = null,
            xMobileValue = {}
        ) {
            const isValidMobile = this.isValidXTelMobile(value, xMobileValue);
            return Validator.value(value).custom(function () {
                if (isValidMobile !== true) {
                    return "Invalid primary mobile number";
                }
            });
        },
        "data.mobile2, x_tel_validates.mobile2": function (
            value = null,
            xMobileValue = {}
        ) {
            const isValidMobile = this.isValidXTelMobile(value, xMobileValue);
            return Validator.value(value).custom(function () {
                if (isValidMobile !== true) {
                    return "Invalid secondary mobile number";
                }
            });
        },
    },
};
</script>

<style scoped>
.site-settings-edit {
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

.form-section-card {
    border-radius: 8px;
    transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-section-card:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06) !important;
}

.section-icon-box {
    width: 34px;
    height: 34px;
    min-width: 34px;
    font-size: 15px;
}

/* Shop Type Options */
.shop-type-option {
    transition: all 0.2s ease-in-out;
    background-color: #f8fafc;
    border: 2px solid #e2e8f0 !important;
}

.shop-type-option:hover {
    border-color: rgb(17, 44, 70) !important;
    background-color: rgba(17, 44, 70, 0.03);
}

.shop-type-option.active-shop-type {
    border-color: rgb(17, 44, 70) !important;
    background-color: rgba(17, 44, 70, 0.08);
    box-shadow: 0 2px 8px rgba(17, 44, 70, 0.15);
}

.image_upload_box .upload_box .img {
    width: 100%;
    height: 100%;
    display: flex;
}

.image_upload_box .upload_box img {
    width: 100% !important;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    margin: auto;
}
</style>
