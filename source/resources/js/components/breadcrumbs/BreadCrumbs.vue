<template lang='pug'>
el-breadcrumb
    el-breadcrumb-item(
        v-for='breadcrumb of breadcrumbs'
        :to='breadcrumb.to'
    ) {{ breadcrumb.title }}
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const breadcrumbs = computed(() => {
    let pathArray = route.path.split('/');
    pathArray.shift();

    return pathArray.reduce((acc, path, idx) => {
        const matched = route.matched[idx];
        const title = matched.meta.title || path;

        acc.push({
            path: path,
            to: acc[idx - 1]
                ? '/' + acc[idx - 1].path + '/' + path
                : '/' + path,
            title
        });

        return acc;
    }, []);
});
</script>
