<template>
  <create-form @onSubmit="submit">
    <!-- 🔒 Invoiced & Locked Banner Alert -->
    <div class="col-12 mb-2" v-if="isLocked">
      <div class="alert alert-warning border border-warning shadow-sm rounded-3 d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-warning bg-opacity-25 text-warning p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <i class="fas fa-lock fs-5"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-0 text-dark">{{ $t('Challan is Locked (Invoiced)') }}</h6>
            <small class="text-muted">
              {{ $t('An official invoice has already been issued for this challan. This record is locked in read-only mode.') }}
            </small>
          </div>
        </div>
        <router-link
          :to="{ name: 'invoice.show', params: { id: (data.invoice_id || (data.invoice && data.invoice.id)) } }"
          class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1.5 font-monospace px-3 py-1.5"
        >
          <i class="fas fa-file-invoice"></i>
          <span>{{ $t('View Invoice') }}</span>
          <span v-if="data.invoice && data.invoice.invoice_no" class="text-info font-monospace ms-1">
            (#{{ data.invoice.invoice_no }})
          </span>
          <i class="fas fa-arrow-right ms-1"></i>
        </router-link>
      </div>
    </div>

    <!-- Left Column: Primary Form Inputs (8 Columns) -->
    <div class="col-lg-8 col-12">
      <div class="row g-3">
        <!-- Card 1: Challan Primary Info (overflow visible to prevent dropdown clipping) -->
        <div class="col-12">
          <div class="card shadow-sm border-0 rounded-3 overflow-visible" style="overflow: visible;">
            <div class="card-header bg-light border-bottom px-3 py-2.5 d-flex align-items-center justify-content-between">
              <span class="fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-file-invoice text-primary"></i>
                <span>{{ $t('Challan Information') }}</span>
              </span>
              <span class="badge bg-primary bg-opacity-10 text-primary font-monospace small">
                {{ data.challan_no ? ('#' + data.challan_no) : $t('New Dispatch') }}
              </span>
            </div>
            <div class="card-body p-3 overflow-visible" style="overflow: visible;">
              <div class="row g-3">
                <Select
                  title="Client / Organization"
                  v-model="data.client_id"
                  field="data.client_id"
                  label="name"
                  :reduce="(obj) => obj.id"
                  :options="clients"
                  :placeholder="$t('-- Select Client --')"
                  :closeOnSelect="true"
                  :appendToBody="true"
                  :required="true"
                  col="6"
                  @change="onClientChange"
                  :disabled="isLocked"
                />

                <date-picker
                  id="date2"
                  v-model="data.challan_date"
                  field="data.challan_date"
                  title="Challan Date"
                  placeholder="Challan Date"
                  col="3"
                  :req="true"
                  :disabled="isLocked"
                ></date-picker>

                <div class="col-md-3">
                  <label class="form-label small fw-semibold text-muted mb-1">{{ $t('Status') }}</label>
                  <Switch
                    v-model="data.status"
                    field="data.status"
                    title=""
                    on-label="Active"
                    off-label="Deactive"
                    :req="true"
                    col="12"
                    :disabled="isLocked"
                  />
                </div>

                <Input
                  v-model="data.delivery_location"
                  field="data.delivery_location"
                  title="Delivery Destination / Shipping Address"
                  :req="false"
                  col="12"
                  placeholder="Enter full address or warehouse delivery location"
                  :disabled="isLocked"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Handover Logistics (Receiver & Delivered By) -->
        <div class="col-12">
          <div class="card shadow-sm border-0 rounded-3 overflow-visible" style="overflow: visible;">
            <div class="card-header bg-light border-bottom px-3 py-2.5">
              <span class="fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-people-carry-box text-primary"></i>
                <span>{{ $t('Handover & Logistics Information') }}</span>
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row g-3">
                <!-- Receiver Details Block -->
                <div class="col-md-7 border-end-md pe-md-3">
                  <div class="d-flex align-items-center gap-1.5 mb-2 text-primary fw-semibold small text-uppercase">
                    <i class="fas fa-user-check"></i>
                    <span>{{ $t('Consignee / Receiver Info') }}</span>
                  </div>
                  <div class="row g-2">
                    <Input
                      v-model="data.receive_by"
                      field="data.receive_by"
                      title="Receiver Name"
                      :req="false"
                      col="12"
                      placeholder="Full name of receiver"
                      :disabled="isLocked"
                    />
                    <Input
                      v-model="data.receive_by_designation"
                      field="data.receive_by_designation"
                      title="Designation"
                      :req="false"
                      col="6"
                      placeholder="e.g. Store Officer"
                      :disabled="isLocked"
                    />
                    <Input
                      v-model="data.receive_by_mobile"
                      field="data.receive_by_mobile"
                      title="Contact Number"
                      :req="false"
                      col="6"
                      placeholder="017xxxxxxxx"
                      :disabled="isLocked"
                    />
                  </div>
                </div>

                <!-- Delivered By Block -->
                <div class="col-md-5 ps-md-3">
                  <div class="d-flex align-items-center gap-1.5 mb-2 text-primary fw-semibold small text-uppercase">
                    <i class="fas fa-truck-fast"></i>
                    <span>{{ $t('Delivered By (Courier/Driver)') }}</span>
                  </div>
                  <div class="row g-2">
                    <Input
                      v-model="data.delivery_by"
                      field="data.delivery_by"
                      title="Courier / Personnel"
                      :req="false"
                      col="12"
                      placeholder="Driver / Delivery Person"
                      :disabled="isLocked"
                    />
                    <Input
                      v-model="data.delivery_by_mobile"
                      field="data.delivery_by_mobile"
                      title="Courier Phone"
                      :req="false"
                      col="12"
                      placeholder="Mobile number"
                      :disabled="isLocked"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Live Client Consignee Profile Card (4 Columns) -->
    <div class="col-lg-4 col-12">
      <div class="card shadow-sm border-0 rounded-3 h-100 overflow-hidden">
        <div class="card-header bg-light border-bottom px-3 py-2.5 d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-building-user text-primary"></i>
            <span>{{ $t('Consignee Profile') }}</span>
          </span>
          <span v-if="selectedClient" class="badge bg-success bg-opacity-10 text-success font-monospace">
            {{ $t('Client Selected') }}
          </span>
        </div>
        <div class="card-body p-3">
          <div v-if="selectedClient">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
              <div class="client-avatar rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px;">
                <i class="fas fa-building"></i>
              </div>
              <div class="overflow-hidden">
                <div class="fw-bold text-dark text-truncate" style="font-size: 15px;">
                  {{ selectedClient.org_name || selectedClient.name }}
                </div>
                <div class="small text-muted text-truncate" v-if="selectedClient.org_name && selectedClient.name !== selectedClient.org_name">
                  <i class="fas fa-user me-1 text-secondary"></i>{{ selectedClient.name }}
                </div>
              </div>
            </div>

            <table class="table table-sm table-borderless mb-0 client-meta-table">
              <tbody>
                <tr v-if="selectedClient.mobile">
                  <td class="text-secondary fw-semibold py-1.5" width="35%">{{ $t('Mobile') }}:</td>
                  <td class="text-end font-monospace text-dark py-1.5">
                    <a :href="'tel:' + selectedClient.mobile" class="text-decoration-none text-dark fw-bold">
                      <i class="fas fa-phone-alt text-success me-1"></i>{{ selectedClient.mobile }}
                    </a>
                  </td>
                </tr>
                <tr v-if="selectedClient.email">
                  <td class="text-secondary fw-semibold py-1.5">{{ $t('Email') }}:</td>
                  <td class="text-end text-dark small py-1.5 text-truncate" style="max-width: 170px;">
                    <a :href="'mailto:' + selectedClient.email" class="text-decoration-none text-muted">
                      {{ selectedClient.email }}
                    </a>
                  </td>
                </tr>
                <tr v-if="selectedClient.address">
                  <td class="text-secondary fw-semibold py-1.5">{{ $t('Address') }}:</td>
                  <td class="text-end text-dark small py-1.5">
                    <i class="fas fa-map-pin text-danger me-1"></i>{{ selectedClient.address }}
                  </td>
                </tr>
                <tr v-if="selectedClient.current_due !== undefined">
                  <td class="text-secondary fw-semibold py-1.5">{{ $t('Current Due') }}:</td>
                  <td class="text-end font-monospace py-1.5 fw-bold" :class="Number(selectedClient.current_due) > 0 ? 'text-danger' : 'text-success'">
                    {{ Number(selectedClient.current_due || 0).toLocaleString() }} Tk
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty Placeholder if no client selected -->
          <div v-else class="text-center py-5 text-muted">
            <div class="mb-2">
              <i class="fas fa-user-plus fa-3x text-secondary opacity-50"></i>
            </div>
            <div class="fw-semibold text-dark">{{ $t('No Client Selected') }}</div>
            <small class="text-muted d-block mt-1">
              {{ $t('Select a Client from the dropdown to automatically attach destination address and consignee contact details.') }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- 📦 Challan Line Items Card (Purchase-Create Style with Dedicated Category Column & Modal) -->
    <div class="col-12 mt-2">
      <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="section-icon theme-bg-soft text-primary rounded d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 16px;">
              <i class="fas fa-boxes-stacked"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0 text-dark">{{ $t('Challan Items & Quantities') }}</h6>
              <span class="small text-muted">{{ $t('Manage dispatched products, categories, specifications and quantities') }}</span>
            </div>
            <span class="badge theme-bg text-white rounded-pill px-2.5 py-1 ms-2 font-monospace" style="background-color: #112C47;">
              {{ data.challan_details ? data.challan_details.length : 0 }} {{ $t('Items') }}
            </span>
          </div>

          <!-- Header Metric Badges & Add Product Button -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary bg-opacity-10 text-primary font-monospace py-1.5 px-2">
              {{ $t('Total Order Qty') }}: <strong>{{ totalOrderQty }}</strong>
            </span>
            <span class="badge bg-success bg-opacity-10 text-success font-monospace py-1.5 px-2" v-if="$route.params.id">
              {{ $t('Total Received Qty') }}: <strong>{{ totalReceiveQty }}</strong>
            </span>
            <button
              type="button"
              class="btn btn-sm btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm"
              @click.prevent="openAddProductModal"
              :disabled="isLocked"
            >
              <i class="fas fa-plus-circle"></i> {{ $t('Add Product Row') }}
            </button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive mb-0">
            <table class="table table-hover align-middle mb-0 challan-items-table">
              <thead class="text-uppercase small" style="background-color: #112C47; color: #ffffff;">
                <tr>
                  <th style="width: 4%;" class="text-center">#</th>
                  <th style="width: 16%;" class="text-start ps-3">{{ $t('Category') }}</th>
                  <th style="width: 24%;" class="text-start">{{ $t('Catalogue Item') }}</th>
                  <th style="width: 24%;" class="text-start">{{ $t('Description & Specifications') }}</th>
                  <th style="width: 10%;" class="text-center">{{ $t('Order Qty') }}</th>
                  <th style="width: 10%;" class="text-center">{{ $t('Receive Qty') }}</th>
                  <th style="width: 12%;" class="text-start">{{ $t('Remarks / Notes') }}</th>
                  <th style="width: 10%;" class="text-center">{{ $t('Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.challan_details" :key="index" class="challan-item-row">
                  <!-- Row Index -->
                  <td class="text-center font-monospace text-muted fw-bold">{{ index + 1 }}</td>

                  <!-- 2. Category Column (Before Catalogue Item) -->
                  <td class="text-start ps-3">
                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace small">
                      <i class="fas fa-tag me-1 text-primary"></i>
                      {{ getCategoryTitle(item.category_id, item) }}
                    </span>
                  </td>

                  <!-- 3. Catalogue Item -->
                  <td class="text-start">
                    <div class="d-flex flex-column">
                      <span class="fw-bold text-dark fs-6">{{ getItemTitle(item) }}</span>
                      <small class="text-muted font-monospace" v-if="getItemBarcode(item)">
                        <i class="fas fa-barcode me-1"></i>Barcode: {{ getItemBarcode(item) }}
                      </small>
                    </div>
                  </td>

                  <!-- 4. Description & Specifications -->
                  <td class="text-start">
                    <div class="small text-muted" style="white-space: pre-line;">
                      {{ item.description || '-' }}
                    </div>
                  </td>

                  <!-- 5. Order / Dispatched Qty -->
                  <td class="text-center font-monospace fw-bold text-dark fs-6">
                    {{ item.qty }}
                    <span class="badge bg-light text-primary border font-monospace ms-1 small" style="font-size: 11px;">
                      {{ getUnitTitle(item) }}
                    </span>
                  </td>

                  <!-- 6. Receive Qty -->
                  <td class="text-center font-monospace fw-bold text-success fs-6">
                    <span v-if="Number(item.receive_qty || 0) > 0" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 font-monospace">
                      {{ item.receive_qty }}
                    </span>
                    <span v-else class="text-muted small">0.00</span>
                  </td>

                  <!-- 7. Remarks -->
                  <td class="text-start small text-muted">
                    {{ item.remark || '-' }}
                  </td>

                  <!-- 8. Actions (Edit via Modal & Delete) -->
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary p-1 px-2 rounded"
                        @click="openEditProductModal(index, item)"
                        :disabled="isLocked"
                        :title="$t('Edit Product')"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger p-1 px-2 rounded"
                        @click="removeChallanDetails(index)"
                        :disabled="isLocked"
                        :title="$t('Remove Product')"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Empty State (Purchase Create Style) -->
                <tr v-if="!data.challan_details || data.challan_details.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted bg-white">
                    <div class="empty-state-wrapper py-3">
                      <div class="empty-icon text-primary bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 54px; height: 54px; font-size: 22px;">
                        <i class="fas fa-cart-plus"></i>
                      </div>
                      <h6 class="fw-bold text-dark mb-1">{{ $t('No challan items added yet') }}</h6>
                      <p class="text-muted small mb-3">{{ $t('Click "+ Add Product Row" button to select category, product and dispatch quantities.') }}</p>
                      <button
                        type="button"
                        class="btn btn-primary btn-sm px-4 fw-bold shadow-sm"
                        @click="openAddProductModal"
                        :disabled="isLocked"
                      >
                        <i class="fas fa-plus-circle me-1"></i>{{ $t('Add First Product') }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>

              <!-- Table Summary Footer -->
              <tfoot class="bg-light fw-bold" v-if="data.challan_details && data.challan_details.length > 0">
                <tr>
                  <td colspan="4" class="text-end text-uppercase small text-secondary py-2">{{ $t('Total Quantities') }}:</td>
                  <td class="text-center font-monospace text-primary py-2 fs-6">{{ totalOrderQty }}</td>
                  <td class="text-center font-monospace text-success py-2 fs-6">{{ totalReceiveQty }}</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Bottom Add Row Bar (Purchase Create Style) -->
          <div class="p-3 bg-light border-top text-center" v-if="data.challan_details && data.challan_details.length > 0 && !isLocked">
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm px-4 fw-bold dashed-btn"
              @click.prevent="openAddProductModal"
            >
              <i class="fas fa-plus me-1 text-primary"></i>{{ $t('Add Another Product Row') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 🏷️ Professional Product Addition Popup Modal (Purchase Create Style) -->
    <div
      v-if="showProductModal"
      class="modal fade show d-block product-modal-backdrop"
      tabindex="-1"
      style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(3px); z-index: 1055;"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl border-0 rounded-3 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header text-white py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #112C47;">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-white bg-opacity-20 rounded p-2 text-white">
                <i class="fas" :class="isEditingModal ? 'fa-edit fs-5' : 'fa-cart-plus fs-5'"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold fs-6 mb-0 text-white">
                  {{ isEditingModal ? $t('Edit Challan Product (পণ্য সংশোধন)') : $t('Add Challan Product (পণ্য যোগ করুন)') }}
                </h5>
                <span class="small text-white-50">{{ $t('Select category, choose item, enter quantity and specifications') }}</span>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" @click="closeProductModal"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 bg-white">
            <div class="row g-3">
              <!-- 1. Category Selection (Requirement 2: Category select korle oi category er item gulo asbe) -->
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark mb-1">
                  {{ $t('Category') }} <span class="text-danger">*</span>
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

              <!-- 2. Product / Item Selection (Filtered by Category) -->
              <div class="col-md-6">
                <label class="form-label small fw-bold text-dark mb-1">
                  {{ $t('Product / Item') }} <span class="text-danger">*</span>
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
                <small v-if="!modalForm.category_id" class="text-muted" style="font-size: 11px;">
                  <i class="fas fa-info-circle me-1"></i>{{ $t('Please select category first to load products') }}
                </small>
              </div>

              <!-- 3. Auto-detected Unit Badge -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">{{ $t('Unit') }}</label>
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

              <!-- 4. Order / Dispatched Quantity -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">
                  {{ $t('Order / Dispatched Qty') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                  <input
                    type="number"
                    step="any"
                    min="1"
                    class="form-control form-control-sm text-center font-monospace fw-bold fs-6"
                    placeholder="1"
                    v-model.number="modalForm.qty"
                  />
                  <span class="input-group-text bg-light text-muted">{{ modalForm.unit_title || 'Pcs' }}</span>
                </div>
              </div>

              <!-- 5. Received Quantity (Disabled/Readonly on Create, active on Edit) -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-dark mb-1">
                  {{ $t('Received Qty') }}
                </label>
                <div class="input-group input-group-sm">
                  <input
                    type="number"
                    step="any"
                    min="0"
                    class="form-control form-control-sm text-center font-monospace fw-bold fs-6"
                    placeholder="0.00"
                    v-model.number="modalForm.receive_qty"
                    :readonly="!$route.params.id"
                  />
                  <span class="input-group-text bg-light text-muted">{{ modalForm.unit_title || 'Pcs' }}</span>
                </div>
              </div>

              <!-- 6. Description & Specifications -->
              <div class="col-12">
                <label class="form-label small fw-bold text-dark mb-1">
                  {{ $t('Description & Specifications') }}
                </label>
                <textarea
                  class="form-control form-control-sm"
                  rows="2"
                  v-model="modalForm.description"
                  placeholder="Enter detailed description of materials, model specifications or notes..."
                ></textarea>
              </div>

              <!-- 7. Remarks / Box / Batch -->
              <div class="col-12">
                <label class="form-label small fw-bold text-dark mb-1">
                  {{ $t('Remarks / Batch / Box No') }}
                </label>
                <input
                  type="text"
                  class="form-control form-control-sm"
                  v-model="modalForm.remark"
                  placeholder="e.g. Box #1, Carton A, Serial numbers, etc."
                />
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer py-2 px-4 bg-light border-top d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary btn-sm px-3" @click="closeProductModal">
              {{ $t('Cancel') }}
            </button>
            <button type="button" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" @click="saveProductFromModal">
              <i class="fas fa-check me-1"></i> {{ isEditingModal ? $t('Update Product') : $t('Add to Challan List') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </create-form>
</template>

<script>
const model = "challan";

export default {
  name: "ChallanCreate",
  data() {
    return {
      model: model,
      page_title: "",
      data: {
        client_id: "",
        challan_date: this.$filter ? this.$filter.today() : "",
        delivery_location: "",
        status: "active",
        receive_by: "",
        receive_by_designation: "",
        receive_by_mobile: "",
        delivery_by: "",
        delivery_by_mobile: "",
        is_invoiced: 0,
        invoice_id: null,
        challan_details: [],
      },
      clients: [],
      items: [],
      categories: [],
      selectedClient: null,

      // Modal State for Product Selection (Purchase-create style)
      showProductModal: false,
      isEditingModal: false,
      modalForm: {
        rowIndex: null,
        category_id: null,
        item_id: null,
        item_title: "",
        items: [],
        unit_id: null,
        unit_title: "",
        qty: 1,
        receive_qty: 0,
        description: "",
        remark: "",
      },
    };
  },

  computed: {
    isLocked() {
      return Boolean(this.data.is_invoiced || this.data.invoice_id);
    },

    totalOrderQty() {
      if (!this.data.challan_details || !Array.isArray(this.data.challan_details)) return 0;
      return this.data.challan_details.reduce((sum, item) => sum + Number(item.qty || 0), 0);
    },

    totalReceiveQty() {
      if (!this.data.challan_details || !Array.isArray(this.data.challan_details)) return 0;
      return this.data.challan_details.reduce((sum, item) => sum + Number(item.receive_qty || 0), 0);
    },
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  methods: {
    getCategoryTitle(catId, item = null) {
      if (item?.category?.title) return item.category.title;
      if (item?.item?.category?.title) return item.item.category.title;
      const found = this.categories.find((c) => c.id === catId);
      return found ? found.title : "General";
    },

    getItemTitle(item) {
      if (item?.item?.title) return item.item.title;
      if (item?.item_title) return item.item_title;
      if (item?.item_id) {
        const found = this.items.find((i) => i.id === item.item_id);
        if (found) return found.title;
      }
      return item.item_id ? `Product #${item.item_id}` : "Unknown Product";
    },

    getItemBarcode(item) {
      if (item?.item?.barcode) return item.item.barcode;
      if (item?.item_id) {
        const found = this.items.find((i) => i.id === item.item_id);
        if (found && found.barcode) return found.barcode;
      }
      return "";
    },

    getUnitTitle(item) {
      if (item?.item?.unit?.title) return item.item.unit.title;
      if (item?.unit_title) return item.unit_title;
      return "Pcs";
    },

    openAddProductModal() {
      if (this.isLocked) return;
      this.isEditingModal = false;
      this.modalForm = {
        rowIndex: null,
        category_id: this.categories.length === 1 ? this.categories[0].id : null,
        item_id: null,
        item_title: "",
        items: [],
        unit_id: null,
        unit_title: "",
        qty: 1,
        receive_qty: 0,
        description: "",
        remark: "",
      };

      if (this.modalForm.category_id) {
        this.onModalCategoryChange();
      }

      this.showProductModal = true;
    },

    openEditProductModal(index, item) {
      if (this.isLocked) return;
      this.isEditingModal = true;
      const catId = item.category_id || (item.item ? item.item.category_id : null);

      this.modalForm = {
        rowIndex: index,
        category_id: catId,
        item_id: item.item_id,
        item_title: this.getItemTitle(item),
        items: item.items || [],
        unit_id: item.unit_id || (item.item ? item.item.unit_id : null),
        unit_title: this.getUnitTitle(item),
        qty: item.qty || 1,
        receive_qty: item.receive_qty || 0,
        description: item.description || "",
        remark: item.remark || "",
      };

      if (catId) {
        axios.get(`getitemsbycategory/${catId}`).then((res) => {
          this.modalForm.items = res.data || [];
          const found = this.modalForm.items.find((i) => i.id === item.item_id);
          if (found && !this.modalForm.unit_title) {
            this.modalForm.unit_id = found.unit_id;
            this.modalForm.unit_title = found.unit?.title || "Pcs";
          }
        });
      } else {
        this.modalForm.items = this.items || [];
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
      this.modalForm.description = "";

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
        this.modalForm.unit_title = selectedObj.unit?.title || "Pcs";

        if (!this.modalForm.description) {
          let desc = selectedObj.title;
          if (selectedObj.model_no) {
            desc += " (Model: " + selectedObj.model_no + ")";
          }
          this.modalForm.description = desc;
        }
      }
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
      const selectedCat = this.categories.find((c) => c.id === this.modalForm.category_id);

      const row = {
        id: (this.modalForm.rowIndex !== null && this.data.challan_details[this.modalForm.rowIndex]?.id) 
              ? this.data.challan_details[this.modalForm.rowIndex].id 
              : undefined,
        category_id: this.modalForm.category_id,
        category: selectedCat || { id: this.modalForm.category_id, title: this.getCategoryTitle(this.modalForm.category_id) },
        item_id: this.modalForm.item_id,
        item: selectedItem || { id: this.modalForm.item_id, title: this.modalForm.item_title },
        items: this.modalForm.items,
        unit_id: this.modalForm.unit_id,
        unit_title: this.modalForm.unit_title || (selectedItem?.unit?.title || "Pcs"),
        description: this.modalForm.description,
        qty: parseFloat(this.modalForm.qty) || 1,
        receive_qty: parseFloat(this.modalForm.receive_qty) || 0,
        remark: this.modalForm.remark || "",
      };

      if (!this.data.challan_details) {
        this.data.challan_details = [];
      }

      if (this.modalForm.rowIndex !== null && this.modalForm.rowIndex >= 0) {
        this.data.challan_details.splice(this.modalForm.rowIndex, 1, row);
        this.$toast("Product updated successfully (পণ্য সফলভাবে আপডেট হয়েছে)", "success");
      } else {
        this.data.challan_details.push(row);
        this.$toast("Product added to challan (পণ্য তালিকায় যোগ করা হয়েছে)", "success");
      }

      this.closeProductModal();
    },

    removeChallanDetails(index) {
      if (this.isLocked) return;
      if (this.data.challan_details && this.data.challan_details.length > 0) {
        this.data.challan_details.splice(index, 1);
      }
    },

    submit() {
      if (this.isLocked) {
        this.$toast("This challan has already been invoiced and cannot be modified.", "warning");
        return false;
      }

      if (!this.data.challan_details || this.data.challan_details.length === 0) {
        this.$toast("Please add at least one item to the challan (কমপক্ষে একটি পণ্য যোগ করুন)", "warning");
        return false;
      }

      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          this.$toast("You need to fill " + error + " more empty mandatory fields", "warning");
          return false;
        }

        if (res) {
          var form = document.getElementById("form");
          var formData = new FormData(form);
          formData.append("challan_date", this.data.challan_date);
          formData.append("challan_details", JSON.stringify(this.data.challan_details));

          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },

    getClients() {
      axios
        .get("client?allData=true")
        .then((response) => {
          this.clients = response.data;
          if (this.data.client_id) {
            this.onClientChange(this.data.client_id);
          }
        })
        .catch((error) => {
          console.error(error);
        });
    },

    getCategories() {
      axios
        .get("getcategories/Item")
        .then((response) => {
          this.categories = response.data || [];
        })
        .catch((error) => {
          console.error(error);
        });
    },

    getSalesItems() {
      axios
        .get("getsalesitems/")
        .then((response) => {
          this.items = response.data || [];
        })
        .catch((error) => {
          console.error(error);
        });
    },

    onClientChange(clientId) {
      if (!clientId) {
        this.selectedClient = null;
        return;
      }
      const found = this.clients.find((c) => c.id === clientId);
      if (found) {
        this.selectedClient = found;
        if (!this.data.delivery_location && found.address) {
          this.data.delivery_location = found.address;
        }
        if (!this.data.receive_by && found.name) {
          this.data.receive_by = found.name;
        }
        if (!this.data.receive_by_mobile && found.mobile) {
          this.data.receive_by_mobile = found.mobile;
        }
      }
    },

    fetchChallanData(id) {
      axios
        .get(`${this.model}/${id}`)
        .then((res) => {
          this.data = res.data;
          if (this.data.client_id) {
            this.onClientChange(this.data.client_id);
          }
        })
        .catch((err) => {
          console.error(err);
        });
    },
  },

  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + " Edit";
      this.fetchChallanData(this.$route.params.id);
    } else {
      this.page_title = this.headline(this.model) + " Create";
    }
    this.getClients();
    this.getCategories();
    this.getSalesItems();
  },

  validators: {
    "data.client_id": function (value = null) {
      return Validator.value(value).required("Client is required");
    },
    "data.challan_date": function (value = null) {
      return Validator.value(value).required("Challan Date is required");
    },
  },
};
</script>

<style scoped>
.client-meta-table td {
  border: none;
  font-size: 12px;
}

.challan-items-table th {
  padding: 8px 10px;
  font-size: 11px;
}

.challan-items-table td {
  padding: 8px 10px;
  vertical-align: middle;
}

.dashed-btn {
  border-style: dashed !important;
  border-width: 1.5px !important;
}

.product-modal-backdrop {
  display: block;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
}
</style>
