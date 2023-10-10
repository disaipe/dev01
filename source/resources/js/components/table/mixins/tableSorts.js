import { mapActions } from 'pinia';
import { useTableStore } from '../../../store/modules';

export default {
    data() {
        return {
            sortConfig: {
                remote: !!this.repository,
                trigger: 'default',
                showIcon: false,
                multiple: false
            },

            sortsStore: {}
        };
    },

    created() {
        this.applySavedSorts();
    },

    methods: {
        ...mapActions(useTableStore, [
            'loadColumnSorts',
            'saveColumnSorts'
        ]),

        applySavedSorts() {
          const storedSorts = this.loadColumnSorts(this.tableId);

          if (storedSorts) {
              this.sortsStore = storedSorts;

              this.sortConfig.defaultSort = Object
                  .entries(storedSorts)
                  .map((v) => ({ field: v[0], order: v[1]}));
          }
        },

        toggleColumnSort(column) {
            let order = null;

            if (column.sortable) {
                if (column.order === 'desc') {
                    // keep order empty to clear column sort
                } else if (column.order === 'asc') {
                    order = 'desc';
                } else {
                    order = 'asc';
                }
            }

            this.$refs.vxe.triggerSortEvent(null, column, order);
        },

        handleSortChange({ sortList }) {
            const newSorts = {};

            for (const sort of sortList) {
                newSorts[sort.field] = sort.order;
            }

            this.sortsStore = newSorts;

            this.saveColumnSorts({
                tableId: this.tableId,
                sortList: newSorts
            });

            this.load?.();
        }
    }
}
