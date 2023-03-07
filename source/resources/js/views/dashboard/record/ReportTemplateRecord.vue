<template lang='pug'>
.spreadsheet-page
    .pb-4
        .font-bold {{ record.name }}

    spreadsheet(
        ref='spread'
        :settings='settings'
        :cell-modifier='cellModifier'
    )
        template(#actions-menu-items)
            el-dropdown-item(@click='load') Обновить
            el-dropdown-item(@click='save') Сохранить

        template(#toolbar-actions)
            el-dropdown
                el-button(type='primary') Услуги
                template(#dropdown)
                    el-dropdown-menu
                        el-dropdown-item(@click='insertServiceName') Наименование услуги
                        el-dropdown-item(@click='insertServiceCount') Количество оказанной услуги
                        el-dropdown-item Стоимость услуги
                        el-dropdown-item(divided @click='resetServiceFormat') Сбросить

        template(#toolbar-extra-actions)
            el-button(:loading='data.saving' @click='save')
                Icon.text-lg(icon='material-symbols:save-outline')
</template>

<script>
import { ref, reactive, computed,  } from 'vue';
import { useRoute } from 'vue-router';
import { SelectEditor } from 'handsontable/editors';

import { useRepos } from '../../../store/repository';
import { loadFromBase64 } from '../../../components/spreadsheet/xlsxUtils';
import { bufferToBase64 } from '../../../utils/base64';
import { isServiceNameCell, isServiceCountCell } from '../../../components/spreadsheet/cellTypes';

export default {
    name: 'ReportTemplateRecord',
    setup() {
        const route = useRoute();
        const { id } = route.params;

        const { ReportTemplate, Service } = useRepos();

        const record = ref({});
        const spread = ref();
        const instance = computed(() => spread.value.instance);
        const data = reactive({
            loading: false,
            saving: false
        })

        const services = ref();
        Service.fetch().then(({ items }) => {
            services.value = items.reduce((acc, cur) => {
                acc[cur.$getKey()] = cur;
                return acc;
            }, {});
        });

        const getServiceSelectOption = (type) => {
            return Object.values(services.value).reduce((acc, cur) => {
                acc[`SERVICE#${cur.$getKey()}#${type}`] = cur.$getName();
                return acc;
            }, {});
        };

        const getServiceFromCellValue = (cellValue) => {
            if (cellValue && typeof(cellValue) === 'string') {
                const [, id] = cellValue.split('#');

                if (id) {
                    return services.value[id];
                }
            }

            return null;
        };

        const settings = {
            afterChange: (changes) => {
                if (!changes) {
                    return;
                }

                for (const change of changes) {
                    const [row, col,, newValue] = change;
                    if (isServiceCountCell(newValue)) {
                        setCellServiceComment(row, col, newValue);
                    }
                }
            }
        };

        const load = () => {
            data.loading = true;

            ReportTemplate.load(id).then(({ items }) => {
                const [item] = items;

                record.value = item;

                if (item.content) {
                    loadFromBase64(item.content).then(() => {
                        data.loading = false;
                    });
                }
            });
        };

        const save = () => {
            data.saving = true;

            spread.value.store.workbook.xlsx.writeBuffer().then((buffer) => {
                bufferToBase64(buffer).then((base64) => {
                    record.value.content = base64;

                    ReportTemplate.push(record.value);

                    data.saving = false;
                });
            });
        };

        const serviceNameCellRenderer = (instance, td, row, column, prop, value, cellProperties) => {
            if (isServiceNameCell(value)) {
                const service = getServiceFromCellValue(value);
                if (service) {
                    td.innerText = service.$getName();
                }
            }

            const { className } = cellProperties;

            if (className) {
                td.classList.add(className);
            }
        };

        const serviceCountCellRenderer = (instance, td, row, column, prop, value, cellProperties) => {
            if (isServiceCountCell(value)) {
                td.innerText = 1;
            }

            const { className } = cellProperties;

            if (className) {
                td.classList.add(className);
            }
        };

        const insertServiceName = () => {
            const [row, col] = instance.value.getSelectedLast();
            setCellServiceName(row, col, true);
        };

        const setCellServiceName = (row, col, render = false) => {
            instance.value.setCellMetaObject(row, col, {
                renderer: serviceNameCellRenderer,
                editor: SelectEditor,
                selectOptions: getServiceSelectOption('NAME'),
                type: 'text',
                className: 'service-name'
            });

            if (render) {
                instance.value.render();
            }
        };

        const insertServiceCount = () => {
            const [row, col] = instance.value.getSelectedLast();
            setCellServiceCount(row, col, true);
        };

        const setCellServiceCount = (row, col, render = false) => {
            instance.value.setCellMetaObject(row, col, {
                renderer: serviceCountCellRenderer,
                editor: SelectEditor,
                selectOptions: getServiceSelectOption('COUNT'),
                type: 'numeric',
                className: 'service-count'
            });

            if (render) {
                instance.value.render();
            }
        };

        const setCellServiceComment = (row, col, value) => {
            const service = getServiceFromCellValue(value);

            if (service) {
                instance.value
                    .getPlugin('comments')
                    .setCommentAtCell(row, col, `Количество оказанной услуги "${service.$getName()}"`);
            }
        };

        const cellModifier = (cell) => {
            const row = cell.row - 1;
            const col = cell.col - 1;

            if (isServiceNameCell(cell.value)) {
                setCellServiceName(row, col);
            } else if (isServiceCountCell(cell.value)) {
                setCellServiceCount(row, col);
                setCellServiceComment(row, col, cell.value);
            }
        };

        const resetServiceFormat = (render = true) => {
            const ranges = instance.value.getSelectedRange();
            for (const range of ranges) {
                for (let row = range.from.row; row <= range.to.row; row++) {
                    for (let col = range.from.col; col <= range.to.col; col++) {
                        instance.value.removeCellMeta(row, col, 'renderer');
                        instance.value.removeCellMeta(row, col, 'editor');
                        instance.value.removeCellMeta(row, col, 'selectOptions');
                    }
                }
            }

            if (render) {
                instance.value.render();
            }
        };

        load();

        return {
            data,
            record,
            settings,

            spread,
            cellModifier,

            insertServiceName,
            insertServiceCount,
            resetServiceFormat,

            load,
            save,

            getTitle: () => '123'
        }
    }
}
</script>

<style lang='postcss'>
.spreadsheet-page {
    @apply h-full;
}
td.service-name {
    @apply bg-green-100;
}
td.service-count {
    @apply bg-blue-100;
}
</style>
