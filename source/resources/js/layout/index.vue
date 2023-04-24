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
                        icon(icon='teenyicons:invoice-outline' height='18')
                        div Отчет

                el-sub-menu(index='references')
                    template(#title)
                        .flex.items-center.space-x-2
                            icon(icon='fluent-mdl2:product-catalog' height='18')
                            div Справочники

                    el-menu-item(
                        v-for='route of routes'
                        :route='{ name: route.name }'
                        :index='route.name'
                    )
                        //- el-icon(v-if='route.meta.icon')
                        //-     icon(:icon='route.meta.icon' height='18')
                        div {{ route.meta.title }}

        el-container
            el-header
                .flex.justify-between.w-full
                    //- left side
                    div
                        bread-crumbs

                    //- right side
                    .flex.items-center.space-x-2
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
                                    el-dropdown-item(@click='openSettingsDrawer')
                                        .flex.items-center.space-x-1
                                            icon(icon='material-symbols:display-settings-outline-rounded' height='16')
                                            span Настройки

                                    el-dropdown-item(v-if='user.hasAdminAccess')
                                        a.flex.items-center.space-x-1(href='/admin')
                                            icon(icon='material-symbols:settings-alert' height='16')
                                            span Управление

                                    el-dropdown-item
                                        el-popconfirm(
                                            title='Завершить сеанс?'
                                            @confirm='logout'
                                            width='200'
                                        )
                                            template(#reference)
                                                .flex.items-center.space-x-1
                                                    icon(icon='tabler:logout' height='16')
                                                    span Выйти
            el-main(class='!pr-1')
                //- RouterTabs
                component.pt-1.pr-4.h-full(:is='isRouteScroll ? "el-scrollbar" : "div"')
                    router-view
                    //router-view(v-slot='{ Component }')
                    //    transition(name='fade-transform' mode='out-in')
                    //        keep-alive(:include='cachedViews')
                    //            component(:is='Component')

                el-drawer(
                    v-model='drawer'
                    title='Настройки'
                )
                    el-scrollbar.pr-4
                        .text-xs.text-gray-400.mb-1.
                            Способ отображения формы создания и редактирования записей -
                            в правом боковом меню или модальном окне
                        el-select(v-model='profileSettings.formDisplayType')
                            el-option(value='drawer' label='Боковое меню')
                                .flex.items-center.space-x-2
                                    icon.text-xl(icon='tabler:layout-sidebar-right')
                                    span Боковое меню
                            el-option(value='modal' label='Модальное окно')
                                .flex.items-center.space-x-2
                                    icon.text-xl(icon='tabler:app-window')
                                    span Модальное окно
            //- el-footer Footer
</template>

<script>
import { ref, computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import orderBy from 'lodash/orderBy';

import { useTabsStore, useProfilesSettingsStore } from '../store/modules';
import usePage from '../utils/usePage';
import BreadCrumbs from '../components/breadcrumbs/BreadCrumbs.vue';

export default {
    name: 'BaseLayout',
    components: { BreadCrumbs },
    setup() {
        const { cachedViews } = useTabsStore();

        const drawer = ref(false);

        const profileSettings = useProfilesSettingsStore();

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

            drawer,
            profileSettings,

            openSettingsDrawer: () => drawer.value = true,
            logout: () => window.location.href = '/logout'
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
