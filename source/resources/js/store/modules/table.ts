import clone from 'lodash/clone';
import { defineStore } from 'pinia';

import type { SortOrder } from '@/types';

const COLUMN_DEFAULT_STATE = {
    visible: [],
    order: []
}

type FilterField = {
    value: string | number;
    type: string;
};

type Filter = Record<string, FilterField>;
type Filters = Record<string, Filter>;

type Sort = Record<string, SortOrder>;
type Sorts = Record<string, Sort>;

type Column = {
    visible: string[];
    order: string[];
}
type Columns = Record<string, Column>;

type State = {
    filters: Filters;
    sorts: Sorts;
    columns: Columns;
}

export const useTableStore = defineStore('table', {
    state: (): State => ({
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
    }),

    actions: {

        //---------------------------------------------------------
        //  FILTERS
        //---------------------------------------------------------
    
        loadFilters(tableId: string) {
            return this.filters[tableId];
        },
    
        saveFilter({ tableId, field, type, value }: { tableId: string, field: string, type: string, value: string | number }) {
            if (!tableId) {
                return;
            }
    
            this.$patch((state: State) => {
                if (!state.filters[tableId]) {
                    state.filters[tableId] = {};
                }
    
                if (value === undefined || (Array.isArray(value) && !value.length)) {
                    delete state.filters[tableId][field];
                } else {
                    state.filters[tableId][field] = <FilterField>{ type, value };
                }
            });
        },
    
        resetFilters(tableId: string) {
            delete this.filters[tableId];
        },
    
        //---------------------------------------------------------
        //  COLUMN SORTS
        //---------------------------------------------------------
    
        loadColumnSorts(tableId: string) {
            return this.sorts[tableId];
        },
    
        saveColumnSorts({ tableId, sortList }: { tableId: string, sortList: Sort }) {
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
    
        loadColumns(tableId: string) {
            return this.columns[tableId] || clone(COLUMN_DEFAULT_STATE);
        },
    
        saveColumnVisibility({ tableId, field, value }: { tableId: string, field: string, value: boolean }) {
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
    
        saveColumnOrder({ tableId, order }: { tableId: string, order: string[] }) {
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
    },

    persist: {
        paths: ['filters', 'sorts', 'columns']
    }
});
