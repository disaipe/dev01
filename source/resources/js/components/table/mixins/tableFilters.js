import set from 'lodash/set';
import { mapActions } from 'pinia';
import { useTableStore } from '../../../store/modules';

export default {
    props: {
      filters: {
          type: Object,
          default: () => null
      }
    },
    data: () => ({
        filterStore: {
            filters: {},
            types: {},
            visibility: {},
            inputs: {}
        }
    }),
    computed: {
        hasActiveFilters() {
            return Object.keys(this.filterStore.filters).length > 0;
        }
    },
    created() {
        this.applySavedFilters();
    },
    methods: {
        ...mapActions(useTableStore, [
            'loadFilters',
            'saveFilter',
            'resetFilters'
        ]),

        applySavedFilters() {
            const filters = this.filters || {};
            const storedFilters = this.loadFilters(this.tableId);

            if (storedFilters) {
                Object.assign(filters, storedFilters);
            }

            for (const [field, filter] of Object.entries(filters)) {
                if (typeof filter === 'object') {
                    this.filterStore.filters[field] = filter.value;

                    if (filter.type) {
                        this.filterStore.types[field] = filter.type;
                    }
                } else {
                    this.filterStore.filters[field] = filter;
                }
            }

            this.$emit('filters-ready', this.filterStore.filters);
        },

        resetSavedFilters() {
            this.resetFilters(this.tableId);

            this.filterStore.filters = {};
        },

        getFiltersForRequest() {
            const result = {};

            for (const [key, value] of Object.entries(this.filterStore.filters)) {
                const type = this.filterStore.types[key] || '$eq';
                set(result, `${key}.${type}`, value);
            }

            return result;
        }
    }
};
