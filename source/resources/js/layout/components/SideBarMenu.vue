<template lang='pug'>
.side-bar-menu
    .p-4.w-full.flex.flex-col.flex-wrap
        div(class='space-y-1.5')
            side-bar-menu-item(
                v-for='item of routes'
                :index='item.name'
                :route='item.route'
                :icon='item.icon'
                :label='item.label'
                :children='item.children'
            )
</template>

<script setup>
import { getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import groupBy from 'lodash/groupBy';
import orderBy from 'lodash/orderBy';

import SideBarMenuItem from './SideBarMenuItem.vue';

function makeMenuTree(data) {
    const grouped = groupBy(data, (item) => item.parent || null);

    function childrenOf(parentId) {
        const arr = (grouped[parentId] || [])
            .map((item) => ({ ...item, children: childrenOf(item.name) }));

        return orderBy(arr, ['order', 'label']);
    }

    return childrenOf(null);
}

const app = getCurrentInstance();
const router = useRouter();

// get routes with sidebar menu item
const menuRoutes = [];
for (const route of router.getRoutes()) {
    if (route.meta?.isReference) {
        menuRoutes.push(route);
    } else if (route.meta?.isRecord) {
        //
    } else if (route.meta?.view) {
        menuRoutes.push(route);
    }
}

const menu = app.appContext.config.globalProperties.$page.menu || [];

const flatRoutes = [
    ...menu,

    ...menuRoutes.map((r) => ({
        name: r.name,
        label: r.meta.title,
        icon: r.meta.icon,
        order: r.meta.order,
        parent: r.meta.menuParent,
        route: { name: r.name }
    }))
];

const routes = makeMenuTree(flatRoutes);
</script>

<style scoped lang="postcss">
.side-bar-menu {
    @apply translate-x-0 transition-all duration-300 transform w-60
        bg-white border-e border-gray-200 pb-10 overflow-y-auto
        lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
        [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full
        [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300
        dark:[&::-webkit-scrollbar-track]:bg-slate-700 dark:[&::-webkit-scrollbar-thumb]:bg-slate-500
        dark:bg-gray-800 dark:border-gray-700;
}
</style>
