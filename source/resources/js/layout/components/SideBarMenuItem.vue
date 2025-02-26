<template lang='pug'>
component.side-bar-menu__item(
  :is='route ? "router-link" : "div"'
  :to='route'
  :class='{ "is-active": isActive, "is-open": subMenuOpen }'
  @click='route ? null : toggleSubMenu()'
)
  icon.side-bar-menu__item__icon(v-show='icon' :icon='icon || "tabler:file-filled"' height='18')

  .flex-1 {{ label }}

  icon.side-bar-menu__item__arrow(v-show='children.length' icon='tabler:chevron-down')

template(v-if='children.length')
  .side-bar-menu__sub-menu(:class='subMenuClasses')
    .overflow-hidden
      side-bar-menu-item(
        v-for='child of children'
        :index='child.name'
        :name='child.name'
        :route='child.route'
        :icon='child.icon'
        :label='child.label'
        :children='child.children'
      )
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

export interface SideBarMenuItemRoute {
  /** Route name */
  name: string;
}

export interface SideBarMenuItemProps {
  /** Menu item name */
  name: string;
  /** Icon name */
  icon?: string;
  /** Menu item label */
  label?: string;
  /** Index key */
  index?: string;
  /** Parent name */
  parent?: string | null;
  /** Route props */
  route?: SideBarMenuItemRoute;
  /** Children items */
  children?: SideBarMenuItemProps[];
}

defineOptions({
  components: { Icon },
});

const props = withDefaults(defineProps<SideBarMenuItemProps>(), {
  children: () => ([]),
});

const router = useRouter();

const subMenuOpen = ref(false);

const subMenuClasses = computed(() => {
  return subMenuOpen.value
    ? 'grid-rows-[1fr] opacity-100'
    : 'grid-rows-[0fr] opacity-0 !mt-0';
});

const isActive = computed(() => {
  return router.currentRoute.value.name === props.route?.name;
});

function toggleSubMenu() {
  subMenuOpen.value = !subMenuOpen.value;
}
</script>

<style lang='postcss' scoped>
.side-bar-menu__item {
  @apply flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-slate-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:bg-gray-900 dark:text-white dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600;

  &.is-active {
    @apply text-blue-600;
  }

  &.is-open {
    .side-bar-menu__item__arrow {
      transform: rotate(180deg);
    }
  }

  &__icon {
    @apply shrink-0;
  }

  &__arrow {
    @apply transition-all;
  }
}

.side-bar-menu__sub-menu {
  @apply grid ps-3 transition-all duration-300 ease-in-out;
}
</style>
