<template>
  <create-form @onSubmit='submit'>
    <!-- Left Sidebar: Media, Barcode & Status -->
    <div class="col-lg-3 col-md-4">
      <div class="row g-3">
        <!-- Image Upload Card -->
        <div class="col-12">
          <div class="card border shadow-sm">
            <div class="card-header bg-light py-2">
              <span class="fw-bold small text-dark"><i class="fas fa-image me-1 text-primary"></i> Item Image</span>
            </div>
            <div class="card-body p-3">
              <File
                title="Image"
                field="data.original_image"
                mime="img"
                fileClassName="file2"
                accept=".jpg, .jpeg, .png"
                :showCrop="true"
                :vHeight="
                  $root.media_validators?.image?.min_height ??
                  $root.media_validators?.item?.min_height ??
                  600
                "
                :vWidth="
                  $root.media_validators?.image?.min_width ??
                  $root.media_validators?.item?.min_width ??
                  600
                "
                :vSizeInKb="
                  $root.media_validators?.image?.max_size ??
                  $root.media_validators?.item?.max_size ??
                  5000
                "
                col="12"
              />
              <GlobalCrop
                field="data.original_image"
                v-on:update:modelValue="data.original_image = $event"
                :image="image.original_image"
                :aspectRatio="{
                  aspectRatio:
                    ($root.media_validators?.image?.min_width ??
                      $root.media_validators?.item?.min_width ??
                      600) /
                    ($root.media_validators?.image?.min_height ??
                      $root.media_validators?.item?.min_height ??
                      600),
                }"
                :minWidth="
                  $root.media_validators?.image?.min_width ??
                  $root.media_validators?.item?.min_width ??
                  600
                "
                :minHeight="
                  $root.media_validators?.image?.min_height ??
                  $root.media_validators?.item?.min_height ??
                  600
                "
              ></GlobalCrop>
            </div>
          </div>
        </div>

        <!-- Barcode Preview Card -->
        <div class="col-12" v-if="data.barcode">
          <div class="card border shadow-sm">
            <div class="card-header bg-light py-2">
              <span class="fw-bold small text-dark"><i class="fas fa-barcode me-1 text-primary"></i> Barcode</span>
            </div>
            <div class="card-body p-3 text-center">
              <div class="d-flex justify-content-center my-1">
                <img v-if="data.barcode_image" :src="data.barcode_image" alt="Barcode Preview" style="height: 45px; max-width: 100%;" />
              </div>
              <div class="fw-bold font-monospace fs-6 text-dark mt-1">{{ data.barcode }}</div>
              <button type="button" class="btn btn-xs btn-outline-secondary mt-2 w-100" @click="fetchGeneratedBarcode" title="Regenerate Next Barcode">
                <i class="fas fa-sync-alt me-1"></i> Auto Barcode
              </button>
            </div>
          </div>
        </div>

        <!-- Status Switch Card -->
        <div class="col-12">
          <div class="card border shadow-sm">
            <div class="card-header bg-light py-2">
              <span class="fw-bold small text-dark"><i class="fas fa-toggle-on me-1 text-primary"></i> Status (অবস্থা)</span>
            </div>
            <div class="card-body p-3">
              <Switch v-model='data.status' field='data.status' title='Status' on-label='Active' off-label='Deactive' :req='true' col="12">
              </Switch>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Main Content: Form Inputs & Matrices -->
    <div class="col-lg-9 col-md-8">
      <div class="row g-3">
        <!-- Basic Information Card -->
        <div class="col-12">
          <div class="card border shadow-sm">
            <div class="card-header bg-light py-2">
              <span class="fw-bold text-dark"><i class="fas fa-info-circle me-1 text-primary"></i> Basic Information (মৌলিক তথ্য)</span>
            </div>
            <div class="card-body p-3">
              <div class="row g-3">
                <Select title='Category' v-model='data.category_id' field='data.category_id' label='title'
                  :reduce='(obj) => obj.id' col="4" :options='categories' placeholder='--Select Category--' :closeOnSelect='true'
                  :required='true' />
                <Select title='Brand' v-model='data.brand_id' field='data.brand_id' label='title'
                  :reduce='(obj) => obj.id' col="4" :options='brands'
                  :placeholder="data.category_id ? (brands.length ? '--Select Brand--' : 'No Brand in Category') : '--Select Category First--'"
                  :closeOnSelect='true'
                  :required='false' />
                <Select title='Unit' v-model='data.unit_id' field='data.unit_id' label='title' :reduce='(obj) => obj.id' col="4"
                  :options='units' placeholder='--Select Unit--' :closeOnSelect='true' :required='true' />
                
                <Input v-model='data.title' field='data.title' title='Title' col="6" :req='true' />
                <Input v-model='data.barcode' field='data.barcode' title='Barcode' col="6" placeholder="Auto-generated" :req='false' />
                
                <Input v-model='data.purchase_price' col="6" field='data.purchase_price' title='Purchase Price (ক্রয় মূল্য)' type="number" step="0.01" :req='false' />
                <Input v-model='data.selling_price' col="6" field='data.selling_price' title='Selling Price (বিক্রয় মূল্য)' type="number" step="0.01" :req='false' />
                
                <Textarea v-model='data.description' field='data.description' :required='false' title="Description" col="12" />
              </div>
            </div>
          </div>
        </div>

        <!-- 🛡️ Warranty / Guarantee Section (Display when site_setting shop_type is electronics) -->
        <div class="col-12" v-if="isElectronicsShop">
          <div class="card border border-primary-subtle shadow-sm rounded-3">
            <div class="card-header bg-primary bg-opacity-10 py-2 border-bottom">
              <span class="fw-bold text-primary small d-flex align-items-center gap-2">
                <i class="fas fa-shield-alt"></i> Warranty / Guarantee Management (ওয়ারেন্টি / গ্যারান্টি সেটিংস)
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row g-3 align-items-center">
                <!-- Radio Options -->
                <div class="col-md-5">
                  <label class="form-label fw-bold small text-dark d-block mb-2">Coverage Type (ধরণ):</label>
                  <div class="d-flex flex-wrap gap-3">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input cursor-pointer" type="radio" id="warrantyNone" value="none" v-model="data.warranty_type">
                      <label class="form-check-label small cursor-pointer" for="warrantyNone">None (নেই)</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input cursor-pointer" type="radio" id="warrantyType" value="warranty" v-model="data.warranty_type">
                      <label class="form-check-label small fw-bold text-primary cursor-pointer" for="warrantyType">
                        <i class="fas fa-tools me-1"></i> Warranty (ওয়ারেন্টি)
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input cursor-pointer" type="radio" id="guaranteeType" value="guarantee" v-model="data.warranty_type">
                      <label class="form-check-label small fw-bold text-success cursor-pointer" for="guaranteeType">
                        <i class="fas fa-certificate me-1"></i> Guarantee (গ্যারান্টি)
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Warranty Period Field (Visible when warranty or guarantee selected) -->
                <div class="col-md-7" v-if="data.warranty_type === 'warranty' || data.warranty_type === 'guarantee'">
                  <label class="form-label fw-bold small text-dark mb-1">
                    {{ data.warranty_type === 'guarantee' ? 'Guarantee Period (গ্যারান্টি মেয়াদ)' : 'Warranty Period (ওয়ারেন্টি মেয়াদ)' }}:
                  </label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-primary">
                      <i class="fas fa-calendar-alt"></i>
                    </span>
                    <input
                      type="text"
                      class="form-control form-control-sm fw-bold"
                      placeholder="e.g. 1 Year, 6 Months, 7 Days Replacement..."
                      v-model="data.warranty_period"
                    />
                  </div>
                  <!-- Quick Suggestions -->
                  <div class="d-flex flex-wrap gap-1 mt-1">
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '7 Days Replacement'">7 Days</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '1 Month'">1 Month</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '3 Months'">3 Months</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '6 Months'">6 Months</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '1 Year'">1 Year</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '2 Years'">2 Years</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = '3 Years'">3 Years</span>
                    <span class="badge bg-light text-dark border cursor-pointer hover-shadow" @click="data.warranty_period = 'Lifetime'">Lifetime</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Price Modification / New Purchase Checkbox for Edit Mode -->
        <div class="col-12" v-if="data.id">
          <div class="card border border-info shadow-sm bg-light">
            <div class="card-body p-3">
              <div class="form-check form-switch d-flex align-items-center gap-2">
                <input class="form-check-input ms-0 cursor-pointer" type="checkbox" id="priceModCheck" v-model="is_price_modification" style="transform: scale(1.3);">
                <label class="form-check-label fw-bold text-primary fs-6 cursor-pointer mb-0 ms-2" for="priceModCheck">
                  <i class="fas fa-edit me-1"></i> Price Modification / New Purchase (মূল্য পরিবর্তন / নতুন ক্রয়)
                </label>
              </div>
              <small class="d-block text-muted mt-2">
                <i class="fas fa-info-circle me-1"></i> Enable this switch to modify purchase/selling prices or add new stock for specific color & size variants.
              </small>
            </div>
          </div>
        </div>

        <!-- Color & Size Wise Price & Stock Matrix -->
        <div class="col-12" v-if="!data.id || is_price_modification">
          <div class="card border shadow-sm">
            <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-2">
              <span class="fw-bold fs-6">
                <i class="fas fa-tags me-2"></i>{{ isElectronicsShop ? 'Color Wise Price & Stock Matrix' : 'Color & Size Wise Price & Stock Matrix' }}
              </span>
              <button type="button" class="btn btn-sm btn-success px-3 fw-bold" @click="addVariantRow">
                <i class="fas fa-plus me-1"></i> Add Variant Row
              </button>
            </div>
            <div class="card-body p-0 table-responsive">
              <table class="table table-bordered table-striped mb-0 align-middle">
                <thead class="table-light text-center">
                  <tr>
                    <th :width="isElectronicsShop ? '28%' : '20%'">Color (রং)</th>
                    <th width="20%" v-if="!isElectronicsShop">Size (সাইজ)</th>
                    <th :width="isElectronicsShop ? '24%' : '18%'">Purchase Price (ক্রয় মূল্য)</th>
                    <th :width="isElectronicsShop ? '24%' : '18%'">Selling Price (বিক্রয় মূল্য)</th>
                    <th :width="isElectronicsShop ? '14%' : '14%'">{{ data.id ? 'Add Qty (নতুন স্টক)' : 'Opening Qty (মজুদ)' }}</th>
                    <th width="10%">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(v, index) in variants" :key="index">
                    <td>
                      <select class="form-select form-select-sm" v-model="v.color_id">
                        <option :value="null">-- Select Color --</option>
                        <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.title }}</option>
                      </select>
                    </td>
                    <td v-if="!isElectronicsShop">
                      <select class="form-select form-select-sm" v-model="v.size_id">
                        <option :value="null">-- Select Size --</option>
                        <option v-for="s in sizes" :key="s.id" :value="s.id">{{ s.title }}</option>
                      </select>
                    </td>
                    <td>
                      <input type="number" step="0.01" class="form-control form-control-sm text-end" v-model.number="v.purchase_price" placeholder="0.00" />
                    </td>
                    <td>
                      <input type="number" step="0.01" class="form-control form-control-sm text-end" v-model.number="v.selling_price" placeholder="0.00" />
                    </td>
                    <td>
                      <input type="number" class="form-control form-control-sm text-center" v-model.number="v.qty" placeholder="0" />
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-outline-danger" @click="removeVariantRow(index)" :disabled="variants.length === 1" title="Remove Row">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- 🌟 Prominent Custom Form Footer Actions -->
    <template #form_footer>
      <div class="col-12 mt-3">
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
                <i class="fas fa-check-circle"></i> {{ $route.params.id ? "Update Item" : "Save & Create Item" }}
              </template>
            </button>
          </div>
        </div>
      </div>
    </template>

  </create-form>
