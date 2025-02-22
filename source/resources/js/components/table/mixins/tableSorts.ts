import type { ReferenceRepository } from '@/store';
import type { SortStore } from '@/types';

import type { VxeTableDefines, VxeTablePropTypes } from 'vxe-table';
import { useTableStore } from '@/store/modules';
import { onMounted, reactive, ref } from 'vue';

interface Props {
  repository?: ReferenceRepository;
  vxe: any;
  load: () => void;
}

export function useTableSorts(tableId: string, { repository, vxe, load }: Props) {
  const sortConfig = ref<VxeTablePropTypes.SortConfig>({
    remote: !!repository,
    trigger: 'default',
    showIcon: false,
    multiple: false,
  });

  const sortsStore = reactive<SortStore>({});

  const { loadColumnSorts, saveColumnSorts } = useTableStore();

  function applySavedSorts() {
    const storedSorts = loadColumnSorts(tableId);

    if (storedSorts) {
      Object.assign(sortsStore, storedSorts);

      sortConfig.value.defaultSort = Object
        .entries(storedSorts)
        .map(v => ({ field: v[0], order: v[1] }));
    }
  }

  function toggleColumnSort(column: VxeTableDefines.ColumnInfo) {
    let order = null;

    if (column.sortable) {
      if (column.order === 'desc') {
        // keep order empty to clear column sort
      }
      else if (column.order === 'asc') {
        order = 'desc';
      }
      else {
        order = 'asc';
      }
    }

    vxe.value?.triggerSortEvent(null, column, order);
  }

  function handleSortChange({ sortList }: VxeTableDefines.SortChangeEventParams) {
    const newSorts: SortStore = {};

    for (const sort of sortList) {
      newSorts[sort.field] = sort.order;
    }

    Object.assign(sortsStore, newSorts);

    saveColumnSorts({
      tableId,
      sortList: newSorts,
    });

    load?.();
  }

  onMounted(() => {
    applySavedSorts();
  });

  return {
    sortConfig,
    sortsStore,

    loadColumnSorts,
    saveColumnSorts,

    applySavedSorts,
    toggleColumnSort,
    handleSortChange,
  };
}

export default useTableSorts;
