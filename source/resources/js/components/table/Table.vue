<template lang='pug'>
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

                TableColumnsSettings

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
            :tree-config='treeConfig'
            :menu-config='menuConfig'
            @cell-dblclick='handleRowDblClick'
            @toggle-tree-expand='handleRowExpand'
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
                    :tree-node='tree && i === 0'
                    :cell-render='fields ? { name: "model-field", fields } : null'
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
                                :icon='{ asc: "tabler:arrow-up", desc: "tabler:arrow-down"}[column.order]'
                                height='16'
                              )

                              TableFilter(
                                  v-if='fields'
                                  :field='field'
                                  :schema='fields[field]'
                                  @filter-change='handleFilter'
                                  @click.stop
                              )

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
            hide-on-single-page
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

        el-scrollbar.pr-4
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
    )
        history-table(
            v-if='contextRow.$getKey()'
            :reference='reference'
            :id='contextRow.$getKey()'
        )
</template>

<script>
import { ref, toRef, computed } from 'vue';
import { useRoute } from 'vue-router';
import merge from 'lodash/merge';
import cloneDeep from 'lodash/cloneDeep';

import { useRepos } from '../../store/repository';
import { snake } from '../../utils/stringsUtils';

import tableSorts from './mixins/tableSorts';
import tableFilters from './mixins/tableFilters';
import tableContextMenu from './mixins/tableContextMenu';

import { useTableStore, useProfilesSettingsStore } from '../../store/modules';

import TableFilter from './TableFilter.vue';
import TableColumnsSettings from './TableColumnsSettings.vue';

import './renderers';

import { ElMessage, ElMessageBox } from 'element-plus';

