<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="container-fluid px-2">
      <!-- Top Action Bar -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
        <div class="d-flex align-items-center gap-3">
          <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
            <i class="fas fa-box-open fa-2x"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
              {{ data.title || 'Loading Item...' }}
              <span :class="data.status === 'active' || data.status === 'Active' || data.status === 1 ? 'badge bg-success fs-6' : 'badge bg-danger fs-6'">
                {{ data.status === 'active' || data.status === 'Active' || data.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </h4>
            <div class="text-muted small mt-1 font-monospace">
              <span class="me-3"><i class="fas fa-barcode text-primary me-1"></i> Barcode: <strong>{{ data.barcode || 'N/A' }}</strong></span>
              <span class="me-3" v-if="data.category"><i class="fas fa-folder text-warning me-1"></i> Category: <strong>{{ data.category.title }}</strong></span>
              <span v-if="data.unit"><i class="fas fa-balance-scale text-info me-1"></i> Unit: <strong>{{ data.unit.title }}</strong></span>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold" @click="printSingleBarcode">
            <i class="fas fa-print me-1"></i> Print Barcode
          </button>
          <router-link v-if="data.id" :to="{ name: 'item.edit', params: { id: data.id } }" class="btn btn-warning btn-sm px-3 fw-bold text-dark">
            <i class="fas fa-edit me-1"></i> Edit Item
          </router-link>
          <router-link :to="'/report/itemladger?item_id=' + data.id" class="btn btn-outline-secondary btn-sm px-3 fw-bold">
            <i class="fas fa-chart-line me-1"></i> Item Ledger
          </router-link>
          <router-link :to="{ name: 'item.index' }" class="btn btn-outline-dark btn-sm px-3 fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Back to List
          </router-link>
        </div>
      </div>

      <!-- KPI Summary Cards (Overview) -->
      <div class="row g-3 mb-4" v-if="data.metrics">
        <!-- 1. Current Available Stock -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-uppercase small fw-bold text-muted mb-1">Available Stock</div>
                  <h3 class="mb-0 fw-bold" :class="data.metrics.current_stock > 0 ? 'text-primary' : 'text-danger'">
                    {{ data.metrics.current_stock }}
                    <small class="fs-6 text-muted fw-normal">{{ data.unit ? data.unit.title : 'Units' }}</small>
                  </h3>
                  <div class="small mt-1">
                    <span v-if="data.metrics.current_stock > 5" class="badge bg-success bg-opacity-10 text-success fw-bold">
                      <i class="fas fa-check-circle me-1"></i> In Stock
                    </span>
                    <span v-else-if="data.metrics.current_stock > 0" class="badge bg-warning bg-opacity-10 text-warning fw-bold">
                      <i class="fas fa-exclamation-circle me-1"></i> Low Stock
                    </span>
                    <span v-else class="badge bg-danger bg-opacity-10 text-danger fw-bold">
                      <i class="fas fa-times-circle me-1"></i> Out of Stock
                    </span>
                  </div>
                </div>
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                  <i class="fas fa-boxes fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Total Sold Quantity & Sales Value -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card border-0 shadow-sm border-start border-success border-4 h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-uppercase small fw-bold text-muted mb-1">Total Sales (বিক্রয়)</div>
                  <h3 class="mb-0 fw-bold text-success">
                    {{ data.metrics.total_sold_qty }}
                    <small class="fs-6 text-muted fw-normal">Sold</small>
                  </h3>
                  <div class="small text-muted mt-1 font-monospace">
                    Revenue: <strong class="text-success">{{ $filter.formatBDT(data.metrics.total_sales_amount) }}</strong>
                  </div>
                </div>
                <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                  <i class="fas fa-shopping-cart fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Total Stock In & Purchase Value -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card border-0 shadow-sm border-start border-info border-4 h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-uppercase small fw-bold text-muted mb-1">Total Stock In (ক্রয়/ওপেনিং)</div>
                  <h3 class="mb-0 fw-bold text-info">
                    {{ data.metrics.total_stock_in }}
                    <small class="fs-6 text-muted fw-normal">Received</small>
                  </h3>
                  <div class="small text-muted mt-1 font-monospace">
                    Cost: <strong class="text-dark">{{ $filter.formatBDT(data.metrics.total_purchase_amount) }}</strong>
                  </div>
                </div>
                <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
                  <i class="fas fa-truck-loading fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Total Wastage / Damage -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card border-0 shadow-sm border-start border-danger border-4 h-100 bg-white">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-uppercase small fw-bold text-muted mb-1">Wastage / Loss (অপচয়)</div>
                  <h3 class="mb-0 fw-bold text-danger">
                    {{ data.metrics.total_wastage_qty }}
                    <small class="fs-6 text-muted fw-normal">Wasted</small>
                  </h3>
                  <div class="small text-danger mt-1">
                    <i class="fas fa-trash-alt me-1"></i> Stock deducted
                  </div>
                </div>
                <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                  <i class="fas fa-dumpster fa-2x"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="row g-3 mb-4">
        <!-- Left Side: Image & Barcode Label Preview -->
        <div class="col-lg-4 col-md-5">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
              <span><i class="fas fa-image me-1 text-primary"></i> Product Image</span>
            </div>
            <div class="card-body text-center p-3">
              <img v-if="data.image" :src="data.image" class="img-fluid rounded border shadow-sm" style="max-height: 220px; object-fit: contain;" alt="Product Image" />
              <div v-else class="py-4 text-muted border rounded bg-light">
                <i class="fas fa-image fa-3x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 small">No Product Image Available</p>
              </div>
            </div>
          </div>

          <!-- Barcode Label Preview Box -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
              <span><i class="fas fa-barcode me-1 text-dark"></i> Barcode Label</span>
              <button type="button" class="btn btn-xs btn-primary px-2" @click="printSingleBarcode">
                <i class="fas fa-print me-1"></i> Print
              </button>
            </div>
            <div class="card-body p-3 text-center">
              <div class="p-3 border rounded bg-white shadow-sm mx-auto" style="max-width: 260px; border: 1px dashed #444 !important;">
                <div class="fw-bold text-truncate text-uppercase fs-6 mb-1 text-dark">
                  {{ data.title }}
                </div>
                <div v-if="data.barcode_image" class="my-2 d-flex justify-content-center">
                  <img :src="data.barcode_image" alt="Barcode" style="height: 45px; max-width: 100%;" />
                </div>
                <div class="font-monospace fw-bold fs-6 text-dark" style="letter-spacing: 1.5px;">
                  {{ data.barcode || 'N/A' }}
                </div>
                <div class="fw-bold text-primary mt-2 border-top pt-1 fs-6 font-monospace" v-if="primarySellingPrice > 0">
                  MRP: {{ $filter.formatBDT(primarySellingPrice) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: Master Specifications & Details + Warranty Info -->
        <div class="col-lg-8 col-md-7">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
              <span><i class="fas fa-info-circle me-1 text-primary"></i> Master Specifications</span>
              <span class="badge bg-secondary font-monospace">Item #{{ data.id }}</span>
            </div>
            <div class="card-body p-3">
              <table class="table table-striped table-bordered align-middle mb-0">
                <tbody>
                  <tr>
                    <th width="30%">Item Title (নাম):</th>
                    <td class="fw-bold fs-6 text-dark">{{ data.title }}</td>
                  </tr>
                  <tr>
                    <th>Category (ক্যাটাগরি):</th>
                    <td class="fw-semibold">{{ data.category ? data.category.title : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th>Brand (ব্র্যান্ড):</th>
                    <td class="fw-semibold">{{ data.brand ? data.brand.title : 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th>Unit (পরিমাপ একক):</th>
                    <td><span class="badge bg-info bg-opacity-10 text-info border border-info fw-bold">{{ data.unit ? data.unit.title : 'Piece / Unit' }}</span></td>
                  </tr>
                  <tr>
                    <th>Barcode (বারকোড):</th>
                    <td>
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-dark font-monospace fs-6 px-3 py-1">{{ data.barcode || 'N/A' }}</span>
                        <img v-if="data.barcode_image" :src="data.barcode_image" alt="Barcode" style="height: 34px; max-width: 140px; background: #fff; padding: 2px; border: 1px solid #ddd; border-radius: 4px;" />
                        <button type="button" class="btn btn-sm btn-outline-primary px-2 py-0 fw-semibold" @click="printSingleBarcode" title="Print Barcode">
                          <i class="fas fa-print me-1"></i> Print
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th>Primary Purchase Price:</th>
                    <td class="font-monospace fw-semibold text-dark">{{ $filter.formatBDT(primaryPurchasePrice) }}</td>
                  </tr>
                  <tr>
                    <th>Primary Selling Price (MRP):</th>
                    <td class="font-monospace fw-bold text-success fs-6">{{ $filter.formatBDT(primarySellingPrice) }}</td>
                  </tr>
                  <tr>
                    <th>Average Margin / Profit:</th>
                    <td>
                      <span class="badge bg-success bg-opacity-10 text-success fw-bold font-monospace px-2 py-1">
                        +{{ $filter.formatBDT(primarySellingPrice - primaryPurchasePrice) }}
                        <span v-if="primaryPurchasePrice > 0">
                          ({{ (((primarySellingPrice - primaryPurchasePrice) / primaryPurchasePrice) * 100).toFixed(1) }}%)
                        </span>
                      </span>
                    </td>
                  </tr>
                  <tr v-if="data.description">
                    <th>Description (বিবরণ):</th>
                    <td class="text-secondary small">{{ data.description }}</td>
                  </tr>
                  <tr>
                    <th>Created Timestamp:</th>
                    <td class="small text-muted">{{ data.created_at || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Warranty & Guarantee Card (Moved to Right Side) -->
          <div class="card border border-primary-subtle shadow-sm mb-3" v-if="isElectronicsShop || data.warranty_type !== 'none'">
            <div class="card-header bg-primary bg-opacity-10 py-2">
              <span class="fw-bold text-primary small d-flex align-items-center gap-2">
                <i class="fas fa-shield-alt"></i> Warranty & Guarantee Info (ওয়ারেন্টি তথ্য)
              </span>
            </div>
            <div class="card-body p-3">
              <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-circle" :class="data.warranty_type === 'guarantee' ? 'bg-success bg-opacity-10 text-success' : (data.warranty_type === 'warranty' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-muted')">
                  <i :class="data.warranty_type === 'guarantee' ? 'fas fa-certificate fa-2x' : (data.warranty_type === 'warranty' ? 'fas fa-tools fa-2x' : 'fas fa-shield-alt fa-2x')"></i>
                </div>
                <div>
                  <div class="small text-muted text-uppercase fw-bold">Coverage Type:</div>
                  <h6 class="mb-0 fw-bold text-capitalize" :class="data.warranty_type === 'guarantee' ? 'text-success' : (data.warranty_type === 'warranty' ? 'text-primary' : 'text-muted')">
                    {{ data.warranty_type === 'guarantee' ? 'Replacement Guarantee' : (data.warranty_type === 'warranty' ? 'Service Warranty' : 'No Warranty') }}
                  </h6>
                  <div class="small fw-semibold mt-1" v-if="data.warranty_period">
                    <i class="fas fa-clock text-warning me-1"></i> Period: <span class="badge bg-light text-dark border">{{ data.warranty_period }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Variant-Wise Price, Stock & Sales Matrix -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
              <span class="fw-bold fs-6">
                <i class="fas fa-tags me-2 text-warning"></i>Variant-Wise Price, Stock & Sales Matrix (ভেরিয়েন্ট অনুযায়ী স্টক ও বিক্রয়)
              </span>
              <span class="badge bg-primary">{{ data.variants_breakdown ? data.variants_breakdown.length : 0 }} Variants</span>
            </div>
            <div class="card-body p-0 table-responsive">
              <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light text-center" style="font-size: 13px;">
                  <tr>
                    <th width="4%">#</th>
                    <th :width="isElectronicsShop ? '16%' : '12%'">Color (রং)</th>
                    <th width="12%" v-if="!isElectronicsShop">Size (সাইজ)</th>
                    <th width="12%">Purchase Price</th>
                    <th width="12%">Selling Price</th>
                    <th width="14%">Profit Margin</th>
                    <th width="9%">Total In</th>
                    <th width="9%">Sold Qty</th>
                    <th width="9%">Wastage</th>
                    <th width="9%">Current Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(v, index) in data.variants_breakdown" :key="index">
                    <td class="text-center fw-bold">{{ index + 1 }}</td>
                    <td class="text-center">
                      <span class="badge bg-light text-dark border px-2 py-1" v-if="v.color_title">
                        <i class="fas fa-palette me-1 text-primary"></i>{{ v.color_title }}
                      </span>
                      <span class="text-muted small" v-else>Standard</span>
                    </td>
                    <td class="text-center" v-if="!isElectronicsShop">
                      <span class="badge bg-light text-dark border px-2 py-1" v-if="v.size_title">
                        <i class="fas fa-ruler me-1 text-secondary"></i>{{ v.size_title }}
                      </span>
                      <span class="text-muted small" v-else>Standard</span>
                    </td>
                    <td class="text-end font-monospace">{{ $filter.formatBDT(v.purchase_price) }}</td>
                    <td class="text-end font-monospace fw-bold text-success">{{ $filter.formatBDT(v.selling_price) }}</td>
                    <td class="text-center">
                      <span class="badge font-monospace" :class="v.profit_margin > 0 ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-secondary bg-opacity-10 text-muted'">
                        +{{ $filter.formatBDT(v.profit_margin) }} ({{ v.margin_percent }}%)
                      </span>
                    </td>
                    <td class="text-center fw-semibold text-info">{{ v.total_qty_in }}</td>
                    <td class="text-center fw-bold text-success">{{ v.total_sold }}</td>
                    <td class="text-center fw-semibold text-danger">{{ v.total_wastage }}</td>
                    <td class="text-center">
                      <span class="badge fs-6 font-monospace" :class="v.current_stock > 5 ? 'bg-success' : (v.current_stock > 0 ? 'bg-warning text-dark' : 'bg-danger')">
                        {{ v.current_stock }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="!data.variants_breakdown || data.variants_breakdown.length === 0">
                    <td :colspan="isElectronicsShop ? 9 : 10" class="text-center text-muted py-3">No variant details found for this item.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabbed Deep-Dive History Section -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-light p-2">
              <ul class="nav nav-pills card-header-pills" id="itemTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active fw-bold small" id="price-tab" data-bs-toggle="tab" data-bs-target="#price-tab-pane" type="button" role="tab">
                    <i class="fas fa-history me-1 text-primary"></i> Price Change History (মূল্য পরিবর্তনের ইতিহাস)
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link fw-bold small" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-tab-pane" type="button" role="tab">
                    <i class="fas fa-shopping-cart me-1 text-success"></i> Recent Sales (সাম্প্রতিক বিক্রয়সমূহ)
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link fw-bold small" id="purchase-tab" data-bs-toggle="tab" data-bs-target="#purchase-tab-pane" type="button" role="tab">
                    <i class="fas fa-truck-loading me-1 text-info"></i> Recent Purchases (সাম্প্রতিক ক্রয়সমূহ)
                  </button>
                </li>
              </ul>
            </div>
            <div class="card-body p-0">
              <div class="tab-content" id="itemTabContent">
                <!-- Tab 1: Price History -->
                <div class="tab-pane fade show active p-3" id="price-tab-pane" role="tabpanel">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                      <thead class="table-light text-center" style="font-size: 13px;">
                        <tr>
                          <th width="5%">#</th>
                          <th width="20%">Date & Time</th>
                          <th width="25%">Source / Change Event</th>
                          <th width="15%">Variant (Color / Size)</th>
                          <th width="18%" class="text-end">Purchase Price (ক্রয় মূল্য)</th>
                          <th width="17%" class="text-end">Selling Price (বিক্রয় মূল্য)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(ph, pIndex) in data.price_history" :key="pIndex">
                          <td class="text-center">{{ pIndex + 1 }}</td>
                          <td class="text-center small font-monospace">{{ ph.date }}</td>
                          <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                              <i class="fas fa-tag me-1"></i>{{ ph.type }}
                            </span>
                          </td>
                          <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ ph.color_title }} / {{ ph.size_title }}</span>
                          </td>
                          <td class="text-end font-monospace">{{ ph.purchase_price ? $filter.formatBDT(ph.purchase_price) : '-' }}</td>
                          <td class="text-end font-monospace fw-bold text-success">{{ ph.selling_price ? $filter.formatBDT(ph.selling_price) : '-' }}</td>
                        </tr>
                        <tr v-if="!data.price_history || data.price_history.length === 0">
                          <td colspan="6" class="text-center text-muted py-3">No price modification records logged yet.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Tab 2: Recent Sales Transactions -->
                <div class="tab-pane fade p-3" id="sales-tab-pane" role="tabpanel">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                      <thead class="table-light text-center" style="font-size: 13px;">
                        <tr>
                          <th width="5%">#</th>
                          <th width="15%">Invoice No</th>
                          <th width="12%">Date</th>
                          <th width="20%">Customer</th>
                          <th width="15%">Variant / S/N</th>
                          <th width="10%">Quantity</th>
                          <th width="11%" class="text-end">Unit Rate</th>
                          <th width="12%" class="text-end">Total Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(s, sIdx) in data.recent_sales" :key="sIdx">
                          <td class="text-center">{{ sIdx + 1 }}</td>
                          <td class="text-center fw-bold font-monospace text-primary">{{ s.invoice_no }}</td>
                          <td class="text-center small">{{ s.invoice_date }}</td>
                          <td>{{ s.customer_name || 'Walk-in Customer' }}</td>
                          <td class="small">
                            <span v-if="s.color_title || s.size_title" class="badge bg-light text-dark border me-1">
                              {{ s.color_title || '-' }} / {{ s.size_title || '-' }}
                            </span>
                            <span v-if="s.serial_no" class="badge bg-info bg-opacity-10 text-info border">
                              S/N: {{ s.serial_no }}
                            </span>
                          </td>
                          <td class="text-center fw-bold">{{ s.qty }}</td>
                          <td class="text-end font-monospace">{{ $filter.formatBDT(s.price) }}</td>
                          <td class="text-end font-monospace fw-bold text-success">{{ $filter.formatBDT(s.total_amount) }}</td>
                        </tr>
                        <tr v-if="!data.recent_sales || data.recent_sales.length === 0">
                          <td colspan="8" class="text-center text-muted py-3">No sales transactions recorded for this item yet.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Tab 3: Recent Purchases -->
                <div class="tab-pane fade p-3" id="purchase-tab-pane" role="tabpanel">
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                      <thead class="table-light text-center" style="font-size: 13px;">
                        <tr>
                          <th width="5%">#</th>
                          <th width="15%">Challan / Invoice</th>
                          <th width="12%">Purchase Date</th>
                          <th width="20%">Supplier Name</th>
                          <th width="15%">Variant</th>
                          <th width="10%">Quantity</th>
                          <th width="11%" class="text-end">Unit Cost</th>
                          <th width="12%" class="text-end">Total Cost</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(p, pIdx) in data.recent_purchases" :key="pIdx">
                          <td class="text-center">{{ pIdx + 1 }}</td>
                          <td class="text-center fw-bold font-monospace text-dark">{{ p.invoiceno || 'N/A' }}</td>
                          <td class="text-center small">{{ p.purchase_date }}</td>
                          <td>{{ p.supplier_name || 'N/A' }}</td>
                          <td class="small">
                            <span v-if="p.color_title || p.size_title" class="badge bg-light text-dark border">
                              {{ p.color_title || '-' }} / {{ p.size_title || '-' }}
                            </span>
                            <span v-else class="text-muted">Standard</span>
                          </td>
                          <td class="text-center fw-bold text-info">{{ p.qty }}</td>
                          <td class="text-end font-monospace">{{ $filter.formatBDT(p.rate) }}</td>
                          <td class="text-end font-monospace fw-bold text-dark">{{ $filter.formatBDT(p.total_amount) }}</td>
                        </tr>
                        <tr v-if="!data.recent_purchases || data.recent_purchases.length === 0">
                          <td colspan="8" class="text-center text-muted py-3">No purchase records found for this item yet.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Hidden Single Barcode Printable Area -->
    <div id="singleBarcodePrintArea" style="display: none;">
      <div style="width: 220px; padding: 10px 12px; border: 1px dashed #000; text-align: center; font-family: Arial, sans-serif; margin: 0 auto; background: #fff;">
        <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-transform: uppercase; color: #000;">
          {{ data.title }}
        </div>
        <div v-if="data.barcode_image" style="margin: 5px 0;">
          <img :src="data.barcode_image" style="width: 180px; height: 45px; display: block; margin: 0 auto;" />
        </div>
        <div style="font-size: 14px; font-weight: bold; letter-spacing: 1.5px; margin-top: 3px; font-family: monospace; color: #000;">
          {{ data.barcode }}
        </div>
        <div style="font-size: 12px; margin-top: 4px; font-weight: bold; border-top: 1px dashed #444; padding-top: 3px; color: #000;" v-if="primarySellingPrice > 0">
          MRP: {{ $filter.formatBDT(primarySellingPrice) }}
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "item";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
    };
  },

  computed: {
    isElectronicsShop() {
      const shopType = this.site?.shop_type || this.$root.site?.shop_type;
      return shopType === 'electronics';
    },

    primaryPurchasePrice() {
      if (this.data.item_prices && this.data.item_prices.length > 0) {
        return Number(this.data.item_prices[0].purchase_price || 0);
      }
      return 0;
    },

    primarySellingPrice() {
      if (this.data.item_prices && this.data.item_prices.length > 0) {
        return Number(this.data.item_prices[0].selling_price || 0);
      }
      return 0;
    },
  },

  methods: {
    printSingleBarcode() {
      if (!this.data.barcode) {
        this.$toast("No barcode available for this item", "warning");
        return;
      }
      this.print(
        "singleBarcodePrintArea",
        "Barcode Label - " + (this.data.title || "Item")
      );
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.card {
  height: auto;
}
.nav-pills .nav-link {
  color: #495057;
  border-radius: 6px;
}
.nav-pills .nav-link.active {
  background-color: #0d6efd;
  color: #fff;
}
</style>
