<template>
  <create-form @onSubmit='submit'>
    <div class="col-lg-3">
      <div class="row g-3">
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
    <div class="col-lg-9">
      <div class="row g-3">
        <Select title='Category' v-model='data.category_id' field='data.category_id' label='title'
          :reduce='(obj) => obj.id' col="4 col-md-3" :options='categories' placeholder='--Select One--' :closeOnSelect='true'
          :required='true' />
        <Input v-model='data.title' field='data.title' title='Title' col="4 col-md-3" :req='true' />
        <Input v-model='data.barcode' field='data.barcode' title='Barcode' col="4 col-md-3" placeholder="Auto-generated" :req='false' />
        <Select title='Unit' v-model='data.unit_id' field='data.unit_id' label='title' :reduce='(obj) => obj.id' col="4 col-md-3"
          :options='units' placeholder='--Select One--' :closeOnSelect='true' :required='true' />
        <Input v-model='data.opening_rate' col="4 col-md-3" field='data.opening_rate' title='Default Opening Rate' :req='false' />
        <Input v-model='data.opening_qty' col="4 col-md-3" field='data.opening_qty' title='Default Opening Qty' :req='false' />
        <Textarea v-model='data.description' field='data.description' :required='false' title="Description" col="12" />

        <div class="col-md-6" v-if="data.barcode">
          <div class="p-2 border rounded bg-light d-flex align-items-center gap-3">
            <div>
              <small class="text-muted d-block fw-bold mb-1">Barcode Preview:</small>
              <img v-if="data.barcode_image" :src="data.barcode_image" alt="Barcode Preview" style="height: 45px;" />
              <div class="fw-bold font-monospace mt-1 fs-6 text-dark">{{ data.barcode }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" @click="fetchGeneratedBarcode" title="Regenerate Next Barcode">
              <i class="fas fa-sync-alt me-1"></i> Auto Barcode
            </button>
          </div>
        </div>

        <!-- 🛡️ Warranty / Guarantee Section (Display when site_setting shop_type is electronics) -->
        <div class="col-12" v-if="isElectronicsShop">
          <div class="card border border-primary-subtle shadow-sm rounded-3">
            <div class="card-header bg-primary bg-opacity-10 py-2 border-bottom">
              <span class="fw-bold text-primary small d-flex align-items-center gap-2">
                <i class="fas fa-shield-alt"></i> Warranty / Guarantee Management (ওয়ারেন্টি / গ্যারান্টি সেটিংস)
              </span>
            </div>
            <div class="card-body p-3">
              <div class="row g-3 align-items-center">
                <!-- Radio Options -->
                <div class="col-md-5">
                  <label class="form-label fw-bold small text-dark d-block mb-2">Coverage Type (ধরণ):</label>
                  <div class="d-flex flex-wrap gap-3">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="warrantyNone" value="none" v-model="data.warranty_type">
                      <label class="form-check-label small cursor-pointer" for="warrantyNone">None (নেই)</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="warrantyType" value="warranty" v-model="data.warranty_type">
                      <label class="form-check-label small fw-bold text-primary cursor-pointer" for="warrantyType">
                        <i class="fas fa-tools me-1"></i> Warranty (ওয়ারেন্টি)
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" id="guaranteeType" value="guarantee" v-model="data.warranty_type">
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
          <div class="form-check form-switch p-3 bg-light border rounded">
            <input class="form-check-input ms-0 me-2" type="checkbox" id="priceModCheck" v-model="is_price_modification" style="transform: scale(1.2);">
            <label class="form-check-label fw-bold text-primary" for="priceModCheck">
              <i class="fas fa-edit me-1"></i> Price Modification / New Purchase (মূল্য পরিবর্তন / নতুন ক্রয়)
            </label>
            <small class="d-block text-muted ms-4 mt-1">Check this box to modify purchase/selling prices or add new stock for specific color & size variants.</small>
          </div>
        </div>

        <!-- Color & Size Wise Price & Stock Matrix -->
        <div class="col-12 mt-3" v-if="!data.id || is_price_modification">
          <div class="card border shadow-sm">
            <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-2">
              <span class="fw-bold fs-6"><i class="fas fa-tags me-2"></i>Color & Size Wise Price & Stock Matrix</span>
              <button type="button" class="btn btn-sm btn-success px-3" @click="addVariantRow">
                <i class="fas fa-plus me-1"></i> Add Variant Row
              </button>
            </div>
            <div class="card-body p-0 table-responsive">
              <table class="table table-bordered table-striped mb-0 align-middle">
                <thead class="table-light text-center">
                  <tr>
                    <th width="22%">Color (রং)</th>
                    <th width="22%">Size (সাইজ)</th>
                    <th width="18%">Purchase Price (ক্রয় মূল্য)</th>
                    <th width="18%">Selling Price (বিক্রয় মূল্য)</th>
                    <th width="12%">Qty (পরিমাণ)</th>
                    <th width="8%">Action</th>
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
                    <td>
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
                      <button type="button" class="btn btn-sm btn-outline-danger" @click="removeVariantRow(index)" :disabled="variants.length === 1">
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
    <Switch v-model='data.status' field='data.status' title='status' on-label='Active' off-label='Deactive' :req='true'>
    </Switch>

  </create-form>
</template>

<script>
import Editor from '../../../components/Form/CKEditor';

const model = 'item';

export default {
  components: { Editor },
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
      data: {
        original_image: '',
        barcode: '',
        status: 'active',
        warranty_type: 'none',
        warranty_period: '',
      },
      image: {
        original_image: '',
      },
      categories: [],
      units: [],
      colors: [],
      sizes: [],
      is_price_modification: false,
      variants: [
        { color_id: null, size_id: null, purchase_price: 0, selling_price: 0, qty: 0 }
      ],
    };
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
      this.variants.push({ color_id: null, size_id: null, purchase_price: 0, selling_price: 0, qty: 0 });
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
          formData.append('unit_id', this.data.unit_id || '');
          formData.append('status', this.data.status || 'active');
          formData.append('barcode', this.data.barcode || '');
          formData.append('warranty_type', this.data.warranty_type || 'none');
          formData.append('warranty_period', this.data.warranty_period || '');
          formData.append('is_price_modification', this.is_price_modification ? '1' : '0');
          formData.append('variants', JSON.stringify(this.variants));
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
        if (this.data.item_prices && this.data.item_prices.length > 0) {
          this.variants = this.data.item_prices.map(p => ({
            color_id: p.color_id,
            size_id: p.size_id,
            purchase_price: p.purchase_price,
            selling_price: p.selling_price,
            qty: 0,
          }));
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