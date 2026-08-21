<template>
  <div class="pos-container p-2 bg-light min-vh-100">
    <!-- POS Top Header Bar -->
    <div class="card border-0 shadow-sm mb-2 text-white" style="background-color: #112C47;">
      <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <h4 class="mb-0 fw-bold text-white"><i class="fas fa-cash-register me-2 text-warning"></i>QTerminal</h4>
          <span class="badge bg-secondary font-monospace">{{ currentDate }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <router-link to="/invoice" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 font-monospace">
            <i class="fas fa-file-invoice"></i> Invoices
          </router-link>
          <router-link to="/pos/return" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1 font-monospace">
            <i class="fas fa-undo"></i> Sales Return
          </router-link>
          <router-link to="/admin/dashboard" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <i class="fas fa-tachometer-alt"></i> Dashboard
          </router-link>
        </div>
      </div>
    </div>

    <!-- 👤 Sleek Horizontal Client Info Bar (Compact & Flat) -->
    <div class="card border-0 shadow-sm mb-2">
      <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Client Mobile Search Input (Compact, rgb(17 44 70) Theme Color) -->
          <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 240px; max-width: 320px;">
            <span class="fw-bold small text-nowrap" style="color: rgb(17 44 70);"><i class="fas fa-user theme-icon me-1"></i>Client (F4):</span>
            <div class="input-group input-group-sm client-search-group">
              <input
                ref="clientMobileInput"
                type="text"
                class="form-control form-control-sm font-monospace fw-bold client-input"
                placeholder="Mobile: 017xxxxxxxx"
                v-model="client.mobile"
                @keyup.enter="searchCustomer"
                @blur="searchCustomer"
              >
              <button type="button" class="btn client-search-btn" @click="searchCustomer" title="Search Client">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>

          <!-- Selected Customer Info Pill -->
          <div v-if="client.id" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-between bg-light p-1 px-3 rounded border">
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold text-dark fs-6">{{ client.name }}</span>
              <small class="text-muted font-monospace"><i class="fas fa-phone-alt theme-icon me-1"></i>{{ client.mobile }}</small>
              <small class="text-muted" v-if="client.address && client.address !== 'N/A'">({{ client.address }})</small>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-danger bg-opacity-10 text-danger border border-danger font-monospace px-2 py-1" v-if="client.current_due > 0">
                Due: Tk. {{ formatPrice(client.current_due) }}
              </span>
              <span class="badge bg-warning text-dark border font-monospace px-2 py-1" v-if="client.coupon_enabled">
                <i class="fas fa-gift me-1"></i>{{ formatPrice(client.points_balance || 0) }} Pts (≈ Tk. {{ formatPrice(client.points_value_in_tk || 0) }})
              </span>
              <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" @click="resetClient" title="Clear / Change Customer">
                <i class="fas fa-times me-1"></i>Change
              </button>
            </div>
          </div>

          <!-- Quick New Client Registration Inline Form if Not Found -->
          <div v-else-if="showNewClientForm" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 bg-warning bg-opacity-10 p-1 px-2 rounded border border-warning">
            <span class="small fw-bold text-dark text-nowrap"><i class="fas fa-user-plus me-1 text-warning"></i>New:</span>
            <input
              type="text"
              class="form-control form-control-sm font-monospace fw-bold client-input bg-light"
              placeholder="Mobile *"
              v-model="newClient.mobile"
              maxlength="11"
              readonly
              style="max-width: 125px; cursor: not-allowed;"
              title="Mobile number cannot be changed"
            >
            <input
              ref="newClientNameInput"
              type="text"
              class="form-control form-control-sm client-input"
              placeholder="Client Name *"
              v-model="newClient.name"
              @keyup.enter="createQuickCustomer"
              style="max-width: 150px;"
            >
            <input
              type="text"
              class="form-control form-control-sm client-input flex-grow-1"
              placeholder="Address / Location (optional)"
              v-model="newClient.address"
              @keyup.enter="createQuickCustomer"
              style="min-width: 180px; max-width: 300px;"
            >
            <button type="button" class="btn client-save-btn text-nowrap" @click="createQuickCustomer" title="Save Client (Press Enter / Ctrl+Enter)">
              <i class="fas fa-save me-1"></i>Save <small class="text-white-50 ms-1">[Enter]</small>
            </button>
            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" @click="showNewClientForm = false" title="Cancel (Esc)">Cancel</button>
          </div>

          <!-- Default Walk-in Customer Hint -->
          <div v-else class="text-muted small d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-2 py-1">
              <i class="fas fa-walking theme-icon me-1"></i>Walk-in Customer (ডিফল্ট গ্রাহক)
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 🌟 Main 2-Column POS Workspace: Left (Search & Cart Table in Unified Card) + Right (Payment & Checkout) -->
    <div class="row g-2 align-items-start">
      <!-- Left Column: Item Search & Cart Table (Aligned in a Single Card) -->
      <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card border-0 shadow-sm mb-2 h-100">
          <!-- Item Search Bar Section (⭐️ Highlighted) -->
          <div class="card-body p-3 pb-2">
            <div class="position-relative">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small mb-0 d-flex align-items-center gap-1" style="color: rgb(17 44 70);">
                  <i class="fas fa-search theme-icon"></i>
                  <span>Search Item / Scan Barcode (F2)</span>
                </label>
                <span class="text-muted" style="font-size: 11px;"><kbd class="bg-dark text-white">↑</kbd> <kbd class="bg-dark text-white">↓</kbd> = Navigate | <kbd class="bg-dark text-white">Enter</kbd> = Select</span>
              </div>
              <div class="item-search-bar d-flex align-items-stretch">
                <span class="search-barcode-icon d-flex align-items-center justify-content-center px-3">
                  <i class="fas fa-barcode fs-5 theme-icon"></i>
                </span>
                <input
                  ref="itemSearchInput"
                  type="text"
                  class="form-control item-search-input"
                  placeholder="Type product title, SKU, or scan barcode... (Press F2 to focus)"
                  v-model="searchTerm"
                  @input="onSearchInput"
                  @keydown.down.prevent="navigateSearchResults(1)"
                  @keydown.up.prevent="navigateSearchResults(-1)"
                  @keydown.enter.prevent="handleSearchEnter"
                  @keydown.esc="clearSearch"
                >
                <button type="button" class="btn btn-clear-search px-3" @click="clearSearch" v-if="searchTerm" title="Clear search">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Search Results Dropdown with Arrow Keyboard Navigation -->
              <div v-if="searchResults.length > 0" class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 search-dropdown" style="max-height: 320px; overflow-y: auto; z-index: 9999;">
                <div
                  v-for="(item, idx) in searchResults"
                  :key="item.id"
                  :id="'search-item-' + idx"
                  class="p-2 border-bottom cursor-pointer d-flex align-items-center justify-content-between transition-all"
                  :class="{ 'active-search-row': selectedSearchIndex === idx, 'hover-bg-light': selectedSearchIndex !== idx }"
                  @click="openItemModal(item)"
                  @mouseenter="selectedSearchIndex = idx"
                >
                  <div>
                    <div class="fw-bold" :class="selectedSearchIndex === idx ? 'text-white' : 'text-dark'">{{ item.title }}</div>
                    <small :class="selectedSearchIndex === idx ? 'text-white-50' : 'text-muted'" class="font-monospace me-2">Barcode: {{ item.barcode }}</small>
                    <span class="badge search-category-badge" :class="selectedSearchIndex === idx ? 'badge-on-dark' : 'badge-on-light'" v-if="item.category">
                      {{ item.category.title }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <span class="small font-monospace" :class="selectedSearchIndex === idx ? 'text-white-50' : 'text-muted'" style="font-size: 11px;">[Enter to Select]</span>
                    <button type="button" class="btn btn-xs" :class="selectedSearchIndex === idx ? 'btn-light fw-bold text-dark' : 'btn-primary'">
                      Select Item
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cart Table in Perfect Alignment with Search Bar -->
          <div class="card-body p-0 table-responsive border-top mt-1" style="min-height: 250px; max-height: calc(100vh - 280px); overflow-y: auto;">
            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 13px;">
              <thead class="table-light sticky-top" style="z-index: 2;">
                <tr>
                  <th style="width: 26%;">Item Title (পণ্যের নাম)</th>
                  <th style="width: 18%;">Color / Size</th>
                  <th style="width: 15%;" v-if="isElectronicsShop">Serial No</th>
                  <th style="width: 11%;" class="text-center">Qty</th>
                  <th style="width: 13%;" class="text-end">Price (দর)</th>
                  <th style="width: 12%;" class="text-end">Total (মোট)</th>
                  <th style="width: 5%;" class="text-center">Act</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(cItem, idx) in cart" :key="idx">
                  <td>
                    <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" :title="cItem.title">{{ cItem.title }}</div>
                    <small class="text-muted font-monospace" style="font-size: 11px;">{{ cItem.barcode }}</small>
                  </td>
                  <td>
                    <span class="badge bg-info text-dark me-1" v-if="cItem.color_title" style="font-size: 11px;">{{ cItem.color_title }}</span>
                    <span class="badge bg-secondary me-1" v-if="cItem.size_title" style="font-size: 11px;">{{ cItem.size_title }}</span>
                    <span v-if="!cItem.color_title && !cItem.size_title" class="text-muted small">Standard</span>
                  </td>
                  <td v-if="isElectronicsShop">
                    <input type="text" class="form-control form-control-sm font-monospace p-1" style="max-width: 110px; font-size: 11px;" v-model="cItem.serial_no" placeholder="Optional">
                  </td>
                  <td class="text-center">
                    <input type="number" min="1" class="form-control form-control-sm text-center fw-bold p-1 mx-auto" style="max-width: 60px; font-size: 13px;" v-model.number="cItem.qty">
                  </td>
                  <td class="text-end font-monospace">
                    <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace p-1 ms-auto" style="max-width: 80px; font-size: 13px;" v-model.number="cItem.rate">
                  </td>
                  <td class="text-end font-monospace fw-bold fs-6" style="color: rgb(17 44 70);">
                    {{ formatPrice(cItem.qty * cItem.rate) }}
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" @click="removeCartItem(idx)" title="Remove">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="cart.length === 0">
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-shopping-basket fa-2x mb-2 text-secondary opacity-50"></i>
                    <p class="mb-0 small">Cart is empty. Search items above or scan barcode (F2) to add products.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Compact Cart Footer Strip (Total Items & Clear Cart) -->
          <div class="card-footer bg-light py-1 px-3 d-flex justify-content-between align-items-center border-top small text-muted">
            <span>
              <i class="fas fa-shopping-cart me-1 theme-icon"></i>Cart Items: <strong class="text-dark font-monospace">{{ cart.length }}</strong> (Total Qty: <strong class="text-dark font-monospace">{{ cartTotalQty }}</strong>)
            </span>
            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2" @click="clearCart" v-if="cart.length > 0" title="Clear all cart items">
              <i class="fas fa-trash-alt me-1"></i>Clear Cart
            </button>
          </div>
        </div>
      </div>

      <!-- Right Column: Payment & Checkout Summary (Flat & Space-Optimized) -->
      <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-3">
            <!-- Calculations Breakdown -->
            <div class="p-2 px-3 bg-light rounded border mb-2">
              <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span class="text-muted small">Subtotal:</span>
                <span class="fw-bold font-monospace">Tk. {{ formatPrice(cartSubtotal) }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span class="text-muted small">Discount (ছাড়):</span>
                <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace py-0 px-2" style="max-width: 110px; height: 28px;" v-model.number="discount" placeholder="0.00">
              </div>

              <!-- 🎁 Redeem Points Section (If points exist) -->
              <div v-if="client.coupon_enabled && client.points_balance > 0" class="p-1 px-2 my-1 bg-warning bg-opacity-10 border border-warning rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 11px;">
                    <i class="fas fa-gift text-warning"></i> Redeem (Max: {{ maxRedeemablePoints }} Pts):
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-dark py-0 px-1" style="font-size: 9px;" @click="redeemAllPoints">
                    All
                  </button>
                </div>
                <div class="input-group input-group-sm">
                  <input type="number" min="0" :max="maxRedeemablePoints" class="form-control font-monospace text-center fw-bold py-0" style="height: 26px;" placeholder="0" v-model.number="points_to_redeem">
                  <span class="input-group-text bg-white small font-monospace text-success fw-bold py-0 px-1" style="font-size: 11px;">- Tk. {{ formatPrice(pointsDiscountAmount) }}</span>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span class="text-muted small">VAT / Tax:</span>
                <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace py-0 px-2" style="max-width: 110px; height: 28px;" v-model.number="vat" placeholder="0.00">
              </div>

              <div class="d-flex justify-content-between align-items-center pt-2">
                <span class="fw-bold text-dark fs-6">Net Payable:</span>
                <span class="fw-bold font-monospace fs-5 text-success">Tk. {{ formatPrice(netPayable) }}</span>
              </div>
            </div>

            <!-- Payment Method & Paid Amount in Compact Row -->
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label fw-bold text-muted mb-0" style="font-size: 11px;">Payment Method</label>
                <select class="form-select form-select-sm font-monospace fw-bold py-1" style="height: 32px;" v-model="payment_method">
                  <option value="Cash">Cash (নগদ)</option>
                  <option value="Card">Card</option>
                  <option value="bKash">bKash (বিকাশ)</option>
                  <option value="Nagad">Nagad (নগদ)</option>
                  <option value="Rocket">Rocket (রকেট)</option>
                  <option value="Bank">Bank Transfer</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label fw-bold text-muted mb-0" style="font-size: 11px;">Paid Amount (প্রদত্ত টাকা)</label>
                <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace fw-bold text-primary py-1" style="height: 32px; font-size: 14px;" v-model.number="paid_amount">
              </div>
              <div class="col-12" v-if="payment_method !== 'Cash'">
                <input type="text" class="form-control form-control-sm font-monospace py-1" style="height: 28px;" placeholder="TrxID / Reference No." v-model="trxid">
              </div>
            </div>

            <!-- Change Return Amount (Compact Line) -->
            <div class="d-flex justify-content-between align-items-center p-2 bg-light border rounded mb-3">
              <span class="text-muted fw-bold small">Change (ফেরত):</span>
              <span class="fw-bold font-monospace fs-6" :class="changeAmount >= 0 ? 'text-success' : 'text-danger'">
                Tk. {{ formatPrice(changeAmount) }}
              </span>
            </div>

            <!-- ⭐️ Complete Sale & Print Button (Prominent & Always Visible) -->
            <button
              type="button"
              class="btn btn-success btn-lg w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
              @click="submitCheckout"
              :disabled="cart.length === 0 || isSubmitting"
            >
              <i class="fas fa-print"></i> Complete Sale & Print (F8)
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Popup for Item Color, Size & Serial Selection with Full Mouseless Keyboard Control -->
    <div
      v-if="showItemModal"
      class="modal fade show d-block tab-modal-backdrop"
      tabindex="-1"
      style="background: rgba(0,0,0,0.55);"
      @keydown.esc="closeItemModal"
      @keydown.ctrl.enter="addToCartFromModal"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark text-white py-2">
            <h5 class="modal-title fw-bold fs-6"><i class="fas fa-box-open me-2"></i>Select Color, Size & Serial</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeItemModal"></button>
          </div>
          <div class="modal-body p-3" v-if="activeItem">
            <div class="d-flex align-items-center gap-3 mb-3 p-2 border rounded bg-light">
              <img v-if="activeItem.image" :src="activeItem.image" class="img-fluid rounded border" style="height: 60px;" alt="Product">
              <div>
                <h6 class="fw-bold text-dark mb-1">{{ activeItem.title }}</h6>
                <small class="text-muted font-monospace me-2">Barcode: {{ activeItem.barcode }}</small>
                <span class="badge bg-dark" v-if="activeItem.unit">{{ activeItem.unit.title }}</span>
              </div>
            </div>

            <div class="row g-3">
              <!-- Color Selection -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Color (রং)</label>
                <select
                  ref="modalColorSelect"
                  class="form-select form-select-sm"
                  v-model="modalSelection.color_id"
                  @change="onVariantChange"
                  @keydown.enter.prevent="focusNextModalInput('size')"
                >
                  <option :value="null">-- Standard / Any Color --</option>
                  <option v-for="c in availableColors" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <!-- Size Selection -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Size (সাইজ)</label>
                <select
                  ref="modalSizeSelect"
                  class="form-select form-select-sm"
                  v-model="modalSelection.size_id"
                  @change="onVariantChange"
                  @keydown.enter.prevent="focusNextModalInput('qty')"
                >
                  <option :value="null">-- Standard / Any Size --</option>
                  <option v-for="s in availableSizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>

              <!-- Stock & Price Info -->
              <div class="col-12">
                <div class="p-2 border rounded d-flex align-items-center justify-content-between" :class="modalSelection.available_stock > 0 ? 'bg-white' : 'bg-danger bg-opacity-10 border-danger'">
                  <span class="small font-monospace">Available Stock: <strong :class="modalSelection.available_stock > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'">{{ modalSelection.available_stock }} {{ modalSelection.available_stock <= 0 ? '(Out of Stock)' : '' }}</strong></span>
                  <span class="small font-monospace">Selling Price: <strong class="text-success">Tk. {{ formatPrice(modalSelection.rate) }}</strong></span>
                </div>
              </div>

              <!-- Serial No (For Electronics / Serialized items) -->
              <div class="col-12" v-if="isElectronicsShop">
                <label class="form-label fw-bold small text-muted">Serial No. (ইলেকট্রনিক্স পণ্যের জন্য সিরিয়াল নং)</label>
                <input
                  ref="modalSerialInput"
                  type="text"
                  class="form-control form-control-sm font-monospace"
                  placeholder="Enter Serial No if applicable"
                  v-model="modalSelection.serial_no"
                  @keydown.enter.prevent="focusNextModalInput('qty')"
                >
              </div>

              <!-- Selling Price (Editable) -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Unit Rate (দর)</label>
                <input
                  ref="modalRateInput"
                  type="number"
                  step="0.01"
                  class="form-control form-control-sm font-monospace text-end"
                  v-model.number="modalSelection.rate"
                  @keydown.enter.prevent="addToCartFromModal"
                >
              </div>

              <!-- Quantity -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">Quantity (পরিমাণ) <span class="text-primary">[Enter = Add]</span></label>
                <input
                  ref="modalQtyInput"
                  type="number"
                  min="1"
                  :max="modalSelection.available_stock > 0 ? modalSelection.available_stock : 9999"
                  class="form-control form-control-sm font-monospace text-center fw-bold"
                  v-model.number="modalSelection.qty"
                  @keydown.enter.prevent="addToCartFromModal"
                >
              </div>
            </div>
          </div>
          <div class="modal-footer py-2 d-flex justify-content-between align-items-center">
            <div class="small text-muted">
              <kbd>Enter</kbd> / <kbd>Ctrl+Enter</kbd> = Add to Cart | <kbd>Esc</kbd> = Close
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-secondary" @click="closeItemModal">Cancel (Esc)</button>
              <button ref="modalAddBtn" type="button" class="btn btn-sm btn-primary px-4 fw-bold shadow-sm" @click="addToCartFromModal">
                <i class="fas fa-cart-plus me-1"></i> Add to Cart (Enter)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden Printable POS Sales Receipt / Invoice (Dynamic Formats based on Site Settings) -->
    <div id="posInvoicePrintArea" class="d-none" v-if="completedInvoice">
      <!-- 1. 🖨️ Thermal 80mm Layout (Standard 3-Inch POS Receipt) -->
      <div v-if="effectivePrintFormat === 'thermal-80mm'" class="thermal-80mm-receipt" style="width: 78mm; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; line-height: 1.35; padding: 4px; margin: 0 auto; color: #000;">
        <div style="text-align: center; margin-bottom: 8px;">
          <h2 style="font-size: 16px; font-weight: bold; margin: 0 0 2px 0; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
          <div style="font-size: 10px; line-height: 1.2;">{{ $root.site?.address || '' }}</div>
          <div style="font-size: 10px;">Phone: {{ $root.site?.mobile1 || '' }} <span v-if="$root.site?.mobile2">/ {{ $root.site?.mobile2 }}</span></div>
          <div style="font-size: 10px;" v-if="$root.site?.vat_no">VAT Reg: {{ $root.site?.vat_no }}</div>
          <div style="font-size: 12px; font-weight: bold; margin-top: 5px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 3px 0; letter-spacing: 1px;">
            SALES RECEIPT
          </div>
        </div>

        <div style="margin-bottom: 6px; font-size: 10px; line-height: 1.3;">
          <div style="display: flex; justify-content: space-between;">
            <span><strong>Inv #:</strong> {{ completedInvoice.invoice_no }}</span>
            <span><strong>Date:</strong> {{ completedInvoice.invoice_date }}</span>
          </div>
          <div><strong>Customer:</strong> {{ completedInvoice.client ? completedInvoice.client.name : 'Walk-in Customer' }}</div>
          <div v-if="completedInvoice.client && completedInvoice.client.mobile"><strong>Mobile:</strong> {{ completedInvoice.client.mobile }}</div>
          <div><strong>Payment:</strong> {{ completedInvoice.payment_method || 'Cash' }} <span v-if="completedInvoice.trxid">(Trx: {{ completedInvoice.trxid }})</span></div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10px;">
          <thead>
            <tr style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
              <th style="text-align: left; padding: 4px 0;">Item Description</th>
              <th style="text-align: center; padding: 4px 0; width: 30px;">Qty</th>
              <th style="text-align: right; padding: 4px 0; width: 48px;">Rate</th>
              <th style="text-align: right; padding: 4px 0; width: 55px;">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in completedInvoice.details" :key="d.id" style="border-bottom: 1px dashed #ddd;">
              <td style="padding: 3px 0;">
                <div style="font-weight: 600;">{{ d.item ? d.item.title : 'Item' }}</div>
                <div style="font-size: 9px; color: #333;" v-if="d.color || d.size">
                  {{ d.color ? d.color.title : '' }} {{ d.size ? '/' + d.size.title : '' }}
                </div>
                <div style="font-size: 9px; color: #222; font-family: monospace;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
                <div style="font-size: 9px; color: #000; font-weight: bold;" v-if="d.item && d.item.warranty_type && d.item.warranty_type !== 'none'">
                  [{{ d.item.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}: {{ d.item.warranty_period }}]
                </div>
              </td>
              <td style="text-align: center; padding: 3px 0; vertical-align: top;">{{ d.qty }}</td>
              <td style="text-align: right; padding: 3px 0; vertical-align: top;">{{ formatPrice(d.amount) }}</td>
              <td style="text-align: right; padding: 3px 0; vertical-align: top; font-weight: 600;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <div style="border-top: 1px solid #000; padding-top: 4px; font-size: 10.5px; line-height: 1.4;">
          <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>Tk. {{ formatPrice(completedInvoice.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.discount > 0">
            <span>Special Discount:</span>
            <span>- Tk. {{ formatPrice(completedInvoice.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.vat > 0">
            <span>VAT / Tax:</span>
            <span>+ Tk. {{ formatPrice(completedInvoice.vat) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 3px;">
            <span>NET PAYABLE:</span>
            <span>Tk. {{ formatPrice(completedInvoice.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid Amount:</span>
            <span>Tk. {{ formatPrice(completedInvoice.paid_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="(completedInvoice.paid_amount - completedInvoice.amount) > 0">
            <span>Change / Return:</span>
            <span>Tk. {{ formatPrice(completedInvoice.paid_amount - completedInvoice.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="(completedInvoice.amount - completedInvoice.paid_amount) > 0">
            <span style="font-weight: bold; color: red;">Balance Due:</span>
            <span style="font-weight: bold;">Tk. {{ formatPrice(completedInvoice.amount - completedInvoice.paid_amount) }}</span>
          </div>

          <!-- ⭐️ Customer Loyalty Points in Receipt -->
          <div v-if="completedInvoice.coupon_enabled" style="margin-top: 5px; border-top: 1px dashed #000; padding-top: 4px; font-size: 9.5px;">
            <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.points_redeemed > 0">
              <span>Points Redeemed:</span>
              <span>- {{ completedInvoice.points_redeemed }} Pts</span>
            </div>
            <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.points_earned > 0">
              <span>Points Earned Today:</span>
              <span>+ {{ completedInvoice.points_earned }} Pts</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
              <span>Total Points Balance:</span>
              <span>{{ formatPrice(completedInvoice.points_balance) }} Pts</span>
            </div>
          </div>
        </div>

        <div style="text-align: center; margin-top: 12px; border-top: 1px dashed #000; padding-top: 6px; font-size: 9.5px; line-height: 1.3;">
          <div style="font-weight: bold;">Thank you for shopping with us!</div>
          <div>Please preserve this receipt for warranty and returns within 7 days.</div>
          <div style="font-size: 8.5px; color: #555; margin-top: 3px;">Software by QPOS</div>
        </div>
      </div>

      <!-- 2. 🖨️ Thermal 60mm / 58mm Layout (Compact 2-Inch POS Receipt) -->
      <div v-else-if="effectivePrintFormat === 'thermal-60mm'" class="thermal-60mm-receipt" style="width: 56mm; font-family: monospace, Arial; font-size: 9.5px; line-height: 1.25; padding: 2px; margin: 0 auto; color: #000;">
        <div style="text-align: center; margin-bottom: 5px;">
          <h2 style="font-size: 13px; font-weight: bold; margin: 0 0 1px 0; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
          <div style="font-size: 8.5px; line-height: 1.1;">{{ $root.site?.address || '' }}</div>
          <div style="font-size: 8.5px;">Mob: {{ $root.site?.mobile1 || '' }}</div>
          <div style="font-size: 10px; font-weight: bold; margin-top: 3px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 2px 0;">
            SALES RECEIPT
          </div>
        </div>

        <div style="margin-bottom: 4px; font-size: 8.5px; line-height: 1.2;">
          <div><strong>Inv:</strong> {{ completedInvoice.invoice_no }}</div>
          <div><strong>Date:</strong> {{ completedInvoice.invoice_date }}</div>
          <div><strong>Cust:</strong> {{ completedInvoice.client ? completedInvoice.client.name : 'Walk-in' }}</div>
          <div v-if="completedInvoice.client && completedInvoice.client.mobile"><strong>Ph:</strong> {{ completedInvoice.client.mobile }}</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 8.5px;">
          <thead>
            <tr style="border-bottom: 1px solid #000; border-top: 1px solid #000;">
              <th style="text-align: left; padding: 2px 0;">Item</th>
              <th style="text-align: center; padding: 2px 0; width: 18px;">Q</th>
              <th style="text-align: right; padding: 2px 0; width: 40px;">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in completedInvoice.details" :key="d.id" style="border-bottom: 1px dashed #eee;">
              <td style="padding: 2px 0;">
                <div style="font-weight: 600;">{{ d.item ? d.item.title : 'Item' }}</div>
                <div style="font-size: 8px; color: #333;" v-if="d.color || d.size">
                  {{ d.color ? d.color.title : '' }}{{ d.size ? '/' + d.size.title : '' }}
                </div>
                <div style="font-size: 8px; color: #222;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
                <div style="font-size: 8px;" v-if="d.item && d.item.warranty_type && d.item.warranty_type !== 'none'">
                  W: {{ d.item.warranty_period }}
                </div>
              </td>
              <td style="text-align: center; padding: 2px 0; vertical-align: top;">{{ d.qty }}</td>
              <td style="text-align: right; padding: 2px 0; vertical-align: top; font-weight: 600;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <div style="border-top: 1px solid #000; padding-top: 3px; font-size: 9px; line-height: 1.3;">
          <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>{{ formatPrice(completedInvoice.original_amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.discount > 0">
            <span>Discount:</span>
            <span>-{{ formatPrice(completedInvoice.discount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;" v-if="completedInvoice.vat > 0">
            <span>VAT:</span>
            <span>+{{ formatPrice(completedInvoice.vat) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 10.5px; border-top: 1px dashed #000; padding-top: 2px; margin-top: 2px;">
            <span>NET TOTAL:</span>
            <span>Tk. {{ formatPrice(completedInvoice.amount) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Paid:</span>
            <span>Tk. {{ formatPrice(completedInvoice.paid_amount) }}</span>
          </div>

          <div v-if="completedInvoice.coupon_enabled" style="margin-top: 3px; border-top: 1px dashed #000; padding-top: 2px; font-size: 8px;">
            <div style="display: flex; justify-content: space-between;">
              <span>Points Earned:</span>
              <span>+{{ completedInvoice.points_earned }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
              <span>Points Balance:</span>
              <span>{{ formatPrice(completedInvoice.points_balance) }}</span>
            </div>
          </div>
        </div>

        <div style="text-align: center; margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px; font-size: 8px;">
          <div>Thanks for visiting!</div>
          <div>Preserve receipt for returns.</div>
        </div>
      </div>

      <!-- 3. 🖨️ Normal Printer A5 Layout (Compact Half-Page Invoice) -->
      <div v-else-if="effectivePrintFormat === 'normal-a5'" class="normal-a5-invoice" style="width: 100%; max-width: 140mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; line-height: 1.3; color: #111; margin: 0 auto; padding: 5px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #112C47; padding-bottom: 8px; margin-bottom: 8px;">
          <div>
            <h2 style="font-size: 17px; font-weight: bold; margin: 0; color: #112C47;">{{ $root.site?.title || 'QPOS STORE' }}</h2>
            <div style="font-size: 10px; color: #444;">{{ $root.site?.address || '' }}</div>
            <div style="font-size: 10px; color: #444;">Phone: {{ $root.site?.mobile1 || '' }} | Email: {{ $root.site?.contact_email || '' }}</div>
            <div style="font-size: 9.5px; color: #555;" v-if="$root.site?.vat_no">VAT/BIN: {{ $root.site?.vat_no }}</div>
          </div>
          <div style="text-align: right;">
            <div style="display: inline-block; background: #112C47; color: #fff; font-size: 11px; font-weight: bold; padding: 2px 10px; border-radius: 3px; letter-spacing: 0.5px;">
              SALES INVOICE
            </div>
            <div style="font-size: 10.5px; font-weight: bold; margin-top: 4px; font-family: monospace;">#{{ completedInvoice.invoice_no }}</div>
            <div style="font-size: 9.5px; color: #555;">Date: {{ completedInvoice.invoice_date }}</div>
          </div>
        </div>

        <!-- Bill To / Customer Details -->
        <div style="display: flex; justify-content: space-between; background: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 10px; margin-bottom: 8px; font-size: 10px;">
          <div>
            <strong>Bill To (গ্রাহক):</strong>
            <div style="font-weight: 600; font-size: 11px;">{{ completedInvoice.client ? completedInvoice.client.name : 'Walk-in Customer' }}</div>
            <div v-if="completedInvoice.client?.mobile">Mobile: {{ completedInvoice.client.mobile }}</div>
            <div v-if="completedInvoice.client?.address">Address: {{ completedInvoice.client.address }}</div>
          </div>
          <div style="text-align: right;">
            <div><strong>Payment Method:</strong> {{ completedInvoice.payment_method || 'Cash' }}</div>
            <div v-if="completedInvoice.trxid"><strong>TrxID:</strong> {{ completedInvoice.trxid }}</div>
            <div><strong>Status:</strong> <span style="font-weight: bold; color: green;">PAID</span></div>
          </div>
        </div>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10px;">
          <thead>
            <tr style="background: #112C47; color: #fff;">
              <th style="border: 1px solid #112C47; padding: 4px 6px; text-align: center; width: 25px;">#</th>
              <th style="border: 1px solid #112C47; padding: 4px 6px; text-align: left;">Item Description & Details</th>
              <th style="border: 1px solid #112C47; padding: 4px 6px; text-align: center; width: 35px;">Qty</th>
              <th style="border: 1px solid #112C47; padding: 4px 6px; text-align: right; width: 55px;">Rate (৳)</th>
              <th style="border: 1px solid #112C47; padding: 4px 6px; text-align: right; width: 65px;">Total (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, idx) in completedInvoice.details" :key="d.id">
              <td style="border: 1px solid #cbd5e1; padding: 4px; text-align: center;">{{ idx + 1 }}</td>
              <td style="border: 1px solid #cbd5e1; padding: 4px 6px;">
                <div style="font-weight: 600;">{{ d.item ? d.item.title : 'Item' }}</div>
                <div style="font-size: 9px; color: #475569;" v-if="d.color || d.size">
                  Variant: {{ d.color ? d.color.title : '' }} {{ d.size ? '/' + d.size.title : '' }}
                </div>
                <div style="font-size: 9px; color: #0284c7; font-family: monospace;" v-if="d.serial_no">
                  S/N: {{ d.serial_no }}
                </div>
                <div style="font-size: 9px; color: #16a34a; font-weight: 600;" v-if="d.item && d.item.warranty_type && d.item.warranty_type !== 'none'">
                  {{ d.item.warranty_type === 'guarantee' ? 'Guarantee' : 'Warranty' }}: {{ d.item.warranty_period }}
                </div>
              </td>
              <td style="border: 1px solid #cbd5e1; padding: 4px; text-align: center; font-weight: bold;">{{ d.qty }}</td>
              <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: right; font-family: monospace;">{{ formatPrice(d.amount) }}</td>
              <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: right; font-weight: bold; font-family: monospace;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Summary & Totals -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; font-size: 10px;">
          <!-- Left Notes & Loyalty Points -->
          <div style="width: 52%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px;">
            <div v-if="completedInvoice.coupon_enabled" style="margin-bottom: 4px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px;">
              <strong style="color: #d97706;">Loyalty Points:</strong>
              Earned: <strong>+{{ completedInvoice.points_earned }}</strong> | Balance: <strong>{{ formatPrice(completedInvoice.points_balance) }} Pts</strong>
            </div>
            <div style="font-size: 8.5px; color: #64748b; line-height: 1.2;">
              <div>* Goods once sold cannot be returned without original cash memo within 7 days.</div>
              <div>* Physical and liquid damage will void any item warranty.</div>
            </div>
          </div>

          <!-- Right Calculation Box -->
          <div style="width: 44%;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
              <tbody>
                <tr>
                  <td style="padding: 2px 4px;">Subtotal:</td>
                  <td style="padding: 2px 4px; text-align: right; font-family: monospace;">৳ {{ formatPrice(completedInvoice.original_amount) }}</td>
                </tr>
                <tr v-if="completedInvoice.discount > 0">
                  <td style="padding: 2px 4px; color: #dc2626;">Discount:</td>
                  <td style="padding: 2px 4px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(completedInvoice.discount) }}</td>
                </tr>
                <tr v-if="completedInvoice.vat > 0">
                  <td style="padding: 2px 4px;">VAT / Tax:</td>
                  <td style="padding: 2px 4px; text-align: right; font-family: monospace;">+ ৳ {{ formatPrice(completedInvoice.vat) }}</td>
                </tr>
                <tr style="border-top: 1px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 11px;">
                  <td style="padding: 4px;">Net Payable:</td>
                  <td style="padding: 4px; text-align: right; color: #112C47; font-family: monospace;">৳ {{ formatPrice(completedInvoice.amount) }}</td>
                </tr>
                <tr>
                  <td style="padding: 2px 4px;">Paid Amount:</td>
                  <td style="padding: 2px 4px; text-align: right; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(completedInvoice.paid_amount) }}</td>
                </tr>
                <tr v-if="(completedInvoice.amount - completedInvoice.paid_amount) > 0">
                  <td style="padding: 2px 4px; color: #dc2626; font-weight: bold;">Due Amount:</td>
                  <td style="padding: 2px 4px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(completedInvoice.amount - completedInvoice.paid_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Signatures -->
        <div style="display: flex; justify-content: space-between; margin-top: 25px; padding-top: 5px; font-size: 9px; color: #333;">
          <div style="border-top: 1px dashed #64748b; width: 35%; text-align: center; padding-top: 3px;">Customer's Signature</div>
          <div style="border-top: 1px dashed #64748b; width: 35%; text-align: center; padding-top: 3px;">Authorized Signature & Seal</div>
        </div>
      </div>

      <!-- 4. 🖨️ Normal Printer A4 Layout (Full Professional A4 Corporate Invoice) -->
      <div v-else class="normal-a4-invoice" style="width: 100%; max-width: 190mm; font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #111; margin: 0 auto; padding: 10px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #112C47; padding-bottom: 12px; margin-bottom: 12px;">
          <div>
            <h1 style="font-size: 22px; font-weight: bold; margin: 0 0 4px 0; color: #112C47; text-transform: uppercase;">{{ $root.site?.title || 'QPOS STORE' }}</h1>
            <div style="font-size: 11px; color: #475569; max-width: 380px;">{{ $root.site?.address || '' }}</div>
            <div style="font-size: 11px; color: #475569; margin-top: 2px;">
              <span><strong>Mobile:</strong> {{ $root.site?.mobile1 || '' }} <span v-if="$root.site?.mobile2">/ {{ $root.site?.mobile2 }}</span></span>
              <span v-if="$root.site?.contact_email" style="margin-left: 8px;"><strong>Email:</strong> {{ $root.site?.contact_email }}</span>
            </div>
            <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;" v-if="$root.site?.vat_no || $root.site?.hs_code">
              <span v-if="$root.site?.vat_no"><strong>VAT / BIN:</strong> {{ $root.site?.vat_no }}</span>
              <span v-if="$root.site?.hs_code" style="margin-left: 8px;"><strong>HS Code:</strong> {{ $root.site?.hs_code }}</span>
            </div>
          </div>
          <div style="text-align: right;">
            <div style="display: inline-block; background: #112C47; color: #fff; font-size: 13px; font-weight: bold; padding: 4px 14px; border-radius: 4px; letter-spacing: 1px;">
              RETAIL SALES INVOICE
            </div>
            <div style="font-size: 14px; font-weight: bold; margin-top: 6px; font-family: monospace; color: #112C47;">#{{ completedInvoice.invoice_no }}</div>
            <div style="font-size: 11px; color: #64748b;"><strong>Invoice Date:</strong> {{ completedInvoice.invoice_date }}</div>
            <div style="font-size: 11px; color: #64748b;"><strong>Payment Mode:</strong> {{ completedInvoice.payment_method || 'Cash' }}</div>
          </div>
        </div>

        <!-- Customer & Bill To Box -->
        <div style="display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 12px;">
          <div>
            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #64748b; margin-bottom: 3px;">Bill To (ক্রেতার তথ্য):</div>
            <div style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ completedInvoice.client ? completedInvoice.client.name : 'Walk-in Customer' }}</div>
            <div style="font-size: 11px; color: #475569;" v-if="completedInvoice.client?.mobile"><strong>Mobile:</strong> {{ completedInvoice.client.mobile }}</div>
            <div style="font-size: 11px; color: #475569;" v-if="completedInvoice.client?.address"><strong>Address:</strong> {{ completedInvoice.client.address }}</div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #64748b; margin-bottom: 3px;">Transaction Status:</div>
            <span style="display: inline-block; background: #dcfce7; color: #166534; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; border: 1px solid #bbf7d0;">
              PAID & COMPLETED
            </span>
            <div style="font-size: 11px; color: #475569; margin-top: 4px;" v-if="completedInvoice.trxid"><strong>TrxID:</strong> {{ completedInvoice.trxid }}</div>
          </div>
        </div>

        <!-- Line Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px;">
          <thead>
            <tr style="background: #112C47; color: #fff;">
              <th style="border: 1px solid #112C47; padding: 6px 8px; text-align: center; width: 30px;">#</th>
              <th style="border: 1px solid #112C47; padding: 6px 10px; text-align: left;">Item Description & Specifications</th>
              <th style="border: 1px solid #112C47; padding: 6px 8px; text-align: center; width: 50px;">Qty</th>
              <th style="border: 1px solid #112C47; padding: 6px 10px; text-align: right; width: 85px;">Unit Price (৳)</th>
              <th style="border: 1px solid #112C47; padding: 6px 10px; text-align: right; width: 95px;">Total Amount (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, idx) in completedInvoice.details" :key="d.id">
              <td style="border: 1px solid #cbd5e1; padding: 6px; text-align: center; color: #64748b;">{{ idx + 1 }}</td>
              <td style="border: 1px solid #cbd5e1; padding: 6px 10px;">
                <div style="font-weight: 600; font-size: 12px; color: #0f172a;">{{ d.item ? d.item.title : 'Item' }}</div>
                <div style="font-size: 10.5px; color: #475569;" v-if="d.color || d.size">
                  <span v-if="d.color">Color: <strong>{{ d.color.title }}</strong></span>
                  <span v-if="d.size" style="margin-left: 8px;">Size: <strong>{{ d.size.title }}</strong></span>
                </div>
                <div style="font-size: 10px; color: #0284c7; font-family: monospace; margin-top: 1px;" v-if="d.serial_no">
                  Serial Number: <strong>{{ d.serial_no }}</strong>
                </div>
                <div style="font-size: 10.5px; color: #16a34a; font-weight: 600; margin-top: 1px;" v-if="d.item && d.item.warranty_type && d.item.warranty_type !== 'none'">
                  <i class="fas fa-shield-alt"></i> Coverage: {{ d.item.warranty_type === 'guarantee' ? 'Replacement Guarantee' : 'Official Warranty' }} ({{ d.item.warranty_period }})
                </div>
              </td>
              <td style="border: 1px solid #cbd5e1; padding: 6px; text-align: center; font-weight: bold; font-size: 12px;">{{ d.qty }}</td>
              <td style="border: 1px solid #cbd5e1; padding: 6px 10px; text-align: right; font-family: monospace;">{{ formatPrice(d.amount) }}</td>
              <td style="border: 1px solid #cbd5e1; padding: 6px 10px; text-align: right; font-weight: bold; font-family: monospace; font-size: 12px;">{{ formatPrice(d.total_amount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Summary & Banking / Terms Section -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
          <!-- Left: Terms & Customer Points -->
          <div style="width: 54%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; font-size: 10.5px;">
            <div v-if="completedInvoice.coupon_enabled" style="margin-bottom: 8px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 6px;">
              <strong style="color: #d97706;"><i class="fas fa-gift me-1"></i> Customer Loyalty Rewards:</strong>
              <div style="margin-top: 2px;">
                <span>Points Earned: <strong class="text-success">+{{ completedInvoice.points_earned }} Pts</strong></span>
                <span v-if="completedInvoice.points_redeemed > 0" style="margin-left: 10px;">Redeemed: <strong class="text-danger">-{{ completedInvoice.points_redeemed }} Pts</strong></span>
                <span style="margin-left: 10px;">Available Balance: <strong>{{ formatPrice(completedInvoice.points_balance) }} Pts</strong></span>
              </div>
            </div>

            <div style="font-weight: bold; color: #334155; margin-bottom: 2px;">Terms & Conditions:</div>
            <div style="color: #64748b; font-size: 9.5px; line-height: 1.35;">
              <div>1. Please preserve this invoice for any warranty claims and exchange within 7 days.</div>
              <div>2. Warranty does not cover physical damage, burn, liquid ingress, or broken warranty seals.</div>
              <div>3. Disputed items will be inspected according to company service policy.</div>
            </div>

            <div v-if="$root.site?.bank_name" style="margin-top: 6px; font-size: 9.5px; color: #475569; border-top: 1px dashed #cbd5e1; padding-top: 4px;">
              <strong>Bank Info:</strong> {{ $root.site?.bank_name }} | A/C: {{ $root.site?.account_number }} | Branch: {{ $root.site?.branch_name }}
            </div>
          </div>

          <!-- Right: Totals Table -->
          <div style="width: 42%;">
            <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
              <tbody>
                <tr>
                  <td style="padding: 4px 6px; color: #475569;">Gross Subtotal:</td>
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">৳ {{ formatPrice(completedInvoice.original_amount) }}</td>
                </tr>
                <tr v-if="completedInvoice.discount > 0">
                  <td style="padding: 4px 6px; color: #dc2626;">Special Discount:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #dc2626; font-family: monospace;">- ৳ {{ formatPrice(completedInvoice.discount) }}</td>
                </tr>
                <tr v-if="completedInvoice.vat > 0">
                  <td style="padding: 4px 6px; color: #475569;">VAT / Tax:</td>
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">+ ৳ {{ formatPrice(completedInvoice.vat) }}</td>
                </tr>
                <tr style="border-top: 2px solid #112C47; font-weight: bold; background: #f1f5f9; font-size: 13px;">
                  <td style="padding: 6px 8px; color: #112C47;">TOTAL PAYABLE:</td>
                  <td style="padding: 6px 8px; text-align: right; color: #112C47; font-family: monospace;">৳ {{ formatPrice(completedInvoice.amount) }}</td>
                </tr>
                <tr>
                  <td style="padding: 4px 6px; color: #166534; font-weight: bold;">Paid Amount:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #166534; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(completedInvoice.paid_amount) }}</td>
                </tr>
                <tr v-if="(completedInvoice.paid_amount - completedInvoice.amount) > 0">
                  <td style="padding: 4px 6px; color: #475569;">Change Return:</td>
                  <td style="padding: 4px 6px; text-align: right; font-family: monospace;">৳ {{ formatPrice(completedInvoice.paid_amount - completedInvoice.amount) }}</td>
                </tr>
                <tr v-if="(completedInvoice.amount - completedInvoice.paid_amount) > 0">
                  <td style="padding: 4px 6px; color: #dc2626; font-weight: bold;">Balance Due:</td>
                  <td style="padding: 4px 6px; text-align: right; color: #dc2626; font-weight: bold; font-family: monospace;">৳ {{ formatPrice(completedInvoice.amount - completedInvoice.paid_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Formal Signature Blocks -->
        <div style="display: flex; justify-content: space-between; margin-top: 40px; padding-top: 8px; font-size: 10.5px; color: #334155;">
          <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Customer's Acceptance</div>
          <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Prepared By (Cashier)</div>
          <div style="border-top: 1px dashed #64748b; width: 30%; text-align: center; padding-top: 4px;">Authorized Signature & Seal</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      currentDate: new Date().toLocaleDateString('en-GB'),
      client: { id: null, name: '', mobile: '', address: '', current_due: 0, coupon_enabled: false, points_balance: 0, points_value_in_tk: 0, point_redeem_rate: 10, point_earn_rate: 1, min_points_to_redeem: 10 },
      showNewClientForm: false,
      newClient: { name: '', mobile: '', address: '' },

      searchTerm: '',
      searchResults: [],
      selectedSearchIndex: -1,
      allColors: [],
      allSizes: [],

      showItemModal: false,
      activeItem: null,
      modalSelection: {
        color_id: null,
        size_id: null,
        serial_no: '',
        qty: 1,
        rate: 0,
        available_stock: 0
      },

      cart: [],
      discount: 0,
      points_to_redeem: 0,
      vat: 0,
      payment_method: 'Cash',
      mbanking_type: '',
      trxid: '',
      paid_amount: 0,

      isSubmitting: false,
      completedInvoice: null,
    };
  },
  computed: {
    cartTotalQty() {
      return this.cart.reduce((sum, i) => sum + (floatval(i.qty) || 0), 0);
    },
    cartSubtotal() {
      return this.cart.reduce((sum, i) => sum + (floatval(i.qty) * floatval(i.rate)), 0);
    },
    maxRedeemablePoints() {
      if (!this.client || !this.client.coupon_enabled || !this.client.points_balance) return 0;
      const rate = floatval(this.client.point_redeem_rate || 10);
      const maxByBill = Math.floor((this.cartSubtotal + floatval(this.vat)) * rate);
      return Math.min(floatval(this.client.points_balance), Math.max(0, maxByBill));
    },
    pointsDiscountAmount() {
      if (!this.client || !this.client.coupon_enabled || !this.points_to_redeem) return 0;
      const rate = floatval(this.client.point_redeem_rate || 10);
      return rate > 0 ? (floatval(this.points_to_redeem) / rate) : 0;
    },
    totalDiscount() {
      return floatval(this.discount) + this.pointsDiscountAmount;
    },
    netPayable() {
      const net = (this.cartSubtotal - this.totalDiscount) + floatval(this.vat);
      return Math.max(0, net);
    },
    changeAmount() {
      return floatval(this.paid_amount) - this.netPayable;
    },
    availableColors() {
      if (this.activeItem) {
        const itemPrices = this.activeItem.item_prices || this.activeItem.itemPrices || [];
        const stockSummaries = this.activeItem.stock_summaries || this.activeItem.stockSummaries || [];
        const colorIdsFromPrices = itemPrices.map(p => p.color_id).filter(id => id !== null);
        const colorIdsFromStock = stockSummaries.map(s => s.color_id).filter(id => id !== null);
        const allColorIds = Array.from(new Set([...colorIdsFromPrices, ...colorIdsFromStock]));
        if (allColorIds.length > 0) {
          return this.allColors.filter(c => allColorIds.includes(c.id));
        }
      }
      return this.allColors;
    },
    availableSizes() {
      if (this.activeItem) {
        const itemPrices = this.activeItem.item_prices || this.activeItem.itemPrices || [];
        const stockSummaries = this.activeItem.stock_summaries || this.activeItem.stockSummaries || [];
        const sizeIdsFromPrices = itemPrices.map(p => p.size_id).filter(id => id !== null);
        const sizeIdsFromStock = stockSummaries.map(s => s.size_id).filter(id => id !== null);
        const allSizeIds = Array.from(new Set([...sizeIdsFromPrices, ...sizeIdsFromStock]));
        if (allSizeIds.length > 0) {
          return this.allSizes.filter(s => allSizeIds.includes(s.id));
        }
      }
      return this.allSizes;
    },
    isElectronicsShop() {
      const shopType = this.$root.site?.shop_type;
      return !shopType || shopType === 'electronics';
    },
    printerType() {
      return this.$root.site?.printer_type || 'thermal';
    },
    normalPaperSize() {
      return this.$root.site?.normal_paper_size || 'A4';
    },
    thermalPaperSize() {
      return this.$root.site?.thermal_paper_size || '80mm';
    },
    effectivePrintFormat() {
      const type = (this.printerType || 'thermal').toString().toLowerCase();
      if (type === 'normal') {
        const size = (this.normalPaperSize || 'A4').toString().toUpperCase();
        return size === 'A5' ? 'normal-a5' : 'normal-a4';
      } else {
        const size = (this.thermalPaperSize || '80mm').toString().toLowerCase();
        return size === '60mm' ? 'thermal-60mm' : 'thermal-80mm';
      }
    },
  },
  watch: {
    netPayable(val) {
      this.paid_amount = val;
    }
  },
  methods: {
    formatPrice(val) {
      return floatval(val).toFixed(2);
    },
    printPOSInvoice() {
      if (!this.completedInvoice) return;
      const format = this.effectivePrintFormat;
      const invoiceNo = this.completedInvoice.invoice_no || 'POS-Invoice';

      let pageStyles = '';
      if (format === 'thermal-80mm') {
        pageStyles = `
          @page { size: 80mm auto; margin: 2mm 3mm; }
          html, body { margin: 0; padding: 0; width: 80mm; background: #fff; font-family: 'Courier New', Courier, monospace, Arial; font-size: 11px; color: #000; }
          .pos-print-wrapper { width: 78mm; margin: 0 auto; padding: 2px 0; }
        `;
      } else if (format === 'thermal-60mm') {
        pageStyles = `
          @page { size: 58mm auto; margin: 1mm 1mm; }
          html, body { margin: 0; padding: 0; width: 58mm; background: #fff; font-family: 'Courier New', Courier, monospace, Arial; font-size: 9.5px; color: #000; }
          .pos-print-wrapper { width: 56mm; margin: 0 auto; padding: 1px 0; }
        `;
      } else if (format === 'normal-a5') {
        pageStyles = `
          @page { size: 148mm 210mm; margin: 5mm 6mm; }
          html, body { margin: 0; padding: 0; width: 148mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 10.5px; color: #111; }
          .pos-print-wrapper { width: 138mm; max-width: 138mm; margin: 0 auto; }
        `;
      } else { // normal-a4
        pageStyles = `
          @page { size: 210mm 297mm; margin: 10mm 12mm; }
          html, body { margin: 0; padding: 0; width: 210mm; background: #fff; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
          .pos-print-wrapper { width: 190mm; max-width: 190mm; margin: 0 auto; }
        `;
      }

      const printContents = document.getElementById('posInvoicePrintArea');
      if (!printContents) return;

      const WinPrint = window.open('', '', 'left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0');
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
      <head>
        <title>Sales Invoice - ${invoiceNo}</title>
        <meta charset="utf-8">
        <style>
          * { box-sizing: border-box; }
          ${pageStyles}
          @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          }
        </style>
      </head>
      <body>
        <div class="pos-print-wrapper">
          ${printContents.innerHTML}
        </div>
      </body>
      </html>`);
      WinPrint.document.close();
      WinPrint.focus();
      setTimeout(() => {
        WinPrint.print();
      }, 350);
    },
    searchCustomer() {
      if (!this.client.mobile || this.client.mobile.trim() === '') return;
      const cleanMobile = this.client.mobile.trim();
      if (!/^\d{11}$/.test(cleanMobile)) {
        this.$toast('Please enter an 11-digit mobile number (১১ ডিজিটের মোবাইল নম্বর দিন)', 'warning');
        return;
      }
      axios.get(`pos/search-customer`, { params: { mobile: cleanMobile } })
        .then(res => {
          if (res.data && res.data.id) {
            this.client = res.data;
            this.showNewClientForm = false;
            this.$toast(`Client found: ${res.data.name}`, 'success');
          } else {
            this.showNewClientForm = true;
            this.newClient.mobile = cleanMobile;
            this.newClient.name = '';
            this.newClient.address = '';
            this.$toast('Client not found. Register a new client.', 'info');
            this.$nextTick(() => {
              this.$refs.newClientNameInput?.focus();
            });
          }
        })
        .catch(err => {
          this.showNewClientForm = true;
          this.newClient.mobile = cleanMobile;
          this.newClient.name = '';
          this.newClient.address = '';
          this.$toast('Client lookup failed. Fill details to register.', 'info');
          this.$nextTick(() => {
            this.$refs.newClientNameInput?.focus();
          });
        });
    },
    resetClient() {
      this.client = { id: null, name: '', mobile: '', address: '', current_due: 0, coupon_enabled: false, points_balance: 0, points_value_in_tk: 0, point_redeem_rate: 10, point_earn_rate: 1, min_points_to_redeem: 10 };
      this.showNewClientForm = false;
      this.newClient = { name: '', mobile: '', address: '' };
      this.$nextTick(() => {
        this.$refs.clientMobileInput?.focus();
      });
    },
    createQuickCustomer() {
      const mobile = (this.newClient.mobile || '').trim();
      if (!mobile) {
        this.$toast('Mobile number is required (মোবাইল নম্বর দিন)', 'warning');
        return;
      }
      if (!/^\d{11}$/.test(mobile)) {
        this.$toast('Mobile number must be exactly 11 digits (১১ ডিজিটের সঠিক মোবাইল নম্বর দিন)', 'warning');
        return;
      }
      if (!this.newClient.name || !this.newClient.name.trim()) {
        this.$toast('Client name is required (গ্রাহকের নাম দিন)', 'warning');
        return;
      }

      axios.post('pos/quick-customer', {
        mobile: mobile,
        name: this.newClient.name.trim(),
        address: this.newClient.address ? this.newClient.address.trim() : ''
      }).then(res => {
        if (res.data) {
          this.client = res.data;
          this.showNewClientForm = false;
          this.$toast(`Client "${res.data.name}" registered successfully`, 'success');
        }
      }).catch(err => {
        const msg = err.response?.data?.message || 'Failed to register client';
        this.$toast(msg, 'error');
      });
    },
    onSearchInput() {
      if (!this.searchTerm || this.searchTerm.trim().length < 1) {
        this.searchResults = [];
        this.selectedSearchIndex = -1;
        return;
      }
      axios.get('pos/search-items', { params: { term: this.searchTerm.trim() } })
        .then(res => {
          this.searchResults = res.data.items || [];
          this.allColors = res.data.colors || [];
          this.allSizes = res.data.sizes || [];
          this.selectedSearchIndex = this.searchResults.length > 0 ? 0 : -1;
        });
    },
    navigateSearchResults(step) {
      if (!this.searchResults || this.searchResults.length === 0) return;
      let newIndex = this.selectedSearchIndex + step;
      if (newIndex < 0) {
        newIndex = this.searchResults.length - 1;
      } else if (newIndex >= this.searchResults.length) {
        newIndex = 0;
      }
      this.selectedSearchIndex = newIndex;

      this.$nextTick(() => {
        const el = document.getElementById(`search-item-${this.selectedSearchIndex}`);
        if (el) {
          el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
      });
    },
    handleSearchEnter() {
      if (this.searchResults.length > 0) {
        const idx = (this.selectedSearchIndex >= 0 && this.selectedSearchIndex < this.searchResults.length)
          ? this.selectedSearchIndex
          : 0;
        this.openItemModal(this.searchResults[idx]);
      }
    },
    clearSearch() {
      this.searchTerm = '';
      this.searchResults = [];
      this.selectedSearchIndex = -1;
    },
    openItemModal(item) {
      this.activeItem = item;
      this.searchResults = [];
      this.selectedSearchIndex = -1;
      this.searchTerm = '';

      let defaultColorId = null;
      let defaultSizeId = null;
      let defaultRate = floatval(item.opening_rate || item.sale_price || 0);
      let defaultStock = 0;

      const stockSummaries = item.stock_summaries || item.stockSummaries || [];
      const itemPrices = item.item_prices || item.itemPrices || [];

      // ⭐️ 1. Find the variant with the HIGHEST stock from stock summaries
      if (stockSummaries.length > 0) {
        const sortedSummaries = [...stockSummaries].sort((a, b) => floatval(b.current_stock) - floatval(a.current_stock));
        const highestStockVariant = sortedSummaries[0];
        if (highestStockVariant) {
          defaultColorId = (highestStockVariant.color_id !== undefined && highestStockVariant.color_id !== null) ? highestStockVariant.color_id : null;
          defaultSizeId = (highestStockVariant.size_id !== undefined && highestStockVariant.size_id !== null) ? highestStockVariant.size_id : null;
          defaultStock = floatval(highestStockVariant.current_stock);
        }
      } else if (itemPrices.length > 0) {
        defaultColorId = itemPrices[0].color_id || null;
        defaultSizeId = itemPrices[0].size_id || null;
      }

      // ⭐️ 2. Find selling price for this variant
      if (itemPrices.length > 0) {
        const priceMatch = itemPrices.find(p => (p.color_id || null) == (defaultColorId || null) && (p.size_id || null) == (defaultSizeId || null));
        if (priceMatch && floatval(priceMatch.selling_price) > 0) {
          defaultRate = floatval(priceMatch.selling_price);
        } else if (itemPrices[0] && floatval(itemPrices[0].selling_price) > 0) {
          defaultRate = floatval(itemPrices[0].selling_price);
        }
      }

      this.modalSelection = {
        color_id: defaultColorId,
        size_id: defaultSizeId,
        serial_no: '',
        qty: 1,
        rate: defaultRate,
        available_stock: defaultStock
      };

      this.onVariantChange();
      this.showItemModal = true;

      // Auto focus first interactive field in modal
      this.$nextTick(() => {
        if (this.availableColors && this.availableColors.length > 0 && this.$refs.modalColorSelect) {
          this.$refs.modalColorSelect.focus();
        } else if (this.availableSizes && this.availableSizes.length > 0 && this.$refs.modalSizeSelect) {
          this.$refs.modalSizeSelect.focus();
        } else if (this.$refs.modalQtyInput) {
          this.$refs.modalQtyInput.focus();
          this.$refs.modalQtyInput.select();
        }
      });
    },
    closeItemModal() {
      this.showItemModal = false;
      this.activeItem = null;
      this.$nextTick(() => {
        this.$refs.itemSearchInput?.focus();
      });
    },
    focusNextModalInput(target) {
      if (target === 'size' && this.$refs.modalSizeSelect) {
        this.$refs.modalSizeSelect.focus();
      } else if (target === 'serial' && this.$refs.modalSerialInput) {
        this.$refs.modalSerialInput.focus();
      } else if (target === 'rate' && this.$refs.modalRateInput) {
        this.$refs.modalRateInput.focus();
        this.$refs.modalRateInput.select();
      } else if (target === 'qty' && this.$refs.modalQtyInput) {
        this.$refs.modalQtyInput.focus();
        this.$refs.modalQtyInput.select();
      } else {
        this.addToCartFromModal();
      }
    },
    onVariantChange() {
      if (!this.activeItem) return;

      const itemPrices = this.activeItem.item_prices || this.activeItem.itemPrices || [];
      const stockSummaries = this.activeItem.stock_summaries || this.activeItem.stockSummaries || [];

      // Price lookup
      if (itemPrices.length > 0) {
        const match = itemPrices.find(p => (p.color_id || null) == (this.modalSelection.color_id || null) && (p.size_id || null) == (this.modalSelection.size_id || null));
        if (match && floatval(match.selling_price) > 0) {
          this.modalSelection.rate = floatval(match.selling_price);
        }
      }

      // Stock lookup
      let stock = 0;
      if (stockSummaries.length > 0) {
        // First try exact variant match
        const exactMatches = stockSummaries.filter(s => {
          const colorMatch = (this.modalSelection.color_id === null || this.modalSelection.color_id === undefined)
            ? (s.color_id === null || s.color_id === undefined)
            : s.color_id == this.modalSelection.color_id;
          const sizeMatch = (this.modalSelection.size_id === null || this.modalSelection.size_id === undefined)
            ? (s.size_id === null || s.size_id === undefined)
            : s.size_id == this.modalSelection.size_id;
          return colorMatch && sizeMatch;
        });

        if (exactMatches.length > 0) {
          stock = exactMatches.reduce((acc, curr) => acc + floatval(curr.current_stock), 0);
        } else {
          // If no exact match, try broader match
          const broaderMatches = stockSummaries.filter(s => {
            const colorMatch = !this.modalSelection.color_id || s.color_id == this.modalSelection.color_id;
            const sizeMatch = !this.modalSelection.size_id || s.size_id == this.modalSelection.size_id;
            return colorMatch && sizeMatch;
          });
          stock = broaderMatches.reduce((acc, curr) => acc + floatval(curr.current_stock), 0);
        }
      }
      this.modalSelection.available_stock = stock;
    },
    async addToCartFromModal() {
      if (!this.activeItem) return;

      // 1. Stock Check: Available stock must be > 0
      if (this.modalSelection.available_stock <= 0) {
        this.$toast('স্টক খালি! স্টক ছাড়া পণ্য কার্টে যোগ করা সম্ভব নয়।', 'warning');
        return;
      }

      if (this.modalSelection.qty > this.modalSelection.available_stock) {
        this.$toast(`পর্যাপ্ত স্টক নেই! সর্বোচ্চ প্রাপ্য স্টক: ${this.modalSelection.available_stock}`, 'warning');
        return;
      }

      // 2. Serial Number Validation (Purchase existence & Prior sales check)
      if (this.modalSelection.serial_no && this.modalSelection.serial_no.trim() !== '') {
        const serialNo = this.modalSelection.serial_no.trim();
        try {
          const checkRes = await axios.get('pos/validate-serial', {
            params: {
              item_id: this.activeItem.id,
              color_id: this.modalSelection.color_id,
              size_id: this.modalSelection.size_id,
              serial_no: serialNo
            }
          });

          if (checkRes.data && checkRes.data.valid === false) {
            this.$toast(checkRes.data.message, 'warning');
            return;
          }
        } catch (err) {
          console.error(err);
        }
      }

      const colorObj = this.allColors.find(c => c.id == this.modalSelection.color_id);
      const sizeObj = this.allSizes.find(s => s.id == this.modalSelection.size_id);

      // Check if identical item+color+size is already in cart, increment quantity
      const existingCartIndex = this.cart.findIndex(c => 
        c.item_id === this.activeItem.id && 
        c.color_id == this.modalSelection.color_id && 
        c.size_id == this.modalSelection.size_id &&
        (!this.modalSelection.serial_no || c.serial_no === this.modalSelection.serial_no)
      );

      if (existingCartIndex > -1 && !this.modalSelection.serial_no) {
        const newQty = this.cart[existingCartIndex].qty + (this.modalSelection.qty || 1);
        if (newQty > this.modalSelection.available_stock) {
          this.$toast(`পর্যাপ্ত স্টক নেই! সর্বোচ্চ প্রাপ্য স্টক: ${this.modalSelection.available_stock}`, 'warning');
          return;
        }
        this.cart[existingCartIndex].qty = newQty;
      } else {
        this.cart.push({
          item_id: this.activeItem.id,
          title: this.activeItem.title,
          barcode: this.activeItem.barcode,
          color_id: this.modalSelection.color_id,
          color_title: colorObj ? colorObj.title : null,
          size_id: this.modalSelection.size_id,
          size_title: sizeObj ? sizeObj.title : null,
          serial_no: this.modalSelection.serial_no,
          qty: this.modalSelection.qty || 1,
          rate: this.modalSelection.rate || 0,
        });
      }

      this.closeItemModal();
      this.$toast('Item added to cart', 'success');

      // Refocus item search input for rapid consecutive item entry
      this.$nextTick(() => {
        this.$refs.itemSearchInput?.focus();
      });
    },
    // Alias for backward compatibility
    confirmAddToCart() {
      this.addToCartFromModal();
    },
    removeCartItem(index) {
      this.cart.splice(index, 1);
    },
    clearCart() {
      this.cart = [];
    },
    redeemAllPoints() {
      this.points_to_redeem = this.maxRedeemablePoints;
    },
    submitCheckout() {
      if (this.cart.length === 0) {
        this.$toast('Cart is empty', 'warning');
        return;
      }

      this.isSubmitting = true;

      const payload = {
        client_id: this.client.id,
        client_mobile: this.client.mobile,
        client_name: this.client.name,
        client_address: this.client.address,
        cart: this.cart,
        discount: this.totalDiscount,
        manual_discount: this.discount,
        points_redeemed: this.points_to_redeem,
        vat: this.vat,
        payment_method: this.payment_method,
        mbanking_type: this.mbanking_type,
        trxid: this.trxid,
        paid_amount: this.paid_amount,
      };

      axios.post('pos/checkout', payload)
        .then(res => {
          this.isSubmitting = false;
          if (res.data && res.data.success) {
            this.completedInvoice = res.data.invoice;
            this.$toast('Sale completed successfully!', 'success');

            // Trigger POS Print
            this.$nextTick(() => {
              this.printPOSInvoice();
              this.resetPOS();
            });
          }
        })
        .catch(err => {
          this.isSubmitting = false;
          this.$toast(err.response?.data?.exception || 'Failed to complete checkout', 'danger');
        });
    },
    resetPOS() {
      this.client = { id: null, name: '', mobile: '', address: '', current_due: 0, coupon_enabled: false, points_balance: 0, points_value_in_tk: 0, point_redeem_rate: 10, point_earn_rate: 1, min_points_to_redeem: 10 };
      this.showNewClientForm = false;
      this.cart = [];
      this.discount = 0;
      this.points_to_redeem = 0;
      this.vat = 0;
      this.paid_amount = 0;
      this.trxid = '';
      this.payment_method = 'Cash';
      this.searchTerm = '';
      this.searchResults = [];
      this.selectedSearchIndex = -1;
    },
    handleKeydown(e) {
      if (this.showItemModal) {
        if (e.key === 'Escape') {
          e.preventDefault();
          this.closeItemModal();
        }
        return;
      }

      if (this.showNewClientForm) {
        if (e.key === 'Escape') {
          e.preventDefault();
          this.showNewClientForm = false;
          return;
        } else if ((e.ctrlKey && e.key === 'Enter') || (e.altKey && (e.key === 's' || e.key === 'S'))) {
          e.preventDefault();
          this.createQuickCustomer();
          return;
        }
      }

      if (e.key === 'F2' || (e.ctrlKey && e.key === 'f')) {
        e.preventDefault();
        this.$refs.itemSearchInput?.focus();
        this.$refs.itemSearchInput?.select();
      } else if (e.key === 'F4' || (e.ctrlKey && e.key === 'm')) {
        e.preventDefault();
        this.$refs.clientMobileInput?.focus();
        this.$refs.clientMobileInput?.select();
      } else if (e.key === 'F8' || (e.ctrlKey && e.key === 'p')) {
        e.preventDefault();
        this.submitCheckout();
      } else if (e.key === 'Escape') {
        this.clearSearch();
      }
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
  }
};

function floatval(val) {
  const f = parseFloat(val);
  return isNaN(f) ? 0 : f;
}
</script>

<style scoped>
.theme-icon {
  color: rgb(17, 44, 70) !important;
}

.client-input {
  height: 30px !important;
  font-size: 12px !important;
  border-radius: 4px !important;
  border: 1px solid rgb(17, 44, 70) !important;
  padding: 0 8px !important;
}

.client-search-group .client-input {
  border-radius: 4px 0 0 4px !important;
}

.client-search-btn {
  background-color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
  height: 30px !important;
  border-radius: 0 4px 4px 0 !important;
  padding: 0 10px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 12px !important;
}

.client-search-btn:hover {
  background-color: #1a3d61 !important;
  color: #ffffff !important;
}

.client-save-btn {
  background-color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
  height: 30px !important;
  border-radius: 4px !important;
  padding: 0 10px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 12px !important;
  font-weight: 600 !important;
}

.client-save-btn:hover {
  background-color: #1a3d61 !important;
  color: #ffffff !important;
}

/* ⭐️ Highlighted Item Search Bar */
.item-search-bar {
  background-color: #ffffff;
  border: 2px solid rgb(17, 44, 70);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(17, 44, 70, 0.12);
  transition: all 0.2s ease-in-out;
}

.item-search-bar:focus-within {
  box-shadow: 0 0 0 3px rgba(17, 44, 70, 0.2), 0 4px 12px rgba(17, 44, 70, 0.15);
  border-color: rgb(17, 44, 70);
}

.search-barcode-icon {
  background-color: #f1f5f9;
  border-right: 1.5px solid #cbd5e1;
}

.item-search-input {
  height: 42px !important;
  border: none !important;
  box-shadow: none !important;
  font-size: 14px !important;
  font-weight: 600;
  padding: 0 14px !important;
  background-color: transparent !important;
  color: rgb(17, 44, 70) !important;
}

.item-search-input::placeholder {
  color: #94a3b8;
  font-weight: 400;
  font-size: 13px;
}

.btn-clear-search {
  border: none !important;
  background: transparent;
  color: #64748b;
}

.btn-clear-search:hover {
  color: #dc2626;
}

.hover-bg-light:hover {
  background-color: #f8f9fa;
}

.tab-modal-backdrop {
  z-index: 1055;
}

.active-search-row {
  background-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.active-search-row small {
  color: rgba(255, 255, 255, 0.85) !important;
}

/* 🏷 Category Badge Styling for Search Results */
.search-category-badge {
  font-size: 11px !important;
  padding: 3px 8px !important;
  border-radius: 4px !important;
  font-weight: 600 !important;
  display: inline-block !important;
  line-height: 1.2 !important;
}

.search-category-badge.badge-on-dark {
  background-color: #ffffff !important;
  color: rgb(17, 44, 70) !important;
  border: 1px solid #ffffff !important;
}

.search-category-badge.badge-on-light {
  background-color: #e2e8f0 !important;
  color: #1e293b !important;
  border: 1px solid #cbd5e1 !important;
}

.transition-all {
  transition: all 0.15s ease-in-out;
}
</style>
