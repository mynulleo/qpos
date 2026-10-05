<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="row custom_row g-3">
      <div class="col-md-4">
        <fieldset>
          <span class="legend">{{ $t('Voucher Information') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <tbody>
                <tr>
                  <th>{{ $t('Voucher No') }}</th>
                  <th width="5%">:</th>
                  <td>{{ data.voucherno }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Voucher Type') }}</th>
                  <th>:</th>
                  <td>{{ data.voucher_type }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Voucher Date') }}</th>
                  <th>:</th>
                  <td>{{ data.voucher_date }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Financial Year') }}</th>
                  <th>:</th>
                  <td>{{ data.financial_year?.title }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Narration') }}</th>
                  <th>:</th>
                  <td>{{ data.narration }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Payslipno') }}</th>
                  <th>:</th>
                  <td>{{ data.payment?.payslipno }}</td>
                </tr>
                <tr>
                  <th>{{ $t('Payment') }}</th>
                  <th>:</th>
                  <td>{{ data.payment?.payment_date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </fieldset>
      </div>
      <div class="col-md-8">
        <fieldset>
          <span class="legend">{{ $t('Voucher Information') }}</span>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ $t('Account Type') }}</th>
                  <th>{{ $t('Account Name') }}</th>
                  <th>{{ $t('Dr. Amount') }}</th>
                  <th>{{ $t('Cr. Amount') }}</th>
                  <th>{{ $t('Reference Type') }}</th>
                  <th>{{ $t('Reference ID') }}</th>
                  <th>{{ $t('Narration') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in data.voucher_details" :key="index">
                  <td>{{ item.account_type }}</td>
                  <td>{{ item.account?.account_name }}</td>
                  <td>{{ item.dr_amount }}</td>
                  <td>{{ item.cr_amount }}</td>
                  <td>{{ item.reference_type }}</td>
                  <td>{{ item.reference_id }}</td>
                  <td>{{ item.line_narration }}</td>
                </tr>
                <tr>
                  <td colspan="2"><strong>Total</strong></td>
                  <td><strong>{{ totalDr.toFixed(2) }}</strong></td>
                  <td><strong>{{ totalCr.toFixed(2) }}</strong></td>
                  <td colspan="3"></td>
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

const model = "voucher";

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
  computed: {
    totalDr() {
      if (!this.data?.voucher_details) return 0;
      return this.data.voucher_details.reduce((sum, item) => {
        return sum + (parseFloat(item.dr_amount) || 0);
      }, 0);
    },
    totalCr() {
      if (!this.data?.voucher_details) return 0;
      return this.data.voucher_details.reduce((sum, item) => {
        return sum + (parseFloat(item.cr_amount) || 0);
      }, 0);
    },
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>