import { mapActions } from 'pinia';
import defaults from 'lodash/defaults';

import { useTableStore } from '../../../store/modules';
import sortBy from 'lodash/sortBy';

export default {
    data: () => ({
        columnStore: {}
    }),
    computed: {
        /**
         * Returns all available table columns
         * Used to TableColumnsSetting
         * @returns {Object[]}
         */
        allowedColumns() {
            const order = this.loadColumns(this.tableId).order;

            const columns = Object
                .values(this.columnStore)
                .filter((column) => !column.hidden);

            return sortBy(columns, (c) => order.indexOf(c.field));
        },

        /**
         * Returns all visible at now columns
         * @returns {Object[]}
         */
        visibleColumns() {
            return this.allowedColumns.filter((column) => column.visible);
        }
    },
    watch: {
        fields: {
            handler() {
                this.columnStore = this.syncColumnStore();
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

                const { label, visible, hidden } = schema;

                defaults(result[field], {
                    field,
                    label,
                    hidden,
                    visible
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