</template>

<script>
import Editor from '../../../components/Form/CKEditor';

const model = 'item';

export default {
  components: { Editor },
  computed: {
    isElectronicsShop() {
      const shopType = this.site?.shop_type || this.$root.site?.shop_type;
      return shopType === 'electronics';
    },
  },
  data() {
    return {
      model: model,
      page_title: '',
      data: {
        original_image: '',
        barcode: '',
        category_id: null,
        brand_id: null,
        unit_id: null,
        purchase_price: '',
        selling_price: '',
        status: 'active',
        warranty_type: 'none',
        warranty_period: '',
      },
      image: {
        original_image: '',
      },
      categories: [],
      brands: [],
      units: [],
      colors: [],
      sizes: [],
      is_price_modification: false,
      variants: [
        { color_id: null, size_id: null, purchase_price: 0, selling_price: 0, qty: 0 }
      ],
    };
  },

  watch: {
    'data.category_id'(newVal, oldVal) {
      this.getBrands(newVal);
      if (oldVal && newVal !== oldVal) {
        this.data.brand_id = null;
      }
    },
    'data.purchase_price'(newVal) {
      if (this.variants.length === 1 && (!this.variants[0].purchase_price || this.variants[0].purchase_price == 0)) {
        this.variants[0].purchase_price = newVal ? Number(newVal) : 0;
      }
    },
    'data.selling_price'(newVal) {
      if (this.variants.length === 1 && (!this.variants[0].selling_price || this.variants[0].selling_price == 0)) {
        this.variants[0].selling_price = newVal ? Number(newVal) : 0;
      }
    },
  },

  provide() {
    return {
      validate: this.validation,
      data: () => this.data,
      image: this.image,
    };
  },
  methods: {
    addVariantRow() {
      const defPurchase = this.data.purchase_price ? Number(this.data.purchase_price) : 0;
      const defSelling = this.data.selling_price ? Number(this.data.selling_price) : 0;
      this.variants.push({
        color_id: null,
        size_id: null,
        purchase_price: defPurchase,
        selling_price: defSelling,
        qty: 0
      });
    },
    removeVariantRow(index) {
      if (this.variants.length > 1) {
        this.variants.splice(index, 1);
      }
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
          formData.append('description', this.data.description || '');
          formData.append('category_id', this.data.category_id || '');
          formData.append('brand_id', this.data.brand_id || '');
          formData.append('unit_id', this.data.unit_id || '');
          formData.append('purchase_price', this.data.purchase_price || 0);
          formData.append('selling_price', this.data.selling_price || 0);
          formData.append('status', this.data.status || 'active');
          formData.append('barcode', this.data.barcode || '');
          formData.append('warranty_type', this.data.warranty_type || 'none');
          formData.append('warranty_period', this.data.warranty_period || '');
          formData.append('is_price_modification', this.is_price_modification ? '1' : '0');
          const cleanedVariants = this.variants.map(v => ({
            ...v,
            size_id: this.isElectronicsShop ? null : v.size_id
          }));
          formData.append('variants', JSON.stringify(cleanedVariants));
          formData.append('image_base64', this.data.original_image ?? '');
          formData.append(
            'image_resize_value',
            this.$root.media_validators?.image?.resize_value ??
            this.$root.media_validators?.item?.resize_value ??
            ''
          );

          if (this.data.id) {
            this.update(this.model, formData, this.data.id, true);
          } else {
            this.store(this.model, formData);
          }
        }
      });
    },
    getCategories() {
      let module = 'Item';
      axios.get(`getcategories/${module}`)
        .then((response) => {
          this.categories = response.data;
        });
    },
    getBrands(categoryId = null) {
      const catId = categoryId || this.data.category_id;
      if (!catId) {
        this.brands = [];
        return;
      }
      axios.get(`getbrands/${catId}`)
        .then((response) => {
          this.brands = response.data;
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
    fetchGeneratedBarcode() {
      axios.get('generate-item-barcode')
        .then((response) => {
          if (response.data) {
            this.data.barcode = response.data.barcode;
            this.data.barcode_image = response.data.barcode_image;
          }
        });
    }
  },
  created() {
    if (this.$route.params.id) {
      this.page_title = this.headline(this.model) + ' Edit';
      this.get_data(`${this.model}/${this.$route.params.id}`).then(() => {
        if (this.data.category_id) {
          this.getBrands(this.data.category_id);
        }
        if (this.data.item_prices && this.data.item_prices.length > 0) {
          this.variants = this.data.item_prices.map(p => ({
            color_id: p.color_id,
            size_id: p.size_id,
            purchase_price: p.purchase_price,
            selling_price: p.selling_price,
            qty: 0,
          }));
          if (!this.data.purchase_price && this.data.item_prices[0]) {
            this.data.purchase_price = this.data.item_prices[0].purchase_price;
          }
          if (!this.data.selling_price && this.data.item_prices[0]) {
            this.data.selling_price = this.data.item_prices[0].selling_price;
          }
        }
      });
    } else {
      this.page_title = this.headline(this.model) + ' Create';
      this.fetchGeneratedBarcode();
    }
    this.getCategories();
    this.getUnits();
    this.getColorsAndSizes();
    this.getMediaValidators('Item');
  },

  validators: {
    'data.category_id': function (value = null) { return Validator.value(value).required('Category Id is required'); },
    'data.title': function (value = null) { return Validator.value(value).required('Title is required'); },
  },
}
</script>