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
                placeholder="e.g. INV-2026-001"
                :req="false"
              />
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
              <span class="small text-muted">Select products, set pricing, quantities and serial numbers</span>
            </div>
            <span class="badge theme-bg text-white rounded-pill px-2 py-1 ms-2 font-monospace">
              {{ data.purchase_details ? data.purchase_details.length : 0 }} Rows
            </span>
          </div>

          <!-- Add Item Row Button (Top Right) -->
          <button
            type="button"
            class="btn btn-sm btn-primary d-flex align-items-center gap-2 px-3 fw-semibold shadow-sm"
            @click.prevent="addPurchaseDetailsRow()"
          >
            <i class="fas fa-plus-circle"></i> Add Product Row
          </button>
        </div>

        <div class="card-body p-0 overflow-visible">
          <div class="custom-table-container">
            <table class="table custom-items-table table-hover align-middle mb-0">
              <thead class="theme-table-header text-center">
                <tr>
                  <th style="width: 3%;">#</th>
                  <th style="width: 15%;">Category</th>
                  <th style="width: 18%;">Item / Product</th>
                  <th style="width: 9%;">Color</th>
                  <th style="width: 9%;">Size</th>
                  <th style="width: 8%;">Unit</th>
                  <th style="width: 9%;">Purchase Price</th>
                  <th style="width: 9%;">Selling Price</th>
                  <th style="width: 7%;">Qty</th>
                  <th style="width: 10%;" v-if="isElectronicsShop">Serial Nos</th>
                  <th style="width: 9%;">Total</th>
                  <th style="width: 3%;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(pitem, index) in data.purchase_details" :key="index" class="item-row">
                  <!-- Serial Number -->
                  <td class="text-center fw-bold text-muted font-monospace">{{ index + 1 }}</td>

                  <!-- Category with appendToBody -->
                  <td>
                    <div class="table-vselect-wrapper">
                      <v-select
                        v-model="pitem.category_id"
                        label="title"
                        :reduce="(obj) => obj.id"
                        :options="categories"
                        placeholder="-- Category --"
                        :closeOnSelect="true"
                        :appendToBody="true"
                        @update:modelValue="onCategoryChange(pitem)"
                      />
                    </div>
                  </td>

                  <!-- Item Selection with appendToBody -->
                  <td>
                    <div class="table-vselect-wrapper">
                      <v-select
                        v-model="pitem.item_id"
                        label="title"
                        :reduce="(obj) => obj.id"
                        :options="pitem.items || []"
                        placeholder="-- Select Product --"
                        :closeOnSelect="true"
                        :appendToBody="true"
                        :disabled="!pitem.category_id"
                      />
                    </div>
                  </td>

                  <!-- Color Variant -->
                  <td>
                    <select class="form-select form-select-sm table-compact-input" v-model="pitem.color_id">
                      <option :value="null">-- Standard --</option>
                      <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </select>
                  </td>

                  <!-- Size Variant -->
                  <td>
                    <select class="form-select form-select-sm table-compact-input" v-model="pitem.size_id">
                      <option :value="null">-- Standard --</option>
                      <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                    </select>
                  </td>

                  <!-- Unit -->
                  <td>
                    <select class="form-select form-select-sm table-compact-input" v-model="pitem.unit_id">
                      <option :value="null">-- Unit --</option>
                      <option v-for="u in units" :key="u.id" :value="u.id">{{ u.title }}</option>
                    </select>
                  </td>

                  <!-- Purchase Price -->
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted px-1 border-end-0">৳</span>
                      <input
                        type="number"
                        step="any"
                        min="0"
                        class="form-control form-control-sm text-end font-monospace table-compact-input"
                        placeholder="0.00"
                        v-model.number="pitem.price"
                        @input="onRowValueChange(pitem)"
                      />
                    </div>
                  </td>

                  <!-- Selling Price -->
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light text-muted px-1 border-end-0">৳</span>
                      <input
                        type="number"
                        step="any"
                        min="0"
                        class="form-control form-control-sm text-end font-monospace table-compact-input"
                        placeholder="0.00"
                        v-model.number="pitem.selling_price"
                      />
                    </div>
                  </td>

                  <!-- Quantity -->
                  <td>
                    <input
                      type="number"
                      step="any"
                      min="0"
                      class="form-control form-control-sm text-center fw-bold font-monospace table-compact-input"
                      placeholder="0"
                      v-model.number="pitem.qty"
                      @input="onRowValueChange(pitem)"
                    />
                  </td>

                  <!-- Serial Numbers (for Electronics) -->
                  <td class="text-center" v-if="isElectronicsShop">
                    <button
                      type="button"
                      class="btn btn-xs w-100 d-flex align-items-center justify-content-center gap-1 serial-btn"
                      :class="getSerialCount(pitem.serial_no) > 0 ? 'btn-outline-primary fw-bold active-serial' : 'btn-outline-secondary'"
                      @click="openSerialModal(index, pitem)"
                      :title="pitem.serial_no || 'Manage Serial Numbers'"
                    >
                      <i class="fas fa-barcode"></i>
                      <span v-if="getSerialCount(pitem.serial_no) > 0">
                        {{ getSerialCount(pitem.serial_no) }} S/N
                      </span>
                      <span v-else>+ Serials</span>
                    </button>
                  </td>

                  <!-- Row Total Amount -->
                  <td class="text-end font-monospace fw-bold text-theme pe-3">
                    {{ formatCurrency(pitem.total_amount) }}
                  </td>

                  <!-- Action Buttons -->
                  <td class="text-center">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger btn-action-delete"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      data-bs-title="Remove Row"
                      v-x-tooltip
                      @click.prevent="removePurchaseDetails(index)"
                      :disabled="data.purchase_details && data.purchase_details.length <= 1"
                      title="Remove Row"
                    >
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </td>
                </tr>

                <tr v-if="!data.purchase_details || data.purchase_details.length === 0">
                  <td :colspan="isElectronicsShop ? 12 : 11" class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                    <p class="mb-0">No purchase items added yet. Click "+ Add Product Row" to begin.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Bottom Add Row Bar -->
          <div class="p-3 bg-light border-top text-center">
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm px-4 fw-bold dashed-btn"
              @click.prevent="addPurchaseDetailsRow()"
            >
              <i class="fas fa-plus me-1 text-primary"></i> Add Another Product Row (পণ্য যোগ করুন)
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

    <!-- 🏷️ Enhanced Multiple Serial Number Entry Modal -->
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
                <span class="badge bg-secondary">Qty: {{ activeRowItem.qty || 0 }}</span>
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
                  @click="copyAllModalSerials"
                  :disabled="modalSerials.length === 0"
                  title="Copy all serials to clipboard"
                >
                  <i class="fas fa-copy me-1"></i> Copy All
                </button>
                <button
                  type="button"
                  class="btn btn-xs btn-outline-danger"
                  @click="clearAllModalSerials"
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

            <!-- Serial Numbers Chips Container -->
            <div class="serials-list-box p-3 border rounded bg-light">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted text-uppercase">
                  Serial Numbers List ({{ modalSerials.length }})
                </span>
              </div>

              <div v-if="modalSerials.length > 0" class="d-flex flex-wrap gap-2 serial-chips-wrap">
                <span
                  v-for="(sn, sIdx) in modalSerials"
                  :key="sIdx"
                  class="badge serial-badge font-monospace p-2 d-flex align-items-center gap-2 shadow-sm"
                >
                  <span class="badge-num">#{{ sIdx + 1 }}</span>
                  <span class="badge-text">{{ sn }}</span>
                  <i
                    class="fas fa-times delete-serial-btn"
                    @click="removeSerial(sIdx)"
                    title="Remove Serial"
                  ></i>
                </span>
              </div>
              <div v-else class="text-center py-4 text-muted">
                <i class="fas fa-barcode fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                <p class="mb-0 small">No serial numbers entered yet. Scan or type above.</p>
              </div>
            </div>

            <!-- Modal Footer Controls -->
            <div class="d-flex justify-content-between align-items-center mt-3 p-2 bg-light rounded border">
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="syncQtyCheck"
                  v-model="syncQtyWithSerials"
                />
                <label class="form-check-label small fw-bold text-dark cursor-pointer" for="syncQtyCheck">
                  Auto update Row Quantity to {{ modalSerials.length }}
                </label>
              </div>
              <span class="text-muted small">
                Total: <strong class="text-theme font-monospace fs-6">{{ modalSerials.length }}</strong> Serials
              </span>
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
const model = "purchase";

