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
                <router-link
                    v-if="tableRoutes.edit && $root.checkPermission(tableRoutes.edit)"
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

                <!-- Delete Action (Icon only) -->
                <a
                    href="javascript:void(0)"
                    v-if="tableRoutes.destroy && $root.checkPermission(tableRoutes.destroy)"
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
</style>
