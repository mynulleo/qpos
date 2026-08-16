<template>
    <WorkerBasedChallan v-if="$root.site.system_mode == 'workorder-based'" />
    <SalesBasedChallan v-else-if="$root.site.system_mode == 'sales-based'" />
</template>

<script>
import WorkerBasedChallan from './WorkerBasedChallan.vue'
import SalesBasedChallan from './SalesBasedChallan.vue'

export default {
    components: {
        WorkerBasedChallan,
        SalesBasedChallan
    }
}
</script>
