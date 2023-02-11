import { defineStore } from 'pinia';

const state = () => ({
    filters: {
        // [tableId]: {
        //   [field]: {
        //     value: [value]
        //   }
        // }
    },
    columns: {
        // [tableId]: {
        //      visible: [],
        //      order: []
        // }
    },
    expanded: {
        // [tableId]: []
    }
})

const actions = {
    loadFilters(tableId) {
        return this.filters[tableId];
    },

    saveFilter({ tableId, field, value }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (!state.filters[tableId]) {
                state.filters[tableId] = {};
            }

            if (value === undefined) {
                delete this.filters[tableId][field];
            } else {
                state.filters[tableId][field] = { value };
            }
        });
    },

    resetFilters(tableId) {
        delete this.filters[tableId];
    },

    loadColumns(tableId) {
        return this.columns[tableId];
    },

    saveColumnVisibility({ tableId, field, value }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (!state.columns[tableId]) {
                state.columns[tableId] = {
                    visible: [],
                    order: []
                };
            }

            const idx = state.columns[tableId].visible.findIndex((v) => v === field);

            if (value && idx === -1) {
                state.columns[tableId].visible.push(field);
            } else if (!value && idx > -1) {
                state.columns[tableId].visible.splice(idx, 1);
            }
        });
    },

    saveColumnOrder({ tableId, order }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (!state.columns[tableId]) {
                state.columns[tableId] = {
                    visible: [],
                    order: []
                };
            }

            state.columns[tableId].order = order;
        });
    },

    loadExpanded(tableId) {
        return this.expanded[tableId] || [];
    },

    saveExpanded({ tableId, expanded }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (Array.isArray(expanded) && expanded.length) {
                state.expanded[tableId] = expanded;
            } else {
                delete state.expanded[tableId];
            }
        });
    }
}

export const useTableStore = defineStore('table', {
    state,
    actions,
    persist: {
        paths: ['filters', 'columns', 'expanded']
    }
});
