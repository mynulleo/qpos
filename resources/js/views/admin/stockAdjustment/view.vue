<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="container-fluid px-2" v-if="data && data.adjustment_no">
      <!-- Top Action Bar -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
        <div class="d-flex align-items-center gap-3">
          <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
            <i class="fas fa-sliders-h fa-2x"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
              Adjustment #{{ data.adjustment_no }}
              <span class="badge bg-success fs-6" v-if="data.status === 'active'">Active</span>
              <span class="badge bg-danger fs-6" v-else>Inactive</span>
            </h4>
            <div class="text-muted small mt-1 font-monospace">
              <span class="me-3"><i class="fas fa-calendar-alt text-primary me-1"></i> Date: <strong>{{ data.adjustment_date }}</strong></span>
              <span class="me-3"><i class="fas fa-tag text-info me-1"></i> Type: <strong>{{ data.adjustment_type }}</strong></span>
              <span v-if="data.conducted_by"><i class="fas fa-user-check text-success me-1"></i> By: <strong>{{ data.conducted_by.full_name || data.conducted_by.name }}</strong></span>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold" @click="print('printArea', model)">
            <i class="fas fa-print me-1"></i> Print Voucher
          </button>
          <router-link :to="{ name: model + '.index' }" class="btn btn-outline-dark btn-sm px-3 fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Back to List
          </router-link>
        </div>
      </div>

      <!-- Printable Area -->
      <div id="printArea">
        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
          <!-- 1. Total Items -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 bg-white">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Adjusted Items</div>
                    <h3 class="mb-0 fw-bold text-primary">{{ data.details ? data.details.length : data.total_items }}</h3>
                  </div>
                  <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                    <i class="fas fa-boxes fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Stock In Additions -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 bg-white">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Added Stock</div>
                    <h3 class="mb-0 fw-bold text-success">+{{ totalQtyIn }}</h3>
                  </div>
                  <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                    <i class="fas fa-arrow-up fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Stock Out Deductions -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100 bg-white">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Deducted Stock</div>
                    <h3 class="mb-0 fw-bold text-danger">-{{ totalQtyOut }}</h3>
                  </div>
                  <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                    <i class="fas fa-arrow-down fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Total Valuation Impact -->
          <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100 bg-white">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Financial Value</div>
                    <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $filter.formatBDT(data.total_amount) }}</h3>
                  </div>
                  <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
                    <i class="fas fa-coins fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Master Adjustment Info Card -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-light py-2 fw-bold text-dark">
            <i class="fas fa-info-circle me-1 text-primary"></i> Adjustment Details
          </div>
          <div class="card-body p-3">
            <div class="row g-3">
              <div class="col-md-3">
                <span class="text-muted small d-block">Adjustment No:</span>
                <strong class="font-monospace text-dark">{{ data.adjustment_no }}</strong>
              </div>
              <div class="col-md-3">
                <span class="text-muted small d-block">Adjustment Date:</span>
                <strong>{{ data.adjustment_date }}</strong>
              </div>
              <div class="col-md-3">
                <span class="text-muted small d-block">Adjustment Type:</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border">{{ data.adjustment_type }}</span>
              </div>
              <div class="col-md-3">
                <span class="text-muted small d-block">Conducted By:</span>
                <strong>{{ data.conducted_by ? (data.conducted_by.full_name || data.conducted_by.name) : 'Admin' }}</strong>
              </div>
              <div class="col-md-6" v-if="data.reason">
                <span class="text-muted small d-block">Reason:</span>
                <div class="text-dark">{{ data.reason }}</div>
              </div>
              <div class="col-md-6" v-if="data.remarks">
                <span class="text-muted small d-block">Remarks:</span>
                <div class="text-muted">{{ data.remarks }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Itemized Matrix Table -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-dark text-white py-2 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-1"></i> Adjusted Items Breakdown</span>
            <span class="badge bg-primary rounded-pill">{{ data.details ? data.details.length : 0 }} Items</span>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0 text-center">
              <thead class="table-light">
                <tr>
                  <th width="4%">#</th>
                  <th width="26%" class="text-start">Product (পণ্য)</th>
                  <th width="14%">Variant (ভ্যারিয়েন্ট)</th>
                  <th width="10%">Unit Cost</th>
                  <th width="12%">System Stock (পূর্বে)</th>
                  <th width="12%">Physical Count (বাস্তব)</th>
                  <th width="12%">Difference (সমন্বয়)</th>
                  <th width="10%" class="text-end">Total Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.details" :key="index">
                  <td class="fw-bold text-muted">{{ index + 1 }}</td>
                  <td class="text-start">
                    <div class="fw-bold text-dark">{{ item.item ? item.item.title : 'Item #' + item.item_id }}</div>
                    <div class="small text-muted font-monospace" v-if="item.item && item.item.barcode">
                      <i class="fas fa-barcode me-1"></i>{{ item.item.barcode }}
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border me-1" v-if="item.color">
                      {{ item.color.title }}
                    </span>
                    <span class="badge bg-light text-dark border" v-if="item.size">
                      {{ item.size.title }}
                    </span>
                    <span class="text-muted small" v-if="!item.color && !item.size">Standard</span>
                  </td>
                  <td class="font-monospace">{{ $filter.formatBDT(item.unit_cost) }}</td>
                  <td class="font-monospace fw-bold">{{ item.system_qty }}</td>
                  <td class="font-monospace fw-bold table-warning">{{ item.physical_qty }}</td>
                  <td>
                    <span
                      v-if="item.difference_qty > 0"
                      class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 font-monospace"
                    >
                      <i class="fas fa-arrow-up me-1"></i>+{{ item.difference_qty }} (In)
                    </span>
                    <span
                      v-else-if="item.difference_qty < 0"
                      class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 font-monospace"
                    >
                      <i class="fas fa-arrow-down me-1"></i>{{ item.difference_qty }} (Out)
                    </span>
                    <span
                      v-else
                      class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace"
                    >
                      0 (Matched)
                    </span>
                  </td>
                  <td class="font-monospace fw-bold text-end">{{ $filter.formatBDT(item.total_amount) }}</td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="4" class="text-end">Total Summary:</td>
                  <td class="text-center font-monospace">{{ totalSystemQty }}</td>
                  <td class="text-center font-monospace table-warning">{{ totalPhysicalQty }}</td>
                  <td class="text-center font-monospace">
                    <span class="text-success">+{{ totalQtyIn }}</span> / <span class="text-danger">-{{ totalQtyOut }}</span>
                  </td>
                  <td class="text-end font-monospace fs-6 text-primary">{{ $filter.formatBDT(data.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Signature Footer for Print -->
        <div class="row mt-5 pt-4 d-none d-print-flex justify-content-between text-center">
          <div class="col-4">
            <div class="border-top border-dark pt-2 fw-bold">
              Conducted / Prepared By
            </div>
          </div>
          <div class="col-4">
            <div class="border-top border-dark pt-2 fw-bold">
              Inventory Manager
            </div>
          </div>
          <div class="col-4">
            <div class="border-top border-dark pt-2 fw-bold">
              Authorized Signature
            </div>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = 'stockAdjustment';

export default {
  computed: {
    totalQtyIn() {
      if (!this.data || !this.data.details) return 0;
      return this.data.details
        .filter(d => Number(d.difference_qty) > 0)
        .reduce((sum, d) => sum + Number(d.difference_qty), 0);
    },
    totalQtyOut() {
      if (!this.data || !this.data.details) return 0;
      return this.data.details
        .filter(d => Number(d.difference_qty) < 0)
        .reduce((sum, d) => sum + Math.abs(Number(d.difference_qty)), 0);
    },
    totalSystemQty() {
      if (!this.data || !this.data.details) return 0;
      return this.data.details.reduce((sum, d) => sum + Number(d.system_qty || 0), 0);
    },
    totalPhysicalQty() {
      if (!this.data || !this.data.details) return 0;
      return this.data.details.reduce((sum, d) => sum + Number(d.physical_qty || 0), 0);
    }
  },

  data() {
    return {
      model: model,
      page_title: 'Stock Adjustment View',
      data: {},
    };
  },

  methods: {
    get_data(id) {
      axios.get(`${this.model}/${id}`)
        .then((res) => {
          this.data = res.data;
          this.page_title = `Stock Adjustment - ${res.data.adjustment_no}`;
        });
    }
  },

  created() {
    if (this.$route.params.id) {
      this.get_data(this.$route.params.id);
    }
  }
};
</script>
