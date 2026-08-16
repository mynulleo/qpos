<template>
    <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false" printArea="challanPrintArea">
        <div class="my-5" id="challanPrintArea">
            <div class="text-center mb-2 report-title">
                <h3 class="fw-bold">{{ $root.site.title }}.</h3>
                <p class="mb-1">{{ $root.site.address }}</p>
                <p>Email: {{ $root.site.contact_email }} | Phone: {{ $root.site.mobile1 }}</p>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div style="width:100%">
                    <h5 class="fw-bold mb-1 text-center" style="width:100%;">DELIVERY CHALLAN</h5>
                    <small class="text-muted">Report Date: <strong>{{ reportDate }}</strong></small>
                </div>
                <div class="text-end">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-sm-6 col-sx-6">
                    <fieldset>
                        <table class="table table-report">
                            <tbody>
                                <tr>
                                    <td width="30%">Challan No</td>
                                    <td width="3%">:</td>
                                    <td>{{ data.challan_no }}</td>
                                </tr>
                                <tr>
                                    <td>Challan Date</td>
                                    <td>:</td>
                                    <td>{{ data.challan_date }}</td>
                                </tr>
                                <tr>
                                    <td>Order NO</td>
                                    <td>:</td>
                                    <td>{{ data.workorder?.order_no }}</td>
                                </tr>
                                <tr>
                                    <td>UNO NO</td>
                                    <td>:</td>
                                    <td>{{ data.workorder?.uno_no }}</td>
                                </tr>
                                <tr>
                                    <td>Order Date</td>
                                    <td>:</td>
                                    <td>{{ data.workorder?.order_date }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <div class="col-md-6 col-sm-6 col-sx-6">
                    <fieldset>
                        <table class="table table-report">
                            <tbody>
                                <tr>
                                    <td width="30%">Client</td>
                                    <td width="3%">:</td>
                                    <td>{{ data.client?.org_name }}</td>
                                </tr>
                                <tr>
                                    <td>Shipping</td>
                                    <td>:</td>
                                    <td>{{ data.shipping }}</td>
                                </tr>
                                <tr>
                                    <td>Receive By</td>
                                    <td>:</td>
                                    <td>{{ data.receive_by }}</td>
                                </tr>
                                <tr>
                                    <td>Designation</td>
                                    <td>:</td>
                                    <td>{{ data.receive_by_designation }}</td>
                                </tr>
                                <tr>
                                    <td>Mobile</td>
                                    <td>:</td>
                                    <td>{{ data.receive_by_mobile }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <div class="col-md-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="3%">Sl</th>
                                <th width="50%">Item/Description</th>
                                <th width="10%">Quantity</th>
                                <th width="15%">Receive Quantity</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in data.challan_details" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <template v-if="item.item">
                                        {{ item?.item?.title }}
                                        <br>
                                    </template>
                                    <template v-if="item.description">
                                        <p style="white-space: pre-line;"> {{ item.description }}</p>
                                    </template>
                                </td>
                                <td>{{ item.qty }}</td>
                                <td> {{ item.receive_qty == "0.00" ? '' : item.receive_qty }} </td>
                                <td>{{ item.remark }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </view-page>
</template>

<script>
import moment from "moment";

const model = "challan";

export default {
    data() {
        return {
            page_title: "",
            model: model,
            reportDate: moment().format('D MMMM, YYYY'),
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
