import type { ModelSchema, TableColumnOptions } from '@/types';
import type { Ref } from 'vue';
import { useTableStore } from '@/store/modules';

import defaults from 'lodash/defaults';
import sortBy from 'lodash/sortBy';
import { computed, reactive, watch } from 'vue';

const HIDDEN_BY_DEFAULT = [
  'id',
  'created_at',
  'updated_at',
  'deleted_at',
];

type ColumnStore = Record<string, TableColumnOptions>;

interface Props {
  fields: Ref<ModelSchema>;
  columns: Ref<TableColumnOptions[]>;
}

export function useTableColumns(tableId: string, { fields, columns }: Props) {
  const {
    loadColumns,
    saveColumnVisibility,
    saveColumnOrder,
  } = useTableStore();

  const columnStore = reactive<ColumnStore>({});

  const allowedColumns = computed(() => {
    const order = loadColumns(tableId).order;

    const _columns = Object
      .values(columnStore)
      .filter(column => !column.hidden);

    return sortBy(_columns, c => order.indexOf(c.field));
  });

  const visibleColumns = computed(() => allowedColumns.value.filter(column => column.visible));

  watch(() => fields.value, () => Object.assign(columnStore, syncColumnStore()), { deep: true });

  function handleColumnVisible(field: string) {
    saveColumnVisibility({
      tableId,
      field,
      value: columnStore[field].visible === true || false,
    });
  }

  function syncColumnStore() {
    const result = {} as ColumnStore;

    // get params from props
    if (columns.value) {
      for (const column of columns.value) {
        const { field, label, visible } = column;

        result[field] = {
          field,
          label,
          visible: visible || true,
        };
      }
    }

    // get params from schema
    for (const [field, schema] of Object.entries(fields.value)) {
      if (!result[field]) {
        result[field] = {} as TableColumnOptions;
      }

      const { label, visible, hidden } = schema;

      defaults(result[field], {
        field,
        label,
        hidden,
        visible,
      });
    }

    // get params from store
    const settings = loadColumns(tableId);
    if (settings) {
      for (const field of settings.visible) {
        if (result[field]) {
          result[field].visible = true;
        }
      }
    }

    // check for visible columns and display all columns (almost) if visibility settings are not set
    const _columns = Object.values(result).filter(column => column.visible);

    if (!_columns.length) {
      for (const column of Object.values(result)) {
        if (!column.hidden && !HIDDEN_BY_DEFAULT.includes(column.field)) {
          column.visible = true;

          saveColumnVisibility({
            tableId,
            field: column.field,
            value: true,
          });
        }
      }
    }

    return result;
  }

  return {
    columnStore,

    allowedColumns,
    visibleColumns,

    loadColumns,
    saveColumnVisibility,
    saveColumnOrder,

    handleColumnVisible,
  };
}

export default useTableColumns;
