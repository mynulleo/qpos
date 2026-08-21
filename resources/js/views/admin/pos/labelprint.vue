<template>
  <div class="label-print-container">
    <!-- 🌟 Top Header -->
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
      <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
          <i class="fas fa-barcode text-primary fs-3"></i>
          Barcode Label Printing (বারকোড লেবেল প্রিন্ট)
        </h4>
        <p class="text-muted small mb-0">
          Scan or search items to queue labels and print multiple barcode stickers in custom formats.
        </p>
      </div>

      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1"
          @click="showSettings = !showSettings">
          <i class="fas fa-cog"></i> {{ showSettings ? 'Hide Settings' : 'Label Settings' }}
        </button>
        <router-link :to="{ name: 'pos.index' }" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
          <i class="fas fa-cash-register"></i> POS Terminal
        </router-link>
        <router-link :to="{ name: 'item.index' }" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1">
          <i class="fas fa-arrow-left"></i> Products
        </router-link>
      </div>
    </div>

    <!-- ⚙️ Label Settings & Customization Panel (Collapsible) -->
    <div class="card border-0 shadow-sm mb-3" v-if="showSettings">
      <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
          <i class="fas fa-sliders-h text-primary"></i> Label Dimensions, Sizing & Layout Settings (লেবেল সাইজ ও কাস্টমাইজেশন)
        </h6>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary px-2 py-1 font-monospace">
            {{ settings.width }}{{ settings.unit }} × {{ settings.height }}{{ settings.unit }} ({{ formattedDimensionMm }})
          </span>
          <button type="button" class="btn btn-xs btn-outline-dark py-1 px-2 d-inline-flex align-items-center gap-1" @click="swapDimensions" title="Rotate label (Swap Width & Height)">
            <i class="fas fa-sync-alt"></i> Rotate / Swap
          </button>
        </div>
      </div>
      <div class="card-body p-3">
        <div class="row g-3">
          <!-- 1. Preset Size Dropdown -->
          <div class="col-lg-4 col-md-6">
            <label class="form-label fw-bold small text-secondary">
              <i class="fas fa-ruler-combined me-1 text-primary"></i> Label Size Preset (সাইজ প্রিসেট):
            </label>
            <select class="form-select form-select-sm fw-semibold" v-model="settings.preset" @change="onPresetChange">
              <option value="4x2">4" × 2" (101.6 × 50.8 mm) - Standard Barcode Tag (Landscape)</option>
              <option value="2x4">2" × 4" (50.8 × 101.6 mm) - Rotated / Tall Tag (Portrait)</option>
              <option value="3x2">3" × 2" (76.2 × 50.8 mm) - Retail Label (Landscape)</option>
              <option value="2x1">2" × 1" (50.8 × 25.4 mm) - Compact Price Tag (Landscape)</option>
              <option value="4x6">4" × 6" (101.6 × 152.4 mm) - Shipping & Box Label (Portrait)</option>
              <option value="50x30">50 × 30 mm (2.0" × 1.2") - Standard Thermal Roll</option>
              <option value="30x50">30 × 50 mm (1.2" × 2.0") - Rotated 30x50mm Thermal Tag</option>
              <option value="40x25">40 × 25 mm (1.6" × 1.0") - Jewelry & Small Sticker</option>
              <option value="38x25">38 × 25 mm - 3 Columns A4 Sheet (24 per page)</option>
              <option value="30x20">30 × 20 mm - 4 Columns A4 Sheet (40 per page)</option>
              <option value="custom">Custom Size (কাস্টম সাইজ)</option>
            </select>
          </div>

          <!-- 2. Width, Height & Unit Selector with Rotate Button -->
          <div class="col-lg-4 col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label fw-bold small text-secondary mb-0">
                <i class="fas fa-arrows-alt me-1 text-primary"></i> Custom Dimensions (প্রস্থ × উচ্চতা):
              </label>
              <div class="btn-group btn-group-sm">
                <button
                  type="button"
                  class="btn btn-xs py-0 px-2"
                  :class="settings.unit === 'in' ? 'btn-primary' : 'btn-outline-secondary'"
                  style="font-size: 11px;"
                  @click="changeUnit('in')">
                  Inch (in)
                </button>
                <button
                  type="button"
                  class="btn btn-xs py-0 px-2"
                  :class="settings.unit === 'mm' ? 'btn-primary' : 'btn-outline-secondary'"
                  style="font-size: 11px;"
                  @click="changeUnit('mm')">
                  MM (mm)
                </button>
              </div>
            </div>

            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white small fw-bold">W:</span>
              <input
                type="number"
                step="0.01"
                min="0.1"
                class="form-control text-center font-monospace fw-bold"
                v-model.number="settings.width"
                @input="onDimensionManualChange"
                placeholder="Width"
              />
              <span class="input-group-text bg-white px-1 border-start-0 border-end-0">×</span>
              <span class="input-group-text bg-white small fw-bold">H:</span>
              <input
                type="number"
                step="0.01"
                min="0.1"
                class="form-control text-center font-monospace fw-bold"
                v-model.number="settings.height"
                @input="onDimensionManualChange"
                placeholder="Height"
              />
              <span class="input-group-text bg-light fw-bold font-monospace">{{ settings.unit }}</span>
              <button
                type="button"
                class="btn btn-outline-primary"
                @click="swapDimensions"
                title="Rotate / Swap Width & Height (যেমন 4x2 থেকে 2x4 বা 2x4 থেকে 4x2)">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <small class="text-muted" style="font-size: 11px;">
                Orientation: <strong>{{ isPortraitLabel ? '↕ Portrait (খাড়া)' : '↔ Landscape (অনুভূমিক)' }}</strong>
              </small>
              <small class="text-primary font-monospace" style="font-size: 11px;">
                {{ settings.unit === 'in' ? formattedDimensionMm : formattedDimensionIn }}
              </small>
            </div>
          </div>

          <!-- 3. Paper Type & Column Layout -->
          <div class="col-lg-4 col-md-6">
            <label class="form-label fw-bold small text-secondary mb-1">
              <i class="fas fa-print me-1 text-primary"></i> Printer Paper & Columns:
            </label>
            <div class="input-group input-group-sm">
              <select class="form-select form-select-sm" v-model="settings.paperType">
                <option value="thermal">Continuous Roll / Barcode Printer</option>
                <option value="A4">A4 Sheet Paper (210 × 297 mm)</option>
                <option value="letter">Letter Sheet Paper (8.5 × 11 in)</option>
              </select>
              <select class="form-select form-select-sm" v-model.number="settings.columns" style="max-width: 115px;">
                <option :value="1">1 Column</option>
                <option :value="2">2 Columns</option>
                <option :value="3">3 Columns</option>
                <option :value="4">4 Columns</option>
              </select>
            </div>
          </div>

          <!-- 4. Store / Business Name on Label -->
          <div class="col-lg-4 col-md-6">
            <label class="form-label fw-bold small text-secondary mb-1">Store / Business Name on Label:</label>
            <input
              type="text"
              class="form-control form-control-sm"
              v-model="settings.companyName"
              placeholder="e.g. QPOS Store"
            />
          </div>

          <!-- 5. Content Toggles -->
          <div class="col-lg-8 col-md-12">
            <label class="form-label fw-bold small text-secondary d-block mb-1">Label Content & Formatting Toggles:</label>
            <div class="d-flex flex-wrap gap-3 mt-1 bg-light p-2 rounded border">
              <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="showCompany" v-model="settings.showCompany">
                <label class="form-check-label small" for="showCompany">Store Name</label>
              </div>
              <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="showTitle" v-model="settings.showTitle">
                <label class="form-check-label small" for="showTitle">Product Title</label>
              </div>
              <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="showPrice" v-model="settings.showPrice">
                <label class="form-check-label small" for="showPrice">Price (মূল্য)</label>
              </div>
              <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="showBarcodeNum" v-model="settings.showBarcodeNum">
                <label class="form-check-label small" for="showBarcodeNum">Barcode Digits</label>
              </div>
              <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" id="showBorder" v-model="settings.showBorder">
                <label class="form-check-label small" for="showBorder">Sticker Border</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔍 Search & Scan Card -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body p-3">
        <div class="row g-2 align-items-center">
          <div class="col-lg-8 col-md-7">
            <div class="position-relative">
              <div class="input-group input-group-lg shadow-sm rounded-3">
                <span class="input-group-text bg-white border-end-0 text-primary">
                  <i class="fas fa-barcode fa-lg"></i>
                </span>
                <input
                  ref="barcodeSearchInput"
                  type="text"
                  class="form-control form-control-lg border-start-0 ps-0 fw-bold"
                  placeholder="Scan barcode with scanner or type product name/code (Press Enter)..."
                  v-model="searchTerm"
                  @input="onSearchInput"
                  @keyup.enter="handleSearchEnter"
                  autocomplete="off"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary border-start-0"
                  v-if="searchTerm"
                  @click="clearSearch">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Search Results Dropdown -->
              <div
                v-if="searchResults.length > 0"
                class="position-absolute w-100 bg-white border rounded-3 shadow-lg mt-1 search-results-dropdown"
                style="max-height: 380px; overflow-y: auto; z-index: 9999;">
                <div
                  v-for="item in searchResults"
                  :key="item.id"
                  class="p-2 border-bottom hover-bg-light cursor-pointer d-flex align-items-center justify-content-between"
                  @click="addItemToQueue(item)">
                  <div class="d-flex align-items-center gap-2">
                    <img v-if="item.image" :src="item.image" class="rounded border" style="width: 42px; height: 42px; object-fit: cover;" />
                    <div v-else class="rounded bg-light border d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                      <i class="fas fa-box text-muted"></i>
                    </div>
                    <div>
                      <div class="fw-bold text-dark">{{ item.title }}</div>
                      <div class="small text-muted">
                        <span class="font-monospace text-primary fw-bold me-2"><i class="fas fa-barcode me-1"></i>{{ item.barcode || 'N/A' }}</span>
                        <span class="badge bg-secondary me-1" v-if="item.category">{{ item.category.title }}</span>
                        <span class="fw-bold text-success">Tk. {{ formatMoney(item.opening_rate || (item.item_prices && item.item_prices[0] ? item.item_prices[0].selling_price : 0)) }}</span>
                      </div>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-primary px-3 rounded-pill">
                    <i class="fas fa-plus me-1"></i> Add
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-5 d-flex gap-2">
            <button
              type="button"
              class="btn btn-lg btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm rounded-3"
              @click="openBrowseModal">
              <i class="fas fa-boxes"></i> Browse Inventory
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 📋 Queued Labels List & Action Section -->
    <div class="row g-3">
      <!-- Left / Main: Queued Products Table -->
      <div class="col-xl-7 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">
                {{ labelQueue.length }} Products
              </span>
              <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">
                {{ totalStickersCount }} Total Stickers
              </span>
            </div>

            <div class="d-flex align-items-center gap-2" v-if="labelQueue.length > 0">
              <div class="d-flex align-items-center gap-1 bg-light px-2 py-1 rounded border">
                <span class="small text-muted fw-bold text-nowrap">Set All Qty:</span>
                <input
                  type="number"
                  min="1"
                  class="form-control form-control-sm text-center font-monospace fw-bold"
                  style="width: 55px;"
                  v-model.number="bulkQty"
                  @change="applyBulkQuantity"
                />
              </div>
              <button
                type="button"
                class="btn btn-sm btn-outline-danger"
                @click="clearQueue"
                title="Clear all queued items">
                <i class="fas fa-trash-alt me-1"></i> Clear All
              </button>
            </div>
          </div>

          <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0" v-if="labelQueue.length > 0">
              <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                  <th width="5%" class="text-center">#</th>
                  <th width="35%">Product Details</th>
                  <th width="20%">Barcode</th>
                  <th width="15%">Price (Tk.)</th>
                  <th width="18%" class="text-center">Print Copies (পরিমাণ)</th>
                  <th width="7%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in labelQueue"
                  :key="item.queue_id"
                  :class="{ 'table-primary-subtle': selectedPreviewIndex === index }"
                  @click="selectedPreviewIndex = index"
                  class="cursor-pointer">
                  <td class="text-center text-muted small">{{ index + 1 }}</td>
                  <td>
                    <div class="fw-bold text-dark mb-1">{{ item.title }}</div>
                    <div class="small text-muted d-flex gap-2 align-items-center">
                      <span class="badge bg-light text-dark border" v-if="item.category">{{ item.category.title }}</span>
                      <span v-if="item.unit">Unit: {{ item.unit.title }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1">
                      {{ item.barcode }}
                    </span>
                    <div class="mt-1" v-if="item.barcode_image">
                      <img :src="item.barcode_image" style="height: 24px; max-width: 130px;" />
                    </div>
                  </td>
                  <td @click.stop>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control form-control-sm font-monospace fw-bold text-end"
                      v-model.number="item.price"
                      style="max-width: 95px;"
                    />
                  </td>
                  <td @click.stop>
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary px-2 py-1"
                        @click="item.qty > 1 ? item.qty-- : removeItem(index)">
                        <i class="fas fa-minus small"></i>
                      </button>
                      <input
                        type="number"
                        min="1"
                        class="form-control form-control-sm text-center fw-bold font-monospace"
                        style="width: 55px;"
                        v-model.number="item.qty"
                      />
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary px-2 py-1"
                        @click="item.qty++">
                        <i class="fas fa-plus small"></i>
                      </button>
                    </div>
                    <!-- Quick Set Pills -->
                    <div class="d-flex justify-content-center gap-1 mt-1">
                      <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="item.qty = 1">1</span>
                      <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="item.qty = 5">5</span>
                      <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="item.qty = 10">10</span>
                      <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="item.qty = 20">20</span>
                    </div>
                  </td>
                  <td class="text-center" @click.stop>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger rounded-circle p-1"
                      style="width: 30px; height: 30px;"
                      @click="removeItem(index)"
                      title="Remove from queue">
                      <i class="fas fa-times"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Empty State -->
            <div v-else class="text-center py-5 text-muted">
              <div class="mb-3">
                <i class="fas fa-barcode fa-4x text-secondary opacity-25"></i>
              </div>
              <h5 class="fw-bold text-secondary">No products queued for printing</h5>
              <p class="small text-muted mb-3">
                Scan barcode using a scanner or search products from the top search box to add barcode labels.
              </p>
              <button type="button" class="btn btn-sm btn-primary px-4 rounded-pill shadow-sm" @click="openBrowseModal">
                <i class="fas fa-search me-1"></i> Browse Products
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Live Realistic Sticker Preview & Print Action -->
      <div class="col-xl-5 col-lg-5">
        <!-- 🖨️ Compact Print Action Button -->
        <button
          type="button"
          class="btn btn-success btn-lg w-100 fw-bold py-3 mb-3 shadow-sm d-flex align-items-center justify-content-center gap-2 text-white"
          :disabled="labelQueue.length === 0"
          @click="printLabels">
          <i class="fas fa-print fa-lg"></i>
          <span>Print All Barcode Labels</span>
          <span class="badge bg-white text-success font-monospace ms-1 px-2 py-1" v-if="totalStickersCount > 0">
            {{ totalStickersCount }} Labels
          </span>
        </button>

        <!-- 🏷️ Live Label Sample Preview Card -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-bold small text-dark d-flex align-items-center gap-1">
              <i class="fas fa-eye text-primary"></i> Live Label Preview
            </span>
            <div class="d-flex align-items-center gap-2">
              <button
                type="button"
                class="btn btn-outline-secondary btn-sm py-0 px-2 d-inline-flex align-items-center gap-1"
                style="font-size: 11px;"
                @click="swapDimensions"
                title="Rotate preview (Swap Width & Height)">
                <i class="fas fa-sync-alt"></i> Rotate ({{ isPortraitLabel ? 'Portrait' : 'Landscape' }})
              </button>
              <div class="btn-group btn-group-sm" v-if="labelQueue.length > 1">
                <button
                  type="button"
                  class="btn btn-outline-secondary btn-sm px-2 py-0"
                  :disabled="selectedPreviewIndex <= 0"
                  @click="selectedPreviewIndex--">
                  <i class="fas fa-chevron-left" style="font-size: 10px;"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-outline-secondary btn-sm px-2 py-0"
                  :disabled="selectedPreviewIndex >= labelQueue.length - 1"
                  @click="selectedPreviewIndex++">
                  <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="card-body p-3 bg-light text-center">
            <!-- Realistic Sticker Label Container (Dynamically Scaled & Styled) -->
            <div class="label-preview-stage">
              <div
                class="thermal-label-box"
                :class="[{ 'bordered': settings.showBorder, 'is-portrait': isPortraitLabel }]"
                :style="previewLabelStyle">
                
                <!-- Store Name -->
                <div v-if="settings.showCompany" class="label-company-name">
                  {{ settings.companyName || $root.site.title || 'QPOS STORE' }}
                </div>

                <!-- Product Title -->
                <div v-if="settings.showTitle" class="label-product-title" :title="activePreviewItem ? activePreviewItem.title : 'Sample Product Title'">
                  {{ activePreviewItem ? activePreviewItem.title : 'SAMPLE PRODUCT TITLE' }}
                </div>

                <!-- Barcode Image -->
                <div class="label-barcode-image-wrapper">
                  <img
                    v-if="activePreviewItem && activePreviewItem.barcode_image"
                    :src="activePreviewItem.barcode_image"
                    class="label-barcode-img"
                    alt="Barcode"
                  />
                  <div v-else class="barcode-placeholder font-monospace">
                    |||||||||||||||||||||||||||||||||||||
                  </div>
                </div>

                <!-- Barcode Text -->
                <div v-if="settings.showBarcodeNum" class="label-barcode-digits">
                  {{ activePreviewItem ? activePreviewItem.barcode : '1002001' }}
                </div>

                <!-- Price Box -->
                <div v-if="settings.showPrice" class="label-price-tag">
                  <span class="price-currency">Price: Tk.</span>
                  <span class="price-val">{{ formatMoney(activePreviewItem ? activePreviewItem.price : 450) }}</span>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
              <small class="text-muted" style="font-size: 11px;">
                <i class="fas fa-ruler-combined me-1 text-primary"></i>
                Size: <strong>{{ settings.width }} {{ settings.unit }} × {{ settings.height }} {{ settings.unit }}</strong>
                ({{ formattedDimensionMm }}) • <strong>{{ isPortraitLabel ? 'Portrait ↕' : 'Landscape ↔' }}</strong>
              </small>
              <button
                type="button"
                class="btn btn-link btn-sm text-decoration-none p-0"
                style="font-size: 11px;"
                @click="showSettings = !showSettings">
                <i class="fas fa-sliders-h me-1"></i> {{ showSettings ? 'Hide Settings' : 'Customize Size' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📦 Modal: Browse Inventory Items (Advanced Filtering) -->
    <div class="modal fade" id="browseItemsModal" tabindex="-1" aria-hidden="true" ref="browseModal">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
          <!-- Modal Header -->
          <div class="modal-header bg-light border-bottom py-3">
            <div class="d-flex align-items-center gap-2">
              <span class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle">
                <i class="fas fa-boxes fa-lg"></i>
              </span>
              <div>
                <h5 class="modal-title fw-bold mb-0 text-dark">
                  Browse Inventory & Select Products for Barcode Labels
                </h5>
                <p class="small text-muted mb-0">
                  Filter by Barcode range, New/Recent items date, Category, or Keyword to batch print stickers.
                </p>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-3 bg-white">
            <!-- 🔍 Advanced Filter Toolbar Box -->
            <div class="bg-light p-3 rounded-3 border mb-3">
              <div class="row g-2 align-items-center">
                <!-- 1. Search Keyword -->
                <div class="col-lg-3 col-md-6">
                  <label class="form-label fw-bold small text-secondary mb-1">
                    <i class="fas fa-search me-1 text-primary"></i> Search Product:
                  </label>
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      placeholder="Title or barcode..."
                      v-model="modalFilters.searchTerm"
                      @input="onModalFilterChangeDebounced"
                    />
                    <button
                      type="button"
                      class="btn btn-outline-secondary"
                      v-if="modalFilters.searchTerm"
                      @click="modalFilters.searchTerm = ''; fetchModalItems();">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>

                <!-- 2. Category Filter -->
                <div class="col-lg-3 col-md-6">
                  <label class="form-label fw-bold small text-secondary mb-1">
                    <i class="fas fa-tags me-1 text-primary"></i> Category:
                  </label>
                  <select
                    class="form-select form-select-sm"
                    v-model="modalFilters.categoryId"
                    @change="fetchModalItems">
                    <option value="">-- All Categories --</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                  </select>
                </div>

                <!-- 3. New Items / Date Filter Preset -->
                <div class="col-lg-3 col-md-6">
                  <label class="form-label fw-bold small text-secondary mb-1">
                    <i class="fas fa-clock me-1 text-success"></i> New Items / Date:
                  </label>
                  <select
                    class="form-select form-select-sm fw-semibold"
                    :class="{ 'border-success text-success': modalFilters.dateFilter !== 'all' }"
                    v-model="modalFilters.dateFilter"
                    @change="onDateFilterPresetChange">
                    <option value="all">All Products (সব আইটেম)</option>
                    <option value="today">✨ Added Today (আজকের নতুন আইটেম)</option>
                    <option value="yesterday">🕒 Added Yesterday (গতকালের আইটেম)</option>
                    <option value="last_7_days">📅 Last 7 Days (গত ৭ দিনের আইটেম)</option>
                    <option value="last_30_days">🗓️ Last 30 Days (গত ৩০ দিনের আইটেম)</option>
                    <option value="custom">📆 Custom Date Range (তারিখ নির্বাচন)</option>
                  </select>
                </div>

                <!-- 4. Barcode Range (From - To) -->
                <div class="col-lg-3 col-md-6">
                  <label class="form-label fw-bold small text-secondary mb-1">
                    <i class="fas fa-barcode me-1 text-dark"></i> Barcode Range (From - To):
                  </label>
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control form-control-sm font-monospace text-center"
                      placeholder="From Barcode"
                      v-model="modalFilters.fromBarcode"
                      @input="onModalFilterChangeDebounced"
                    />
                    <span class="input-group-text px-1 bg-white border-start-0 border-end-0 text-muted">-</span>
                    <input
                      type="text"
                      class="form-control form-control-sm font-monospace text-center"
                      placeholder="To Barcode"
                      v-model="modalFilters.toBarcode"
                      @input="onModalFilterChangeDebounced"
                    />
                  </div>
                </div>
              </div>

              <!-- Secondary Row: Custom Date Range & Filter Actions -->
              <div class="row g-2 mt-2 pt-2 border-top align-items-center" v-if="modalFilters.dateFilter === 'custom' || showMoreModalFilters">
                <!-- Custom From Date -->
                <div class="col-lg-3 col-md-4" v-if="modalFilters.dateFilter === 'custom'">
                  <label class="form-label fw-bold small text-muted mb-1">From Date:</label>
                  <input
                    type="date"
                    class="form-control form-control-sm"
                    v-model="modalFilters.fromDate"
                    @change="fetchModalItems"
                  />
                </div>

                <!-- Custom To Date -->
                <div class="col-lg-3 col-md-4" v-if="modalFilters.dateFilter === 'custom'">
                  <label class="form-label fw-bold small text-muted mb-1">To Date:</label>
                  <input
                    type="date"
                    class="form-control form-control-sm"
                    v-model="modalFilters.toDate"
                    @change="fetchModalItems"
                  />
                </div>

                <!-- Barcode Status Filter -->
                <div class="col-lg-3 col-md-4">
                  <label class="form-label fw-bold small text-muted mb-1">Barcode Status:</label>
                  <select
                    class="form-select form-select-sm"
                    v-model="modalFilters.hasBarcode"
                    @change="fetchModalItems">
                    <option value="all">All (With & Without Barcode)</option>
                    <option value="yes">Only With Barcode Generated</option>
                    <option value="no">Only Missing Barcode</option>
                  </select>
                </div>

                <!-- Price Range -->
                <div class="col-lg-3 col-md-4">
                  <label class="form-label fw-bold small text-muted mb-1">Price Range (Tk.):</label>
                  <div class="input-group input-group-sm">
                    <input
                      type="number"
                      class="form-control form-control-sm text-end"
                      placeholder="Min"
                      v-model.number="modalFilters.minPrice"
                      @input="onModalFilterChangeDebounced"
                    />
                    <span class="input-group-text px-1 bg-white border-start-0 border-end-0 text-muted">-</span>
                    <input
                      type="number"
                      class="form-control form-control-sm text-end"
                      placeholder="Max"
                      v-model.number="modalFilters.maxPrice"
                      @input="onModalFilterChangeDebounced"
                    />
                  </div>
                </div>
              </div>

              <!-- Quick Filter Chips & Reset Row -->
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top">
                <!-- Left: Quick Date Preset Buttons -->
                <div class="d-flex flex-wrap align-items-center gap-1">
                  <span class="small text-muted fw-bold me-1" style="font-size: 11px;">Quick Presets:</span>
                  <button
                    type="button"
                    class="btn btn-xs py-0 px-2 rounded-pill"
                    :class="modalFilters.dateFilter === 'today' ? 'btn-success' : 'btn-outline-secondary'"
                    style="font-size: 11px;"
                    @click="setQuickDatePreset('today')">
                    ✨ Today (আজকে)
                  </button>
                  <button
                    type="button"
                    class="btn btn-xs py-0 px-2 rounded-pill"
                    :class="modalFilters.dateFilter === 'yesterday' ? 'btn-info text-white' : 'btn-outline-secondary'"
                    style="font-size: 11px;"
                    @click="setQuickDatePreset('yesterday')">
                    🕒 Yesterday (গতকাল)
                  </button>
                  <button
                    type="button"
                    class="btn btn-xs py-0 px-2 rounded-pill"
                    :class="modalFilters.dateFilter === 'last_7_days' ? 'btn-primary' : 'btn-outline-secondary'"
                    style="font-size: 11px;"
                    @click="setQuickDatePreset('last_7_days')">
                    📅 Last 7 Days (৭ দিন)
                  </button>
                  <button
                    type="button"
                    class="btn btn-xs py-0 px-2 rounded-pill"
                    :class="modalFilters.dateFilter === 'last_30_days' ? 'btn-primary' : 'btn-outline-secondary'"
                    style="font-size: 11px;"
                    @click="setQuickDatePreset('last_30_days')">
                    🗓️ Last 30 Days
                  </button>
                  <button
                    type="button"
                    class="btn btn-xs py-0 px-2 rounded-pill"
                    :class="modalFilters.hasBarcode === 'yes' ? 'btn-dark' : 'btn-outline-secondary'"
                    style="font-size: 11px;"
                    @click="toggleBarcodeOnlyPreset">
                    <i class="fas fa-barcode me-1"></i> Has Barcode
                  </button>
                </div>

                <!-- Right: More Filters & Reset -->
                <div class="d-flex align-items-center gap-2">
                  <button
                    type="button"
                    class="btn btn-link btn-sm text-decoration-none p-0 text-secondary"
                    style="font-size: 11px;"
                    @click="showMoreModalFilters = !showMoreModalFilters">
                    <i class="fas" :class="showMoreModalFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    {{ showMoreModalFilters ? 'Less Filters' : 'More Filters' }}
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger py-0 px-2 d-inline-flex align-items-center gap-1"
                    style="font-size: 11px;"
                    v-if="hasActiveModalFilters"
                    @click="resetModalFilters">
                    <i class="fas fa-undo"></i> Reset Filters
                  </button>
                </div>
              </div>

              <!-- Active Filter Chips -->
              <div class="d-flex flex-wrap gap-1 mt-2" v-if="hasActiveModalFilters">
                <span class="small text-muted fw-bold me-1 align-self-center" style="font-size: 10px;">Active Filters:</span>
                <span class="badge bg-primary text-white d-inline-flex align-items-center gap-1" v-if="modalFilters.searchTerm">
                  Keyword: "{{ modalFilters.searchTerm }}"
                  <i class="fas fa-times cursor-pointer" @click="modalFilters.searchTerm = ''; fetchModalItems();"></i>
                </span>
                <span class="badge bg-secondary text-white d-inline-flex align-items-center gap-1" v-if="modalFilters.categoryId">
                  Category: {{ getCategoryTitle(modalFilters.categoryId) }}
                  <i class="fas fa-times cursor-pointer" @click="modalFilters.categoryId = ''; fetchModalItems();"></i>
                </span>
                <span class="badge bg-success text-white d-inline-flex align-items-center gap-1" v-if="modalFilters.dateFilter !== 'all'">
                  Date: {{ getDatePresetLabel(modalFilters.dateFilter) }}
                  <i class="fas fa-times cursor-pointer" @click="modalFilters.dateFilter = 'all'; fetchModalItems();"></i>
                </span>
                <span class="badge bg-dark text-white d-inline-flex align-items-center gap-1 font-monospace" v-if="modalFilters.fromBarcode || modalFilters.toBarcode">
                  Barcode: {{ modalFilters.fromBarcode || 'Min' }} - {{ modalFilters.toBarcode || 'Max' }}
                  <i class="fas fa-times cursor-pointer" @click="modalFilters.fromBarcode = ''; modalFilters.toBarcode = ''; fetchModalItems();"></i>
                </span>
                <span class="badge bg-info text-white d-inline-flex align-items-center gap-1" v-if="modalFilters.hasBarcode !== 'all'">
                  {{ modalFilters.hasBarcode === 'yes' ? 'Has Barcode Only' : 'Missing Barcode Only' }}
                  <i class="fas fa-times cursor-pointer" @click="modalFilters.hasBarcode = 'all'; fetchModalItems();"></i>
                </span>
              </div>
            </div>

            <!-- 📊 Table Top Selection & Statistics Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center bg-light px-3 py-2 rounded-2 border mb-2 gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="form-check mb-0">
                  <input
                    type="checkbox"
                    id="selectAllModalCheckbox"
                    class="form-check-input"
                    :checked="isAllSelectedInModal"
                    @change="toggleSelectAllModal"
                  />
                  <label class="form-check-label fw-bold small text-dark cursor-pointer" for="selectAllModalCheckbox">
                    Select All ({{ filteredModalItems.length }} Products)
                  </label>
                </div>

                <div class="btn-group btn-group-sm">
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm py-0 px-2"
                    style="font-size: 11px;"
                    @click="selectFirstNItems(10)"
                    title="Select first 10 items">
                    +10
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm py-0 px-2"
                    style="font-size: 11px;"
                    @click="selectFirstNItems(25)"
                    title="Select first 25 items">
                    +25
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm py-0 px-2"
                    style="font-size: 11px;"
                    @click="selectFirstNItems(50)"
                    title="Select first 50 items">
                    +50
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm py-0 px-2 text-danger"
                    style="font-size: 11px;"
                    v-if="selectedModalItemIds.length > 0"
                    @click="selectedModalItemIds = []"
                    title="Clear current selection">
                    Clear ({{ selectedModalItemIds.length }})
                  </button>
                </div>
              </div>

              <div class="d-flex align-items-center gap-2">
                <span class="small text-muted" style="font-size: 12px;">
                  Showing <strong>{{ filteredModalItems.length }}</strong> products
                </span>
                <span class="badge bg-primary fs-6 px-2 py-1 rounded-pill" v-if="selectedModalItemIds.length > 0">
                  {{ selectedModalItemIds.length }} Selected
                </span>
              </div>
            </div>

            <!-- 📋 Modal Products Table -->
            <div class="table-responsive border rounded-2" style="max-height: 400px; min-height: 200px;">
              <!-- Loading Spinner Overlay -->
              <div v-if="modalLoading" class="text-center py-5">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                <span class="text-muted small fw-bold">Loading inventory items...</span>
              </div>

              <table v-else class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light sticky-top shadow-sm">
                  <tr class="small text-muted text-uppercase">
                    <th width="4%" class="text-center">#</th>
                    <th width="4%" class="text-center">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="isAllSelectedInModal"
                        @change="toggleSelectAllModal"
                      />
                    </th>
                    <th width="32%">Product Details</th>
                    <th width="16%">Barcode</th>
                    <th width="16%">Created Date</th>
                    <th width="14%" class="text-end">Price (Tk.)</th>
                    <th width="14%" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, idx) in filteredModalItems"
                    :key="item.id"
                    :class="{ 'table-primary-subtle': selectedModalItemIds.includes(item.id) }"
                    class="cursor-pointer"
                    @click="toggleModalItem(item)">
                    <td class="text-center text-muted small" @click.stop>{{ idx + 1 }}</td>
                    <td class="text-center" @click.stop>
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="selectedModalItemIds.includes(item.id)"
                        @change="toggleModalItem(item)"
                      />
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <img
                          v-if="item.image"
                          :src="item.image"
                          class="rounded border"
                          style="width: 34px; height: 34px; object-fit: cover;"
                        />
                        <div
                          v-else
                          class="rounded bg-light border d-flex align-items-center justify-content-center"
                          style="width: 34px; height: 34px;">
                          <i class="fas fa-box text-muted small"></i>
                        </div>
                        <div>
                          <div class="fw-bold text-dark text-break" style="font-size: 13px;">
                            {{ item.title }}
                          </div>
                          <div class="small text-muted d-flex align-items-center gap-1">
                            <span class="badge bg-light text-dark border py-0 px-1" style="font-size: 10px;" v-if="item.category">
                              {{ item.category.title }}
                            </span>
                            <span v-if="item.unit" style="font-size: 10px;">{{ item.unit.title }}</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 12px;">
                        <i class="fas fa-barcode me-1 text-primary"></i>{{ item.barcode || 'N/A' }}
                      </span>
                    </td>
                    <td>
                      <span
                        v-if="getItemDateBadge(item.created_at)"
                        class="badge py-1 px-2 fw-semibold"
                        :class="getItemDateBadge(item.created_at).class"
                        style="font-size: 11px;">
                        {{ getItemDateBadge(item.created_at).label }}
                      </span>
                      <small v-else class="text-muted">-</small>
                    </td>
                    <td class="text-end font-monospace text-success fw-bold">
                      Tk. {{ formatMoney(item.opening_rate || (item.item_prices && item.item_prices[0] ? item.item_prices[0].selling_price : 0)) }}
                    </td>
                    <td class="text-center" @click.stop>
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary py-1 px-2 rounded-pill d-inline-flex align-items-center gap-1"
                        style="font-size: 11px;"
                        @click="addSingleItemToQueue(item)">
                        <i class="fas fa-plus"></i> Add
                      </button>
                    </td>
                  </tr>
                  <tr v-if="filteredModalItems.length === 0 && !modalLoading">
                    <td colspan="7" class="text-center py-5 text-muted">
                      <div class="mb-2">
                        <i class="fas fa-search fa-2x text-secondary opacity-50"></i>
                      </div>
                      <div class="fw-bold">No products match your active filters.</div>
                      <p class="small text-muted mb-2">Try adjusting or clearing your search term, barcode range, or date filter.</p>
                      <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-pill" @click="resetModalFilters">
                        <i class="fas fa-undo me-1"></i> Reset Filters
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Modal Footer with Batch Quantity Selector & Add Button -->
          <div class="modal-footer bg-light border-top d-flex flex-wrap justify-content-between align-items-center py-2 px-3">
            <!-- Left: Default Quantity per Item to Add -->
            <div class="d-flex align-items-center gap-2">
              <span class="small fw-bold text-dark">Sticker Copies per Product:</span>
              <div class="input-group input-group-sm" style="width: 110px;">
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="modalAddQty > 1 ? modalAddQty-- : null">
                  <i class="fas fa-minus"></i>
                </button>
                <input
                  type="number"
                  min="1"
                  max="999"
                  class="form-control text-center font-monospace fw-bold"
                  v-model.number="modalAddQty"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="modalAddQty++">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="d-none d-sm-flex gap-1">
                <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="modalAddQty = 1">1</span>
                <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="modalAddQty = 2">2</span>
                <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="modalAddQty = 5">5</span>
                <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="modalAddQty = 10">10</span>
              </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                Close
              </button>
              <button
                type="button"
                class="btn btn-primary btn-sm px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                :disabled="selectedModalItemIds.length === 0"
                @click="addSelectedModalItems">
                <i class="fas fa-plus-circle"></i>
                <span>Add Selected ({{ selectedModalItemIds.length }})</span>
                <span class="badge bg-white text-primary rounded-pill font-monospace" v-if="selectedModalItemIds.length > 0">
                  {{ selectedModalItemIds.length * modalAddQty }} Stickers
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🖨️ Hidden Master Printable Area (#labelPrintArea) -->
    <div id="labelPrintArea" style="display: none;">
      <div :class="['barcode-print-wrapper', 'layout-' + settings.layout]">
        <div
          v-for="(sticker, sIdx) in generatedStickersList"
          :key="sIdx"
          :class="['sticker-item', 'layout-' + settings.layout, { 'with-border': settings.showBorder }]">
          
          <div v-if="settings.showCompany" class="sticker-company">
            {{ settings.companyName || $root.site.title || 'QPOS' }}
          </div>

          <div v-if="settings.showTitle" class="sticker-title">
            {{ sticker.title }}
          </div>

          <div class="sticker-barcode-box">
            <img
              v-if="sticker.barcode_image"
              :src="sticker.barcode_image"
              class="sticker-barcode-img"
            />
          </div>

          <div v-if="settings.showBarcodeNum" class="sticker-barcode-num">
            {{ sticker.barcode }}
          </div>

          <div v-if="settings.showPrice" class="sticker-price">
            Tk. {{ formatMoney(sticker.price) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import _ from "lodash";
import bootstrap from "bootstrap/dist/js/bootstrap.bundle.min.js";

const model = "pos";

const sizePresetMap = {
  "4x2": { unit: "in", width: 4, height: 2, columns: 1, paperType: "thermal", orientation: "landscape" },
  "2x4": { unit: "in", width: 2, height: 4, columns: 1, paperType: "thermal", orientation: "portrait" },
  "3x2": { unit: "in", width: 3, height: 2, columns: 1, paperType: "thermal", orientation: "landscape" },
  "2x1": { unit: "in", width: 2, height: 1, columns: 1, paperType: "thermal", orientation: "landscape" },
  "4x6": { unit: "in", width: 4, height: 6, columns: 1, paperType: "thermal", orientation: "portrait" },
  "50x30": { unit: "mm", width: 50, height: 30, columns: 1, paperType: "thermal", orientation: "landscape" },
  "30x50": { unit: "mm", width: 30, height: 50, columns: 1, paperType: "thermal", orientation: "portrait" },
  "40x25": { unit: "mm", width: 40, height: 25, columns: 1, paperType: "thermal", orientation: "landscape" },
  "38x25": { unit: "mm", width: 38, height: 25, columns: 3, paperType: "A4", orientation: "landscape" },
  "30x20": { unit: "mm", width: 30, height: 20, columns: 4, paperType: "A4", orientation: "landscape" },
};

export default {
  data() {
    return {
      model: model,
      page_title: "Barcode Label Print",
      searchTerm: "",
      searchResults: [],
      labelQueue: [],
      bulkQty: 1,
      selectedPreviewIndex: 0,
      showSettings: false,
      settings: {
        preset: "4x2", // 4x2, 2x4, 3x2, 2x1, 4x6, 50x30, 30x50, 40x25, 38x25, 30x20, custom
        unit: "in", // 'in' or 'mm'
        width: 4, // 4 inches (101.6 mm)
        height: 2, // 2 inches (50.8 mm)
        orientation: "landscape", // 'landscape' or 'portrait'
        columns: 1, // 1 (Continuous roll), 2, 3, 4
        paperType: "thermal", // 'thermal', 'A4', 'letter'
        gapH: 0.08, // in inches
        gapV: 0.12, // in inches
        pageMargin: 0,
        padding: 8, // px
        companyName: "",
        showCompany: true,
        showTitle: true,
        showPrice: true,
        showBarcodeNum: true,
        showBorder: true,
        titleFontSize: 12,
        priceFontSize: 13,
        barcodeHeight: 38,
        barcodeNumFontSize: 11,
        companyFontSize: 11,
      },
      // Modal Browse Inventory State
      allItems: [],
      categories: [],
      modalLoading: false,
      showMoreModalFilters: false,
      modalAddQty: 1,
      selectedModalItemIds: [],
      modalFilters: {
        searchTerm: "",
        categoryId: "",
        fromBarcode: "",
        toBarcode: "",
        dateFilter: "all", // all, today, yesterday, last_7_days, last_30_days, custom
        fromDate: "",
        toDate: "",
        hasBarcode: "all", // all, yes, no
        minPrice: "",
        maxPrice: "",
      },
    };
  },

  computed: {
    totalStickersCount() {
      return this.labelQueue.reduce((sum, item) => sum + (parseInt(item.qty) || 0), 0);
    },

    activePreviewItem() {
      if (this.labelQueue.length > 0) {
        const idx = Math.min(this.selectedPreviewIndex, this.labelQueue.length - 1);
        return this.labelQueue[idx >= 0 ? idx : 0];
      }
      return null;
    },

    isPortraitLabel() {
      const w = parseFloat(this.settings.width) || 4;
      const h = parseFloat(this.settings.height) || 2;
      return h > w;
    },

    formattedDimensionMm() {
      if (this.settings.unit === "mm") {
        return `${this.settings.width} × ${this.settings.height} mm`;
      }
      const wMm = +(parseFloat(this.settings.width || 0) * 25.4).toFixed(1);
      const hMm = +(parseFloat(this.settings.height || 0) * 25.4).toFixed(1);
      return `${wMm} × ${hMm} mm`;
    },

    formattedDimensionIn() {
      if (this.settings.unit === "in") {
        return `${this.settings.width}" × ${this.settings.height}"`;
      }
      const wIn = +(parseFloat(this.settings.width || 0) / 25.4).toFixed(2);
      const hIn = +(parseFloat(this.settings.height || 0) / 25.4).toFixed(2);
      return `${wIn}" × ${hIn}"`;
    },

    previewLabelStyle() {
      const s = this.settings;
      const w = parseFloat(s.width) || 4;
      const h = parseFloat(s.height) || 2;
      const ratio = w / h;

      let targetWidth = 260;
      let targetHeight = 260 / ratio;

      // Adjust for tall labels (e.g. 2x4 portrait)
      if (targetHeight > 340) {
        targetHeight = 340;
        targetWidth = targetHeight * ratio;
      }
      // Adjust for wide labels (e.g. 4x2 landscape)
      if (targetWidth > 300) {
        targetWidth = 300;
        targetHeight = targetWidth / ratio;
      }
      if (targetHeight < 110) {
        targetHeight = 110;
      }
      if (targetWidth < 140) {
        targetWidth = 140;
      }

      return {
        width: `${Math.round(targetWidth)}px`,
        minHeight: `${Math.round(targetHeight)}px`,
        padding: `${s.padding || 8}px`,
      };
    },

    layoutPresetLabel() {
      return this.settings.preset === "custom" ? "Custom Size" : "Standard Preset";
    },

    previewItem() {
      return this.activePreviewItem;
    },

    generatedStickersList() {
      const stickers = [];
      this.labelQueue.forEach((item) => {
        const count = parseInt(item.qty) || 0;
        for (let i = 0; i < count; i++) {
          stickers.push({
            title: item.title,
            barcode: item.barcode,
            barcode_image: item.barcode_image,
            price: item.price,
          });
        }
      });
      return stickers;
    },

    hasActiveModalFilters() {
      const f = this.modalFilters;
      return (
        !!f.searchTerm ||
        !!f.categoryId ||
        !!f.fromBarcode ||
        !!f.toBarcode ||
        f.dateFilter !== "all" ||
        !!f.fromDate ||
        !!f.toDate ||
        f.hasBarcode !== "all" ||
        !!f.minPrice ||
        !!f.maxPrice
      );
    },

    filteredModalItems() {
      let list = this.allItems;
      const f = this.modalFilters;

      // Category filter
      if (f.categoryId) {
        list = list.filter((i) => i.category_id == f.categoryId);
      }

      // Keyword Search (Title or Barcode)
      if (f.searchTerm) {
        const term = f.searchTerm.toLowerCase().trim();
        list = list.filter(
          (i) =>
            (i.title && i.title.toLowerCase().includes(term)) ||
            (i.barcode && i.barcode.toString().toLowerCase().includes(term))
        );
      }

      // Barcode Range Filter (From Barcode - To Barcode)
      if (f.fromBarcode && f.toBarcode) {
        const fromTrim = f.fromBarcode.trim();
        const toTrim = f.toBarcode.trim();
        if (!isNaN(fromTrim) && !isNaN(toTrim)) {
          let minB = parseInt(fromTrim);
          let maxB = parseInt(toTrim);
          if (minB > maxB) {
            const t = minB; minB = maxB; maxB = t;
          }
          list = list.filter((i) => {
            if (!i.barcode || isNaN(i.barcode)) return false;
            const bNum = parseInt(i.barcode);
            return bNum >= minB && bNum <= maxB;
          });
        } else {
          list = list.filter((i) => {
            if (!i.barcode) return false;
            const bStr = i.barcode.toString();
            return bStr >= fromTrim && bStr <= toTrim;
          });
        }
      } else if (f.fromBarcode) {
        const fromTrim = f.fromBarcode.trim();
        if (!isNaN(fromTrim)) {
          const minB = parseInt(fromTrim);
          list = list.filter((i) => i.barcode && !isNaN(i.barcode) && parseInt(i.barcode) >= minB);
        } else {
          list = list.filter((i) => i.barcode && i.barcode.toString() >= fromTrim);
        }
      } else if (f.toBarcode) {
        const toTrim = f.toBarcode.trim();
        if (!isNaN(toTrim)) {
          const maxB = parseInt(toTrim);
          list = list.filter((i) => i.barcode && !isNaN(i.barcode) && parseInt(i.barcode) <= maxB);
        } else {
          list = list.filter((i) => i.barcode && i.barcode.toString() <= toTrim);
        }
      }

      // Date / New Items Filter
      if (f.dateFilter !== "all") {
        const now = new Date();
        const todayStr = now.toISOString().slice(0, 10);

        if (f.dateFilter === "today") {
          list = list.filter((i) => {
            if (!i.created_at) return false;
            return i.created_at.slice(0, 10) === todayStr;
          });
        } else if (f.dateFilter === "yesterday") {
          const yest = new Date(now);
          yest.setDate(yest.getDate() - 1);
          const yestStr = yest.toISOString().slice(0, 10);
          list = list.filter((i) => {
            if (!i.created_at) return false;
            return i.created_at.slice(0, 10) === yestStr;
          });
        } else if (f.dateFilter === "last_7_days") {
          const past = new Date(now);
          past.setDate(past.getDate() - 7);
          list = list.filter((i) => {
            if (!i.created_at) return false;
            return new Date(i.created_at) >= past;
          });
        } else if (f.dateFilter === "last_30_days") {
          const past = new Date(now);
          past.setDate(past.getDate() - 30);
          list = list.filter((i) => {
            if (!i.created_at) return false;
            return new Date(i.created_at) >= past;
          });
        } else if (f.dateFilter === "custom") {
          if (f.fromDate) {
            list = list.filter((i) => i.created_at && i.created_at.slice(0, 10) >= f.fromDate);
          }
          if (f.toDate) {
            list = list.filter((i) => i.created_at && i.created_at.slice(0, 10) <= f.toDate);
          }
        }
      }

      // Barcode Status
      if (f.hasBarcode === "yes") {
        list = list.filter((i) => !!i.barcode && i.barcode.toString().trim() !== "");
      } else if (f.hasBarcode === "no") {
        list = list.filter((i) => !i.barcode || i.barcode.toString().trim() === "");
      }

      // Price Range
      if (f.minPrice !== "" && !isNaN(f.minPrice)) {
        list = list.filter((i) => {
          const price = parseFloat(i.opening_rate) || (i.item_prices && i.item_prices[0] ? parseFloat(i.item_prices[0].selling_price) : 0);
          return price >= parseFloat(f.minPrice);
        });
      }
      if (f.maxPrice !== "" && !isNaN(f.maxPrice)) {
        list = list.filter((i) => {
          const price = parseFloat(i.opening_rate) || (i.item_prices && i.item_prices[0] ? parseFloat(i.item_prices[0].selling_price) : 0);
          return price <= parseFloat(f.maxPrice);
        });
      }

      return list;
    },

    isAllSelectedInModal() {
      if (this.filteredModalItems.length === 0) return false;
      return this.filteredModalItems.every((item) => this.selectedModalItemIds.includes(item.id));
    },
  },

  methods: {
    // =========================================================
    // ⚙️ LABEL SIZE PRESET & ROTATION METHODS
    // =========================================================
    onPresetChange() {
      const p = sizePresetMap[this.settings.preset];
      if (p) {
        this.settings.unit = p.unit;
        this.settings.width = p.width;
        this.settings.height = p.height;
        this.settings.columns = p.columns || 1;
        this.settings.paperType = p.paperType || "thermal";
        this.settings.orientation = p.orientation || (p.width >= p.height ? "landscape" : "portrait");
        this.$toast(`Applied ${this.settings.preset} label preset (${this.settings.width}${this.settings.unit} × ${this.settings.height}${this.settings.unit})`, "info");
      }
    },

    swapDimensions() {
      const tempW = parseFloat(this.settings.width) || 4;
      const tempH = parseFloat(this.settings.height) || 2;
      this.settings.width = tempH;
      this.settings.height = tempW;
      this.settings.orientation = this.settings.width >= this.settings.height ? "landscape" : "portrait";

      // Match preset name if standard
      if (this.settings.unit === "in") {
        if (this.settings.width === 4 && this.settings.height === 2) this.settings.preset = "4x2";
        else if (this.settings.width === 2 && this.settings.height === 4) this.settings.preset = "2x4";
        else if (this.settings.width === 3 && this.settings.height === 2) this.settings.preset = "3x2";
        else if (this.settings.width === 2 && this.settings.height === 1) this.settings.preset = "2x1";
        else this.settings.preset = "custom";
      } else if (this.settings.unit === "mm") {
        if (this.settings.width === 50 && this.settings.height === 30) this.settings.preset = "50x30";
        else if (this.settings.width === 30 && this.settings.height === 50) this.settings.preset = "30x50";
        else if (this.settings.width === 40 && this.settings.height === 25) this.settings.preset = "40x25";
        else this.settings.preset = "custom";
      } else {
        this.settings.preset = "custom";
      }

      this.$toast(
        `Rotated label to ${this.settings.width} ${this.settings.unit} × ${this.settings.height} ${this.settings.unit} (${this.settings.orientation.toUpperCase()})`,
        "success"
      );
    },

    changeUnit(newUnit) {
      if (this.settings.unit === newUnit) return;
      if (newUnit === "mm" && this.settings.unit === "in") {
        this.settings.width = +(parseFloat(this.settings.width) * 25.4).toFixed(1);
        this.settings.height = +(parseFloat(this.settings.height) * 25.4).toFixed(1);
        this.settings.unit = "mm";
      } else if (newUnit === "in" && this.settings.unit === "mm") {
        this.settings.width = +(parseFloat(this.settings.width) / 25.4).toFixed(2);
        this.settings.height = +(parseFloat(this.settings.height) / 25.4).toFixed(2);
        this.settings.unit = "in";
      }
    },

    onDimensionManualChange() {
      this.settings.preset = "custom";
      this.settings.orientation = parseFloat(this.settings.width) >= parseFloat(this.settings.height) ? "landscape" : "portrait";
    },

    onSearchInput: _.debounce(function () {
      if (!this.searchTerm || this.searchTerm.trim().length === 0) {
        this.searchResults = [];
        return;
      }

      const term = this.searchTerm.trim();
      axios
        .get("pos/labelprint", { params: { term: term } })
        .then((res) => {
          this.searchResults = res.data || [];
        })
        .catch((err) => {
          console.error("Search error:", err);
        });
    }, 250),

    handleSearchEnter() {
      if (!this.searchTerm) return;
      const term = this.searchTerm.trim();

      // Check if exact match in current searchResults
      const exactMatch = this.searchResults.find(
        (i) => i.barcode && i.barcode.toString().toLowerCase() === term.toLowerCase()
      );

      if (exactMatch) {
        this.addItemToQueue(exactMatch);
        this.clearSearch();
        return;
      }

      // If only 1 item in results
      if (this.searchResults.length === 1) {
        this.addItemToQueue(this.searchResults[0]);
        this.clearSearch();
        return;
      }

      // Otherwise do direct barcode lookup
      axios
        .get("pos/labelprint", { params: { term: term } })
        .then((res) => {
          const items = res.data || [];
          if (items.length === 1) {
            this.addItemToQueue(items[0]);
            this.clearSearch();
          } else if (items.length > 1) {
            this.searchResults = items;
          } else {
            this.$toast("No item found with barcode: " + term, "warning");
          }
        });
    },

    clearSearch() {
      this.searchTerm = "";
      this.searchResults = [];
      if (this.$refs.barcodeSearchInput) {
        this.$refs.barcodeSearchInput.focus();
      }
    },

    addItemToQueue(item, customQty = null) {
      if (!item.barcode) {
        this.$toast("Item has no barcode generated", "warning");
      }

      const qtyToAdd = customQty ? parseInt(customQty) : 1;

      // Check if already in queue
      const existing = this.labelQueue.find((q) => q.id === item.id);
      if (existing) {
        existing.qty = (parseInt(existing.qty) || 0) + qtyToAdd;
        this.$toast(`Updated "${item.title}" label quantity to ${existing.qty}`, "info");
      } else {
        const defaultPrice =
          parseFloat(item.opening_rate) ||
          (item.item_prices && item.item_prices[0]
            ? parseFloat(item.item_prices[0].selling_price)
            : 0);

        this.labelQueue.push({
          queue_id: Date.now() + "_" + Math.random().toString(36).substr(2, 9),
          id: item.id,
          title: item.title,
          barcode: item.barcode,
          barcode_image: item.barcode_image,
          category: item.category,
          unit: item.unit,
          price: defaultPrice,
          qty: qtyToAdd,
        });

        this.$toast(`Added "${item.title}" to label queue (${qtyToAdd} copy)`, "success");
      }

      this.clearSearch();
    },

    addSingleItemToQueue(item) {
      this.addItemToQueue(item, this.modalAddQty || 1);
    },

    removeItem(index) {
      this.labelQueue.splice(index, 1);
    },

    clearQueue() {
      if (confirm("Are you sure you want to clear all queued labels?")) {
        this.labelQueue = [];
      }
    },

    applyBulkQuantity() {
      const q = parseInt(this.bulkQty) || 1;
      this.labelQueue.forEach((item) => {
        item.qty = q;
      });
    },

    formatMoney(val) {
      const num = Number(val || 0);
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    // =========================================================
    // 📦 ADVANCED MODAL BROWSE INVENTORY METHODS
    // =========================================================
    openBrowseModal() {
      const modalEl = document.getElementById("browseItemsModal");
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }

      if (this.allItems.length === 0) {
        this.fetchModalItems();
      }
    },

    fetchModalItems() {
      this.modalLoading = true;
      const params = {
        allData: true,
        term: this.modalFilters.searchTerm || undefined,
        category_id: this.modalFilters.categoryId || undefined,
        from_barcode: this.modalFilters.fromBarcode || undefined,
        to_barcode: this.modalFilters.toBarcode || undefined,
        date_filter: this.modalFilters.dateFilter !== "all" && this.modalFilters.dateFilter !== "custom" ? this.modalFilters.dateFilter : undefined,
        from_date: this.modalFilters.dateFilter === "custom" ? this.modalFilters.fromDate : undefined,
        to_date: this.modalFilters.dateFilter === "custom" ? this.modalFilters.toDate : undefined,
        has_barcode: this.modalFilters.hasBarcode !== "all" ? this.modalFilters.hasBarcode : undefined,
        min_price: this.modalFilters.minPrice || undefined,
        max_price: this.modalFilters.maxPrice || undefined,
      };

      axios
        .get("pos/labelprint", { params: params })
        .then((res) => {
          this.allItems = res.data || [];
        })
        .catch((err) => {
          console.error("Fetch modal items error:", err);
        })
        .finally(() => {
          this.modalLoading = false;
        });
    },

    onModalFilterChangeDebounced: _.debounce(function () {
      this.fetchModalItems();
    }, 350),

    onDateFilterPresetChange() {
      if (this.modalFilters.dateFilter !== "custom") {
        this.modalFilters.fromDate = "";
        this.modalFilters.toDate = "";
      }
      this.fetchModalItems();
    },

    setQuickDatePreset(preset) {
      this.modalFilters.dateFilter = preset;
      this.modalFilters.fromDate = "";
      this.modalFilters.toDate = "";
      this.fetchModalItems();
    },

    toggleBarcodeOnlyPreset() {
      this.modalFilters.hasBarcode = this.modalFilters.hasBarcode === "yes" ? "all" : "yes";
      this.fetchModalItems();
    },

    resetModalFilters() {
      this.modalFilters = {
        searchTerm: "",
        categoryId: "",
        fromBarcode: "",
        toBarcode: "",
        dateFilter: "all",
        fromDate: "",
        toDate: "",
        hasBarcode: "all",
        minPrice: "",
        maxPrice: "",
      };
      this.selectedModalItemIds = [];
      this.fetchModalItems();
    },

    getCategoryTitle(catId) {
      const cat = this.categories.find((c) => c.id == catId);
      return cat ? cat.title : "Category #" + catId;
    },

    getDatePresetLabel(preset) {
      const map = {
        today: "Today (আজকে)",
        yesterday: "Yesterday (গতকাল)",
        last_7_days: "Last 7 Days (৭ দিন)",
        last_30_days: "Last 30 Days (৩০ দিন)",
        custom: "Custom Date Range",
      };
      return map[preset] || preset;
    },

    getItemDateBadge(createdAt) {
      if (!createdAt) return null;
      try {
        const itemDate = new Date(createdAt);
        const today = new Date();
        
        const isToday =
          itemDate.getDate() === today.getDate() &&
          itemDate.getMonth() === today.getMonth() &&
          itemDate.getFullYear() === today.getFullYear();

        if (isToday) {
          return { label: "✨ Added Today", class: "bg-success text-white" };
        }

        const yest = new Date(today);
        yest.setDate(yest.getDate() - 1);
        const isYesterday =
          itemDate.getDate() === yest.getDate() &&
          itemDate.getMonth() === yest.getMonth() &&
          itemDate.getFullYear() === yest.getFullYear();

        if (isYesterday) {
          return { label: "🕒 Yesterday", class: "bg-info text-white" };
        }

        const diffTime = Math.abs(today - itemDate);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays <= 7) {
          return { label: `${diffDays} days ago`, class: "bg-primary text-white" };
        }

        const formatted = itemDate.toLocaleDateString("en-GB", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        });
        return { label: formatted, class: "bg-light text-dark border" };
      } catch (e) {
        return null;
      }
    },

    toggleModalItem(item) {
      const idx = this.selectedModalItemIds.indexOf(item.id);
      if (idx > -1) {
        this.selectedModalItemIds.splice(idx, 1);
      } else {
        this.selectedModalItemIds.push(item.id);
      }
    },

    toggleSelectAllModal(e) {
      if (e.target.checked) {
        this.selectedModalItemIds = this.filteredModalItems.map((i) => i.id);
      } else {
        this.selectedModalItemIds = [];
      }
    },

    selectFirstNItems(count) {
      const ids = this.filteredModalItems.slice(0, count).map((i) => i.id);
      this.selectedModalItemIds = Array.from(new Set([...this.selectedModalItemIds, ...ids]));
    },

    addSelectedModalItems() {
      const selected = this.allItems.filter((i) => this.selectedModalItemIds.includes(i.id));
      const qtyToAdd = Math.max(1, parseInt(this.modalAddQty) || 1);

      selected.forEach((item) => {
        const existing = this.labelQueue.find((q) => q.id === item.id);
        if (existing) {
          existing.qty += qtyToAdd;
        } else {
          const defaultPrice =
            parseFloat(item.opening_rate) ||
            (item.item_prices && item.item_prices[0]
              ? parseFloat(item.item_prices[0].selling_price)
              : 0);

          this.labelQueue.push({
            queue_id: Date.now() + "_" + Math.random().toString(36).substr(2, 9),
            id: item.id,
            title: item.title,
            barcode: item.barcode,
            barcode_image: item.barcode_image,
            category: item.category,
            unit: item.unit,
            price: defaultPrice,
            qty: qtyToAdd,
          });
        }
      });

      const totalStickersAdded = selected.length * qtyToAdd;
      this.$toast(`Added ${selected.length} product(s) (${totalStickersAdded} stickers) to queue`, "success");

      this.selectedModalItemIds = [];

      const modalEl = document.getElementById("browseItemsModal");
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      }
    },

    // =========================================================
    // 🖨️ CUSTOM PRINT ENGINE WITH EXACT DIMENSIONS & ROTATION
    // =========================================================
    printLabels() {
      if (this.generatedStickersList.length === 0) {
        this.$toast("Please add at least one product sticker to print", "warning");
        return;
      }

      const s = this.settings;
      const wVal = `${s.width}${s.unit}`;
      const hVal = `${s.height}${s.unit}`;
      const columns = parseInt(s.columns) || 1;
      const isContinuous = columns === 1 && s.paperType === "thermal";

      // Convert width and height to mm for accurate proportional scaling
      const hMm = s.unit === "in" ? (parseFloat(s.height) || 1) * 25.4 : parseFloat(s.height) || 25.4;
      const wMm = s.unit === "in" ? (parseFloat(s.width) || 2) * 25.4 : parseFloat(s.width) || 50.8;

      let padVal = "3px 4px";
      let companyFs = 10;
      let titleFs = 10.5;
      let barcodeH = 30;
      let barcodeNumFs = 10;
      let priceFs = 11.5;

      if (hMm <= 26) {
        // Very compact tags (e.g. 2" x 1", 50x25mm, 40x25mm, 30x20mm)
        padVal = "1.5px 3px";
        companyFs = 8.5;
        titleFs = 8.5;
        barcodeH = 20;
        barcodeNumFs = 8;
        priceFs = 9.5;
      } else if (hMm <= 38) {
        // Compact medium tags (e.g. 50x30mm, 40x30mm)
        padVal = "2px 4px";
        companyFs = 9.5;
        titleFs = 9.5;
        barcodeH = 26;
        barcodeNumFs = 9;
        priceFs = 10.5;
      } else if (hMm <= 55) {
        // Standard tags (e.g. 4" x 2", 3" x 2")
        padVal = "4px 6px";
        companyFs = 11;
        titleFs = 11.5;
        barcodeH = 34;
        barcodeNumFs = 10.5;
        priceFs = 12.5;
      } else {
        // Large tags (e.g. 4" x 6")
        padVal = "6px 8px";
        companyFs = 13;
        titleFs = 13;
        barcodeH = 48;
        barcodeNumFs = 12;
        priceFs = 14;
      }

      const pageSizeRule = isContinuous
        ? `${wVal} ${hVal}`
        : s.paperType === "A4"
        ? "A4 portrait"
        : s.paperType === "letter"
        ? "letter portrait"
        : `${wVal} ${hVal}`;

      const gapHVal = s.unit === "in" ? `${s.gapH || 0.08}in` : `${s.gapH || 2}mm`;
      const gapVVal = s.unit === "in" ? `${s.gapV || 0.12}in` : `${s.gapV || 3}mm`;
      const pageMarginVal = isContinuous
        ? "0mm"
        : s.unit === "in"
        ? `${s.pageMargin || 0.2}in`
        : `${s.pageMargin || 5}mm`;

      const printStyles = `
        <style>
          @page {
            size: ${pageSizeRule};
            margin: ${pageMarginVal};
          }
          * {
            box-sizing: border-box !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: ${isContinuous ? wVal : "100%"} !important;
            height: auto !important;
            background: #ffffff !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
          }
          .barcode-print-wrapper {
            ${
              isContinuous
                ? `display: block !important; width: ${wVal} !important; margin: 0 !important; padding: 0 !important;`
                : `display: flex !important; flex-wrap: wrap !important; align-items: flex-start !important; justify-content: flex-start !important; width: 100% !important; padding: 0 !important; margin: 0 !important;`
            }
          }
          .sticker-item {
            width: ${wVal} !important;
            height: ${hVal} !important;
            min-width: ${wVal} !important;
            min-height: ${hVal} !important;
            max-width: ${wVal} !important;
            max-height: ${hVal} !important;
            box-sizing: border-box !important;
            ${isContinuous ? "margin: 0 !important;" : `margin-right: ${columns > 1 ? gapHVal : "0"} !important; margin-bottom: ${gapVVal} !important;`}
            padding: ${padVal} !important;
            background: #ffffff !important;
            color: #000000 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: space-between !important;
            text-align: center !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            ${isContinuous ? "page-break-after: always !important; break-after: page !important;" : ""}
            overflow: hidden !important;
            ${s.showBorder ? "border: 1px solid #000000 !important;" : "border: none !important;"}
          }
          ${isContinuous ? ".sticker-item:last-child { page-break-after: auto !important; break-after: auto !important; }" : ""}
          .sticker-company {
            font-size: ${companyFs}px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: #000000 !important;
            margin: 0 0 1px 0 !important;
            line-height: 1.05 !important;
            width: 100% !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            flex-shrink: 0 !important;
          }
          .sticker-title {
            font-size: ${titleFs}px !important;
            font-weight: 700 !important;
            color: #000000 !important;
            margin: 0 0 1px 0 !important;
            line-height: 1.1 !important;
            width: 100% !important;
            max-height: ${titleFs * 1.3}px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
          }
          .sticker-barcode-box {
            margin: 1px 0 !important;
            height: ${barcodeH}px !important;
            max-height: ${barcodeH}px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            flex-grow: 1 !important;
            flex-shrink: 1 !important;
            overflow: hidden !important;
          }
          .sticker-barcode-img {
            display: block !important;
            margin: 0 auto !important;
            height: ${barcodeH}px !important;
            max-height: 100% !important;
            max-width: 96% !important;
            object-fit: contain !important;
            image-rendering: -webkit-optimize-contrast !important;
            image-rendering: crisp-edges !important;
          }
          .sticker-barcode-num {
            font-size: ${barcodeNumFs}px !important;
            font-weight: 700 !important;
            font-family: "Courier New", Courier, monospace !important;
            letter-spacing: 1px !important;
            color: #000000 !important;
            line-height: 1 !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
            white-space: nowrap !important;
          }
          .sticker-price {
            font-size: ${priceFs}px !important;
            font-weight: 800 !important;
            border-top: 1px dashed #000000 !important;
            padding-top: 1.5px !important;
            margin-top: 1px !important;
            width: 100% !important;
            color: #000000 !important;
            line-height: 1.05 !important;
            flex-shrink: 0 !important;
            white-space: nowrap !important;
          }
        </style>
      `;

      const prtHtml = document.getElementById("labelPrintArea").innerHTML;
      const WinPrint = window.open("", "", "left=0,top=0,width=850,height=950,toolbar=0,scrollbars=1,status=0");
      WinPrint.document.write(`<!DOCTYPE html>
        <html>
        <head>
          <title>Barcode Labels (${this.totalStickersCount} Stickers - ${wVal} × ${hVal})</title>
          ${printStyles}
        </head>
        <body>
          ${prtHtml}
        </body>
        </html>`);
      WinPrint.document.close();
      WinPrint.focus();
      setTimeout(() => {
        WinPrint.print();
      }, 400);
    },

    fetchCategories() {
      axios.get("getcategories/Item").then((res) => {
        this.categories = res.data || [];
      });
    },
  },

  created() {
    this.page_title = "Barcode Label Printing";
    this.fetchCategories();
  },

  mounted() {
    if (this.$root.site && this.$root.site.title) {
      this.settings.companyName = this.$root.site.title;
    }
    if (this.$refs.barcodeSearchInput) {
      this.$refs.barcodeSearchInput.focus();
    }
  },

  validators: {},
};
</script>

<style scoped>
.card {
  height: auto !important;
}

.cursor-pointer {
  cursor: pointer;
}

.hover-bg-light:hover {
  background-color: #f8f9fa;
}

.hover-shadow:hover {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.table-primary-subtle {
  background-color: #e7f1ff !important;
}

/* ========================================================= */
/* 🏷️ REALISTIC STICKER LIVE PREVIEW STYLING                */
/* ========================================================= */
.label-preview-stage {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  padding: 16px 12px;
  background-color: #f1f3f5;
  background-image: radial-gradient(#d1d5db 1px, transparent 1px);
  background-size: 14px 14px;
  border-radius: 8px;
  border: 1px dashed #d1d5db;
}

.thermal-label-box {
  background: #ffffff;
  border-radius: 6px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04);
  padding: 10px 14px;
  text-align: center;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #111;
  transition: all 0.25s ease-in-out;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border: 1px solid #e5e7eb;
}

.thermal-label-box.bordered {
  border: 1.5px solid #111;
}

.thermal-label-box.is-portrait {
  justify-content: space-around;
}

.label-company-name {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #222;
  margin-bottom: 2px;
  line-height: 1.2;
  width: 100%;
}

.label-product-title {
  font-size: 12px;
  font-weight: 700;
  color: #000;
  margin-bottom: 4px;
  line-height: 1.3;
  width: 100%;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
}

.label-barcode-image-wrapper {
  margin: 3px 0 2px 0;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  min-height: 38px;
}

.label-barcode-img {
  max-height: 38px;
  max-width: 95%;
  display: block;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

.barcode-placeholder {
  font-size: 12px;
  letter-spacing: -1px;
  color: #666;
  padding: 6px;
  background: #f8f9fa;
  border-radius: 3px;
  width: 90%;
}

.label-barcode-digits {
  font-size: 12px;
  font-weight: 700;
  font-family: "Courier New", Courier, monospace;
  letter-spacing: 2px;
  color: #000;
  margin-bottom: 3px;
  line-height: 1;
}

.label-price-tag {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  border-top: 1px dashed #333;
  padding-top: 4px;
  margin-top: 2px;
  width: 100%;
  font-weight: 800;
  font-size: 13px;
  color: #000;
  line-height: 1.2;
}

.label-price-tag .price-currency {
  font-size: 11px;
  font-weight: 700;
}

.label-price-tag .price-val {
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.5px;
}

/* ========================================================= */
/* 🖨️ PRINT STYLES FOR LABEL PRINTING (#labelPrintArea)     */
/* ========================================================= */
.barcode-print-wrapper {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: flex-start;
  padding: 0;
  margin: 0;
}

/* Sticker Item Base Box */
.sticker-item {
  padding: 6px 8px;
  text-align: center;
  font-family: Arial, Helvetica, sans-serif;
  background: #fff;
  color: #000;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.sticker-item.with-border {
  border: 1px solid #333;
}

.sticker-company {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #000;
  margin-bottom: 2px;
  line-height: 1.1;
  width: 100%;
}

.sticker-title {
  font-size: 11px;
  font-weight: 700;
  color: #000;
  margin-bottom: 2px;
  line-height: 1.2;
  width: 100%;
  max-height: 26px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sticker-barcode-box {
  margin: 2px 0;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.sticker-barcode-img {
  display: block;
  margin: 0 auto;
  height: 38px;
  max-width: 100%;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

.sticker-barcode-num {
  font-size: 11px;
  font-weight: 700;
  font-family: "Courier New", Courier, monospace;
  letter-spacing: 1.5px;
  color: #000;
  line-height: 1.1;
}

.sticker-price {
  font-size: 12px;
  font-weight: 800;
  border-top: 1px dashed #333;
  padding-top: 2px;
  margin-top: 2px;
  width: 100%;
  color: #000;
  line-height: 1.1;
}

@media print {
  @page {
    margin: 3mm;
    size: auto;
  }

  body {
    margin: 0;
    padding: 0;
    background: #fff !important;
  }

  .sticker-item {
    page-break-inside: avoid !important;
  }
}
</style>