export default {
  computed: {
    isElectronicsShop() {
      const shopType = this.$root.site?.shop_type;
      return !shopType || shopType === "electronics";
    },
    totalItemsCount() {
      return this.data.purchase_details ? this.data.purchase_details.length : 0;
    },
    totalQuantity() {
      if (!this.data.purchase_details) return 0;
      return this.data.purchase_details.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
    },
    activeRowItem() {
      if (this.activeRowIndex !== null && this.data.purchase_details) {
        return this.data.purchase_details[this.activeRowIndex];
      }
      return null;
    }
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
        purchase_details: [
          {
            category_id: null,
            item_id: null,
            color_id: null,
            size_id: null,
            unit_id: null,
            items: [],
            price: 0,
            selling_price: 0,
            qty: 1,
            serial_no: "",
            total_amount: 0,
          }
        ],
      },
      categories: [],
      units: [],
      items: [],
      colors: [],
      sizes: [],

      // Serial Modal State
      showSerialModal: false,
      activeRowIndex: null,
      tempSerial: "",
      bulkSerialText: "",
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

  watch: {
    "data.purchase_details": {
      handler() {
        this.calculateRowAmount();
        this.calculateTotals();
      },
      deep: true
    },
    "data.discount": function () {
      this.calculateTotals();
    },
    "data.tax": function () {
      this.calculateTotals();
    }
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
    getSelectedProductTitle(item) {
      if (!item) return "Product";
      if (item.items && item.item_id) {
        const found = item.items.find((i) => i.id === item.item_id);
        if (found) return found.title;
      }
      return item.item_id ? `Product #${item.item_id}` : "No product selected";
    },
    getSerialCount(serialStr) {
      if (!serialStr) return 0;
      return serialStr
        .split(",")
        .map((s) => s.trim())
        .filter((s) => s.length > 0).length;
    },
    openSerialModal(index, pitem) {
      this.activeRowIndex = index;
      this.tempSerial = "";
      this.bulkSerialText = "";
      this.bulkEntryMode = false;
      if (pitem.serial_no) {
        this.modalSerials = pitem.serial_no
          .split(",")
          .map((s) => s.trim())
          .filter((s) => s.length > 0);
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
      this.tempSerial = "";
      this.bulkSerialText = "";
    },
    addSerialFromInput() {
      const sn = this.tempSerial ? this.tempSerial.trim() : "";
      if (sn) {
        if (!this.modalSerials.includes(sn)) {
          this.modalSerials.push(sn);
        } else {
          this.$toast("Serial number already exists in list", "warning");
        }
        this.tempSerial = "";
      }
    },
    processBulkSerials() {
      if (!this.bulkSerialText) return;
      const rawList = this.bulkSerialText
        .split(/[\n,;\s]+/)
        .map((s) => s.trim())
        .filter((s) => s.length > 0);

      let addedCount = 0;
      rawList.forEach((sn) => {
        if (sn && !this.modalSerials.includes(sn)) {
          this.modalSerials.push(sn);
          addedCount++;
        }
      });

      this.bulkSerialText = "";
      this.bulkEntryMode = false;
      this.$toast(`${addedCount} serial numbers added`, "success");
    },
    removeSerial(index) {
      this.modalSerials.splice(index, 1);
    },
    clearAllModalSerials() {
      this.modalSerials = [];
    },
    copyAllModalSerials() {
      if (this.modalSerials.length === 0) return;
      const text = this.modalSerials.join(", ");
      navigator.clipboard.writeText(text).then(() => {
        this.$toast("Serials copied to clipboard", "success");
      });
    },
    saveSerialsFromModal() {
      if (this.activeRowIndex !== null && this.data.purchase_details[this.activeRowIndex]) {
        const serialStr = this.modalSerials.join(", ");
        this.data.purchase_details[this.activeRowIndex].serial_no = serialStr;
        if (this.syncQtyWithSerials && this.modalSerials.length > 0) {
          this.data.purchase_details[this.activeRowIndex].qty = this.modalSerials.length;
        }
      }
      this.closeSerialModal();
      this.$toast("Serial numbers updated", "success");
    },
    onRowValueChange(pitem) {
      const price = parseFloat(pitem.price) || 0;
      const qty = parseFloat(pitem.qty) || 0;
      pitem.total_amount = Number((price * qty).toFixed(2));
      this.calculateTotals();
    },
    calculateRowAmount() {
      if (!this.data.purchase_details) return;
      this.data.purchase_details.forEach((detail) => {
        const price = parseFloat(detail.price) || 0;
        const qty = parseFloat(detail.qty) || 0;
        detail.total_amount = Number((price * qty).toFixed(2));
      });
    },
    calculateTotals() {
      if (!this.data.purchase_details) return;
      let totalAmt = 0;
      this.data.purchase_details.forEach((detail) => {
        totalAmt += parseFloat(detail.total_amount) || 0;
      });
      this.data.amount = Number(totalAmt.toFixed(2));
      const discount = parseFloat(this.data.discount) || 0;
      const tax = parseFloat(this.data.tax) || 0;
      this.data.total_amount = Number((totalAmt - discount + tax).toFixed(2));
    },
    addPurchaseDetailsRow() {
      if (!this.data.purchase_details) {
        this.data.purchase_details = [];
      }
      this.data.purchase_details.push({
        category_id: null,
        items: [],
        item_id: null,
        color_id: null,
        size_id: null,
        unit_id: null,
        price: 0,
        selling_price: 0,
        qty: 1,
        serial_no: "",
        total_amount: 0,
      });
    },
    removePurchaseDetails(index) {
      if (this.data.purchase_details && this.data.purchase_details.length > 1) {
        this.data.purchase_details.splice(index, 1);
        this.calculateTotals();
      }
    },
    onCategoryChange(pitem) {
      pitem.item_id = null;
      pitem.items = [];

      if (!pitem.category_id) {
        return;
      }

      axios
        .get(`getitemsbycategory/${pitem.category_id}`)
        .then((response) => {
          pitem.items = response.data || [];
        })
        .catch(() => {
          pitem.items = [];
        });
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
          this.$toast("Please add at least one purchase item", "warning");
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
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + " Edit";
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + " Create";
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
  overflow: visible !important;
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
  overflow: visible !important;
}

.custom-items-table tbody tr {
  position: relative;
}

.custom-items-table tbody tr td {
  padding: 8px 6px;
  vertical-align: middle;
  border-color: #f1f5f9;
  overflow: visible !important;
}

.item-row:hover {
  background-color: rgba(17, 44, 71, 0.02) !important;
}

/* Compact Table Form Controls */
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

.table-vselect-wrapper {
  position: relative;
  overflow: visible !important;
}

.table-vselect-wrapper :deep(.v-select) {
  background-color: #ffffff;
  overflow: visible !important;
}

.table-vselect-wrapper :deep(.vs__dropdown-toggle) {
  min-height: 36px !important;
  height: 36px !important;
  padding: 0 4px !important;
  border-radius: 6px !important;
  border-color: #cbd5e1 !important;
  font-size: 13px !important;
}

.table-vselect-wrapper :deep(.vs__selected) {
  margin: 2px 0 0 0 !important;
  padding: 0 2px !important;
  font-size: 13px !important;
  color: #1e293b !important;
}

.table-vselect-wrapper :deep(.vs__search) {
  margin: 2px 0 0 0 !important;
  padding: 0 4px !important;
  font-size: 13px !important;
}

.table-vselect-wrapper :deep(.vs__actions) {
  padding: 0 4px !important;
}

.btn-action-delete {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  font-size: 13px;
}

.btn-xs {
  padding: 4px 8px !important;
  font-size: 11px !important;
  border-radius: 6px !important;
}

.serial-btn {
  height: 34px;
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

/* Calculation & Summary Panel */
.grand-total-box {
  background: linear-gradient(135deg, #112C47 0%, #1e3a5f 100%);
  box-shadow: 0 4px 12px rgba(17, 44, 71, 0.15);
}

/* Serial Modal Chips */
.serials-list-box {
  min-height: 140px;
  max-height: 240px;
  overflow-y: auto;
}

.serial-badge {
  background-color: #112C47;
  color: #ffffff;
  font-size: 13px;
  border-radius: 6px;
}

.badge-num {
  background-color: rgba(255, 255, 255, 0.2);
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 11px;
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
  width: 6px;
  height: 6px;
}

.serials-list-box::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}
</style>