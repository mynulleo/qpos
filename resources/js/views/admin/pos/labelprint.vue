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
      <div class="card-header bg-light py-2">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sliders-h me-1 text-primary"></i> Label Layout & Content Settings</h6>
      </div>
      <div class="card-body p-3">
        <div class="row g-3">
          <!-- Preset Layout Selection -->
          <div class="col-md-4">
            <label class="form-label fw-bold small">Label Layout & Paper Type (লেবেল সাইজ ও লেআউট):</label>
            <select class="form-select form-select-sm" v-model="settings.layout">
              <option value="thermal">Continuous Roll / 1 Column (Thermal 50x30mm / 40x25mm)</option>
              <option value="2col">2 Columns Sheet (50x30mm)</option>
              <option value="3col">3 Columns Sheet (38x25mm / 24 per A4 Sheet)</option>
              <option value="4col">4 Columns Sheet (30x20mm / 40 per A4 Sheet)</option>
              <option value="grid">Compact Grid (Retail / Small Stickers)</option>
            </select>
          </div>

          <!-- Company Name Customization -->
          <div class="col-md-4">
            <label class="form-label fw-bold small">Store / Business Name on Label:</label>
            <input type="text" class="form-control form-control-sm" v-model="settings.companyName"
              placeholder="e.g. QPOS Store">
          </div>

          <!-- Content Toggles -->
          <div class="col-md-4">
            <label class="form-label fw-bold small d-block">Content Options:</label>
            <div class="d-flex flex-wrap gap-3 mt-1">
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="showCompany" v-model="settings.showCompany">
                <label class="form-check-label small" for="showCompany">Store Name</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="showTitle" v-model="settings.showTitle">
                <label class="form-check-label small" for="showTitle">Product Title</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="showPrice" v-model="settings.showPrice">
                <label class="form-check-label small" for="showPrice">Price (মূল্য)</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="showBarcodeNum" v-model="settings.showBarcodeNum">
                <label class="form-check-label small" for="showBarcodeNum">Barcode Text</label>
              </div>
              <div class="form-check form-check-inline">
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
            <div class="d-flex align-items-center gap-2" v-if="labelQueue.length > 1">
              <span class="small text-muted" style="font-size: 11px;">
                Item {{ selectedPreviewIndex + 1 }} of {{ labelQueue.length }}
              </span>
              <div class="btn-group btn-group-sm">
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
            <!-- Realistic Sticker Label Container -->
            <div class="label-preview-stage">
              <div
                class="thermal-label-box"
                :class="['preset-' + settings.layout, { 'bordered': settings.showBorder }]">
                
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
                <i class="fas fa-info-circle me-1 text-primary"></i>
                Preset: <strong>{{ layoutPresetLabel }}</strong>
              </small>
              <button
                type="button"
                class="btn btn-link btn-sm text-decoration-none p-0"
                style="font-size: 11px;"
                @click="showSettings = !showSettings">
                <i class="fas fa-sliders-h me-1"></i> Customize Layout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 📦 Modal: Browse Inventory Items -->
    <div class="modal fade" id="browseItemsModal" tabindex="-1" aria-hidden="true" ref="browseModal">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold">
              <i class="fas fa-boxes me-2 text-primary"></i> Select Products for Label Printing
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-3">
            <div class="row g-2 mb-3">
              <div class="col-md-7">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search by title or barcode..."
                  v-model="modalSearchTerm"
                  @input="filterModalItems"
                />
              </div>
              <div class="col-md-5">
                <select class="form-select" v-model="modalCategoryFilter" @change="filterModalItems">
                  <option value="">-- All Categories --</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                </select>
              </div>
            </div>

            <div class="table-responsive" style="max-height: 380px;">
              <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light sticky-top">
                  <tr>
                    <th width="6%" class="text-center">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="isAllSelectedInModal"
                        @change="toggleSelectAllModal"
                      />
                    </th>
                    <th width="44%">Product Name</th>
                    <th width="25%">Barcode</th>
                    <th width="25%">Price</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredModalItems" :key="item.id" class="cursor-pointer" @click="toggleModalItem(item)">
                    <td class="text-center" @click.stop>
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="selectedModalItemIds.includes(item.id)"
                        @change="toggleModalItem(item)"
                      />
                    </td>
                    <td>
                      <div class="fw-bold text-dark">{{ item.title }}</div>
                      <small class="text-muted" v-if="item.category">{{ item.category.title }}</small>
                    </td>
                    <td class="font-monospace fw-bold">{{ item.barcode || 'N/A' }}</td>
                    <td class="font-monospace text-success fw-bold">
                      Tk. {{ formatMoney(item.opening_rate || (item.item_prices && item.item_prices[0] ? item.item_prices[0].selling_price : 0)) }}
                    </td>
                  </tr>
                  <tr v-if="filteredModalItems.length === 0">
                    <td colspan="4" class="text-center py-4 text-muted">No products found matching search.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer bg-light justify-content-between">
            <span class="small text-muted fw-bold">
              Selected: {{ selectedModalItemIds.length }} item(s)
            </span>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button
                type="button"
                class="btn btn-primary"
                :disabled="selectedModalItemIds.length === 0"
                @click="addSelectedModalItems">
                <i class="fas fa-plus me-1"></i> Add Selected ({{ selectedModalItemIds.length }})
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
        layout: "thermal", // thermal, 2col, 3col, 4col, grid
        companyName: "",
        showCompany: true,
        showTitle: true,
        showPrice: true,
        showBarcodeNum: true,
        showBorder: true,
      },
      // Modal data
      allItems: [],
      categories: [],
      modalSearchTerm: "",
      modalCategoryFilter: "",
      selectedModalItemIds: [],
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

    layoutPresetLabel() {
      const map = {
        thermal: "Continuous Roll (Thermal 50x30mm)",
        "2col": "2-Columns Sheet (50x30mm)",
        "3col": "3-Columns Sheet (38x25mm / 24 per A4)",
        "4col": "4-Columns Sheet (30x20mm / 40 per A4)",
        grid: "Compact Grid (Retail)",
      };
      return map[this.settings.layout] || this.settings.layout;
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

    filteredModalItems() {
      let list = this.allItems;
      if (this.modalCategoryFilter) {
        list = list.filter((i) => i.category_id == this.modalCategoryFilter);
      }
      if (this.modalSearchTerm) {
        const term = this.modalSearchTerm.toLowerCase();
        list = list.filter(
          (i) =>
            (i.title && i.title.toLowerCase().includes(term)) ||
            (i.barcode && i.barcode.toLowerCase().includes(term))
        );
      }
      return list;
    },

    isAllSelectedInModal() {
      if (this.filteredModalItems.length === 0) return false;
      return this.filteredModalItems.every((item) => this.selectedModalItemIds.includes(item.id));
    },
  },

  methods: {
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

    addItemToQueue(item) {
      if (!item.barcode) {
        this.$toast("Item has no barcode generated", "warning");
      }

      // Check if already in queue
      const existing = this.labelQueue.find((q) => q.id === item.id);
      if (existing) {
        existing.qty = (parseInt(existing.qty) || 0) + 1;
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
          qty: 1,
        });

        this.$toast(`Added "${item.title}" to label queue`, "success");
      }

      this.clearSearch();
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

    // Modal Methods
    openBrowseModal() {
      this.modalSearchTerm = "";
      this.modalCategoryFilter = "";
      this.selectedModalItemIds = [];

      // Fetch all items if not loaded
      if (this.allItems.length === 0) {
        this.$root.spinner = true;
        axios
          .get("item?allData=true")
          .then((res) => {
            this.allItems = res.data || [];
          })
          .finally(() => {
            this.$root.spinner = false;
          });
      }

      const modalEl = document.getElementById("browseItemsModal");
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      }
    },

    filterModalItems() {
      // computed handles filtering
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

    addSelectedModalItems() {
      const selected = this.allItems.filter((i) => this.selectedModalItemIds.includes(i.id));
      selected.forEach((item) => {
        const existing = this.labelQueue.find((q) => q.id === item.id);
        if (existing) {
          existing.qty += 1;
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
            qty: 1,
          });
        }
      });

      this.$toast(`Added ${selected.length} product(s) to label queue`, "success");

      const modalEl = document.getElementById("browseItemsModal");
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      }
    },

    printLabels() {
      if (this.generatedStickersList.length === 0) {
        this.$toast("Please add at least one product sticker to print", "warning");
        return;
      }

      // Trigger custom print on #labelPrintArea
      this.print("labelPrintArea", "Barcode Labels (" + this.totalStickersCount + " Stickers)");
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
  min-height: 180px;
  padding: 12px 10px;
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
  width: 245px;
  min-height: 135px;
  text-align: center;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #111;
  transition: all 0.2s ease;
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
  min-height: 40px;
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

/* Continuous Thermal Roll (1 column) */
.layout-thermal {
  display: block;
}
.layout-thermal .sticker-item {
  width: 190px;
  min-height: 105px;
  margin: 0 auto 12px auto;
  page-break-inside: avoid;
}

/* 2-Column Sheet */
.layout-2col .sticker-item {
  width: 48%;
  margin: 1%;
  page-break-inside: avoid;
}

/* 3-Column Sheet (38x25mm) */
.layout-3col .sticker-item {
  width: 31.3%;
  margin: 1%;
  page-break-inside: avoid;
}

/* 4-Column Sheet (30x20mm) */
.layout-4col .sticker-item {
  width: 23%;
  margin: 1%;
  page-break-inside: avoid;
}

/* Grid Layout */
.layout-grid .sticker-item {
  width: 160px;
  margin: 5px;
  page-break-inside: avoid;
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