export default {
    name: 'ItTable',
    components: { TableFilter, TableColumnsSettings },
    mixins: [tableSorts, tableFilters, tableContextMenu],
    provide() {
        return {
            TableInstance: this
        }
    },
    props: {
        id: {
            type: String,
            default: Math.random().toString(36).substring(7)
        },
        reference: {
            type: String,
            default: null
        },
        columns: {
            type: Array,
            default: () => ([])
        },
        items: {
            type: Array,
            default: () => null
        },
        context: {
            type: Object,
            default: null,
        },
        tree: {
            type: Boolean,
            default: false
        },
        canLoad: {
            type: Boolean,
            default: true,
        },
        canCreate: {
            type: Boolean,
            default: true
        },
        canUpdate: {
            type: Boolean,
            default: true
        },
        canDelete: {
            type: Boolean,
            default: true
        }
    },
    setup(props) {
        const errors = ref([]);
        const id = toRef(props, 'id');
        const reference = toRef(props, 'reference');
        const columns = toRef(props, 'columns');

        const route = useRoute();
        const tableId = [route.name, snake(reference.value || id.value)].join('_');

        const repository = useRepos()[reference.value];

        // User profile settings
        const profilesSettings = useProfilesSettingsStore();
        const drawerComponent = computed(() => profilesSettings.formDisplayType === 'modal'
            ? 'el-dialog'
            : 'el-drawer'
        );

        // Tree table functionality
        const { loadExpanded, saveExpanded } = useTableStore();
        const expanded = ref(loadExpanded(tableId));

        const visibleColumns = ref(columns.value);
        const hasVisibleColumns = computed(() => visibleColumns.value?.length > 0);

        // Get model fields schema
        const fields = ref();
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

        return {
            errors,

            tableId,
            repository,

            fields,

            visibleColumns,
            hasVisibleColumns,

            drawerComponent,

            expanded,
            saveExpanded: () => saveExpanded({ tableId, expanded: expanded.value })
        };
    },
    data: function () {
        return {
            data: [],

            pagination: {
                page: 1,
                pageSize: 100,
                total: 0
            },

            selectedRow: null,
            contextRow: null,

            drawer: false,
            historyDialog: false,

            loading: false,

            loadingConfig: {
                text: 'Собираем данные'
            },
            rowConfig: {
                useKey: true,
                keyField: this.repository?.model.$getKeyName()
            },
            treeConfig: {
                indent: 4,
                rowField: 'id',
                parentField: 'parent_id',
                lazy: true,
                hasChild: '$hasChildren',
                children: 'children',
                loadMethod: this.loadChildren,
                reserve: true,
                expandRowKeys: this.expanded
            }
        }
    },
    computed: {
        verifiedData() {
            if (!this.visibleColumns.length) {
                return [{}];
            }

            return this.data;
        }
    },
    mounted() {
        if (this.items) {
            this.data = this.items;
        } else {
            this.load();
        }
    },
    methods: {
        load() {
            if (this.repository) {
                this.repository.fetchRelatedModels().then(() => {
                    this.tree ? this.loadTree() : this.loadPages();
                });
            }
        },

        loadPages() {
            this.loading = true;

            const query = {
                filters: cloneDeep(this.context || {})
            };

            if (this.pagination.page) {
                query.page = this.pagination.page;
            }

            if (this.pagination.pageSize) {
                query.perPage = this.pagination.pageSize;
            }

            if (Object.keys(this.filterStore.filters || {}).length) {
                merge(query.filters, this.filterStore.filters)
            }

            if (Object.keys(this.sortsStore || {}).length) {
                query.order = this.sortsStore;
            }

            return this.repository.fetch(query).then(({ response, items }) => {
                const { status, total } = response.data;

                if (status) {
                    const eagerLoad = this.repository.getEagerLoad();

                    if (eagerLoad && eagerLoad.length) {
                        let query = this.repository;

                        for (const eager of eagerLoad) {
                            query = query.with(eager);
                        }

                        query.load(items);
                    }

                    this.data = items;
                    this.pagination.total = total;
                }

                this.$nextTick(() => {
                    this.loading = false;
                });
            });
        },

        loadTree(root = null) {
            const query = { root };

            if (Object.keys(this.filterStore.filters || {}).length) {
                query.filters = this.filterStore.filters;
            }

            return this.repository.fetch(query).then(({ response, items }) => {
                const { keys } = response.data;
                const eagerLoad = this.repository.getEagerLoad();

                if (eagerLoad) {
                    this.repository.with(eagerLoad).load(items);
                }

                // TODO
                for (const item of items) {
                    if (keys[item.$getKey()]) {
                        item.$hasChildren = true;
                    }
                }

                if (root === null) {
                    this.data = items;
                }

                return items;
            });
        },

        loadChildren({ row }) {
            return this.loadTree(row.$getKey());
        },

        create() {
            const record = this.repository.make();

            this.selectedRow = this.repository.make(record);
            this.drawer = true;
        },

        save({ original, saved }) {
            if (saved) {
                this.repository.withAll().load([saved]);

                const key = original.$getKey();

                // find record in table to update it
                const record = this.$refs.vxe.getRowById(key);

                if (record) {
                    // update if found
                    Object.assign(record, saved);
                } else {
                    // create new row if no record exists
                    if (this.tree) {
                        const parentKey = saved[this.treeConfig.parentField];
                        this.$refs.vxe.getRowById(parentKey)?.[this.treeConfig.children]?.push(saved);
                    } else {
                        this.$refs.vxe.insertAt(saved, 0);
                        this.data.unshift(saved);
                    }
                }

                this.selectedRow = saved;

                ElMessage({
                    type: 'success',
                    message: 'Сохранение завершено'
                });
            }
        },

        remove(removed) {
            if (removed) {
                for (const key of removed) {
                    const row = this.$refs.vxe.getRowById(key);

                    if (row) {
                        this.$refs.vxe.remove(row);
                    }
                }

                ElMessage({
                    type: 'success',
                    message: 'Удаление завершено'
                });
            }

            this.closeDrawer();
        },

        handlePageChange() {
            this.load();
        },

        handleFilter(field, value) {
            this.saveFilter({
                tableId: this.tableId,
                field,
                value
            });

            this.load();
        },

        handleResetFilters() {
          this.resetSavedFilters();

          this.load();
        },

        handleRowDblClick({ row }) {
            this.selectedRow = this.repository.make(row);
            this.drawer = true;
        },

        handleRowExpand({ row, expanded }) {
            const key = row.$getKey()
            if (expanded) {
                if (!this.expanded.includes(key)) {
                    this.expanded.push(key);
                }
            } else {
                this.expanded = this.expanded.filter((v) => v !== key);
            }
            this.saveExpanded();
        },



        onContextRowEdit(row) {
            this.selectedRow = this.repository.make(row);
            this.drawer = true;
        },

        onContextRowRemove(row) {
            ElMessageBox.confirm(
                'Запись будет удалена. Продолжить?',
                'Внимание!',
                {
                    confirmButtonText: 'Да, удалить',
                    cancelButtonText: 'Нет',
                    type: 'warning'
                }
            ).then(() => {
                this.repository.remove(row.$getKey()).then((removed) => {
                    this.remove(removed);
                });
            });
        },

        onContextRowHistory(row) {
            this.contextRow = row;
            this.historyDialog = true;
        },

        closeDrawer() {
            this.selectedRow = null;
            this.drawer = false;
        }
    }
}
</script>
