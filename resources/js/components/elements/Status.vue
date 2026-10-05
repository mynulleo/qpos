<template>
    <v-select-container
        title="Select Status"
        field="search_data.status"
        col="4"
    >
        <v-select
            v-model="search_data.status"
            label="name"
            :reduce="(obj) => obj.value"
            :options="modules"
            :placeholder="$t('--Select Status--')"
            :closeOnSelect="true"
        ></v-select>
    </v-select-container>
</template>

<script>
export default {
    inject: ["search_data"],
    computed: {
        modules() {
            return [
                { name: this.$t("All"), value: "" },
                { name: this.$t("Active"), value: "active" },
                { name: this.$t("Deactive"), value: "deactive" },
            ];
        },
    },
};
</script>
