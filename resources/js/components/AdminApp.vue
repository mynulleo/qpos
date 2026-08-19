<template>
    <div>
        <!-- full page loading -->
        <div class="fullpage_loader" v-if="$root.initialLoader">
            <!-- <div class="loader"></div> -->
        </div>

        <router-view></router-view>

        <ValidationMessage />

        <!-- 🛡️ Software Expiration Lockout Screen -->
        <SoftwareLockModal />
    </div>
</template>

<script>
import ValidationMessage from "./elements/ValidationMessage.vue";
import SoftwareLockModal from "./SoftwareLockModal.vue";

export default {
    components: {
        ValidationMessage,
        SoftwareLockModal,
    },

    props: {
        successMessage: {
            type: String,
            default: "",
        }
    },

    mounted() {
        if (this.successMessage) {
            this.$toast(this.successMessage, "success");
        }

        // Check for SSLCommerz return parameters
        const urlParams = new URLSearchParams(window.location.search);
        const paymentStatus = urlParams.get('payment_status');
        if (paymentStatus === 'success') {
            const months = urlParams.get('months') || '';
            const newExpiry = urlParams.get('new_expiry') || '';
            this.$toast(`আপনার সাবস্ক্রিপশন সফলভাবে ${months} মাসের জন্য নবায়ন করা হয়েছে! নতুন মেয়াদ: ${newExpiry}`, "success");
            
            // Re-fetch system config to update global state and unlock UI
            if (this.$root.getInitializeSystems) {
                this.$root.getInitializeSystems();
            }
            window.history.replaceState({}, document.title, window.location.pathname);
        } else if (paymentStatus === 'failed') {
            this.$toast('পেমেন্ট সম্পন্ন হয়নি বা ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।', 'error');
            window.history.replaceState({}, document.title, window.location.pathname);
        } else if (paymentStatus === 'canceled') {
            this.$toast('পেমেন্ট বাতিল করা হয়েছে।', 'warning');
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
};
</script>
