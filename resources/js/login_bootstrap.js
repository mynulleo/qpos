import axios from "axios";
window.axios = axios;

const baseUrlMeta = document.head.querySelector('meta[name="base-url"]');
if (baseUrlMeta) {
    window.axios.defaults.baseURL = baseUrlMeta.content;
}

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
    );
}

