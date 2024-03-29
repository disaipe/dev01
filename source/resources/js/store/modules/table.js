import { defineStore } from 'pinia';
import clone from 'lodash/clone';

const COLUMN_DEFAULT_STATE = {
    visible: [],
    order: []
}

const state = () => ({
    filters: {
        // [tableId]: {
        //   [field]: {
        //     value: [value],
        //     type: [filter type]
        //   }
        // }
    },
    sorts: {
      // [tableId]: {
      //   [field]: [order]
      //}
    },
    columns: {
        // [tableId]: {
        //      visible: [],
        //      order: []
        // }
    }
});

const actions = {

    //---------------------------------------------------------
    //  FILTERS
    //---------------------------------------------------------

    loadFilters(tableId) {
        return this.filters[tableId];
    },

    saveFilter({ tableId, field, type, value }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (!state.filters[tableId]) {
                state.filters[tableId] = {};
            }

            if (value === undefined || (Array.isArray(value) && !value.length)) {
                delete state.filters[tableId][field];
            } else {
                state.filters[tableId][field] = { type, value };
            }
        });
    },

    resetFilters(tableId) {
        delete this.filters[tableId];
    },

    //---------------------------------------------------------
    //  COLUMN SORTS
    //---------------------------------------------------------

    loadColumnSorts(tableId) {
        return this.sorts[tableId];
    },

    saveColumnSorts({ tableId, sortList }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (!sortList || !Object.keys(sortList).length ) {
                delete state.sorts[tableId];
            } else {
                state.sorts[tableId] = sortList;
            }
        });
    },

    //---------------------------------------------------------
    //  COLUMNS VISIBILITY AND ORDER
    //---------------------------------------------------------

    loadColumns(tableId) {
        return this.columns[tableId] || clone(COLUMN_DEFAULT_STATE);
    },

    saveColumnVisibility({ tableId, field, value }) {
        if (!tableId) {
            return;
        }

        this.$patch((state) => {
            if (!state.columns[tableId]) {
                state.columns[tableId] = clone(COLUMN_DEFAULT_STATE);
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
                state.columns[tableId] = clone(COLUMN_DEFAULT_STATE);
            }

            state.columns[tableId].order = order;
        });
    }
};

export const useTableStore = defineStore('table', {
    state,
    actions,
    persist: {
        paths: ['filters', 'sorts', 'columns']
    }
});
