<template>
    <InvoiceWorkerBased v-if="$root.site.system_mode == 'workorder-based'" />
    <InvoiceSalesBased v-else-if="$root.site.system_mode == 'sales-based'" />
</template>

<script>
import InvoiceWorkerBased from './InvoiceWorkerBased.vue'
import InvoiceSalesBased from './InvoiceSalesBased.vue'

export default {
    components: {
        InvoiceWorkerBased,
        InvoiceSalesBased
    }
}
</script>
