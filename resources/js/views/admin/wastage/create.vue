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

    <!-- Wastage Items Table -->
    <div class="col-md-12 mb-3">
      <fieldset class="mt-2 p-3 border rounded shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="legend fw-bold fs-6 text-danger">
            <i class="fas fa-boxes me-2"></i>Wastage / Damaged Items List (নষ্ট/ক্ষতিগ্রস্ত পণ্যের তালিকা)
          </span>
          <button type="button" class="btn btn-sm btn-outline-danger fw-bold" @click="addWastageDetailsRow">
            <i class="fas fa-plus me-1"></i> Add Another Item
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center" style="font-size: 13px;">
              <tr>
                <th style="width: 14%;">Category</th>
                <th style="width: 18%;">Item / Product</th>
                <th style="width: 10%;">Color</th>
                <th style="width: 10%;">Size</th>
                <th style="width: 8%;">Stock</th>
                <th style="width: 8%;">Wastage Qty</th>
                <th style="width: 9%;">Unit Cost</th>
                <th style="width: 9%;">Total Loss</th>
                <th style="width: 12%;">Wastage Reason</th>
                <th style="width: 11%;">Expired Date</th>
                <th style="width: 4%;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(witem, index) in data.wastage_details" :key="index">
                <!-- Category -->
                <td>
                  <Select v-model="witem.category_id" field="witem.category_id" label="title"
                    :reduce="(obj) => obj.id" :options="categories" placeholder="-- Category --" :closeOnSelect="true"
                    :required="false" col="12" @change="onCategoryChange(witem)" />
                </td>

                <!-- Item -->
                <td>
                  <Select v-model="witem.item_id" field="witem.item_id" label="title" :reduce="(obj) => obj.id"
                    :options="witem.items && witem.items.length ? witem.items : allItems" placeholder="-- Select Item --" :closeOnSelect="true"
                    :required="true" col="12" @change="onItemChange(witem)" />
                </td>

                <!-- Color Variant -->
                <td>
                  <select class="form-select form-select-sm" v-model="witem.color_id" @change="fetchVariantStock(witem)">
                    <option :value="null">-- Standard --</option>
                    <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.title }}</option>
                  </select>
                </td>

                <!-- Size Variant -->
                <td>
                  <select class="form-select form-select-sm" v-model="witem.size_id" @change="fetchVariantStock(witem)">
                    <option :value="null">-- Standard --</option>
                    <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                  </select>
                </td>

                <!-- Current Stock Badge -->
                <td class="text-center">
                  <span class="badge" :class="(witem.available_stock > 0) ? 'bg-success' : 'bg-secondary'" style="font-size: 12px;">
                    {{ witem.available_stock !== undefined ? witem.available_stock : '-' }}
                  </span>
                </td>

                <!-- Quantity -->
                <td>
                  <input type="number" step="any" min="0.01" class="form-control form-control-sm text-center fw-bold"
                    v-model="witem.quantity" placeholder="0" />
                </td>

                <!-- Unit Cost Price -->
                <td>
                  <input type="number" step="any" class="form-control form-control-sm text-end font-monospace"
                    v-model="witem.unit_price" placeholder="0.00" />
                </td>

                <!-- Total Amount -->
                <td>
                  <input type="text" class="form-control form-control-sm text-end font-monospace fw-bold bg-light"
                    :value="(Number(witem.quantity || 0) * Number(witem.unit_price || 0)).toFixed(2)" readonly />
                </td>

                <!-- Reason -->
                <td>
                  <select class="form-select form-select-sm" v-model="witem.reason">
                    <option value="Expired">Expired (মেয়াদোত্তীর্ণ)</option>
                    <option value="Damaged in Storage">Damaged (স্টোরেজে ক্ষতিগ্রস্ত)</option>
                    <option value="Broken / Shattered">Broken (ভাঙা / নষ্ট)</option>
                    <option value="Spoiled / Rotten">Spoiled (পচে গেছে / বিনষ্ট)</option>
                    <option value="Factory / Quality Defect">Quality Defect (ত্রুটিপূর্ণ)</option>
                    <option value="Lost / Theft">Lost / Theft (হারিয়ে গেছে / চুরি)</option>
                    <option value="Audit Shortage">Audit Shortage (অডিটে ঘাটতি)</option>
                    <option value="Packaging Defect">Packaging Defect (প্যাকেজিং নষ্ট)</option>
                    <option value="Other">Other (অন্যান্য)</option>
                  </select>
                </td>

                <!-- Expired Date -->
                <td>
                  <date-picker :id="'exp_date_' + index" v-model="witem.expired_date"
                    :field="'witem.expired_date_' + index" placeholder="YYYY-MM-DD" col="12" :req="false" />
                </td>

                <!-- Action Button -->
                <td class="text-center">
                  <div class="d-flex align-items-center justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" title="Delete Row"
                      @click.prevent="removeWastageDetails(index)" v-if="data.wastage_details.length > 1">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                    <button v-if="isLastItem(data.wastage_details, index)" type="button" class="btn btn-sm btn-outline-success p-1"
                      title="Add Row" @click.prevent="addWastageDetailsRow">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Summary Footer Banner -->
        <div class="row mt-3 pt-3 border-top bg-light rounded mx-0 align-items-center">
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
            <span class="fw-bold fs-5 text-danger ms-2 font-monospace">{{ $root.currency(totalLossAmount) }}</span>
          </div>
        </div>
      </fieldset>
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
        wastage_details: [
          {
            category_id: null,
            items: [],
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
          },
        ],
      },
      categories: [],
      allItems: [],
      units: [],
      colors: [],
      sizes: [],
    };
  },

  provide() {
    return {
      validate: this.validation,
    };
  },

  computed: {
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
    }
  },

  methods: {
    onCategoryChange(witem) {
      witem.item_id = null;
      witem.available_stock = 0;

      if (!witem.category_id) {
        witem.items = this.allItems;
        return;
      }

      axios.get(`getitemsbycategory/${witem.category_id}`)
        .then((response) => {
          witem.items = response.data;
        })
        .catch(() => {
          witem.items = [];
        });
    },

    onItemChange(witem) {
      if (!witem.item_id) {
        witem.unit_price = 0;
        witem.available_stock = 0;
        return;
      }

      // Find item details from allItems or response
      const matched = this.allItems.find(it => it.id === witem.item_id);
      if (matched) {
        if (!witem.category_id && matched.category_id) {
          witem.category_id = matched.category_id;
        }
        witem.unit_id = matched.unit_id;
        witem.unit_price = matched.purchase_price || matched.price || 0;
      }

      this.fetchVariantStock(witem);
    },

    fetchVariantStock(witem) {
      if (!witem.item_id) return;

      axios.get(`getitemstock/${witem.item_id}`)
        .then((res) => {
          witem.available_stock = res.data ?? 0;
        })
        .catch(() => {
          witem.available_stock = 0;
        });
    },

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

    addWastageDetailsRow() {
      this.data.wastage_details.push({
        category_id: null,
        items: this.allItems,
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
      });
    },

    removeWastageDetails(index) {
      if (this.data.wastage_details.length > 1) {
        this.data.wastage_details.splice(index, 1);
      }
    },

    isLastItem(items, index) {
      return index === items.length - 1;
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

        // Validate items
        for (let i = 0; i < this.data.wastage_details.length; i++) {
          const it = this.data.wastage_details[i];
          if (!it.item_id) {
            this.$toast(`Please select an item for row #${i + 1}`, 'warning');
            return false;
          }
          if (!it.quantity || Number(it.quantity) <= 0) {
            this.$toast(`Please enter a valid quantity for row #${i + 1}`, 'warning');
            return false;
          }
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
              this.fetchVariantStock(detail);
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
