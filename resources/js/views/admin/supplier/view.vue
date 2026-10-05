<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="row custom_row g-3">
      <div class="col-md-6">
        <fieldset>
          <span class="legend">{{ $t('Basic Information') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th>{{ $t('SUP ID') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.supid }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Organization') }}</th>
                  <th>:</th>
                  <td>{{ data.org_name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Contact Person') }}</th>
                  <th>:</th>
                  <td>{{ data.name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Designation') }}</th>
                  <th>:</th>
                  <td>{{ data.designation }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Email') }}</th>
                  <th>:</th>
                  <td>{{ data.email }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Address') }}</th>
                  <th>:</th>
                  <td>{{ data.address }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </fieldset>
      </div>
      <div class="col-md-6">
        <fieldset>
          <span class="legend">{{ $t('Office Information') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th>{{ $t('Account') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.account_name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Account No') }}</th>
                  <th>:</th>
                  <td>{{ data.account_no }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Bank') }}</th>
                  <th>:</th>
                  <td>{{ data.bank?.name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Branch') }}</th>
                  <th>:</th>
                  <td>{{ data.branch }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Routing') }}</th>
                  <th>:</th>
                  <td>{{ data.routing }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Website') }}</th>
                  <th>:</th>
                  <td>{{ data.website }}</td>
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

const model = "supplier";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {

      },
      fileColumns: [],
    };
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>