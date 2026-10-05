
import toast from "izitoast";
import 'izitoast/dist/css/iziToast.min.css'

export default {
    install: function (app) {
        app.config.globalProperties.toast = toast;

        app.mixin({
            methods: {
                $toast(message, type = 'info', title = '', time = 5000) {
                    let method = type || 'info';
                    if (method === 'danger') method = 'error';
                    if (!this.toast || typeof this.toast[method] !== 'function') {
                        method = 'info';
                    }
                    if (this.toast && typeof this.toast[method] === 'function') {
                        const translatedMsg = typeof this.$t === 'function' && message ? this.$t(message) : message;
                        const translatedTitle = title ? (typeof this.$t === 'function' ? this.$t(title) : title) : (type || 'INFO').toUpperCase() + " !!";
                        this.toast[method]({
                            position: 'topCenter',
                            title: translatedTitle,
                            message: translatedMsg || '',
                            timeout: time,
                        });
                    }
                }
            },
        })
    }
};
