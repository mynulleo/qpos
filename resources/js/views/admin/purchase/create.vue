<template>
  <create-form @onSubmit='submit'>
    <Input v-model='data.invoiceno' field='data.invoiceno' col="2" title='Invoice No' :req='false' />
    <date-picker id='date1' v-model='data.purchase_date' field='data.purchase_date' title='Purchase Date'
      placeholder='Purchase Date' col='2' :req='true'></date-picker>
    <div class="col-8"></div>
    <Select title='Suppliers' v-model='data.supplier_id' field='data.supplier_id' label='org_name'
      :reduce='(obj) => obj.id' :options='$root.global.suppliers' col="3" placeholder='--Select One--'
      :closeOnSelect='true' :required='true' />
    <Input v-model='data.amount' col="2" field='data.amount' title='Amount' :req='false' />
    <Input v-model='data.discount' col="2" field='data.discount' title='Discount' :req='false' />
    <Input v-model='data.tax' col="2" field='data.tax' title='Tax' :req='false' />
    <Input v-model='data.total_amount' col="2" field='data.total_amount' title='Total Amount' :req='false' />
    <div class="col-md-12 mb-3">
      <fieldset class="mt-4">
        <span class="legend"><i class="fas fa-boxes me-2"></i>Purchase Items (ক্রয়কৃত পণ্যসমূহ)</span>
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-dark text-center">
                  <tr>
                    <th style="width:15%">Category</th>
                    <th style="width:15%">Item</th>
                    <th style="width:11%">Color</th>
                    <th style="width:11%">Size</th>
                    <th style="width:9%">Unit</th>
                    <th style="width:9%">Purchase Price</th>
                    <th style="width:9%">Selling Price</th>
                    <th style="width:7%">Qty</th>
                    <th style="width:9%">Serial Nos</th>
                    <th style="width:10%">Amount</th>
                    <th style="width:4%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(pitem, index) in data.purchase_details" :key="index">
                    <td>
                      <Select v-model="pitem.category_id" field="pitem.category_id" label="title"
                        :reduce="(obj) => obj.id" :options="categories" placeholder="--Select--" :closeOnSelect="true"
                        :required="false" col="12" @change="onCategoryChange(pitem)" />
                    </td>
                    <td>
                      <Select v-model="pitem.item_id" field="pitem.item_id" label="title" :reduce="(obj) => obj.id"
                        :options="pitem.items" placeholder="--Select--" :closeOnSelect="true" :required="false"
                        col="12" />
                    </td>
                    <td>
                      <select class="form-select form-select-sm" v-model="pitem.color_id">
                        <option :value="null">-- Standard --</option>
                        <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.title }}</option>
                      </select>
                    </td>
                    <td>
                      <select class="form-select form-select-sm" v-model="pitem.size_id">
                        <option :value="null">-- Standard --</option>
                        <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                      </select>
                    </td>
                    <td>
                      <Select v-model="pitem.unit_id" field="pitem.unit_id" label="title" :reduce="(obj) => obj.id"
                        :options="units" placeholder="--Select--" :closeOnSelect="true" :required="false" col="12" />
                    </td>
                    <td>
                      <Input v-model="pitem.price" field="pitem.price" col="12" placeholder="0.00" />
                    </td>
                    <td>
                      <Input v-model="pitem.selling_price" field="pitem.selling_price" col="12" placeholder="0.00" />
                    </td>
                    <td>
                      <Input v-model="pitem.qty" field="pitem.qty" col="12" placeholder="0" />
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-outline-primary position-relative px-2 py-1" @click="openSerialModal(index, pitem)" title="Add / View Serial Numbers">
                        <i class="fas fa-barcode me-1"></i> Serials
                        <span class="badge bg-danger ms-1" v-if="getSerialCount(pitem.serial_no) > 0">{{ getSerialCount(pitem.serial_no) }}</span>
                      </button>
                    </td>
                    <td>
                      <Input v-model="pitem.total_amount" field="pitem.total_amount" col="12" />
                    </td>
                    <td class="text-center">
                      <div class="multiple_fields_actions_btn d-flex align-items-center justify-content-center gap-1">
                        <button type="button" class="btns delete_one" data-bs-toggle="tooltip" data-bs-placement="top"
                          data-bs-title="Delete" v-x-tooltip @click.prevent="removePurchaseDetails(index)" v-if="Object.keys(data.purchase_details).length > 1">
                          <i class="fas fa-trash"></i>
                        </button>
                        <button v-if="isLastItem(data.purchase_details, index)" type="button" class="btns add_more"
                          data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add" v-x-tooltip
                          @click.prevent="addPurchaseDetailsRow()">
                          <i class="fas fa-plus-square"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </fieldset>
    </div>
    <Switch v-model='data.status' field='data.status' title='status' on-label='Active' off-label='Deactive' :req='true'>
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
              <i class="fas fa-info-circle me-1"></i> Type or scan serial number and press <strong>Enter</strong> to add quickly.
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
                <label class="form-check-label small fw-bold" for="syncQtyCheck">Auto update Row Quantity to {{ modalSerials.length }}</label>
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
const model = 'purchase';

