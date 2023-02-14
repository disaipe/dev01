import { mapActions } from 'pinia';
import defaults from 'lodash/defaults';

import { useTableStore } from '../../../store/modules';

export default {
    data: () => ({
        columnStore: {},
        columnOrder: []
    }),
    computed: {
        visibleColumns() {
            return Object
                .values(this.columnStore)
                .filter((column) => column.visible);
        }
    },
    watch: {
        fields: {
            handler() {
                this.columnStore = this.syncColumnStore();

                // TODO
                // this.columnOrder = ...
            },
            deep: true
        }
    },
    methods: {
        ...mapActions(useTableStore, [
            'loadColumns',
            'saveColumnVisibility',
            'saveColumnOrder'
        ]),

        handleColumnVisible(field) {
            this.saveColumnVisibility({
                tableId: this.tableId,
                field,
                value: this.columnStore[field].visible
            });
        },

        syncColumnStore() {
            let result = {};

            // get params from props
            if (this.columns) {
                for (const column of this.columns) {
                    const { field, label, visible } = column;

                    result[field] = {
                        field,
                        label,
                        visible: visible || true
                    };
                }
            }

            // get params from schema
            for (const [field, schema] of Object.entries(this.fields)) {
                if (!result[field]) {
                    result[field] = {};
                }

                const { label, defaultColumn } = schema;

                defaults(result[field], {
                    field,
                    label,
                    visible: defaultColumn
                });
            }

            // get params from store
            const settings = this.loadColumns(this.tableId);
            if (settings) {
                for (const field of settings.visible) {
                    if (result[field]) {
                        result[field].visible = true;
                    }
                }
            }

            return result;
        }
    }
}
