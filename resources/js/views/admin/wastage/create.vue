<template>
  <create-form @onSubmit='submit'>
    <div class="row g-3 mb-3">
      <Input v-model='data.audit_number' field='data.audit_number' col="3" title='Audit Number' placeholder='Auto Generated (e.g. WST-2026...)' :req='false' :readonly="true" />
      
      <date-picker id='date1' v-model='data.audit_date' field='data.audit_date' title='Audit Date'
        placeholder='Audit Date' col='3' :req='true'></date-picker>

      <Input v-model='data.audited_by' field='data.audited_by' col="3" title='Audited By (Team / Auditors)' placeholder='e.g. Audit Team A / John, Dave' :req='false' />

      <Select title='Lead Auditor (Employee)' v-model='data.auditor_id' field='data.auditor_id' label='full_name'
        :reduce='(obj) => obj.id' :options='$root.global.employees' col="3" placeholder='--Select Lead Auditor--'
        :closeOnSelect='true' :required='false' />

      <div class="col-md-12">
        <label class="form-label small fw-semibold">Audit Notes / General Remarks</label>
        <textarea class="form-control" rows="2" v-model="data.note" placeholder="Enter general audit description, location, or findings..."></textarea>
      </div>
    </div>

    <!-- Wastage Items Table Card -->
    <div class="col-md-12 mb-3">
      <div class="card shadow-sm border-0 bg-white">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <span class="fs-6 fw-bold text-danger">
              <i class="fas fa-boxes me-2"></i>Wastage / Damaged Items List (নষ্ট/ক্ষতিগ্রস্ত পণ্যের তালিকা)
            </span>
            <span class="badge bg-danger rounded-pill px-2 py-1">{{ data.wastage_details.length }} Items</span>
          </div>

          <!-- Add Item Button (Right Side) -->
          <button type="button" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm d-flex align-items-center gap-2" @click="openAddItemModal">
            <i class="fas fa-plus-circle"></i> Add Another Item
          </button>
        </div>

        <div class="card-body p-0">
          <!-- Items List Table -->
          <div class="table-responsive" v-if="data.wastage_details && data.wastage_details.length > 0">
            <table class="table table-bordered table-hover align-middle mb-0">
              <thead class="table-dark text-center" style="font-size: 13px;">
                <tr>
                  <th style="width: 4%;">#</th>
                  <th style="width: 24%;">Item / Product</th>
                  <th style="width: 11%;">Variant</th>
                  <th style="width: 14%;">Reason & Remarks</th>
                  <th style="width: 10%;">Expired Date</th>
                  <th style="width: 8%;">Stock</th>
                  <th style="width: 7%;">Qty</th>
                  <th style="width: 9%;">Unit Cost</th>
                  <th style="width: 9%;">Total Loss</th>
                  <th style="width: 8%;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(witem, index) in data.wastage_details" :key="index">
                  <td class="text-center fw-bold text-muted">{{ index + 1 }}</td>
                  
                  <!-- Item Info -->
                  <td>
                    <div class="fw-bold text-dark">{{ getItemTitle(witem) }}</div>
                    <div class="small text-muted d-flex align-items-center gap-2 mt-1 flex-wrap">
                      <span v-if="getCategoryTitle(witem)" class="badge bg-light text-dark border">
                        {{ getCategoryTitle(witem) }}
                      </span>
                      <span v-if="getItemBarcode(witem)" class="font-monospace text-secondary">
                        <i class="fas fa-barcode me-1"></i>{{ getItemBarcode(witem) }}
                      </span>
                    </div>
                    <div v-if="isElectronicsShop && witem.serial_no" class="small text-primary mt-1 font-monospace d-flex align-items-center flex-wrap gap-1">
                      <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                        <i class="fas fa-hashtag me-1"></i>S/N: {{ witem.serial_no }}
                      </span>
                    </div>
                  </td>

                  <!-- Variant -->
                  <td class="text-center">
                    <span class="badge bg-light text-dark border me-1" v-if="getColorTitle(witem)">
                      {{ getColorTitle(witem) }}
                    </span>
                    <span class="badge bg-light text-dark border" v-if="getSizeTitle(witem)">
                      {{ getSizeTitle(witem) }}
                    </span>
                    <span class="text-muted small" v-if="!getColorTitle(witem) && !getSizeTitle(witem)">
                      Standard
                    </span>
                  </td>

                  <!-- Reason -->
                  <td>
                    <span class="badge" :class="getReasonBadgeClass(witem.reason)">
                      {{ witem.reason || 'Damaged' }}
                    </span>
                    <div v-if="witem.remarks" class="small text-muted text-truncate mt-1" style="max-width: 180px;" :title="witem.remarks">
                      {{ witem.remarks }}
                    </div>
                  </td>

                  <!-- Expired Date -->
                  <td class="text-center">
                    <span v-if="witem.expired_date" class="badge bg-danger bg-opacity-10 text-danger fw-bold">
                      <i class="fas fa-calendar-times me-1"></i>{{ witem.expired_date }}
                    </span>
                    <span v-else class="text-muted small">-</span>
                  </td>

                  <!-- Stock -->
                  <td class="text-center">
                    <span class="badge" :class="(witem.available_stock > 0) ? 'bg-success' : 'bg-danger'" style="font-size: 11px;">
                      {{ witem.available_stock !== undefined ? witem.available_stock : '-' }}
                    </span>
                  </td>

                  <!-- Quantity -->
                  <td class="text-center fw-bold fs-6 text-dark">
                    {{ witem.quantity }}
                  </td>

                  <!-- Unit Cost -->
                  <td class="text-end font-monospace">
                    {{ $filter.formatBDT(witem.unit_price) }}
                  </td>

                  <!-- Total Amount -->
                  <td class="text-end font-monospace fw-bold text-danger">
                    {{ $filter.formatBDT(Number(witem.quantity || 0) * Number(witem.unit_price || 0)) }}
                  </td>

                  <!-- Action Buttons -->
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <!-- Edit -->
                      <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2" title="Edit Item" @click.prevent="openEditItemModal(index)">
                        <i class="fas fa-edit"></i>
                      </button>
                      <!-- View -->
                      <button type="button" class="btn btn-sm btn-outline-info p-1 px-2" title="View Item Details" @click.prevent="openViewItemModal(witem, index)">
                        <i class="fas fa-eye"></i>
                      </button>
                      <!-- Delete -->
                      <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" title="Remove Item" @click.prevent="promptDeleteItem(index)">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center p-5 bg-white">
            <div class="mb-3 text-muted">
              <i class="fas fa-box-open fa-3x opacity-50"></i>
            </div>
            <h6 class="text-muted fw-bold mb-1">No Wastage Items Added Yet</h6>
            <p class="text-muted small mb-3">Click on the button below to add damaged, expired, or wasted items with live stock verification.</p>
            <button type="button" class="btn btn-danger btn-sm px-4 fw-bold shadow-sm" @click="openAddItemModal">
              <i class="fas fa-plus me-1"></i> Add First Item
            </button>
          </div>
        </div>

        <!-- Summary Footer Banner -->
        <div class="card-footer bg-light p-3 border-top">
          <div class="row align-items-center g-2">
            <div class="col-md-4">
              <span class="text-muted fw-semibold">Total Item Lines:</span>
              <span class="fw-bold fs-6 text-dark ms-2">{{ data.wastage_details.length }}</span>
            </div>
            <div class="col-md-4 text-center">
              <span class="text-muted fw-semibold">Total Quantity:</span>
              <span class="fw-bold fs-5 text-warning ms-2">{{ totalQuantity }}</span>
            </div>
            <div class="col-md-4 text-end">
              <span class="text-muted fw-semibold">Total Estimated Loss:</span>
              <span class="fw-bold fs-5 text-danger ms-2 font-monospace">{{ $filter.formatBDT(totalLossAmount) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- 1. POPUP MODAL: ADD / EDIT WASTAGE ITEM                        -->
    <!-- ============================================================== -->
    <div v-if="showItemModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); z-index: 1060;" @click.self="closeItemModal">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <!-- Modal Header -->
          <div class="modal-header bg-danger text-white py-2">
            <h5 class="modal-title fw-bold fs-6">
              <i class="fas" :class="editingIndex !== null ? 'fa-edit' : 'fa-plus-circle'"></i>
              {{ editingIndex !== null ? ' Edit Wastage Item' : ' Add Wastage Item (আইটেম যুক্ত করুন)' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeItemModal" aria-label="Close"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-3">
            <div class="row g-3">
              <!-- Category Filter -->
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Category (Optional Filter)</label>
                <v-select v-model="modalItem.category_id" label="title" :reduce="(obj) => obj.id"
                  :options="categories" placeholder="-- Filter by Category --" :closeOnSelect="true"
                  @option:selected="onModalCategoryChange" @option:deselected="onModalCategoryDeselect"></v-select>
              </div>

              <!-- Item / Product (Required) -->
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Select Item / Product <span class="text-danger">*</span></label>
                <v-select v-model="modalItem.item_id" label="title" :reduce="(obj) => obj.id"
                  :options="filteredItems" placeholder="-- Select Item --" :closeOnSelect="true"
                  @option:selected="onModalItemSelect">
                  <template #option="option">
                    <div class="d-flex justify-content-between align-items-center">
                      <span>{{ option.title }}</span>
                      <small class="text-muted font-monospace" v-if="option.barcode">{{ option.barcode }}</small>
                    </div>
                  </template>
                </v-select>
              </div>

              <!-- Color Variant -->
              <div class="col-md-6">
                <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                  <span>Color Variant</span>
                  <span v-if="modalItem.color_id" class="badge bg-light text-muted border" style="font-size: 10px;">
                    Selected
                  </span>
                </label>
                <select class="form-select form-select-sm" v-model="modalItem.color_id" @change="onVariantChange">
                  <option :value="null">-- Standard (No Color) --</option>
                  <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>

              <!-- Size Variant -->
              <div class="col-md-6">
                <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                  <span>Size Variant</span>
                  <span v-if="modalItem.size_id" class="badge bg-light text-muted border" style="font-size: 10px;">
                    Selected
                  </span>
                </label>
                <select class="form-select form-select-sm" v-model="modalItem.size_id" @change="onVariantChange">
                  <option :value="null">-- Standard (No Size) --</option>
                  <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
              </div>

              <!-- Live Stock & Unit Price Status Banner -->
              <div class="col-12" v-if="modalItem.item_id">
                <div class="p-2 px-3 rounded border d-flex justify-content-between align-items-center flex-wrap gap-2"
                  :class="modalItem.available_stock > 0 ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger'">
                  <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-warehouse" :class="modalItem.available_stock > 0 ? 'text-success' : 'text-danger'"></i>
                    <span class="small fw-semibold text-dark">Live Available Stock:</span>
                    <span class="badge" :class="modalItem.available_stock > 0 ? 'bg-success' : 'bg-danger'">
                      {{ modalItem.available_stock }} Units
                    </span>
                    <span v-if="modalItem.available_stock <= 0" class="badge bg-danger text-white">
                      <i class="fas fa-ban me-1"></i>Out of Stock (স্টক ০ - এন্ট্রি বন্ধ)
                    </span>
                    <span v-if="isFetchingStock" class="spinner-border spinner-border-sm text-secondary ms-1"></span>
                  </div>
                  <div class="small text-muted">
                    <span>Base Unit Cost: <strong>{{ $filter.formatBDT(modalItem.unit_price) }}</strong></span>
                    <span v-if="isElectronicsShop" class="ms-2">
                      | Type: <span class="badge" :class="modalItem.has_purchased_serials ? 'bg-info text-dark' : 'bg-secondary'">
                        {{ modalItem.has_purchased_serials ? 'Serialized Product' : 'Non-Serialized' }}
                      </span>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Wastage Quantity -->
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Wastage Quantity <span class="text-danger">*</span></label>
                <input type="number" step="any" min="0.01"
                  :disabled="modalItem.available_stock <= 0"
                  class="form-control form-control-sm fw-bold text-center"
                  v-model="modalItem.quantity" placeholder="1" />
                <div v-if="modalItem.available_stock <= 0" class="text-danger small mt-1 fw-semibold">
                  <i class="fas fa-ban me-1"></i>Stock is 0. Cannot add wastage.
                </div>
                <div v-else-if="modalItem.available_stock > 0 && Number(modalItem.quantity) > Number(modalItem.available_stock)" class="text-danger small mt-1">
                  <i class="fas fa-exclamation-triangle me-1"></i>Exceeds current stock ({{ modalItem.available_stock }})
                </div>
              </div>

              <!-- Unit Cost Price -->
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Unit Cost / Purchase Price (৳) <span class="text-danger">*</span></label>
                <input type="number" step="any" min="0" class="form-control form-control-sm text-end font-monospace"
                  v-model="modalItem.unit_price" placeholder="0.00" />
              </div>

              <!-- Computed Total Loss -->
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Total Loss Value (৳)</label>
                <div class="form-control form-control-sm bg-light text-end font-monospace fw-bold text-danger">
                  {{ $filter.formatBDT(computedModalTotal) }}
                </div>
              </div>

              <!-- Wastage Reason -->
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Wastage Reason (ক্ষতির কারণ) <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" v-model="modalItem.reason">
                  <option value="Damaged in Storage">Damaged in Storage (স্টোরেজে ক্ষতিগ্রস্ত)</option>
                  <option value="Expired">Expired (মেয়াদোত্তীর্ণ)</option>
                  <option value="Broken / Shattered">Broken / Shattered (ভাঙা / নষ্ট)</option>
                  <option value="Spoiled / Rotten">Spoiled / Rotten (পচে গেছে / বিনষ্ট)</option>
                  <option value="Factory / Quality Defect">Factory / Quality Defect (ত্রুটিপূর্ণ)</option>
                  <option value="Lost / Theft">Lost / Theft (হারিয়ে গেছে / চুরি)</option>
                  <option value="Audit Shortage">Audit Shortage (অডিটে ঘাটতি)</option>
                  <option value="Packaging Defect">Packaging Defect (প্যাকেজিং নষ্ট)</option>
                  <option value="Other">Other (অন্যান্য)</option>
                </select>
              </div>

              <!-- Expired Date -->
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Expired Date (If Applicable)</label>
                <input type="date" class="form-control form-control-sm" v-model="modalItem.expired_date" />
              </div>

              <!-- ============================================================== -->
              <!-- SERIAL NUMBER TAG ENTRY SYSTEM (Electronics Shops Only)        -->
              <!-- ============================================================== -->
              <div class="col-12" v-if="isElectronicsShop">
                <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                  <span>
                    <i class="fas fa-barcode me-1 text-danger"></i>Serial Number Tag(s)
                    <span v-if="modalItem.has_purchased_serials" class="text-danger fw-bold">
                      * (Required: {{ modalItem.quantity }} Serials)
                    </span>
                    <span v-else class="text-muted fw-normal">
                      (Optional - Not Serialized in Purchase)
                    </span>
                  </span>
                  <span class="badge" :class="serialCountBadgeClass">
                    {{ serialTags.length }} / {{ modalItem.quantity }} Added
                  </span>
                </label>

                <!-- Tag Box Input Container -->
                <div class="border rounded bg-white p-2 min-h-tags d-flex flex-wrap align-items-center gap-1">
                  <!-- Rendered Serial Tags -->
                  <span v-for="(tag, tIdx) in serialTags" :key="tIdx"
                    class="badge bg-primary text-white d-inline-flex align-items-center gap-1 py-1 px-2 font-monospace shadow-sm"
                    style="font-size: 12px;">
                    <i class="fas fa-tag"></i> {{ tag }}
                    <i class="fas fa-times-circle ms-1 cursor-pointer"
                      style="cursor: pointer;"
                      title="Remove serial tag"
                      @click="removeSerialTag(tIdx)"></i>
                  </span>

                  <!-- Live Input Field Inside Tag Box -->
                  <input
                    ref="serialInputBox"
                    type="text"
                    :disabled="modalItem.available_stock <= 0"
                    class="form-control border-0 p-1 flex-grow-1 font-monospace shadow-none"
                    style="min-width: 220px; font-size: 13px;"
                    v-model="currentSerialInput"
                    placeholder="Type or Scan Serial & press Enter..."
                    @keydown.enter.prevent="addCurrentSerialTag"
                    @keydown.comma.prevent="addCurrentSerialTag"
                  />

                  <button type="button" class="btn btn-sm btn-primary py-0 px-2 fw-semibold"
                    @click="addCurrentSerialTag" :disabled="!currentSerialInput.trim() || isCheckingSerial || modalItem.available_stock <= 0">
                    <span v-if="isCheckingSerial" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-plus me-1"></i> Add Tag
                  </button>
                </div>

                <div class="small text-muted mt-1 d-flex justify-content-between">
                  <span><i class="fas fa-info-circle me-1"></i>Type serial and press <strong>Enter</strong> or comma (<strong>,</strong>) to validate & add tag.</span>
                  <span v-if="isCheckingSerial" class="text-primary fw-semibold"><i class="fas fa-spinner fa-spin me-1"></i>Verifying sales status...</span>
                </div>

                <!-- Available Serials Quick-Chips -->
                <div v-if="modalItem.available_serials && modalItem.available_serials.length > 0" class="mt-2 p-2 bg-light rounded border">
                  <div class="small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-tags me-1 text-success"></i>Available In-Stock Serials (Click to toggle tag):</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                      {{ modalItem.available_serials.length }} available
                    </span>
                  </div>
                  <div class="d-flex flex-wrap gap-1" style="max-height: 110px; overflow-y: auto;">
                    <span v-for="sn in modalItem.available_serials" :key="sn"
                      class="badge border px-2 py-1 cursor-pointer transition-all"
                      :class="serialTags.includes(sn) ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white text-dark hover-shadow'"
                      style="cursor: pointer; font-size: 11px;"
                      @click="toggleAvailableSerial(sn)">
                      <i class="fas" :class="serialTags.includes(sn) ? 'fa-check-circle me-1' : 'fa-plus me-1 text-secondary'"></i>{{ sn }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Remarks / Notes -->
              <div class="col-12">
                <label class="form-label small fw-semibold">Item Remarks / Details</label>
                <input type="text" class="form-control form-control-sm"
                  v-model="modalItem.remarks" placeholder="Add specific observation for this item..." />
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light py-2 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm px-3" @click="closeItemModal">
              <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button type="button" class="btn btn-danger btn-sm px-4 fw-bold shadow-sm"
              @click="saveModalItem" :disabled="modalItem.available_stock <= 0">
              <i class="fas" :class="editingIndex !== null ? 'fa-check' : 'fa-plus'"></i>
              {{ editingIndex !== null ? ' Save Changes' : ' Add to List' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. POPUP MODAL: VIEW ITEM DETAILS PREVIEW                     -->
    <!-- ============================================================== -->
    <div v-if="showViewModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); z-index: 1060;" @click.self="closeViewModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" v-if="viewingItem">
          <div class="modal-header bg-dark text-white py-2">
            <h5 class="modal-title fw-bold fs-6"><i class="fas fa-box-open me-2"></i>Wastage Item Preview</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeViewModal"></button>
          </div>
          <div class="modal-body p-3">
            <table class="table table-sm table-bordered align-middle mb-0">
              <tbody>
                <tr>
                  <th class="bg-light text-muted" style="width: 40%;">Item Title:</th>
                  <td class="fw-bold text-dark">{{ getItemTitle(viewingItem) }}</td>
                </tr>
                <tr v-if="getItemBarcode(viewingItem)">
                  <th class="bg-light text-muted">Barcode:</th>
                  <td class="font-monospace">{{ getItemBarcode(viewingItem) }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Category:</th>
                  <td>{{ getCategoryTitle(viewingItem) || 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Variant:</th>
                  <td>
                    <span v-if="getColorTitle(viewingItem)" class="badge bg-light text-dark border me-1">{{ getColorTitle(viewingItem) }}</span>
                    <span v-if="getSizeTitle(viewingItem)" class="badge bg-light text-dark border">{{ getSizeTitle(viewingItem) }}</span>
                    <span v-if="!getColorTitle(viewingItem) && !getSizeTitle(viewingItem)" class="text-muted">Standard</span>
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Stock at Audit:</th>
                  <td><span class="badge bg-success">{{ viewingItem.available_stock ?? '-' }} Units</span></td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Wasted Quantity:</th>
                  <td class="fw-bold fs-6 text-warning">{{ viewingItem.quantity }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Unit Cost Price:</th>
                  <td class="font-monospace">{{ $filter.formatBDT(viewingItem.unit_price) }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Total Loss Value:</th>
                  <td class="font-monospace fw-bold text-danger fs-6">
                    {{ $filter.formatBDT(Number(viewingItem.quantity || 0) * Number(viewingItem.unit_price || 0)) }}
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-muted">Wastage Reason:</th>
                  <td>
                    <span class="badge" :class="getReasonBadgeClass(viewingItem.reason)">
                      {{ viewingItem.reason || 'Damaged' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="viewingItem.expired_date">
                  <th class="bg-light text-muted">Expired Date:</th>
                  <td class="text-danger fw-bold"><i class="fas fa-calendar-times me-1"></i>{{ viewingItem.expired_date }}</td>
                </tr>
                <tr v-if="isElectronicsShop && viewingItem.serial_no">
                  <th class="bg-light text-muted">Serial Number(s):</th>
                  <td class="font-monospace text-primary fw-bold">{{ viewingItem.serial_no }}</td>
                </tr>
                <tr v-if="viewingItem.remarks">
                  <th class="bg-light text-muted">Remarks / Notes:</th>
                  <td>{{ viewingItem.remarks }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm px-4" @click="closeViewModal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- 3. POPUP MODAL: DELETE CONFIRMATION                            -->
    <!-- ============================================================== -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); z-index: 1060;" @click.self="closeDeleteModal">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 text-center p-3">
          <div class="text-danger mb-2">
            <i class="fas fa-exclamation-triangle fa-3x"></i>
          </div>
          <h6 class="fw-bold text-dark mb-1">Remove Item?</h6>
          <p class="text-muted small mb-3">Are you sure you want to remove this item from the wastage list?</p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-secondary btn-sm px-3" @click="closeDeleteModal">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" @click="executeDeleteItem">Yes, Remove</button>
          </div>
        </div>
      </div>
    </div>

  </create-form>
</template>

<script>
const model = 'wastage';

export default {
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        audit_number: '',
        audit_date: this.$filter.today(),
        audited_by: '',
        auditor_id: null,
        branch_id: null,
        note: '',
        status: 'pending',
        wastage_details: [],
      },
      categories: [],
      allItems: [],
      units: [],
      colors: [],
      sizes: [],

      // Modal State
      showItemModal: false,
      editingIndex: null,
      isFetchingStock: false,
      isCheckingSerial: false,
      currentSerialInput: '',
      serialTags: [],

      modalItem: {
        category_id: null,
        item_id: null,
        color_id: null,
        size_id: null,
        unit_id: null,
        available_stock: 0,
        quantity: 1,
        unit_price: 0,
        total_amount: 0,
        reason: 'Damaged in Storage',
        expired_date: null,
        serial_no: '',
        remarks: '',
        has_purchased_serials: false,
        available_serials: [],
        sold_serials: [],
        all_purchased_serials: [],
        item_variants: [],
      },

      // View Modal
      showViewModal: false,
      viewingItem: null,

      // Delete Modal
      showDeleteModal: false,
      deletingIndex: null,
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  computed: {
    isElectronicsShop() {
      const shopType = this.$root.site?.shop_type;
      return !shopType || shopType === 'electronics';
    },

    totalQuantity() {
      if (!this.data.wastage_details) return 0;
      return this.data.wastage_details.reduce((sum, row) => {
        return sum + Number(row.quantity || 0);
      }, 0).toFixed(2);
    },

    totalLossAmount() {
      if (!this.data.wastage_details) return 0;
      return this.data.wastage_details.reduce((sum, row) => {
        return sum + (Number(row.quantity || 0) * Number(row.unit_price || 0));
      }, 0).toFixed(2);
    },

    filteredItems() {
      if (this.modalItem.category_id) {
        return this.allItems.filter(it => it.category_id === this.modalItem.category_id);
      }
      return this.allItems;
    },

    computedModalTotal() {
      const q = Number(this.modalItem.quantity || 0);
      const p = Number(this.modalItem.unit_price || 0);
      return (q * p).toFixed(2);
    },

    serialCountBadgeClass() {
      if (!this.modalItem.has_purchased_serials) {
        return 'bg-secondary';
      }
      const entered = this.serialTags.length;
      const target = Number(this.modalItem.quantity || 0);
      if (entered === target && target > 0) return 'bg-success';
      if (entered > target) return 'bg-danger';
      return 'bg-warning text-dark';
    }
  },

  methods: {
    // -------------------------------------------------------------
    // Helper Formatters
    // -------------------------------------------------------------
    getItemTitle(witem) {
      if (witem.item && witem.item.title) return witem.item.title;
      const found = this.allItems.find(it => it.id === witem.item_id);
      return found ? found.title : (witem.item_title || `Item #${witem.item_id}`);
    },

    getItemBarcode(witem) {
      if (witem.item && witem.item.barcode) return witem.item.barcode;
      const found = this.allItems.find(it => it.id === witem.item_id);
      return found ? found.barcode : '';
    },

    getCategoryTitle(witem) {
      if (witem.category && witem.category.title) return witem.category.title;
      if (witem.category_id) {
        const found = this.categories.find(c => c.id === witem.category_id);
        if (found) return found.title;
      }
      const item = this.allItems.find(it => it.id === witem.item_id);
      if (item && item.category) return item.category.title;
      if (item && item.category_id) {
        const found = this.categories.find(c => c.id === item.category_id);
        if (found) return found.title;
      }
      return '';
    },

    getColorTitle(witem) {
      if (witem.color && witem.color.title) return witem.color.title;
      if (witem.color_id) {
        const found = this.colors.find(c => c.id === witem.color_id);
        if (found) return found.title;
      }
      return '';
    },

    getSizeTitle(witem) {
      if (witem.size && witem.size.title) return witem.size.title;
      if (witem.size_id) {
        const found = this.sizes.find(s => s.id === witem.size_id);
        if (found) return found.title;
      }
      return '';
    },

    getReasonBadgeClass(reason) {
      if (!reason) return 'bg-secondary';
      if (reason.includes('Expired')) return 'bg-danger';
      if (reason.includes('Damaged')) return 'bg-warning text-dark';
      if (reason.includes('Broken')) return 'bg-danger text-white';
      if (reason.includes('Spoiled')) return 'bg-dark text-white';
      if (reason.includes('Lost') || reason.includes('Theft') || reason.includes('Shortage')) return 'bg-secondary text-white';
      if (reason.includes('Quality')) return 'bg-info text-dark';
      return 'bg-primary';
    },

    // -------------------------------------------------------------
    // Modal Open / Close Handlers
    // -------------------------------------------------------------
    openAddItemModal() {
      this.editingIndex = null;
      this.currentSerialInput = '';
      this.serialTags = [];
      this.modalItem = {
        category_id: null,
        item_id: null,
        color_id: null,
        size_id: null,
        unit_id: null,
        available_stock: 0,
        quantity: 1,
        unit_price: 0,
        total_amount: 0,
        reason: 'Damaged in Storage',
        expired_date: null,
        serial_no: '',
        remarks: '',
        has_purchased_serials: false,
        available_serials: [],
        sold_serials: [],
        all_purchased_serials: [],
        item_variants: [],
      };
      this.showItemModal = true;
    },

    openEditItemModal(index) {
      this.editingIndex = index;
      this.currentSerialInput = '';
      const target = this.data.wastage_details[index];
      this.modalItem = JSON.parse(JSON.stringify(target));

      // Parse existing serials into tags
      if (this.modalItem.serial_no) {
        this.serialTags = this.modalItem.serial_no
          .split(/[\r\n,]+/)
          .map(s => s.trim())
          .filter(Boolean);
      } else {
        this.serialTags = [];
      }

      this.showItemModal = true;
      this.fetchVariantStockAndCost();
    },

    closeItemModal() {
      this.showItemModal = false;
      this.editingIndex = null;
      this.currentSerialInput = '';
      this.serialTags = [];
    },

    // View Modal
    openViewItemModal(item, index) {
      this.viewingItem = item;
      this.showViewModal = true;
    },

    closeViewModal() {
      this.showViewModal = false;
      this.viewingItem = null;
    },

    // Delete Modal
    promptDeleteItem(index) {
      this.deletingIndex = index;
      this.showDeleteModal = true;
    },

    closeDeleteModal() {
      this.showDeleteModal = false;
      this.deletingIndex = null;
    },

    executeDeleteItem() {
      if (this.deletingIndex !== null) {
        this.data.wastage_details.splice(this.deletingIndex, 1);
        this.$toast('Item removed from wastage list', 'info');
      }
      this.closeDeleteModal();
    },

    // -------------------------------------------------------------
    // Item / Variant / Stock Triggers in Modal
    // -------------------------------------------------------------
    onModalCategoryChange(selected) {
      if (this.modalItem.item_id) {
        const item = this.allItems.find(it => it.id === this.modalItem.item_id);
        if (item && item.category_id !== this.modalItem.category_id) {
          this.modalItem.item_id = null;
          this.modalItem.available_stock = 0;
          this.modalItem.unit_price = 0;
          this.serialTags = [];
          this.modalItem.has_purchased_serials = false;
          this.modalItem.color_id = null;
          this.modalItem.size_id = null;
        }
      }
    },

    onModalCategoryDeselect() {
      this.modalItem.category_id = null;
    },

    onModalItemSelect(selected) {
      this.serialTags = [];
      this.currentSerialInput = '';

      if (!selected) {
        this.modalItem.unit_price = 0;
        this.modalItem.available_stock = 0;
        this.modalItem.has_purchased_serials = false;
        this.modalItem.color_id = null;
        this.modalItem.size_id = null;
        return;
      }

      if (!this.modalItem.category_id && selected.category_id) {
        this.modalItem.category_id = selected.category_id;
      }
      this.modalItem.unit_id = selected.unit_id;
      this.modalItem.unit_price = selected.purchase_price || selected.price || 0;

      this.isFetchingStock = true;
      axios.get(`getitemstock/${this.modalItem.item_id}?details=1`)
        .then((res) => {
          this.isFetchingStock = false;
          if (typeof res.data === 'object' && res.data !== null) {
            // Auto-select the variant that has the maximum stock!
            this.modalItem.color_id = res.data.default_color_id !== undefined ? res.data.default_color_id : null;
            this.modalItem.size_id = res.data.default_size_id !== undefined ? res.data.default_size_id : null;
            this.modalItem.available_stock = Number(res.data.stock ?? res.data.default_stock ?? 0);
            if (res.data.unit_cost !== undefined && res.data.unit_cost > 0) {
              this.modalItem.unit_price = res.data.unit_cost;
            }
            this.modalItem.has_purchased_serials = res.data.has_purchased_serials || false;
            this.modalItem.available_serials = res.data.available_serials || [];
            this.modalItem.sold_serials = res.data.sold_serials || [];
            this.modalItem.all_purchased_serials = res.data.all_purchased_serials || [];
            this.modalItem.item_variants = res.data.variants || [];

            if (this.modalItem.available_stock <= 0) {
              this.modalItem.quantity = 0;
              this.$toast('⚠️ This item currently has 0 available stock. Wastage cannot be entered.', 'warning');
            } else {
              this.modalItem.quantity = 1;
            }
          }
        })
        .catch(() => {
          this.isFetchingStock = false;
          this.modalItem.available_stock = 0;
          this.modalItem.quantity = 0;
        });
    },

    onVariantChange() {
      if (!this.modalItem.item_id) return;

      this.isFetchingStock = true;
      let params = {
        details: 1,
        strict_variant: 1,
        color_id: this.modalItem.color_id !== null ? this.modalItem.color_id : '',
        size_id: this.modalItem.size_id !== null ? this.modalItem.size_id : ''
      };

      axios.get(`getitemstock/${this.modalItem.item_id}`, { params: params })
        .then((res) => {
          this.isFetchingStock = false;
          if (typeof res.data === 'object' && res.data !== null) {
            this.modalItem.available_stock = Number(res.data.stock ?? 0);
            if (res.data.unit_cost !== undefined && res.data.unit_cost > 0) {
              this.modalItem.unit_price = res.data.unit_cost;
            }
            this.modalItem.available_serials = res.data.available_serials || [];

            if (this.modalItem.available_stock <= 0) {
              this.modalItem.quantity = 0;
              this.$toast('⚠️ Selected variant has 0 stock. Wastage cannot be entered.', 'warning');
            } else if (Number(this.modalItem.quantity) <= 0 || Number(this.modalItem.quantity) > this.modalItem.available_stock) {
              this.modalItem.quantity = 1;
            }
          } else {
            this.modalItem.available_stock = Number(res.data ?? 0);
          }
        })
        .catch(() => {
          this.isFetchingStock = false;
          this.modalItem.available_stock = 0;
        });
    },

    fetchVariantStockAndCost() {
      if (!this.modalItem.item_id) return;

      this.isFetchingStock = true;
      let params = {
        details: 1,
        strict_variant: 1,
        color_id: this.modalItem.color_id !== null ? this.modalItem.color_id : '',
        size_id: this.modalItem.size_id !== null ? this.modalItem.size_id : ''
      };

      axios.get(`getitemstock/${this.modalItem.item_id}`, { params: params })
        .then((res) => {
          this.isFetchingStock = false;
          if (typeof res.data === 'object' && res.data !== null) {
            this.modalItem.available_stock = Number(res.data.stock ?? 0);
            if (res.data.unit_cost !== undefined && res.data.unit_cost > 0) {
              this.modalItem.unit_price = res.data.unit_cost;
            }
            this.modalItem.has_purchased_serials = res.data.has_purchased_serials || false;
            this.modalItem.available_serials = res.data.available_serials || [];
            this.modalItem.sold_serials = res.data.sold_serials || [];
            this.modalItem.all_purchased_serials = res.data.all_purchased_serials || [];
          } else {
            this.modalItem.available_stock = Number(res.data ?? 0);
          }
        })
        .catch(() => {
          this.isFetchingStock = false;
          this.modalItem.available_stock = 0;
        });
    },

    // -------------------------------------------------------------
    // Serial Number Tag System Handlers
    // -------------------------------------------------------------
    addCurrentSerialTag() {
      if (!this.currentSerialInput || !this.currentSerialInput.trim()) return;

      if (!this.modalItem.item_id) {
        this.$toast('Please select an item first before adding serial numbers', 'warning');
        return;
      }

      if (this.modalItem.available_stock <= 0) {
        this.$toast('❌ Stock is 0! Cannot add serials for out-of-stock items.', 'error');
        return;
      }

      const rawInputs = this.currentSerialInput.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean);
      this.currentSerialInput = '';

      rawInputs.forEach(sn => {
        // 1. Check duplicate in current tag list
        const alreadyInTags = this.serialTags.some(t => t.toLowerCase() === sn.toLowerCase());
        if (alreadyInTags) {
          this.$toast(`Serial "${sn}" is already in your tag list`, 'warning');
          return;
        }

        // 2. Check if sold (in sold_serials and active, i.e. not returned)
        const isSold = (this.modalItem.sold_serials || []).some(s => s.toLowerCase() === sn.toLowerCase());
        if (isSold) {
          this.$toast(`❌ Serial "${sn}" is currently SOLD (in an active invoice) and not returned! Cannot add to wastage.`, 'error');
          return;
        }

        // 3. If item is serialized in purchase, verify it exists in purchase records
        if (this.modalItem.has_purchased_serials) {
          const isPurchased = (this.modalItem.all_purchased_serials || []).some(s => s.toLowerCase() === sn.toLowerCase());
          if (!isPurchased) {
            this.$toast(`❌ Serial "${sn}" was not found in purchase records for this item!`, 'error');
            return;
          }
        }

        // 4. Server-Side Verification Check
        this.isCheckingSerial = true;
        axios.post('wastage/check-serial', {
          item_id: this.modalItem.item_id,
          serial_no: sn,
          wastage_id: this.data.id || null
        })
        .then(res => {
          this.isCheckingSerial = false;
          if (res.data.valid) {
            this.serialTags.push(sn);
            this.modalItem.serial_no = this.serialTags.join(', ');
            this.$toast(`Serial "${sn}" added`, 'success');
          } else {
            this.$toast(res.data.message || `Serial "${sn}" is invalid`, 'error');
          }
        })
        .catch(err => {
          this.isCheckingSerial = false;
          const msg = err.response?.data?.message || `Failed to verify serial "${sn}"`;
          this.$toast(msg, 'error');
        });
      });
    },

    removeSerialTag(index) {
      this.serialTags.splice(index, 1);
      this.modalItem.serial_no = this.serialTags.join(', ');
    },

    toggleAvailableSerial(sn) {
      if (this.modalItem.available_stock <= 0) {
        this.$toast('❌ Stock is 0! Cannot select serial.', 'error');
        return;
      }

      const idx = this.serialTags.findIndex(t => t.toLowerCase() === sn.toLowerCase());
      if (idx !== -1) {
        // Remove
        this.removeSerialTag(idx);
      } else {
        // Add
        this.serialTags.push(sn);
        this.modalItem.serial_no = this.serialTags.join(', ');
      }
    },

    // -------------------------------------------------------------
    // Save Modal Item to Table List
    // -------------------------------------------------------------
    saveModalItem() {
      if (!this.modalItem.item_id) {
        this.$toast('Please select an item / product', 'warning');
        return;
      }

      // Check: Stock must be greater than 0
      if (Number(this.modalItem.available_stock || 0) <= 0) {
        this.$toast('❌ Current stock is 0! Out-of-stock items cannot be added to wastage.', 'error');
        return;
      }

      if (!this.modalItem.quantity || Number(this.modalItem.quantity) <= 0) {
        this.$toast('Please enter a valid wastage quantity greater than 0', 'warning');
        return;
      }

      if (Number(this.modalItem.quantity) > Number(this.modalItem.available_stock)) {
        this.$toast(`❌ Wastage quantity (${this.modalItem.quantity}) cannot exceed current available stock (${this.modalItem.available_stock})!`, 'error');
        return;
      }

      if (this.modalItem.unit_price === null || this.modalItem.unit_price === '' || Number(this.modalItem.unit_price) < 0) {
        this.$toast('Please enter a valid unit cost price', 'warning');
        return;
      }

      // Serial Number validation for Electronics shop
      if (this.isElectronicsShop) {
        if (this.modalItem.has_purchased_serials) {
          if (this.serialTags.length === 0) {
            this.$toast('This item has purchase serial numbers. Serial number is mandatory before adding to list!', 'warning');
            return;
          }
          if (this.serialTags.length !== Number(this.modalItem.quantity)) {
            this.$toast(`Quantity is ${this.modalItem.quantity}, but you entered ${this.serialTags.length} serial numbers. Serial count must match quantity.`, 'warning');
            return;
          }
        }
        this.modalItem.serial_no = this.serialTags.join(', ');
      } else {
        this.modalItem.serial_no = '';
      }

      const itemPayload = {
        category_id: this.modalItem.category_id,
        item_id: this.modalItem.item_id,
        color_id: this.modalItem.color_id,
        size_id: this.modalItem.size_id,
        unit_id: this.modalItem.unit_id,
        available_stock: Number(this.modalItem.available_stock || 0),
        quantity: Number(this.modalItem.quantity),
        unit_price: Number(this.modalItem.unit_price || 0),
        total_amount: Number(this.computedModalTotal),
        reason: this.modalItem.reason || 'Damaged in Storage',
        expired_date: this.modalItem.expired_date || null,
        serial_no: this.modalItem.serial_no ? this.modalItem.serial_no.trim() : '',
        remarks: this.modalItem.remarks ? this.modalItem.remarks.trim() : '',
      };

      if (this.editingIndex !== null) {
        // Update existing item
        this.data.wastage_details.splice(this.editingIndex, 1, itemPayload);
        this.$toast('Wastage item updated successfully', 'success');
      } else {
        // Add new item
        this.data.wastage_details.push(itemPayload);
        this.$toast('Wastage item added to list', 'success');
      }

      this.closeItemModal();
    },

    // -------------------------------------------------------------
    // Initial Data Fetching
    // -------------------------------------------------------------
    getCategories() {
      axios.get('getcategories/Item')
        .then((response) => {
          this.categories = response.data;
        });
    },

    getAllItems() {
      axios.get('item?allData=true')
        .then((response) => {
          this.allItems = response.data;
        });
    },

    getUnits() {
      axios.get('getunits/Item')
        .then((response) => {
          this.units = response.data;
        });
    },

    getColorsAndSizes() {
      axios.get('color?allData=true').then(res => { this.colors = res.data; });
      axios.get('size?allData=true').then(res => { this.sizes = res.data; });
    },

    fetchRowStock(detail) {
      if (!detail.item_id) return;
      let params = {
        details: 1,
        strict_variant: 1,
        color_id: detail.color_id !== null ? detail.color_id : '',
        size_id: detail.size_id !== null ? detail.size_id : ''
      };

      axios.get(`getitemstock/${detail.item_id}`, { params: params })
        .then((res) => {
          if (typeof res.data === 'object' && res.data !== null) {
            detail.available_stock = Number(res.data.stock ?? 0);
          } else {
            detail.available_stock = Number(res.data ?? 0);
          }
        })
        .catch(() => {
          detail.available_stock = 0;
        });
    },

    // -------------------------------------------------------------
    // Form Submit
    // -------------------------------------------------------------
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

        if (!this.data.wastage_details || this.data.wastage_details.length === 0) {
          this.$toast('Please add at least one wastage item to the list before submitting', 'warning');
          return false;
        }

        if (res) {
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
      this.page_title = this.headline(this.model) + ' Edit';
      axios.get(`${this.model}/${this.$route.params.id}`)
        .then(response => {
          this.data = response.data;
          if (this.data.wastage_details && this.data.wastage_details.length) {
            this.data.wastage_details.forEach(detail => {
              this.fetchRowStock(detail);
            });
          }
        });
    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }

    this.getCategories();
    this.getAllItems();
    this.getUnits();
    this.getColorsAndSizes();
  },

  validators: {
    'data.audit_date': function (value = null) {
      return Validator.value(value).required('Audit Date is required');
    },
  },
};
</script>

<style scoped>
.min-h-tags {
  min-height: 44px;
}
.cursor-pointer {
  cursor: pointer;
}
.transition-all {
  transition: all 0.2s ease-in-out;
}
.hover-shadow:hover {
  background-color: #f0f4f8 !important;
  border-color: #0d6efd !important;
  color: #0d6efd !important;
}
</style>
