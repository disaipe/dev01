<template lang='pug'>
.it-table
    .flex.flex-col.space-y-2
        .flex.items-center.justify-between.pr-4
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
            :data='data'
            :row-config='rowConfig'
            :tree-config='treeConfig'
            @cell-dblclick='handleRowDblClick'
            @toggle-tree-expand='handleRowExpand'
        )
            template(#default)
                vxe-column(
                    v-for='({ field, label }, i) of visibleColumns'
                    :prop='field'
                    :label='label'
                    :tree-node='tree && i === 0'
                )
                    template(#header='{ column }')
                        .flex.items-center.justify-between.space-x-2.leading-3
                            span(style='word-break: break-word') {{ label || fields[field].label }}

                            TableFilter(
                                :field='field'
                                @filter-change='handleFilter'
                            )

                    template(#default='{ row }')
                        template(v-if='fields[field].relation && row[field]')
                            span {{ row[field].$getName() }}
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

    el-drawer(
        v-model='drawer'
        @closed='close'
    )
        el-scrollbar.pr-4
            model-form(
                ref='form'
                v-if='selectedRow'
                v-model='selectedRow'
                :rules='rules'
                :disabled='!canUpdate'
                label-position='top'
            )
                model-form-item(
                    v-for='(field, prop) of fields'
                    v-model='selectedRow[prop]'
                    :field='field'
                    :prop='prop'
                )

                el-button(v-if='canUpdate' type='primary' :loading='saving' @click='save($refs.form)') Сохранить

                el-popconfirm(
                    v-if='canRemove'
                    width='auto'
                    title='Точно удалить?'
                    confirm-button-text='Да'
                    cancel-button-text='Подумаю ещё'
                    @confirm='remove'
                )
                    template(#reference)
                        el-button(v-if='canDelete' type='danger' :loading='removing') Удалить
</template>

<script>
import { ref, toRef, computed } from 'vue';
import { useRoute } from 'vue-router';

import { useRepos } from '../../store/repository';
import { validationRulesFromSchema } from '../../utils/formUtils';
import { snake } from '../../utils/stringsUtils';

import tableFilters from './mixins/tableFilters';

import { useTableStore} from '../../store/modules';

import TableFilter from './TableFilter.vue';
import TableColumnsSettings from './TableColumnsSettings.vue';
import ModelFormItem from '../model-form/ModelFormItem.vue';

export default {
    name: 'ItTable',
    components: { ModelFormItem, TableColumnsSettings, TableFilter },
    mixins: [tableFilters],
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
        const reference = toRef(props, 'reference');
        const visibleColumns = toRef(props, 'columns');

        const route = useRoute();
        const tableId = [route.name, snake(reference.value)].join('_');

        const repository = useRepos()[reference.value];
        const fieldsSchema = ref();
        const fields = ref();
        repository.getFieldsSchema().then((schema) => {
            fieldsSchema.value = schema;

            const a = Object.values(schema).filter((value) => {
                return value.visible !== false
            });

            fields.value = Object.fromEntries(a);
        });

        const {
            loadExpanded,
            saveExpanded
        } = useTableStore();
        const expanded = ref(loadExpanded(tableId));


        return {
            tableId,
            repository,
            fields,
            visibleColumns,

            expanded,
            saveExpanded: () => saveExpanded({ tableId, expanded: expanded.value }),

            rules: computed(() => validationRulesFromSchema(fields.value)),
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

            drawer: false,

            loading: false,
            saving: false,
            removing: false,

            rowConfig: {
                useKey: true,
                keyField: this.repository.model.$getKeyName()
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
        canRemove() {
            return this.selectedRow && this.selectedRow.$isSaved();
        }
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            this.tree ? this.loadTree() : this.loadPages();
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

                    if (eagerLoad) {
                        this.repository.with(eagerLoad).load(items);
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

            console.log(this.filterStore.filters);

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

        save(form) {
            form.validate((valid) => {
              if (valid) {
                  this.saving = true;

                  this.repository.push(this.selectedRow).then((savedRecord) => {
                      if (savedRecord) {
                          this.saving = false;

                          const key = this.selectedRow.$getKey();

                          // find record in table to update it
                          const record = this.$refs.vxe.getRowById(key);

                          if (record) {
                              // update if found
                              Object.assign(record, savedRecord);
                          } else {
                              // create new row if no record exists
                              if (this.tree) {
                                  const parentKey = savedRecord[this.treeConfig.parentField];
                                  this.$refs.vxe.getRowById(parentKey)?.[this.treeConfig.children]?.push(savedRecord);
                              } else {
                                  this.$refs.vxe.insertAt(savedRecord, 0);
                              }
                          }

                          this.selectedRow = savedRecord;
                      }
                  });
              }
            });
        },

        remove() {
            this.removing = true;

            this.repository.remove(this.selectedRow.$getKey()).then((removed) => {
                this.removing = false;

                if (removed) {
                    const keyName = this.selectedRow.$getKeyName();

                    for (const key of removed) {
                        const rowIdx = this.data.findIndex((row) => row[keyName] === key);

                        if (rowIdx > -1) {
                            this.data.splice(rowIdx, 1);
                        }
                    }
                }

                this.close();
            });
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

        close() {
            this.selectedRow = null;
            this.drawer = false;
        }
    }
}
</script>
