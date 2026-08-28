<template>
  <create-form @onSubmit='submit'>
    <date-picker id='date_grn' v-model='data.grn_date' field='data.grn_date' title='GRN Date'
      placeholder='GRN Date' col='2' :req='true'></date-picker>

    <Select title='Purchase Order (PO)' v-model='data.purchase_id' field='data.purchase_id' label='invoiceno'
      :reduce='(obj) => obj.id' :options='pendingPurchases' col="3" placeholder='--Select Purchase Order--'
      :closeOnSelect='true' :required='true' @change="onPurchaseChange" :readonly="$route.params.id ? true : false" />

    <Select title='Destination Warehouse' v-model='data.warehouse_id' field='data.warehouse_id' label='name'
      :reduce='(obj) => obj.id' :options='$root.global.warehouses' col="3" placeholder='--Select Warehouse--'
      :closeOnSelect='true' :required='true' />

    <Input v-model='selectedSupplierName' field='selectedSupplierName' col="4" title='Supplier' :readonly="true" :req='false' />

    <Input v-model='data.challan_no' col="2" field='data.challan_no' title='Supplier Challan No' :req='false' />
    <date-picker id='date_challan' v-model='data.challan_date' field='data.challan_date' title='Challan Date'
      placeholder='Challan Date' col='2' :req='false'></date-picker>
    <Input v-model='data.received_by' col="3" field='data.received_by' title='Received By (Staff Name)' :req='false' />
    <Input v-model='data.note' col="5" field='data.note' title='Remarks / Note' :req='false' />

    <div class="col-md-12 mb-3">
      <fieldset class="mt-3">
        <span class="legend"><i class="fas fa-boxes me-2"></i>Received Items (পণ্য গ্রহণের বিবরণী)</span>
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-dark text-center">
                  <tr>
                    <th style="width:18%">Item</th>
                    <th style="width:10%">Color</th>
                    <th style="width:10%">Size</th>
                    <th style="width:7%">Unit</th>
                    <th style="width:9%">Ordered</th>
                    <th style="width:9%">Prev. Recv</th>
                    <th style="width:9%">Remaining</th>
                    <th style="width:10%">Receive Qty</th>
                    <th style="width:8%" v-if="isElectronicsShop">Serials</th>
                    <th style="width:9%">Rate</th>
                    <th style="width:10%">Total (BDT)</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-if="data.grn_details && data.grn_details.length > 0">
                    <tr v-for="(pitem, index) in data.grn_details" :key="index" :class="{'table-light': pitem.remaining_qty <= 0}">
                      <td>
                        <strong>{{ pitem.item ? pitem.item.title : (pitem.item_id ? 'Item #' + pitem.item_id : 'N/A') }}</strong>
                        <div class="small text-muted" v-if="pitem.category">{{ pitem.category.title }}</div>
                      </td>
                      <td class="text-center">{{ pitem.color ? pitem.color.title : '-' }}</td>
                      <td class="text-center">{{ pitem.size ? pitem.size.title : '-' }}</td>
                      <td class="text-center">{{ pitem.unit ? pitem.unit.title : '-' }}</td>
                      <td class="text-center font-monospace">{{ pitem.ordered_qty }}</td>
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
                          class="form-control form-control-sm text-end fw-bold"
                          :class="{'border-danger text-danger': pitem.received_qty > pitem.remaining_qty}"
                          v-model.number="pitem.received_qty"
                          @input="onQtyChange(pitem)"
                        />
                        <div v-if="pitem.received_qty > pitem.remaining_qty" class="text-danger small mt-1">
                          Exceeds remaining!
                        </div>
                      </td>
                      <td class="text-center" v-if="isElectronicsShop">
                        <button type="button" class="btn btn-sm btn-outline-primary position-relative px-2 py-1" @click="openSerialModal(index, pitem)" title="Scan / Add Serial Numbers">
                          <i class="fas fa-barcode me-1"></i> Serials
                          <span class="badge bg-danger ms-1" v-if="getSerialCount(pitem.serial_no) > 0">{{ getSerialCount(pitem.serial_no) }}</span>
                        </button>
                      </td>
                      <td class="text-end font-monospace">{{ this.$filter.formatBDT(pitem.unit_price) }}</td>
                      <td class="text-end font-monospace fw-bold text-success">{{ this.$filter.formatBDT(pitem.total_amount) }}</td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr>
                      <td :colspan="isElectronicsShop ? 11 : 10" class="text-center py-4 text-muted">
                        <i class="fas fa-cart-arrow-down fa-2x mb-2 d-block opacity-50"></i>
                        Please select a Purchase Order above to load items.
                      </td>
                    </tr>
                  </template>
                </tbody>
                <tfoot class="table-light" v-if="data.grn_details && data.grn_details.length > 0">
                  <tr class="fw-bold">
                    <td colspan="7" class="text-end">Summary:</td>
                    <td class="text-end font-monospace text-primary fs-6">{{ data.total_qty }}</td>
                    <td v-if="isElectronicsShop"></td>
                    <td class="text-end">Total:</td>
                    <td class="text-end font-monospace text-success fs-6">{{ this.$filter.formatBDT(data.total_amount) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </fieldset>
    </div>

    <Switch v-model='data.status' field='data.status' title='Status' on-label='Active' off-label='Deactive' :req='true'>
    </Switch>

    <!-- Multiple Serial Number Entry Modal -->
    <div v-if="showSerialModal" class="modal fade show d-block tab-modal-backdrop" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark text-white py-2">
            <h5 class="modal-title fw-bold fs-6"><i class="fas fa-barcode me-2"></i>Multiple Serial Numbers Entry</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeSerialModal"></button>
          </div>
          <div class="modal-body p-3">
            <div class="alert alert-info py-2 small mb-3">
              <i class="fas fa-info-circle me-1"></i> Scan or enter serial numbers and press <strong>Enter</strong>.
            </div>
            
            <div class="input-group mb-3">
              <input ref="serialInput" type="text" class="form-control form-control-lg font-monospace fw-bold" placeholder="Enter / Scan Serial No..." v-model="tempSerial" @keyup.enter="addSerialFromInput">
              <button type="button" class="btn btn-primary fw-bold px-4" @click="addSerialFromInput">
                <i class="fas fa-plus me-1"></i> Add Serial
              </button>
            </div>

            <!-- Serial List Badges -->
            <div class="p-3 border rounded bg-light" style="min-height: 120px; max-height: 250px; overflow-y: auto;">
              <div v-if="modalSerials.length > 0" class="d-flex flex-wrap gap-2">
                <span v-for="(sn, sIdx) in modalSerials" :key="sIdx" class="badge bg-dark fs-6 font-monospace p-2 d-flex align-items-center gap-2">
                  <span>#{{ sIdx + 1 }}: {{ sn }}</span>
                  <i class="fas fa-times text-warning cursor-pointer" @click="removeSerial(sIdx)" title="Remove Serial"></i>
                </span>
              </div>
              <div v-else class="text-center py-4 text-muted">
                <i class="fas fa-barcode fa-2x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 small">No serial numbers added yet.</p>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
              <span class="fw-bold text-dark">Total Serials: <span class="text-primary font-monospace fs-5">{{ modalSerials.length }}</span></span>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="syncQtyCheck" v-model="syncQtyWithSerials">
                <label class="form-check-label small fw-bold" for="syncQtyCheck">Auto sync Received Qty to {{ modalSerials.length }}</label>
              </div>
            </div>
          </div>
          <div class="modal-footer py-2 bg-light">
            <button type="button" class="btn btn-secondary btn-sm" @click="closeSerialModal">Cancel</button>
            <button type="button" class="btn btn-success btn-sm px-4 fw-bold" @click="saveSerialsFromModal">
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
      const shopType = this.$root.site?.shop_type;
      return !shopType || shopType === 'electronics';
    },
  },
  data() {
    return {
      model: model,
      page_title: '',
      pendingPurchases: [],
      selectedSupplierName: '',
      data: {
        grn_date: this.$filter.today(),
        purchase_id: null,
        supplier_id: null,
        warehouse_id: null,
        challan_no: '',
        challan_date: '',
        received_by: '',
        note: '',
        total_qty: 0,
        total_amount: 0,
        status: 'active',
        grn_details: [],
      },

      // Serial modal state
      showSerialModal: false,
      activeRowIndex: null,
      tempSerial: '',
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
    getPendingPurchases() {
      axios.get('grn/pending-purchases')
        .then((res) => {
          this.pendingPurchases = res.data;
        })
        .catch((err) => {
          console.error(err);
        });
    },

    onPurchaseChange(purchase_id) {
      if (!purchase_id) {
        this.data.supplier_id = null;
        this.selectedSupplierName = '';
        this.data.grn_details = [];
        this.calculateTotals();
        return;
      }

      axios.get(`grn/get-purchase-items/${purchase_id}`)
        .then((res) => {
          this.data.supplier_id = res.data.purchase.supplier_id;
          this.selectedSupplierName = res.data.purchase.supplier?.org_name || '';
          this.data.grn_details = res.data.items;
          this.calculateTotals();
        })
        .catch((err) => {
          this.$toast('Failed to load items for this purchase order.', 'error');
        });
    },

    onQtyChange(item) {
      const qty = parseFloat(item.received_qty) || 0;
      const price = parseFloat(item.unit_price) || 0;
      item.total_amount = qty * price;
      this.calculateTotals();
    },

    calculateTotals() {
      let totalQty = 0;
      let totalAmount = 0;

      if (this.data.grn_details) {
        this.data.grn_details.forEach((item) => {
          const qty = parseFloat(item.received_qty) || 0;
          const price = parseFloat(item.unit_price) || 0;
          item.total_amount = qty * price;
          totalQty += qty;
          totalAmount += item.total_amount;
        });
      }

      this.data.total_qty = totalQty;
      this.data.total_amount = totalAmount;
    },

    getSerialCount(serialStr) {
      if (!serialStr) return 0;
      return serialStr.split(',').map((s) => s.trim()).filter((s) => s.length > 0).length;
    },

    openSerialModal(index, pitem) {
      this.activeRowIndex = index;
      this.tempSerial = '';
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
    },

    addSerialFromInput() {
      const sn = this.tempSerial ? this.tempSerial.trim() : '';
      if (sn) {
        if (!this.modalSerials.includes(sn)) {
          this.modalSerials.push(sn);
        } else {
          this.$toast('Serial number already added in list', 'warning');
        }
        this.tempSerial = '';
      }
    },

    removeSerial(index) {
      this.modalSerials.splice(index, 1);
    },

    saveSerialsFromModal() {
      if (this.activeRowIndex !== null && this.data.grn_details[this.activeRowIndex]) {
        const row = this.data.grn_details[this.activeRowIndex];
        const serialStr = this.modalSerials.join(', ');
        row.serial_no = serialStr;
        if (this.syncQtyWithSerials && this.modalSerials.length > 0) {
          row.received_qty = Math.min(row.remaining_qty, this.modalSerials.length);
          this.onQtyChange(row);
        }
      }
      this.closeSerialModal();
      this.$toast('Serial numbers updated', 'success');
    },

    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          this.$toast(
            'You need to fill ' + error + ' more empty mandatory fields',
            'warning'
          );
          return false;
        }

        // Validate items received
        const hasExceeded = this.data.grn_details.some(
          (item) => parseFloat(item.received_qty) > parseFloat(item.remaining_qty)
        );
        if (hasExceeded) {
          this.$toast('Receive quantity cannot exceed remaining quantity for any item.', 'error');
          return false;
        }

        if (this.data.total_qty <= 0) {
          this.$toast('Please enter at least one item quantity to receive.', 'warning');
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

    getGrnData() {
      axios.get(`${this.model}/${this.$route.params.id}`)
        .then((response) => {
          this.data = response.data;
          this.selectedSupplierName = response.data.supplier?.org_name || '';
          this.calculateTotals();
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },

  created() {
    this.getPendingPurchases();

    if (this.$route.params.id) {
      this.page_title = `Goods Receive (GRN) Edit`;
      this.getGrnData();
    } else {
      this.page_title = `Goods Receive (GRN) Create`;
      // If purchase_id is passed as query parameter
      if (this.$route.query.purchase_id) {
        this.data.purchase_id = parseInt(this.$route.query.purchase_id);
        this.onPurchaseChange(this.data.purchase_id);
      }
    }
  },

  validators: {
    'data.grn_date': function (value = null) {
      return Validator.value(value).required('GRN Date is required');
    },
    'data.purchase_id': function (value = null) {
      return Validator.value(value).required('Purchase Order is required');
    },
    'data.warehouse_id': function (value = null) {
      return Validator.value(value).required('Destination Warehouse is required');
    },
  },
};
</script>
