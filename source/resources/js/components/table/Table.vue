<template lang='pug'>
.it-table
    .errors-list(v-if='errors.length')
        el-alert(title='Что-то пошло не так' type='error' :closable='false' show-icon)
            ul
                li(v-for='error of errors') {{ error }}

    .flex.flex-col.space-y-2(v-else)
        .flex.items-center.justify-between
            el-button-group
                el-button(v-if='canCreate' icon='CirclePlus' @click='create') Создать
                el-button(icon='Refresh' @click='load') Обновить

            TableColumnsSettings

        vxe-table(
            v-if='fields'
            ref='vxe'
            id='tableId'
            border
            size='small'
            :loading='loading'
            :loading-config='loadingConfig'
            :data='verifiedData'
            :row-config='rowConfig'
            :tree-config='treeConfig'
            :menu-config='menuConfig'
            @cell-dblclick='handleRowDblClick'
            @toggle-tree-expand='handleRowExpand'
            @menu-click='onContextMenuClick'
        )
            template(#default)
                vxe-column(
                    :visible='!visibleColumns.length'
                )
                    el-alert(type='warning' title='Не выбраны видимые колонки')
                slot(name='columns-before')
                vxe-column(
                    v-for='({ field, label }, i) of visibleColumns'
                    :prop='field'
                    :label='label'
                    :tree-node='tree && i === 0'
                )
                    template(#header='{ column }')
                        .flex.items-center.justify-between.space-x-2.leading-3
                            span(style='word-break: break-word') {{ label }}

                            TableFilter(
                                :field='field'
                                :schema='fields[field]'
                                @filter-change='handleFilter'
                            )

                    template(#default='{ row }')
                        template(v-if='fields[field].relation && row[field]')
                            span {{ row[field].$getName() }}
                        template(v-else-if='fields[field].type === "boolean"')
                            el-switch(v-model='row[field]' size='small' disabled)
                        template(v-else-if='fields[field].type === "datetime"')
                            span {{ $filter.formatDate(row[field], true) }}
                        template(v-else-if='fields[field].type === "date"')
                            span {{ $filter.formatDate(row[field]) }}
                        span(v-else) {{ row[field] }}

            template(#empty)
                el-empty(description='Данные не пришли...')

        el-pagination(
            v-model:current-page='pagination.page'
            v-model:page-size='pagination.pageSize'
            layout='prev, pager, next, sizes'
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
                    div(v-if='selectedRow.$isSaved()') Редактирование
                    div(v-else) Создание

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
import dayjs from 'dayjs';

import { useRepos } from '../../store/repository';
import { snake } from '../../utils/stringsUtils';

import tableFilters from './mixins/tableFilters';
import tableContextMenu from './mixins/tableContextMenu';

import { useTableStore, useProfilesSettingsStore } from '../../store/modules';

import TableFilter from './TableFilter.vue';
import TableColumnsSettings from './TableColumnsSettings.vue';

export default {
    name: 'ItTable',
    components: { TableColumnsSettings, TableFilter },
    mixins: [tableFilters, tableContextMenu],
    provide() {
        return {
            TableInstance: this
        }
    },
    props: {
        reference: {
            type: String,
            default: null
        },
        columns: {
            type: Array,
            default: () => ([])
        },
        tree: {
            type: Boolean,
            default: false
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
        const reference = toRef(props, 'reference');
        const columns = toRef(props, 'columns');

        const route = useRoute();
        const tableId = [route.name, snake(reference.value)].join('_');

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

        // Get model fields schema
        const fields = ref();
        if (repository) {
            repository.getFieldsSchema().then((schema) => {
                fields.value = schema;
            });
        } else {
            errors.value.push('Не задана модель данных Pinia. Без схемы полей данных дальнейшая работа невозможна');
        }

        return {
            errors,

            tableId,
            repository,

            fields,

            visibleColumns,

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
        this.load();
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

            const query = {};

            if (this.pagination.page) {
                query.page = this.pagination.page;
            }

            if (this.pagination.pageSize) {
                query.perPage = this.pagination.pageSize;
            }

            if (Object.keys(this.filterStore.filters || {}).length) {
                query.filters = this.filterStore.filters;
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
                const key = original.$getKey();

                // find record in table to update it
                const record = this.$refs.vxe.getRowById(key);

                if (record) {
                    this.repository.withAll().load([saved]);

                    // update if found
                    Object.assign(record, saved);
                } else {
                    // create new row if no record exists
                    if (this.tree) {
                        const parentKey = saved[this.treeConfig.parentField];
                        this.$refs.vxe.getRowById(parentKey)?.[this.treeConfig.children]?.push(saved);
                    } else {
                        this.$refs.vxe.insertAt(saved, 0);
                    }
                }

                this.selectedRow = saved;
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
