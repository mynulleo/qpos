<template>
    <view-page :defaultTable="false" :showCreateRoute="false" :showDeleteButton="false">
        <div class="row custom_row g-3">
            <div class="col-md-6 col-sm-6">
                <fieldset>
                    <span class="legend">{{ $t('Workorder Info') }}</span>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="40%">{{ $t('Order Date') }}</th>
                                <th width="5%">:</th>
                                <td>{{ data.order_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Order No') }}</th>
                                <th>:</th>
                                <td>{{ data.order_no }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('UNO No') }}</th>
                                <th>:</th>
                                <td>{{ data.uno_no }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Delivery Date') }}</th>
                                <th>:</th>
                                <td>{{ data.delivery_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Currency') }}</th>
                                <th>:</th>
                                <td>{{ data.currency?.title }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Currency Rate') }}</th>
                                <th>:</th>
                                <td>{{ data.currency_rate }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Shipping') }}</th>
                                <th>:</th>
                                <td>{{ data.shipping }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Remarks') }}</th>
                                <th>:</th>
                                <td>{{ data.remarks }}</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div class="col-md-6 col-sm-6">
                <fieldset>
                    <span class="legend">{{ $t('Order By') }}</span>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="40%">{{ $t('Order By') }}</th>
                                <th width="5%">:</th>
                                <td>{{ data.client?.org_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Contact Person') }}</th>
                                <th>:</th>
                                <td>{{ data.client?.name }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Email') }}</th>
                                <th>:</th>
                                <td>{{ data.client?.email }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Mobile') }}</th>
                                <th>:</th>
                                <td>{{ data.client?.mobile }}</td>
                            </tr>
                            <tr>
                                <th>{{ $t('Address') }}</th>
                                <th>:</th>
                                <td>{{ data.client?.address }}</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div class="col-md-12">
                <fieldset>
                    <span class="legend">{{ $t('Workorder Items') }}</span>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ $t('Item/Description') }}</th>
                                <th>{{ $t('Actual Qty') }}</th>
                                <th>{{ $t('Ordered Qty') }}</th>
                                <th>{{ $t('Unit Price') }}</th>
                                <th>{{ $t('Total Price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in data.workorder_details" :key="index">
                                <td>
                                    <template v-if="item.item">
                                        {{ item?.item?.title }}
                                        <br>
                                    </template>
                                    <template v-if="item.description">
                                        <p style="white-space: pre-line;"> {{ item.description }}</p>
                                    </template>
                                </td>
                                <td>{{ item.actual_qty }}</td>
                                <td>{{ item.ordered_qty }}</td>
                                <td>{{ item.unit_price }}</td>
                                <td>{{ item.price }}</td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </div>
        </div>
    </view-page>
</template>

<script>

const model = "workorder";

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
