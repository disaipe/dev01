<template lang='pug'>
.reference-page
    ItTable(
        :reference='reference'
        :can-create='canCreate'
        :can-update='canUpdate'
        :can-delete='canDelete'
    )
        template(#columns-before)
            vxe-column(v-if='hasRecordView' width='40px')
                template(#default='{ row }')
                    el-link(:underline='false' @click='editRecord(row.$getKey())')
                        icon(icon='tabler:list-details' height='20')
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const reference = route.meta?.model;
const canCreate = route.meta?.permissions?.create;
const canUpdate = route.meta?.permissions?.update;
const canDelete = route.meta?.permissions?.delete;
const hasRecordView = !!route.meta?.recordView;

const editRecord = (id) => {
    router.push({ name: route.meta?.recordView, params: { id } });
};
</script>

<script>
export default {
    name: 'BaseReference'
}
</script>
