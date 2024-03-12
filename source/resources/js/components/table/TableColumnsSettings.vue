<template lang="pug">
el-dropdown(
    trigger='click'
    placement='bottom-end'
)
    el-button
        icon(icon='tabler:columns-3' height='16')

    template(#dropdown)
        .max-h-64
            el-scrollbar
                .flex.flex-col.p-2(ref='sortables')
                    el-checkbox(
                        v-for='{ field } of allowedColumns'
                        v-model='columnStore[field].visible'
                        :label='columnStore[field].label'
                        :data-id='field'
                        @change='handleColumnVisible(field)'
                    )
</template>

<script setup>
import { ref, inject, onMounted, nextTick, watch } from 'vue';
import Sortable from 'sortablejs';

import { useTableColumns } from './mixins/tableColumns';

const { 
    tableId, 
    fields, 
    columns, 
    vxe, 
    setVisibleColumns
} = inject('TableInstance');

const sortable = ref();
const sortables = ref();

const {
    columnStore,

    allowedColumns,
    visibleColumns,

    loadColumns,
    saveColumnVisibility,
    saveColumnOrder,

    handleColumnVisible
} = useTableColumns(tableId, { fields, columns });

watch(() => visibleColumns.value, () => {
    setVisibleColumns(visibleColumns.value);
});

onMounted(() => {
    sortable.value = new Sortable(sortables.value, {
        onEnd: () => {
            const order = sortable.value.toArray();

            saveColumnOrder({ tableId, order });

            nextTick(() => {
                sortable.value.sort(order);
                vxe.value?.updateData();
            });
        }
    });
});
</script>

<script>
export default {
    name: 'TableColumnsSettings'
}
</script>