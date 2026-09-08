<template>
    <form @submit.prevent="submit">
        <div class="row g-3">
            <Input id="email" title="Email Address" v-model="data.email" field="data.email" required
                placeholder="Enter your Email Address" col="12" :disabled="$root.spinner" />

            <Input id="new_password" type="password" title="New Password" v-model="data.password" field="data.password"
                required placeholder="Enter your New Password" col="12" :disabled="$root.spinner" />

            <Input id="confirm_password" type="password" title="Confirm Password" v-model="data.password_confirmation"
                field="data.password_confirmation" required placeholder="Enter your Confirm Password" col="12"
                :disabled="$root.spinner" />

            <div class="col-12 mt-4">
                <button type="submit" class="theme_btn rounded-2 w-100" :disabled="$root.spinner">
                    <template v-if="$root.spinner">
                        <i class="fa fa-spinner fa-spin me-1"></i> Processing...
                    </template>
                    <template v-else> Reset Password </template>
                </button>
            </div>
            <div class="col-12 text-center">
                <a href="javascript:void(0)" @click.prevent="backToLogin" class="forgot text-center">Back to Login</a>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    data() {
        return {
            data: {
                token: this.token,
                password: "",
                password_confirmation: "",
                email: this.email,
            },
        };
    },
    methods: {
        submit() {
            this.$validate().then((res) => {
                if (res) {
                    if (this.$root.spinner) {
                        return false;
                    }

                    this.$root.spinner = true;

                    axios
                        .post("/password/reset", this.data)
                        .then((res) => {
                            this.$root.spinner = false;
                            this.$toast(res.data.message, "success");

                            setTimeout(() => {
                                window.location.href = `${this.$root.baseurl}/qpanel`;
                            }, 2000);
                        })
                        .catch((e) => {
                            this.$root.spinner = false;
                            this.$toast(
                                e.response?.data?.message ??
                                "Something went wrong!",
                                "error"
                            );
                        });
                }
            });
        },

        backToLogin() {
            if (this.$parent && this.$parent.reset) {
                this.$parent.reset.flag = false;
            }
            if (typeof this.toggleForgetFlag === "function") {
                this.toggleForgetFlag(false);
            }
            if (window.history.pushState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        },
    },
    inject: ["token", "resetToken", "email", "toggleForgetFlag"],
    provide() {
        return {
            validate: this.validation,
        };
    },
    created() {
        this.data.token = this.token;
        if (this.email) {
            this.data.email = this.email;
        }
    },
    validators: {
        "data.email": function (value = null) {
            return Validator.value(value)
                .email()
                .maxLength(100)
                .required("Email is required");
        },
        "data.password": function (value) {
            return Validator.value(value).required().minLength(6);
        },
        "data.password_confirmation": function (value) {
            return Validator.value(value).required().match(this.data.password);
        },
    },
};
</script>
