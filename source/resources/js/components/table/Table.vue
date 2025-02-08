<template lang="pug">
.it-table
    .errors-list(v-if='errors.length')
        el-alert(title='Что-то пошло не так' type='error' :closable='false' show-icon)
            ul
                li(v-for='error of errors') {{ error }}

    .flex.flex-col.space-y-2(v-else)
        .flex.items-center.justify-between
            el-button-group
                el-button(v-if='canCreate' @click='create') Создать
                    template(#icon)
                        icon(icon='tabler:circle-plus')

                el-button(v-if='canLoad' @click='load') Обновить
                    template(#icon)
                        icon(icon='tabler:refresh')

            .space-x-2
                el-button(
                    v-if='hasActiveFilters'
                    text
                    @click='handleResetFilters'
                )
                    .flex.space-x-1
                        icon(icon='tabler:filter-off')
                        span Сбросить фильтры

                table-export-settings(@on-export='download')
                table-columns-settings

        vxe-table(
            v-if='fields || columns'
            ref='vxe'
            id='tableId'
            border
            size='small'
            :loading='loading'
            :loading-config='loadingConfig'
            :data='verifiedData'
            :row-config='rowConfig'
            :sort-config='sortConfig'
            :menu-config='menuConfig'
            @cell-dblclick='handleRowDblClick'
            @menu-click='onContextMenuClick'
            @sort-change='handleSortChange'
        )
            template(#default)
                vxe-column(
                    :visible='!hasVisibleColumns'
                )
                    el-alert(type='warning' title='Не выбраны видимые колонки')

                template(v-if='hasVisibleColumns')
                    slot(name='columns-before')

                vxe-column(
                    v-for='({ field, label }, i) of visibleColumns'
                    sortable
                    :field='field'
                    :label='label'
                    :cell-render='fields ? { name: "model-field", fields } : undefined'
                )
                    template(#header='{ column }')
                        .flex.items-center.justify-between.space-x-2.leading-3
                            span.cursor-pointer(
                              style='word-break: break-word'
                              @click='toggleColumnSort(column)'
                            ) {{ label }}

                            .flex.items-center
                              icon(
                                v-show='column.order'
                                :icon='getColumnOrderIcon(column.order)'
                                height='16'
                              )

                              table-filter(
                                  v-if='fields'
                                  :field='field'
                                  :schema='fields[field]'
                                  @filter-change='handleFilter'
                                  @click.stop
                              )

                vxe-column(
                    :visible='canDelete && hasVisibleColumns'
                    fixed='right'
                    width='36px'
                )
                  template(#default='{ row }')
                    el-link(:underline='false' @click='confirmRowRemoving(row)')
                      icon.text-gray-300(class='hover:text-red-400' icon='tabler:trash')

            template(#empty)
                el-empty(description='Данные не пришли...')
                    el-button(
                        v-if='hasActiveFilters'
                        type='primary'
                        @click='handleResetFilters'
                    )
                        .flex.space-x-1
                            icon(icon='clarity:filter-off-solid')
                            span Сбросить фильтры, может поможет

        el-pagination(
            v-model:current-page='pagination.page'
            v-model:page-size='pagination.pageSize'
            layout='prev, pager, next, sizes, ->, total'
            :page-sizes='[100, 200, 300]'
            :total='pagination.total'
            @current-change='handlePageChange'
        )

    component(
        :is='drawerComponent'
        v-model='drawer'
        @closed='closeDrawer'
    )
        template(#header)
            .py-2(v-if='selectedRow')
                .text-lg.font-bold.text-gray-600 {{ selectedRow.$getName() }}
                .text-xs.text-gray-500
                    div(v-if='selectedRow.$isSaved() && canUpdate') Редактирование
                    div(v-else-if='!selectedRow.$isSaved() && canCreate') Создание
                    div(v-else) Просмотр

        el-scrollbar.pr-4(v-if='selectedRow')
            record-form(
                v-model='selectedRow'
                :can-create='canCreate'
                :can-update='canUpdate'
                :can-delete='canDelete'
                @saved='save'
                @removed='remove'
            )

    el-dialog(
        v-model='historyDialog'
        title='История изменений'
        width='70vw'
    )
        history-table(
            v-if='contextRow?.$getKey()'
            :reference='reference'
            :id='contextRow.$getKey()'
        )
</template>

<script setup lang="ts">
import type { Model } from 'pinia-orm';
import type { VxeTableDefines, VxeTableInstance } from 'vxe-table';

import { ref, toRef, computed, reactive, onMounted, nextTick, provide } from 'vue';
import { useRoute } from 'vue-router';
import merge from 'lodash/merge';
import cloneDeep from 'lodash/cloneDeep';
import { ElMessage, ElMessageBox } from 'element-plus';

import type { TableProps } from './tableProps';

import { useRepos } from '../../store/repository';
import { snake } from '../../utils/stringsUtils';
import { raiseErrorMessage } from '../../utils/exceptions';

import { useTableSorts } from './mixins/tableSorts';
import { useTableFilter } from './mixins/tableFilters';
import { useTableContextMenu } from './mixins/tableContextMenu';

import { useProfilesSettingsStore } from '../../store/modules';

import TableFilter from './TableFilter.vue';
import TableColumnsSettings from './TableColumnsSettings.vue';
import TableExportSettings from './TableExportSettings.vue';

import type { 
    ModelSchema,
    ErrorResponse, 
    FetchQueryDownloadOptions, 
    FetchQueryParams, 
    TableColumnOptions
} from '@/types';

interface CellDblclickEventParams extends VxeTableDefines.CellDblclickEventParams {
    row: Model;
};

const emit = defineEmits<{ (event: string, ...args: any[]): void }>();

const props = withDefaults(defineProps<TableProps>(), {
    id: Math.random().toString(36).substring(7),
    columns: () => [],
    canLoad: true,
    canCreate: true,
    canUpdate: true,
    canDelete: true
});

const errors = ref<string[]>([]);
const id = toRef(props, 'id');
const reference = toRef(props, 'reference');
const columns = toRef(props, 'columns');

const route = useRoute();
const tableId = [route.name, snake(reference.value || id.value)].join('_');

const repository = reference.value ? useRepos()[reference.value] : undefined;

// User profile settings
const profilesSettings = useProfilesSettingsStore();
const drawerComponent = computed(() => profilesSettings.formDisplayType === 'modal'
    ? 'el-dialog'
    : 'el-drawer'
);

const visibleColumns = ref(columns.value);
const hasVisibleColumns = computed(() => visibleColumns.value?.length > 0);

// Get model fields schema
const fields = ref<ModelSchema>();
if (repository) {
    repository.getFieldsSchema().then((schema) => {
        fields.value = schema;
    });
} else if (columns.value) {
    // fields.value = columns.value.reduce((cur, acc) => {
    //   acc[cur.field] = { label: cur.label };
    //
    //   return acc;
    // }, {});
} else {
    errors.value.push('Не задана модель данных Pinia. Без схемы полей данных дальнейшая работа невозможна');
}

const vxe = ref<VxeTableInstance>();
const data = ref<any[]>([]);

const pagination = reactive({
    page: 1,
    pageSize: 100,
    total: 0
});

const selectedRow = ref<Model>();
const contextRow = ref<Model>();

selectedRow.value?.$getLocalKey

const drawer = ref(false);
const historyDialog = ref(false);
const loading = ref(false);

const loadingConfig = {
    text: 'Собираем данные'
};

const rowConfig  = {
    useKey: true,
    keyField: repository?.getModel().$getSingleKeyName()
};

const verifiedData = computed(() => {
    if (!visibleColumns.value.length) {
        return [{}];
    }

    return data.value;
});

const {
    filterStore,

    hasActiveFilters,

    loadFilters,
    saveFilter,
    resetFilters,
    resetSavedFilters,
    getFiltersForRequest
} = useTableFilter(tableId, { props, emit });

const {
    menuConfig,
    setContextMenuAction,
    onContextMenuClick
} = useTableContextMenu(tableId, { props });

setContextMenuAction('onContextRowOpen', onContextRowOpen);
setContextMenuAction('onContextRowRemove', onContextRowRemove);
setContextMenuAction('onContextRowHistory', onContextRowHistory);

const {
    sortsStore,
    sortConfig,
    toggleColumnSort,
    handleSortChange
} = useTableSorts(tableId, { repository, vxe, load });

onMounted(() => {
    if (props.items) {
        data.value = props.items;
    } else {
        load();
    }
});

function load() {
    if (repository) {
        repository
            .fetchRelatedModels()
            .then(() => {
                loadPages();
            })
            .catch((response: Error | ErrorResponse) => {
                let message;

                if (response instanceof Error) {
                    message = response.message;
                } else {
                    message = `(${response.status}) ${response.statusText}`;
                }

                raiseErrorMessage(message, 'Ошибка загрузки связанных записей');

                console.error(response);
            });
    }
}

function getQueryParams(): FetchQueryParams {
    const query: FetchQueryParams = {
        filters: cloneDeep(props.context || {})
    };

    if (pagination.page) {
        query.page = pagination.page;
    }

    if (pagination.pageSize) {
        query.perPage = pagination.pageSize;
    }

    if (Object.keys(filterStore.filters || {}).length) {
        merge(query.filters, getFiltersForRequest());
    }

    if (Object.keys(sortsStore || {}).length) {
        query.order = sortsStore;
    }

    return query;
}

function loadPages() {
    if (!repository) {
        return;
    }

    loading.value = true;

    const query = getQueryParams();

    return repository
        .fetch(query)
        .then(({ response, items }) => {
            const { status, total } = response.data;

            if (status && items) {
                const eagerLoad = repository.getEagerLoad();

                if (eagerLoad && eagerLoad.length) {
                    let query = repository.query();

                    for (const eager of eagerLoad) {
                        query = query.with(eager);
                    }

                    query.load(items);
                }

                data.value = items;
                pagination.total = total;
            }
        })
        .catch((response) => {
            const message = `(${response.status}) ${response.statusText}`;
            raiseErrorMessage(message);
        })
        .finally(() => {
            nextTick(() => {
                loading.value = false;
            });
        });
}

function download(options: FetchQueryDownloadOptions) {
    if (!repository) {
        return;
    }

    loading.value = true;

    const query = getQueryParams();
    query.columns = visibleColumns.value.map((column) => column.field);
    query.options = options;

    return repository
        .export(query)
        .catch((response: ErrorResponse) => {
            const message = `(${response.status}) ${response.statusText}`;
            raiseErrorMessage(message);
        })
        .finally(() => {
            nextTick(() => {
                loading.value = false;
            });
        });
}

function create() {
    if (!repository) {
        return;
    }

    const record = repository?.make();

    selectedRow.value = repository.make(record);
    drawer.value = true;
}

function save({ original, saved }: { original: Model, saved: Model }) {
    if (repository && saved) {
        repository.withAll().load([saved]);

        const key = original.$getSingleKey();

        // find record in table to update it
        const record = vxe.value?.getRowById(key);

        if (record) {
            // update if found
            Object.assign(record, saved);
        } else {
            // create new row if no record exists
            vxe.value?.insertAt(saved, 0);
            data.value.unshift(saved);
        }

        selectedRow.value = saved;

        ElMessage({
            type: 'success',
            message: 'Сохранение завершено'
        });
    }
}

function remove(removed: number[]) {
    if (removed) {
        for (const key of removed) {
            const row = vxe.value?.getRowById(key);

            if (row) {
                vxe.value?.remove(row);
            }
        }

        ElMessage({
            type: 'success',
            message: 'Удаление завершено'
        });
    }

    closeDrawer();
}

function confirmRowRemoving(row: Model) {
    if (!repository) {
        return;
    }

    ElMessageBox.confirm(
        'Запись будет удалена. Продолжить?',
        'Внимание!',
        {
        confirmButtonText: 'Да, удалить',
        cancelButtonText: 'Нет',
        confirmButtonClass: 'el-button--danger',
        type: 'warning'
        }
    ).then(() => {
        repository
            .remove(row.$getSingleKey())
            .then((removed) => {
                if (removed) {
                    remove(removed);
                }
            });
    }).catch(() => {
        // chill
    });
}

function handlePageChange() {
    load();
}

function handleFilter(field: string, value: any, type: string) {
    saveFilter({
        tableId,
        field,
        type,
        value
    });

    load();
}

function handleResetFilters() {
    resetSavedFilters();

    load();
}

function handleRowDblClick({ row }: CellDblclickEventParams) {
    if (repository) {
        selectedRow.value = repository.make(row);
        drawer.value = true;
    }
}

function setVisibleColumns(columns: TableColumnOptions[]) {
    visibleColumns.value = columns;
}

function onContextRowOpen(row: Model) {
    selectedRow.value = repository?.make(row);
    drawer.value = true;
}

function onContextRowRemove(row: Model) {
    confirmRowRemoving(row);
}

function onContextRowHistory(row: Model) {
    contextRow.value = row;
    historyDialog.value = true;
}

function closeDrawer() {
    selectedRow.value = undefined;
    drawer.value = false;
}

function getColumnOrderIcon(order?: string): string | undefined {
    switch (order) {
        case 'asc':
            return 'tabler:arrow-up';
        case 'desc':
            return 'tabler:arrow-down';
        default:
            return undefined;
    }
}

provide('TableInstance', {
    tableId,
    vxe,

    fields,

    filterStore,

    columns,
    visibleColumns,

    setVisibleColumns
});
</script>

<script lang="ts">
export default {
    name: 'ItTable'
}
</script>