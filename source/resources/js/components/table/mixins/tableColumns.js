import { mapActions } from 'pinia';
import defaults from 'lodash/defaults';

import { useTableStore } from '../../../store/modules';
import sortBy from 'lodash/sortBy';

const HIDDEN_BY_DEFAULT = [
    'id',
    'created_at',
    'updated_at',
    'deleted_at'
];

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

            // check for visible columns and display all columns (almost) if visibility settings are not set
            const columns = Object.values(result).filter((column) => column.visible);

            if (!columns.length) {
                for (const column of Object.values(result)) {
                    if (!column.hidden && !HIDDEN_BY_DEFAULT.includes(column.field)) {
                        column.visible = true;

                        this.saveColumnVisibility({
                            tableId: this.tableId,
                            field: column.field,
                            value: true
                        });
                    }
                }
            }

            return result;
        }
    }
}
