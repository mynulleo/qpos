<template>
  <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
    <div class="row custom_row g-3">
      <div class="col-md-2">
        <fieldset>
          <span class="legend">{{ $t('Image') }}</span>
          <div class="view_file_item text-center" data-bs-toggle="tooltip" data-bs-placement="top"
            data-bs-title="Image">
            <div class="img">
              <img :src="data.image" alt="img-cover" style="max-width:100%">
            </div>
          </div>
        </fieldset>
      </div>
      <div class="col-md-10">
        <div class="row g-3">
          <div class="col-md-12">
            <fieldset>
              <span class="legend">{{ $t('Image') }}</span>
              <div class="table-responsive">
                <table class="table table-striped">
                  <tbody>
                    <tr>
                      <th width="30%">{{ $t('EmpID') }}</th>
                      <th width="5%">:</th>
                      <td>{{ data.empid }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Joining Date') }}</th>
                      <th>:</th>
                      <td>{{ data.joining_date }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Designation') }}</th>
                      <th>:</th>
                      <td>{{ data.designation?.title }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Full Name') }}</th>
                      <th>:</th>
                      <td>{{ data.full_name }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Email') }}</th>
                      <th>:</th>
                      <td>{{ data.email }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Mobile') }}</th>
                      <th>:</th>
                      <td>{{ data.mobile }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Date of Birth') }}</th>
                      <th>:</th>
                      <td>{{ data.dob }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Nid') }}</th>
                      <th>:</th>
                      <td>{{ data.nid }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Address') }}</th>
                      <th>:</th>
                      <td>{{ data.address }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Branch') }}</th>
                      <th>:</th>
                      <td>{{ data.office_branch?.branch_name }}</td>
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
          <div class="col-md-6">
            <fieldset class="mt-3">
              <span class="legend">{{ $t('Family Info') }}</span>
              <div class="table-responsive">
                <table class="table table-striped">
                  <tbody>
                    <tr>
                      <th>{{ $t('Father Name') }}</th>
                      <th width="5%">:</th>
                      <td>{{ data.father_name }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Father Occupation') }}</th>
                      <th>:</th>
                      <td>{{ data.father_occupation }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Father NID') }}</th>
                      <th>:</th>
                      <td>{{ data.father_nid }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Father Mobile') }}</th>
                      <th>:</th>
                      <td>{{ data.father_mobile }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Mother Name') }}</th>
                      <th>:</th>
                      <td>{{ data.mother_name }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Mother Occupation') }}</th>
                      <th>:</th>
                      <td>{{ data.mother_occupation }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Mother NID') }}</th>
                      <th>:</th>
                      <td>{{ data.mother_nid }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Mother Mobile') }}</th>
                      <th>:</th>
                      <td>{{ data.mother_mobile }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Spouse Name') }}</th>
                      <th>:</th>
                      <td>{{ data.spouse_name }}</td>
                    </tr>
                    <tr>
                      <th>{{ $t('Spouse Mobile') }}</th>
                      <th>:</th>
                      <td>{{ data.spouse_mobile }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </fieldset>
          </div>
          <div class="col-md-6">
            <div class="row">
              <div class="col-md-12">
                <fieldset class="mt-3">
                  <span class="legend">{{ $t('Salary Info') }}</span>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <tbody>
                        <tr v-for="(salary, index) in data.employee_salaries" :key="index">
                          <th>{{ salary.title }}</th>
                          <th width="5%">:</th>
                          <td>{{ salary.amount }}</td>
                        </tr>
                        <tr>
                          <th>{{ $t('Total') }}</th>
                          <th>:</th>
                          <td><strong>{{ data.salary }}</strong></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </fieldset>
              </div>
              <div class="col-md-12">
                <fieldset class="mt-3">
                  <span class="legend">{{ $t('Bank Information') }}</span>
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
                          <td>{{ data.branch_name }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </fieldset>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </view-page>
</template>

<script>

const model = "employee";

export default {
  data() {
    return {
      page_title: "",
      model: model,
      data: {

      },
      fileColumns: [{ field: "image", title: "Image" },],
    };
  },
  created() {
    this.page_title = `${this.headline(this.model)} View`;
    this.get_data(`${this.model}/${this.$route.params.id}`);
  },
};
</script>