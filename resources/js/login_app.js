import "./login_bootstrap";
import { createApp } from "vue";
import { createStore } from "vuex";
import auth from "./store/auth";

// Import Validation...
import validate from "simple-vue-validator";
const Validator = validate.Validator;
window.Validator = Validator;

// Import Plugins...
import toast_plugin from "./plugin/toast";
import i18n from "./plugin/login_i18n";

// Import Components...
import Input from "./components/Form/Input.vue";
import login from "./views/auth/login.vue";

const store = createStore({
    modules: {
        auth,
    },
});

// App Initialize...
const app = createApp({
    data() {
        return {
            baseurl: window.laravel ? window.laravel.baseurl : "",
            asset_url: window.laravel ? window.laravel.asset_url : "",
            storage_url: window.laravel ? window.laravel.storage_url : "",
            spinner: false,
            site: "",
            initialLoader: false,
        };
    },
});

app.component("Input", Input);
app.component("login", login);

app.use(validate);
app.use(toast_plugin);
app.use(i18n);
app.use(store);

app.config.productionTip = false;

app.mount("#app");

