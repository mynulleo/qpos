<template>
  <create-form @onSubmit='submit'>
    <!-- 1. Header Information Card -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border-0 bg-white">
        <div class="card-header bg-light py-2">
          <span class="fw-bold text-dark"><i class="fas fa-sliders-h me-1 text-primary"></i> Stock Adjustment Information (সমন্বয় সাধারণ তথ্য)</span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <Input v-model='data.adjustment_no' field='data.adjustment_no' col="3" title='Adjustment No (চালান নং)' placeholder='Auto Generated' :req='false' :readonly="true" />
            
            <date-picker id='adj_date' v-model='data.adjustment_date' field='data.adjustment_date' title='Adjustment Date (তারিখ)'
              placeholder='Select Date' col='3' :req='true'></date-picker>

            <Select title='Adjustment Type (সমন্বয়ের ধরণ)' v-model='data.adjustment_type' field='data.adjustment_type'
              :options='adjustmentTypes' col="3" placeholder='--Select Type--'
              :closeOnSelect='true' :required='true' />

            <Select title='Conducted By (দায়িত্বরত কর্মকর্তা/অডিটর)' v-model='data.conducted_by' field='data.conducted_by' label='full_name'
              :reduce='(obj) => obj.id' :options='$root.global.employees' col="3" placeholder='--Select Employee--'
              :closeOnSelect='true' :required='false' />

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Reason (সমন্বয়ের কারণ)</label>
              <input type="text" class="form-control" v-model="data.reason" placeholder="e.g. Annual Inventory Count, Damaged items found, Stock mismatch..." />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold">Remarks / Note (মন্তব্য)</label>
              <input type="text" class="form-control" v-model="data.remarks" placeholder="Additional audit notes or instructions..." />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Fast Category Filter, Barcode Scanner & Product Search -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border border-primary-subtle bg-light">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span class="fw-bold text-dark">
            <i class="fas fa-search-plus me-1 text-primary"></i> Product Search & Barcode Scanner (পণ্য নির্বাচন ও বারকোড স্ক্যানার)
          </span>
          <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
            <i class="fas fa-magic me-1"></i>পণ্য সিলেক্ট বা স্ক্যান করা মাত্রই অটোমেটিক ভ্যারিয়েন্ট, ক্রয়মূল্য ও মজুদসহ তালিকায় যুক্ত হবে
          </span>
        </div>
        <div class="card-body p-3">
          <div class="row g-2 align-items-end">
            <!-- 2.1 Category Dropdown -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                <span><i class="fas fa-folder me-1 text-warning"></i> 1. Category Filter:</span>
                <button
                  type="button"
                  class="btn btn-xs btn-outline-primary py-0 px-2 small"
                  style="font-size: 11px;"
                  v-if="selectedCategoryId"
                  @click="loadAllCategoryItems"
                  :disabled="isLoadingCategoryItems"
                  title="Load all products under this category into table"
                >
                  <i class="fas fa-spinner fa-spin me-1" v-if="isLoadingCategoryItems"></i>
                  <i class="fas fa-layer-group me-1" v-else></i>
                  ক্যাটাগরির সব যোগ করুন
                </button>
              </label>
              <select class="form-select shadow-sm" v-model="selectedCategoryId" @change="onCategoryChange">
                <option :value="null">-- All Categories (সকল ক্যাটাগরি) --</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.title }}
                </option>
              </select>
            </div>

            <!-- 2.2 Barcode Fast Scanner Input -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-dark">
                <i class="fas fa-barcode me-1 text-danger"></i> 2. Barcode Scanner (বারকোড স্ক্যান):
              </label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-white text-danger border-end-0">
                  <i class="fas fa-barcode"></i>
                </span>
                <input
                  ref="barcodeInput"
                  type="text"
                  class="form-control border-start-0"
                  v-model.trim="barcodeScanInput"
                  @keyup.enter="handleBarcodeScan"
                  placeholder="Scan barcode & press Enter..."
                  autocomplete="off"
                />
              </div>
            </div>

            <!-- 2.3 Product Select Dropdown (Auto-adds to table immediately on selection) -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-dark">
                <i class="fas fa-box-open me-1 text-primary"></i> 3. Select Product (পণ্য নির্বাচন করুন):
              </label>
              <v-select
                v-model="selectedItem"
                :options="itemList"
                label="title"
                placeholder="Type title or barcode to search & add..."
                @option:selected="onItemSelect"
                @search="onSearchItems"
                class="shadow-sm"
              >
                <template #no-options="{ search, searching }">
                  <template v-if="searching">
                    <span class="text-muted small">No items matching "<em>{{ search }}</em>"</span>
                  </template>
                  <em v-else class="text-muted small">Type product name or barcode...</em>
                </template>
                <template #option="option">
                  <div class="d-flex justify-content-between align-items-center py-1">
                    <div>
                      <strong class="text-dark">{{ option.title }}</strong>
                      <div class="small text-muted font-monospace" v-if="option.barcode">
                        <i class="fas fa-barcode me-1 text-secondary"></i>{{ option.barcode }}
                      </div>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-secondary font-monospace d-block mb-1" v-if="option.category">{{ option.category.title }}</span>
                      <span class="badge bg-info text-dark font-monospace" v-if="option.opening_rate || option.purchase_price">৳ {{ option.opening_rate || option.purchase_price }}</span>
                    </div>
                  </div>
                </template>
              </v-select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Adjustment Items Table Card -->
    <div class="col-12 mb-3">
      <div class="card shadow-sm border-0 bg-white">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fs-6 fw-bold">
              <i class="fas fa-boxes me-2"></i>Stock Adjustment Items Matrix (সমন্বয় তালিকা)
            </span>
            <span class="badge bg-primary rounded-pill px-2 py-1">{{ data.details.length }} Items</span>
          </div>

          <div class="d-flex align-items-center gap-3 small font-monospace">
            <span><i class="fas fa-arrow-up text-success me-1"></i>Stock In: <strong class="text-success">{{ totalQtyIn }}</strong></span>
            <span><i class="fas fa-arrow-down text-danger me-1"></i>Stock Out: <strong class="text-danger">{{ totalQtyOut }}</strong></span>
            <button
              type="button"
              class="btn btn-outline-danger btn-xs py-0 px-2 ms-2 text-white border-danger"
              style="font-size: 11px;"
              v-if="data.details.length > 0"
              @click="clearAllItems"
              title="Clear all items from table"
            >
              <i class="fas fa-trash me-1"></i>তালিকা খালি করুন
            </button>
          </div>
        </div>

        <div class="card-body p-0 table-responsive">
          <table class="table table-bordered table-striped align-middle mb-0 text-center" v-if="data.details.length > 0">
            <thead class="table-light">
              <tr>
                <th width="3%">#</th>
                <th width="25%" class="text-start">Product (পণ্য)</th>
                <th width="11%">Variant (ভ্যারিয়েন্ট)</th>
                <th width="11%">Unit Cost (ক্রয়মূল্য)</th>
                <th width="12%" class="table-info">System Stock (সিস্টেম মজুদ)</th>
                <th width="14%" class="table-warning">Physical Count (বাস্তব গণনা)</th>
                <th width="13%">Difference (পার্থক্য)</th>
                <th width="8%">Total Impact</th>
                <th width="3%">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in data.details" :key="index">
                <td class="fw-bold text-muted">{{ index + 1 }}</td>
                
                <!-- Product Title, Category & Barcode -->
                <td class="text-start">
                  <div class="fw-bold text-dark">{{ item.title }}</div>
                  <div class="small text-muted d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light text-secondary border" v-if="item.category_title">
                      {{ item.category_title }}
                    </span>
                    <span class="font-monospace text-secondary" v-if="item.barcode">
                      <i class="fas fa-barcode me-1"></i>{{ item.barcode }}
                    </span>
                  </div>
                </td>

                <!-- Variant Info (Color & Size) -->
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-1" v-if="item.color_title">
                    {{ item.color_title }}
                  </span>
                  <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" v-if="item.size_title">
                    {{ item.size_title }}
                  </span>
                  <span class="text-muted small" v-if="!item.color_title && !item.size_title">Standard</span>
                </td>

                <!-- Unit Cost (Auto-filled with purchase price, editable) -->
                <td>
                  <div class="input-group input-group-sm">
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control form-control-sm text-end font-monospace"
                      v-model.number="item.unit_cost"
                      @input="recalculateRow(item)"
                      placeholder="0.00"
                    />
                  </div>
                </td>

                <!-- System Stock (Read-Only) -->
                <td class="table-info font-monospace fw-bold fs-6 text-primary">
                  {{ item.system_qty }}
                </td>

                <!-- Physical Count (User Input Field) -->
                <td class="table-warning">
                  <input
                    type="number"
                    step="1"
                    min="0"
                    class="form-control form-control-sm text-center font-monospace fw-bold fs-6 border-warning shadow-sm"
                    v-model.number="item.physical_qty"
                    @input="recalculateRow(item)"
                    placeholder="0"
                  />
                </td>

                <!-- Difference Badge (+ In / - Out / Matched) -->
                <td>
                  <span
                    v-if="item.difference_qty > 0"
                    class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 font-monospace fs-6"
                  >
                    <i class="fas fa-arrow-up me-1"></i>+{{ item.difference_qty }} (In)
                  </span>
                  <span
                    v-else-if="item.difference_qty < 0"
                    class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 font-monospace fs-6"
                  >
                    <i class="fas fa-arrow-down me-1"></i>{{ item.difference_qty }} (Out)
                  </span>
                  <span
                    v-else
                    class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace fs-6"
                  >
                    0 (Matched)
                  </span>
                </td>

                <!-- Total Amount (Financial Impact) -->
                <td class="font-monospace fw-semibold text-end">
                  {{ $filter.formatBDT(item.total_amount) }}
                </td>

                <!-- Remove Row -->
                <td>
                  <button type="button" class="btn btn-sm btn-outline-danger" @click="removeItemRow(index)" title="Remove Item">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-else class="text-center py-5 text-muted bg-light">
            <i class="fas fa-clipboard-check fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>কোনো আইটেম যোগ করা হয়নি</h5>
            <p class="small mb-0">উপরের সার্চ বক্স থেকে পণ্য সিলেক্ট করুন অথবা বারকোড স্ক্যান করুন; স্বয়ংক্রিয়ভাবে স্টক ও ক্রয়মূল্যসহ তালিকায় যোগ হবে।</p>
          </div>
        </div>

        <!-- Summary Footer Card -->
        <div class="card-footer bg-white border-top p-3" v-if="data.details.length > 0">
          <div class="row g-3 align-items-center justify-content-between">
            <div class="col-md-7">
              <div class="d-flex flex-wrap gap-3 small">
                <span class="badge bg-light text-dark border p-2">
                  Total Items: <strong>{{ data.details.length }}</strong>
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success p-2">
                  <i class="fas fa-plus-circle me-1"></i>Total Stock Added: <strong>+{{ totalQtyIn }}</strong>
                </span>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger p-2">
                  <i class="fas fa-minus-circle me-1"></i>Total Stock Deducted: <strong>-{{ totalQtyOut }}</strong>
                </span>
              </div>
            </div>

            <div class="col-md-5 text-end">
              <span class="text-muted small me-2">Total Financial Impact (মোট আর্থিক প্রভাব):</span>
              <strong class="fs-5 text-primary font-monospace">{{ $filter.formatBDT(totalAmount) }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Prominent Action Footer -->
    <template #form_footer>
      <div class="col-12 mt-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 bg-white border rounded shadow-sm">
          <router-link :to="{ name: model + '.index' }" class="btn btn-outline-secondary px-4 fw-semibold">
            <i class="fas fa-arrow-left me-1"></i> Back to List
          </router-link>

          <button
            type="submit"
            class="theme_btn px-5 py-2 fw-bold d-flex align-items-center gap-2 shadow"
            :disabled="$root.submit || data.details.length === 0"
          >
            <template v-if="$root.submit">
              <i class="fa fa-spinner fa-spin"></i> Processing...
            </template>
            <template v-else>
              <i class="fas fa-check-circle"></i> Save Stock Adjustment
            </template>
          </button>
        </div>
      </div>
    </template>
  </create-form>
</template>

<script>
const model = 'stockAdjustment';

export default {
  computed: {
    isElectronicsShop() {
      const shopType = this.site?.shop_type || this.$root.site?.shop_type;
      return shopType === 'electronics';
    },
    totalQtyIn() {
      return this.data.details
        .filter(d => d.difference_qty > 0)
        .reduce((sum, d) => sum + Number(d.difference_qty), 0);
    },
    totalQtyOut() {
      return this.data.details
        .filter(d => d.difference_qty < 0)
        .reduce((sum, d) => sum + Math.abs(Number(d.difference_qty)), 0);
    },
    totalAmount() {
      return this.data.details.reduce((sum, d) => sum + Number(d.total_amount || 0), 0);
    }
  },

  data() {
    return {
      model: model,
      page_title: 'Create Stock Adjustment',
      data: {
        adjustment_no: '',
        adjustment_date: this.currentDate(),
        adjustment_type: 'Physical Count',
        conducted_by: null,
        reason: '',
        remarks: '',
        status: 'active',
        details: [],
      },
      adjustmentTypes: ['Physical Count', 'Damage Correction', 'Reconciliation', 'Periodic Audit', 'Other'],
      categories: [],
      selectedCategoryId: null,
      barcodeScanInput: '',
      itemList: [],
      selectedItem: null,
      searchTimeout: null,
      isAddingItem: false,
      isLoadingCategoryItems: false,
    };
  },

  provide() {
    return {
      validate: this.validation,
      data: () => this.data,
      image: {},
    };
  },

  methods: {
    currentDate() {
      const d = new Date();
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${day}/${month}/${year}`;
    },

    getCategories() {
      axios.get('getcategories/Item')
        .then((res) => {
          this.categories = res.data || [];
        });
    },

    getCategoryName(catId) {
      const cat = this.categories.find(c => c.id === catId);
      return cat ? cat.title : '';
    },

    onCategoryChange() {
      this.selectedItem = null;
      this.fetchItems();
    },

    fetchItems() {
      const params = { allData: true };
      if (this.selectedCategoryId) {
        params.category_id = this.selectedCategoryId;
      }
      axios.get('item', { params })
        .then((res) => {
          this.itemList = res.data || [];
        })
        .catch(() => {
          this.itemList = [];
        });
    },

    onSearchItems(search, loading) {
      if (!search || search.trim().length < 1) {
        return;
      }
      loading(true);
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        const params = {
          term: search.trim()
        };
        if (this.selectedCategoryId) {
          params.category_id = this.selectedCategoryId;
        }
        axios.get('pos/search-items', { params })
          .then((res) => {
            if (res.data && res.data.items) {
              this.itemList = res.data.items;
            } else if (Array.isArray(res.data)) {
              this.itemList = res.data;
            }
            loading(false);
          })
          .catch(() => {
            loading(false);
          });
      }, 250);
    },

    handleBarcodeScan() {
      const code = this.barcodeScanInput ? this.barcodeScanInput.trim() : '';
      if (!code) return;

      axios.get('pos/search-items', { params: { term: code } })
        .then((res) => {
          const items = res.data && res.data.items ? res.data.items : (Array.isArray(res.data) ? res.data : []);
          const exactItem = items.find(i => i.barcode && String(i.barcode).toLowerCase() === code.toLowerCase()) || items[0];
          if (exactItem) {
            this.onItemSelect(exactItem);
          } else {
            this.$toast(`কোনো পণ্য পাওয়া যায়নি (Barcode: ${code})`, 'warning');
          }
          this.barcodeScanInput = '';
          if (this.$refs.barcodeInput) this.$refs.barcodeInput.focus();
        })
        .catch(() => {
          this.$toast(`বারকোড সার্চে সমস্যা হয়েছে!`, 'error');
          this.barcodeScanInput = '';
          if (this.$refs.barcodeInput) this.$refs.barcodeInput.focus();
        });
    },

    onItemSelect(item) {
      if (!item) return;
      const itemId = item.id ? item.id : item;
      if (!itemId) return;

      this.isAddingItem = true;

      // Automatically fetch stock & purchase price and add to details matrix
      axios.get(`getitemstock/${itemId}?details=true`)
        .then((res) => {
          const stockData = res.data || {};
          const itemObj = stockData.item || item || {};
          const baseCost = Number(stockData.unit_cost ?? itemObj.purchase_price ?? itemObj.opening_rate ?? 0);
          const variants = stockData.variants || [];

          if (variants && variants.length > 0) {
            let addedCount = 0;
            variants.forEach(v => {
              const vColorId = v.color_id || null;
              const vSizeId = v.size_id || null;
              const vColorTitle = v.color_title && v.color_title !== 'Standard' ? v.color_title : null;
              const vSizeTitle = v.size_title && v.size_title !== 'Standard' ? v.size_title : null;
              const vCost = Number(v.unit_cost ?? baseCost);
              const vStock = Number(v.stock ?? 0);

              // Check if already in details
              const exists = this.data.details.some(d => 
                Number(d.item_id) === Number(itemId) && 
                (d.color_id || null) == (vColorId || null) && 
                (d.size_id || null) == (vSizeId || null)
              );

              if (!exists) {
                this.data.details.push({
                  item_id: Number(itemId),
                  title: itemObj.title || item.title || 'Product #' + itemId,
                  barcode: itemObj.barcode || item.barcode || '',
                  category_title: itemObj.category ? itemObj.category.title : (item.category ? item.category.title : ''),
                  color_id: vColorId,
                  color_title: vColorTitle,
                  size_id: vSizeId,
                  size_title: vSizeTitle,
                  unit_cost: vCost,
                  system_qty: vStock,
                  physical_qty: vStock,
                  difference_qty: 0,
                  total_amount: 0,
                  remarks: '',
                });
                addedCount++;
              }
            });

            if (addedCount > 0) {
              this.$toast(`'${itemObj.title || item.title}' (${addedCount} ভ্যারিয়েন্ট) তালিকায় যুক্ত হয়েছে!`, 'success');
            } else {
              this.$toast(`'${itemObj.title || item.title}' ইতিমধ্যে তালিকায় রয়েছে!`, 'warning');
            }
          } else {
            // Standard item without variants
            const stock = Number(stockData.stock ?? 0);
            const exists = this.data.details.some(d => Number(d.item_id) === Number(itemId) && !d.color_id && !d.size_id);
            
            if (!exists) {
              this.data.details.push({
                item_id: Number(itemId),
                title: itemObj.title || item.title || 'Product #' + itemId,
                barcode: itemObj.barcode || item.barcode || '',
                category_title: itemObj.category ? itemObj.category.title : (item.category ? item.category.title : ''),
                color_id: null,
                color_title: null,
                size_id: null,
                size_title: null,
                unit_cost: baseCost,
                system_qty: stock,
                physical_qty: stock,
                difference_qty: 0,
                total_amount: 0,
                remarks: '',
              });
              this.$toast(`'${itemObj.title || item.title}' তালিকায় যুক্ত হয়েছে!`, 'success');
            } else {
              this.$toast(`'${itemObj.title || item.title}' ইতিমধ্যে তালিকায় রয়েছে!`, 'warning');
            }
          }

          // Reset selection so user can immediately search next item
          this.$nextTick(() => {
            this.selectedItem = null;
            this.isAddingItem = false;
            if (this.$refs.barcodeInput) this.$refs.barcodeInput.focus();
          });
        })
        .catch((err) => {
          this.isAddingItem = false;
          console.error(err);
          this.$toast('পণ্য যুক্ত করতে সমস্যা হয়েছে!', 'error');
        });
    },

    loadAllCategoryItems() {
      if (!this.selectedCategoryId) return;
      this.isLoadingCategoryItems = true;

      axios.get('item', { params: { allData: true, category_id: this.selectedCategoryId } })
        .then(async (res) => {
          const items = res.data || [];
          if (items.length === 0) {
            this.$toast('এই ক্যাটাগরিতে কোনো পণ্য পাওয়া যায়নি!', 'warning');
            this.isLoadingCategoryItems = false;
            return;
          }

          let addedTotal = 0;
          for (const it of items) {
            try {
              const stockRes = await axios.get(`getitemstock/${it.id}?details=true`);
              const stockData = stockRes.data || {};
              const baseCost = Number(stockData.unit_cost ?? it.purchase_price ?? it.opening_rate ?? 0);
              const variants = stockData.variants || [];

              if (variants && variants.length > 0) {
                variants.forEach(v => {
                  const vColorId = v.color_id || null;
                  const vSizeId = v.size_id || null;
                  const vColorTitle = v.color_title && v.color_title !== 'Standard' ? v.color_title : null;
                  const vSizeTitle = v.size_title && v.size_title !== 'Standard' ? v.size_title : null;
                  const vCost = Number(v.unit_cost ?? baseCost);
                  const vStock = Number(v.stock ?? 0);

                  const exists = this.data.details.some(d => 
                    Number(d.item_id) === Number(it.id) && 
                    (d.color_id || null) == (vColorId || null) && 
                    (d.size_id || null) == (vSizeId || null)
                  );

                  if (!exists) {
                    this.data.details.push({
                      item_id: Number(it.id),
                      title: it.title || 'Product #' + it.id,
                      barcode: it.barcode || '',
                      category_title: it.category ? it.category.title : '',
                      color_id: vColorId,
                      color_title: vColorTitle,
                      size_id: vSizeId,
                      size_title: vSizeTitle,
                      unit_cost: vCost,
                      system_qty: vStock,
                      physical_qty: vStock,
                      difference_qty: 0,
                      total_amount: 0,
                      remarks: '',
                    });
                    addedTotal++;
                  }
                });
              } else {
                const stock = Number(stockData.stock ?? 0);
                const exists = this.data.details.some(d => Number(d.item_id) === Number(it.id) && !d.color_id && !d.size_id);
                if (!exists) {
                  this.data.details.push({
                    item_id: Number(it.id),
                    title: it.title || 'Product #' + it.id,
                    barcode: it.barcode || '',
                    category_title: it.category ? it.category.title : '',
                    color_id: null,
                    color_title: null,
                    size_id: null,
                    size_title: null,
                    unit_cost: baseCost,
                    system_qty: stock,
                    physical_qty: stock,
                    difference_qty: 0,
                    total_amount: 0,
                    remarks: '',
                  });
                  addedTotal++;
                }
              }
            } catch (e) {
              console.error(e);
            }
          }

          this.isLoadingCategoryItems = false;
          this.$toast(`${addedTotal} টি পণ্য সফলভাবে তালিকায় যোগ করা হয়েছে!`, 'success');
        })
        .catch(() => {
          this.isLoadingCategoryItems = false;
          this.$toast('ক্যাটাগরির পণ্য লোড করতে সমস্যা হয়েছে!', 'error');
        });
    },

    clearAllItems() {
      if (confirm('আপনি কি নিশ্চিত যে তালিকা খালি করতে চান?')) {
        this.data.details = [];
        this.$toast('তালিকা খালি করা হয়েছে!', 'info');
      }
    },

    recalculateRow(item) {
      const pQty = item.physical_qty !== '' && item.physical_qty !== null ? Number(item.physical_qty) : 0;
      const sQty = Number(item.system_qty || 0);
      item.difference_qty = pQty - sQty;
      const cost = Number(item.unit_cost || 0);
      item.total_amount = Math.round(Math.abs(item.difference_qty) * cost * 100) / 100;
    },

    removeItemRow(index) {
      this.data.details.splice(index, 1);
    },

    getEditData() {
      if (this.$route.params.id) {
        axios.get(`${this.model}/${this.$route.params.id}`)
          .then((res) => {
            const adj = res.data;
            if (adj) {
              this.data.adjustment_no = adj.adjustment_no;
              this.data.adjustment_date = adj.adjustment_date;
              this.data.adjustment_type = adj.adjustment_type;
              this.data.conducted_by = adj.conducted_by;
              this.data.reason = adj.reason;
              this.data.remarks = adj.remarks;
              this.data.status = adj.status;

              if (adj.details && adj.details.length > 0) {
                this.data.details = adj.details.map(d => ({
                  item_id: d.item_id,
                  title: d.item ? d.item.title : 'Item #' + d.item_id,
                  barcode: d.item ? d.item.barcode : '',
                  category_title: d.item && d.item.category ? d.item.category.title : '',
                  color_id: d.color_id,
                  color_title: d.color ? d.color.title : null,
                  size_id: d.size_id,
                  size_title: d.size ? d.size.title : null,
                  unit_cost: Number(d.unit_cost || 0),
                  system_qty: Number(d.system_qty || 0),
                  physical_qty: Number(d.physical_qty || 0),
                  difference_qty: Number(d.difference_qty || 0),
                  total_amount: Number(d.total_amount || 0),
                  remarks: d.remarks || '',
                }));
              }
            }
          });
      }
    },

    submit() {
      this.$validate().then((res) => {
        const error = this.validation.countErrors();
        if (error > 0) {
          this.$toast('প্রয়োজনীয় ফিল্ডগুলো পূরণ করুন!', 'warning');
          return false;
        }

        if (this.data.details.length === 0) {
          this.$toast('কমপক্ষে একটি আইটেম যোগ করুন!', 'warning');
          return false;
        }

        if (this.$route.params.id) {
          this.update(this.model, this.data, this.$route.params.id);
        } else {
          this.store(this.model, this.data);
        }
      });
    }
  },

  created() {
    this.getCategories();
    this.fetchItems();
    this.getEditData();
  },

  mounted() {
    if (this.$refs.barcodeInput) {
      this.$nextTick(() => {
        this.$refs.barcodeInput.focus();
      });
    }
  },

  validators: {
    'data.adjustment_type': function (value = null) { return Validator.value(value).required('Adjustment Type is required'); },
    'data.adjustment_date': function (value = null) { return Validator.value(value).required('Adjustment Date is required'); },
  }
};
</script>

<style scoped>
.table > :not(caption) > * > * {
  padding: 0.5rem 0.5rem;
}
.table-warning input:focus {
  background-color: #fffbe6;
  border-color: #ffc107;
  box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
}
</style>
