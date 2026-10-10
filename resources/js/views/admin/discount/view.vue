<template>
  <view-page>
    <div class="row g-3">
      <!-- 1. Left Card: Campaign Details -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
              <i class="fas fa-tags me-2"></i>{{ $t("Discount Campaign Details") }}
            </h6>
            <span
              :class="data.status === 'active' ? 'badge bg-success' : 'badge bg-danger'"
              class="px-2 py-1"
            >
              {{ data.status ? $t(data.status.toUpperCase()) : '' }}
            </span>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
              <tbody>
                <tr>
                  <th width="38%">{{ $t("Campaign Title") }}</th>
                  <td>
                    <strong class="text-dark fs-6">{{ data.title || data.name || $t("N/A") }}</strong>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Discount Scope") }}</th>
                  <td>
                    <span v-if="data.scope === 'category'" class="badge bg-warning text-dark border">
                      <i class="fas fa-folder me-1"></i>{{ $t("Category Wise") }}
                    </span>
                    <span v-else class="badge bg-info text-dark border">
                      <i class="fas fa-boxes me-1"></i>{{ $t("Item Wise") }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Target Summary") }}</th>
                  <td>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <strong class="text-dark">{{ data.target_title || $t("All") }}</strong>
                      <span
                        v-if="data.scope === 'category' && data.category_summary"
                        class="badge bg-success bg-opacity-10 text-success border border-success font-monospace"
                      >
                        <i class="fas fa-cubes me-1"></i>{{ data.category_summary.total_items }} {{ $t("Items Covered") }}
                      </span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Discount Rate / Value") }}</th>
                  <td>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fs-6 px-3 py-1 font-monospace fw-bold">
                      <i class="fas fa-tag me-1"></i>{{ data.discount_display || (data.discount_value + (data.discount_type === 'percentage' ? '%' : ' ৳')) }}
                    </span>
                    <span class="text-muted small ms-2 text-capitalize">({{ data.discount_type || data.unit }})</span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Applicable On") }}</th>
                  <td>
                    <span class="badge bg-light text-dark border px-2 py-1 text-capitalize font-monospace">
                      {{ data.applicable_on === 'both' ? $t('Retail & Wholesale') : (data.applicable_on ? $t(data.applicable_on) : $t('Both')) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 2. Right Card: Validity & Audit Information -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
              <i class="fas fa-calendar-alt me-2"></i>{{ $t("Validity & Audit Information") }}
            </h6>
            <span v-if="data.is_expired" class="badge bg-danger">
              <i class="fas fa-clock me-1"></i>{{ $t("Expired") }}
            </span>
            <span v-else-if="data.status === 'active'" class="badge bg-success">
              <i class="fas fa-check-circle me-1"></i>{{ $t("Active Campaign") }}
            </span>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
              <tbody>
                <tr>
                  <th width="38%">{{ $t("Valid From Date") }}</th>
                  <td>
                    <span class="font-monospace fw-bold text-dark">
                      {{ data.valid_from ? formatDate(data.valid_from) : '-' }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Valid To Date") }}</th>
                  <td>
                    <span class="font-monospace fw-bold text-dark">
                      {{ data.valid_to ? formatDate(data.valid_to) : '-' }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Validity Period") }}</th>
                  <td>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1 font-monospace">
                      {{ data.validity || '-' }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Created By") }}</th>
                  <td>
                    <span class="fw-semibold text-dark">
                      {{ data.creator?.full_name || data.creator?.name || data.creator?.email || $t("Admin") }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Created At") }}</th>
                  <td>
                    <span class="text-muted small font-monospace">
                      {{ data.created_at ? formatDateTime(data.created_at) : '-' }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <th>{{ $t("Remarks / Notes") }}</th>
                  <td>
                    <span class="text-secondary">{{ data.notes || data.description || $t("No special remarks") }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 3. Category Analytics KPI Cards (When scope is 'category') -->
      <div class="col-12" v-if="data.scope === 'category' && data.category_summary">
        <div class="row g-3">
          <!-- KPI 1: Total Category Items -->
          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-white p-3 h-100 kpi-card">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block mb-1">{{ $t("Total Category Items") }}</span>
                  <h4 class="mb-0 fw-bold text-dark font-monospace">
                    {{ data.category_summary.total_items }}
                  </h4>
                  <small class="text-success fw-semibold">
                    <i class="fas fa-check-circle me-1"></i>{{ data.category_summary.active_items }} {{ $t("Active") }}
                  </small>
                </div>
                <div class="kpi-icon-circle bg-primary bg-opacity-10 text-primary">
                  <i class="fas fa-boxes fs-4"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- KPI 2: Category Price Range -->
          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-white p-3 h-100 kpi-card">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block mb-1">{{ $t("Retail Price Range") }}</span>
                  <h5 class="mb-0 fw-bold text-dark font-monospace">
                    ৳{{ formatPrice(data.category_summary.min_price) }} - {{ formatPrice(data.category_summary.max_price) }}
                  </h5>
                  <small class="text-muted">
                    {{ $t("Average:") }} ৳{{ formatPrice(data.category_summary.avg_price) }}
                  </small>
                </div>
                <div class="kpi-icon-circle bg-success bg-opacity-10 text-success">
                  <i class="fas fa-tags fs-4"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- KPI 3: Applied Discount Benefit -->
          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-white p-3 h-100 kpi-card">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block mb-1">{{ $t("Campaign Benefit") }}</span>
                  <h4 class="mb-0 fw-bold text-danger font-monospace">
                    {{ data.discount_display }}
                  </h4>
                  <small class="text-muted">
                    {{ $t("On all") }} {{ data.category_summary.total_items }} {{ $t("category items") }}
                  </small>
                </div>
                <div class="kpi-icon-circle bg-danger bg-opacity-10 text-danger">
                  <i class="fas fa-percent fs-4"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- KPI 4: Max Unit Savings -->
          <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-white p-3 h-100 kpi-card">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block mb-1">{{ $t("Max Customer Saving") }}</span>
                  <h4 class="mb-0 fw-bold text-info font-monospace">
                    ৳ {{ formatPrice(calculateDiscountAmount(data.category_summary.max_price)) }}
                  </h4>
                  <small class="text-muted">
                    {{ $t("Per item purchase") }}
                  </small>
                </div>
                <div class="kpi-icon-circle bg-info bg-opacity-10 text-info">
                  <i class="fas fa-piggy-bank fs-4"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Information Callout Alert -->
        <div class="alert alert-primary border-primary border-opacity-25 bg-primary bg-opacity-10 mt-3 mb-0 d-flex align-items-center gap-3 p-3 rounded">
          <i class="fas fa-info-circle fs-3 text-primary"></i>
          <div>
            <strong class="d-block text-dark mb-1">
              {{ $t("Category Wide Coverage Policy:") }}
            </strong>
            <span class="text-secondary small">
              {{ $t("This discount applies to all existing") }} <strong>{{ data.category_summary.total_items }} {{ $t("items") }}</strong> {{ $t("under") }} <strong>"{{ data.category ? data.category.title : '' }}"</strong> {{ $t("as well as any newly added items in this category. Whenever any of these items are added to the POS cart, the discount will be applied automatically.") }}
            </span>
          </div>
        </div>
      </div>

      <!-- 4. Interactive Products Table (For Category Wise & Item Wise) -->
      <div class="col-12" v-if="data.items_details && data.items_details.length > 0">
        <div class="card border-0 shadow-sm mt-1">
          <!-- Card Header with Search & Count -->
          <div class="card-header bg-dark text-white py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <h6 class="mb-0 fw-bold">
                <i class="fas" :class="data.scope === 'category' ? 'fa-folder-open' : 'fa-boxes'"></i>
                <span v-if="data.scope === 'category'">
                  {{ $t("Products in Category:") }} {{ data.category ? data.category.title : '' }}
                </span>
                <span v-else>
                  {{ $t("Included Products / Items") }}
                </span>
              </h6>
              <span class="badge bg-primary fw-bold">
                {{ filteredItems.length }} {{ $t("Items") }}
              </span>
              <span class="badge bg-white text-dark fw-bold" v-if="filteredItems.length !== (data.items_details || []).length">
                {{ $t("Filtered from") }} {{ (data.items_details || []).length }}
              </span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
              <!-- Live Search Box -->
              <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-white border-end-0">
                  <i class="fas fa-search text-muted"></i>
                </span>
                <input
                  type="text"
                  class="form-control border-start-0"
                  v-model="itemSearch"
                  :placeholder="$t('Search name or barcode...')"
                />
                <button
                  type="button"
                  class="btn btn-outline-light btn-sm"
                  v-if="itemSearch"
                  @click="itemSearch = ''"
                  :title="$t('Clear Search')"
                >
                  <i class="fas fa-times text-dark"></i>
                </button>
              </div>

              <!-- Page Size Selector -->
              <select class="form-select form-select-sm text-dark bg-white" style="width: 100px;" v-model.number="pageSize">
                <option :value="10">10 / page</option>
                <option :value="25">25 / page</option>
                <option :value="50">50 / page</option>
                <option :value="100">100 / page</option>
              </select>

              <span class="badge bg-danger fs-6 fw-bold">
                {{ data.discount_display }} {{ $t("OFF") }}
              </span>
            </div>
          </div>

          <!-- Card Body: Table -->
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th width="5%" class="text-center">#</th>
                    <th>{{ $t("Product Title") }}</th>
                    <th width="14%" class="text-center">{{ $t("Barcode") }}</th>
                    <th width="13%" class="text-end">{{ $t("Retail Price") }}</th>
                    <th width="13%" class="text-end" v-if="data.applicable_on !== 'retail'">{{ $t("Wholesale Price") }}</th>
                    <th width="12%" class="text-end">{{ $t("Discount") }}</th>
                    <th width="14%" class="text-end">{{ $t("Final Retail Price") }}</th>
                    <th width="9%" class="text-center">{{ $t("Status") }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in paginatedItems" :key="item.id">
                    <td class="text-center font-monospace text-muted">
                      {{ (currentPage - 1) * pageSize + index + 1 }}
                    </td>
                    <td>
                      <strong class="text-dark">{{ item.title }}</strong>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-secondary font-monospace" v-if="item.barcode">
                        {{ item.barcode }}
                      </span>
                      <span class="text-muted small" v-else>-</span>
                    </td>
                    <td class="text-end font-monospace">
                      ৳ {{ formatPrice(item.retail_price) }}
                    </td>
                    <td class="text-end font-monospace text-secondary" v-if="data.applicable_on !== 'retail'">
                      ৳ {{ formatPrice(item.wholesale_price) }}
                    </td>
                    <td class="text-end font-monospace text-danger fw-bold">
                      - ৳ {{ formatPrice(calculateDiscountAmount(item.retail_price)) }}
                    </td>
                    <td class="text-end font-monospace text-success fw-bold fs-6">
                      ৳ {{ formatPrice(calculateFinalPrice(item.retail_price)) }}
                    </td>
                    <td class="text-center">
                      <span
                        :class="item.status === 'active' || item.status == 1 ? 'badge bg-success' : 'badge bg-secondary'"
                      >
                        {{ (item.status === 'active' || item.status == 1) ? $t('Active') : $t('Inactive') }}
                      </span>
                    </td>
                  </tr>

                  <tr v-if="paginatedItems.length === 0">
                    <td :colspan="data.applicable_on !== 'retail' ? 8 : 7" class="text-center py-4 text-muted">
                      <i class="fas fa-search fa-2x mb-2 text-secondary d-block"></i>
                      {{ $t("No matching products found for:") }} "<strong>{{ itemSearch }}</strong>"
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Table Pagination Controls -->
            <div class="p-3 bg-light border-top d-flex align-items-center justify-content-between flex-wrap gap-2" v-if="totalPages > 1">
              <span class="text-muted small">
                {{ $t("Showing") }} <strong>{{ (currentPage - 1) * pageSize + 1 }}</strong> - <strong>{{ Math.min(currentPage * pageSize, filteredItems.length) }}</strong> {{ $t("of") }} <strong>{{ filteredItems.length }}</strong> {{ $t("products") }}
              </span>

              <div class="d-flex align-items-center gap-1">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary px-3"
                  :disabled="currentPage <= 1"
                  @click="currentPage--"
                >
                  <i class="fas fa-chevron-left me-1"></i> {{ $t("Previous") }}
                </button>

                <span class="badge bg-white text-dark border px-3 py-2 font-monospace">
                  {{ $t("Page") }} {{ currentPage }} / {{ totalPages }}
                </span>

                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary px-3"
                  :disabled="currentPage >= totalPages"
                  @click="currentPage++"
                >
                  {{ $t("Next") }} <i class="fas fa-chevron-right ms-1"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State (When no items at all) -->
      <div class="col-12" v-else-if="data.scope === 'category' && (!data.items_details || data.items_details.length === 0)">
        <div class="card border-0 shadow-sm p-4 text-center bg-light">
          <i class="fas fa-box-open fa-3x text-secondary mb-2"></i>
          <h6 class="fw-bold text-dark">{{ $t("No products found in this category.") }}</h6>
          <p class="text-muted small mb-0">{{ $t("Add products to this category in the Items module to see them automatically receive this discount.") }}</p>
        </div>
      </div>

      <!-- 5. Bottom Navigation Actions -->
      <div class="col-12 mt-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 bg-white border rounded shadow-sm">
          <router-link :to="{ name: model + '.index' }" class="btn btn-outline-secondary px-4 fw-semibold">
            <i class="fas fa-arrow-left me-1"></i> {{ $t("Back to List") }}
          </router-link>

          <router-link
            :to="{ name: model + '.edit', params: { id: data.id } }"
            class="btn btn-primary px-4 fw-bold d-flex align-items-center gap-2"
          >
            <i class="fas fa-edit"></i> {{ $t("Edit Discount Campaign") }}
          </router-link>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "discount";

export default {
  data() {
    return {
      page_title: "Discount Details",
      model: model,
      data: {},
      fileColumns: [],
      itemSearch: "",
      currentPage: 1,
      pageSize: 15,
      table: {
        routes: {},
      },
    };
  },
  computed: {
    filteredItems() {
      const list = this.data.items_details || [];
      if (!this.itemSearch) return list;
      const q = this.itemSearch.toLowerCase().trim();
      return list.filter((item) => {
        const titleMatch = item.title && item.title.toLowerCase().includes(q);
        const barcodeMatch = item.barcode && String(item.barcode).toLowerCase().includes(q);
        return titleMatch || barcodeMatch;
      });
    },
    totalPages() {
      return Math.ceil(this.filteredItems.length / this.pageSize) || 1;
    },
    paginatedItems() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredItems.slice(start, start + this.pageSize);
    },
  },
  watch: {
    itemSearch() {
      this.currentPage = 1;
    },
    pageSize() {
      this.currentPage = 1;
    },
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return "-";
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
      } catch (e) {
        return dateStr;
      }
    },
    formatDateTime(dateStr) {
      if (!dateStr) return "-";
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString("en-GB", {
          day: "2-digit",
          month: "short",
          year: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
      } catch (e) {
        return dateStr;
      }
    },
    formatPrice(val) {
      const num = Number(val) || 0;
      return num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    calculateDiscountAmount(basePrice) {
      const price = Number(basePrice) || 0;
      const discVal = Number(this.data.discount_value) || 0;
      if (this.data.discount_type === "percentage") {
        return Number(((price * discVal) / 100).toFixed(2));
      }
      return Number(Math.min(price, discVal).toFixed(2));
    },
    calculateFinalPrice(basePrice) {
      const price = Number(basePrice) || 0;
      const discAmt = this.calculateDiscountAmount(price);
      return Number(Math.max(0, price - discAmt).toFixed(2));
    },
  },
  created() {
    this.page_title = `${this.headline(this.model)} Details`;
    this.setBreadcrumbs(this.model, "view");
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
.kpi-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 10px;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.kpi-icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
