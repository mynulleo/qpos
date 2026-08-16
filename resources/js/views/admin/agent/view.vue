<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="row custom_row g-3">
      <div class="col-md-4 text-center">
        <fieldset>
          <span class="legend">Agent Image</span>
          <div class="p-3">
            <img v-if="data.image" :src="data.image" class="img-fluid rounded border" style="max-height: 200px;" alt="Agent Image" />
            <p v-else class="text-muted">No Image Available</p>
          </div>
        </fieldset>
      </div>
      <div class="col-md-8">
        <fieldset>
          <span class="legend">Agent Information</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th width="30%">Full Name</th>
                  <th width="5%">:</th>
                  <td>{{ data.full_name }}</td>
                </tr>
                <tr>
                  <th>Mobile</th>
                  <th>:</th>
                  <td>{{ data.mobile }}</td>
                </tr>
                <tr>
                  <th>Organization</th>
                  <th>:</th>
                  <td>{{ data.organization }}</td>
                </tr>
                <tr>
                  <th>Designation</th>
                  <th>:</th>
                  <td>{{ data.designation }}</td>
                </tr>
                <tr>
                  <th>Address</th>
                  <th>:</th>
                  <td>{{ data.address }}</td>
                </tr>
                <tr>
                  <th>Status</th>
                  <th>:</th>
                  <td>
                    <span :class="data.status === 'active' ? 'badge bg-success' : 'badge bg-danger'">
                      {{ data.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </fieldset>
      </div>
    </div>
  </view-page>
</template>

<script>
const model = "agent";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {},
    };
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>
