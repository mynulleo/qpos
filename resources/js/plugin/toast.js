
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
                        this.toast[method]({
                            position: 'topCenter',
                            title: title ? title : (type || 'INFO').toUpperCase() + " !!",
                            message: message || '',
                            timeout: time,
                        });
                    }
                }
            },
        })
    }
};
