<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="row custom_row g-3">
      <div class="col-md-4">
        <fieldset>
          <span class="legend">{{ $t('Office Information') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th>{{ $t('ClientID') }}</th>
                  <th>:</th>
                  <td>{{ data.clientid }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Reg Date') }}</th>
                  <th>:</th>
                  <td>{{ data.reg_date }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Note') }}</th>
                  <th>:</th>
                  <td>{{ data.note }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Status') }}</th>
                  <th>:</th>
                  <td>{{ data.status }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Created At') }}</th>
                  <th>:</th>
                  <td>{{ data.created_at }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Updated At') }}</th>
                  <th>:</th>
                  <td>{{ data.updated_at }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </fieldset>
      </div>
      <div class="col-md-4">
        <fieldset>
          <span class="legend">{{ $t('Client Information') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th>{{ $t('Client Name') }}</th>
                  <th>:</th>
                  <td>{{ data.org_name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Contact Information') }}</th>
                  <th>:</th>
                  <td>{{ data.name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Mobile') }}</th>
                  <th>:</th>
                  <td>{{ data.mobile }}</td>
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
                <tr>
                  <th>{{ $t('District') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.district?.district_name }} ({{ data.area?.area_name }})</td>
                </tr>
                <tr>
                  <th>{{ $t('Status') }}</th>
                  <th>:</th>
                  <td>{{ data.status }}</td>
                </tr>

              </tbody>
            </table>
          </div>
        </fieldset>
      </div>
      <div class="col-md-4">
        <fieldset>
          <span class="legend">{{ $t('Bank Info') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th>{{ $t('Account Name') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.account_name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Account No') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.account_no }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Bank') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.bank?.bank_name }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Branch') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.branch }}</td>
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

const model = "client";

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