<template>
  <create-form @onSubmit="submit">
    <div class="grn-create-wrapper">
      <!-- 🌟 Mode Selector Tabs -->
      <div class="card border-0 shadow-sm mb-4 tab-header-card">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
              <div class="header-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-boxes fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0 text-dark">Goods Receive Note (GRN)</h5>
                <span class="small text-muted">Select mode to receive stock and process inventory</span>
              </div>
            </div>

            <!-- Tab Switcher Pills -->
            <div class="grn-tabs-nav d-flex p-1 bg-light rounded-pill border shadow-sm">
              <button
                type="button"
                class="tab-pill-btn btn btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2"
                :class="{ active: activeTab === 'po' }"
                @click="switchTab('po')"
              >
                <i class="fas fa-file-invoice-dollar"></i>
                <span>1. PO Based</span>
                <span class="badge bg-white text-dark rounded-pill shadow-xs small ms-1" v-if="activeTab === 'po'">Active</span>
              </button>

              <button
                type="button"
                class="tab-pill-btn btn btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2"
                :class="{ active: activeTab === 'supplier' }"
                @click="switchTab('supplier')"
              >
                <i class="fas fa-truck-loading"></i>
                <span>2. Supplier Based</span>
                <span class="badge bg-white text-dark rounded-pill shadow-xs small ms-1" v-if="activeTab === 'supplier'">Active</span>
              </button>

              <button
                type="button"
                class="tab-pill-btn btn btn-sm rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2"
                :class="{ active: activeTab === 'direct' }"
                @click="switchTab('direct')"
              >
                <i class="fas fa-bolt text-warning"></i>
                <span>3. Direct Purchase</span>
                <span class="badge bg-warning text-dark rounded-pill shadow-xs small ms-1" v-if="activeTab === 'direct'">Instant Paid</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 🌟 Mode Banner Notice -->
      <div class="alert alert-dismissible fade show border-0 shadow-sm mb-4 mode-alert" :class="getModeAlertClass">
        <div class="d-flex align-items-center gap-3">
          <div class="mode-alert-icon rounded-circle d-flex align-items-center justify-content-center">
            <i :class="getModeIcon"></i>
          </div>
          <div class="flex-grow-1">
            <h6 class="fw-bold mb-1">{{ getModeTitle }}</h6>
            <p class="mb-0 small opacity-85">{{ getModeDescription }}</p>
          </div>
        </div>
      </div>

      <!-- 🌟 General Information Card -->
      <div class="card border-0 shadow-sm mb-4 form-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Receiving Details (গ্রহণ তথ্যাবলী)</h6>
              <span class="small text-muted">Basic requisition, warehouse and challan information</span>
            </div>
          </div>
          <div>
            <span class="badge" :class="data.status ? 'bg-success' : 'bg-secondary'">
              {{ data.status ? 'Active' : 'Deactive' }}
            </span>
          </div>
        </div>

        <div class="card-body p-4 overflow-visible">
          <div class="row g-3">
            <!-- GRN Date -->
            <div class="col-md-3">
              <date-picker
                id="date_grn"
                v-model="data.grn_date"
                field="data.grn_date"
                title="GRN Date (গ্রহণের তারিখ)"
                placeholder="GRN Date"
                col="12"
                :req="true"
              ></date-picker>
            </div>

            <!-- TAB 1: PO Based Dropdown (Rich Professional Selector) -->
            <div class="col-md-5" v-if="activeTab === 'po'">
              <label class="form-label small fw-bold text-dark mb-1">
                Purchase Order (ক্রয় আদেশ) <span class="text-danger">*</span>
              </label>
              <div class="po-vselect-wrapper">
                <v-select
                  v-model="data.purchase_id"
                  label="invoiceno"
                  :reduce="(obj) => obj.id"
                  :options="pendingPurchases"
                  placeholder="-- Search by PO No, Supplier, Date or Amount --"
                  :closeOnSelect="true"
                  :appendToBody="true"
                  :filterBy="filterPoOptions"
                  @update:modelValue="onPurchaseChange"
                  :disabled="$route.params.id ? true : false"
                  class="po-vselect"
                >
                  <!-- Selected item inside input -->
                  <template #selected-option="option">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                      <span class="badge bg-primary text-white font-monospace px-2 py-1">
                        <i class="fas fa-file-invoice me-1"></i>{{ option.invoiceno }}
                      </span>
                      <span class="small fw-bold text-dark text-truncate" v-if="option.supplier?.org_name">
                        {{ option.supplier.org_name }}
                      </span>
                      <span class="badge bg-light text-muted border small" v-if="option.purchase_date">
                        <i class="far fa-calendar-alt me-1"></i>{{ formatDate(option.purchase_date) }}
                      </span>
                      <span class="badge bg-success bg-opacity-10 text-success font-monospace fw-bold" v-if="option.total_amount">
                        {{ formatCurrency(option.total_amount) }}
                      </span>
                    </div>
                  </template>

                  <!-- Options list in dropdown -->
                  <template #option="option">
                    <div class="po-option-card py-2">
                      <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                          <span class="badge bg-primary text-white font-monospace px-2 py-1">
                            <i class="fas fa-file-invoice me-1"></i>{{ option.invoiceno }}
                          </span>
                          <span class="fw-bold text-dark fs-6">
                            {{ option.supplier?.org_name || 'No Supplier' }}
                          </span>
                        </div>
                        <span
                          class="badge rounded-pill px-2 py-1 font-monospace"
                          :class="option.receive_status === 'Partial' ? 'bg-warning text-dark' : 'bg-info text-white'"
                        >
                          {{ option.receive_status || 'Pending' }}
                        </span>
                      </div>
                      <div class="d-flex align-items-center justify-content-between text-muted small mt-1 pt-1 border-top border-light">
                        <div class="d-flex align-items-center gap-3">
                          <span>
                            <i class="far fa-calendar-alt me-1 text-primary"></i>
                            PO Date: <strong class="text-secondary">{{ formatDate(option.purchase_date) }}</strong>
                          </span>
                        </div>
                        <div class="text-end">
                          <span>Total PO Value: </span>
                          <strong class="text-success font-monospace fs-6">
                            {{ formatCurrency(option.total_amount) }}
                          </strong>
                        </div>
                      </div>
                    </div>
                  </template>
                </v-select>
              </div>
            </div>

            <!-- TAB 2: Manual Supplier Dropdown -->
            <div class="col-md-4" v-if="activeTab === 'supplier'">
              <Select
                title="Supplier (সরবরাহকারী)"
                v-model="data.supplier_id"
                field="data.supplier_id"
                label="org_name"
                :reduce="(obj) => obj.id"
                :options="$root.global.suppliers"
                col="12"
                placeholder="-- Select Supplier --"
                :closeOnSelect="true"
                :appendToBody="true"
                :required="true"
              />
            </div>

            <!-- Destination Warehouse (All Tabs) -->
            <div :class="activeTab === 'po' ? 'col-md-4' : (activeTab === 'supplier' ? 'col-md-5' : 'col-md-5')">
              <Select
                title="Destination Warehouse (গন্তব্য ওয়্যারহাউস)"
                v-model="data.warehouse_id"
                field="data.warehouse_id"
                label="name"
                :reduce="(obj) => obj.id"
                :options="$root.global.warehouses"
                col="12"
                placeholder="-- Select Warehouse --"
                :closeOnSelect="true"
                :appendToBody="true"
                :required="true"
              />
            </div>

            <!-- Selected PO Information Banner (Tab 1) -->
            <div class="col-12" v-if="activeTab === 'po' && selectedPurchase">
              <div class="p-3 rounded-3 border bg-light bg-opacity-75 d-flex align-items-center justify-content-between flex-wrap gap-3 shadow-xs">
                <div class="d-flex align-items-center gap-3">
                  <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fas fa-file-invoice-dollar fs-5"></i>
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <h6 class="fw-bold mb-0 text-dark font-monospace">{{ selectedPurchase.invoiceno }}</h6>
                      <span
                        class="badge rounded-pill px-2 py-1 font-monospace"
                        :class="selectedPurchase.receive_status === 'Partial' ? 'bg-warning text-dark' : 'bg-info text-white'"
                      >
                        {{ selectedPurchase.receive_status || 'Pending' }}
                      </span>
                    </div>
                    <div class="small text-muted mt-1">
                      <i class="fas fa-building me-1 text-secondary"></i>
                      Supplier: <strong class="text-dark">{{ selectedSupplierName || selectedPurchase.supplier?.org_name || 'N/A' }}</strong>
                      <span v-if="selectedPurchase.supplier?.mobile" class="ms-2 text-secondary">
                        <i class="fas fa-phone-alt me-1"></i>{{ selectedPurchase.supplier.mobile }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                  <div class="text-center px-3 py-1 bg-white rounded border">
                    <span class="small text-muted d-block" style="font-size: 11px;">PO Order Date</span>
                    <strong class="text-dark font-monospace">{{ formatDate(selectedPurchase.purchase_date) }}</strong>
                  </div>
                  <div class="text-center px-3 py-1 bg-white rounded border">
                    <span class="small text-muted d-block" style="font-size: 11px;">Total PO Amount</span>
                    <strong class="text-success font-monospace fs-6">{{ formatCurrency(selectedPurchase.total_amount) }}</strong>
                  </div>
                  <div class="text-center px-3 py-1 bg-white rounded border">
                    <span class="small text-muted d-block" style="font-size: 11px;">PO Line Items</span>
                    <strong class="text-primary font-monospace">{{ data.grn_details ? data.grn_details.length : 0 }} Products</strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- Challan No -->
            <div :class="activeTab === 'direct' ? 'col-md-4' : 'col-md-3'">
              <Input
                v-model="data.challan_no"
                col="12"
                field="data.challan_no"
                :title="activeTab === 'direct' ? 'Receipt / Memo No' : 'Supplier Challan No'"
                placeholder="e.g. CH-90812"
                :req="false"
              />
            </div>

            <!-- Challan Date -->
            <div :class="activeTab === 'direct' ? 'col-md-4' : 'col-md-3'">
              <date-picker
                id="date_challan"
                v-model="data.challan_date"
                field="data.challan_date"
                title="Challan / Memo Date"
                placeholder="Challan Date"
                col="12"
                :req="false"
              ></date-picker>
            </div>

            <!-- Received By -->
            <div :class="activeTab === 'direct' ? 'col-md-4' : 'col-md-3'">
              <Input
                v-model="data.received_by"
                col="12"
                field="data.received_by"
                title="Received By (গ্রহীতার নাম)"
                placeholder="Staff / Store Keeper"
                :req="false"
              />
            </div>

            <!-- Remarks / Note -->
            <div class="col-md-10">
              <Input
                v-model="data.note"
                col="12"
                field="data.note"
                title="Remarks / Note (মন্তব্য)"
                placeholder="Any special remarks or delivery notes"
                :req="false"
              />
            </div>

            <!-- Status Switch -->
            <div class="col-md-2 d-flex flex-column justify-content-center">
              <label class="form-label small fw-semibold mb-2">GRN Status</label>
              <Switch
                v-model="data.status"
                field="data.status"
                title=""
                on-label="Active"
                off-label="Deactive"
                :req="false"
                col="12"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- 📦 SECTION 1: PO Based Table (Tab 1) -->
      <div v-if="activeTab === 'po'" class="card border-0 shadow-sm mb-4 form-card items-table-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-boxes"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">PO Received Items (ক্রয় আদেশের পণ্য তালিকা)</h6>
              <span class="small text-muted">Receive remaining quantities from selected purchase order</span>
            </div>
          </div>
          <span class="badge theme-bg text-white rounded-pill px-3 py-1 font-monospace" v-if="data.grn_details?.length">
            {{ data.grn_details.length }} Item(s)
          </span>
        </div>

        <div class="card-body p-0 overflow-visible">
          <div class="table-responsive table-scrollable">
            <table class="table custom-items-table table-hover align-middle mb-0">
              <thead class="theme-table-header text-center">
                <tr>
                  <th style="min-width: 220px;">Item / Product</th>
                  <th style="min-width: 100px;">Color</th>
                  <th style="min-width: 100px;" v-if="!isElectronicsShop">Size</th>
                  <th style="min-width: 80px;">Unit</th>
                  <th style="min-width: 90px;">Ordered</th>
                  <th style="min-width: 90px;">Prev. Recv</th>
                  <th style="min-width: 90px;">Remaining</th>
                  <th style="min-width: 110px;">Receive Qty</th>
                  <th style="min-width: 110px;" v-if="isElectronicsShop">Serials</th>
                  <th style="min-width: 110px;">Rate (৳)</th>
                  <th style="min-width: 120px;">Total (৳)</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="data.grn_details && data.grn_details.length > 0">
                  <tr v-for="(pitem, index) in data.grn_details" :key="index" :class="{ 'table-light opacity-75': pitem.remaining_qty <= 0 }">
                    <td>
                      <strong class="text-dark">{{ pitem.item ? pitem.item.title : (pitem.item_id ? 'Item #' + pitem.item_id : 'N/A') }}</strong>
                      <div class="small text-muted" v-if="pitem.category">{{ pitem.category.title }}</div>
                    </td>
                    <td class="text-center font-monospace">{{ pitem.color ? pitem.color.title : '-' }}</td>
                    <td class="text-center font-monospace" v-if="!isElectronicsShop">{{ pitem.size ? pitem.size.title : '-' }}</td>
                    <td class="text-center">{{ pitem.unit ? pitem.unit.title : '-' }}</td>
                    <td class="text-center font-monospace text-muted">{{ pitem.ordered_qty }}</td>
                    <td class="text-center font-monospace text-secondary">{{ pitem.previously_received_qty }}</td>
                    <td class="text-center font-monospace fw-bold" :class="pitem.remaining_qty > 0 ? 'text-primary' : 'text-success'">
                      {{ pitem.remaining_qty }}
                    </td>
                    <td>
                      <input
                        type="number"
                        step="any"
                        min="0"
                        :max="pitem.remaining_qty"
                        class="form-control form-control-sm text-end fw-bold font-monospace table-compact-input"
                        :class="{ 'border-danger text-danger': pitem.received_qty > pitem.remaining_qty }"
                        v-model.number="pitem.received_qty"
                        @input="onPoQtyChange(pitem)"
                      />
                      <div v-if="pitem.received_qty > pitem.remaining_qty" class="text-danger small mt-1">
                        Exceeds remaining!
                      </div>
                    </td>
                    <td class="text-center" v-if="isElectronicsShop">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary position-relative px-2 py-1 serial-btn w-100"
                        :class="{ 'active-serial': getSerialCount(pitem.serial_no) > 0 }"
                        @click="openSerialModal(index, pitem)"
                        title="Scan / Add Serial Numbers"
                      >
                        <i class="fas fa-barcode me-1"></i> Serials
                        <span class="badge bg-danger ms-1" v-if="getSerialCount(pitem.serial_no) > 0">{{ getSerialCount(pitem.serial_no) }}</span>
                      </button>
                    </td>
                    <td class="text-end font-monospace">{{ formatCurrency(pitem.unit_price) }}</td>
                    <td class="text-end font-monospace fw-bold text-success text-nowrap">{{ formatCurrency(pitem.total_amount) }}</td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                      <i class="fas fa-cart-arrow-down fa-3x mb-2 d-block opacity-25"></i>
                      <h6 class="fw-bold text-secondary">No Purchase Order Selected</h6>
                      <p class="small text-muted mb-0">Please select a pending Purchase Order from the dropdown above to load items.</p>
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="table-light" v-if="data.grn_details && data.grn_details.length > 0">
                <tr class="fw-bold align-middle">
                  <td :colspan="isElectronicsShop ? 6 : 7" class="text-end text-dark pe-3">Summary Totals:</td>
                  <td class="text-center font-monospace text-primary fs-6">{{ data.total_qty }}</td>
                  <td v-if="isElectronicsShop" class="text-center text-muted small">-</td>
                  <td class="text-end text-dark">Total:</td>
                  <td class="text-end font-monospace text-success fs-6 text-nowrap">{{ formatCurrency(data.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- 📦 SECTION 2 & 3: Product Summary Table (Supplier Based & Direct Purchase) -->
      <div v-if="activeTab === 'supplier' || activeTab === 'direct'" class="card border-0 shadow-sm mb-4 form-card items-table-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-boxes"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">
                {{ activeTab === 'supplier' ? 'Supplier Received Products (পণ্য তালিকা)' : 'Direct Purchase Products (ক্রয়কৃত পণ্য তালিকা)' }}
              </h6>
              <span class="small text-muted">Manage products, variants, unit pricing, quantities and serial tracking</span>
            </div>
            <span class="badge theme-bg text-white rounded-pill px-2 py-1 ms-2 font-monospace">
              {{ data.grn_details ? data.grn_details.length : 0 }} Items
            </span>
          </div>

          <!-- Add Product Button (Opens Popup Modal) -->
          <button
            type="button"
            class="btn btn-sm btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm"
            @click.prevent="openAddProductModal"
          >
            <i class="fas fa-plus-circle"></i> Add Product Row (পণ্য যোগ করুন)
          </button>
        </div>

        <div class="card-body p-0 overflow-visible">
          <div class="table-responsive table-scrollable">
            <table class="table custom-items-table table-hover align-middle mb-0">
              <thead class="theme-table-header text-center">
                <tr>
                  <th style="width: 4%;">#</th>
                  <th style="width: 28%;" class="text-start ps-3">Product / Item (পণ্য ও বিবরণ)</th>
                  <th style="width: 14%;">Variant (ভেরিয়েন্ট)</th>
                  <th style="width: 8%;">Unit (একক)</th>
                  <th style="width: 12%;" class="text-end">Cost Price (ক্রয়)</th>
                  <th style="width: 12%;" class="text-end">Selling Price (বিক্রয়)</th>
                  <th style="width: 8%;">Qty (পরিমাণ)</th>
                  <th style="width: 10%;" v-if="isElectronicsShop">Serials</th>
                  <th style="width: 12%;" class="text-end pe-3">Total Amount</th>
                  <th style="width: 8%;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(pitem, index) in data.grn_details" :key="index" class="item-row">
                  <!-- Row Index -->
                  <td class="text-center fw-bold text-muted font-monospace">{{ index + 1 }}</td>

                  <!-- Product / Item info -->
                  <td class="text-start ps-3">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-light text-dark border px-2 py-0.5 small">
                          {{ getCategoryTitle(pitem.category_id, pitem) }}
                        </span>
                        <span class="fw-bold text-dark fs-6">{{ getItemTitle(pitem) }}</span>
                      </div>
                      <small class="text-muted font-monospace" v-if="getItemBarcode(pitem)">
                        <i class="fas fa-barcode me-1"></i>Barcode: {{ getItemBarcode(pitem) }}
                      </small>
                    </div>
                  </td>

                  <!-- Variant (Color / Size) -->
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                      <span class="badge bg-secondary" v-if="getColorTitle(pitem.color_id, pitem)">
                        <i class="fas fa-palette me-1"></i>{{ getColorTitle(pitem.color_id, pitem) }}
                      </span>
                      <span class="badge bg-info text-dark" v-if="!isElectronicsShop && getSizeTitle(pitem.size_id, pitem)">
                        <i class="fas fa-ruler me-1"></i>{{ getSizeTitle(pitem.size_id, pitem) }}
                      </span>
                      <span class="text-muted small" v-if="!getColorTitle(pitem.color_id, pitem) && (isElectronicsShop || !getSizeTitle(pitem.size_id, pitem))">
                        Standard
                      </span>
                    </div>
                  </td>

                  <!-- Unit (Auto Detected) -->
                  <td class="text-center">
                    <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                      {{ getUnitTitle(pitem.unit_id, pitem) }}
                    </span>
                  </td>

                  <!-- Cost Price -->
                  <td class="text-end font-monospace text-muted fw-semibold">
                    ৳ {{ formatNum(pitem.unit_price) }}
                  </td>

                  <!-- Selling Price -->
                  <td class="text-end font-monospace text-success fw-semibold">
                    ৳ {{ formatNum(pitem.selling_price) }}
                  </td>

                  <!-- Quantity -->
                  <td class="text-center font-monospace fw-bold fs-6 text-dark">
                    {{ pitem.received_qty }}
                  </td>

                  <!-- Serials (Electronics Shop) -->
                  <td class="text-center" v-if="isElectronicsShop">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary position-relative px-2 py-1 serial-btn"
                      :class="{ 'active-serial': getSerialCount(pitem.serial_no) > 0 }"
                      @click="openEditProductModal(index, pitem)"
                      title="View / Edit Serial Numbers"
                    >
                      <i class="fas fa-barcode me-1"></i> Serials
                      <span class="badge bg-danger ms-1" v-if="getSerialCount(pitem.serial_no) > 0">{{ getSerialCount(pitem.serial_no) }}</span>
                    </button>
                  </td>

                  <!-- Row Total Amount -->
                  <td class="text-end font-monospace fw-bold text-theme pe-3 fs-6">
                    {{ formatCurrency(pitem.total_amount) }}
                  </td>

                  <!-- Actions (Edit & Delete) -->
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary btn-action"
                        @click="openEditProductModal(index, pitem)"
                        title="Edit Product (সংশোধন করুন)"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger btn-action"
                        @click="removeDynamicItemRow(index)"
                        title="Remove Product (মুছে ফেলুন)"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="!data.grn_details || data.grn_details.length === 0">
                  <td :colspan="isElectronicsShop ? 10 : 9" class="text-center py-5 text-muted bg-white">
                    <div class="empty-state-wrapper py-3">
                      <div class="empty-icon theme-bg-soft text-theme rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-cart-plus fa-2x"></i>
                      </div>
                      <h6 class="fw-bold text-dark mb-1">No products added yet</h6>
                      <p class="text-muted small mb-3">Click "+ Add Product Row" button to configure product specifications, pricing, quantities and serials.</p>
                      <button
                        type="button"
                        class="btn btn-primary btn-sm px-4 fw-bold shadow-sm"
                        @click="openAddProductModal"
                      >
                        <i class="fas fa-plus-circle me-1"></i> Add First Product (পণ্য যোগ করুন)
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="table-light" v-if="data.grn_details && data.grn_details.length > 0">
                <tr class="fw-bold align-middle">
                  <td :colspan="6" class="text-end text-dark pe-3">Summary Totals:</td>
                  <td class="text-center font-monospace text-primary fs-6">{{ data.total_qty }}</td>
                  <td v-if="isElectronicsShop" class="text-center text-muted small">-</td>
                  <td class="text-end font-monospace text-success fs-6 text-nowrap pe-3">{{ formatCurrency(data.sub_total || data.total_amount) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Bottom Add Row Bar -->
          <div class="p-3 bg-light border-top text-center" v-if="data.grn_details && data.grn_details.length > 0">
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm px-4 fw-bold dashed-btn"
              @click.prevent="openAddProductModal"
            >
              <i class="fas fa-plus me-1 text-primary"></i> Add Another Product Row (আরও পণ্য যোগ করুন)
            </button>
          </div>
        </div>
      </div>

      <!-- 💳 SECTION 3: Direct Purchase Payment & Fund Settlement (Tab 3 Only) -->
      <div v-if="activeTab === 'direct'" class="row g-3 mb-4">
        <!-- Fund Account Selection & Live Balance (7 cols) -->
        <div class="col-xl-7 col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 form-card fund-settlement-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center">
                  <i class="fas fa-wallet"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">Fund Account & Settlement (তহবিল ও তাৎক্ষণিক পরিশোধ)</h6>
                  <span class="small text-muted">Select fund source to debit payment directly</span>
                </div>
              </div>
              <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
                <i class="fas fa-check-circle me-1"></i> Instant Cash Outflow
              </span>
            </div>

            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-7">
                  <label class="form-label small fw-bold text-dark">
                    Debit / Paid From Fund Account <span class="text-danger">*</span>
                  </label>
                  <v-select
                    v-model="data.fund_account_id"
                    label="name"
                    :reduce="(obj) => obj.id"
                    :options="fundaccounts"
                    placeholder="-- Select Fund Account --"
                    :closeOnSelect="true"
                    :appendToBody="true"
                    @update:modelValue="onFundAccountChange"
                  />
                  <small class="text-muted mt-1 d-block">
                    Select Cash in Hand, Bank, or Mobile Banking fund account.
                  </small>
                </div>

                <!-- Live Fund Balance Display -->
                <div class="col-md-5">
                  <div class="fund-balance-card p-3 rounded-3 border" :class="fundBalanceClass">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <span class="small fw-bold text-uppercase opacity-75">Available Balance</span>
                      <i class="fas fa-coins text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-0 font-monospace">{{ formatCurrency(fundBalance) }}</h4>
                    <div class="mt-1 small" v-if="fundBalanceWarning">
                      <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Balance low</span>
                    </div>
                    <div class="mt-1 small" v-else-if="data.fund_account_id">
                      <span class="badge bg-success bg-opacity-25 text-success fw-semibold"><i class="fas fa-check me-1"></i> Sufficient</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Automated Voucher notice -->
              <div class="mt-4 p-3 bg-light rounded border d-flex align-items-center gap-3">
                <div class="text-primary fs-4">
                  <i class="fas fa-receipt"></i>
                </div>
                <div class="small">
                  <strong class="text-dark d-block">Automated Accounting & Payment Integration:</strong>
                  <span class="text-muted">
                    Upon submission, this direct purchase will automatically create a <strong>Payment (Paid)</strong> record and generate a balanced <strong>Accounting Voucher</strong> debiting <em>Purchase Expense</em> and crediting the selected <em>Fund Account</em>.
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Breakdown & Discount (5 cols) -->
        <div class="col-xl-5 col-lg-6 col-12">
          <div class="card border-0 shadow-sm h-100 form-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-calculator"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-0 text-dark">Financial Summary (হিসাব বিবরণী)</h6>
                <span class="small text-muted">Direct purchase cost & instant payment calculation</span>
              </div>
            </div>

            <div class="card-body p-4">
              <!-- Sub Total -->
              <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-nowrap">
                <span class="text-muted fw-semibold">Items Sub Total (পণ্যের মোট মূল্য):</span>
                <span class="fw-bold font-monospace fs-6 text-dark">{{ formatCurrency(data.sub_total) }}</span>
              </div>

              <!-- Discount -->
              <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-nowrap">
                <div>
                  <span class="text-muted fw-semibold d-block">Discount (ছাড় / ডিসকাউন্ট):</span>
                  <small class="text-muted opacity-75">Cash discount received</small>
                </div>
                <div class="input-group input-group-sm" style="width: 150px;">
                  <span class="input-group-text bg-light text-muted px-2">৳</span>
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace fw-bold"
                    placeholder="0.00"
                    v-model.number="data.discount"
                    @input="calculateDynamicTotals"
                  />
                </div>
              </div>

              <!-- Net Payable / Paid Total Box (Single Line Total) -->
              <div class="grand-total-box p-3 rounded-3 mt-3 d-flex justify-content-between align-items-center text-nowrap">
                <div class="d-flex align-items-center gap-2">
                  <i class="fas fa-coins text-warning fs-5"></i>
                  <span class="text-white fs-6 fw-bold">Total (সর্বমোট প্রদেয়):</span>
                </div>
                <div class="text-end">
                  <h4 class="fw-bold mb-0 text-white font-monospace">{{ formatCurrency(data.total_amount) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📊 Summary KPI Row for Modes 1 & 2 (Single Line per card) -->
      <div v-if="activeTab !== 'direct'" class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card stat-card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center justify-content-between text-nowrap">
              <div class="d-flex align-items-center gap-2">
                <div class="stat-icon-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                  <i class="fas fa-layer-group"></i>
                </div>
                <span class="fw-bold text-muted small text-uppercase">Total Items:</span>
              </div>
              <h5 class="fw-bold mb-0 text-dark font-monospace">{{ data.grn_details ? data.grn_details.length : 0 }} Items</h5>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stat-card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center justify-content-between text-nowrap">
              <div class="d-flex align-items-center gap-2">
                <div class="stat-icon-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                  <i class="fas fa-dolly"></i>
                </div>
                <span class="fw-bold text-muted small text-uppercase">Total Received Qty:</span>
              </div>
              <h5 class="fw-bold mb-0 text-primary font-monospace">{{ data.total_qty }} Units</h5>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stat-card border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center justify-content-between text-nowrap">
              <div class="d-flex align-items-center gap-2">
                <div class="stat-icon-sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                  <i class="fas fa-coins"></i>
                </div>
                <span class="fw-bold text-muted small text-uppercase">Total:</span>
              </div>
              <h5 class="fw-bold mb-0 text-success font-monospace">{{ formatCurrency(data.total_amount) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🌟 Custom Footer Actions inside CreateForm -->
    <template #form_footer>
      <div class="col-12 mt-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 bg-white border rounded shadow-sm">
          <router-link :to="{ name: model + '.index' }" class="btn btn-outline-secondary px-4 fw-semibold">
            <i class="fas fa-arrow-left me-1"></i> Back to GRN List
          </router-link>

          <div class="d-flex align-items-center gap-2">
            <button
              type="submit"
              class="theme_btn px-5 py-2 fw-bold d-flex align-items-center gap-2 shadow"
              :disabled="$root.submit"
            >
              <template v-if="$root.submit">
                <i class="fa fa-spinner fa-spin"></i> Processing...
              </template>
              <template v-else>
                <i class="fas fa-check-circle"></i>
                <span>{{ getSubmitButtonText }}</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- 🏷️ Professional Product Addition & Serial Entry Popup Modal (Supplier & Direct Mode) -->
    <div
      v-if="showProductModal"
      class="modal fade show d-block product-modal-backdrop"
      tabindex="-1"
      style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(3px); z-index: 1055;"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl border-0 rounded-3 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header theme-bg text-white py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-white bg-opacity-20 rounded p-2 text-white">
                <i class="fas" :class="isEditingModal ? 'fa-edit fs-5' : 'fa-cart-plus fs-5'"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold fs-6 mb-0 text-white">
                  {{ isEditingModal ? 'Edit Received Product (পণ্য সংশোধন)' : 'Add Received Product (পণ্য যোগ করুন)' }}
                </h5>
                <span class="small text-white-50">Select product, set costs, variants, quantities and serial tracking</span>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="closeProductModal"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 bg-white" style="max-height: 80vh; overflow-y: auto;">
            <div class="row g-3">
              <!-- Category Selection with appendToBody -->
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark mb-1">
                  Category (ক্যাটাগরি) <span class="text-danger">*</span>
                </label>
                <v-select
                  v-model="modalForm.category_id"
                  label="title"
                  :reduce="(obj) => obj.id"
                  :options="categories"
                  placeholder="-- Select Category --"
                  :closeOnSelect="true"
                  :appendToBody="true"
                  @update:modelValue="onModalCategoryChange"
                  class="modal-vselect"
                />
              </div>

              <!-- Product / Item Selection with appendToBody -->
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark mb-1">
                  Product / Item (পণ্য) <span class="text-danger">*</span>
                </label>
                <v-select
                  v-model="modalForm.item_id"
                  label="title"
                  :reduce="(obj) => obj.id"
                  :options="modalForm.items"
                  placeholder="-- Select Product --"
                  :closeOnSelect="true"
                  :appendToBody="true"
                  :disabled="!modalForm.category_id"
                  @option:selected="onModalItemChange"
                  @update:modelValue="onModalItemChange"
                  class="modal-vselect"
                />
              </div>

              <!-- Color Variant -->
              <div :class="isElectronicsShop ? 'col-md-6' : 'col-md-4'">
                <label class="form-label small fw-bold text-dark mb-1">
                  Color (রং)
                </label>
                <select class="form-select form-select-sm" v-model="modalForm.color_id">
                  <option :value="null">-- Standard / None --</option>
                  <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <!-- Size Variant (Hidden for Electronics) -->
              <div class="col-md-4" v-if="!isElectronicsShop">
                <label class="form-label small fw-bold text-dark mb-1">
                  Size (সাইজ)
                </label>
                <select class="form-select form-select-sm" v-model="modalForm.size_id">
                  <option :value="null">-- Standard / None --</option>
                  <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>

              <!-- Auto-detected Unit Display Badge -->
              <div :class="isElectronicsShop ? 'col-md-6' : 'col-md-4'">
                <label class="form-label small fw-bold text-dark mb-1">
                  Unit (একক - অটোমেটিক)
                </label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light text-muted"><i class="fas fa-balance-scale"></i></span>
                  <input
                    type="text"
                    class="form-control form-control-sm bg-light fw-bold text-primary font-monospace"
                    :value="modalForm.unit_title || 'Pcs (Default)'"
                    readonly
                  />
                </div>
              </div>

              <!-- Purchase Price (ক্রয় মূল্য) -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">
                  Purchase Cost (ক্রয় মূল্য ৳) <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light text-muted">৳</span>
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace fw-bold"
                    placeholder="0.00"
                    v-model.number="modalForm.unit_price"
                    @input="onModalPriceOrQtyChange"
                  />
                </div>
              </div>

              <!-- Selling Price (বিক্রয় মূল্য) -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">
                  Selling Price (বিক্রয় মূল্য ৳)
                </label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light text-muted">৳</span>
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace text-success fw-bold"
                    placeholder="0.00"
                    v-model.number="modalForm.selling_price"
                  />
                </div>
              </div>

              <!-- Quantity (পরিমাণ) -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">
                  Received Qty (পরিমাণ) <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                  <input
                    type="number"
                    step="any"
                    min="1"
                    class="form-control form-control-sm text-center font-monospace fw-bold fs-6"
                    placeholder="1"
                    v-model.number="modalForm.received_qty"
                    @input="onModalPriceOrQtyChange"
                  />
                  <span class="input-group-text bg-light text-muted">{{ modalForm.unit_title || 'Pcs' }}</span>
                </div>
              </div>

              <!-- Line Total Amount Preview Banner -->
              <div class="col-12">
                <div class="p-2 px-3 rounded bg-light border d-flex justify-content-between align-items-center">
                  <span class="small fw-bold text-muted text-uppercase">Line Total Calculation (মোট):</span>
                  <span class="fw-bold font-monospace fs-6 text-theme">
                    {{ modalForm.received_qty || 0 }} {{ modalForm.unit_title || 'Pcs' }} × ৳ {{ formatNum(modalForm.unit_price || 0) }} = 
                    <span class="text-primary">{{ formatCurrency(modalForm.total_amount) }}</span>
                  </span>
                </div>
              </div>

              <!-- 🏷️ Serial Numbers / IMEI Tracking (Collapsible Accordion for Electronics Shop) -->
              <div class="col-12" v-if="isElectronicsShop">
                <div class="border rounded-3 overflow-hidden shadow-xs">
                  <!-- Accordion Header (Click to toggle) -->
                  <div
                    class="p-3 bg-light d-flex align-items-center justify-content-between cursor-pointer border-bottom-0"
                    :class="{ 'border-bottom': modalForm.showSerialsSection }"
                    @click="modalForm.showSerialsSection = !modalForm.showSerialsSection"
                    style="user-select: none;"
                  >
                    <div class="d-flex align-items-center gap-2">
                      <div class="text-primary fs-6">
                        <i class="fas fa-barcode"></i>
                      </div>
                      <div>
                        <strong class="text-dark small d-block">Serial Numbers / IMEI Tracking (সিরিয়াল নম্বর সমূহ)</strong>
                        <span class="text-muted" style="font-size: 11px;">
                          {{ modalForm.showSerialsSection ? 'Click to collapse serial entry section' : 'Click to expand and scan/add device serial numbers' }}
                        </span>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge rounded-pill font-monospace" :class="modalForm.serialsList.length > 0 ? 'bg-primary text-white' : 'bg-secondary bg-opacity-25 text-dark'">
                        {{ modalForm.serialsList.length }} Serials
                      </span>
                      <i class="fas fa-chevron-down text-muted transition-transform" :class="{ 'fa-rotate-180': modalForm.showSerialsSection }"></i>
                    </div>
                  </div>

                  <!-- Accordion Body -->
                  <div v-show="modalForm.showSerialsSection" class="p-3 bg-white">
                    <!-- Mode Switch & Action Bar -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                      <div class="btn-group btn-group-sm" role="group">
                        <button
                          type="button"
                          class="btn"
                          :class="!modalForm.bulkMode ? 'btn-primary' : 'btn-outline-secondary'"
                          @click="modalForm.bulkMode = false"
                        >
                          <i class="fas fa-barcode me-1"></i> Quick / Scan Mode
                        </button>
                        <button
                          type="button"
                          class="btn"
                          :class="modalForm.bulkMode ? 'btn-primary' : 'btn-outline-secondary'"
                          @click="modalForm.bulkMode = true"
                        >
                          <i class="fas fa-paste me-1"></i> Bulk Paste Mode
                        </button>
                      </div>

                      <div class="d-flex align-items-center gap-2">
                        <button
                          type="button"
                          class="btn btn-xs btn-outline-secondary"
                          @click="copyAllModalSerials"
                          :disabled="modalForm.serialsList.length === 0"
                          title="Copy all serials to clipboard"
                        >
                          <i class="fas fa-copy me-1"></i> Copy All
                        </button>
                        <button
                          type="button"
                          class="btn btn-xs btn-outline-danger"
                          @click="clearAllModalSerials"
                          :disabled="modalForm.serialsList.length === 0"
                          title="Clear serials list"
                        >
                          <i class="fas fa-trash-alt me-1"></i> Clear
                        </button>
                      </div>
                    </div>

                    <!-- 1. Single Scan Input -->
                    <div v-if="!modalForm.bulkMode" class="mb-3">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                        <input
                          ref="modalSerialInput"
                          type="text"
                          class="form-control font-monospace fw-bold"
                          placeholder="Type or scan serial/IMEI and press Enter..."
                          v-model="modalForm.tempSerial"
                          @keyup.enter.prevent="addModalSerial"
                        />
                        <button type="button" class="btn btn-primary fw-bold px-3" @click.prevent="addModalSerial">
                          <i class="fas fa-plus me-1"></i> Add
                        </button>
                      </div>
                      <small class="text-muted mt-1 d-block" style="font-size: 11px;">
                        <i class="fas fa-info-circle me-1 text-primary"></i> Press <strong>Enter</strong> to instantly add serials one by one.
                      </small>
                    </div>

                    <!-- 2. Bulk Paste Textarea -->
                    <div v-else class="mb-3">
                      <textarea
                        class="form-control form-control-sm font-monospace"
                        rows="3"
                        v-model="modalForm.bulkSerialText"
                        placeholder="Paste multiple serials separated by line break, comma, or space (e.g. SN001&#10;SN002&#10;SN003)..."
                      ></textarea>
                      <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-xs btn-primary fw-bold px-3" @click.prevent="processModalBulkSerials">
                          <i class="fas fa-plus-circle me-1"></i> Add Extracted Serials
                        </button>
                      </div>
                    </div>

                    <!-- Serials Chips List Box -->
                    <div class="serials-list-box p-2 border rounded bg-light mb-2" style="max-height: 140px; overflow-y: auto;">
                      <div v-if="modalForm.serialsList.length > 0" class="d-flex flex-wrap gap-2">
                        <span
                          v-for="(sn, sIdx) in modalForm.serialsList"
                          :key="sIdx"
                          class="badge serial-badge font-monospace p-2 d-flex align-items-center gap-2 shadow-xs"
                        >
                          <span class="badge-num">#{{ sIdx + 1 }}</span>
                          <span class="fw-bold">{{ sn }}</span>
                          <i class="fas fa-times delete-serial-btn ms-1" @click="removeModalSerial(sIdx)" title="Remove Serial"></i>
                        </span>
                      </div>
                      <div v-else class="text-center py-3 text-muted">
                        <i class="fas fa-barcode fa-lg mb-1 text-secondary opacity-50 d-block"></i>
                        <span class="small">No serial numbers entered yet.</span>
                      </div>
                    </div>

                    <!-- Sync Qty Checkbox -->
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                      <span class="small fw-bold text-dark">
                        Total Serials:
                        <span class="text-primary font-monospace fs-6">{{ modalForm.serialsList.length }}</span>
                      </span>
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modalSyncQtyCheck" v-model="modalForm.syncQtyWithSerials" />
                        <label class="form-check-label small fw-bold" for="modalSyncQtyCheck">Auto sync Received Qty to {{ modalForm.serialsList.length }}</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary btn-sm px-3" @click="closeProductModal">
              Cancel
            </button>
            <button type="button" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" @click="saveProductFromModal">
              <i class="fas fa-check me-1"></i> {{ isEditingModal ? 'Update Product' : 'Add to Received List' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 🏷️ Enhanced Multiple Serial Number Entry Modal (PO Based Tab) -->
    <div
      v-if="showSerialModal"
      class="modal fade show d-block serial-modal-backdrop"
      tabindex="-1"
      style="background: rgba(17, 44, 70, 0.65); backdrop-filter: blur(2px);"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header theme-bg text-white py-3 px-4">
            <div class="d-flex align-items-center gap-2">
              <div class="bg-white bg-opacity-20 rounded p-2 text-white">
                <i class="fas fa-barcode fs-5"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold fs-6 mb-0 text-white">Manage Serial Numbers (সিরিয়াল নম্বর সমূহ)</h5>
                <span class="small text-white-50">Add or scan unique device serial numbers</span>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="closeSerialModal"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 bg-white">
            <!-- Active Item Banner -->
            <div class="p-3 mb-3 rounded border bg-light d-flex justify-content-between align-items-center flex-wrap gap-2" v-if="activeRowItem">
              <div>
                <span class="small text-muted d-block">Selected Item:</span>
                <strong class="text-dark">{{ getSelectedProductTitle(activeRowItem) }}</strong>
              </div>
              <div class="d-flex align-items-center gap-2 font-monospace">
                <span class="badge bg-secondary">Received Qty: {{ activeRowItem.received_qty || 0 }}</span>
                <span class="badge theme-bg text-white">Serials: {{ modalSerials.length }}</span>
              </div>
            </div>

            <!-- Tabs: Single Entry vs Bulk Entry -->
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="btn-group btn-group-sm" role="group">
                <button
                  type="button"
                  class="btn"
                  :class="!bulkEntryMode ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="bulkEntryMode = false"
                >
                  <i class="fas fa-keyboard me-1"></i> Quick / Barcode Entry
                </button>
                <button
                  type="button"
                  class="btn"
                  :class="bulkEntryMode ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="bulkEntryMode = true"
                >
                  <i class="fas fa-paste me-1"></i> Bulk Paste List
                </button>
              </div>

              <div class="d-flex align-items-center gap-2">
                <button
                  type="button"
                  class="btn btn-xs btn-outline-secondary"
                  @click="copyAllModalSerialsStandalone"
                  :disabled="modalSerials.length === 0"
                  title="Copy all serials to clipboard"
                >
                  <i class="fas fa-copy me-1"></i> Copy All
                </button>
                <button
                  type="button"
                  class="btn btn-xs btn-outline-danger"
                  @click="clearAllModalSerialsStandalone"
                  :disabled="modalSerials.length === 0"
                  title="Clear list"
                >
                  <i class="fas fa-trash-alt me-1"></i> Clear
                </button>
              </div>
            </div>

            <!-- 1. Single Scan / Text Input -->
            <div v-if="!bulkEntryMode" class="mb-3">
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                <input
                  ref="serialInput"
                  type="text"
                  class="form-control font-monospace fw-bold"
                  placeholder="Type or scan serial number and press Enter..."
                  v-model="tempSerial"
                  @keyup.enter.prevent="addSerialFromInput"
                />
                <button type="button" class="btn btn-primary fw-bold px-4" @click.prevent="addSerialFromInput">
                  <i class="fas fa-plus me-1"></i> Add
                </button>
              </div>
              <small class="text-muted mt-1 d-block">
                <i class="fas fa-info-circle me-1 text-primary"></i> Press <strong>Enter</strong> to instantly add serial numbers one by one.
              </small>
            </div>

            <!-- 2. Bulk Paste Textarea -->
            <div v-else class="mb-3">
              <textarea
                class="form-control font-monospace"
                rows="4"
                v-model="bulkSerialText"
                placeholder="Paste multiple serial numbers separated by line break, comma, or space (e.g. SN001&#10;SN002&#10;SN003)..."
              ></textarea>
              <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-sm btn-primary fw-bold px-3" @click.prevent="processBulkSerials">
                  <i class="fas fa-plus-circle me-1"></i> Add Extracted Serials
                </button>
              </div>
            </div>

            <!-- Serial Badges List Box -->
            <div class="serials-list-box p-3 border rounded bg-light">
              <div v-if="modalSerials.length > 0" class="d-flex flex-wrap gap-2">
                <span
                  v-for="(sn, sIdx) in modalSerials"
                  :key="sIdx"
                  class="badge serial-badge font-monospace p-2 d-flex align-items-center gap-2 shadow-xs"
                >
                  <span class="badge-num">#{{ sIdx + 1 }}</span>
                  <span class="fw-bold">{{ sn }}</span>
                  <i class="fas fa-times delete-serial-btn ms-1" @click="removeSerial(sIdx)" title="Remove Serial"></i>
                </span>
              </div>
              <div v-else class="text-center py-4 text-muted">
                <i class="fas fa-barcode fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                <p class="mb-0 small">No serial numbers entered yet.</p>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
              <span class="fw-bold text-dark">
                Total Serials Entered:
                <span class="text-primary font-monospace fs-5">{{ modalSerials.length }}</span>
              </span>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="syncQtyCheck" v-model="syncQtyWithSerials" />
                <label class="form-check-label small fw-bold" for="syncQtyCheck">Auto sync Received Qty to {{ modalSerials.length }}</label>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary btn-sm px-3" @click="closeSerialModal">
              Cancel
            </button>
            <button type="button" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" @click="saveSerialsFromModal">
              <i class="fas fa-check me-1"></i> Save Serials
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = 'grn';

export default {
  computed: {
    isElectronicsShop() {
      const shopType = (this.$root.site?.shop_type || this.$root.site_setting?.shop_type || '').toLowerCase();
      return shopType === 'electronics';
    },

    activeRowItem() {
      if (this.activeRowIndex !== null && this.data.grn_details) {
        return this.data.grn_details[this.activeRowIndex];
      }
      return null;
    },

    getModeAlertClass() {
      if (this.activeTab === 'po') return 'alert-primary bg-primary bg-opacity-10 text-primary border-primary border-opacity-25';
      if (this.activeTab === 'supplier') return 'alert-info bg-info bg-opacity-10 text-dark border-info border-opacity-25';
      return 'alert-warning bg-warning bg-opacity-10 text-dark border-warning border-opacity-50';
    },

    getModeIcon() {
      if (this.activeTab === 'po') return 'fas fa-file-invoice-dollar fs-4 text-primary';
      if (this.activeTab === 'supplier') return 'fas fa-truck-loading fs-4 text-info';
      return 'fas fa-bolt fs-4 text-warning';
    },

    getModeTitle() {
      if (this.activeTab === 'po') return 'Mode 1: Purchase Order (PO) Based Receiving';
      if (this.activeTab === 'supplier') return 'Mode 2: Supplier Direct Receiving (Manual Selection)';
      return 'Mode 3: Direct Purchase (Instant Fund Payment & Accounts Voucher)';
    },

    getModeDescription() {
      if (this.activeTab === 'po') {
        return 'Select an approved Purchase Order from the dropdown. The supplier and pending PO line items will automatically populate.';
      }
      if (this.activeTab === 'supplier') {
        return 'Receive goods directly from a chosen supplier without a previous PO. Select supplier, choose category & products, and specify quantities.';
      }
      return 'Direct cash/bank purchase without PO or Supplier dropdowns. Inventory is stocked immediately, payments are debited from the chosen fund account, and accounting vouchers are generated automatically.';
    },

    getSubmitButtonText() {
      if (this.$route.params.id) return 'Update GRN Record';
      if (this.activeTab === 'direct') return 'Save & Complete Direct Purchase';
      return 'Save & Receive Goods Note';
    },

    fundBalanceClass() {
      if (!this.data.fund_account_id) return 'bg-light text-muted';
      if (this.fundBalanceWarning) return 'bg-danger bg-opacity-10 border-danger text-danger';
      return 'bg-success bg-opacity-10 border-success text-success';
    },

    fundBalanceWarning() {
      if (!this.data.fund_account_id) return false;
      return (this.data.total_amount > 0 && this.fundBalance < this.data.total_amount);
    }
  },

  data() {
    return {
      model: model,
      page_title: '',
      activeTab: 'po', // 'po', 'supplier', 'direct'
      pendingPurchases: [],
      selectedPurchase: null,
      selectedSupplierName: '',
      fundaccounts: [],
      fundBalance: 0,

      // Reference datasets
      categories: [],
      units: [],
      colors: [],
      sizes: [],

      data: {
        grn_type: 'po',
        grn_date: this.$filter.today(),
        purchase_id: null,
        supplier_id: null,
        warehouse_id: null,
        challan_no: '',
        challan_date: '',
        received_by: '',
        note: '',
        sub_total: 0,
        discount: 0,
        total_qty: 0,
        total_amount: 0,
        fund_account_id: null,
        status: 'active',
        grn_details: [],
      },

      // Product addition/editing popup modal state
      showProductModal: false,
      isEditingModal: false,
      modalForm: {
        rowIndex: null,
        category_id: null,
        item_id: null,
        item_title: '',
        items: [],
        color_id: null,
        size_id: null,
        unit_id: null,
        unit_title: '',
        unit_price: 0,
        selling_price: 0,
        received_qty: 1,
        total_amount: 0,
        serial_no: '',
        serialsList: [],
        tempSerial: '',
        bulkSerialText: '',
        bulkMode: false,
        showSerialsSection: false,
        syncQtyWithSerials: true,
      },

      // Standalone Serial modal state (PO mode)
      showSerialModal: false,
      activeRowIndex: null,
      tempSerial: '',
      bulkSerialText: '',
      bulkEntryMode: false,
      modalSerials: [],
      syncQtyWithSerials: true,
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    formatCurrency(amount) {
      const val = parseFloat(amount) || 0;
      return (
        '৳ ' +
        val.toLocaleString('en-BD', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })
      );
    },

    formatNum(n) {
      const val = parseFloat(n) || 0;
      return val.toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    },

    getCategoryTitle(catId, item = null) {
      if (item && item.category && item.category.title) return item.category.title;
      if (catId && this.categories.length) {
        const found = this.categories.find((c) => c.id === catId);
        if (found) return found.title;
      }
      return 'Category';
    },

    getItemTitle(item) {
      if (!item) return 'Product';
      if (item.item && item.item.title) return item.item.title;
      if (item.items && item.item_id) {
        const found = item.items.find((i) => i.id === item.item_id);
        if (found) return found.title;
      }
      if (this.modalForm?.items && item.item_id) {
        const found = this.modalForm.items.find((i) => i.id === item.item_id);
        if (found) return found.title;
      }
      return item.item_id ? `Product #${item.item_id}` : 'Product';
    },

    getItemBarcode(item) {
      if (!item) return '';
      if (item.item && item.item.barcode) return item.item.barcode;
      if (item.items && item.item_id) {
        const found = item.items.find((i) => i.id === item.item_id);
        if (found && found.barcode) return found.barcode;
      }
      return '';
    },

    getColorTitle(colorId, item = null) {
      if (item && item.color && item.color.title) return item.color.title;
      if (colorId && this.colors.length) {
        const found = this.colors.find((c) => c.id === colorId);
        if (found) return found.title;
      }
      return '';
    },

    getSizeTitle(sizeId, item = null) {
      if (item && item.size && item.size.title) return item.size.title;
      if (sizeId && this.sizes.length) {
        const found = this.sizes.find((s) => s.id === sizeId);
        if (found) return found.title;
      }
      return '';
    },

    getUnitTitle(unitId, item = null) {
      if (item && item.unit && item.unit.title) return item.unit.title;
      if (item && item.unit_title) return item.unit_title;
      if (unitId && this.units.length) {
        const found = this.units.find((u) => u.id === unitId);
        if (found) return found.title;
      }
      return 'Pcs';
    },

    filterPoOptions(option, label, search) {
      if (!search) return true;
      const s = search.toLowerCase().trim();
      const inv = (option.invoiceno || '').toLowerCase();
      const sup = (option.supplier?.org_name || '').toLowerCase();
      const date = (option.purchase_date || '').toLowerCase();
      const amt = String(option.total_amount || '').toLowerCase();
      const st = (option.receive_status || '').toLowerCase();
      const disp = (option.display_label || '').toLowerCase();
      return inv.includes(s) || sup.includes(s) || date.includes(s) || amt.includes(s) || st.includes(s) || disp.includes(s);
    },

    switchTab(tab) {
      if (this.activeTab === tab) return;
      this.activeTab = tab;
      this.data.grn_type = tab;
      this.selectedPurchase = null;

      if (tab === 'po') {
        this.data.supplier_id = null;
        this.selectedSupplierName = '';
        this.data.fund_account_id = null;
        this.data.discount = 0;
        this.data.grn_details = [];
        this.data.purchase_id = null;
        this.calculatePoTotals();
      } else if (tab === 'supplier') {
        this.data.purchase_id = null;
        this.selectedSupplierName = '';
        this.data.fund_account_id = null;
        this.data.discount = 0;
        this.data.grn_details = [];
        this.calculateDynamicTotals();
      } else if (tab === 'direct') {
        this.data.purchase_id = null;
        this.data.supplier_id = null;
        this.selectedSupplierName = '';
        this.data.grn_details = [];
        if (this.fundaccounts.length > 0 && !this.data.fund_account_id) {
          this.data.fund_account_id = this.fundaccounts[0].id;
          this.onFundAccountChange(this.data.fund_account_id);
        }
        this.calculateDynamicTotals();
      }
    },

    getPendingPurchases() {
      axios.get('grn/pending-purchases')
        .then((res) => {
          this.pendingPurchases = res.data || [];
        })
        .catch((err) => {
          console.error(err);
        });
    },

    getFundAccounts() {
      axios.get('getfundaccounts/')
        .then((res) => {
          this.fundaccounts = res.data || [];
          if (this.activeTab === 'direct' && !this.data.fund_account_id && this.fundaccounts.length > 0) {
            this.data.fund_account_id = this.fundaccounts[0].id;
            this.onFundAccountChange(this.data.fund_account_id);
          }
        })
        .catch((err) => {
          console.error(err);
        });
    },

    onFundAccountChange(fund_account_id) {
      if (!fund_account_id) {
        this.fundBalance = 0;
        return;
      }
      axios.get(`getfunds/${fund_account_id}`)
        .then((res) => {
          this.fundBalance = parseFloat(res.data) || 0;
        })
        .catch((err) => {
          console.error(err);
          this.fundBalance = 0;
        });
    },

    getCategories() {
      axios.get('getcategories/Item')
        .then((res) => {
          this.categories = res.data || [];
        })
        .catch((err) => console.error(err));
    },

    getUnits() {
      axios.get('getunits/Item')
        .then((res) => {
          this.units = res.data || [];
        })
        .catch((err) => console.error(err));
    },

    getColorsAndSizes() {
      axios.get('color?allData=true').then((res) => {
        this.colors = res.data || [];
      });
      axios.get('size?allData=true').then((res) => {
        this.sizes = res.data || [];
      });
    },

    onPurchaseChange(purchase_id) {
      if (!purchase_id) {
        this.data.supplier_id = null;
        this.selectedSupplierName = '';
        this.selectedPurchase = null;
        this.data.grn_details = [];
        this.calculatePoTotals();
        return;
      }

      this.selectedPurchase = this.pendingPurchases.find((p) => p.id === purchase_id) || null;

      axios.get(`grn/purchase-items/${purchase_id}`)
        .then((res) => {
          this.data.supplier_id = res.data.purchase.supplier_id;
          this.selectedSupplierName = res.data.purchase.supplier?.org_name || '';
          this.selectedPurchase = res.data.purchase;
          this.data.grn_details = res.data.items || [];
          this.calculatePoTotals();
        })
        .catch((err) => {
          this.$toast('Failed to load items for this purchase order.', 'error');
        });
    },

    onPoQtyChange(item) {
      const qty = parseFloat(item.received_qty) || 0;
      const price = parseFloat(item.unit_price) || 0;
      item.total_amount = Number((qty * price).toFixed(2));
      this.calculatePoTotals();
    },

    calculatePoTotals() {
      let totalQty = 0;
      let totalAmount = 0;

      if (this.data.grn_details) {
        this.data.grn_details.forEach((item) => {
          const qty = parseFloat(item.received_qty) || 0;
          const price = parseFloat(item.unit_price) || 0;
          item.total_amount = Number((qty * price).toFixed(2));
          totalQty += qty;
          totalAmount += item.total_amount;
        });
      }

      this.data.total_qty = totalQty;
      this.data.sub_total = Number(totalAmount.toFixed(2));
      this.data.total_amount = Number(totalAmount.toFixed(2));
    },

    // 🌟 Product Popup Modal Handlers (Supplier & Direct Purchase)
    openAddProductModal() {
      this.isEditingModal = false;
      this.modalForm = {
        rowIndex: null,
        category_id: null,
        item_id: null,
        item_title: '',
        items: [],
        color_id: null,
        size_id: null,
        unit_id: null,
        unit_title: '',
        unit_price: 0,
        selling_price: 0,
        received_qty: 1,
        total_amount: 0,
        serial_no: '',
        serialsList: [],
        tempSerial: '',
        bulkSerialText: '',
        bulkMode: false,
        showSerialsSection: false, // collapsed by default!
        syncQtyWithSerials: true,
      };
      this.showProductModal = true;
    },

    openEditProductModal(index, pitem) {
      this.isEditingModal = true;
      const unitPrice = parseFloat(pitem.unit_price) || 0;
      const sellingPrice = parseFloat(pitem.selling_price) || 0;
      const qty = parseFloat(pitem.received_qty) || 1;
      const totalAmount = parseFloat(pitem.total_amount) || Number((unitPrice * qty).toFixed(2));
      const serials = pitem.serial_no ? pitem.serial_no.split(',').map((s) => s.trim()).filter((s) => s.length > 0) : [];

      this.modalForm = {
        rowIndex: index,
        category_id: pitem.category_id,
        item_id: pitem.item_id,
        item_title: this.getItemTitle(pitem),
        items: pitem.items || [],
        color_id: pitem.color_id || null,
        size_id: pitem.size_id || null,
        unit_id: pitem.unit_id || null,
        unit_title: this.getUnitTitle(pitem.unit_id, pitem),
        unit_price: unitPrice,
        selling_price: sellingPrice,
        received_qty: qty,
        total_amount: totalAmount,
        serial_no: pitem.serial_no || '',
        serialsList: serials,
        tempSerial: '',
        bulkSerialText: '',
        bulkMode: false,
        showSerialsSection: serials.length > 0, // open if already has serials
        syncQtyWithSerials: true,
      };

      if (pitem.category_id && (!pitem.items || pitem.items.length === 0)) {
        axios.get(`getitemsbycategory/${pitem.category_id}`).then((res) => {
          this.modalForm.items = res.data || [];
          const found = this.modalForm.items.find((i) => i.id === pitem.item_id);
          if (found && !this.modalForm.unit_title) {
            this.modalForm.unit_id = found.unit_id;
            this.modalForm.unit_title = found.unit?.title || this.getUnitTitle(found.unit_id);
          }
        });
      }

      this.showProductModal = true;
    },

    closeProductModal() {
      this.showProductModal = false;
      this.isEditingModal = false;
    },

    onModalCategoryChange() {
      this.modalForm.item_id = null;
      this.modalForm.items = [];
      this.modalForm.unit_id = null;
      this.modalForm.unit_title = '';

      if (!this.modalForm.category_id) return;

      axios
        .get(`getitemsbycategory/${this.modalForm.category_id}`)
        .then((response) => {
          this.modalForm.items = response.data || [];
        })
        .catch(() => {
          this.modalForm.items = [];
        });
    },

    onModalItemChange(selectedObj = null) {
      if (!selectedObj || typeof selectedObj !== 'object') {
        selectedObj = this.modalForm.items.find((i) => i.id === this.modalForm.item_id);
      }
      if (selectedObj) {
        this.modalForm.item_id = selectedObj.id;
        this.modalForm.item_title = selectedObj.title;
        this.modalForm.unit_id = selectedObj.unit_id || null;
        this.modalForm.unit_title = selectedObj.unit?.title || this.getUnitTitle(selectedObj.unit_id);
        if (selectedObj.opening_rate && (!this.modalForm.unit_price || this.modalForm.unit_price == 0)) {
          this.modalForm.unit_price = parseFloat(selectedObj.opening_rate) || 0;
        }
        this.onModalPriceOrQtyChange();
      }
    },

    onModalPriceOrQtyChange() {
      const price = parseFloat(this.modalForm.unit_price) || 0;
      const qty = parseFloat(this.modalForm.received_qty) || 0;
      this.modalForm.total_amount = Number((price * qty).toFixed(2));
    },

    // 🔍 Check Serial Duplicates in Database
    async checkSerialDatabase(sn) {
      try {
        const res = await axios.post('grn/check-serials', {
          serials: [sn],
          grn_id: this.data.id || null,
        });
        if (res.data?.has_duplicates && res.data.duplicates.length > 0) {
          const dup = res.data.duplicates[0];
          this.$toast(`⚠️ Warning: Serial "${sn}" already exists in ${dup.grn_no} (${dup.item_name})!`, 'error');
          return false;
        }
      } catch (err) {
        console.error(err);
      }
      return true;
    },

    async checkBulkSerialsDatabase(serials) {
      try {
        const res = await axios.post('grn/check-serials', {
          serials: serials,
          grn_id: this.data.id || null,
        });
        if (res.data?.has_duplicates && res.data.duplicates.length > 0) {
          res.data.duplicates.forEach((dup) => {
            this.$toast(`⚠️ Warning: Serial "${dup.serial_no}" already exists in ${dup.grn_no} (${dup.item_name})!`, 'error');
          });
        }
      } catch (err) {
        console.error(err);
      }
    },

    // Serial Entry in Modal Form
    async addModalSerial() {
      const sn = this.modalForm.tempSerial ? this.modalForm.tempSerial.trim() : '';
      if (!sn) return;

      if (this.modalForm.serialsList.includes(sn)) {
        this.$toast(`Serial number "${sn}" already added in this list`, 'warning');
        this.modalForm.tempSerial = '';
        return;
      }

      // Check if serial is in another row
      if (this.data.grn_details) {
        for (let idx = 0; idx < this.data.grn_details.length; idx++) {
          if (this.modalForm.rowIndex !== null && idx === this.modalForm.rowIndex) continue;
          const otherSerials = (this.data.grn_details[idx].serial_no || '').split(',').map((s) => s.trim());
          if (otherSerials.includes(sn)) {
            this.$toast(`Serial "${sn}" is already entered in Row #${idx + 1}!`, 'error');
          }
        }
      }

      // Check database
      await this.checkSerialDatabase(sn);

      this.modalForm.serialsList.push(sn);
      this.modalForm.tempSerial = '';

      if (this.modalForm.syncQtyWithSerials) {
        this.modalForm.received_qty = this.modalForm.serialsList.length;
        this.onModalPriceOrQtyChange();
      }
    },

    async processModalBulkSerials() {
      if (!this.modalForm.bulkSerialText) return;
      const rawList = this.modalForm.bulkSerialText
        .split(/[\n,;\s]+/)
        .map((s) => s.trim())
        .filter((s) => s.length > 0);

      if (rawList.length === 0) return;

      const toAdd = [];
      rawList.forEach((sn) => {
        if (!this.modalForm.serialsList.includes(sn) && !toAdd.includes(sn)) {
          toAdd.push(sn);
        }
      });

      if (toAdd.length > 0) {
        await this.checkBulkSerialsDatabase(toAdd);
        this.modalForm.serialsList.push(...toAdd);
        this.$toast(`${toAdd.length} serial numbers added to list`, 'success');

        if (this.modalForm.syncQtyWithSerials) {
          this.modalForm.received_qty = this.modalForm.serialsList.length;
          this.onModalPriceOrQtyChange();
        }
      }

      this.modalForm.bulkSerialText = '';
      this.modalForm.bulkMode = false;
    },

    removeModalSerial(index) {
      this.modalForm.serialsList.splice(index, 1);
      if (this.modalForm.syncQtyWithSerials && this.modalForm.serialsList.length > 0) {
        this.modalForm.received_qty = this.modalForm.serialsList.length;
        this.onModalPriceOrQtyChange();
      }
    },

    clearAllModalSerials() {
      this.modalForm.serialsList = [];
    },

    copyAllModalSerials() {
      if (this.modalForm.serialsList.length === 0) return;
      const text = this.modalForm.serialsList.join(', ');
      navigator.clipboard.writeText(text).then(() => {
        this.$toast('Serials copied to clipboard', 'success');
      });
    },

    saveProductFromModal() {
      if (!this.modalForm.category_id) {
        this.$toast('Please select Category (ক্যাটাগরি নির্বাচন করুন)', 'warning');
        return;
      }
      if (!this.modalForm.item_id) {
        this.$toast('Please select Product / Item (পণ্য নির্বাচন করুন)', 'warning');
        return;
      }
      if (!this.modalForm.received_qty || parseFloat(this.modalForm.received_qty) <= 0) {
        this.$toast('Quantity must be greater than 0 (পরিমাণ অন্তত ১ হতে হবে)', 'warning');
        return;
      }

      const selectedItem = this.modalForm.items.find((i) => i.id === this.modalForm.item_id);
      const unitPrice = parseFloat(this.modalForm.unit_price) || 0;
      const sellingPrice = parseFloat(this.modalForm.selling_price) || 0;
      const qty = parseFloat(this.modalForm.received_qty) || 1;
      const totalAmount = Number((unitPrice * qty).toFixed(2));
      const serialStr = this.modalForm.serialsList.join(', ');

      const newRow = {
        category_id: this.modalForm.category_id,
        category: { id: this.modalForm.category_id, title: this.getCategoryTitle(this.modalForm.category_id) },
        item_id: this.modalForm.item_id,
        item: selectedItem || { id: this.modalForm.item_id, title: this.modalForm.item_title },
        items: this.modalForm.items,
        color_id: this.modalForm.color_id || null,
        size_id: this.modalForm.size_id || null,
        unit_id: this.modalForm.unit_id || (selectedItem ? selectedItem.unit_id : null),
        unit_title: this.modalForm.unit_title || (selectedItem?.unit?.title || 'Pcs'),
        unit_price: unitPrice,
        selling_price: sellingPrice,
        received_qty: qty,
        ordered_qty: qty,
        previously_received_qty: 0,
        remaining_qty: 0,
        total_amount: totalAmount,
        serial_no: serialStr,
      };

      if (!this.data.grn_details) {
        this.data.grn_details = [];
      }

      if (this.modalForm.rowIndex !== null && this.modalForm.rowIndex >= 0) {
        this.data.grn_details.splice(this.modalForm.rowIndex, 1, newRow);
        this.$toast('Product updated successfully (পণ্য সফলভাবে আপডেট হয়েছে)', 'success');
      } else {
        this.data.grn_details.push(newRow);
        this.$toast('Product added to receive list (পণ্য তালিকায় যুক্ত হয়েছে)', 'success');
      }

      this.calculateDynamicTotals();
      this.closeProductModal();
    },

    removeDynamicItemRow(index) {
      if (this.data.grn_details) {
        this.data.grn_details.splice(index, 1);
        this.calculateDynamicTotals();
        this.$toast('Product removed from list', 'info');
      }
    },

    calculateDynamicTotals() {
      let totalQty = 0;
      let subTotal = 0;

      if (this.data.grn_details) {
        this.data.grn_details.forEach((item) => {
          const price = parseFloat(item.unit_price) || 0;
          const qty = parseFloat(item.received_qty) || 0;
          item.total_amount = Number((price * qty).toFixed(2));
          totalQty += qty;
          subTotal += item.total_amount;
        });
      }

      this.data.total_qty = totalQty;
      this.data.sub_total = Number(subTotal.toFixed(2));
      const discount = parseFloat(this.data.discount) || 0;
      this.data.total_amount = Math.max(0, Number((subTotal - discount).toFixed(2)));
    },

    getSelectedProductTitle(item) {
      if (!item) return 'Product';
      if (item.item && item.item.title) return item.item.title;
      if (item.items && item.item_id) {
        const found = item.items.find((i) => i.id === item.item_id);
        if (found) return found.title;
      }
      return item.item_id ? `Product #${item.item_id}` : 'No product selected';
    },

    getSerialCount(serialStr) {
      if (!serialStr) return 0;
      return serialStr.split(',').map((s) => s.trim()).filter((s) => s.length > 0).length;
    },

    // Standalone Serial Modal (for PO mode)
    openSerialModal(index, pitem) {
      this.activeRowIndex = index;
      this.tempSerial = '';
      this.bulkSerialText = '';
      this.bulkEntryMode = false;
      if (pitem.serial_no) {
        this.modalSerials = pitem.serial_no.split(',').map((s) => s.trim()).filter((s) => s.length > 0);
      } else {
        this.modalSerials = [];
      }
      this.showSerialModal = true;

      this.$nextTick(() => {
        if (this.$refs.serialInput) {
          this.$refs.serialInput.focus();
        }
      });
    },

    closeSerialModal() {
      this.showSerialModal = false;
      this.activeRowIndex = null;
      this.modalSerials = [];
      this.tempSerial = '';
      this.bulkSerialText = '';
    },

    async addSerialFromInput() {
      const sn = this.tempSerial ? this.tempSerial.trim() : '';
      if (sn) {
        if (this.modalSerials.includes(sn)) {
          this.$toast(`Serial number "${sn}" already added in list`, 'warning');
          this.tempSerial = '';
          return;
        }

        // Check if serial is in another row
        if (this.data.grn_details) {
          for (let idx = 0; idx < this.data.grn_details.length; idx++) {
            if (this.activeRowIndex !== null && idx === this.activeRowIndex) continue;
            const otherSerials = (this.data.grn_details[idx].serial_no || '').split(',').map((s) => s.trim());
            if (otherSerials.includes(sn)) {
              this.$toast(`Serial "${sn}" is already entered in Row #${idx + 1}!`, 'error');
            }
          }
        }

        // Check database
        await this.checkSerialDatabase(sn);

        this.modalSerials.push(sn);
        this.tempSerial = '';
      }
    },

    async processBulkSerials() {
      if (!this.bulkSerialText) return;
      const rawList = this.bulkSerialText
        .split(/[\n,;\s]+/)
        .map((s) => s.trim())
        .filter((s) => s.length > 0);

      if (rawList.length === 0) return;

      const toAdd = [];
      rawList.forEach((sn) => {
        if (!this.modalSerials.includes(sn) && !toAdd.includes(sn)) {
          toAdd.push(sn);
        }
      });

      if (toAdd.length > 0) {
        await this.checkBulkSerialsDatabase(toAdd);
        this.modalSerials.push(...toAdd);
        this.$toast(`${toAdd.length} serial numbers added to list`, 'success');
      }

      this.bulkSerialText = '';
      this.bulkEntryMode = false;
    },

    removeSerial(index) {
      this.modalSerials.splice(index, 1);
    },

    clearAllModalSerialsStandalone() {
      this.modalSerials = [];
    },

    copyAllModalSerialsStandalone() {
      if (this.modalSerials.length === 0) return;
      const text = this.modalSerials.join(', ');
      navigator.clipboard.writeText(text).then(() => {
        this.$toast('Serials copied to clipboard', 'success');
      });
    },

    saveSerialsFromModal() {
      if (this.activeRowIndex !== null && this.data.grn_details[this.activeRowIndex]) {
        const row = this.data.grn_details[this.activeRowIndex];
        const serialStr = this.modalSerials.join(', ');
        row.serial_no = serialStr;

        if (this.syncQtyWithSerials && this.modalSerials.length > 0) {
          if (this.activeTab === 'po') {
            row.received_qty = Math.min(row.remaining_qty, this.modalSerials.length);
            this.onPoQtyChange(row);
          } else {
            row.received_qty = this.modalSerials.length;
            this.calculateDynamicTotals();
          }
        }
      }
      this.closeSerialModal();
      this.$toast('Serial numbers updated', 'success');
    },

    submit: function () {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          this.$toast(
            'You need to fill ' + error + ' more empty mandatory fields',
            'warning'
          );
          return false;
        }

        // Tab-specific validation
        if (this.activeTab === 'po') {
          if (!this.data.purchase_id) {
            this.$toast('Please select a Purchase Order.', 'warning');
            return false;
          }

          const hasExceeded = this.data.grn_details.some(
            (item) => parseFloat(item.received_qty) > parseFloat(item.remaining_qty)
          );
          if (hasExceeded) {
            this.$toast('Receive quantity cannot exceed remaining quantity for any item.', 'error');
            return false;
          }
        } else if (this.activeTab === 'supplier') {
          if (!this.data.supplier_id) {
            this.$toast('Please select a Supplier.', 'warning');
            return false;
          }

          if (!this.data.grn_details || this.data.grn_details.length === 0) {
            this.$toast('Please add at least one product row (পণ্য যোগ করুন)।', 'warning');
            return false;
          }

          const hasEmptyProduct = this.data.grn_details.some((item) => !item.item_id);
          if (hasEmptyProduct) {
            this.$toast('Please select a product for all rows.', 'warning');
            return false;
          }
        } else if (this.activeTab === 'direct') {
          if (!this.data.fund_account_id) {
            this.$toast('Please select a Fund Account for payment settlement.', 'warning');
            return false;
          }

          if (!this.data.grn_details || this.data.grn_details.length === 0) {
            this.$toast('Please add at least one product row (পণ্য যোগ করুন)।', 'warning');
            return false;
          }

          const hasEmptyProduct = this.data.grn_details.some((item) => !item.item_id);
          if (hasEmptyProduct) {
            this.$toast('Please select a product for all rows.', 'warning');
            return false;
          }
        }

        if (this.data.total_qty <= 0) {
          this.$toast('Please enter at least one item quantity to receive.', 'warning');
          return false;
        }

        this.data.grn_type = this.activeTab;

        if (res) {
          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },

    getGrnData() {
      axios.get(`${this.model}/${this.$route.params.id}`)
        .then((response) => {
          const fetched = response.data;
          this.data = fetched;
          this.activeTab = fetched.grn_type || (fetched.purchase_id ? 'po' : (fetched.supplier_id ? 'supplier' : 'direct'));
          this.data.grn_type = this.activeTab;
          this.selectedSupplierName = fetched.supplier?.org_name || '';
          this.selectedPurchase = fetched.purchase || null;

          if (fetched.fund_account_id) {
            this.onFundAccountChange(fetched.fund_account_id);
          }

          if (this.activeTab === 'po') {
            this.calculatePoTotals();
          } else {
            // Attach categories items for each dynamic row
            if (this.data.grn_details) {
              this.data.grn_details.forEach((detail) => {
                if (detail.category_id) {
                  axios.get(`getitemsbycategory/${detail.category_id}`).then((ires) => {
                    detail.items = ires.data || [];
                  });
                }
              });
            }
            this.calculateDynamicTotals();
          }
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },

  created() {
    this.getPendingPurchases();
    this.getCategories();
    this.getUnits();
    this.getColorsAndSizes();
    this.getFundAccounts();

    if (this.$route.params.id) {
      this.page_title = `Goods Receive (GRN) Edit`;
      this.getGrnData();
    } else {
      this.page_title = `Goods Receive (GRN) Create`;
      // If purchase_id is passed as query parameter
      if (this.$route.query.purchase_id) {
        this.activeTab = 'po';
        this.data.grn_type = 'po';
        this.data.purchase_id = parseInt(this.$route.query.purchase_id);
        this.onPurchaseChange(this.data.purchase_id);
      }
    }
  },

  validators: {
    'data.grn_date': function (value = null) {
      return Validator.value(value).required('GRN Date is required');
    },
    'data.warehouse_id': function (value = null) {
      return Validator.value(value).required('Destination Warehouse is required');
    },
    'data.purchase_id': function (value = null) {
      if (this.activeTab === 'po') {
        return Validator.value(value).required('Purchase Order is required');
      }
    },
    'data.supplier_id': function (value = null) {
      if (this.activeTab === 'supplier') {
        return Validator.value(value).required('Supplier is required');
      }
    },
    'data.fund_account_id': function (value = null) {
      if (this.activeTab === 'direct') {
        return Validator.value(value).required('Fund Account is required for Direct Purchase');
      }
    },
  },
};
</script>

<style scoped>
.grn-create-wrapper {
  animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

.theme-bg {
  background-color: #112C47 !important;
}

.text-theme {
  color: #112C47 !important;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 71, 0.08) !important;
}

.theme-table-header {
  background-color: #112C47;
  color: #ffffff;
}

.theme-table-header th {
  padding: 12px 10px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.3px;
  text-transform: uppercase;
  border: none;
}

.custom-items-table tbody tr td {
  padding: 8px 6px;
  vertical-align: middle;
  border-color: #f1f5f9;
}

/* Tabs Switcher Navigation */
.grn-tabs-nav {
  background-color: #f1f5f9 !important;
  gap: 4px;
}

.tab-pill-btn {
  color: #475569;
  border: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-pill-btn:hover {
  color: #0f172a;
  background-color: rgba(255, 255, 255, 0.6);
}

.tab-pill-btn.active {
  background: #112C47;
  color: #ffffff !important;
  box-shadow: 0 4px 10px rgba(17, 44, 71, 0.2);
}

/* Mode Alert Banner */
.mode-alert {
  border-radius: 10px;
}

.mode-alert-icon {
  width: 44px;
  height: 44px;
  background-color: rgba(255, 255, 255, 0.7);
  flex-shrink: 0;
}

/* Table Form Inputs & Responsive Layout */
.table-scrollable {
  overflow-x: auto !important;
  overflow-y: visible !important;
  min-height: 220px;
}

.table-compact-input {
  height: 36px !important;
  font-size: 13px !important;
  border-radius: 6px !important;
  border-color: #cbd5e1;
}

.table-compact-input:focus {
  border-color: #112C47;
  box-shadow: 0 0 0 2px rgba(17, 44, 71, 0.15);
}

.btn-action {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  font-size: 13px;
}

.serial-btn {
  height: 32px;
  font-size: 12px;
}

.serial-btn.active-serial {
  background-color: rgba(17, 44, 71, 0.08);
  border-color: #112C47;
  color: #112C47;
}

.dashed-btn {
  border: 1.5px dashed #94a3b8;
  background-color: #ffffff;
  color: #334155;
  transition: all 0.2s ease;
}

.dashed-btn:hover {
  border-color: #112C47;
  background-color: rgba(17, 44, 71, 0.04);
  color: #112C47;
}

/* Fund Balance Card */
.fund-balance-card {
  transition: all 0.2s ease;
}

/* Grand Total Box */
.grand-total-box {
  background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%);
  box-shadow: 0 4px 12px rgba(17, 44, 71, 0.15);
}

/* Product Modal Backdrop */
.product-modal-backdrop {
  z-index: 1055 !important;
}

.modal-vselect :deep(.vs__dropdown-toggle) {
  min-height: 38px !important;
  border-radius: 6px !important;
  border-color: #cbd5e1 !important;
  font-size: 13px !important;
}

.modal-vselect :deep(.vs__selected) {
  font-size: 13px !important;
  color: #1e293b !important;
}

/* Serial Modal Chips */
.serials-list-box {
  min-height: 80px;
  max-height: 180px;
  overflow-y: auto;
}

.serial-badge {
  background-color: #112C47;
  color: #ffffff;
  font-size: 12px;
  border-radius: 6px;
}

.badge-num {
  background-color: rgba(255, 255, 255, 0.2);
  padding: 1px 5px;
  border-radius: 4px;
  font-size: 10px;
}

.delete-serial-btn {
  color: #fca5a5;
  cursor: pointer;
  transition: color 0.15s ease;
}

.delete-serial-btn:hover {
  color: #ef4444;
}

.cursor-pointer {
  cursor: pointer;
}

.transition-transform {
  transition: transform 0.2s ease;
}

.fa-rotate-180 {
  transform: rotate(180deg);
}

.btn-xs {
  padding: 4px 8px !important;
  font-size: 11px !important;
  border-radius: 6px !important;
}

/* Global vs dropdown */
:global(.vs__dropdown-menu) {
  z-index: 999999 !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18) !important;
  border-radius: 6px !important;
  border: 1px solid #cbd5e1 !important;
  max-height: 260px !important;
  background-color: #ffffff !important;
}

:global(.vs__dropdown-option) {
  padding: 7px 12px !important;
  font-size: 13px !important;
  color: #1e293b !important;
}

:global(.vs__dropdown-option--highlight) {
  background-color: #112C47 !important;
  color: #ffffff !important;
}

.stat-icon-sm {
  flex-shrink: 0;
}

/* PO Selector Dropdown Styling */
.po-vselect-wrapper :deep(.vs__dropdown-toggle) {
  min-height: 40px !important;
  border-radius: 6px !important;
  border-color: #cbd5e1 !important;
  padding: 2px 6px !important;
  background-color: #ffffff;
}

.po-vselect-wrapper :deep(.vs__dropdown-toggle):focus-within {
  border-color: #112C47 !important;
  box-shadow: 0 0 0 2px rgba(17, 44, 71, 0.15) !important;
}

.po-option-card {
  padding: 4px 0;
}

:global(.vs__dropdown-option--highlight .po-option-card .text-dark),
:global(.vs__dropdown-option--highlight .po-option-card .text-muted),
:global(.vs__dropdown-option--highlight .po-option-card .text-secondary),
:global(.vs__dropdown-option--highlight .po-option-card .text-success) {
  color: #ffffff !important;
}
</style>
