<template>
  <div class="pos-container p-2 bg-light min-vh-100">
    <!-- POS Top Header Bar -->
    <div class="card border-0 shadow-sm mb-2 text-white" style="background-color: #112C47;">
      <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <h4 class="mb-0 fw-bold text-white"><i class="fas fa-cash-register me-2 text-warning"></i>{{ $t('QTerminal') }}</h4>
          <span class="badge bg-secondary font-monospace">{{ localizedCurrentDate }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button
            type="button"
            class="btn btn-sm d-flex align-items-center gap-1 shadow-sm px-2.5 py-1 rounded-pill fw-bold border btn-outline-light bg-white text-dark"
            @click="toggleLanguage"
            :title="$t('Select Language')"
          >
            <i class="fas fa-language fa-lg text-primary"></i>
            <span class="fw-bold">{{ $locale === 'bn' ? '🇧🇩 বাংলা' : ($locale === 'hi' ? '🇮🇳 हिन्दी' : ($locale === 'fr' ? '🇫🇷 FR' : ($locale === 'es' ? '🇪🇸 ES' : '🇺🇸 EN'))) }}</span>
          </button>
          <button type="button" class="btn btn-sm btn-outline-info text-white d-flex align-items-center gap-1 font-monospace" @click="openHelpModal">
            <i class="fas fa-question-circle"></i> {{ $t('Help') }}
          </button>
          <router-link to="/invoice" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 font-monospace">
            <i class="fas fa-file-invoice"></i> {{ $t('Invoices') }}
          </router-link>
          <router-link to="/pos/return" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1 font-monospace">
            <i class="fas fa-undo"></i> {{ $t('Sales Return') }}
          </router-link>
          <router-link to="/admin/dashboard" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <i class="fas fa-tachometer-alt"></i> {{ $t('Dashboard') }}
          </router-link>
        </div>
      </div>
    </div>

    <!-- 👤 Sleek Horizontal Client Info Bar (Compact & Flat) -->
    <div class="card border-0 shadow-sm mb-2">
      <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <!-- Client Search Input with Live Autocomplete -->
          <div class="d-flex align-items-center gap-2 flex-grow-1 position-relative" style="min-width: 240px; max-width: 340px;">
            <span class="fw-bold small text-nowrap" style="color: rgb(17 44 70);"><i class="fas fa-user theme-icon me-1"></i>{{ $t('Client') }} (F4):</span>
            <div class="input-group input-group-sm client-search-group w-100">
              <input
                ref="clientSearchInput"
                type="text"
                class="form-control form-control-sm font-monospace fw-bold client-input"
                :placeholder="$t('Mobile / Name / ID...')"
                v-model="customerSearchTerm"
                @input="onCustomerSearchInput"
                @keydown.down.prevent="navigateCustomerResults(1)"
                @keydown.up.prevent="navigateCustomerResults(-1)"
                @keydown.enter.prevent="handleCustomerSearchEnter"
                @keydown.esc="customerSearchResults = []"
              >
              <button type="button" class="btn client-search-btn" @click="handleCustomerSearchEnter" :title="$t('Search Client')">
                <i class="fas fa-search"></i>
              </button>
            </div>

            <!-- Customer Search Results Dropdown -->
            <div v-if="customerSearchResults.length > 0" class="position-absolute w-100 bg-white border rounded shadow-lg customer-search-dropdown" style="top: 100%; left: 0; max-height: 280px; overflow-y: auto; z-index: 10000; margin-top: 2px;">
              <div
                v-for="(cust, cIdx) in customerSearchResults"
                :key="'cust_opt_' + (cust.id || cIdx) + '_' + cIdx"
                class="p-2 border-bottom cursor-pointer d-flex align-items-center justify-content-between transition-all"
                :class="{ 'bg-primary text-white': selectedCustomerIndex === cIdx, 'hover-bg-light text-dark': selectedCustomerIndex !== cIdx }"
                @click="selectCustomer(cust)"
                @mouseenter="selectedCustomerIndex = cIdx"
              >
                <div>
                  <div class="fw-bold fs-6" :class="selectedCustomerIndex === cIdx ? 'text-white' : 'text-dark'">{{ cust.name }}</div>
                  <small :class="selectedCustomerIndex === cIdx ? 'text-white-50' : 'text-muted'" class="font-monospace">
                    <i class="fas fa-phone-alt me-1"></i>{{ cust.mobile }}
                  </small>
                </div>
                <div class="text-end">
                  <span class="badge" :class="cust.customer_type === 'wholesale' ? (selectedCustomerIndex === cIdx ? 'bg-white text-primary' : 'bg-primary') : (selectedCustomerIndex === cIdx ? 'bg-white text-dark' : 'bg-secondary')">
                    {{ cust.customer_type === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                  </span>
                  <div v-if="cust.current_due > 0" class="small font-monospace" :class="selectedCustomerIndex === cIdx ? 'text-white-50' : 'text-danger'">
                    Due: ৳{{ $bnNum(formatPrice(cust.current_due)) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Selected Customer Info Pill with Interactive Price Type Switch -->
          <div v-if="client.id" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-between bg-light p-1 px-3 rounded border">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="fw-bold text-dark fs-6">{{ client.name }}</span>
              <!-- Interactive Customer Type / Price Switch Button -->
              <button
                type="button"
                class="btn btn-xs rounded-pill px-2.5 py-0.5 fw-bold d-flex align-items-center gap-1 shadow-sm border"
                :class="client.customer_type === 'wholesale' ? 'btn-primary text-white' : 'btn-outline-secondary bg-white text-dark'"
                @click="toggleCustomerPriceType"
                :title="$t('Click to toggle price nature between Retail & Wholesale for this sale')"
              >
                <i class="fas" :class="client.customer_type === 'wholesale' ? 'fa-boxes text-warning' : 'fa-user-tag text-primary'"></i>
                <span>{{ client.customer_type === 'wholesale' ? $t('Wholesale Customer (পাইকারি)') : $t('Retail Customer (খুচরা)') }}</span>
                <i class="fas fa-sync-alt ms-1 text-muted" style="font-size: 9px;"></i>
              </button>
              <small class="text-muted font-monospace"><i class="fas fa-phone-alt theme-icon me-1"></i>{{ client.mobile }}</small>
              <small class="text-muted" v-if="client.address && client.address !== 'N/A'">({{ client.address }})</small>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-danger bg-opacity-10 text-danger border border-danger font-monospace px-2 py-1" v-if="client.current_due > 0">
                {{ $t('Due') }}: {{ $t('Tk.') }} {{ $bnNum(formatPrice(client.current_due)) }}
              </span>
              <span class="badge bg-warning text-dark border font-monospace px-2 py-1" v-if="client.coupon_enabled">
                <i class="fas fa-gift me-1"></i>{{ $bnNum(formatPrice(client.points_balance || 0)) }} {{ $t('Pts') }} (≈ {{ $t('Tk.') }} {{ $bnNum(formatPrice(client.points_value_in_tk || 0)) }})
              </span>
              <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" @click="resetClient" :title="$t('Clear / Change Customer')">
                <i class="fas fa-times me-1"></i>{{ $t('Change') }}
              </button>
            </div>
          </div>

          <!-- Quick New Client Registration Inline Form if Not Found -->
          <div v-else-if="showNewClientForm" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 bg-warning bg-opacity-10 p-1 px-2 rounded border border-warning">
            <span class="small fw-bold text-dark text-nowrap"><i class="fas fa-user-plus me-1 text-warning"></i>{{ $t('New') }}:</span>
            <input
              ref="newClientMobileInput"
              type="text"
              class="form-control form-control-sm font-monospace fw-bold client-input"
              :placeholder="$t('Mobile') + ' *'"
              v-model="newClient.mobile"
              maxlength="11"
              style="max-width: 125px;"
            >
            <input
              ref="newClientNameInput"
              type="text"
              class="form-control form-control-sm client-input"
              :placeholder="$t('Client Name') + ' *'"
              v-model="newClient.name"
              @keyup.enter="createQuickCustomer"
              style="max-width: 150px;"
            >
            <select
              class="form-select form-select-sm client-input"
              v-model="newClient.customer_type"
              style="max-width: 130px;"
              :title="$t('Customer Type')"
            >
              <option value="retail">{{ $t('Retail') }}</option>
              <option value="wholesale">{{ $t('Wholesale') }}</option>
            </select>
            <input
              type="text"
              class="form-control form-control-sm client-input flex-grow-1"
              :placeholder="$t('Address / Location (optional)')"
              v-model="newClient.address"
              @keyup.enter="createQuickCustomer"
              style="min-width: 160px; max-width: 260px;"
            >
            <button type="button" class="btn client-save-btn text-nowrap" @click="createQuickCustomer" :title="$t('Save Client (Press Enter / Ctrl+Enter)')">
              <i class="fas fa-save me-1"></i>{{ $t('Save') }} <small class="text-white-50 ms-1">[Enter]</small>
            </button>
            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-1" @click="showNewClientForm = false" :title="$t('Cancel (Esc)')">{{ $t('Cancel') }}</button>
          </div>

          <!-- Default Walk-in Customer Hint -->
          <div v-else class="text-muted small d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-2 py-1">
              <i class="fas fa-walking theme-icon me-1"></i>{{ $t('Walk-in Customer') }}
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
                  <span>{{ $t('Search Item / Scan Barcode') }} (F2)</span>
                </label>
                <span class="text-muted" style="font-size: 11px;"><kbd class="bg-dark text-white">↑</kbd> <kbd class="bg-dark text-white">↓</kbd> = {{ $t('Navigate') }} | <kbd class="bg-dark text-white">Enter</kbd> = {{ $t('Select') }}</span>
              </div>
              <div class="item-search-bar d-flex align-items-stretch">
                <span class="search-barcode-icon d-flex align-items-center justify-content-center px-3">
                  <i class="fas fa-barcode fs-5 theme-icon"></i>
                </span>
                <input
                  ref="itemSearchInput"
                  type="text"
                  class="form-control item-search-input"
                  :placeholder="$t('Type product title, SKU, or scan barcode... (Press F2 to focus)')"
                  v-model="searchTerm"
                  @input="onSearchInput"
                  @keydown.down.prevent="navigateSearchResults(1)"
                  @keydown.up.prevent="navigateSearchResults(-1)"
                  @keydown.enter.prevent="handleSearchEnter"
                  @keydown.esc="clearSearch"
                >
                <button type="button" class="btn btn-clear-search px-3" @click="clearSearch" v-if="searchTerm" :title="$t('Clear search')">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Search Results Dropdown with Arrow Keyboard Navigation -->
              <div v-if="searchResults.length > 0" class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 search-dropdown" style="max-height: 320px; overflow-y: auto; z-index: 9999;">
                <div
                  v-for="(item, idx) in searchResults"
                  :key="'search_opt_' + (item.id || idx) + '_' + idx"
                  :id="'search-item-' + idx"
                  class="p-2 border-bottom cursor-pointer d-flex align-items-center justify-content-between transition-all"
                  :class="{ 'active-search-row': selectedSearchIndex === idx, 'hover-bg-light': selectedSearchIndex !== idx }"
                  @click="selectItem(item)"
                  @mouseenter="selectedSearchIndex = idx"
                >
                  <div>
                    <div class="fw-bold" :class="selectedSearchIndex === idx ? 'text-white' : 'text-dark'">{{ item.title }}</div>
                    <small :class="selectedSearchIndex === idx ? 'text-white-50' : 'text-muted'" class="font-monospace me-2">{{ $t('Barcode') }}: {{ item.barcode }}</small>
                    <span class="badge search-category-badge" :class="selectedSearchIndex === idx ? 'badge-on-dark' : 'badge-on-light'" v-if="item.category">
                      {{ item.category.title }}
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <div class="text-end me-2">
                      <div class="fw-bold font-monospace fs-6" :class="selectedSearchIndex === idx ? 'text-white' : 'text-success'">
                        {{ $t('Tk.') }} {{ $bnNum(formatPrice(getItemPrice(item))) }}
                      </div>
                      <span class="badge" :class="selectedSearchIndex === idx ? 'bg-white text-dark' : (effectivePriceNature === 'wholesale' ? 'bg-primary' : 'bg-secondary')" style="font-size: 10px;">
                        {{ effectivePriceNature === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                      </span>
                    </div>
                    <span class="small font-monospace" :class="selectedSearchIndex === idx ? 'text-white-50' : 'text-muted'" style="font-size: 11px;">[{{ $t('Enter to Select') }}]</span>
                    <button type="button" class="btn btn-xs" :class="selectedSearchIndex === idx ? 'btn-light fw-bold text-dark' : 'btn-primary'">
                      {{ $t('Select Item') }}
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
                  <th style="width: 26%;">{{ $t('Item Title') }}</th>
                  <th style="width: 18%;">{{ $t('Color / Size') }}</th>
                  <th style="width: 15%;" v-if="isElectronicsShop">{{ $t('Serial No') }}</th>
                  <th style="width: 11%;" class="text-center">{{ $t('Qty') }}</th>
                  <th style="width: 13%;" class="text-end">{{ $t('Price') }}</th>
                  <th style="width: 12%;" class="text-end">{{ $t('Total') }}</th>
                  <th style="width: 5%;" class="text-center">{{ $t('Act') }}</th>
                </tr>
              </thead>
              <tbody v-if="cart.length > 0">
                <tr v-for="(cItem, idx) in cart" :key="'cart_row_' + (cItem.item_id || 0) + '_' + (cItem.color_id || 0) + '_' + (cItem.size_id || 0) + '_' + idx">
                  <td>
                    <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" :title="cItem.title">{{ cItem.title }}</div>
                    <small class="text-muted font-monospace" style="font-size: 11px;">{{ cItem.barcode }}</small>
                  </td>
                  <td>
                    <span class="badge bg-info text-dark me-1" v-if="cItem.color_title" style="font-size: 11px;">{{ cItem.color_title }}</span>
                    <span class="badge bg-secondary me-1" v-if="cItem.size_title" style="font-size: 11px;">{{ cItem.size_title }}</span>
                    <span v-if="!cItem.color_title && !cItem.size_title" class="text-muted small">{{ $t('Standard') }}</span>
                  </td>
                  <td v-if="isElectronicsShop">
                    <input 
                      type="text" 
                      class="form-control form-control-sm font-monospace p-1" 
                      style="max-width: 130px; font-size: 11px;" 
                      v-model="cItem.serial_no" 
                      @input="onCartItemSerialChange(cItem)"
                      :placeholder="$t('Serial (comma separated)')"
                    >
                    <div class="text-primary font-monospace mt-0.5" v-show="getSerialsCount(cItem.serial_no) > 0" style="font-size: 10px;">
                      <i class="fas fa-hashtag me-0.5"></i>{{ getSerialsCount(cItem.serial_no) }} {{ $t('Serials') }}
                    </div>
                  </td>
                  <td class="text-center">
                    <input 
                      type="number" 
                      min="1" 
                      class="form-control form-control-sm text-center fw-bold p-1 mx-auto" 
                      style="max-width: 60px; font-size: 13px;" 
                      v-model.number="cItem.qty"
                      @input="onCartItemQtyChange(cItem)"
                    >
                  </td>
                  <td class="text-end font-monospace">
                    <div v-show="cItem.discount_amount > 0" class="mb-1">
                      <small class="text-muted text-decoration-line-through d-block" style="font-size: 11px;">
                        {{ $t('Tk.') }} {{ $bnNum(formatPrice(cItem.base_rate)) }}
                      </small>
                    </div>
                    <!-- Rate input with wholesale/retail indicator badge underneath -->
                    <div class="d-flex flex-column align-items-end">
                      <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace p-1" style="max-width: 80px; font-size: 13px;" v-model.number="cItem.rate" @input="onCartItemRateChange(cItem)">
                      <span class="badge mt-0.5" :class="effectivePriceNature === 'wholesale' ? 'bg-primary bg-opacity-10 text-primary border border-primary' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary'" style="font-size: 9px;" :title="effectivePriceNature === 'wholesale' ? 'Wholesale Price applied' : 'Retail Price applied'">
                        {{ effectivePriceNature === 'wholesale' ? $t('Wholesale') : $t('Retail') }}
                      </span>
                    </div>
                  </td>
                  <td class="text-end font-monospace fw-bold fs-6" style="color: rgb(17 44 70);">
                    {{ $bnNum(formatPrice(cItem.qty * cItem.rate)) }}
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" @click="removeCartItem(idx)" :title="$t('Remove')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr>
                  <td :colspan="isElectronicsShop ? 7 : 6" class="text-center py-4 text-muted">
                    <i class="fas fa-shopping-basket fa-2x mb-2 text-secondary opacity-50"></i>
                    <p class="mb-0 small">{{ $t('Cart is empty. Search items above or scan barcode (F2) to add products.') }}</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Compact Cart Footer Strip (Total Items & Clear Cart) -->
          <div class="card-footer bg-light py-1 px-3 d-flex justify-content-between align-items-center border-top small text-muted">
            <span>
              <i class="fas fa-shopping-cart me-1 theme-icon"></i>{{ $t('Cart Items') }}: <strong class="text-dark font-monospace">{{ $bnNum(cart.length) }}</strong> ({{ $t('Total Qty') }}: <strong class="text-dark font-monospace">{{ $bnNum(cartTotalQty) }}</strong>)
            </span>
            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2" @click="clearCart" v-if="cart.length > 0" :title="$t('Clear all cart items')">
              <i class="fas fa-trash-alt me-1"></i>{{ $t('Clear Cart') }}
            </button>
          </div>
        </div>

        <!-- 📜 Terms & Conditions Configuration below Cart (Active when site setting show_pos_terms is enabled) -->
        <div v-if="showPosTermsConfig" class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom cursor-pointer" @click="showTermsSection = !showTermsSection">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-file-contract text-primary"></i>
              <span class="fw-bold small text-dark">{{ $t('Terms & Conditions') }}</span>
              <span class="badge bg-primary font-monospace" style="font-size: 10px;">
                {{ $bnNum(selectedTermsList.length) }} {{ $t('selected') }}
              </span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 font-semibold" @click.stop="addCustomTerm" :title="$t('Add Condition')">
                <i class="fas fa-plus me-1"></i>{{ $t('Add Condition') }}
              </button>
              <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" @click.stop="resetInvoiceTerms" :title="$t('Reset')">
                <i class="fas fa-sync-alt me-1"></i>{{ $t('Reset') }}
              </button>
              <i class="fas fa-chevron-down text-muted transition-all" :style="{ transform: showTermsSection ? 'rotate(180deg)' : 'rotate(0deg)', fontSize: '11px' }"></i>
            </div>
          </div>
          <div v-show="showTermsSection" class="card-body p-2 bg-white" style="max-height: 180px; overflow-y: auto;">
            <div v-if="invoiceTerms.length > 0" class="d-flex flex-column gap-2">
              <div v-for="(term, tIdx) in invoiceTerms" :key="tIdx" class="d-flex align-items-center gap-2 p-1 px-2 rounded border" :class="term.selected ? 'bg-light border-primary border-opacity-25' : 'bg-white border-light opacity-75'">
                <!-- Checkbox -->
                <div class="form-check m-0">
                  <input type="checkbox" class="form-check-input cursor-pointer"
                    v-model="term.selected" :id="'pos_term_' + tIdx"
                    :title="term.selected ? $t('Included in Invoice') : $t('Excluded from Invoice')">
                </div>
                
                <!-- Editable Input Box -->
                <input type="text" class="form-control form-control-sm font-monospace"
                  :class="term.selected ? 'fw-semibold text-dark' : 'text-muted text-decoration-line-through'"
                  style="font-size: 12px; height: 28px;"
                  v-model="term.condition"
                  :placeholder="$t('Condition text...')">
                
                <!-- Delete / Remove button -->
                <button type="button" class="btn btn-xs btn-outline-danger border-0 p-1 text-muted" @click="removeCustomTerm(tIdx)" :title="$t('Remove this condition')">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
            <div v-else class="text-center text-muted small py-3">
              <p class="mb-1">{{ $t('No conditions loaded for Invoice module.') }}</p>
              <button type="button" class="btn btn-xs btn-primary theme_btn px-2" @click="addCustomTerm">
                <i class="fas fa-plus me-1"></i> {{ $t('Add Custom Condition') }}
              </button>
            </div>
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
                <span class="text-muted small">{{ $t('Subtotal') }}:</span>
                <span class="fw-bold font-monospace">{{ $t('Tk.') }} {{ $bnNum(formatPrice(cartSubtotal)) }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span class="text-muted small">{{ $t('Discount') }}:</span>
                <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace py-0 px-2" style="max-width: 110px; height: 28px;" v-model.number="discount" placeholder="0.00">
              </div>

              <!-- 🎁 Redeem Points Section (If points exist) -->
              <div v-if="client.coupon_enabled && client.points_balance > 0" class="p-1 px-2 my-1 bg-warning bg-opacity-10 border border-warning rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 11px;">
                    <i class="fas fa-gift text-warning"></i> {{ $t('Redeem') }} ({{ $t('Max') }}: {{ $bnNum(maxRedeemablePoints) }} {{ $t('Pts') }}):
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-dark py-0 px-1" style="font-size: 9px;" @click="redeemAllPoints">
                    {{ $t('All') }}
                  </button>
                </div>
                <div class="input-group input-group-sm">
                  <input type="number" min="0" :max="maxRedeemablePoints" class="form-control font-monospace text-center fw-bold py-0" style="height: 26px;" placeholder="0" v-model.number="points_to_redeem">
                  <span class="input-group-text bg-white small font-monospace text-success fw-bold py-0 px-1" style="font-size: 11px;">- {{ $t('Tk.') }} {{ $bnNum(formatPrice(pointsDiscountAmount)) }}</span>
                </div>
              </div>

              <!-- 🏷 VAT / Tax Switch & Calculation -->
              <div class="py-1 px-2 my-1 rounded border transition-all" :class="is_vat_applicable ? 'bg-primary bg-opacity-10 border-primary' : 'bg-white border-light'">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="form-check form-switch m-0 p-0 d-flex align-items-center gap-1">
                    <input class="form-check-input ms-0 cursor-pointer" type="checkbox" id="posVatSwitch"
                      v-model="is_vat_applicable" @change="onVatSwitchToggle"
                      style="transform: scale(1.1); cursor: pointer;">
                    <label class="form-check-label fw-bold cursor-pointer small mb-0" for="posVatSwitch" :class="is_vat_applicable ? 'text-primary' : 'text-muted'" style="font-size: 11px;">
                      <i class="fas fa-file-invoice-dollar me-1"></i>{{ is_vat_applicable ? $t('With VAT') : $t('Without VAT') }}
                    </label>
                  </div>

                  <!-- Rate & Calculated Amount in one clean line -->
                  <div v-if="is_vat_applicable" class="d-flex align-items-center gap-1">
                    <div class="input-group input-group-sm" style="max-width: 68px;" title="VAT Percentage Rate">
                      <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm text-center font-monospace py-0 px-1"
                        style="height: 24px; font-size: 11px;" v-model.number="vat_percent" @input="calculateVatAmount" placeholder="0">
                      <span class="input-group-text bg-white small py-0 px-1" style="font-size: 9.5px;">%</span>
                    </div>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace fw-bold text-primary py-0 px-2"
                      style="max-width: 88px; height: 24px; font-size: 12px;" v-model.number="vat" placeholder="0.00">
                  </div>
                  <div v-else class="text-muted font-monospace small" style="font-size: 11px;">
                    {{ $t('Tk.') }} {{ $bnNum('0.00') }}
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center pt-2">
                <span class="fw-bold text-dark fs-6">{{ $t('Net Payable') }}:</span>
                <span class="fw-bold font-monospace fs-5 text-success">{{ $t('Tk.') }} {{ $bnNum(formatPrice(netPayable)) }}</span>
              </div>
            </div>

            <!-- Payment Method & Paid Amount in Compact Row -->
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label fw-bold text-muted mb-0" style="font-size: 11px;">{{ $t('Payment Method') }}</label>
                <select class="form-select form-select-sm font-monospace fw-bold py-1" style="height: 32px;" v-model="payment_method">
                  <option value="Cash">{{ $t('Cash') }}</option>
                  <option value="Card">{{ $t('Card') }}</option>
                  <option value="bKash">{{ $t('bKash') }}</option>
                  <option value="Nagad">{{ $t('Nagad') }}</option>
                  <option value="Rocket">{{ $t('Rocket') }}</option>
                  <option value="Bank">{{ $t('Bank Transfer') }}</option>
                </select>
              </div>
              <div class="col-6">
                <div class="d-flex justify-content-between align-items-center mb-0">
                  <label class="form-label fw-bold text-muted mb-0" style="font-size: 11px;">{{ $t('Paid Amount') }}</label>
                  <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none font-monospace fw-bold text-primary" style="font-size: 10px;" @click="setFullPay" :title="$t('Pay Full Amount (Press F7, F9, or Alt+F)')">{{ $t('Full Pay') }} (F7)</button>
                </div>
                <input ref="paidAmountInput" type="number" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace fw-bold text-primary py-1" style="height: 32px; font-size: 14px;" v-model.number="paid_amount" placeholder="0.00">
              </div>
              <div class="col-12" v-if="payment_method !== 'Cash'">
                <input type="text" class="form-control form-control-sm font-monospace py-1" style="height: 28px;" :placeholder="$t('TrxID / Reference No.')" v-model="trxid">
              </div>
            </div>

            <!-- Due / Change Return Amount (Compact Line) -->
            <div class="d-flex justify-content-between align-items-center p-2 bg-light border rounded mb-3">
              <span class="text-muted fw-bold small">{{ (paid_amount || 0) >= netPayable ? $t('Change:') : $t('Due Amount:') }}</span>
              <span class="fw-bold font-monospace fs-6" :class="(paid_amount || 0) >= netPayable ? 'text-success' : 'text-danger'">
                {{ $t('Tk.') }} {{ $bnNum(formatPrice((paid_amount || 0) >= netPayable ? ((paid_amount || 0) - netPayable) : (netPayable - (paid_amount || 0)))) }}
              </span>
            </div>

            <!-- ⭐️ Complete Sale & Print Button (Prominent & Always Visible) -->
            <button
              type="button"
              class="btn btn-success btn-lg w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
              @click="submitCheckout"
              :disabled="cart.length === 0 || isSubmitting"
            >
              <i class="fas fa-print"></i> {{ $t('Complete Sale & Print') }} (F8)
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ⚠️ Modal Popup for Multiple Items with Same Barcode (Duplicate Barcode Selector with Keyboard Numbers 1-9 & Arrows) -->
    <div
      v-if="showDuplicateBarcodeModal"
      class="modal fade show d-block tab-modal-backdrop"
      tabindex="-1"
      style="background: rgba(0,0,0,0.65); z-index: 10050;"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-warning text-dark py-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <i class="fas fa-exclamation-triangle fs-5 text-dark"></i>
              <div>
                <h5 class="modal-title fw-bold fs-6 mb-0">{{ $t('Multiple Products Found') }}</h5>
                <small class="font-monospace text-dark opacity-75">{{ $t('Barcode') }}: <strong>{{ duplicateBarcodeScanned }}</strong> ({{ $bnNum(duplicateBarcodeItems.length) }} {{ $t('items found') }})</small>
              </div>
            </div>
            <button type="button" class="btn-close" @click="closeDuplicateModal"></button>
          </div>
          <div class="modal-body p-3 bg-light">
            <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
              <div class="small">
                <i class="fas fa-keyboard me-1"></i> {{ $t('Shortcuts') }}: {{ $t('Press') }} <strong>[1]</strong>-<strong>[{{ Math.min(duplicateBarcodeItems.length, 9) }}]</strong> {{ $t('or') }} <strong>{{ $t('Arrow Keys (↑/↓)') }}</strong> {{ $t('then') }} <strong>[Enter]</strong>.
              </div>
              <span class="badge bg-dark font-monospace">Esc = {{ $t('Close') }}</span>
            </div>

            <div class="list-group shadow-sm">
              <div
                v-for="(item, idx) in duplicateBarcodeItems"
                :key="item.id"
                class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between cursor-pointer transition-all"
                :class="{ 'bg-primary text-white active-dup-item': selectedDuplicateIndex === idx, 'bg-white text-dark': selectedDuplicateIndex !== idx }"
                @click="selectDuplicateItem(item)"
                @mouseenter="selectedDuplicateIndex = idx"
              >
                <div class="d-flex align-items-center gap-3">
                  <div
                    class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm"
                    :class="selectedDuplicateIndex === idx ? 'bg-white text-primary' : 'bg-primary text-white'"
                    style="width: 38px; height: 38px; min-width: 38px;"
                  >
                    {{ $bnNum(idx + 1) }}
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1" :class="selectedDuplicateIndex === idx ? 'text-white' : 'text-dark'">{{ item.title }}</h6>
                    <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 12px;">
                      <span class="badge" :class="selectedDuplicateIndex === idx ? 'bg-white text-dark' : 'bg-light text-muted border'" v-if="item.category">
                        {{ item.category.title }}
                      </span>
                      <span class="font-monospace" :class="selectedDuplicateIndex === idx ? 'text-white-50' : 'text-muted'">
                        {{ $t('Barcode') }}: {{ item.barcode }}
                      </span>
                      <span class="font-monospace" :class="selectedDuplicateIndex === idx ? 'text-white' : ''">
                        {{ $t('Stock') }}: <strong :class="selectedDuplicateIndex === idx ? 'text-warning' : (getItemStock(item) > 0 ? 'text-success' : 'text-danger')">{{ $bnNum(getItemStock(item)) }}</strong>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="text-end">
                  <div class="fs-5 fw-bold font-monospace" :class="selectedDuplicateIndex === idx ? 'text-white' : 'text-success'">
                    {{ $t('Tk.') }} {{ $bnNum(formatPrice(getItemPrice(item))) }}
                  </div>
                  <button
                    type="button"
                    class="btn btn-sm mt-1 px-3 fw-bold"
                    :class="selectedDuplicateIndex === idx ? 'btn-light text-primary shadow-sm' : 'btn-outline-primary'"
                  >
                    <i class="fas fa-check me-1"></i> {{ $t('Select') }} [{{ $bnNum(idx + 1) }}]
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-2 d-flex justify-content-between align-items-center bg-white">
            <div class="small text-muted font-monospace">
              {{ $t('Press') }} <kbd>1</kbd>-<kbd>{{ Math.min(duplicateBarcodeItems.length, 9) }}</kbd> {{ $t('or') }} <kbd>↑</kbd><kbd>↓</kbd> {{ $t('then') }} <kbd>Enter</kbd>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" @click="closeDuplicateModal">{{ $t('Cancel') }} (Esc)</button>
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
            <h5 class="modal-title fw-bold fs-6"><i class="fas fa-box-open me-2"></i>{{ $t('Select Color, Size & Serial') }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeItemModal"></button>
          </div>
          <div class="modal-body p-3" v-if="activeItem">
            <div class="d-flex align-items-center gap-3 mb-3 p-2 border rounded bg-light">
              <img v-if="activeItem.image" :src="activeItem.image" class="img-fluid rounded border" style="height: 60px;" alt="Product">
              <div>
                <h6 class="fw-bold text-dark mb-1">{{ activeItem.title }}</h6>
                <small class="text-muted font-monospace me-2">{{ $t('Barcode') }}: {{ activeItem.barcode }}</small>
                <span class="badge bg-dark" v-if="activeItem.unit">{{ activeItem.unit.title }}</span>
              </div>
            </div>

            <div class="row g-3">
              <!-- Color Selection -->
              <div class="col-6" v-if="availableColors && availableColors.length > 0">
                <label class="form-label fw-bold small text-muted">{{ $t('Color') }}</label>
                <select
                  ref="modalColorSelect"
                  class="form-select form-select-sm"
                  v-model="modalSelection.color_id"
                  @change="onVariantChange"
                  @keydown.enter.prevent="focusNextModalInput('size')"
                >
                  <option :value="null">{{ $t('-- Standard / Any Color --') }}</option>
                  <option v-for="c in availableColors" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <!-- Size Selection -->
              <div class="col-6" v-if="availableSizes && availableSizes.length > 0">
                <label class="form-label fw-bold small text-muted">{{ $t('Size') }}</label>
                <select
                  ref="modalSizeSelect"
                  class="form-select form-select-sm"
                  v-model="modalSelection.size_id"
                  @change="onVariantChange"
                  @keydown.enter.prevent="focusNextModalInput('qty')"
                >
                  <option :value="null">{{ $t('-- Standard / Any Size --') }}</option>
                  <option v-for="s in availableSizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>

              <!-- Stock & Price Info -->
              <div class="col-12">
                <div class="p-2 border rounded d-flex align-items-center justify-content-between" :class="modalSelection.available_stock > 0 ? 'bg-white' : 'bg-danger bg-opacity-10 border-danger'">
                  <span class="small font-monospace">{{ $t('Available Stock:') }} <strong :class="modalSelection.available_stock > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'">{{ $bnNum(modalSelection.available_stock) }} {{ modalSelection.available_stock <= 0 ? '(' + $t('Out of Stock') + ')' : '' }}</strong></span>
                  <div class="text-end">
                    <div v-if="modalSelection.discount_amount > 0">
                      <small class="text-muted text-decoration-line-through me-1">{{ $t('Tk.') }} {{ $bnNum(formatPrice(modalSelection.base_rate)) }}</small>
                    </div>
                    <span class="small font-monospace">
                      <span class="badge me-1" :class="effectivePriceNature === 'wholesale' ? 'bg-primary' : 'bg-secondary'" style="font-size: 10px;">
                        {{ effectivePriceNature === 'wholesale' ? $t('Wholesale Price:') : $t('Retail Price:') }}
                      </span>
                      <strong class="text-success">{{ $t('Tk.') }} {{ $bnNum(formatPrice(modalSelection.rate)) }}</strong>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Serial No (For items with purchase serials or serialized items) -->
              <div class="col-12" v-if="activeItem && (activeItem.has_purchase_serials || activeItem.is_serialized || isElectronicsShop)">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-bold small text-muted mb-0">
                    {{ $t('Serial No / IMEI') }}
                    <span class="badge bg-primary text-white ms-1 font-monospace" v-if="serialTags.length > 0">
                      {{ $bnNum(serialTags.length) }} {{ $t('selected') }} (Qty: {{ $bnNum(modalSelection.qty) }})
                    </span>
                  </label>
                  <span class="badge bg-info text-dark" v-if="modalAvailableSerials && modalAvailableSerials.length > 0">
                    {{ $bnNum(modalAvailableSerials.length) }} {{ $t('available in stock') }}
                  </span>
                </div>

                <!-- Selected Serial Tags Badges -->
                <div class="d-flex flex-wrap gap-1 mb-1.5 p-1 bg-light border rounded align-items-center" v-if="serialTags.length > 0" style="max-height: 80px; overflow-y: auto;">
                  <span v-for="(stag, stIdx) in serialTags" :key="'stag_' + stIdx + '_' + stag" class="badge bg-primary text-white font-monospace d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 11px;">
                    <span>{{ stag }}</span>
                    <i class="fas fa-times cursor-pointer ms-1 text-white-50 hover-text-white" @click.stop="removeSerialTag(stIdx)" :title="$t('Remove')"></i>
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 ms-auto font-monospace" style="font-size: 10px;" @click="clearAllSerialTags" :title="$t('Clear All')">
                    <i class="fas fa-trash-alt me-1"></i>{{ $t('Clear') }}
                  </button>
                </div>

                <div class="input-group input-group-sm">
                  <input
                    ref="modalSerialInput"
                    type="text"
                    list="availableSerialsDatalist"
                    class="form-control form-control-sm font-monospace"
                    :placeholder="$t('Type or scan serial (Comma / Enter to add multiple)')"
                    v-model="modalSerialTyped"
                    @keydown="onModalSerialKeydown"
                    @blur="handleModalSerialEnter"
                  >
                  <button type="button" class="btn btn-outline-primary" @click="handleModalSerialEnter" :title="$t('Add Serial')">
                    <i class="fas fa-plus"></i>
                  </button>
                  <datalist id="availableSerialsDatalist">
                    <option v-for="(sn, snIdx) in modalAvailableSerials" :key="'avail_sn_opt_' + snIdx" :value="sn">{{ sn }}</option>
                  </datalist>
                </div>

                <!-- Quick select badges for available stock serials -->
                <div class="mt-1" v-if="modalAvailableSerials && modalAvailableSerials.length > 0">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-bold" style="font-size: 10.5px;">{{ $t('Click stock serial to select/deselect:') }}</small>
                    <button type="button" class="btn btn-xs btn-link p-0 text-primary fw-bold text-decoration-none" style="font-size: 10.5px;" @click="selectAllAvailableSerials">
                      {{ $t('Select All Available') }}
                    </button>
                  </div>
                  <div class="d-flex flex-wrap gap-1 p-1 bg-white border rounded" style="max-height: 90px; overflow-y: auto;">
                    <button
                      type="button"
                      v-for="(sn, snIdx) in modalAvailableSerials"
                      :key="'avail_sn_btn_' + snIdx + '_' + sn"
                      class="btn btn-xs py-0.5 px-1.5 font-monospace transition-all"
                      :class="serialTags.includes(sn) ? 'btn-primary shadow-sm fw-bold' : 'btn-outline-secondary'"
                      style="font-size: 11px;"
                      @click="toggleSerialTag(sn)"
                    >
                      <i class="fas fa-check me-1" v-if="serialTags.includes(sn)"></i>{{ sn }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Selling Price (Editable) -->
              <div class="col-6">
                <label class="form-label fw-bold small text-muted">{{ $t('Unit Rate') }}</label>
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
                <label class="form-label fw-bold small text-muted">{{ $t('Quantity') }} <span class="text-primary">[{{ $t('Enter = Add') }}]</span></label>
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
              <kbd>Enter</kbd> / <kbd>Ctrl+Enter</kbd> = {{ $t('Add to Cart') }} | <kbd>Esc</kbd> = {{ $t('Close') }}
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-secondary" @click="closeItemModal">{{ $t('Cancel') }} (Esc)</button>
              <button ref="modalAddBtn" type="button" class="btn btn-sm btn-primary px-4 fw-bold shadow-sm" @click="addToCartFromModal">
                <i class="fas fa-cart-plus me-1"></i> {{ $t('Add to Cart') }} (Enter)
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
              <th style="text-align: left; padding: 4px 0;">{{ $t('Item Description') }}</th>
              <th style="text-align: center; padding: 4px 0; width: 30px;">{{ $t('Qty') }}</th>
              <th style="text-align: right; padding: 4px 0; width: 48px;">{{ $t('Rate') }}</th>
              <th style="text-align: right; padding: 4px 0; width: 55px;">{{ $t('Total') }}</th>
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

        <!-- 📜 Terms & Conditions in Thermal 80mm -->
        <div v-if="completedInvoice.terms_conditions && completedInvoice.terms_conditions.length > 0" style="margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px; font-size: 8.5px; line-height: 1.25;">
          <div style="font-weight: bold; margin-bottom: 2px;">TERMS & CONDITIONS:</div>
          <div v-for="(tc, tcIdx) in completedInvoice.terms_conditions" :key="tcIdx">
            • {{ tc }}
          </div>
        </div>

        <!-- 🏛️ Organization Memberships in Thermal 80mm -->
        <div v-if="siteMembershipsForInvoice && siteMembershipsForInvoice.length > 0" style="margin-top: 6px; border-top: 1px dashed #000; padding-top: 4px; text-align: center;">
          <div style="font-size: 8px; font-weight: bold; margin-bottom: 3px; text-transform: uppercase;">Member / Affiliated With</div>
          <div style="display: flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap;">
            <div v-for="(m, mIdx) in siteMembershipsForInvoice" :key="mIdx" style="display: inline-flex; align-items: center; gap: 3px; font-size: 8px;">
              <img v-if="m.logo || m.logo_url" :src="m.logo_url || m.logo" style="max-height: 18px; max-width: 25px; object-fit: contain;" />
              <span>{{ m.org_name }}</span>
            </div>
          </div>
        </div>

        <div style="text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px; font-size: 9.5px; line-height: 1.3;">
          <div style="font-weight: bold;">Thank you for shopping with us!</div>
          <div v-if="!completedInvoice.terms_conditions || completedInvoice.terms_conditions.length === 0">Please preserve this receipt for warranty and returns within 7 days.</div>
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
              <th style="text-align: left; padding: 2px 0;">{{ $t('Item') }}</th>
              <th style="text-align: center; padding: 2px 0; width: 18px;">{{ $t('Q') }}</th>
              <th style="text-align: right; padding: 2px 0; width: 40px;">{{ $t('Total') }}</th>
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

        <!-- Terms & Conditions in Thermal 60mm -->
        <div v-if="completedInvoice.terms_conditions && completedInvoice.terms_conditions.length > 0" style="margin-top: 5px; border-top: 1px dashed #000; padding-top: 3px; font-size: 7.5px; line-height: 1.2;">
          <div style="font-weight: bold; margin-bottom: 1px;">Terms:</div>
          <div v-for="(tc, tcIdx) in completedInvoice.terms_conditions" :key="tcIdx">
            - {{ tc }}
          </div>
        </div>

        <!-- Organization Memberships in Thermal 60mm -->
        <div v-if="siteMembershipsForInvoice && siteMembershipsForInvoice.length > 0" style="margin-top: 4px; border-top: 1px dashed #000; padding-top: 2px; text-align: center; font-size: 7.5px;">
          <div v-for="(m, mIdx) in siteMembershipsForInvoice" :key="mIdx" style="display: inline-block; margin: 1px 3px;">
            {{ m.org_name }}
          </div>
        </div>

        <div style="text-align: center; margin-top: 6px; border-top: 1px dashed #000; padding-top: 4px; font-size: 8px;">
          <div>Thanks for visiting!</div>
          <div v-if="!completedInvoice.terms_conditions || completedInvoice.terms_conditions.length === 0">Preserve receipt for returns.</div>
        </div>
      </div>

      <!-- 3. 🖨️ Normal Printer Invoice (Dynamic Layouts: Layout 1 Classic, Layout 2 Modern, Layout 3 Compact) -->
      <div v-else key="pos-print-normal" class="normal-invoice-container" :class="effectivePrintFormat === 'normal-a5' ? 'normal-a5-wrapper' : 'normal-a4-wrapper'" style="max-width: 900px; margin: 0 auto;">
        <invoice-layout-1 
          v-if="selectedInvoiceLayout === 'layout1'"
          :data="completedInvoice"
          :site-setting="siteSetting"
          :show-header-info="true"
        />
        <invoice-layout-2 
          v-else-if="selectedInvoiceLayout === 'layout2'"
          :data="completedInvoice"
          :site-setting="siteSetting"
          :show-header-info="true"
        />
        <invoice-layout-3 
          v-else-if="selectedInvoiceLayout === 'layout3'"
          :data="completedInvoice"
          :site-setting="siteSetting"
          :show-header-info="true"
        />
        <invoice-layout-1 
          v-else
          :data="completedInvoice"
          :site-setting="siteSetting"
          :show-header-info="true"
        />
      </div>
    </div>

    <!-- ℹ️ POS Bengali Help Info Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1060;" v-if="showHelpModal">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
          <div class="modal-header bg-dark text-white py-2">
            <h5 class="modal-title fs-6 text-white"><i class="fas fa-cash-register me-2 text-warning"></i>{{ $t('POS Terminal Help') }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="showHelpModal = false"></button>
          </div>
          <div class="modal-body p-3">
            <div v-if="posHelpContent" v-html="posHelpContent"></div>
            <div v-else class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-1"></i> {{ $t('Loading help...') }}</div>
          </div>
          <div class="modal-footer py-1">
            <button type="button" class="btn btn-sm btn-secondary" @click="showHelpModal = false">{{ $t('Close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import InvoiceLayout1 from '../invoice/components/InvoiceLayout1.vue';
import InvoiceLayout2 from '../invoice/components/InvoiceLayout2.vue';
import InvoiceLayout3 from '../invoice/components/InvoiceLayout3.vue';

export default {
  components: {
    InvoiceLayout1,
    InvoiceLayout2,
    InvoiceLayout3,
  },
  data() {
    return {
      showHelpModal: false,
      posHelpContent: '',
      currentDate: new Date().toLocaleDateString('en-GB'),
      client: { id: null, name: '', mobile: '', customer_type: 'retail', address: '', current_due: 0, coupon_enabled: false, points_balance: 0, points_value_in_tk: 0, point_redeem_rate: 10, point_earn_rate: 1, min_points_to_redeem: 10 },
      customerSearchTerm: '',
      customerSearchResults: [],
      selectedCustomerIndex: -1,
      showNewClientForm: false,
      newClient: { name: '', mobile: '', customer_type: 'retail', address: '' },
      activeDiscounts: [],

      searchTerm: '',
      searchResults: [],
      selectedSearchIndex: -1,
      allColors: [],
      allSizes: [],

      showItemModal: false,
      activeItem: null,
      modalAvailableSerials: [],
      serialTags: [],
      modalSerialTyped: '',
      modalSelection: {
        color_id: null,
        size_id: null,
        serial_no: '',
        qty: 1,
        base_rate: 0,
        discount_amount: 0,
        discount_title: '',
        discount_display: '',
        rate: 0,
        available_stock: 0
      },

      showDuplicateBarcodeModal: false,
      duplicateBarcodeItems: [],
      duplicateBarcodeScanned: '',
      selectedDuplicateIndex: 0,

      cart: [],
      discount: 0,
      points_to_redeem: 0,
      is_vat_applicable: false,
      vat_percent: 0,
      vat: 0,
      payment_method: 'Cash',
      mbanking_type: '',
      trxid: '',
      paid_amount: 0,

      // 📜 Terms & Conditions State
      invoiceTerms: [],
      rawDefaultTerms: [],
      showTermsSection: true,

      isSubmitting: false,
      completedInvoice: null,
    };
  },
  computed: {
    localizedCurrentDate() {
      const now = new Date();
      if (this.$locale === 'bn') {
        const months = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        const d = this.$bnNum(now.getDate());
        const m = months[now.getMonth()];
        const y = this.$bnNum(now.getFullYear());
        return `${d} ${m}, ${y}`;
      } else if (this.$locale === 'hi') {
        const months = ['जनवरी', 'फरवरी', 'मार्च', 'अप्रैल', 'मई', 'जून', 'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर', 'नवंबर', 'दिसंबर'];
        const d = now.getDate();
        const m = months[now.getMonth()];
        const y = now.getFullYear();
        return `${d} ${m}, ${y}`;
      } else if (this.$locale === 'fr') {
        return now.toLocaleDateString('fr-FR');
      } else if (this.$locale === 'es') {
        return now.toLocaleDateString('es-ES');
      }
      return now.toLocaleDateString('en-GB');
    },
    showPosTermsConfig() {
      return !!(this.$root.site?.show_pos_terms == 1 || this.$root.site?.show_pos_terms === true || this.$root.site?.show_pos_terms === '1');
    },
    selectedTermsList() {
      return (this.invoiceTerms || [])
        .filter(t => t.selected && t.condition && t.condition.trim() !== '')
        .map(t => t.condition.trim());
    },
    siteMembershipsForInvoice() {
      const mem = this.$root.site?.memberships;
      let list = [];
      if (Array.isArray(mem)) {
        list = mem;
      } else if (typeof mem === 'string') {
        try {
          list = JSON.parse(mem) || [];
        } catch (e) {
          list = [];
        }
      }
      return list.filter(m => m && (m.show_in_invoice === 1 || m.show_in_invoice === true || m.show_in_invoice === '1'));
    },
    cartTotalQty() {
      return this.cart.reduce((sum, i) => sum + (floatval(i.qty) || 0), 0);
    },
    cartSubtotal() {
      return this.cart.reduce((sum, i) => {
        const base = floatval(i.base_rate) > 0 ? floatval(i.base_rate) : floatval(i.rate);
        return sum + (floatval(i.qty) * base);
      }, 0);
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
        const colorIdsFromPrices = itemPrices.map(p => p.color_id).filter(id => id !== null && id !== undefined && id !== '');
        const colorIdsFromStock = stockSummaries.map(s => s.color_id).filter(id => id !== null && id !== undefined && id !== '');
        const allColorIds = Array.from(new Set([...colorIdsFromPrices, ...colorIdsFromStock]));
        if (allColorIds.length > 0) {
          return this.allColors.filter(c => allColorIds.includes(c.id));
        }
        return [];
      }
      return this.allColors;
    },
    availableSizes() {
      if (this.activeItem) {
        const itemPrices = this.activeItem.item_prices || this.activeItem.itemPrices || [];
        const stockSummaries = this.activeItem.stock_summaries || this.activeItem.stockSummaries || [];
        const sizeIdsFromPrices = itemPrices.map(p => p.size_id).filter(id => id !== null && id !== undefined && id !== '');
        const sizeIdsFromStock = stockSummaries.map(s => s.size_id).filter(id => id !== null && id !== undefined && id !== '');
        const allSizeIds = Array.from(new Set([...sizeIdsFromPrices, ...sizeIdsFromStock]));
        if (allSizeIds.length > 0) {
          return this.allSizes.filter(s => allSizeIds.includes(s.id));
        }
        return [];
      }
      return this.allSizes;
    },
    isGroceryShop() {
      const shopType = (this.$root.site?.shop_type || '').toLowerCase();
      return shopType === 'grocery' || shopType === 'departmental' || shopType === 'departmental_store';
    },
    isElectronicsShop() {
      const shopType = (this.$root.site?.shop_type || '').toLowerCase();
      return shopType === 'electronics';
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
    siteSetting() {
      return this.$root?.site || {};
    },
    selectedInvoiceLayout() {
      const l = (this.$root.site?.invoice_layout || 'layout1').toString().toLowerCase();
      if (l === 'layout2' || l === '2' || l === 'modern') return 'layout2';
      if (l === 'layout3' || l === '3' || l === 'compact') return 'layout3';
      return 'layout1';
    },
    effectivePriceNature() {
      return this.getEffectivePriceNature();
    },
  },
  watch: {
    cartSubtotal() {
      if (this.is_vat_applicable && floatval(this.vat_percent) > 0) {
        this.calculateVatAmount();
      }
    },
    totalDiscount() {
      if (this.is_vat_applicable && floatval(this.vat_percent) > 0) {
        this.calculateVatAmount();
      }
    },
    '$root.site': {
      immediate: true,
      deep: true,
      handler(site) {
        if (site && site.default_vat !== undefined && site.default_vat !== null) {
          this.initVatFromSettings();
        }
      }
    }
  },
  methods: {
    loadInvoiceTerms() {
      axios.get('termsCondition/by-module/Invoice')
        .then(res => {
          const list = res.data || [];
          if (list.length > 0) {
            this.rawDefaultTerms = JSON.parse(JSON.stringify(list));
            this.invoiceTerms = list.map(item => ({
              id: item.id,
              condition: item.condition_text || item.condition || '',
              selected: item.is_default == 1 || item.is_default === true,
              is_default: item.is_default == 1 || item.is_default === true ? 1 : 0,
            }));
          } else {
            this.invoiceTerms = [];
            this.rawDefaultTerms = [];
          }
        })
        .catch(err => {
          console.error('Failed to load invoice terms:', err);
        });
    },
    addCustomTerm() {
      this.invoiceTerms.push({
        id: null,
        condition: '',
        selected: true,
        is_default: 0
      });
      this.showTermsSection = true;
    },
    removeCustomTerm(idx) {
      this.invoiceTerms.splice(idx, 1);
    },
    resetInvoiceTerms() {
      if (this.rawDefaultTerms && this.rawDefaultTerms.length > 0) {
        this.invoiceTerms = JSON.parse(JSON.stringify(this.rawDefaultTerms)).map(item => ({
          id: item.id,
          condition: item.condition_text || item.condition || '',
          selected: item.is_default == 1 || item.is_default === true,
          is_default: item.is_default == 1 || item.is_default === true ? 1 : 0,
        }));
        this.$toast('Invoice terms reset to default', 'info');
      }
    },
    openHelpModal() {
      this.showHelpModal = true;
      if (!this.posHelpContent) {
        axios.get('helpInfo/Pos/index').then(res => {
          this.posHelpContent = res.data?.description || '';
        });
      }
    },
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

      let stylesHtml = "";
      for (const node of [
        ...document.querySelectorAll('link[rel="stylesheet"], style'),
      ]) {
        stylesHtml += node.outerHTML;
      }

      const WinPrint = window.open('', '', 'left=0,top=0,width=850,height=900,toolbar=0,scrollbars=1,status=0');
      WinPrint.document.write(`<!DOCTYPE html>
      <html>
      <head>
        <title>Sales Invoice - ${invoiceNo}</title>
        <meta charset="utf-8">
        ${stylesHtml}
        <style>
          * { box-sizing: border-box !important; }
          ${pageStyles}
          @media print {
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
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
    loadActiveDiscounts() {
      axios.get('pos/active-discounts')
        .then(res => {
          this.activeDiscounts = res.data || [];
          this.recalculateCartPrices();
        })
        .catch(err => {
          console.error('Failed to load active discounts:', err);
        });
    },
    getEffectivePriceNature(customerType = null) {
      const saleNature = (this.$root.site?.sale_nature || 'both').toLowerCase();
      if (saleNature === 'retail') return 'retail';
      if (saleNature === 'wholesale') return 'wholesale';
      
      const cType = (customerType || this.client?.customer_type || 'retail').toLowerCase();
      return cType === 'wholesale' ? 'wholesale' : 'retail';
    },
    resolveItemPrice(item, colorId = null, sizeId = null, customerType = null) {
      if (!item) return { basePrice: 0, priceNature: 'retail' };
      const priceNature = this.getEffectivePriceNature(customerType);
      const itemPrices = item.item_prices || item.itemPrices || [];
      
      let matchedPriceRow = null;
      if (itemPrices.length > 0) {
        matchedPriceRow = itemPrices.find(p => (p.color_id || null) == (colorId || null) && (p.size_id || null) == (sizeId || null));
        if (!matchedPriceRow && (colorId === null && sizeId === null)) {
          matchedPriceRow = itemPrices.find(p => p.color_id === null && p.size_id === null) || itemPrices[0];
        }
      }

      let basePrice = 0;
      if (matchedPriceRow) {
        if (priceNature === 'wholesale') {
          if (floatval(matchedPriceRow.wholesale_price) > 0) {
            basePrice = floatval(matchedPriceRow.wholesale_price);
          } else if (floatval(matchedPriceRow.whole_sale_price) > 0) {
            basePrice = floatval(matchedPriceRow.whole_sale_price);
          } else if (floatval(matchedPriceRow.retail_price) > 0) {
            basePrice = floatval(matchedPriceRow.retail_price);
          } else if (floatval(matchedPriceRow.selling_price) > 0) {
            basePrice = floatval(matchedPriceRow.selling_price);
          }
        } else { // retail
          if (floatval(matchedPriceRow.retail_price) > 0) {
            basePrice = floatval(matchedPriceRow.retail_price);
          } else if (floatval(matchedPriceRow.selling_price) > 0) {
            basePrice = floatval(matchedPriceRow.selling_price);
          } else if (floatval(matchedPriceRow.wholesale_price) > 0) {
            basePrice = floatval(matchedPriceRow.wholesale_price);
          } else if (floatval(matchedPriceRow.whole_sale_price) > 0) {
            basePrice = floatval(matchedPriceRow.whole_sale_price);
          }
        }
      }

      if (basePrice <= 0) {
        if (priceNature === 'wholesale') {
          if (floatval(item.wholesale_price) > 0) {
            basePrice = floatval(item.wholesale_price);
          } else if (floatval(item.whole_sale_price) > 0) {
            basePrice = floatval(item.whole_sale_price);
          } else if (floatval(item.retail_price) > 0) {
            basePrice = floatval(item.retail_price);
          } else if (floatval(item.sale_price || item.selling_price || item.opening_rate || 0) > 0) {
            basePrice = floatval(item.sale_price || item.selling_price || item.opening_rate || 0);
          }
        } else { // retail
          if (floatval(item.retail_price) > 0) {
            basePrice = floatval(item.retail_price);
          } else if (floatval(item.sale_price || item.selling_price || item.opening_rate || 0) > 0) {
            basePrice = floatval(item.sale_price || item.selling_price || item.opening_rate || 0);
          } else if (floatval(item.wholesale_price) > 0) {
            basePrice = floatval(item.wholesale_price);
          } else if (floatval(item.whole_sale_price) > 0) {
            basePrice = floatval(item.whole_sale_price);
          }
        }
      }

      return { basePrice, priceNature };
    },
    calculateItemDiscount(item, basePrice, priceNature = null) {
      if (!item || floatval(basePrice) <= 0 || !this.activeDiscounts || this.activeDiscounts.length === 0) {
        return { discountAmount: 0, finalRate: floatval(basePrice), discountObj: null, discountTitle: '', discountDisplay: '' };
      }

      const nature = priceNature || this.getEffectivePriceNature();
      const today = new Date().toISOString().split('T')[0];

      // Filter discounts applicable on date & nature
      const validDiscounts = this.activeDiscounts.filter(d => {
        if (d.status != 1 && d.status !== true && d.status !== 'active') return false;
        if (d.valid_from && d.valid_from > today) return false;
        if (d.valid_to && d.valid_to < today) return false;
        if (d.applicable_on && d.applicable_on !== 'both' && d.applicable_on !== nature) return false;
        return true;
      });

      // 1. Priority: Item-wise discount
      let matchedDiscount = validDiscounts.find(d => {
        if (d.scope !== 'item') return false;
        if (Array.isArray(d.items)) {
          return d.items.map(Number).includes(Number(item.id));
        }
        if (d.items && typeof d.items === 'object') {
          return Object.values(d.items).map(Number).includes(Number(item.id));
        }
        if (typeof d.items === 'string') {
          try {
            const parsed = JSON.parse(d.items);
            if (Array.isArray(parsed)) return parsed.map(Number).includes(Number(item.id));
          } catch(e) {
            return d.items.split(',').map(s => Number(s.trim())).includes(Number(item.id));
          }
        }
        return d.item_id == item.id;
      });

      // 2. Fallback: Category-wise discount
      if (!matchedDiscount && item.category_id) {
        matchedDiscount = validDiscounts.find(d => d.scope === 'category' && d.category_id == item.category_id);
      }

      if (!matchedDiscount) {
        return { discountAmount: 0, finalRate: floatval(basePrice), discountObj: null, discountTitle: '', discountDisplay: '' };
      }

      const discVal = floatval(matchedDiscount.discount_value);
      let discountAmount = 0;
      let discountDisplay = '';

      if (matchedDiscount.discount_type === 'percentage') {
        discountAmount = (floatval(basePrice) * discVal) / 100;
        discountDisplay = `${discVal}%`;
      } else { // fixed
        discountAmount = Math.min(floatval(basePrice), discVal);
        discountDisplay = `৳${discVal}`;
      }

      discountAmount = Number(discountAmount.toFixed(2));
      const finalRate = Math.max(0, Number((floatval(basePrice) - discountAmount).toFixed(2)));

      return {
        discountAmount,
        finalRate,
        discountObj: matchedDiscount,
        discountTitle: matchedDiscount.title,
        discountDisplay
      };
    },
    getItemPrice(item) {
      if (!item) return 0;
      const { basePrice, priceNature } = this.resolveItemPrice(item);
      const { finalRate } = this.calculateItemDiscount(item, basePrice, priceNature);
      return finalRate;
    },
    recalculateCartPrices() {
      if (!this.cart || this.cart.length === 0) return;

      this.cart.forEach(cItem => {
        const itemObj = cItem.item_object || { id: cItem.item_id, category_id: cItem.category_id, retail_price: cItem.retail_price, wholesale_price: cItem.wholesale_price, item_prices: cItem.item_prices };
        const { basePrice, priceNature } = this.resolveItemPrice(itemObj, cItem.color_id, cItem.size_id);
        const { discountAmount, finalRate, discountTitle, discountDisplay } = this.calculateItemDiscount(itemObj, basePrice, priceNature);

        cItem.base_rate = basePrice;
        cItem.discount_amount = discountAmount;
        cItem.discount_title = discountTitle || '';
        cItem.discount_display = discountDisplay || '';
        cItem.rate = finalRate;
      });

      this.syncCartDiscount();

      if (this.is_vat_applicable && floatval(this.vat_percent) > 0) {
        this.calculateVatAmount();
      }
    },
    onCustomerSearchInput() {
      const term = (this.customerSearchTerm || '').trim();
      if (!term || term.length < 1) {
        this.customerSearchResults = [];
        this.selectedCustomerIndex = -1;
        return;
      }
      axios.get('pos/search-customers', { params: { term: term } })
        .then(res => {
          this.customerSearchResults = res.data || [];
          this.selectedCustomerIndex = this.customerSearchResults.length > 0 ? 0 : -1;
        })
        .catch(err => {
          console.error('Customer search error:', err);
        });
    },
    navigateCustomerResults(step) {
      if (!this.customerSearchResults || this.customerSearchResults.length === 0) return;
      let newIndex = this.selectedCustomerIndex + step;
      if (newIndex < 0) {
        newIndex = this.customerSearchResults.length - 1;
      } else if (newIndex >= this.customerSearchResults.length) {
        newIndex = 0;
      }
      this.selectedCustomerIndex = newIndex;
    },
    handleCustomerSearchEnter() {
      if (this.customerSearchResults && this.customerSearchResults.length > 0 && this.selectedCustomerIndex >= 0) {
        this.selectCustomer(this.customerSearchResults[this.selectedCustomerIndex]);
        return;
      }
      this.searchCustomer();
    },
    selectCustomer(cust) {
      if (!cust) return;
      this.client = cust;
      this.customerSearchTerm = '';
      this.customerSearchResults = [];
      this.selectedCustomerIndex = -1;
      this.showNewClientForm = false;
      this.recalculateCartPrices();
      this.$toast(`${this.$t('Customer selected:')} ${cust.name} (${cust.customer_type === 'wholesale' ? this.$t('Wholesale') : this.$t('Retail')})`, 'success');
    },
    toggleCustomerPriceType() {
      if (!this.client) return;
      const currentType = (this.client.customer_type || 'retail').toLowerCase();
      const newType = currentType === 'wholesale' ? 'retail' : 'wholesale';
      this.client.customer_type = newType;
      this.recalculateCartPrices();
      this.$toast(newType === 'wholesale' ? this.$t('Switched to Wholesale Price rate (পাইকারি দর)') : this.$t('Switched to Retail Price rate (খুচরা দর)'), 'info');
    },
    searchCustomer() {
      const term = (this.customerSearchTerm || this.client.mobile || '').trim();
      if (!term) return;
      axios.get(`pos/search-customer`, { params: { mobile: term, term: term } })
        .then(res => {
          if (res.data && res.data.id) {
            this.client = res.data;
            this.customerSearchTerm = '';
            this.customerSearchResults = [];
            this.selectedCustomerIndex = -1;
            this.showNewClientForm = false;
            this.recalculateCartPrices();
            this.$toast(`Client found: ${res.data.name} (${res.data.customer_type === 'wholesale' ? 'Wholesale' : 'Retail'})`, 'success');
          } else {
            this.customerSearchResults = [];
            this.showNewClientForm = true;
            this.newClient.mobile = /^\d+$/.test(term) ? term : '';
            this.newClient.name = !/^\d+$/.test(term) ? term : '';
            this.newClient.customer_type = 'retail';
            this.newClient.address = '';
            this.$toast('Client not found. Register a new client.', 'info');
            this.$nextTick(() => {
              if (this.newClient.mobile) {
                this.$refs.newClientNameInput?.focus();
              } else {
                this.$refs.newClientMobileInput?.focus();
              }
            });
          }
        })
        .catch(err => {
          this.customerSearchResults = [];
          this.showNewClientForm = true;
          this.newClient.mobile = /^\d+$/.test(term) ? term : '';
          this.newClient.name = !/^\d+$/.test(term) ? term : '';
          this.newClient.customer_type = 'retail';
          this.newClient.address = '';
          this.$toast('Client lookup failed. Fill details to register.', 'info');
          this.$nextTick(() => {
            this.$refs.newClientNameInput?.focus();
          });
        });
    },
    resetClient() {
      this.client = { id: null, name: '', mobile: '', customer_type: 'retail', address: '', current_due: 0, coupon_enabled: false, points_balance: 0, points_value_in_tk: 0, point_redeem_rate: 10, point_earn_rate: 1, min_points_to_redeem: 10 };
      this.customerSearchTerm = '';
      this.customerSearchResults = [];
      this.selectedCustomerIndex = -1;
      this.showNewClientForm = false;
      this.newClient = { name: '', mobile: '', customer_type: 'retail', address: '' };
      this.recalculateCartPrices();
      this.$nextTick(() => {
        this.$refs.clientSearchInput?.focus();
      });
    },
    createQuickCustomer() {
      const mobile = (this.newClient.mobile || '').trim();
      if (!mobile) {
        this.$toast('Mobile number is required', 'warning');
        return;
      }
      if (!/^\d{11}$/.test(mobile)) {
        this.$toast('Mobile number must be exactly 11 digits', 'warning');
        return;
      }
      if (!this.newClient.name || !this.newClient.name.trim()) {
        this.$toast('Client name is required', 'warning');
        return;
      }

      axios.post('pos/quick-customer', {
        mobile: mobile,
        name: this.newClient.name.trim(),
        customer_type: this.newClient.customer_type || 'retail',
        address: this.newClient.address ? this.newClient.address.trim() : ''
      }).then(res => {
        if (res.data) {
          this.client = res.data;
          this.showNewClientForm = false;
          this.recalculateCartPrices();
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
    getItemStock(item) {
      if (!item) return 0;
      const stockSummaries = item.stock_summaries || item.stockSummaries || [];
      if (stockSummaries.length > 0) {
        return stockSummaries.reduce((sum, s) => sum + floatval(s.current_stock), 0);
      }
      return floatval(item.current_stock || item.opening_stock || 0);
    },
    isSimpleProduct(item) {
      if (!item) return true;

      // 1. Serial requirement: If serial numbers exist in purchase/GRN records or item is specifically serialized
      const hasPurchaseSerials = !!(item.has_purchase_serials || item.is_serialized);
      if (hasPurchaseSerials) {
        return false;
      }

      // 2. Color / Size check: Check if stock has color or size data
      const stockSummaries = item.stock_summaries || item.stockSummaries || [];
      const hasColorOrSizeInStock = stockSummaries.some(s => 
        (s.color_id !== null && s.color_id !== undefined && s.color_id !== '') ||
        (s.size_id !== null && s.size_id !== undefined && s.size_id !== '')
      );

      if (hasColorOrSizeInStock) {
        return false;
      }

      // 3. If stockSummaries is empty (0 stock), check if itemPrices has colors/sizes defined
      if (stockSummaries.length === 0) {
        const itemPrices = item.item_prices || item.itemPrices || [];
        const hasColorOrSizeInPrices = itemPrices.some(p => 
          (p.color_id !== null && p.color_id !== undefined && p.color_id !== '') ||
          (p.size_id !== null && p.size_id !== undefined && p.size_id !== '')
        );
        if (hasColorOrSizeInPrices) {
          return false;
        }
      }

      // Otherwise (no color, no size, no purchase serials) -> Simple Product (direct add to cart)
      return true;
    },
    selectItem(item) {
      if (!item) return;
      this.processSelectedItem(item);
    },
    processSelectedItem(item, scannedSerial = '') {
      if (!item) return;

      if (scannedSerial) {
        const cleanSerial = scannedSerial.trim();
        const existingCartItem = this.cart.find(c => c.item_id === item.id);
        if (existingCartItem) {
          const currentSerials = (existingCartItem.serial_no || '').split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
          if (currentSerials.map(s => s.toLowerCase()).includes(cleanSerial.toLowerCase())) {
            this.$toast(`সিরিয়াল নম্বর "${cleanSerial}" ইতিপূর্বে কার্টে যোগ করা হয়েছে!`, 'warning');
            return;
          }
          currentSerials.push(cleanSerial);
          existingCartItem.serial_no = currentSerials.join(', ');
          existingCartItem.qty = currentSerials.length;
          this.syncCartDiscount();
          this.$toast(`"${item.title}" এ সিরিয়াল (${cleanSerial}) যোগ করা হয়েছে (Qty: ${existingCartItem.qty})`, 'success');
          this.searchTerm = '';
          this.searchResults = [];
          this.$nextTick(() => {
            this.$refs.itemSearchInput?.focus();
          });
          return;
        }
      }

      if (this.isSimpleProduct(item) && !scannedSerial) {
        this.addSimpleItemToCart(item);
      } else {
        this.openItemModal(item, scannedSerial);
      }
    },
    addSimpleItemToCart(item) {
      this.searchTerm = '';
      this.searchResults = [];
      this.selectedSearchIndex = -1;
      this.showDuplicateBarcodeModal = false;

      const availableStock = this.getItemStock(item);

      if (availableStock <= 0) {
        this.$toast(`"${item.title}" এর স্টক খালি!`, 'warning');
        return;
      }

      // If single variant with positive stock exists, pick its color/size
      const stockSummaries = item.stock_summaries || item.stockSummaries || [];
      const positiveStockRows = stockSummaries.filter(s => floatval(s.current_stock) > 0);
      let colorId = null;
      let sizeId = null;
      let colorTitle = null;
      let sizeTitle = null;

      if (positiveStockRows.length === 1) {
        const singleVar = positiveStockRows[0];
        if (singleVar.color_id) {
          colorId = singleVar.color_id;
          colorTitle = singleVar.color?.title || ((this.allColors || []).find(c => c && c.id == colorId)?.title) || null;
        }
        if (singleVar.size_id) {
          sizeId = singleVar.size_id;
          sizeTitle = singleVar.size?.title || ((this.allSizes || []).find(s => s && s.id == sizeId)?.title) || null;
        }
      }

      const { basePrice, priceNature } = this.resolveItemPrice(item, colorId, sizeId);
      const { discountAmount, finalRate, discountTitle, discountDisplay } = this.calculateItemDiscount(item, basePrice, priceNature);

      const existingCartIndex = this.cart.findIndex(c =>
        c.item_id === item.id &&
        (c.color_id || null) == (colorId || null) &&
        (c.size_id || null) == (sizeId || null) &&
        (!c.serial_no || c.serial_no === '')
      );

      if (existingCartIndex > -1) {
        const currentQty = floatval(this.cart[existingCartIndex].qty);
        if (currentQty + 1 > availableStock) {
          this.$toast(`পর্যাপ্ত স্টক নেই! সর্বোচ্চ প্রাপ্য স্টক: ${availableStock}`, 'warning');
          return;
        }
        this.cart[existingCartIndex].qty = currentQty + 1;
      } else {
        this.cart.push({
          item_id: item.id,
          item_object: item,
          category_id: item.category_id,
          title: item.title,
          barcode: item.barcode,
          color_id: colorId,
          color_title: colorTitle,
          size_id: sizeId,
          size_title: sizeTitle,
          serial_no: '',
          qty: 1,
          base_rate: basePrice,
          discount_amount: discountAmount,
          discount_title: discountTitle || '',
          discount_display: discountDisplay || '',
          rate: finalRate,
          available_stock: availableStock,
        });
      }

      this.syncCartDiscount();

      this.$toast(`"${item.title}" কার্টে যোগ করা হয়েছে`, 'success');

      this.$nextTick(() => {
        this.$refs.itemSearchInput?.focus();
      });
    },
    selectDuplicateItem(item) {
      this.showDuplicateBarcodeModal = false;
      this.duplicateBarcodeItems = [];
      this.processSelectedItem(item);
    },
    closeDuplicateModal() {
      this.showDuplicateBarcodeModal = false;
      this.duplicateBarcodeItems = [];
      this.duplicateBarcodeScanned = '';
      this.$nextTick(() => {
        this.$refs.itemSearchInput?.focus();
      });
    },
    handleSearchEnter() {
      const term = (this.searchTerm || '').toString().trim();
      if (!term) return;

      if (this.searchResults && this.searchResults.length > 0) {
        const exactBarcodeMatches = this.searchResults.filter(it => it && it.barcode && String(it.barcode).trim().toLowerCase() === term.toLowerCase());
        if (exactBarcodeMatches.length > 1) {
          this.duplicateBarcodeItems = exactBarcodeMatches;
          this.duplicateBarcodeScanned = term;
          this.selectedDuplicateIndex = 0;
          this.showDuplicateBarcodeModal = true;
          return;
        }

        if (exactBarcodeMatches.length === 1) {
          this.processSelectedItem(exactBarcodeMatches[0]);
          return;
        }

        let selectedItem = null;
        if (this.selectedSearchIndex >= 0 && this.selectedSearchIndex < this.searchResults.length) {
          selectedItem = this.searchResults[this.selectedSearchIndex];
        } else if (this.searchResults.length > 0) {
          selectedItem = this.searchResults[0];
        }

        if (selectedItem) {
          this.processSelectedItem(selectedItem);
        }
      } else {
        axios.get('pos/search-items', { params: { term: term } })
          .then(res => {
            const items = res.data.items || [];
            if (items.length === 0) {
              this.$toast('কোন পণ্য পাওয়া যায়নি!', 'warning');
              return;
            }

            this.allColors = res.data.colors || [];
            this.allSizes = res.data.sizes || [];

            const exactBarcodeMatches = items.filter(it => it && it.barcode && String(it.barcode).trim().toLowerCase() === term.toLowerCase());
            if (exactBarcodeMatches.length > 1) {
              this.duplicateBarcodeItems = exactBarcodeMatches;
              this.duplicateBarcodeScanned = term;
              this.selectedDuplicateIndex = 0;
              this.showDuplicateBarcodeModal = true;
              return;
            }

            if (exactBarcodeMatches.length === 1) {
              this.processSelectedItem(exactBarcodeMatches[0]);
              return;
            }

            if (items.length === 1) {
              const singleItem = items[0];
              const isSerialSearch = term && singleItem.barcode !== term && singleItem.title !== term;
              this.processSelectedItem(singleItem, isSerialSearch ? term : '');
            } else {
              this.duplicateBarcodeItems = items;
              this.duplicateBarcodeScanned = term;
              this.selectedDuplicateIndex = 0;
              this.showDuplicateBarcodeModal = true;
            }
          })
          .catch(err => {
            console.error('POS search error:', err);
            this.$toast('পণ্য অনুসন্ধানে সমস্যা হয়েছে', 'error');
          });
      }
    },
    clearSearch() {
      this.searchTerm = '';
      this.searchResults = [];
      this.selectedSearchIndex = -1;
    },
    getSerialsCount(serialStr) {
      if (!serialStr || typeof serialStr !== 'string') return 0;
      return serialStr.split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean).length;
    },
    onCartItemSerialChange(cItem) {
      if (!cItem) return;
      const count = this.getSerialsCount(cItem.serial_no);
      if (count > 0) {
        cItem.qty = count;
      }
    },
    onCartItemQtyChange(cItem) {
      if (!cItem) return;
      const count = this.getSerialsCount(cItem.serial_no);
      if (count > 0 && cItem.qty < count) {
        cItem.qty = count;
      }
      this.syncCartDiscount();
    },
    onCartItemRateChange(cItem) {
      if (!cItem) return;
      const currentRate = floatval(cItem.rate);
      const baseRate = floatval(cItem.base_rate);
      if (baseRate > 0) {
        cItem.discount_amount = Math.max(0, Number((baseRate - currentRate).toFixed(2)));
      } else {
        cItem.base_rate = currentRate;
        cItem.discount_amount = 0;
      }
      this.syncCartDiscount();
    },
    syncCartDiscount() {
      const itemsDiscountTotal = this.cart.reduce((sum, item) => {
        const qty = floatval(item.qty || 0);
        const discAmt = floatval(item.discount_amount || 0);
        return sum + (discAmt * qty);
      }, 0);
      this.discount = Number(itemsDiscountTotal.toFixed(2));
      if (this.is_vat_applicable && floatval(this.vat_percent) > 0) {
        this.calculateVatAmount();
      }
    },
    toggleSerialTag(sn) {
      if (!sn) return;
      const clean = sn.trim();
      if (!clean) return;
      const idx = this.serialTags.findIndex(s => s.toLowerCase() === clean.toLowerCase());
      if (idx > -1) {
        this.serialTags.splice(idx, 1);
      } else {
        this.serialTags.push(clean);
      }
      this.syncSerialTagsToModal();
    },
    addSerialTag(sn) {
      if (!sn) return;
      const clean = sn.trim();
      if (!clean) return;
      const exists = this.serialTags.some(s => s.toLowerCase() === clean.toLowerCase());
      if (!exists) {
        this.serialTags.push(clean);
        this.syncSerialTagsToModal();
      }
    },
    removeSerialTag(index) {
      this.serialTags.splice(index, 1);
      this.syncSerialTagsToModal();
    },
    clearAllSerialTags() {
      this.serialTags = [];
      this.syncSerialTagsToModal();
    },
    selectAllAvailableSerials() {
      if (!this.modalAvailableSerials || this.modalAvailableSerials.length === 0) return;
      const set = new Set(this.serialTags);
      this.modalAvailableSerials.forEach(sn => {
        if (sn && sn.trim()) set.add(sn.trim());
      });
      this.serialTags = Array.from(set);
      this.syncSerialTagsToModal();
    },
    onModalSerialKeydown(e) {
      if (e.key === ',' || e.key === 'Enter') {
        e.preventDefault();
        this.handleModalSerialEnter();
      }
    },
    handleModalSerialEnter() {
      if (!this.modalSerialTyped || this.modalSerialTyped.trim() === '') return;
      const pieces = this.modalSerialTyped.split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
      pieces.forEach(p => {
        this.addSerialTag(p);
      });
      this.modalSerialTyped = '';
    },
    syncSerialTagsToModal() {
      this.modalSelection.serial_no = this.serialTags.join(', ');
      if (this.serialTags.length > 0) {
        this.modalSelection.qty = this.serialTags.length;
      } else if (!this.modalSelection.qty || this.modalSelection.qty < 1) {
        this.modalSelection.qty = 1;
      }
    },
    openItemModal(item, scannedSerial = '') {
      this.activeItem = item;
      this.searchResults = [];
      this.selectedSearchIndex = -1;
      this.searchTerm = '';
      this.modalAvailableSerials = item.available_serials || [];
      this.modalSerialTyped = '';
      this.serialTags = [];

      if (scannedSerial && scannedSerial.trim() !== '') {
        const split = scannedSerial.split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
        this.serialTags = Array.from(new Set(split));
      }

      let defaultColorId = null;
      let defaultSizeId = null;
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

      const { basePrice, priceNature } = this.resolveItemPrice(item, defaultColorId, defaultSizeId);
      const { discountAmount, finalRate, discountTitle, discountDisplay } = this.calculateItemDiscount(item, basePrice, priceNature);

      const initialQty = this.serialTags.length > 0 ? this.serialTags.length : 1;
      const initialSerialNo = this.serialTags.join(', ');

      this.modalSelection = {
        color_id: defaultColorId,
        size_id: defaultSizeId,
        serial_no: initialSerialNo,
        qty: initialQty,
        base_rate: basePrice,
        discount_amount: discountAmount,
        discount_title: discountTitle || '',
        discount_display: discountDisplay || '',
        rate: finalRate,
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
        } else if (this.activeItem && (this.activeItem.has_purchase_serials || this.activeItem.is_serialized || this.isElectronicsShop) && this.$refs.modalSerialInput) {
          this.$refs.modalSerialInput.focus();
        } else if (this.$refs.modalQtyInput) {
          this.$refs.modalQtyInput.focus();
          this.$refs.modalQtyInput.select();
        }
      });
    },
    closeItemModal() {
      this.showItemModal = false;
      this.activeItem = null;
      this.serialTags = [];
      this.modalSerialTyped = '';
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

      const stockSummaries = this.activeItem.stock_summaries || this.activeItem.stockSummaries || [];

      // Price and discount lookup
      const { basePrice, priceNature } = this.resolveItemPrice(this.activeItem, this.modalSelection.color_id, this.modalSelection.size_id);
      const { discountAmount, finalRate, discountTitle, discountDisplay } = this.calculateItemDiscount(this.activeItem, basePrice, priceNature);

      this.modalSelection.base_rate = basePrice;
      this.modalSelection.discount_amount = discountAmount;
      this.modalSelection.discount_title = discountTitle || '';
      this.modalSelection.discount_display = discountDisplay || '';
      this.modalSelection.rate = finalRate;

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

      // 1. Process any pending typed serials in input
      if (this.modalSerialTyped && this.modalSerialTyped.trim() !== '') {
        this.handleModalSerialEnter();
      }

      // Sync serials
      if (this.serialTags.length > 0) {
        this.modalSelection.serial_no = this.serialTags.join(', ');
        this.modalSelection.qty = this.serialTags.length;
      }

      // Stock Check: Available stock must be > 0
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

      const colorObj = (this.allColors || []).find(c => c && c.id == this.modalSelection.color_id);
      const sizeObj = (this.allSizes || []).find(s => s && s.id == this.modalSelection.size_id);

      // Check if identical item+color+size is already in cart, increment quantity or merge serials
      const existingCartIndex = this.cart.findIndex(c => 
        c.item_id === this.activeItem.id && 
        (c.color_id || null) == (this.modalSelection.color_id || null) && 
        (c.size_id || null) == (this.modalSelection.size_id || null)
      );

      if (existingCartIndex > -1) {
        const existing = this.cart[existingCartIndex];
        if (this.modalSelection.serial_no) {
          const existingSerials = (existing.serial_no || '').split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
          const newSerials = (this.modalSelection.serial_no || '').split(/[\r\n,;]+/).map(s => s.trim()).filter(Boolean);
          const combinedSerials = Array.from(new Set([...existingSerials, ...newSerials]));
          
          if (combinedSerials.length > this.modalSelection.available_stock) {
            this.$toast(`পর্যাপ্ত স্টক নেই! সর্বোচ্চ প্রাপ্য স্টক: ${this.modalSelection.available_stock}`, 'warning');
            return;
          }
          existing.serial_no = combinedSerials.join(', ');
          existing.qty = combinedSerials.length;
        } else {
          const currentQty = floatval(existing.qty);
          const addQty = floatval(this.modalSelection.qty) || 1;
          const newQty = currentQty + addQty;
          if (newQty > this.modalSelection.available_stock) {
            this.$toast(`পর্যাপ্ত স্টক নেই! সর্বোচ্চ প্রাপ্য স্টক: ${this.modalSelection.available_stock}`, 'warning');
            return;
          }
          existing.qty = newQty;
        }
      } else {
        const finalQty = this.serialTags.length > 0 ? this.serialTags.length : (this.modalSelection.qty || 1);
        this.cart.push({
          item_id: this.activeItem.id,
          item_object: this.activeItem,
          category_id: this.activeItem.category_id,
          title: this.activeItem.title,
          barcode: this.activeItem.barcode,
          color_id: this.modalSelection.color_id,
          color_title: colorObj ? colorObj.title : null,
          size_id: this.modalSelection.size_id,
          size_title: sizeObj ? sizeObj.title : null,
          serial_no: this.modalSelection.serial_no || '',
          qty: finalQty,
          base_rate: this.modalSelection.base_rate || 0,
          discount_amount: this.modalSelection.discount_amount || 0,
          discount_title: this.modalSelection.discount_title || '',
          discount_display: this.modalSelection.discount_display || '',
          rate: this.modalSelection.rate || 0,
          available_stock: this.modalSelection.available_stock,
        });
      }

      this.syncCartDiscount();
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
      this.syncCartDiscount();
    },
    clearCart() {
      this.cart = [];
      this.syncCartDiscount();
    },
    redeemAllPoints() {
      this.points_to_redeem = this.maxRedeemablePoints;
    },
    submitCheckout() {
      if (this.cart.length === 0) {
        this.$toast('Cart is empty', 'warning');
        return;
      }

      // 1. Walk-in customer validation: Paid amount cannot be 0
      const isWalkIn = !this.client.id || this.client.mobile === '00000000000' || this.client.name === 'Walk-in Customer';
      const paidVal = floatval(this.paid_amount);
      if (isWalkIn && paidVal <= 0) {
        this.$toast('Walk-in কাস্টমারের জন্য Paid Amount ০ রাখা যাবে না! অনুগ্রহ করে পেমেন্ট দিন অথবা কাস্টমার রেজিস্টার/সিলেক্ট করুন।', 'warning');
        return;
      }

      this.isSubmitting = true;

      const termsPayload = (this.invoiceTerms || [])
        .filter(t => t.selected && t.condition && t.condition.trim() !== '')
        .map(t => t.condition.trim());

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
        vat_percent: this.is_vat_applicable ? this.vat_percent : 0,
        payment_method: this.payment_method,
        mbanking_type: this.mbanking_type,
        trxid: this.trxid,
        paid_amount: this.paid_amount,
        terms_conditions: termsPayload,
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
    setFullPay() {
      if (this.cart.length === 0) {
        return;
      }
      this.paid_amount = Number(this.netPayable.toFixed(2));
      this.$nextTick(() => {
        this.$refs.paidAmountInput?.focus();
        this.$refs.paidAmountInput?.select();
      });
    },
    resetPOS() {
      this.client = { id: null, name: '', mobile: '', customer_type: 'retail', address: '', current_due: 0, coupon_enabled: false, points_balance: 0, points_value_in_tk: 0, point_redeem_rate: 10, point_earn_rate: 1, min_points_to_redeem: 10 };
      this.showNewClientForm = false;
      this.cart = [];
      this.discount = 0;
      this.points_to_redeem = 0;
      this.initVatFromSettings();
      this.paid_amount = 0;
      this.trxid = '';
      this.payment_method = 'Cash';
      this.searchTerm = '';
      this.searchResults = [];
      this.selectedSearchIndex = -1;
      this.showDuplicateBarcodeModal = false;
      this.duplicateBarcodeItems = [];
      this.duplicateBarcodeScanned = '';
      this.selectedDuplicateIndex = 0;
      this.loadActiveDiscounts();

      // Reset terms conditions to defaults
      if (this.rawDefaultTerms && this.rawDefaultTerms.length > 0) {
        this.invoiceTerms = JSON.parse(JSON.stringify(this.rawDefaultTerms)).map(item => ({
          id: item.id,
          condition: item.condition_text || item.condition || '',
          selected: item.is_default == 1 || item.is_default === true,
          is_default: item.is_default == 1 || item.is_default === true ? 1 : 0,
        }));
      }
    },
    handleKeydown(e) {
      if (e.target === this.$refs.itemSearchInput && e.key === 'Enter') {
        return;
      }

      if (this.showDuplicateBarcodeModal) {
        if (e.key === 'Escape') {
          e.preventDefault();
          this.closeDuplicateModal();
          return;
        }
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          if (this.duplicateBarcodeItems && this.duplicateBarcodeItems.length > 0) {
            this.selectedDuplicateIndex = (this.selectedDuplicateIndex + 1) % this.duplicateBarcodeItems.length;
          }
          return;
        }
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          if (this.duplicateBarcodeItems && this.duplicateBarcodeItems.length > 0) {
            this.selectedDuplicateIndex = (this.selectedDuplicateIndex - 1 + this.duplicateBarcodeItems.length) % this.duplicateBarcodeItems.length;
          }
          return;
        }
        if (e.key === 'Enter') {
          e.preventDefault();
          if (this.duplicateBarcodeItems && this.duplicateBarcodeItems[this.selectedDuplicateIndex]) {
            this.selectDuplicateItem(this.duplicateBarcodeItems[this.selectedDuplicateIndex]);
          }
          return;
        }
        // Numeric direct selection (1 to 9)
        if (e.key >= '1' && e.key <= '9') {
          const numIdx = parseInt(e.key) - 1;
          if (this.duplicateBarcodeItems && numIdx >= 0 && numIdx < this.duplicateBarcodeItems.length) {
            e.preventDefault();
            this.selectDuplicateItem(this.duplicateBarcodeItems[numIdx]);
            return;
          }
        }
        return;
      }

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
        const inp = this.$refs.clientSearchInput || this.$refs.clientMobileInput;
        inp?.focus();
        inp?.select();
      } else if (e.key === 'F7' || e.key === 'F9' || (e.altKey && (e.key === 'f' || e.key === 'F')) || (e.altKey && (e.key === 'p' || e.key === 'P'))) {
        e.preventDefault();
        this.setFullPay();
      } else if (e.key === 'F8' || (e.ctrlKey && e.key === 'p')) {
        e.preventDefault();
        this.submitCheckout();
      } else if (e.key === 'Escape') {
        this.clearSearch();
      }
    },
    initVatFromSettings() {
      const defaultVat = floatval(this.$root.site?.default_vat || 0);
      this.vat_percent = defaultVat;
      if (defaultVat > 0) {
        this.is_vat_applicable = true;
        this.calculateVatAmount();
      } else {
        this.is_vat_applicable = false;
        this.vat = 0;
      }
    },
    onVatSwitchToggle() {
      if (this.is_vat_applicable) {
        const defaultVat = floatval(this.$root.site?.default_vat || 0);
        if (floatval(this.vat_percent) <= 0 && defaultVat > 0) {
          this.vat_percent = defaultVat;
        } else if (floatval(this.vat_percent) <= 0) {
          this.vat_percent = 5;
        }
        this.calculateVatAmount();
      } else {
        this.vat = 0;
      }
    },
    calculateVatAmount() {
      if (!this.is_vat_applicable || floatval(this.vat_percent) <= 0) {
        this.vat = 0;
        return;
      }
      const taxableBase = Math.max(0, this.cartSubtotal - this.totalDiscount);
      const calculated = (taxableBase * floatval(this.vat_percent)) / 100;
      this.vat = Number(calculated.toFixed(2));
    },
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeydown);
    this.initVatFromSettings();
    this.loadInvoiceTerms();
    this.loadActiveDiscounts();
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

.active-dup-item {
  background-color: rgb(17, 44, 70) !important;
  border-color: rgb(17, 44, 70) !important;
  color: #ffffff !important;
}

.active-dup-item h6 {
  color: #ffffff !important;
}
</style>
