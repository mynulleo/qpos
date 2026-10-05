<template>
    <button
        :type="type"
        :disabled="$root.submit ? true : false"
        class="theme_btn"
    >
        <span v-if="$root.submit">
            <i class="fa fa-spinner fa-spin"></i>
            <span v-if="process">{{ $t(process) }}...</span>
            <span v-else> {{ $t('Processing...') }}</span>
        </span>
        <span v-else> {{ $t(computedBtnTitle) }}</span>
    </button>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: "Submit",
        },

        process: {
            type: String,
        },

        type: {
            type: String,
            default: "submit",
        },
    },

    computed: {
        computedBtnTitle() {
            let split = this.$route?.name ? this.$route.name.split(".") : [];
            if (split.length >= 2) {
                if (this.title === "Submit" && split[1] === "edit") {
                    return "Update";
                }
            }
            return this.title || "Submit";
        },
    },
};
</script>

