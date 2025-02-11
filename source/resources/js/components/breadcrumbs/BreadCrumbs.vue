<template lang='pug'>
el-breadcrumb
    el-breadcrumb-item(
        v-for='breadcrumb of breadcrumbs'
        :to='breadcrumb.to'
    ) {{ breadcrumb.title }}
</template>

<script setup lang="ts">
import type { BreadcrumbItemProps } from 'element-plus';
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

interface BreadcrumbItem extends BreadcrumbItemProps {
  path: string;
  title: string;
}

const breadcrumbs = computed(() => {
  const pathArray = route.path.split('/');
  pathArray.shift();

  return pathArray.reduce((acc: BreadcrumbItem[], path: string, idx: number) => {
    const matched = route.matched[idx];
    const title = matched.meta?.title || path;

    acc.push({
      path,
      to: acc[idx - 1]
        ? `/${acc[idx - 1].path}/${path}`
        : `/${path}`,
      title,
    } as BreadcrumbItem);

    return acc;
  }, []);
});
</script>
