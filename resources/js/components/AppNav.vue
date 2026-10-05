<template>
    <header class="header_area w-100">
        <div class="row">
            <div class="col-md-4 col-4 align-self-center">
                <div class="header_left">
                    <div class="main d-flex gap-2 align-items-center">
                        <div class="sidebar_control_bar">
                            <button type="button" class="sidebar_control_btn border-0" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-title="Navbar" v-x-tooltip>
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-menu-deep">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 6h16" />
                                    <path d="M7 12h13" />
                                    <path d="M10 18h10" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-8 align-self-center">
                <div class="header_right d-flex align-items-center justify-content-end gap-4">
                    <div class="date_time position-relative d-none d-sm-block">
                        <p id="currentDateTime" class="mb-0 fw-semibold d-flex align-items-center gap-2" style="color: #112C47 !important; font-size: 13.5px; white-space: nowrap;">
                            <i class="far fa-clock text-primary"></i>
                            <span>{{ currentDateTime }}</span>
                        </p>
                    </div>
                    <div class="action_info d-flex gap-3 align-items-center">
                        <!-- 🔄 SaaS Database Update Available Indicator -->
                        <router-link
                            v-if="$root.global?.db_update_needed"
                            :to="{ name: 'softwareupdate.index' }"
                            class="btn btn-sm btn-warning text-dark fw-bold d-flex align-items-center gap-1 shadow-sm px-2 py-1 pulse-update-btn text-decoration-none rounded-pill"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            title="Database Update Available"
                        >
                            <i class="fas fa-rotate fa-spin-pulse"></i>
                            <span class="d-none d-md-inline" style="font-size: 11px;">Update DB</span>
                            <span class="badge bg-danger rounded-pill text-white ms-1" style="font-size: 10px;">
                                {{ $root.global?.pending_updates_count }}
                            </span>
                        </router-link>

                        <!-- 🌐 Reactive Language Dropdown -->
                        <div class="lang_dropdown_box position-relative" ref="langDropdownRef">
                            <button
                                type="button"
                                class="btn btn-sm d-flex align-items-center gap-2 shadow-sm px-2.5 py-1.5 rounded-pill border lang-select-btn"
                                :class="langDropdownOpen ? 'btn-primary text-white border-primary shadow' : 'btn-outline-dark bg-white text-dark'"
                                @click.stop="toggleLangDropdown"
                                style="font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); min-height: 33px;"
                            >
                                <span style="font-size: 16px; line-height: 1;">{{ currentLanguageObj.flag }}</span>
                                <span class="d-none d-md-inline">{{ currentLanguageObj.nativeName }}</span>
                                <span class="d-inline d-md-none">{{ currentLanguageObj.short }}</span>
                                <i class="fas fa-chevron-down ms-1" :style="{ fontSize: '10px', transition: 'transform 0.25s ease', transform: langDropdownOpen ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                            </button>

                            <div
                                v-if="langDropdownOpen"
                                class="lang_menu_panel position-absolute end-0 mt-2 py-1 shadow-lg bg-white rounded-3 border"
                                style="z-index: 1050; min-width: 215px; animation: langFadeIn 0.2s ease-out; box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important;"
                                @click.stop
                            >
                                <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top">
                                    <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-globe me-1 text-primary"></i>{{ $t('Select Language') }}
                                    </span>
                                    <span class="badge bg-primary rounded-pill text-white" style="font-size: 10px;">5</span>
                                </div>
                                <ul class="list-unstyled mb-0 py-1">
                                    <li v-for="lang in availableLanguages" :key="lang.code">
                                        <a
                                            href="javascript:void(0)"
                                            class="dropdown-item px-3 py-2 d-flex align-items-center justify-content-between text-decoration-none lang-option-item"
                                            :class="{ 'active-lang bg-primary bg-opacity-10 text-primary fw-bold': $locale === lang.code }"
                                            @click="selectLanguage(lang.code)"
                                            style="transition: all 0.15s ease;"
                                        >
                                            <div class="d-flex align-items-center gap-2">
                                                <span style="font-size: 18px; line-height: 1;">{{ lang.flag }}</span>
                                                <div class="d-flex flex-column text-start">
                                                    <span class="lh-sm" style="font-size: 13px; font-weight: 600;">{{ lang.nativeName }}</span>
                                                    <small class="text-muted" style="font-size: 11px;">{{ lang.name }}</small>
                                                </div>
                                            </div>
                                            <i v-if="$locale === lang.code" class="fas fa-check-circle text-primary ms-2" style="font-size: 14px;"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="user_box position-relative">
                            <button type="button"
                                class="user_image dropdown_menu rounded-pill bg-transparent border-0 position-relative"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Profile" v-x-tooltip>
                                <img class="rounded-pill w-100 h-100 object-fit-cover" :src="user?.profile_three ??
                                    `${$root.asset_url}/images/profile.jpg`
                                    " alt="profile-user" />
                            </button>
                            <div class="user_information position-absolute dropdown_menu_info">
                                <div class="edit_image text-center">
                                    <div class="image position-relative">
                                        <img :src="user?.profile_one ??
                                            `${$root.asset_url}/images/profile.jpg`
                                            " :href="user?.profile_two" v-x-zoom-image alt="profile-edit" />
                                    </div>
                                    <div class="name text-center">
                                        <h4 class="nm">
                                            {{ $root.user.name }}
                                        </h4>
                                        <router-link :to="{
                                            name: 'profile.profileDetails',
                                        }" class="edit">Edit Your Profile</router-link>
                                    </div>
                                    <div class="menus text-start">
                                        <ul class="list-unstyled">
                                            <li v-for="(
