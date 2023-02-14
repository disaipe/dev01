<template lang='pug'>
.reference-page
    ItTable(
        :reference='reference'
        :can-create='canCreate'
        :can-update='canUpdate'
        :can-delete='canDelete'
    )
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const reference = route.meta?.model;
const canCreate = route.meta?.permissions?.create;
const canUpdate = route.meta?.permissions?.update;
const canDelete = route.meta?.permissions?.delete;
</script>

<script>
export default {
    name: 'BaseReference'
}
</script>
