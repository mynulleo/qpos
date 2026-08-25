<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="container-fluid px-3">
      <!-- Top Action Bar -->
      <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
          <h4 class="mb-0 fw-bold text-dark">
            <i class="fas fa-dumpster text-danger me-2"></i>Wastage Audit: <span class="font-monospace text-primary">{{ data.audit_number }}</span>
          </h4>
          <span class="badge fs-6 px-3 py-1" :class="data.approved_by || data.status === 'approved' ? 'bg-success' : 'bg-warning text-dark'">
            {{ data.approved_by || data.status === 'approved' ? 'Approved (Stock Out)' : 'Pending Approval' }}
          </span>
        </div>

        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" @click="printAudit">
            <i class="fas fa-print me-1"></i> Print Audit Slip
          </button>
          <router-link :to="{ name: 'wastage.index' }" class="btn btn-outline-dark btn-sm px-3 fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Back to List
          </router-link>
        </div>
      </div>

      <!-- Audit Master Info Cards -->
      <div class="row g-3 mb-4" id="printableArea">
        <div class="col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light py-2 fw-semibold text-primary">
              <i class="fas fa-info-circle me-1"></i> Audit Information
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless align-middle mb-0">
                <tbody>
                  <tr>
                    <th width="35%" class="text-muted">Audit Number:</th>
                    <td class="fw-bold font-monospace text-primary fs-6">{{ data.audit_number }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Audit Date:</th>
                    <td class="fw-semibold">{{ data.audit_date }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Audited By (Team):</th>
                    <td>{{ data.audited_by || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Lead Auditor:</th>
                    <td>
                      <span class="badge bg-secondary" v-if="data.auditor">
                        <i class="fas fa-user-check me-1"></i>{{ data.auditor.full_name }}
                      </span>
                      <span v-else class="text-muted">N/A</span>
                    </td>
                  </tr>
                  <tr v-if="data.branch">
                    <th class="text-muted">Branch:</th>
                    <td>{{ data.branch.title }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Created By:</th>
                    <td>{{ data.creator ? data.creator.name : 'System Admin' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light py-2 fw-semibold text-danger">
              <i class="fas fa-calculator me-1"></i> Wastage Financial & Stock Summary
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless align-middle mb-0">
                <tbody>
                  <tr>
                    <th width="40%" class="text-muted">Total Item Lines:</th>
                    <td class="fw-bold">{{ data.wastage_details ? data.wastage_details.length : 0 }} items</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Total Wasted Quantity:</th>
                    <td class="fw-bold text-warning fs-6">{{ data.total_qty }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Estimated Loss Value:</th>
                    <td class="fw-bold text-danger fs-5 font-monospace">{{ $root.currency(data.total_loss_amount) }}</td>
                  </tr>
                  <tr>
                    <th class="text-muted">Approval Status:</th>
                    <td>
                      <span v-if="data.approved_by || data.status === 'approved'" class="badge bg-success">
                        <i class="fas fa-check-circle me-1"></i>Approved
                      </span>
                      <span v-else class="badge bg-warning text-dark">
                        <i class="fas fa-clock me-1"></i>Pending Approval
                      </span>
                    </td>
                  </tr>
                  <tr v-if="data.approved_admin">
                    <th class="text-muted">Approved By:</th>
                    <td class="text-success fw-bold">{{ data.approved_admin.full_name }}</td>
                  </tr>
                  <tr v-if="data.approved_date">
                    <th class="text-muted">Approved Date:</th>
                    <td class="text-muted small">{{ data.approved_date }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Note / Remarks if present -->
        <div class="col-12" v-if="data.note">
          <div class="alert alert-light border shadow-sm p-3 mb-0">
            <h6 class="fw-bold text-muted mb-1"><i class="fas fa-comment-alt me-1"></i>Audit Notes / Remarks:</h6>
            <p class="mb-0 text-dark">{{ data.note }}</p>
          </div>
        </div>

        <!-- Detailed Items Table -->
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold"><i class="fas fa-boxes me-2"></i>Wasted Items Breakdown</h6>
              <span class="badge bg-danger">{{ data.wastage_details ? data.wastage_details.length : 0 }} Line Items</span>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light text-center" style="font-size: 13px;">
                  <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 14%;">Category</th>
                    <th style="width: 22%;">Item Title / Barcode</th>
                    <th style="width: 10%;">Variant</th>
                    <th style="width: 14%;">Reason</th>
                    <th style="width: 11%;">Expired Date</th>
                    <th style="width: 8%;">Quantity</th>
                    <th style="width: 8%;">Unit Cost</th>
                    <th style="width: 8%;">Loss Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in data.wastage_details" :key="index">
                    <td class="text-center fw-bold">{{ index + 1 }}</td>
                    <td>{{ item.category ? item.category.title : '-' }}</td>
                    <td>
                      <span class="fw-bold text-dark">{{ item.item ? item.item.title : 'Item #' + item.item_id }}</span>
                      <div class="small text-muted font-monospace" v-if="item.item && item.item.barcode">
                        Barcode: {{ item.item.barcode }}
                      </div>
                      <div class="small text-secondary" v-if="item.serial_no">
                        <i class="fas fa-barcode me-1"></i>Serials: {{ item.serial_no }}
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-light text-dark border me-1" v-if="item.color">{{ item.color.title }}</span>
                      <span class="badge bg-light text-dark border" v-if="item.size">{{ item.size.title }}</span>
                      <span class="text-muted small" v-if="!item.color && !item.size">Standard</span>
                    </td>
                    <td>
                      <span class="badge" :class="getReasonBadgeClass(item.reason)">
                        {{ item.reason || 'Damaged' }}
                      </span>
                      <div v-if="item.remarks" class="small text-muted fst-italic mt-1">{{ item.remarks }}</div>
                    </td>
                    <td class="text-center">
                      <span v-if="item.expired_date" class="badge bg-danger bg-opacity-10 text-danger fw-bold">
                        <i class="fas fa-calendar-times me-1"></i>{{ item.expired_date }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td class="text-center fw-bold fs-6 text-dark">{{ item.quantity }}</td>
                    <td class="text-end font-monospace">{{ $root.currency(item.unit_price) }}</td>
                    <td class="text-end font-monospace fw-bold text-danger">{{ $root.currency(item.total_amount) }}</td>
                  </tr>
                </tbody>
                <tfoot class="table-light">
                  <tr class="fw-bold">
                    <td colspan="6" class="text-end">Grand Total:</td>
                    <td class="text-center text-warning fs-6">{{ data.total_qty }}</td>
                    <td></td>
                    <td class="text-end text-danger fs-6 font-monospace">{{ $root.currency(data.total_loss_amount) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- Approval Workflow Section -->
        <div class="col-12 mt-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
              <h6 class="mb-0 fw-bold">
                <i class="fas fa-user-shield me-2 text-primary"></i>Approval & Stock-Out Workflow (অনুমোদন প্রক্রিয়া)
              </h6>
              <span class="badge px-3 py-2 fs-6" :class="data.approved_by || data.status === 'approved' ? 'bg-success' : 'bg-secondary'">
                {{ data.approved_by || data.status === 'approved' ? 'Approved & Stock Deducted' : 'Pending Approval' }}
              </span>
            </div>
            <div class="card-body p-4">
              <!-- Guidelines Alert Box -->
              <div class="alert alert-info border-0 shadow-sm mb-0">
                <h6 class="alert-heading fw-bold mb-2">
                  <i class="fas fa-info-circle me-1"></i> Wastage Approval & Stock Out Guidelines
                </h6>
                <p class="mb-1 text-dark">
                  Please review the wasted items and quantities carefully before approving.
                </p>
                <p class="mb-1 text-danger fw-semibold">
                  <i class="fas fa-exclamation-triangle me-1"></i> Approving this wastage will <strong>automatically deduct the wasted quantities from current stock</strong> in real time.
                </p>
                <p class="mb-3 text-muted small">
                  The approver's name and approval timestamp will be permanently logged. If approval is canceled later, the stock will be restored automatically.
                </p>

                <!-- Action Buttons Inside Alert -->
                <div class="d-flex justify-content-end gap-2 pt-2 border-top" v-if="!data.approved_by && data.status !== 'approved'">
                  <router-link v-if="$root.checkPermission('wastage.approved')" to="#"
                    @click.native.prevent="approved" class="btn btn-success fw-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> Approve Wastage (স্টক আউট করুন)
                  </router-link>
                  <span v-else class="text-muted small fst-italic">
                    <i class="fas fa-lock me-1"></i> You do not have permission to approve wastage.
                  </span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top" v-else>
                  <div class="text-success fw-semibold small">
                    <i class="fas fa-check-double me-1"></i> Approved by <strong>{{ data.approved_admin?.full_name }}</strong> on {{ data.approved_date }}.
                  </div>
                  <router-link v-if="$root.checkPermission('wastage.approvalcancel')" to="#"
                    @click.native.prevent="approvalcancel" class="btn btn-danger fw-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-times-circle me-1"></i> Cancel Approval (স্টক ফিরিয়ে আনুন)
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </view-page>
</template>

<script>
const model = "wastage";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
    };
  },

  methods: {
    getReasonBadgeClass(reason) {
      switch (reason) {
        case 'Expired':
          return 'bg-danger';
        case 'Broken / Shattered':
        case 'Damaged in Storage':
          return 'bg-warning text-dark';
        case 'Spoiled / Rotten':
          return 'bg-dark text-white';
        case 'Lost / Theft':
        case 'Audit Shortage':
          return 'bg-secondary';
        default:
          return 'bg-info text-dark';
      }
    },

    approved() {
      if (!confirm('Are you sure you want to approve this wastage audit?\n\nThis will immediately DEDUCT stock for all listed items.')) {
        return;
      }

      axios.post('wastage/approved', {
        wastage_id: this.$route.params.id
      })
      .then(res => {
        this.$toast(
          res.data.message || 'Wastage approved and stock deducted successfully',
          "success"
        );
        this.get_data(`${this.model}/${this.$route.params.id}`);
      })
      .catch(err => {
        this.$toast(err.response?.data?.message || 'Approval failed', 'error');
      });
    },

    approvalcancel() {
      if (!confirm('Are you sure you want to cancel the approval of this wastage audit?\n\nThis will RESTORE the deducted stock for all listed items.')) {
        return;
      }

      axios.post('wastage/approvalcancel', {
        wastage_id: this.$route.params.id
      })
      .then(res => {
        this.$toast(
          res.data.message || 'Wastage approval cancelled and stock restored successfully',
          "success"
        );
        this.get_data(`${this.model}/${this.$route.params.id}`);
      })
      .catch(err => {
        this.$toast(err.response?.data?.message || 'Cancelling approval failed', 'error');
      });
    },

    printAudit() {
      window.print();
    }
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printableArea, #printableArea * {
    visibility: visible;
  }
  #printableArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
