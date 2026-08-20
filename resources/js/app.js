require("./bootstrap");
import { createApp } from "vue";

// Import Plugin...
import toast_plugin from "./plugin/toast";
import filters from "./plugin/filters";

// Import Mixin...
import global_mixin from "./mixins/global";
import crud_mixin from "./mixins/crud";
import utils_mixin from "./mixins/utils";

// Import Vue router...
import router from "./router";

// Import Vuex...
import store from "./store";

import { xTooltip, xZoomImage } from "./helpers/custom-directives";

// Globally prevent form to submit on enter...
document.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});

// 🛡️ Zero-Click Event Capture Barrier when Software is Expired
const blockedEvents = ['click', 'dblclick', 'mousedown', 'mouseup', 'contextmenu', 'keydown', 'keypress', 'keyup', 'touchstart', 'touchend', 'submit'];
blockedEvents.forEach((eventType) => {
    window.addEventListener(eventType, (e) => {
        if (window.__IS_SOFTWARE_EXPIRED__) {
            const target = e.target;
            const isInsideLockModal = target && target.closest('#software-lock-overlay');
            if (!isInsideLockModal) {
                e.stopImmediatePropagation();
                e.preventDefault();
                return false;
            }
        }
    }, { capture: true, passive: false });
});

// App Initialize...
const app = createApp({
    data() {
        return {
            baseurl: laravel.baseurl,
            asset_url: laravel.asset_url,
            storage_url: laravel.storage_url,
            exception_errors: {},
            initialLoader: true,
            spinner: false,
            submit: false,
            ckeditorSpinner: false,
            retried: false,
            tableSpinner: false,
            validation_errors: {},
            helpInfo: {},
            media_validators: {},
        };
    },
    methods: {
        getInitializeSystems() {
            this.initialLoader = true;
            axios
                .get("/initialize-systems")
                .then((res) => {
                    this.$store.dispatch("global/setGlobal", res.data);
                    this.initialLoader = false;
                })
                .catch((e) => {
                    this.handleServerUnavailable();
                    console.log(e);
                });
        },
        checkPermission(route) {
            let routeName = !route ? this.$route?.name : route;
            if (!routeName) return false;
            const perms = this.permissions;
            if (!perms) return false;
            if (Array.isArray(perms)) {
                return perms.includes(routeName);
            }
            if (typeof perms === "object") {
                return Object.values(perms).includes(routeName);
            }
            return false;
        },
        handleServerUnavailable() {
            if (!this.retried) {
                this.retried = true;
                console.log("Server unavailable, retrying once...");
                this.getInitializeSystems();
            } else {
                console.log("Whoopsie! Server Unavailable.");
            }
        },
    },
    mounted() {
        if (this.loggedIn) {
            this.getInitializeSystems();
        }
    },
});

import AdminApp from "./components/AdminApp.vue";

app.component("app", AdminApp);
app.mixin(global_mixin);
app.mixin(crud_mixin);
app.mixin(utils_mixin);
app.use(toast_plugin);
app.use(filters);
app.use(store);
app.use(router);

import loadCustomComponents from "./plugin/custom";
loadCustomComponents(app);
import loadThirdPartyPlugins from "./plugin/third_party";
loadThirdPartyPlugins(app);

app.directive("x-tooltip", xTooltip);
app.directive("x-zoom-image", xZoomImage);

app.config.productionTip = false;

if (process.env.NODE_ENV !== "production") {
    app.config.errorHandler = (error, instance, info) => {
        console.log(
            "%c!!------------ Error occurred -------------!!",
            "color: red; font-size: 20px; font-weight: 800;",
        );
        console.log("----------------------------------------");

        console.log("%cError Info:", "color: red;");
        console.log(info);
        console.log("---------------------------------------");

        console.log("%cError Details:", "color: red;");
        console.log(error);

        console.log("%cError Instance:", "color: red;");
        console.log(instance);
        console.log("--------------------------------------");
        console.log(
            "%cError End! \n\n",
            "color: red; font-size: 20px; font-weight: 800;",
        );

        alert("Error occurred! Please, check your console. Thank you!");
    };
}

app.mount("#app");
