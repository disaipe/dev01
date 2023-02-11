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
                        v-for='(_, field) of columnStore'
                        v-model='columnStore[field].visible'
                        :label='columnStore[field].label'
                        :data-id='field'
                        @change='handleColumnVisible(field)'
                    )
</template>

<script>
import { ref, toRef, inject, reactive } from 'vue';
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
        const sortable = reactive(null);

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
        visibleColumns(value) {
            this.table.visibleColumns = value;
        }
    },
    mounted() {
        this.sortable = new Sortable(
            this.sortables,
            {
                // TODO
                // onEnd: (evt) => {
                //     this.saveColumnOrder({
                //         tableId: this.tableId,
                //         order: this.sortable.toArray()
                //     });
                // }
            }
        );
    }
}
</script>
