<template lang='pug'>
component.side-bar-menu__item(
    :is='route ? "router-link" : "div"'
    :to='route'
    :class='{ "is-active": isActive, "is-open": subMenuOpen }'
    @click='route ? null : toggleSubMenu()'
)
    icon.side-bar-menu__item__icon(v-show='icon' :icon='icon' height='18')

    .flex-1 {{ label }}

    icon.side-bar-menu__item__arrow(v-show='children.length' icon='tabler:chevron-down')

template(v-if='children.length')
    .side-bar-menu__sub-menu(:class='subMenuClasses')
        .overflow-hidden
            side-bar-menu-item(
                v-for='child of children'
                :index='child.name'
                :route='child.route'
                :icon='child.icon'
                :label='child.label'
                :children='child.children'
            )
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
    route: {
        type: Object,
    },
    icon: {
        type: String,
        default: null
    },
    label: {
        type: String,
        default: null
    },
    index: {
        type: String,
        default: null,
    },
    children: {
        type: Array,
        default: () => ([])
    }
});

const router = useRouter();

const subMenuOpen = ref(false);

const subMenuClasses = computed(() => {
    return subMenuOpen.value
        ? 'grid-rows-[1fr] opacity-100'
        : 'grid-rows-[0fr] opacity-0';
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
    @apply flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-slate-700 rounded-lg cursor-pointer
    hover:bg-gray-100
    dark:bg-gray-900 dark:text-white dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600;

    &.is-active {
        @apply text-blue-600;
    }

    &.is-open {
        .side-bar-menu__item__arrow {
            transform: rotate(180deg);
        }
    }

    .side-bar-menu__item__arrow {
        @apply transition-all;
    }
}

.side-bar-menu__sub-menu {
    @apply grid ps-3 pt-2 transition-all duration-300 ease-in-out;
}
</style>
