import { reactive } from "vue";
import en from "../lang/en";
import bn from "../lang/bn";

const savedLocale = localStorage.getItem("qpos_locale") || "en";

export const i18nState = reactive({
    locale: savedLocale,
    messages: {
        en: en,
        bn: bn,
    },
});

export function setLocale(locale, syncServer = true) {
    if (!["en", "bn"].includes(locale)) locale = "en";
    i18nState.locale = locale;
    localStorage.setItem("qpos_locale", locale);

    if (syncServer && window.axios) {
        window.axios.post("set-locale", { locale }).catch(() => {});
    }
}

export function translate(key, defaultText = "") {
    if (key === null || key === undefined || key === "") return "";
    const locale = i18nState.locale || "en";
    const dict = i18nState.messages[locale] || {};
    
    // 1. Direct exact match
    if (dict[key] !== undefined) {
        return dict[key];
    }

    const strKey = String(key);
    const trimmed = strKey.trim();

    // 2. Trimmed match
    if (dict[trimmed] !== undefined) {
        return dict[trimmed];
    }

    // 3. De-underscored match (e.g., 'invoice_no' -> 'invoice no' or 'Invoice No')
    const deUnderscore = trimmed.replaceAll("_", " ");
    if (dict[deUnderscore] !== undefined) {
        return dict[deUnderscore];
    }

    // 4. Case-insensitive match in current locale dictionary
    const lower = trimmed.toLowerCase();
    for (const k in dict) {
        if (k.toLowerCase() === lower) {
            return dict[k];
        }
    }
    const lowerDeUnder = deUnderscore.toLowerCase();
    for (const k in dict) {
        if (k.toLowerCase() === lowerDeUnder) {
            return dict[k];
        }
    }

    // 5. Fallback to English dictionary if locale is bn
    if (locale !== "en") {
        const enDict = i18nState.messages["en"] || {};
        if (enDict[key] !== undefined) {
            return enDict[key];
        }
        if (enDict[trimmed] !== undefined) {
            return enDict[trimmed];
        }
        if (enDict[deUnderscore] !== undefined) {
            return enDict[deUnderscore];
        }
    }

    return defaultText || deUnderscore;
}

export function toggleLocale(syncServer = true) {
    const next = i18nState.locale === "bn" ? "en" : "bn";
    setLocale(next, syncServer);
    return next;
}

export function toBengaliNumber(number) {
    if (number === null || number === undefined) return "";
    if (i18nState.locale !== "bn") return String(number);
    const bnDigits = ["০", "১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯"];
    return String(number).replace(/[0-9]/g, (w) => bnDigits[+w]);
}

export default {
    install: (app) => {
        app.config.globalProperties.$t = translate;
        app.config.globalProperties.$setLocale = setLocale;
        app.config.globalProperties.$toggleLocale = toggleLocale;
        app.config.globalProperties.$toggleLanguage = toggleLocale;
        app.config.globalProperties.$bnNum = toBengaliNumber;

        Object.defineProperty(app.config.globalProperties, "$locale", {
            get() {
                return i18nState.locale;
            },
        });
    },
};
