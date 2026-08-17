<template>
    <view-page :defaultTable="false" :showPrintButton="true" printArea="printArea">
        <div id="printArea">
            <div class="row custom_row g-3">
                <!-- Left Column: Image & Barcode Label Fieldset -->
                <div class="col-md-3 text-center">
                    <!-- Barcode Label Fieldset -->
                    <fieldset>
                        <span class="legend">Barcode Label</span>
                        <div class="p-3 text-center">
                            <div class="barcode-label-box p-3 border rounded bg-white mx-auto shadow-sm"
                                style="max-width: 240px; border: 1px dashed #333 !important;">
                                <div class="fw-bold text-truncate text-uppercase fs-6 mb-1" style="color: #000;">
                                    {{ data.title || 'Item Name' }}
                                </div>
                                <div v-if="data.barcode_image" class="my-2">
                                    <img :src="data.barcode_image" alt="Barcode"
                                        style="height: 45px; max-width: 100%;" />
                                </div>
                                <div class="font-monospace fw-bold fs-6 text-dark" style="letter-spacing: 1.5px;">
                                    {{ data.barcode || 'N/A' }}
                                </div>
                                <div class="fw-bold text-dark mt-2 border-top pt-1 fs-6">
                                    Price: Tk. 500.00
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button"
                                    class="btn btn-sm btn-primary px-4 d-inline-flex align-items-center gap-2"
                                    @click="printSingleBarcode">
                                    <i class="fas fa-print"></i> Print Barcode Label
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-3 text-center">

                    <!-- Item Image Fieldset -->
                    <fieldset>
                        <span class="legend">Item Image</span>
                        <div class="p-3">
                            <img v-if="data.image" :src="data.image" class="img-fluid rounded border"
                                style="max-height: 200px;" alt="Item Image" />
                            <p v-else class="text-muted mb-0">No Image Available</p>
                        </div>
                    </fieldset>


                </div>

                <!-- Right Column: Item Information Fieldset -->
                <div class="col-md-6">
                    <fieldset>
                        <span class="legend">Item Information</span>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th width="30%">Title</th>
                                        <th width="5%">:</th>
                                        <td>{{ data.title }}</td>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <th>:</th>
                                        <td>{{ data.category ? data.category.title : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Unit</th>
                                        <th>:</th>
                                        <td>{{ data.unit ? data.unit.title : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Barcode</th>
                                        <th>:</th>
                                        <td><span class="badge bg-dark font-monospace fs-6 px-3 py-1">{{ data.barcode ||
                                            'N/A' }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Opening Rate</th>
                                        <th>:</th>
                                        <td>{{ data.opening_rate }}</td>
                                    </tr>
                                    <tr>
                                        <th>Opening Qty</th>
                                        <th>:</th>
                                        <td>{{ data.opening_qty }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <th>:</th>
                                        <td>
                                            <span
                                                :class="data.status === 'active' || data.status === 'Active' || data.status === 1 ? 'badge bg-success' : 'badge bg-danger'">
                                                {{ data.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <th>:</th>
                                        <td>{{ data.description || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <!-- Hidden Single Barcode Printable Area -->
        <div id="singleBarcodePrintArea" style="display: none;">
            <div
                style="width: 220px; padding: 10px 12px; border: 1px dashed #000; text-align: center; font-family: Arial, sans-serif; margin: 0 auto; background: #fff;">
                <div
                    style="font-size: 13px; font-weight: bold; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-transform: uppercase; color: #000;">
                    {{ data.title }}
                </div>
                <div v-if="data.barcode_image" style="margin: 5px 0;">
                    <img :src="data.barcode_image"
                        style="width: 180px; height: 45px; display: block; margin: 0 auto;" />
                </div>
                <div
                    style="font-size: 14px; font-weight: bold; letter-spacing: 1.5px; margin-top: 3px; font-family: monospace; color: #000;">
                    {{ data.barcode }}
                </div>
                <div
                    style="font-size: 12px; margin-top: 4px; font-weight: bold; border-top: 1px dashed #444; padding-top: 3px; color: #000;">
                    Price: Tk. 500.00
                </div>
            </div>
        </div>
    </view-page>
</template>

<script>
const model = "item";

export default {
    data() {
        return {
            page_title: "",
            model: model,
            data: {},
        };
    },
    methods: {
        printSingleBarcode() {
            if (!this.data.barcode) {
                this.$toast("No barcode available for this item", "warning");
                return;
            }
            this.print(
                "singleBarcodePrintArea",
                "Barcode Label - " + (this.data.title || "Item")
            );
        },
    },
    created() {
        this.page_title = `${this.headline(this.model)} View`;
        this.get_data(`${this.model}/${this.$route.params.id}`);
    },
};
</script>
