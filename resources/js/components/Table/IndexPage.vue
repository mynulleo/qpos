<template>
    <div>
        <div class="component_loader" :class="{ 'z-top': $root.initialLoader }"
            v-if="$root.spinner && !$root.tableSpinner">
            <div class="loader"></div>
        </div>

        <div id="list_page_wrapper" v-show="!$root.spinner || $root.tableSpinner">
            <!-- Modern Page Header Card -->
            <div class="card border-0 shadow-sm mb-2 page_header_card">
                <div class="card-body py-2 px-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <!-- Left: Page Title & Total Count -->
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold text-dark text-nowrap form_card_title">
                                <slot name="title">
                                    {{ $parent.page_title ?? (model ? ucfirst(model) : "Page Title") }}
                                </slot>
                            </h5>
                            <span class="badge bg-secondary font-monospace" v-if="table && table.meta && table.meta.total">
                                {{ table.meta.total }}
                            </span>
                        </div>

                        <!-- Center: Default Quick Search Bar -->
                        <div class="header_base_search flex-grow-1 mx-md-3" style="max-width: 480px;" v-if="searchBlock">
                            <div class="input-group input-group-sm">
                                <!-- Field Name Selector (if fields_name exists) -->
                                <select v-if="fields_name && Object.keys(fields_name).length > 0"
                                    class="form-select form-select-sm"
                                    style="max-width: 140px;"
                                    v-model="search_data.field_name"
                                    @change="liveSearch">
                                    <option v-for="(item, key) in fields_name" :value="key === 'default' ? '' : key" :key="key">
                                        {{ item }}
                                    </option>
                                </select>

                                <!-- Text Search Input -->
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Search... (Press Enter)"
                                    v-model="search_data.value"
                                    @input="liveSearch"
                                    @keyup.enter="handleEnterSearch"
                                />

                                <!-- Search Action Button (theme color #112C47, white icon) -->
                                <button
                                    type="button"
                                    class="btn btn-sm px-3 theme_search_btn"
                                    @click="handleEnterSearch"
                                    title="Search"
                                >
                                    <i class="fas fa-search"></i>
                                </button>

                                <!-- Reset / Clear Button -->
                                <button
                                    v-if="search_data.value"
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    @click="resetSearchDataAndUpdate"
                                    title="Clear Search"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Right: Advance Filter Toggle, Help & Page Action Buttons -->
                        <div class="d-flex align-items-center gap-2 right_page_header">
                            <!-- Advance Filter Toggle Button (Icon only, help-button style, theme color #112C47) -->
                            <button
                                v-if="$slots['search-field'] || show_status"
                                type="button"
                                class="advance_filter_btn position-relative"
                                @click="show_advance_filter = !show_advance_filter"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-title="Advance Filter"
                                title="Advance Filter"
                                v-x-tooltip
                            >
                                <i class="fas fa-sliders-h"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 4px;" v-if="activeFilterCount > 0">
                                    {{ activeFilterCount }}
                                </span>
                            </button>

                            <!-- Help Button -->
                            <button class="help_btn btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Help" v-x-tooltip>
                                <i class="fas fa-question-circle"></i>
                            </button>

                            <!-- Right Slot: Button (e.g. Add / Back) -->
                            <slot name="button" v-if="button">
                                <AddOrBackButton :route="model + '.create'" :portion="model" :icon="'plus'" />
                            </slot>
                        </div>
                    </div>

                    <!-- Collapsible Advance Filter Drawer Panel -->
                    <div v-show="show_advance_filter" class="mt-2 pt-2 border-top advance_filter_drawer transition-all">
                        <form @submit.prevent="handleEnterSearch">
                            <div class="row g-2 align-items-end">
                                <slot name="search-field"></slot>
                                <StatusDropDown v-if="show_status"></StatusDropDown>

                                <div class="col-md-3 col-sm-12 d-flex gap-2 ms-auto">
                                    <button type="submit" class="btn btn-sm theme_search_btn flex-grow-1 fw-bold">
                                        <i class="fas fa-filter me-1"></i> Apply Filter
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" @click="resetSearchDataAndUpdate">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Sidebar Overlay & Content -->
            <div class="help_overlay"></div>
            <div class="help_info_sidebar position-fixed">
                <div class="help_info_sidebar_content p-4">
                    <h3 class="help_title d-flex align-items-center gap-3">
                        <img width="30" :src="helpInfoIcon()" alt=""> Help
                    </h3>
                    <div class="information_box" v-if="$root.helpInfo.description">
                        <h5 class="top-title"><img width="30" :src="bookImage()" alt="">{{ $parent.page_title }}</h5>
                        <div v-html="$root.helpInfo.description"></div>
                    </div>
                    <p v-else class="text-danger fw-medium no_help_info fs-5">
                        <img width="70" :src="noInfoFound()" alt="">
                        <span class="d-block mt-3">No Help Info Found !!</span>
                    </p>
                </div>
            </div>

            <!-- Loader when Table is refreshing -->
            <table class="table table_loader_only" v-if="$root.tableSpinner">
                <tbody>
                    <tr>
                        <td>
                            <div class="table_loader">
                                <div class="loader"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <template v-else>
                <!-- default custom slot -->
                <slot name="table-list"></slot>

                <!-- base-table -->
                <base-table v-if="defaultTable"></base-table>
                <slot name="summary-page"></slot>
                <!-- base pagination -->
                <Pagination />
            </template>
        </div>
    </div>
</template>

<script>
import _ from "lodash";

export default {
    props: {
        show_status: {
            type: Boolean,
            default: true,
        },
        defaultTable: {
            type: Boolean,
            default: true,
        },
        button: {
            type: Boolean,
            default: true,
        },
        searchBlock: {
            type: Boolean,
            default: true,
        },
        default_search: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            show_advance_filter: false,
            is_focus_bg: false,
            old_search_field_name: this.search_data?.field_name,
            old_search_field_value: this.search_data?.value,
        };
    },

    computed: {
        activeFilterCount() {
            let count = 0;
            if (!this.search_data) return 0;
            for (const key in this.search_data) {
                if (['pagination', 'page', 'field_name', 'value'].includes(key)) continue;
                if (this.search_data[key] !== '' && this.search_data[key] !== null && this.search_data[key] !== undefined) {
                    count++;
                }
            }
            return count;
        }
    },

    inject: {
        table: {
            from: "table",
            default: () => ({}),
        },

        search_data: {
            from: "search_data",
            default: () => ({}),
        },

        model: {
            from: "model",
            default: "",
        },

        search: {
            from: "search",
            default: () => () => { },
        },

        fields_name: {
            from: "fields_name",
            default: "",
        },

        resetSearchData: {
            from: "resetSearchData",
            default: () =>
                function () {
                    this.search_data.pagination = 10;
                    this.search_data.page = 1;
                    this.search_data.field_name = "";
                    this.search_data.value = "";
                    this.search_data.status = "";
                },
        },
    },

    methods: {
        handleEnterSearch() {
            this.$root.tableSpinner = true;
            this.updateQueryParams();
        },

        liveSearch: _.debounce(function (event) {
            if (!this.old_search_field_value) {
                this.old_search_field_value = this.search_data.value;
            }

            if (!this.old_search_field_name) {
                this.old_search_field_name = this.search_data.field_name;
            }

            if (this.old_search_field_name && this.old_search_field_value) {
                this.old_search_field_name = this.search_data.field_name;
                this.old_search_field_value = this.search_data.value;
                this.$root.tableSpinner = true;
                this.search();
            }

            this.old_search_field_name = this.search_data.field_name;
            this.old_search_field_value = this.search_data.value;
        }, 800),

        updateQueryParams() {
            this.$router.push({
                query: this.search_data,
            });

            this.search();
        },

        resetSearchDataAndUpdate() {
            if (this.resetSearchData) {
                this.resetSearchData();
            } else {
                this.search_data.value = "";
                this.search_data.field_name = "";
                this.search_data.page = 1;
            }
            this.$root.tableSpinner = true;
            this.updateQueryParams();
        },
    },

    created() {
        const pageType = "index";
        this.getHelpInfos(this.ucfirst(this.$parent.model), pageType);
    },
};
</script>

<style scoped>
.page_header_card {
    border-radius: 8px;
}
.advance_filter_btn {
    background-color: #112C47;
    color: #ffffff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(17, 44, 71, 0.25);
    transition: all 0.2s ease-in-out;
}
.advance_filter_btn:hover {
    background-color: #1a3d61;
    color: #ffffff;
    transform: scale(1.05);
}
.advance_filter_drawer {
    background-color: #f8fafc;
    padding: 12px;
    border-radius: 6px;
}
.theme_search_btn {
    background-color: #112C47 !important;
    border-color: #112C47 !important;
    color: #ffffff !important;
}
.theme_search_btn:hover {
    background-color: #1a3d61 !important;
    border-color: #1a3d61 !important;
    color: #ffffff !important;
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>
