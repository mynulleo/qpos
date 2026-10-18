import { reactive } from "vue";

const savedLocale =
    (typeof localStorage !== "undefined" &&
        localStorage.getItem("qpos_locale")) ||
    "en";

const bnMessages = {
    "Email Address": "ইমেইল এড্রেস",
    "Password": "পাসওয়ার্ড",
    "New Password": "নতুন পাসওয়ার্ড",
    "Confirm Password": "পাসওয়ার্ড নিশ্চিত করুন",
    "Enter your Email Address": "আপনার ইমেইল এড্রেস দিন",
    "Enter your Password": "আপনার পাসওয়ার্ড দিন",
    "Enter your New Password": "নতুন পাসওয়ার্ড দিন",
    "Enter your Confirm Password": "পাসওয়ার্ড নিশ্চিত করুন",
    "Login": "লগইন",
    "Forget your password?": "পাসওয়ার্ড ভুলে গেছেন?",
    "Back to Login": "লগইন এ ফিরে যান",
    "Send Password Reset Link": "পাসওয়ার্ড রিসেট লিংক পাঠান",
    "Reset Password": "পাসওয়ার্ড রিসেট করুন",
    "Email is required": "ইমেইল প্রয়োজন",
    "Password is required": "পাসওয়ার্ড প্রয়োজন",
    "Please, verify captcha!": "অনুগ্রহ করে ক্যাপচা পূরণ করুন!",
};

export const i18nState = reactive({
    locale: savedLocale,
    messages: {
        en: {},
        bn: bnMessages,
    },
});

export function translate(key, defaultText = "") {
    if (key === null || key === undefined || key === "") return "";
    const locale = i18nState.locale || "en";
    const dict = i18nState.messages[locale] || {};
    const trimmed = String(key).trim();
    if (dict[trimmed] !== undefined) return dict[trimmed];
    return defaultText || key;
}

export function setLocale(locale) {
    i18nState.locale = locale;
    if (typeof localStorage !== "undefined") {
        localStorage.setItem("qpos_locale", locale);
    }
}

export default {
    install: (app) => {
        app.config.globalProperties.$t = translate;
        app.config.globalProperties.$setLocale = setLocale;
        Object.defineProperty(app.config.globalProperties, "$locale", {
            get() {
                return i18nState.locale;
            },
        });
    },
};
