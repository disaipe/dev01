import { mapActions } from 'pinia';
import { useTableStore } from '../../../store/modules';

export default {
    data: () => ({
        filterStore: {
            filters: {},
            visibility: {},
            inputs: {}
        }
    }),
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
            const filters = this.loadFilters(this.tableId);

            if (filters) {
                for (const [filter, valueObject] of Object.entries(filters)) {
                    this.filterStore.filters[filter] = valueObject.value;
                }
            }

            this.$emit('filters-ready', this.filterStore.filters);
        }
    }
};
