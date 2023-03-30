<template lang='pug'>
el-dropdown(
    trigger='click'
    placement='bottom-end'
)
    el-button(icon='Grid')

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

<script>
import { ref, toRef, inject } from 'vue';
import Sortable from 'sortablejs';

import tableColumns from './mixins/tableColumns';

export default {
    name: 'TableColumnsSettings',
    functional: true,
    mixins: [tableColumns],
    setup() {
        const table = inject('TableInstance');
        const columns = toRef(table, 'columns');
        const fields = toRef(table, 'fields');

        const sortables = ref(null);
        const sortable = ref(null);

        return {
            table,
            tableId: table.tableId,
            columns,
            fields,
            sortable,
            sortables
        };
    },
    watch: {
        // Watch column settings and sync them to table
        visibleColumns() {
            this.table.visibleColumns = this.visibleColumns;
        }
    },
    mounted() {
        this.sortable = new Sortable(
            this.sortables,
            {
                onEnd: (evt) => {
                    const order = this.sortable.toArray();

                    this.saveColumnOrder({
                        tableId: this.tableId,
                        order
                    });

                    this.$nextTick(() => {
                        this.sortable.sort(order);
                        this.table.$refs.vxe.updateData();
                    });
                }
            }
        );
    }
}
</script>
