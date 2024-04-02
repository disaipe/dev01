import set from 'lodash/set';
import { reactive, computed, onMounted } from 'vue';

import type { TableProps } from '../tableProps';
import { useTableStore } from '@/store/modules';

type Props = {
    props: TableProps;
    emit: (event: string, ...args: any[]) => void;
};

type FilterStore = {
    filters: Record<string, string | number | boolean>;
    types: Record<string, string>;
    visibility: Record<string, boolean>;
    inputs: Record<string, string>;
};

export function useTableFilter(tableId: string, { props, emit }: Props) {
    const {
        loadFilters,
        saveFilter,
        resetFilters
    } = useTableStore();

    const filterStore = reactive<FilterStore>({
        filters: {},
        types: {},
        visibility: {},
        inputs: {}
    });

    const hasActiveFilters = computed(() => Object.keys(filterStore.filters).length > 0);

    function applySavedFilters() {
        const filters = props.filters || {};
        const storedFilters = loadFilters(tableId);

        if (storedFilters) {
            Object.assign(filters, storedFilters);
        }

        for (const [field, filter] of Object.entries(filters)) {
            if (typeof filter === 'object') {
                filterStore.filters[field] = filter.value;

                if (filter.type) {
                    filterStore.types[field] = filter.type;
                }
            } else {
                filterStore.filters[field] = filter;
            }
        }

        emit('filters-ready', filterStore.filters);
    }

    function resetSavedFilters() {
        resetFilters(tableId);

        filterStore.filters = {};
    }

    function getFiltersForRequest() {
        const result = {};

        for (const [key, value] of Object.entries(filterStore.filters)) {
            const type = filterStore.types[key] || '$eq';
            set(result, `${key}.${type}`, value);
        }

        return result;
    }

    onMounted(() => {
        applySavedFilters();
    });

    return {
        filterStore,

        hasActiveFilters,

        loadFilters,
        saveFilter,
        resetFilters,
        resetSavedFilters,
        getFiltersForRequest
    };
}

export default useTableFilter;