export default {

  data() {
    return {
      model: model,
      page_title: '',
      data: {
        purchase_date: this.$filter.today(),
        amount: 0,
        purchase_details: [
          {
            category_id: null,
            item_id: null,
            color_id: null,
            size_id: null,
            items: [],
            price: 0,
            selling_price: 0,
            qty: 0,
            serial_no: '',
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
  watch: {
    'data.purchase_details': {
      handler() {
        this.calculateRowAmount();
        this.calculateTotals();
      },
      deep: true
    }
  },
  methods: {
    getSerialCount(serialStr) {
      if (!serialStr) return 0;
      return serialStr.split(',').map(s => s.trim()).filter(s => s.length > 0).length;
    },
    openSerialModal(index, pitem) {
      this.activeRowIndex = index;
      this.tempSerial = '';
      if (pitem.serial_no) {
        this.modalSerials = pitem.serial_no.split(',').map(s => s.trim()).filter(s => s.length > 0);
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
      if (this.activeRowIndex !== null && this.data.purchase_details[this.activeRowIndex]) {
        const serialStr = this.modalSerials.join(', ');
        this.data.purchase_details[this.activeRowIndex].serial_no = serialStr;
        if (this.syncQtyWithSerials && this.modalSerials.length > 0) {
          this.data.purchase_details[this.activeRowIndex].qty = this.modalSerials.length;
        }
      }
      this.closeSerialModal();
      this.$toast('Serial numbers saved', 'success');
    },
    submit: function (e) {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();

        if (error > 0) {
          console.log(this.validation.allErrors());
          this.$toast(
            'You need to fill ' + error + ' more empty mandatory fields',
            'warning'
          );
          return false;
        }

        if (res) {
          var form = document.getElementById('form');
          var formData = new FormData(form);
          formData.append('supplier_id', this.data.supplier_id);
          formData.append('purchase_date', this.data.purchase_date);
          if (this.data.id) {
            this.update(this.model, this.data, this.data.id);
          } else {
            this.store(this.model, this.data);
          }
        }
      });
    },
    onCategoryChange(pitem) {
      // category clear হলে item reset
      pitem.item_id = null;
      pitem.items = [];

      if (!pitem.category_id) {
        return;
      }

      axios.get(`getitemsbycategory/${pitem.category_id}`)
        .then((response) => {
          pitem.items = response.data;
        })
        .catch(() => {
          pitem.items = [];
        });
    },
    getCategories() {
      let module = 'Item';
      axios.get(`getcategories/${module}`)
        .then((response) => {
          this.categories = response.data;
        });
    },
    getUnits() {
      let module = 'Item';
      axios.get(`getunits/${module}`)
        .then((response) => {
          this.units = response.data;
        });
    },
    getColorsAndSizes() {
      axios.get('color?allData=true').then(res => { this.colors = res.data; });
      axios.get('size?allData=true').then(res => { this.sizes = res.data; });
    },
    getItems(category_id) {
      axios.get(`getitems/${category_id}`)
        .then((response) => {
          this.items = response.data;
        });
    },
    calculateRowAmount() {
      this.data.purchase_details.forEach(detail => {
        const price = parseFloat(detail.price) || 0;
        const qty = parseFloat(detail.qty) || 0;
        detail.total_amount = price * qty;
      });
    },
    calculateTotals() {
      let totalAmt = 0;
      this.data.purchase_details.forEach(detail => {
        totalAmt += parseFloat(detail.total_amount) || 0;
      });
      this.data.amount = totalAmt;
      this.data.total_amount = totalAmt;
    },
    addPurchaseDetailsRow() {
      this.data.purchase_details.push({
        category_id: null,
        items: [],
        item_id: null,
        color_id: null,
        size_id: null,
        price: 0,
        selling_price: 0,
        qty: 0,
        serial_no: '',
        total_amount: 0,
      });
    },
    removePurchaseDetails(index) {
      if (Object.keys(this.data.purchase_details).length > 1) {
        this.data.purchase_details.splice(index, 1)
      }
    },
    isLastItem(items, index) {
      return index === items.length - 1;
    },
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`);
    } else {
      this.page_title = this.headline(this.model) + ' Create';
    }

    this.getCategories();
    this.getUnits();
    this.getColorsAndSizes();
  },

  validators: {
    'data.supplier_id': function (value = null) { return Validator.value(value).required('Supplier is required'); },
    'data.purchase_date': function (value = null) { return Validator.value(value).required('Purchase Date is required'); },

  },
}

</script>