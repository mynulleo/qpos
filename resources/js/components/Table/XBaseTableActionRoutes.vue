<template>
    <div class="hover-floating-actions">
        <div class="btn-group btn-group-sm shadow-sm bg-white border rounded px-1 py-1">
            <!-- Custom routes in tableRoutes.array -->
            <template v-if="tableRoutes.array && tableRoutes.array.length > 0">
                <template v-for="(route, index) in tableRoutes.array">
                    <template v-if="!route.showWhen || (route.showWhen && route.showWhen(this, item))">
                        <template v-if="route.type == 'destroy'">
                            <a href="javascript:void(0)"
                                v-if="route.route && $root.checkPermission(route.route)"
                                @click.stop="$parent.destroy(item, item.is_delete ?? null)"
                                class="btn btn-xs btn-outline-danger border-0"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" v-x-tooltip>
                                <span v-if="item.is_delete"><i class="fa-solid fa-send-back"></i></span>
                                <template v-else><span v-html="route.content"></span></template>
                            </a>
                        </template>

                        <a href="javascript:void(0)"
                            v-else-if="route.callback || route.modal"
                            @click.stop="route.callback ? route.callback(this, item) : $parent.setModal(item['id'], route.modalid)"
                            class="btn btn-xs btn-outline-secondary border-0"
                            v-html="route.content">
                        </a>

                        <template v-else>
                            <router-link
                                v-if="route.route && $root.checkPermission(route.route)"
                                :to="generateRoute(route)"
                                class="btn btn-xs btn-outline-secondary border-0"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                :data-bs-title="route.title" v-x-tooltip v-html="route.content"></router-link>
                        </template>
                    </template>
                </template>
            </template>

            <!-- Standard View, Edit, Delete Routes -->
            <template v-if="Object.keys(tableRoutes).length > 0">
                <!-- View Action (Icon only) -->
                <router-link
                    v-if="tableRoutes.view && $root.checkPermission(tableRoutes.view)"
                    :to="{
                        name: tableRoutes.view,
                        params: { id: item.id },
                        query: { page: $route.query.page },
                    }"
                    class="btn btn-xs btn-outline-primary border-0"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View" v-x-tooltip
                >
                    <i class="fas fa-eye"></i>
                </router-link>

                <!-- Edit Action (Icon only) -->
                <template v-if="tableRoutes.edit && $root.checkPermission(tableRoutes.edit)">
                    <router-link
                        v-if="canEditItem(item)"
                        :to="{
                            name: tableRoutes.edit,
                            params: { id: item.id },
                            query: { page: $route.query.page },
                        }"
                        class="btn btn-xs btn-outline-success border-0"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit" v-x-tooltip
                    >
                        <i class="fas fa-pencil-alt"></i>
                    </router-link>
                    <span
                        v-else
                        class="btn btn-xs btn-outline-secondary border-0 text-muted opacity-50 cursor-not-allowed"
                        data-bs-toggle="tooltip" data-bs-placement="top"
                        :data-bs-title="editDisabledReason(item)"
                        v-x-tooltip
                    >
                        <i class="fas fa-pencil-alt"></i>
                    </span>
                </template>

                <!-- Delete Action (Icon only) -->
                <template v-if="tableRoutes.destroy && $root.checkPermission(tableRoutes.destroy)">
                    <a
                        href="javascript:void(0)"
                        v-if="canDeleteItem(item)"
                        @click.stop="$parent.destroy(item, item.is_delete ?? null)"
                        class="btn btn-xs btn-outline-danger border-0"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" v-x-tooltip
                    >
                        <span v-if="item.is_delete">
                            <i class="fa-solid fa-send-back"></i>
                        </span>
                        <span v-else>
                            <i class="fas fa-trash-alt"></i>
                        </span>
                    </a>
                    <span
                        v-else
                        class="btn btn-xs btn-outline-secondary border-0 text-muted opacity-50 cursor-not-allowed"
                        data-bs-toggle="tooltip" data-bs-placement="top"
                        :data-bs-title="deleteDisabledReason(item)"
                        v-x-tooltip
                    >
                        <i class="fas fa-trash-alt"></i>
                    </span>
                </template>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        tableRoutes: {
            type: Object,
            default: () => ({}),
        },

        item: {
            type: Object,
            default: () => ({}),
        },
    },

    methods: {
        canEditItem(item) {
            if (!item) return true;
            if (typeof this.tableRoutes.canEdit === "function") {
                return Boolean(this.tableRoutes.canEdit(item));
            }
            if (item.can_edit !== undefined && item.can_edit !== null) {
                return Boolean(item.can_edit);
            }
            if (item.grns_count > 0 || (item.receive_status && item.receive_status !== 'Pending')) {
                return false;
            }
            return true;
        },

        editDisabledReason(item) {
            if (typeof this.tableRoutes.editDisabledReason === "function") {
                return this.tableRoutes.editDisabledReason(item);
            }
            if (item && (item.grns_count > 0 || (item.receive_status && item.receive_status !== 'Pending'))) {
                return "GRN received for this Purchase Order. Edit is locked.";
            }
            return "Edit not allowed";
        },

        canDeleteItem(item) {
            if (!item) return true;
            if (typeof this.tableRoutes.canDestroy === "function") {
                return Boolean(this.tableRoutes.canDestroy(item));
            }
            if (typeof this.tableRoutes.canDelete === "function") {
                return Boolean(this.tableRoutes.canDelete(item));
            }
            if (item.can_delete !== undefined && item.can_delete !== null) {
                return Boolean(item.can_delete);
            }
            if (item.grns_count > 0 || (item.receive_status && item.receive_status !== 'Pending')) {
                return false;
            }
            return true;
        },

        deleteDisabledReason(item) {
            if (typeof this.tableRoutes.deleteDisabledReason === "function") {
                return this.tableRoutes.deleteDisabledReason(item);
            }
            if (item && (item.grns_count > 0 || (item.receive_status && item.receive_status !== 'Pending'))) {
                return "GRN received for this Purchase Order. Delete is locked.";
            }
            return "Delete not allowed";
        },

        generateRoute(route) {
            let params = {};
            let query = {};

            let finalRoute = { name: route.route };

            // only for slug param
            if (route.slug && this.item.slug) {
                params["slug"] = this.item.slug;
                query["slug"] = this.item.slug;
            }

            // only for id param
            if (route.isQuery && this.item.id) {
                params["id"] = this.item.id;
                query["id"] = this.item.id;
            }

            // dynamic params
            if (route.params && route.params.length > 0) {
                route.params.forEach((routeParam) => {
                    if (routeParam.value) {
                        params[routeParam.name] = routeParam.value;
                    } else if (
                        routeParam.field &&
                        this.item[routeParam.field]
                    ) {
                        params[routeParam.name] = this.item[routeParam.field];
                    }
                });
            }

            // dynamic query
            if (route.query && route.query.length > 0) {
                route.query.forEach((routeQuery) => {
                    if (routeQuery.value) {
                        query[routeQuery.name] = routeQuery.value;
                    } else if (
                        routeQuery.field &&
                        this.item[routeQuery.field]
                    ) {
                        query[routeQuery.name] = this.item[routeQuery.field];
                    }
                });
            }

            // added params in route
            if (Object.keys(params).length > 0) {
                finalRoute.params = params;
            }

            // added query in route
            if (Object.keys(query).length > 0) {
                finalRoute.query = query;
            }

            return finalRoute;
        },
    },
};
</script>

<style scoped>
.btn-xs {
    padding: 0.18rem 0.45rem;
    font-size: 0.8rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.cursor-not-allowed {
    cursor: not-allowed !important;
    pointer-events: auto;
}
</style>
