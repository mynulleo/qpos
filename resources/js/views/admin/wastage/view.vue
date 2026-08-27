<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="container">
      <div class="row">
        <!-- Left Master Info -->
        <div class="col-6">
          <table class="table table-striped">
            <tbody>
              <tr>
                <th width="35%">Audit Number</th>
                <th width="5%">:</th>
                <td>{{ data.audit_number }}</td>
              </tr>
              <tr>
                <th>Audit Date</th>
                <th>:</th>
                <td>{{ data.audit_date }}</td>
              </tr>
              <tr>
                <th>Auditor (Team)</th>
                <th>:</th>
                <td>{{ data.audited_by || 'N/A' }}</td>
              </tr>
              <tr>
                <th>Lead Auditor</th>
                <th>:</th>
                <td>{{ data.auditor?.title || data.auditor?.full_name || 'N/A' }}</td>
              </tr>
              <tr v-if="data.branch">
                <th>Branch</th>
                <th>:</th>
                <td>{{ data.branch?.title }}</td>
              </tr>
              <tr>
                <th>Created By</th>
                <th>:</th>
                <td>{{ data.creator?.name || 'System Admin' }}</td>
              </tr>
              <tr v-if="data.note">
                <th>Note / Remarks</th>
                <th>:</th>
                <td>{{ data.note }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Right Master Info -->
        <div class="col-6">
          <table class="table table-striped">
            <tbody>
              <tr>
                <th width="35%">Total Quantity</th>
                <th width="5%">:</th>
                <td>{{ data.total_qty }}</td>
              </tr>
              <tr>
                <th>Total Loss Amount</th>
                <th>:</th>
                <td>{{ data.total_loss_amount }}</td>
              </tr>
              <tr>
                <th>Status</th>
                <th>:</th>
                <td>
                  <span class="badge bg-success" v-if="data.approved_by || data.status === 'approved'">Approved</span>
                  <span class="badge bg-secondary" v-else>Pending</span>
                </td>
              </tr>
              <tr>
                <th>Approved By</th>
                <th>:</th>
                <td>{{ data.approved_admin?.name || data.approved_admin?.full_name || 'N/A' }}</td>
              </tr>
              <tr>
                <th>Approved Date</th>
                <th>:</th>
                <td>{{ data.approved_date || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Wastage Details Table -->
        <div class="col-12">
          <h4 class="mt-3">Wastage Details</h4>
          <table class="table table-striped table-bordered align-middle">
            <thead>
              <tr>
                <th width="5%" class="text-center">#</th>
                <th width="15%">Category</th>
                <th width="25%">Item</th>
                <th width="10%" class="text-center">Variant</th>
                <th width="12%">Reason</th>
                <th width="10%" class="text-center">Expired Date</th>
                <th width="8%" class="text-center">Quantity</th>
                <th width="8%" class="text-end">Unit Cost</th>
                <th width="8%" class="text-end">Total Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in data.wastage_details" :key="index">
                <td class="text-center">{{ index + 1 }}</td>
                <td>{{ item.category?.title || '-' }}</td>
                <td>
                  <span class="fw-bold">{{ item.item?.title || 'Item #' + item.item_id }}</span>
                  <div class="small text-muted" v-if="item.item?.barcode">
                    Barcode: {{ item.item?.barcode }}
                  </div>
                  <div class="small text-primary" v-if="isElectronicsShop && item.serial_no">
                    <span class="badge bg-light text-primary border">
                      S/N: {{ item.serial_no }}
                    </span>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge bg-light text-dark border me-1" v-if="item.color">{{ item.color?.title }}</span>
                  <span class="badge bg-light text-dark border" v-if="item.size">{{ item.size?.title }}</span>
                  <span class="text-muted small" v-if="!item.color && !item.size">-</span>
                </td>
                <td>
                  <span>{{ item.reason || '-' }}</span>
                  <div v-if="item.remarks" class="small text-muted fst-italic">{{ item.remarks }}</div>
                </td>
                <td class="text-center">{{ item.expired_date || '-' }}</td>
                <td class="text-center fw-bold">{{ item.quantity }}</td>
                <td class="text-end">{{ item.unit_price }}</td>
                <td class="text-end fw-bold">{{ item.total_amount }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="fw-bold">
                <td colspan="6" class="text-end">Grand Total:</td>
                <td class="text-center">{{ data.total_qty }}</td>
                <td></td>
                <td class="text-end">{{ data.total_loss_amount }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Approval Card Section -->
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-semibold">Approval Information</h6>
              <span class="badge bg-secondary" v-if="!data.approved_by && data.status !== 'approved'">Pending</span>
              <span class="badge bg-success" v-else>Approved</span>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-12">
                  <!-- Guidelines + Action Button -->
                  <div class="alert alert-info border-0 shadow-sm mb-0">
                    <h6 class="alert-heading fw-semibold mb-2">Wastage Approval Guidelines</h6>
                    <p class="mb-1">
                      Please carefully review the wastage audit information above and verify the items.
                    </p>
                    <p class="mb-1">
                      Once approved, the items will be deducted from current stock and the approver's information will be recorded.
                    </p>
                    <p class="mb-3 text-danger fw-semibold">
                      If approval is canceled, stock will be automatically restored.
                    </p>

                    <!-- Action Buttons Inside Alert -->
                    <div class="d-flex justify-content-end" v-if="!data.approved_by && data.status !== 'approved'">
                      <router-link v-if="$root.checkPermission('wastage.approved')" to="#"
                        @click.native.prevent="approved" class="btn btn-success fw-semibold px-4">
                        <i class="bi bi-check-circle me-1"></i> Approve Wastage
                      </router-link>
                    </div>
                    <div class="d-flex justify-content-end" v-else>
                      <router-link v-if="$root.checkPermission('wastage.approvalcancel')" to="#"
                        @click.native.prevent="approvalcancel" class="btn btn-danger fw-semibold px-4">
                        <i class="bi bi-x-circle me-1"></i> Cancel Approval
                      </router-link>
                    </div>
                  </div>
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
      fileColumns: [],
    };
  },

  computed: {
    isElectronicsShop() {
      const shopType = this.$root.site?.shop_type;
      return !shopType || shopType === 'electronics';
    },
  },

  methods: {
    approved() {
      if (!confirm('Are you sure you want to approve this wastage audit?')) {
        return;
      }
      axios.post('wastage/approved', {
        wastage_id: this.$route.params.id
      })
      .then(res => {
        this.$toast(
          res.data.message || 'Approved successfully',
          "success"
        );
        this.get_data(`${this.model}/${this.$route.params.id}`);
      })
      .catch(err => {
        this.$toast(err.response?.data?.message || 'Approval failed', 'error');
      });
    },

    approvalcancel() {
      if (!confirm('Are you sure you want to cancel the approval of this wastage?')) {
        return;
      }

      axios.post('wastage/approvalcancel', {
        wastage_id: this.$route.params.id
      })
      .then(res => {
        this.$toast(
          res.data.message || 'Approval cancelled successfully',
          "success"
        );
        this.get_data(`${this.model}/${this.$route.params.id}`);
      })
      .catch(err => {
        this.$toast(err.response?.data?.message || 'Approval cancel failed', 'error');
      });
    },
  },

  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>
