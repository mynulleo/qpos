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

                        <!-- 🌐 Reactive Language Toggle Button -->
                        <div class="lang_switch_box position-relative">
                            <button
                                type="button"
                                class="btn btn-sm d-flex align-items-center gap-1 shadow-sm px-2 py-1 rounded-pill fw-bold border"
                                :class="$locale === 'bn' ? 'btn-primary text-white border-primary' : 'btn-outline-dark bg-white text-dark'"
                                @click="toggleLanguage"
                                data-bs-toggle="tooltip"
                                data-bs-placement="bottom"
                                :data-bs-title="$locale === 'bn' ? 'Switch to English' : 'বাংলায় পরিবর্তন করুন'"
                                v-x-tooltip
                                style="font-size: 12px; cursor: pointer; transition: all 0.2s ease-in-out;"
                            >
                                <i class="fas fa-language fa-lg"></i>
                                <span class="fw-bold">{{ $locale === 'bn' ? 'বাংলা' : 'EN' }}</span>
                            </button>
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
    },
    data() {
        return {
            profile: false,
            message: false,
            notification: false,
            currentDateTime: "",
            dateTimeTimer: null,
        };
    },
    watch: {
        "$locale"() {
            this.updateDateTime();
        },
    },
    methods: {
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
            const nextLocale = this.$locale === 'bn' ? 'en' : 'bn';
            if (typeof this.$setLocale === 'function') {
                this.$setLocale(nextLocale);
            }
            try {
                this.callApi("post", "set-locale", { locale: nextLocale }, false);
            } catch (e) {}
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
</style>
