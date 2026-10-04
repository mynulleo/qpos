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
                            <div class="col-12 border-top pt-2">
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
                                <div class="row g-3">
                                    <!-- Departmental Store / Grocery -->
                                    <div class="col-12 col-md-6">
                                        <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'grocery' || data.shop_type === 'departmental' }"
                                            @click="data.shop_type = 'grocery'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopGrocery" value="grocery" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopGrocery">
                                                    <div class="fw-bold small"><i class="fas fa-shopping-basket text-success me-1"></i> Grocery & Departmental (মুদি / ডিপার্টমেন্টাল)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Fast POS (সরাসরি কার্টে যোগ, পপআপ ছাড়া ফাস্ট সেল)</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Clothing -->
                                    <div class="col-12 col-md-6">
                                        <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'clothing' }"
                                            @click="data.shop_type = 'clothing'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopClothing" value="clothing" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopClothing">
                                                    <div class="fw-bold small"><i class="fas fa-tshirt text-info me-1"></i> Clothing & Fashion (গার্মেন্টস ও পোশাক)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Color & Size variants (কালার ও সাইজ ভেরিয়েন্ট)</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Electronics -->
                                    <div class="col-12 col-md-6">
                                        <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'electronics' }"
                                            @click="data.shop_type = 'electronics'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopElectronics" value="electronics" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopElectronics">
                                                    <div class="fw-bold small"><i class="fas fa-tv text-primary me-1"></i> Electronics & Gadgets (ইলেকট্রনিক্স)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Warranty & Serial tracking (ওয়ারেন্টি ও সিরিয়াল ট্র্যাকিং)</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- General Retail -->
                                    <div class="col-12 col-md-6">
                                        <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.shop_type === 'others' }"
                                            @click="data.shop_type = 'others'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="shopOthers" value="others" v-model="data.shop_type">
                                                <label class="form-check-label cursor-pointer text-dark" for="shopOthers">
                                                    <div class="fw-bold small"><i class="fas fa-boxes text-secondary me-1"></i> General Retail (সাধারণ রিটেইল)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Standard inventory (স্ট্যান্ডার্ড ইনভেন্টরি)</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sale Nature Selector -->
                            <div class="col-12 border-top pt-3">
                                <label class="form-label fw-bold small text-dark mb-2">
                                    <i class="fas fa-tags text-theme me-1"></i> Sale Nature (বিক্রয়ের ধরণ / প্রকৃতি):
                                </label>
                                <div class="row g-2">
                                    <!-- Retail -->
                                    <div class="col-12 col-md-4">
                                        <div class="shop-type-option p-2 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.sale_nature === 'retail' }"
                                            @click="data.sale_nature = 'retail'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="natureRetail" value="retail" v-model="data.sale_nature">
                                                <label class="form-check-label cursor-pointer text-dark w-100" for="natureRetail">
                                                    <div class="fw-bold small text-primary"><i class="fas fa-shopping-bag me-1"></i> Retail (খুচরা)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Direct consumer sales</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Whole Sale -->
                                    <div class="col-12 col-md-4">
                                        <div class="shop-type-option p-2 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.sale_nature === 'wholesale' }"
                                            @click="data.sale_nature = 'wholesale'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="natureWholesale" value="wholesale" v-model="data.sale_nature">
                                                <label class="form-check-label cursor-pointer text-dark w-100" for="natureWholesale">
                                                    <div class="fw-bold small text-success"><i class="fas fa-warehouse me-1"></i> Whole Sale (পাইকারি)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Bulk dealer & agent sales</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Both -->
                                    <div class="col-12 col-md-4">
                                        <div class="shop-type-option p-2 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': !data.sale_nature || data.sale_nature === 'both' }"
                                            @click="data.sale_nature = 'both'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="natureBoth" value="both" v-model="data.sale_nature">
                                                <label class="form-check-label cursor-pointer text-dark w-100" for="natureBoth">
                                                    <div class="fw-bold small text-dark"><i class="fas fa-layer-group text-warning me-1"></i> Both (উভয়ই)</div>
                                                    <div class="text-muted" style="font-size: 11px;">Retail & wholesale together</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Default VAT Rate Configuration -->
                            <div class="col-12 border-top pt-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-dark mb-1 d-flex align-items-center gap-1">
                                            <i class="fas fa-percent text-theme"></i> Default VAT Rate (%) (ডিফল্ট ভ্যাট শতকরা হার):
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control fw-bold font-monospace"
                                                v-model.number="data.default_vat" placeholder="0.00">
                                            <span class="input-group-text bg-light fw-bold">%</span>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            <i class="fas fa-info-circle text-primary me-1"></i>
                                            Set <strong>0</strong> for no default VAT. When &gt; 0, POS calculates this % on invoice total automatically.
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-2 px-3 rounded border" :class="Number(data.default_vat) > 0 ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light'">
                                            <div class="small fw-bold text-dark d-flex align-items-center justify-content-between mb-1">
                                                <span><i class="fas fa-calculator me-1 text-primary"></i> POS Calculation Preview:</span>
                                                <span class="badge" :class="Number(data.default_vat) > 0 ? 'bg-primary' : 'bg-secondary'">
                                                    {{ Number(data.default_vat) > 0 ? data.default_vat + '% Active' : '0% (No VAT)' }}
                                                </span>
                                            </div>
                                            <div class="text-muted font-monospace" style="font-size: 11.5px;" v-if="Number(data.default_vat) > 0">
                                                On <strong>Tk. 1,000</strong> sale &rarr; VAT = <strong>Tk. {{ ((1000 * Number(data.default_vat)) / 100).toFixed(2) }}</strong> (Net: Tk. {{ (1000 + (1000 * Number(data.default_vat)) / 100).toFixed(2) }})
                                            </div>
                                            <div class="text-muted" style="font-size: 11.5px;" v-else>
                                                VAT calculation will be <strong>Tk. 0.00</strong> on POS sales by default. Cashier can toggle switch anytime.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 🧾 Invoice Prefix & POS Terms Switch -->
                            <div class="col-12 border-top pt-3">
                                <div class="row g-3">
                                    <!-- 1. Invoice Prefix -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-dark mb-1 d-flex align-items-center gap-1">
                                            <i class="fas fa-receipt text-theme"></i> Invoice Prefix (ইনভয়েস প্রিফিক্স):
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-monospace fw-bold text-uppercase"
                                                v-model="data.invoice_prefix" placeholder="e.g. POS, INV, QPOS" maxlength="20">
                                            <span class="input-group-text bg-light text-muted font-monospace small">
                                                {{ (data.invoice_prefix || 'POS') }}-20261001-0001
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            <i class="fas fa-info-circle text-primary me-1"></i>
                                            ইনভয়েস নম্বরের শুরুতে এই প্রিফিক্সটি ডাইনামিক ভাবে বসবে (যেমনঃ <strong>{{ (data.invoice_prefix || 'POS') }}-20261001-0001</strong>)।
                                        </small>
                                    </div>

                                    <!-- 2. POS Terminal Terms & Condition Toggle Switch -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-dark mb-1 d-flex align-items-center gap-1">
                                            <i class="fas fa-file-contract text-theme"></i> Sale Terminal Terms & Conditions (টার্মিনালে শর্তাবলী প্রদর্শন):
                                        </label>
                                        <div class="p-2 px-3 border rounded bg-light d-flex align-items-center justify-content-between h-auto">
                                            <div>
                                                <span class="small fw-bold text-dark d-block">Show in Sale Terminal / POS</span>
                                                <small class="text-muted" style="font-size: 10.5px;">টার্মিনালে শর্তাবলী এডিট ও প্রিন্ট সুবিধা</small>
                                            </div>
                                            <div class="form-check form-switch m-0 p-0">
                                                <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch"
                                                    id="siteShowPosTermsSwitch" v-model="data.show_pos_terms" :true-value="1" :false-value="0"
                                                    style="transform: scale(1.3); cursor: pointer;">
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            <i class="fas fa-info-circle text-primary me-1"></i>
                                            সক্রিয় থাকলে পিওএস টার্মিনালে চেকআউটের সময় প্রতিটি শর্তের জন্য চেকবক্স ও ইনপুট বক্স শো করবে।
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🏛️ 3. Organization Memberships & Associations -->
            <div class="col-12">
                <div class="card border-0 shadow-sm form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon-box bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-award"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark">Organization Memberships & Associations (অর্গানাইজেশন মেম্বারশিপ)</h6>
                                    <span class="badge bg-primary font-monospace" v-if="data.shop_type === 'electronics'">Electronics Feature</span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Add trade bodies and associations (e.g. BCS, BASIS, ECAB) with logos to display on invoice bills</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold d-flex align-items-center gap-1 shadow-sm px-3" @click="openMembershipModal('create')">
                            <i class="fas fa-plus-circle"></i> Add Membership (মেম্বারশিপ যোগ করুন)
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div v-if="memberships && memberships.length > 0" class="row g-3">
                            <div class="col-xl-4 col-md-6" v-for="(m, idx) in memberships" :key="idx">
                                <div class="p-3 border rounded bg-light h-100 position-relative membership-card d-flex align-items-center justify-content-between gap-3 shadow-xs">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Logo Frame -->
                                        <div class="bg-white p-1 rounded border d-flex align-items-center justify-content-center shadow-xs" style="width: 54px; height: 54px; min-width: 54px;">
                                            <img v-if="m.logo || m.logo_url" :src="m.logo_url || m.logo" class="img-fluid rounded" style="max-height: 44px; max-width: 44px; object-fit: contain;" alt="Logo" />
                                            <i v-else class="fas fa-building fs-4 text-muted opacity-50"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1 fs-6">{{ m.org_name }}</h6>
                                            <div class="form-check form-switch m-0 p-0 d-flex align-items-center gap-1">
                                                <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch"
                                                    :id="`invShow_${idx}`" v-model="m.show_in_invoice" :true-value="1" :false-value="0"
                                                    @change="toggleMembershipInvoice(idx)"
                                                    style="transform: scale(0.9);">
                                                <label class="form-check-label small cursor-pointer" :for="`invShow_${idx}`" :class="m.show_in_invoice ? 'text-success fw-bold' : 'text-muted'">
                                                    {{ m.show_in_invoice ? 'Show in Invoice' : 'Hidden in Invoice' }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" @click="openMembershipModal('edit', idx)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger py-1 px-2" @click="deleteMembership(idx)" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 bg-light rounded border border-dashed">
                            <i class="fas fa-award fs-2 text-muted opacity-25 mb-2"></i>
                            <p class="text-muted small mb-2">No organization memberships added yet (e.g. Bangladesh Computer Samity, BASIS, ECAB).</p>
                            <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm" @click="openMembershipModal('create')">
                                <i class="fas fa-plus me-1"></i> Add Organization Membership
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🖨️ 3. Printer & Print Paper Size Configuration -->
            <div class="col-12">
                <div class="card border-0 shadow-sm form-section-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon-box bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center">
                                <i class="fas fa-print"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Printer & Paper Size Setup (প্রিন্টার ও পেপার সাইজ)</h6>
                                <small class="text-muted" style="font-size: 11px;">Configure whether sales and warranty claims print on Thermal POS rolls or Normal (A4/A5) sheets</small>
                            </div>
                        </div>
                        <span class="badge font-monospace" :class="data.printer_type === 'thermal' ? 'bg-success' : 'bg-primary'">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ data.printer_type === 'thermal' ? 'Thermal Receipt (' + (data.thermal_paper_size || '80mm') + ')' : 'Normal Printer (' + (data.normal_paper_size || 'A4') + ')' }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <!-- 1. Select Printer Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-2">
                                    <i class="fas fa-cog text-theme me-1"></i> Printer Type (প্রিন্টারের ধরণ):
                                </label>
                                <div class="row g-2">
                                    <!-- Thermal Printer -->
                                    <div class="col-6">
                                        <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.printer_type === 'thermal' }"
                                            @click="data.printer_type = 'thermal'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="printerThermal" value="thermal" v-model="data.printer_type">
                                                <label class="form-check-label cursor-pointer text-dark w-100" for="printerThermal">
                                                    <div class="fw-bold small text-success">
                                                        <i class="fas fa-receipt me-1"></i> Thermal Printer
                                                    </div>
                                                    <div class="text-muted" style="font-size: 11px;">POS Receipt Roll (থার্মাল প্রিন্টার)</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Normal Printer -->
                                    <div class="col-6">
                                        <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                            :class="{ 'active-shop-type': data.printer_type === 'normal' }"
                                            @click="data.printer_type = 'normal'">
                                            <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                <input class="form-check-input ms-1 mt-1" type="radio" id="printerNormal" value="normal" v-model="data.printer_type">
                                                <label class="form-check-label cursor-pointer text-dark w-100" for="printerNormal">
                                                    <div class="fw-bold small text-primary">
                                                        <i class="fas fa-print me-1"></i> Normal Printer
                                                    </div>
                                                    <div class="text-muted" style="font-size: 11px;">Laser / Inkjet (সাধারণ প্রিন্টার)</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Select Paper Size based on selected printer type -->
                            <div class="col-md-6">
                                <!-- When Thermal is Selected -->
                                <div v-if="data.printer_type === 'thermal'">
                                    <label class="form-label fw-bold small text-dark mb-2">
                                        <i class="fas fa-scroll text-success me-1"></i> Thermal Paper Width (রোল সাইজ):
                                    </label>
                                    <div class="row g-2">
                                        <!-- 80mm -->
                                        <div class="col-6">
                                            <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                                :class="{ 'active-shop-type': data.thermal_paper_size === '80mm' }"
                                                @click="data.thermal_paper_size = '80mm'">
                                                <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                    <input class="form-check-input ms-1 mt-1" type="radio" id="paper80mm" value="80mm" v-model="data.thermal_paper_size">
                                                    <label class="form-check-label cursor-pointer text-dark w-100" for="paper80mm">
                                                        <div class="fw-bold small text-dark">
                                                            <i class="fas fa-file-invoice text-success me-1"></i> 80mm Roll (3")
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px;">Standard POS (৩ ইঞ্চি স্ট্যান্ডার্ড)</div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 60mm -->
                                        <div class="col-6">
                                            <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                                :class="{ 'active-shop-type': data.thermal_paper_size === '60mm' }"
                                                @click="data.thermal_paper_size = '60mm'">
                                                <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                    <input class="form-check-input ms-1 mt-1" type="radio" id="paper60mm" value="60mm" v-model="data.thermal_paper_size">
                                                    <label class="form-check-label cursor-pointer text-dark w-100" for="paper60mm">
                                                        <div class="fw-bold small text-dark">
                                                            <i class="fas fa-receipt text-info me-1"></i> 60mm / 58mm (2")
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px;">Compact POS (ছোট থার্মাল রোল)</div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- When Normal Printer is Selected -->
                                <div v-else>
                                    <label class="form-label fw-bold small text-dark mb-2">
                                        <i class="fas fa-file-alt text-primary me-1"></i> Invoice Paper Size (কাগজের সাইজ):
                                    </label>
                                    <div class="row g-2">
                                        <!-- A4 -->
                                        <div class="col-6">
                                            <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                                :class="{ 'active-shop-type': data.normal_paper_size === 'A4' }"
                                                @click="data.normal_paper_size = 'A4'">
                                                <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                    <input class="form-check-input ms-1 mt-1" type="radio" id="paperA4" value="A4" v-model="data.normal_paper_size">
                                                    <label class="form-check-label cursor-pointer text-dark w-100" for="paperA4">
                                                        <div class="fw-bold small text-dark">
                                                            <i class="fas fa-file-alt text-primary me-1"></i> A4 Size Paper
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px;">Full Page (ফুল সাইজ ইনভয়েস)</div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- A5 -->
                                        <div class="col-6">
                                            <div class="shop-type-option p-3 rounded border cursor-pointer h-100"
                                                :class="{ 'active-shop-type': data.normal_paper_size === 'A5' }"
                                                @click="data.normal_paper_size = 'A5'">
                                                <div class="form-check m-0 p-0 d-flex align-items-start gap-2">
                                                    <input class="form-check-input ms-1 mt-1" type="radio" id="paperA5" value="A5" v-model="data.normal_paper_size">
                                                    <label class="form-check-label cursor-pointer text-dark w-100" for="paperA5">
                                                        <div class="fw-bold small text-dark">
                                                            <i class="fas fa-file text-warning me-1"></i> A5 Size Paper
                                                        </div>
                                                        <div class="text-muted" style="font-size: 11px;">Half Page (হাফ সাইজ ইনভয়েস)</div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Default Barcode Label Size Preset -->
                            <div class="col-12 border-top pt-3 mt-3">
                                <label class="form-label fw-bold small text-dark mb-1 d-flex align-items-center gap-1">
                                    <i class="fas fa-barcode text-theme"></i> Default Barcode Label Size Preset (ডিফল্ট বারকোড লেবেল সাইজ):
                                </label>
                                <Select
                                    title="Default Label Preset"
                                    v-model="data.label_preset"
                                    field="data.label_preset"
                                    col="12"
                                    label="name"
                                    :reduce="(obj) => obj.value"
                                    :options="$root.global.label_presets || []"
                                    placeholder="--Select Default Barcode Label Size--"
                                    :closeOnSelect="true"
                                />
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    <i class="fas fa-info-circle text-primary me-1"></i> This preset will be selected automatically as the default size on the <strong>Barcode Label Printing</strong> page.
                                </small>
                            </div>

                            <!-- Live preview summary banner -->
                            <div class="col-12">
                                <div class="alert alert-light border py-2 px-3 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="font-size: 12px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-info-circle text-primary fs-5"></i>
                                        <div>
                                            <strong>Active Print Workflow:</strong>
                                            POS Sales & Warranty Claims will automatically print in
                                            <strong class="text-primary font-monospace" v-if="data.printer_type === 'thermal'">
                                                Thermal Receipt ({{ data.thermal_paper_size || '80mm' }})
                                            </strong>
                                            <strong class="text-primary font-monospace" v-else>
                                                Normal Invoice ({{ data.normal_paper_size || 'A4' }})
                                            </strong>
                                            format after submission.
                                        </div>
                                    </div>
                                    <span class="badge bg-secondary font-monospace">Auto Applied to POS & Warranty</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🎁 4. Customer Loyalty & Coupon Reward Points -->
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
            <div class="col-xl-6 col-lg-12">
                <div class="card border-0 shadow-sm h-100 form-section-card">
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
                            <div class="col-12">
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
                            <div class="col-md-6 col-12">
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
                            <div class="col-md-6 col-12">
                                <div class="p-3 border rounded bg-light h-100">
                                    <File title="Favicon (ট্যাব আইকন)" field="data.favicon" mime="img" fileClassName="file3"
                                        vHeight="50" vWidth="50" vSizeInKb="300" :deleteButton="false" col="12" :req="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💳 5. Banking, Invoicing & Tax Details -->
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
            <!-- 🏛️ Membership Add/Edit Modal (Bootstrap/Custom Dialog) -->
            <div v-if="showMembershipModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); z-index: 1060;" @click.self="closeMembershipModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg border-0 rounded-3">
                        <div class="modal-header bg-light py-3 border-bottom">
                            <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-award text-primary"></i>
                                {{ membershipModalMode === 'create' ? 'Add Organization Membership' : 'Edit Organization Membership' }}
                            </h6>
                            <button type="button" class="btn-close" @click="closeMembershipModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Organization Name -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-dark">
                                    Organization Name (সংস্থার নাম) <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" v-model.trim="membershipForm.org_name"
                                    placeholder="e.g. Bangladesh Computer Samity (BCS), BASIS, ECAB" />
                                <small class="text-muted" style="font-size: 11px;">Enter the official name of the trade association or board</small>
                            </div>

                            <!-- Logo Upload -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-dark">
                                    Organization Logo (লোগো)
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="border rounded p-1 bg-white d-flex align-items-center justify-content-center shadow-xs position-relative"
                                        style="width: 70px; height: 70px; min-width: 70px; background-color: #fafafa;">
                                        <img v-if="modalLogoPreview"
                                            :src="modalLogoPreview"
                                            class="img-fluid rounded"
                                            style="max-height: 60px; max-width: 60px; object-fit: contain;"
                                            alt="Preview" />
                                        <i v-else class="fas fa-image fs-3 text-muted opacity-50"></i>
                                        <button v-if="modalLogoPreview" type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                            style="width: 20px; height: 20px; transform: translate(30%, -30%); font-size: 10px;"
                                            @click="removeMembershipLogo" title="Remove Logo">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input ref="membershipFileInput" type="file" class="form-control form-control-sm" accept="image/*" @change="onMembershipLogoChange" />
                                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Recommended size: Square PNG or JPG with transparent background</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Show in Invoice Toggle -->
                            <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="small fw-bold text-dark d-block">Show in Invoice (ইনভয়েসে প্রদর্শন)</span>
                                    <small class="text-muted" style="font-size: 11px;">ইনভয়েস বিলের নিচে অর্গানাইজেশনের লোগো এবং নাম শো করবে</small>
                                </div>
                                <div class="form-check form-switch m-0 p-0">
                                    <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch"
                                        id="modalShowInInvoice" v-model="membershipForm.show_in_invoice" :true-value="1" :false-value="0"
                                        style="transform: scale(1.3); cursor: pointer;">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2 px-3 border-top d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-secondary" @click="closeMembershipModal">Cancel</button>
                            <button type="button" class="btn btn-sm btn-primary px-4 fw-bold" :disabled="!membershipForm.org_name" @click="saveMembershipModal">
                                <i class="fas fa-check me-1"></i> Save Membership
                            </button>
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
        modalLogoPreview() {
            return (
                this.membership_preview_blob ||
                this.membershipForm.preview_url ||
                this.membershipForm.logo_url ||
                this.membershipForm.logo ||
                ""
            );
        },
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
                sale_nature: "both",
                default_vat: 0,
                invoice_prefix: "POS",
                show_pos_terms: 0,
                printer_type: "thermal",
                normal_paper_size: "A4",
                thermal_paper_size: "80mm",
                label_preset: "4x2",
                address: "",
                address_two: "",
                map: "",
                map_two: "",
                coupon_enabled: 0,
                point_earn_rate: 1,
                point_redeem_rate: 10,
                min_points_to_redeem: 10,
            },
            memberships: [],
            showMembershipModal: false,
            membershipModalMode: "create",
            editingMembershipIndex: -1,
            membership_preview_blob: "",
            membershipForm: {
                id: null,
                org_name: "",
                logo: "",
                logo_url: "",
                preview_url: "",
                show_in_invoice: 1,
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
        // Membership Modal Handlers
        removeMembershipLogo() {
            this.membership_preview_blob = "";
            this.membershipForm.logo = "";
            this.membershipForm.logo_url = "";
            this.membershipForm.preview_url = "";
            if (this.$refs.membershipFileInput) {
                this.$refs.membershipFileInput.value = "";
            }
        },

        openMembershipModal(mode = "create", idx = -1) {
            this.membershipModalMode = mode;
            this.editingMembershipIndex = idx;
            this.membership_preview_blob = "";
            if (mode === "edit" && idx >= 0 && this.memberships[idx]) {
                const item = this.memberships[idx];
                const logoVal = item.logo_url || item.logo || "";
                this.membershipForm = {
                    id: item.id || null,
                    org_name: item.org_name || "",
                    logo: logoVal,
                    logo_url: logoVal,
                    preview_url: logoVal,
                    show_in_invoice: item.show_in_invoice !== undefined ? (item.show_in_invoice ? 1 : 0) : 1,
                };
                this.membership_preview_blob = logoVal;
            } else {
                this.membershipForm = {
                    id: null,
                    org_name: "",
                    logo: "",
                    logo_url: "",
                    preview_url: "",
                    show_in_invoice: 1,
                };
                this.membership_preview_blob = "";
            }
            if (this.$refs.membershipFileInput) {
                this.$refs.membershipFileInput.value = "";
            }
            this.showMembershipModal = true;
        },

        closeMembershipModal() {
            this.showMembershipModal = false;
            this.membership_preview_blob = "";
            this.membershipForm = {
                id: null,
                org_name: "",
                logo: "",
                logo_url: "",
                preview_url: "",
                show_in_invoice: 1,
            };
            this.editingMembershipIndex = -1;
            if (this.$refs.membershipFileInput) {
                this.$refs.membershipFileInput.value = "";
            }
        },

        onMembershipLogoChange(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            // 1. Instant zero-delay synchronous preview using Blob URL
            try {
                const blobUrl = URL.createObjectURL(file);
                this.membership_preview_blob = blobUrl;
                this.membershipForm.preview_url = blobUrl;
                this.membershipForm.logo_url = blobUrl;
            } catch (err) {
                console.error("Blob URL error", err);
            }

            // 2. Base64 conversion for payload persistence
            const reader = new FileReader();
            reader.onload = (event) => {
                const base64 = event.target.result;
                this.membershipForm.logo = base64;
                this.membershipForm.logo_url = base64;
                this.membershipForm.preview_url = base64;
                this.membership_preview_blob = base64;
            };
            reader.readAsDataURL(file);
        },

        saveMembershipModal() {
            if (!this.membershipForm.org_name) {
                this.$toast("Organization Name is required", "warning");
                return;
            }

            if (!Array.isArray(this.memberships)) {
                this.memberships = [];
            }

            const logoVal = this.membershipForm.logo || this.membershipForm.logo_url || this.membership_preview_blob || "";
            const payload = {
                id: this.membershipForm.id || null,
                org_name: this.membershipForm.org_name,
                logo: logoVal,
                logo_url: logoVal,
                show_in_invoice: this.membershipForm.show_in_invoice ? 1 : 0,
            };

            if (this.membershipModalMode === "edit" && this.editingMembershipIndex >= 0) {
                this.memberships[this.editingMembershipIndex] = payload;
                this.memberships = [...this.memberships];
            } else {
                this.memberships.push(payload);
            }

            this.closeMembershipModal();
            this.$toast("Membership saved successfully", "success");
        },

        deleteMembership(idx) {
            if (confirm("Are you sure you want to remove this organization membership?")) {
                this.memberships.splice(idx, 1);
                this.memberships = [...this.memberships];
                this.$toast("Membership removed", "info");
            }
        },

        toggleMembershipInvoice(idx) {
            // Local reactivity update
            if (this.memberships[idx]) {
                this.$toast(
                    this.memberships[idx].show_in_invoice ? "Will show in invoice bills" : "Hidden from invoice bills",
                    "info"
                );
            }
        },

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

                    // Ensure all fields from this.data are set in formData
                    for (const key in this.data) {
                        if (this.data[key] !== undefined && this.data[key] !== null) {
                            if (!formData.has(key)) {
                                formData.append(key, this.data[key]);
                            }
                        }
                    }

                    formData.set("title", this.data.title || "");
                    formData.set("short_title", this.data.short_title || "");
                    formData.set("contact_email", this.data.contact_email || "");
                    formData.set("feedback_email", this.data.feedback_email || "");
                    formData.set("mobile1", this.data.mobile1 || "");
                    formData.set("mobile2", this.data.mobile2 || "");
                    formData.set("address", this.data.address || "");
                    formData.set("address_two", this.data.address_two || "");
                    formData.set("map", this.data.map || "");
                    formData.set("map_two", this.data.map_two || "");
                    formData.set("system_mode", this.data.system_mode || "live");
                    formData.set("shop_type", this.data.shop_type || "clothing");
                    formData.set("sale_nature", this.data.sale_nature || "both");
                    formData.set("default_vat", this.data.default_vat ?? 0);
                    formData.set("invoice_prefix", this.data.invoice_prefix || "POS");
                    formData.set("show_pos_terms", this.data.show_pos_terms ? 1 : 0);
                    formData.set("memberships", JSON.stringify(this.memberships || []));
                    formData.set("printer_type", this.data.printer_type || "thermal");
                    formData.set("normal_paper_size", this.data.normal_paper_size || "A4");
                    formData.set("thermal_paper_size", this.data.thermal_paper_size || "80mm");
                    formData.set("label_preset", this.data.label_preset || "4x2");
                    formData.set("default_currency_id", this.data.default_currency_id || 1);
                    formData.set("coupon_enabled", this.data.coupon_enabled ? 1 : 0);
                    formData.set("point_earn_rate", this.data.point_earn_rate ?? 1);
                    formData.set("point_redeem_rate", this.data.point_redeem_rate ?? 10);
                    formData.set("min_points_to_redeem", this.data.min_points_to_redeem ?? 10);
                    formData.set("vat_no", this.data.vat_no || "");
                    formData.set("hs_code", this.data.hs_code || "");
                    formData.set("swift_code", this.data.swift_code || "");
                    formData.set("bank_name", this.data.bank_name || "");
                    formData.set("branch_name", this.data.branch_name || "");
                    formData.set("account_number", this.data.account_number || "");
                    formData.set("routing_number", this.data.routing_number || "");

                    formData.set("logo_base64", this.data.original_logo || "");
                    formData.set("logo_small_base64", this.data.original_logo_small || "");
                    formData.set("logo_resize_value", this.$root.media_validators?.logo?.resize_value ?? "");
                    formData.set("logo_small_resize_value", this.$root.media_validators?.logo_small?.resize_value ?? "");

                    this.store(this.model, formData);
                }
            });
        },

        getSiteSetting() {
            this.$root.submit = true;
            axios
                .get(`${this.model}`)
                .then((res) => {
                    this.data = res.data || {};
                    if (!this.data.sale_nature) this.data.sale_nature = "both";
                    if (this.data.default_vat === undefined || this.data.default_vat === null) this.data.default_vat = 0;
                    if (!this.data.invoice_prefix) this.data.invoice_prefix = "POS";
                    if (this.data.show_pos_terms === undefined || this.data.show_pos_terms === null) this.data.show_pos_terms = 0;
                    if (!this.data.printer_type) this.data.printer_type = "thermal";
                    if (!this.data.normal_paper_size) this.data.normal_paper_size = "A4";
                    if (!this.data.thermal_paper_size) this.data.thermal_paper_size = "80mm";
                    if (!this.data.label_preset) this.data.label_preset = "4x2";

                    // Initialize memberships array
                    let memList = this.data.memberships;
                    if (typeof memList === "string") {
                        try {
                            memList = JSON.parse(memList) || [];
                            if (typeof memList === "string") {
                                memList = JSON.parse(memList) || [];
                            }
                        } catch (e) {
                            memList = [];
                        }
                    }
                    this.memberships = Array.isArray(memList) ? memList : [];
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
                    if (this.$root.getInitializeSystems) {
                        this.$root.getInitializeSystems();
                    }
                    this.$router.push({ name: "siteSetting.show" });
                })
                .catch((error) => {
                    const msg = error.response?.data?.message || "Error updating site settings";
                    this.$toast(msg, "error");
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
