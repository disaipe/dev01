<template lang='pug'>
.app-wrapper
    el-container
        el-aside(width='200px')
            //- el-scrollbar
            el-menu(router)
                el-menu-item(:route='{ name: "dashboard" }' index='dashboard')
                    el-icon
                        HomeFilled
                    div Главная

                el-menu-item(:route='{ name: "report-invoice" }' index='report')
                    .flex.items-center.space-x-2.pl-1
                        Icon(icon='teenyicons:invoice-outline' height='18')
                        div Отчет

                el-menu-item(
                    v-for='route of routes'
                    :route='{ name: route.name }'
                    :index='route.name'
                )
                    el-icon(v-if='route.meta.icon')
                        component(:is='route.meta.icon')
                    div {{ route.meta.title }}

        el-container
            el-header
                .flex.justify-between.w-full
                    //- left side
                    div
                        bread-crumbs

                    //- right side
                    el-dropdown(
                        trigger='click'
                    )
                        .flex.space-x-2.items-center
                            el-avatar(
                                :src='user.avatar'
                                shape='square'
                                size='small'
                            )
                            .text-gray-400 {{ user.name }}

                        template(#dropdown)
                            el-dropdown-menu
                                el-dropdown-item
                                    el-popconfirm(
                                        title='Завершить сеанс?'
                                        @confirm='logout'
                                        width='200'
                                    )
                                        template(#reference)
                                            span Выйти
            el-main(class='!pr-1')
                //- RouterTabs
                component.pt-1.pr-4.h-full(:is='isRouteScroll ? "el-scrollbar" : "div"')
                    router-view
                    //router-view(v-slot='{ Component }')
                    //    transition(name='fade-transform' mode='out-in')
                    //        keep-alive(:include='cachedViews')
                    //            component(:is='Component')
            //- el-footer Footer
</template>

<script>
import { computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import orderBy from 'lodash/orderBy';

import { useTabsStore } from '../store/modules/tabs';
import usePage from '../utils/usePage';
import BreadCrumbs from '../components/breadcrumbs/BreadCrumbs.vue';

export default {
    name: 'BaseLayout',
    components: { BreadCrumbs },
    setup() {
        const { cachedViews } = useTabsStore();

        const logout = () => window.location.href = '/logout';

        const router = useRouter();

        // get reference routes
        const tempRoutes = [];
        for (const route of router.getRoutes()) {
            if (route.meta?.isReference) {
                tempRoutes.push(route);
            }
        }

        // order reference routes
        const routes = reactive(orderBy(tempRoutes, [(r) => r.meta.order], ['desc']));

        const { user } = usePage();

        return {
            isRouteScroll: computed(() => router.currentRoute.value.meta?.scroll !== false),

            cachedViews,
            routes,
            user,
            logout
        };
    }
};
</script>

<style lang='postcss'>
.app-wrapper {
    @apply w-full h-full;

    .el-container {
        @apply h-full;
    }
}

.el-header {
    @apply shadow flex items-center;
}

.el-menu {
    @apply h-full;
}

.el-main {
    @apply relative w-full overflow-hidden;
}
</style>
