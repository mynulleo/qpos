export default {
    namespaced: true,

    state: () => ({
        site: {},
        global: {},
        menus: {},
        permissions: {},
        sliderPosition: {},
        sliderButtonTypes: {},
        keyword: "",
        categoriesModuleNames: {},
        unitModuleNames: {},
        subscription: {
            organization_name: '',
            expired_date: null,
            is_expired: false,
            days_overdue: 0,
            subscription_fee: 500,
            today: '',
        },
        colors: {
            sidebar_footer_color: "#fffff",
            sidebar_top_color: "#fffff",
            sidebar_color: "#2f3a4e",
            navbar_color: "#61daff",
        },
    }),

    mutations: {
        setGlobalData(state, data) {
            state.site = data.site || {};
            state.menus = data.menus || [];
            state.filteredMenus = data.menus || [];
            state.global = data.global || {};
            state.permissions = data.permissions || {};
            state.sliderPosition = data.sliderPosition || {};
            state.sliderLists = data.sliderLists || [];
            state.sliderButtonTypes = data.sliderButtonTypes || {};
            state.categoriesModuleNames = data.categoriesModuleNames || {};
            state.subscription = data.subscription || {
                organization_name: '',
                expired_date: null,
                is_expired: false,
                days_overdue: 0,
                subscription_fee: 500,
                today: '',
            };

            // Global flag for zero-latency DOM/Event/Axios blocking
            if (state.subscription && state.subscription.is_expired) {
                window.__IS_SOFTWARE_EXPIRED__ = true;
                window.__EXPIRED_SUBSCRIPTION__ = state.subscription;
                document.body.classList.add('software-locked');
            } else {
                window.__IS_SOFTWARE_EXPIRED__ = false;
                window.__EXPIRED_SUBSCRIPTION__ = null;
                document.body.classList.remove('software-locked');
            }

            state.colors = {
                sidebar_footer_color: data.site ? data.site.sidebar_footer_color : '#ffffff',
                sidebar_top_color: data.site ? data.site.sidebar_top_color : '#ffffff',
                sidebar_color: data.site ? data.site.sidebar_color : '#2f3a4e',
                navbar_color: data.site ? data.site.navbar_color : '#61daff',
            };
        },

        filterMenus(state) {
            if (state.keyword === "") {
                state.filteredMenus = state.menus;
            } else {
                state.filteredMenus = state.menus.filter((menu) => {
                    return menu.menu_name
                        .toLowerCase()
                        .includes(state.keyword.toLowerCase());
                });
            }
        },

        setKeyword(state, keyword) {
            state.keyword = keyword;
        },
    },
    actions: {
        setGlobal(context, data) {
            context.commit("setGlobalData", data);
        },
        updateKeyword(context, keyword) {
            context.commit("setKeyword", keyword);
            context.commit("filterMenus");
        },
    },
};
