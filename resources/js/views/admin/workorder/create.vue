<template>
    <WorkerBased v-if="$root.site.system_mode == 'workorder-based'" />
    <SalesBased v-else-if="$root.site.system_mode == 'sales-based'" />
</template>

<script>
import WorkerBased from './WorkerBased.vue'
import SalesBased from './SalesBased.vue'

export default {
    components: {
        WorkerBased,
        SalesBased
    }
}
</script>