profileMenu,
    profileMenuIndex
                                                ) in $root.global
        ?.profile_menus" :key="`profile_menu_${profileMenuIndex}`">
                                                <router-link v-if="profileMenu.params" :to="{
                                                    name: profileMenu.route_name,
                                                    params: {
                                                        slug: profileMenu.params,
                                                    },
                                                }">
                                                    <span class="menu_icon" v-if="profileMenu.icon" v-html="profileMenu.icon
                                                        "></span>
                                                    {{ $t(profileMenu.menu_name) }}
                                                </router-link>

                                                <router-link v-else :to="{
                                                    name: profileMenu.route_name,
                                                }">
                                                    <span v-if="profileMenu.icon" v-html="profileMenu.icon
                                                        "></span>
                                                    {{ $t(profileMenu.menu_name) }}
                                                </router-link>
                                            </li>

                                            <li>
                                                <a href="javascript:void(0)" @click.prevent="logout()">
                                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                                    {{ $t('Log Out') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import { mapState } from "vuex";

export default {
    computed: {
        ...mapState("setting", ["colors"]),
        availableLanguages() {
            return this.$availableLocales || [
                { code: "en", name: "English", nativeName: "English", flag: "🇺🇸", short: "EN" },
                { code: "bn", name: "Bengali", nativeName: "বাংলা", flag: "🇧🇩", short: "বাংলা" },
                { code: "hi", name: "Hindi", nativeName: "हिन्दी", flag: "🇮🇳", short: "हिन्दी" },
                { code: "fr", name: "French", nativeName: "Français", flag: "🇫🇷", short: "FR" },
                { code: "es", name: "Spanish", nativeName: "Español", flag: "🇪🇸", short: "ES" },
            ];
        },
        currentLanguageObj() {
            return this.availableLanguages.find(l => l.code === this.$locale) || this.availableLanguages[0];
        },
    },
    data() {
        return {
            profile: false,
            message: false,
            notification: false,
            currentDateTime: "",
            dateTimeTimer: null,
            langDropdownOpen: false,
        };
    },
    watch: {
        "$locale"() {
            this.updateDateTime();
        },
    },
    methods: {
        toggleLangDropdown() {
            this.langDropdownOpen = !this.langDropdownOpen;
        },

        selectLanguage(langCode) {
            this.langDropdownOpen = false;
            if (typeof this.$setLocale === 'function') {
                this.$setLocale(langCode);
            }
            try {
                if (typeof this.callApi === 'function') {
                    this.callApi("post", "set-locale", { locale: langCode }, false);
                } else if (window.axios) {
                    window.axios.post("set-locale", { locale: langCode }).catch(() => {});
                }
            } catch (e) {}
        },

        handleOutsideClick(event) {
            if (this.$refs.langDropdownRef && !this.$refs.langDropdownRef.contains(event.target)) {
                this.langDropdownOpen = false;
            }
        },

        updateDateTime() {
            const now = new Date();
            if (this.$locale === "bn") {
                const bnDays = ["রবিবার", "সোমবার", "মঙ্গলবার", "বুধবার", "বৃহস্পতিবার", "শুক্রবার", "শনিবার"];
                const bnMonths = ["জানুয়ারি", "ফেব্রুয়ারি", "মার্চ", "এপ্রিল", "মে", "জুন", "জুলাই", "আগস্ট", "সেপ্টেম্বর", "অক্টোবর", "নভেম্বর", "ডিসেম্বর"];

                const dayName = bnDays[now.getDay()];
                const day = this.$bnNum(now.getDate());
                const month = bnMonths[now.getMonth()];
                const year = this.$bnNum(now.getFullYear());

                let hours = now.getHours();
                const minutes = this.$bnNum(String(now.getMinutes()).padStart(2, "0"));
                const seconds = this.$bnNum(String(now.getSeconds()).padStart(2, "0"));
                const ampm = hours >= 12 ? "পিএম" : "এএম";
                hours = hours % 12;
                hours = hours ? hours : 12;
                const bnHours = this.$bnNum(hours);

                this.currentDateTime = `${dayName}, ${day} ${month} ${year}, ${bnHours}:${minutes}:${seconds} ${ampm}`;
            } else if (this.$locale === "hi") {
                const hiDays = ["रविवार", "सोमवार", "मंगलवार", "बुधवार", "गुरुवार", "शुक्रवार", "शनिवार"];
                const hiMonths = ["जनवरी", "फरवरी", "मार्च", "अप्रैल", "मई", "जून", "जुलाई", "अगस्त", "सितंबर", "अक्टूबर", "नवंबर", "दिसंबर"];

                const dayName = hiDays[now.getDay()];
                const day = now.getDate();
                const month = hiMonths[now.getMonth()];
                const year = now.getFullYear();

                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, "0");
                const seconds = String(now.getSeconds()).padStart(2, "0");
                const ampm = hours >= 12 ? "PM" : "AM";
                hours = hours % 12;
                hours = hours ? hours : 12;

                this.currentDateTime = `${dayName}, ${day} ${month} ${year}, ${hours}:${minutes}:${seconds} ${ampm}`;
            } else if (this.$locale === "fr") {
                const options = {
                    weekday: "short",
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                    hour12: false,
                };
                this.currentDateTime = now.toLocaleDateString("fr-FR", options);
            } else if (this.$locale === "es") {
                const options = {
                    weekday: "short",
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                    hour12: true,
                };
                this.currentDateTime = now.toLocaleDateString("es-ES", options);
            } else {
                const options = {
                    weekday: "short",
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                    hour12: true,
                };
                this.currentDateTime = now.toLocaleDateString("en-US", options);
            }
        },

        async logout() {
            try {
                await this.callApi("post", "logout", null, false);
            } catch (e) {}
            this.$store.dispatch("auth/logout");
            window.location.href = (this.$root.baseurl ? this.$root.baseurl : '') + "/";
        },

        toggleLanguage() {
            const locales = ['en', 'bn', 'hi', 'fr', 'es'];
            const currentIndex = locales.indexOf(this.$locale);
            const nextLocale = locales[(currentIndex + 1) % locales.length];
            this.selectLanguage(nextLocale);
        },

        loggedInfo() {
            const today = new Date();
            const options = {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric",
            };
            const formattedDate = today.toLocaleDateString("en-US", options);
            const loggedInfo = `You Logged as ${this.ucfirst(
                this.$root.user.name
            )}`;

            return `${loggedInfo}, ${formattedDate}`;
        },
    },

    created() {
        this.updateDateTime();
    },

    mounted() {
        this.updateDateTime();
        this.dateTimeTimer = setInterval(() => {
            this.updateDateTime();
        }, 1000);

        document.addEventListener("click", this.handleOutsideClick);

        // collapsed sidebar js
        $(".control-bar i").click(function () {
            $("body").toggleClass("collapsed-menu");
        });

        $(".mobile-control-bar i").click(function () {
            $(".navigation-body").addClass("show-mobile-sidebar");
            body.style.overflow = "hidden";
        });

        $(".mobile-control-bar i").click(function () {
            $(".toggle-overlay").addClass("show-toggle-overlay");
            body.style.overflow = "hidden";
        });

        $(".close-mobile-menu i").click(function () {
            $(".navigation-body").removeClass("show-mobile-sidebar");
            body.style.overflow = "auto";
        });

        $(".close-mobile-menu i").click(function () {
            $(".toggle-overlay").removeClass("show-toggle-overlay");
            body.style.overflow = "auto";
        });

        $(".toggle-overlay").click(function () {
            $(".toggle-overlay").removeClass("show-toggle-overlay");
            body.style.overflow = "auto";
        });

        $(".toggle-overlay").click(function () {
            $(".navigation-body").removeClass("show-mobile-sidebar");
            body.style.overflow = "auto";
        });

        // Request full screen js
        const arrows = document.querySelector(".fa-arrows-alt");
        const body = document.querySelector("body");

        const toggleFullscreen = () => {
            if (document.fullscreenElement) document.exitFullscreen();
            else body.requestFullscreen();
        };

        // fixed header part js
        $(window).scroll(function () {
            let scrolling = $(this).scrollTop();
            if (scrolling > 0) {
                $(".top-header").addClass("fixed");
            } else {
                $(".top-header").removeClass("fixed");
            }
        });
    },

    beforeUnmount() {
        if (this.dateTimeTimer) {
            clearInterval(this.dateTimeTimer);
            this.dateTimeTimer = null;
        }
        document.removeEventListener("click", this.handleOutsideClick);
    },
};
</script>

<style scoped>
.margin-top-10 {
    margin-top: 10px !important;
}
.pulse-update-btn {
    animation: pulse-border 2s infinite;
}
@keyframes pulse-border {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

@keyframes langFadeIn {
    from {
        opacity: 0;
        transform: translateY(-6px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.lang-select-btn:hover {
    border-color: #0d6efd !important;
    background-color: #f8fafc !important;
}

.lang-option-item {
    border-radius: 6px;
    margin: 2px 6px;
    color: #334155;
}

.lang-option-item:hover {
    background-color: #f1f5f9;
    color: #0f172a;
    transform: translateX(2px);
}

.lang-option-item.active-lang {
    background-color: rgba(13, 110, 253, 0.1) !important;
    color: #0d6efd !important;
}
</style>
