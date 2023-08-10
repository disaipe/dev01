<template lang='pug'>
el-menu-item(
    v-if='!children.length'
    :route='route'
    :index='index'
)
    .flex.items-center.space-x-2.pl-1
        icon(:icon='icon' height='18')
        div {{ label }}

el-sub-menu(
    v-else
    :index='index'
)
    template(#title)
        .flex.items-center.space-x-2.pl-1
            icon(:icon='icon' height='18')
            div {{ label }}
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
defineProps({
    route: {
        type: Object,
        required: true
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
</script>
