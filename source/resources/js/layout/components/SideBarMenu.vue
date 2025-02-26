<template lang='pug'>
.side-bar-menu
  .p-4.w-full.flex.flex-col.flex-wrap
    div(class='space-y-1.5')
      side-bar-menu-item(
        v-for='item of routes'
        :index='item.name'
        :name='item.name'
        :route='item.route'
        :icon='item.icon'
        :label='item.label'
        :children='item.children'
      )
</template>

<script setup lang="ts">
import type { SideBarMenuItemProps } from './SideBarMenuItem.vue';

import usePage from '@/utils/usePage';
import groupBy from 'lodash/groupBy';

import orderBy from 'lodash/orderBy';
import { useRouter } from 'vue-router';
import SideBarMenuItem from './SideBarMenuItem.vue';

function makeMenuTree(data: Omit<SideBarMenuItemProps, 'children'>[]) {
  const grouped = groupBy(data, item => item.parent || '__root__');

  function childrenOf(parentId: string) {
    const arr: SideBarMenuItemProps[] = (grouped[parentId] || [])
      .map(item => ({ ...item, children: childrenOf(item.name) }));

    return orderBy(arr, ['order', 'label']);
  }

  return childrenOf('__root__');
}

const router = useRouter();

// get routes with sidebar menu item
const menuRoutes = [];
for (const route of router.getRoutes()) {
  if (route.meta?.isReference) {
    menuRoutes.push(route);
  }
  else if (route.meta?.isRecord) {
    //
  }
  else if (route.meta?.view) {
    menuRoutes.push(route);
  }
}

const menu = usePage().menu;

const flatRoutes: Omit<SideBarMenuItemProps, 'children'>[] = [
  ...menu,

  ...menuRoutes.map(r => ({
    name: r.name!.toString(),
    label: r.meta.title,
    icon: r.meta.icon,
    order: r.meta.order,
    parent: r.meta.menuParent,
    route: { name: r.name!.toString() },
  })),
];

const routes = makeMenuTree(flatRoutes);
</script>

<style scoped lang="postcss">
.side-bar-menu {
  @apply translate-x-0 transition-all duration-300 transform w-60 bg-white border-e border-gray-200 pb-10 overflow-y-auto lg:block lg:translate-x-0 lg:end-auto lg:bottom-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-slate-700 dark:[&::-webkit-scrollbar-thumb]:bg-slate-500 dark:bg-gray-800 dark:border-gray-700;
}
</style>
