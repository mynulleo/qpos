<template>
  <create-form @onSubmit="submit">
    <div class="purchase-create-wrapper">
      <!-- 🌟 Top Hero / Information Card -->
      <div class="card border-0 shadow-sm mb-4 form-card top-info-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Purchase Order Information (ক্রয় তথ্যাবলী)</h6>
              <span class="small text-muted">Enter invoice details, supplier and purchase date</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <span class="small fw-semibold text-muted">Status:</span>
              <span class="badge" :class="data.status ? 'bg-success' : 'bg-secondary'">
                {{ data.status ? 'Active' : 'Deactive' }}
              </span>
            </div>
          </div>
        </div>

        <div class="card-body p-4 overflow-visible">
          <div class="row g-3">
            <!-- Invoice No -->
            <div class="col-md-3">
              <Input
                v-model="data.invoiceno"
                field="data.invoiceno"
                col="12"
                title="Invoice / Bill No"
                placeholder="Auto Generated (e.g. PUR-...)"
                :req="false"
              >
                <template #append_in_input_group>
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    @click="getGeneratedInvoiceNo(true)"
                    title="Regenerate Invoice / Bill No"
                  >
                    <i class="fas fa-sync-alt"></i>
                  </button>
                </template>
              </Input>
            </div>

            <!-- Purchase Date -->
            <div class="col-md-3">
              <date-picker
                id="date1"
                v-model="data.purchase_date"
                field="data.purchase_date"
                title="Purchase Date"
                placeholder="Purchase Date"
                col="12"
                :req="true"
              ></date-picker>
            </div>

            <!-- Supplier Selection with appendToBody -->
            <div class="col-md-4">
              <Select
                title="Supplier (সরবরাহকারী)"
                v-model="data.supplier_id"
                field="data.supplier_id"
                label="org_name"
                :reduce="(obj) => obj.id"
                :options="$root.global.suppliers"
                col="12"
                placeholder="-- Search / Select Supplier --"
                :closeOnSelect="true"
                :appendToBody="true"
                :required="true"
              />
            </div>

            <!-- Status Switch -->
            <div class="col-md-2 d-flex flex-column justify-content-center">
              <label class="form-label small fw-semibold mb-2">Purchase Status</label>
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

      <!-- 📊 Live KPI / Metric Summary Cards -->
      <div class="row g-3 mb-4 kpi-row">
        <!-- Total Items & Qty -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Total Items & Qty</span>
                <div class="stat-icon theme-bg-soft text-theme rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-boxes"></i>
                </div>
              </div>
              <div class="d-flex align-items-baseline gap-2">
                <h4 class="fw-bold mb-0 text-dark font-monospace">{{ totalItemsCount }} Items</h4>
                <span class="badge bg-light text-dark border font-monospace">{{ totalQuantity }} Units</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Gross Subtotal -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Sub Total (মোট)</span>
                <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-calculator"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-dark font-monospace">{{ formatCurrency(data.amount) }}</h4>
            </div>
          </div>
        </div>

        <!-- Discount & Tax -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted fw-bold small text-uppercase">Discount & Tax</span>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-percentage"></i>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="small text-danger fw-semibold font-monospace">- {{ formatCurrency(data.discount || 0) }}</span>
                <span class="small text-primary fw-semibold font-monospace">+ {{ formatCurrency(data.tax || 0) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Net Payable Grand Total -->
        <div class="col-xl-3 col-md-6 col-12">
          <div class="card stat-card border-0 shadow-sm h-100 total-card-highlight">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-white-50 fw-bold small text-uppercase">Net Grand Total</span>
                <div class="stat-icon bg-white bg-opacity-20 text-white rounded-circle d-flex align-items-center justify-content-center">
                  <i class="fas fa-coins"></i>
                </div>
              </div>
              <h4 class="fw-bold mb-0 text-white font-monospace">{{ formatCurrency(data.total_amount) }}</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- 📦 Purchase Line Items Card -->
      <div class="card border-0 shadow-sm mb-4 form-card items-table-card">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
              <i class="fas fa-cart-arrow-down"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">Purchase Items (ক্রয়কৃত পণ্যসমূহের তালিকা)</h6>
              <span class="small text-muted">Manage items, pricing, unit quantities and serial numbers</span>
            </div>
            <span class="badge theme-bg text-white rounded-pill px-2 py-1 ms-2 font-monospace">
              {{ data.purchase_details ? data.purchase_details.length : 0 }} Items
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

        <div class="card-body p-0">
          <div class="table-responsive custom-table-container">
            <table class="table custom-items-table table-hover align-middle mb-0">
              <thead class="theme-table-header text-center">
                <tr>
                  <th style="width: 4%;">#</th>
                  <th style="width: 24%;" class="text-start ps-3">Product / Item (পণ্য)</th>
                  <th style="width: 12%;">Variant (ভেরিয়েন্ট)</th>
                  <th style="width: 8%;">Unit (একক)</th>
                  <th style="width: 11%;" class="text-end">Cost Price (ক্রয়)</th>
                  <th style="width: 11%;" class="text-end">Selling Price (বিক্রয়)</th>
                  <th style="width: 8%;">Qty (পরিমাণ)</th>
                  <th style="width: 11%;" v-if="isElectronicsShop">Serial Numbers</th>
                  <th style="width: 11%;" class="text-end pe-3">Total Amount</th>
                  <th style="width: 8%;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(pitem, index) in data.purchase_details" :key="index" class="item-row">
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
                    ৳ {{ formatNum(pitem.price) }}
                  </td>

                  <!-- Selling Price -->
                  <td class="text-end font-monospace text-success fw-semibold">
                    ৳ {{ formatNum(pitem.selling_price) }}
                  </td>

                  <!-- Quantity -->
                  <td class="text-center font-monospace fw-bold fs-6 text-dark">
                    {{ pitem.qty }}
                  </td>

                  <!-- Serial Numbers (for Electronics) -->
                  <td class="text-center" v-if="isElectronicsShop">
                    <template v-if="getSerialCount(pitem.serial_no) > 0">
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1 font-monospace"
                        @click="openEditProductModal(index, pitem)"
                        title="Click to view/edit serial numbers"
                      >
                        <i class="fas fa-barcode"></i>
                        <span>{{ getSerialCount(pitem.serial_no) }} S/N</span>
                      </button>
                    </template>
                    <template v-else>
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1"
                        @click="openEditProductModal(index, pitem)"
                        title="Add Serial Numbers"
                      >
                        <i class="fas fa-plus"></i> Add S/N
                      </button>
                    </template>
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
                        @click="removePurchaseDetails(index)"
                        title="Remove Product (মুছে ফেলুন)"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="!data.purchase_details || data.purchase_details.length === 0">
                  <td :colspan="isElectronicsShop ? 10 : 9" class="text-center py-5 text-muted bg-white">
                    <div class="empty-state-wrapper py-3">
                      <div class="empty-icon theme-bg-soft text-theme rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                        <i class="fas fa-cart-plus fa-2x"></i>
                      </div>
                      <h6 class="fw-bold text-dark mb-1">No purchase items added yet</h6>
                      <p class="text-muted small mb-3">Click "+ Add Product Row" button to configure products, pricing and serial numbers.</p>
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
            </table>
          </div>

          <!-- Bottom Add Row Bar -->
          <div class="p-3 bg-light border-top text-center" v-if="data.purchase_details && data.purchase_details.length > 0">
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

      <!-- 📋 Bottom Financial Summary & Notes Panel -->
      <div class="row g-3 mb-4">
        <!-- Remarks & Notes (Left Side) -->
        <div class="col-lg-7 col-md-6 col-12">
          <div class="card border-0 shadow-sm h-100 form-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-comment-alt"></i>
              </div>
              <h6 class="fw-bold mb-0 text-dark">Purchase Notes & Details</h6>
            </div>
            <div class="card-body p-3">
              <label class="form-label small fw-semibold text-muted">Internal Remarks / Delivery Memo</label>
              <textarea
                class="form-control"
                rows="4"
                v-model="data.note"
                placeholder="Enter supplier invoice details, shipment reference, payment terms or delivery notes..."
              ></textarea>
              <div class="d-flex align-items-center gap-2 mt-3 p-2 rounded bg-light border text-muted small">
                <i class="fas fa-info-circle text-primary"></i>
                <span>Stock inventory and serial numbers will be recorded and updated upon Goods Receive Note (GRN).</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Summary (Right Side) -->
        <div class="col-lg-5 col-md-6 col-12">
          <div class="card border-0 shadow-sm h-100 form-card calculation-card">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
              <div class="section-icon theme-bg-soft text-theme rounded d-flex align-items-center justify-content-center">
                <i class="fas fa-receipt"></i>
              </div>
              <h6 class="fw-bold mb-0 text-dark">Financial Summary (হিসাব বিবরণী)</h6>
            </div>
            <div class="card-body p-4">
              <div class="summary-line d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted fw-semibold">Sub Total (মোট পণ্যের মূল্য):</span>
                <span class="fw-bold font-monospace fs-6 text-dark">{{ formatCurrency(data.amount) }}</span>
              </div>

              <!-- Discount Input -->
              <div class="summary-line d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted fw-semibold">Discount (ছাড় / ডিসকাউন্ট):</span>
                <div class="input-group input-group-sm" style="width: 140px;">
                  <span class="input-group-text bg-light text-muted px-2">৳</span>
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace"
                    placeholder="0.00"
                    v-model.number="data.discount"
                    @input="calculateTotals"
                  />
                </div>
              </div>

              <!-- Tax / VAT Input -->
              <div class="summary-line d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted fw-semibold">Tax / VAT (ভ্যাট / ট্যাক্স):</span>
                <div class="input-group input-group-sm" style="width: 140px;">
                  <span class="input-group-text bg-light text-muted px-2">৳</span>
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace"
                    placeholder="0.00"
                    v-model.number="data.tax"
                    @input="calculateTotals"
                  />
                </div>
              </div>

              <!-- Grand Total -->
              <div class="grand-total-box p-3 rounded mt-3 d-flex justify-content-between align-items-center">
                <div>
                  <span class="d-block text-white-50 small fw-bold text-uppercase">Net Total Amount</span>
                  <span class="text-white fs-5 fw-bold">সর্বমোট প্রদেয়</span>
                </div>
                <div class="text-end">
                  <h3 class="fw-bold mb-0 text-white font-monospace">{{ formatCurrency(data.total_amount) }}</h3>
                </div>
              </div>
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
            <i class="fas fa-arrow-left me-1"></i> Back to List
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
                <i class="fas fa-check-circle"></i> {{ $route.params.id ? "Update Purchase" : "Save & Create Purchase" }}
              </template>
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- 🏷️ Professional Product Addition & Serial Entry Popup Modal -->
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
                  {{ isEditingModal ? 'Edit Purchase Product (পণ্য সংশোধন)' : 'Add Purchase Product (পণ্য যোগ করুন)' }}
                </h5>
                <span class="small text-white-50">Select product, set costs, quantities and serial tracking</span>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="closeProductModal"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 bg-white">
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
                  Purchase Price (ক্রয় মূল্য ৳) <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light text-muted">৳</span>
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-end font-monospace fw-bold"
                    placeholder="0.00"
                    v-model.number="modalForm.price"
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
                  Quantity (পরিমাণ) <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                  <input
                    type="number"
                    step="any"
                    min="1"
                    class="form-control form-control-sm text-center font-monospace fw-bold fs-6"
                    placeholder="1"
                    v-model.number="modalForm.qty"
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
                    {{ modalForm.qty || 0 }} {{ modalForm.unit_title || 'Pcs' }} × ৳ {{ formatNum(modalForm.price || 0) }} = 
                    <span class="text-primary">{{ formatCurrency(modalForm.total_amount) }}</span>
                  </span>
                </div>
              </div>

              <!-- 🏷️ Integrated Serial Number Entry Section (Show/Hide Toggle) -->
              <div class="col-12 mt-2">
                <div class="card border rounded-3 overflow-hidden shadow-none">
                  <!-- Section Accordion Header -->
                  <div
                    class="card-header py-2 px-3 bg-light d-flex justify-content-between align-items-center cursor-pointer border-bottom"
                    @click="modalForm.showSerialsSection = !modalForm.showSerialsSection"
                  >
                    <div class="d-flex align-items-center gap-2">
                      <i class="fas fa-barcode text-primary"></i>
                      <span class="fw-bold text-dark small">Serial Numbers / IMEI Tracking (সিরিয়াল নম্বর সমূহ)</span>
                      <span class="badge rounded-pill" :class="modalForm.serialsList.length > 0 ? 'bg-primary' : 'bg-secondary'">
                        {{ modalForm.serialsList.length }} Serials
                      </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-white text-muted border small">
                        {{ modalForm.showSerialsSection ? 'Hide Section' : 'Show / Expand' }}
                      </span>
                      <i class="fas" :class="modalForm.showSerialsSection ? 'fa-chevron-up text-primary' : 'fa-chevron-down text-muted'"></i>
                    </div>
                  </div>

                  <!-- Collapsible Section Body -->
                  <div class="card-body p-3 bg-white" v-show="modalForm.showSerialsSection">
                    <!-- Mode Switch & Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                      <div class="btn-group btn-group-sm" role="group">
                        <button
                          type="button"
                          class="btn"
                          :class="!modalForm.bulkMode ? 'btn-primary' : 'btn-outline-secondary'"
                          @click="modalForm.bulkMode = false"
                        >
                          <i class="fas fa-keyboard me-1"></i> Quick / Barcode Entry
                        </button>
                        <button
                          type="button"
                          class="btn"
                          :class="modalForm.bulkMode ? 'btn-primary' : 'btn-outline-secondary'"
                          @click="modalForm.bulkMode = true"
                        >
                          <i class="fas fa-paste me-1"></i> Bulk Paste List
                        </button>
                      </div>

                      <div class="d-flex align-items-center gap-2">
                        <button
                          type="button"
                          class="btn btn-xs btn-outline-secondary"
                          @click="copyAllModalSerials"
                          :disabled="modalForm.serialsList.length === 0"
                          title="Copy all serials"
                        >
                          <i class="fas fa-copy me-1"></i> Copy All
                        </button>
                        <button
                          type="button"
                          class="btn btn-xs btn-outline-danger"
                          @click="clearAllModalSerials"
                          :disabled="modalForm.serialsList.length === 0"
                          title="Clear all serials"
                        >
                          <i class="fas fa-trash-alt me-1"></i> Clear
                        </button>
                      </div>
                    </div>

                    <!-- 1. Single Scan Input -->
                    <div v-if="!modalForm.bulkMode" class="mb-2">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                        <input
                          type="text"
                          class="form-control font-monospace fw-bold"
                          placeholder="Scan or type Serial No and press Enter..."
                          v-model="modalForm.tempSerial"
                          @keyup.enter.prevent="addModalSerial"
                        />
                        <button type="button" class="btn btn-primary fw-bold px-3" @click.prevent="addModalSerial">
                          <i class="fas fa-plus me-1"></i> Add
                        </button>
                      </div>
                      <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle me-1 text-primary"></i> Press <strong>Enter</strong> to instantly add each serial number.
                      </small>
                    </div>

                    <!-- 2. Bulk Textarea -->
                    <div v-else class="mb-2">
                      <textarea
                        class="form-control form-control-sm font-monospace"
                        rows="3"
                        v-model="modalForm.bulkSerialText"
                        placeholder="Paste multiple serials separated by line break, comma, or space..."
                      ></textarea>
                      <div class="d-flex justify-content-end mt-1">
                        <button type="button" class="btn btn-xs btn-primary fw-bold px-3" @click.prevent="processModalBulkSerials">
                          <i class="fas fa-plus-circle me-1"></i> Add Extracted Serials
                        </button>
                      </div>
                    </div>

                    <!-- Serial Chips Box -->
                    <div class="serials-list-box p-2 border rounded bg-light mt-2">
                      <div v-if="modalForm.serialsList.length > 0" class="d-flex flex-wrap gap-2">
                        <span
                          v-for="(sn, sIdx) in modalForm.serialsList"
                          :key="sIdx"
                          class="badge serial-badge font-monospace p-1 px-2 d-flex align-items-center gap-2 shadow-sm"
                        >
                          <span class="badge-num">#{{ sIdx + 1 }}</span>
                          <span>{{ sn }}</span>
                          <i
                            class="fas fa-times delete-serial-btn"
                            @click="removeModalSerial(sIdx)"
                            title="Remove Serial"
                          ></i>
                        </span>
                      </div>
                      <div v-else class="text-center py-3 text-muted">
                        <span class="small opacity-75">No serial numbers entered for this item yet.</span>
                      </div>
                    </div>

                    <!-- Bottom Auto Sync Checkbox -->
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                      <div class="form-check form-switch mb-0">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          id="syncQtyCheckModal"
                          v-model="modalForm.syncQtyWithSerials"
                        />
                        <label class="form-check-label small fw-bold text-dark cursor-pointer" for="syncQtyCheckModal">
                          Auto match Quantity to {{ modalForm.serialsList.length }}
                        </label>
                      </div>
                      <span class="small text-muted">
                        Total Serials: <strong class="text-theme font-monospace">{{ modalForm.serialsList.length }}</strong>
                      </span>
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
              <i class="fas fa-check me-1"></i> {{ isEditingModal ? 'Update Product' : 'Add to Purchase List' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = "purchase";

export default {
  computed: {
    isElectronicsShop() {
      const shopType = (this.site?.shop_type || this.$root.site?.shop_type || this.$root.site_setting?.shop_type || "").toLowerCase();
      return shopType === "electronics";
    },
    totalItemsCount() {
      return this.data.purchase_details ? this.data.purchase_details.length : 0;
    },
    totalQuantity() {
      if (!this.data.purchase_details) return 0;
      return this.data.purchase_details.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
    },
  },
  data() {
    return {
      model: model,
      page_title: "",
      data: {
        invoiceno: "",
        purchase_date: this.$filter.today(),
        supplier_id: null,
        amount: 0,
        discount: 0,
        tax: 0,
        total_amount: 0,
        status: true,
        note: "",
        purchase_details: [],
      },
      categories: [],
      units: [],
      colors: [],
      sizes: [],

      // Product Modal State
      showProductModal: false,
      isEditingModal: false,
      modalForm: {
        rowIndex: null,
        category_id: null,
        item_id: null,
        item_title: "",
        items: [],
        color_id: null,
        size_id: null,
        unit_id: null,
        unit_title: "",
        price: 0,
        selling_price: 0,
        qty: 1,
        serial_no: "",
        serialsList: [],
        total_amount: 0,
        tempSerial: "",
        bulkSerialText: "",
        bulkMode: false,
        showSerialsSection: true,
        syncQtyWithSerials: true,
      },
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  watch: {
    "data.purchase_details": {
      handler() {
        this.calculateTotals();
      },
      deep: true,
    },
    "data.discount": function () {
      this.calculateTotals();
    },
    "data.tax": function () {
      this.calculateTotals();
    },
  },

  methods: {
    formatCurrency(amount) {
      const val = parseFloat(amount) || 0;
      return (
        "৳ " +
        val.toLocaleString("en-BD", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })
      );
    },
    formatNum(amount) {
      const val = parseFloat(amount) || 0;
      return val.toLocaleString("en-BD", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
    getCategoryTitle(catId, pitem = null) {
      if (pitem?.category?.title) return pitem.category.title;
      const found = this.categories.find((c) => c.id === catId);
      return found ? found.title : "General";
    },
    getItemTitle(pitem) {
      if (pitem?.item?.title) return pitem.item.title;
      if (pitem?.item_title) return pitem.item_title;
      if (pitem?.items && pitem?.item_id) {
        const found = pitem.items.find((i) => i.id === pitem.item_id);
        if (found) return found.title;
      }
      return pitem.item_id ? `Product #${pitem.item_id}` : "Unknown Product";
    },
    getItemBarcode(pitem) {
      if (pitem?.item?.barcode) return pitem.item.barcode;
      if (pitem?.barcode) return pitem.barcode;
      if (pitem?.items && pitem?.item_id) {
        const found = pitem.items.find((i) => i.id === pitem.item_id);
        if (found && found.barcode) return found.barcode;
      }
      return "";
    },
    getColorTitle(colorId, pitem = null) {
      if (pitem?.color?.title) return pitem.color.title;
      const found = this.colors.find((c) => c.id === colorId);
      return found ? found.title : null;
    },
    getSizeTitle(sizeId, pitem = null) {
      if (pitem?.size?.title) return pitem.size.title;
      const found = this.sizes.find((s) => s.id === sizeId);
      return found ? found.title : null;
    },
    getUnitTitle(unitId, pitem = null) {
      if (pitem?.unit?.title) return pitem.unit.title;
      if (pitem?.unit_title) return pitem.unit_title;
      const found = this.units.find((u) => u.id === unitId);
      return found ? found.title : "Pcs";
    },
    getSerialCount(serialStr) {
      if (!serialStr) return 0;
      return serialStr
        .split(",")
        .map((s) => s.trim())
        .filter((s) => s.length > 0).length;
    },
    getSerialsArray(serialStr) {
      if (!serialStr) return [];
      return serialStr
        .split(",")
        .map((s) => s.trim())
        .filter((s) => s.length > 0);
    },
    openAddProductModal() {
      this.isEditingModal = false;
      this.modalForm = {
        rowIndex: null,
        category_id: this.categories.length === 1 ? this.categories[0].id : null,
        item_id: null,
        item_title: "",
        items: [],
        color_id: null,
        size_id: null,
        unit_id: null,
        unit_title: "",
        price: 0,
        selling_price: 0,
        qty: 1,
        serial_no: "",
        serialsList: [],
        total_amount: 0,
        tempSerial: "",
        bulkSerialText: "",
        bulkMode: false,
        showSerialsSection: this.isElectronicsShop,
        syncQtyWithSerials: true,
      };

      if (this.modalForm.category_id) {
        this.onModalCategoryChange();
      }

      this.showProductModal = true;
    },
    openEditProductModal(index, pitem) {
      this.isEditingModal = true;
      const serials = this.getSerialsArray(pitem.serial_no);
      const price = parseFloat(pitem.price) || 0;
      const sellingPrice = parseFloat(pitem.selling_price) || 0;
      const qty = parseFloat(pitem.qty) || 1;
      const totalAmount = parseFloat(pitem.total_amount) || Number((price * qty).toFixed(2));

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
        price: price,
        selling_price: sellingPrice,
        qty: qty,
        serial_no: pitem.serial_no || "",
        serialsList: [...serials],
        total_amount: totalAmount,
        tempSerial: "",
        bulkSerialText: "",
        bulkMode: false,
        showSerialsSection: this.isElectronicsShop || serials.length > 0,
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
      this.modalForm.unit_title = "";

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
      if (!selectedObj || typeof selectedObj !== "object") {
        selectedObj = this.modalForm.items.find((i) => i.id === this.modalForm.item_id);
      }
      if (selectedObj) {
        this.modalForm.item_id = selectedObj.id;
        this.modalForm.item_title = selectedObj.title;
        this.modalForm.unit_id = selectedObj.unit_id || null;
        this.modalForm.unit_title = selectedObj.unit?.title || this.getUnitTitle(selectedObj.unit_id);
        if (selectedObj.opening_rate && (!this.modalForm.price || this.modalForm.price == 0)) {
          this.modalForm.price = parseFloat(selectedObj.opening_rate) || 0;
        }
        if (selectedObj.is_serialized || selectedObj.warranty_type !== "none") {
          this.modalForm.showSerialsSection = true;
        }
        this.onModalPriceOrQtyChange();
      }
    },
    onModalPriceOrQtyChange() {
      const price = parseFloat(this.modalForm.price) || 0;
      const qty = parseFloat(this.modalForm.qty) || 0;
      this.modalForm.total_amount = Number((price * qty).toFixed(2));
    },
    addModalSerial() {
      const sn = this.modalForm.tempSerial ? this.modalForm.tempSerial.trim() : "";
      if (sn) {
        if (!this.modalForm.serialsList.includes(sn)) {
          this.modalForm.serialsList.push(sn);
          if (this.modalForm.syncQtyWithSerials) {
            this.modalForm.qty = this.modalForm.serialsList.length;
            this.onModalPriceOrQtyChange();
          }
        } else {
          this.$toast("Serial number already exists in list", "warning");
        }
        this.modalForm.tempSerial = "";
      }
    },
    processModalBulkSerials() {
      if (!this.modalForm.bulkSerialText) return;
      const rawList = this.modalForm.bulkSerialText
        .split(/[\n,;\s]+/)
        .map((s) => s.trim())
        .filter((s) => s.length > 0);

      let added = 0;
      rawList.forEach((sn) => {
        if (sn && !this.modalForm.serialsList.includes(sn)) {
          this.modalForm.serialsList.push(sn);
          added++;
        }
      });

      if (this.modalForm.syncQtyWithSerials && this.modalForm.serialsList.length > 0) {
        this.modalForm.qty = this.modalForm.serialsList.length;
        this.onModalPriceOrQtyChange();
      }

      this.modalForm.bulkSerialText = "";
      this.modalForm.bulkMode = false;
      this.$toast(`${added} serial numbers added`, "success");
    },
    removeModalSerial(sIdx) {
      this.modalForm.serialsList.splice(sIdx, 1);
      if (this.modalForm.syncQtyWithSerials) {
        this.modalForm.qty = Math.max(1, this.modalForm.serialsList.length);
        this.onModalPriceOrQtyChange();
      }
    },
    clearAllModalSerials() {
      this.modalForm.serialsList = [];
    },
    copyAllModalSerials() {
      if (this.modalForm.serialsList.length === 0) return;
      const text = this.modalForm.serialsList.join(", ");
      navigator.clipboard.writeText(text).then(() => {
        this.$toast("Serials copied to clipboard", "success");
      });
    },
    saveProductFromModal() {
      if (!this.modalForm.category_id) {
        this.$toast("Please select Category (ক্যাটাগরি নির্বাচন করুন)", "warning");
        return;
      }
      if (!this.modalForm.item_id) {
        this.$toast("Please select Product / Item (পণ্য নির্বাচন করুন)", "warning");
        return;
      }
      if (!this.modalForm.qty || parseFloat(this.modalForm.qty) <= 0) {
        this.$toast("Quantity must be greater than 0 (পরিমাণ অন্তত ১ হতে হবে)", "warning");
        return;
      }

      const selectedItem = this.modalForm.items.find((i) => i.id === this.modalForm.item_id);
      const serialStr = this.modalForm.serialsList.join(", ");
      const price = parseFloat(this.modalForm.price) || 0;
      const sellingPrice = parseFloat(this.modalForm.selling_price) || 0;
      const qty = parseFloat(this.modalForm.qty) || 1;
      const totalAmount = Number((price * qty).toFixed(2));

      const newRow = {
        category_id: this.modalForm.category_id,
        item_id: this.modalForm.item_id,
        item: selectedItem || { id: this.modalForm.item_id, title: this.modalForm.item_title },
        items: this.modalForm.items,
        color_id: this.modalForm.color_id || null,
        size_id: this.modalForm.size_id || null,
        unit_id: this.modalForm.unit_id || (selectedItem ? selectedItem.unit_id : null),
        unit_title: this.modalForm.unit_title || (selectedItem?.unit?.title || "Pcs"),
        price: price,
        selling_price: sellingPrice,
        qty: qty,
        serial_no: serialStr,
        total_amount: totalAmount,
      };

      if (!this.data.purchase_details) {
        this.data.purchase_details = [];
      }

      if (this.modalForm.rowIndex !== null && this.modalForm.rowIndex >= 0) {
        this.data.purchase_details.splice(this.modalForm.rowIndex, 1, newRow);
        this.$toast("Product updated successfully (পণ্য সফলভাবে আপডেট হয়েছে)", "success");
      } else {
        this.data.purchase_details.push(newRow);
        this.$toast("Product added to purchase list (পণ্য তালিকায় যুক্ত হয়েছে)", "success");
      }

      this.calculateTotals();
      this.closeProductModal();
    },
    removePurchaseDetails(index) {
      if (this.data.purchase_details) {
        this.data.purchase_details.splice(index, 1);
        this.calculateTotals();
        this.$toast("Product removed from list", "info");
      }
    },
    calculateTotals() {
      if (!this.data.purchase_details) return;
      let totalAmt = 0;
      this.data.purchase_details.forEach((detail) => {
        const price = parseFloat(detail.price) || 0;
        const qty = parseFloat(detail.qty) || 0;
        detail.total_amount = Number((price * qty).toFixed(2));
        totalAmt += detail.total_amount;
      });
      this.data.amount = Number(totalAmt.toFixed(2));
      const discount = parseFloat(this.data.discount) || 0;
      const tax = parseFloat(this.data.tax) || 0;
      this.data.total_amount = Number((totalAmt - discount + tax).toFixed(2));
    },
    getCategories() {
      let module = "Item";
      axios.get(`getcategories/${module}`).then((response) => {
        this.categories = response.data || [];
      });
    },
    getUnits() {
      let module = "Item";
      axios.get(`getunits/${module}`).then((response) => {
        this.units = response.data || [];
      });
    },
    getColorsAndSizes() {
      axios.get("color?allData=true").then((res) => {
        this.colors = res.data || [];
      });
      axios.get("size?allData=true").then((res) => {
        this.sizes = res.data || [];
      });
    },
    submit() {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          this.$toast("You need to fill " + error + " more empty mandatory fields", "warning");
          return false;
        }

        // Validate purchase details
        if (!this.data.purchase_details || this.data.purchase_details.length === 0) {
          this.$toast("Please add at least one purchase item (কমপক্ষে একটি পণ্য যোগ করুন)", "warning");
          return false;
        }

        for (let i = 0; i < this.data.purchase_details.length; i++) {
          const itm = this.data.purchase_details[i];
          if (!itm.item_id) {
            this.$toast(`Please select product for Row #${i + 1}`, "warning");
            return false;
          }
          if (!itm.qty || itm.qty <= 0) {
            this.$toast(`Quantity must be greater than 0 for Row #${i + 1}`, "warning");
            return false;
          }
        }

        if (res) {
          if (this.data.id && (this.data.can_edit === false || this.data.grns_count > 0 || (this.data.receive_status && this.data.receive_status !== 'Pending'))) {
            this.$toast("This Purchase Order has already been received via GRN and cannot be edited.", "error");
            return false;
          }
          var form = document.getElementById("form");
          var formData = new FormData(form);
          formData.append("supplier_id", this.data.supplier_id);
          formData.append("purchase_date", this.data.purchase_date);
          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },
    getGeneratedInvoiceNo(force = false) {
      if (this.$route.params.id && !force) return;
      axios
        .get("purchase/generate-invoiceno")
        .then((response) => {
          if (response.data && (!this.data.invoiceno || force)) {
            this.data.invoiceno = response.data;
            if (force) {
              this.$toast("Generated new invoice/bill no: " + response.data, "success");
            }
          }
        })
        .catch(() => {
          if (!this.data.invoiceno || force) {
            const today = new Date();
            const ymd =
              today.getFullYear() +
              String(today.getMonth() + 1).padStart(2, "0") +
              String(today.getDate()).padStart(2, "0");
            this.data.invoiceno = `PUR-${ymd}-0001`;
          }
        });
    },
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + " Edit";
      this.get_data(`${this.model}/${this.$route.params.id}`).then((res) => {
        if (res && res.data) {
          const p = res.data;
          if (p.can_edit === false || p.grns_count > 0 || (p.grns && p.grns.length > 0) || (p.receive_status && p.receive_status !== 'Pending')) {
            this.$toast("This Purchase Order has already been received via GRN and cannot be edited.", "error");
            this.$router.replace({ name: 'purchase.show', params: { id: this.$route.params.id } });
          }
        }
      });
    } else {
      this.page_title = this.headline(this.model) + " Create";
      this.getGeneratedInvoiceNo();
    }

    this.getCategories();
    this.getUnits();
    this.getColorsAndSizes();
  },

  validators: {
    "data.supplier_id": function (value = null) {
      return Validator.value(value).required("Supplier is required");
    },
    "data.purchase_date": function (value = null) {
      return Validator.value(value).required("Purchase Date is required");
    },
  },
};
</script>

<style scoped>
.purchase-create-wrapper {
  font-family: inherit;
  overflow: visible !important;
}

/* Theme Utilities */
.theme-bg {
  background-color: #112C47 !important;
}

.text-theme {
  color: #112C47 !important;
}

.theme-bg-soft {
  background-color: rgba(17, 44, 71, 0.08) !important;
}

/* Form Cards & Overflows */
.form-card {
  border-radius: 10px;
  background-color: #ffffff;
  border: 1px solid #e9edf4 !important;
  transition: box-shadow 0.2s ease;
  overflow: visible !important;
  position: relative;
}

.form-card.top-info-card {
  z-index: 30;
}

.kpi-row {
  position: relative;
  z-index: 20;
}

.form-card.items-table-card {
  z-index: 25;
}

.form-card.calculation-card {
  z-index: 10;
}

.section-icon {
  width: 38px;
  height: 38px;
  min-width: 38px;
  font-size: 16px;
}

/* Stat Cards */
.stat-card {
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #e9edf4 !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(17, 44, 71, 0.08) !important;
}

.stat-icon {
  width: 38px;
  height: 38px;
  min-width: 38px;
  font-size: 15px;
}

.total-card-highlight {
  background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%) !important;
}

/* Table Styling */
.custom-table-container {
  overflow-x: auto;
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

.custom-items-table {
  width: 100%;
}

.custom-items-table tbody tr td {
  padding: 12px 10px;
  vertical-align: middle;
  border-color: #f1f5f9;
}

.item-row:hover {
  background-color: rgba(17, 44, 71, 0.02) !important;
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
  transition: all 0.15s ease;
}

.btn-xs {
  padding: 3px 8px !important;
  font-size: 11px !important;
  border-radius: 6px !important;
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

/* Empty State */
.empty-state-wrapper {
  padding: 20px;
}

.empty-icon {
  width: 60px;
  height: 60px;
}

/* Calculation & Summary Panel */
.grand-total-box {
  background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%);
  box-shadow: 0 4px 12px rgba(17, 44, 71, 0.15);
}

/* Product Modal Styling */
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

/* Global vs dropdown styling */
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

/* Custom Scrollbars */
.serials-list-box::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}

.serials-list-box::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}
</style>