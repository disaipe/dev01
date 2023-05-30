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
            visibility: {},
            inputs: {}
        }
    }),
    computed: {
        hasActiveFilters() {
            return Object.keys(this.filterStore?.filters).length > 0;
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

            for (const [filter, valueObject] of Object.entries(filters)) {
                if (typeof valueObject === 'object') {
                    this.filterStore.filters[filter] = valueObject.value;
                } else {
                    this.filterStore.filters[filter] = valueObject;
                }
            }

            this.$emit('filters-ready', this.filterStore.filters);
        },

        resetSavedFilters() {
            this.resetFilters(this.tableId);

            this.filterStore.filters = {};
        }
    }
};
