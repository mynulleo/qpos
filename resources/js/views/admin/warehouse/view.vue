<template>
  <view-page>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-warehouse me-2"></i>Warehouse Information</h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped mb-0">
              <tbody>
                <tr>
                  <th width="40%">{{ $t('Warehouse Name') }}</th>
                  <td><strong>{{ data.name }}</strong></td>
                </tr>
                <tr>
                  <th>{{ $t('Warehouse Code') }}</th>
                  <td><span class="badge bg-secondary font-monospace">{{ data.code || 'N/A' }}</span></td>
                </tr>
                <tr>
                  <th>{{ $t('Branch') }}</th>
                  <td>{{ data.branch ? data.branch.title : 'All Branches' }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Capacity') }}</th>
                  <td>{{ data.capacity || 'Not Specified' }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Status') }}</th>
                  <td>
                    <span :class="data.status === 'active' ? 'badge bg-success' : 'badge bg-danger'">
                      {{ data.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-address-book me-2"></i>Contact & Address Details</h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped mb-0">
              <tbody>
                <tr>
                  <th width="40%">{{ $t('Contact Person') }}</th>
                  <td><strong>{{ data.contact_person || 'N/A' }}</strong></td>
                </tr>
                <tr>
                  <th>{{ $t('Contact Person Mobile') }}</th>
                  <td>{{ data.contact_person_phone || 'N/A' }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Warehouse Phone') }}</th>
                  <td>{{ data.phone || 'N/A' }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Email') }}</th>
                  <td>{{ data.email || 'N/A' }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Physical Address') }}</th>
                  <td>{{ data.address || 'N/A' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "warehouse";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
      fileColumns: [],
    };
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>
