<template lang='pug'>
.spreadsheet-page
    .flex.items-center.pb-4.space-x-2
        icon(icon="icon-park-outline:page-template" height='32')
        div
            .font-bold {{ record.name }}
            .text-sm {{ serviceProvider?.name }}

    mixin cellTypeMenuItem(label, type, classes)
        el-dropdown-item(@click=`formatCell('${type}')` :divided=attributes.divided)
            .flex.items-center.space-x-2
                .helper(class=classes)
                div= label

    spreadsheet(
        ref='spread'
        :settings='settings'
        :cell-modifier='cellModifier'
        fit='.spreadsheet-page'
    )
        template(#actions-menu-items)
            el-dropdown-item(@click='load') Обновить
            el-dropdown-item(@click='save') Сохранить

        template(#toolbar-actions)
            el-dropdown(trigger='click')
                el-button(type='primary') Вставить
                template(#dropdown)
                    el-dropdown-menu
                        el-dropdown-item(disabled) Данные услуг
                        +cellTypeMenuItem('Наименование услуги', 'serviceName', 'cell-service-name')
                        +cellTypeMenuItem('Количество оказанной услуги', 'serviceCount', 'cell-service-count')
                        +cellTypeMenuItem('Стоимость услуги', 'servicePrice', 'cell-service-price')

                        el-dropdown-item(divided disabled) Данные договора
                        +cellTypeMenuItem('Номер договора', 'contractNumber', 'cell-contract-number')
                        +cellTypeMenuItem('Дата договора', 'contractDate', 'cell-contract-date')

                        el-dropdown-item(divided disabled) Итоговые суммы
                        +cellTypeMenuItem('Итого', 'totalSum', 'cell-total')
                        +cellTypeMenuItem('Итого НДС', 'totalVat', 'cell-total')
                        +cellTypeMenuItem('Итого с НДС', 'totalWithVat', 'cell-total')

                        el-dropdown-item(divided @click='resetCellFormat')
                            icon.mr-1(icon='tabler:circle-off')
                            span Сбросить

        template(#toolbar-extra-actions)
            el-button(:loading='data.saving' @click='save')
                icon.text-lg(icon='material-symbols:save-outline')
                .ml-1 Сохранить
</template>

<script>
import { ref, reactive, computed } from 'vue';
import { useRoute } from 'vue-router';

import { useRepos } from '../../../store/repository';
import { loadFromBase64 } from '../../../components/spreadsheet/xlsxUtils';
import { bufferToBase64 } from '../../../utils/base64';
import batchApi from '../../../utils/batchApi';

import { cellFormatter, setCellFormat } from '../../../components/spreadsheet/cellFormatter';

export default {
    name: 'ReportTemplateRecord',
    setup() {
        const route = useRoute();
        const { id } = route.params;

        const { ReportTemplate } = useRepos();

        const record = ref({});
        const spread = ref();
        const instance = computed(() => spread.value.instance);
        const data = reactive({
            loading: false,
            saving: false
        });

        const services = ref();
        const serviceProviders = ref();

        const settings = {};

        const serviceProvider = computed(() =>
            serviceProviders.value?.find((provider) => provider.id === record.value.service_provider_id)
        );

        const load = () => {
            data.loading = true;

            return ReportTemplate.load(id).then(({ items }) => {
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

        const formatCell = (cellType) => {
            const [[row, col]] = instance.value.getSelected();

            setCellFormat(instance, row, col, cellType, { services });
        };

        const cellModifier = (cell) => {
            cellFormatter(instance, cell.value, cell.row - 1, cell.col - 1, { services });
        };

        const resetCellFormat = (render = true) => {
            const ranges = instance.value.getSelectedRange();
            for (const range of ranges) {
                for (let row = range.from.row; row <= range.to.row; row++) {
                    for (let col = range.from.col; col <= range.to.col; col++) {
                        instance.value.setCellMetaObject(row, col, {
                           renderer: undefined,
                           editor: undefined,
                           selectOptions: undefined,
                           className: undefined
                        });

                        instance.value.setDataAtCell(row, col, null);
                    }
                }
            }

            if (render) {
                instance.value.render();
            }
        };

        load()
            .then(() => {
                return batchApi.batch('ServiceProvider,Service')
                    .then((result) => {
                        services.value = result.Service.filter((service) => service.service_provider_id === record.value.service_provider_id);
                        serviceProviders.value = result.ServiceProvider;
                    })
            })
            .then(() => {
                instance.value?.render();
            });

        return {
            data,
            record,
            settings,

            serviceProvider,

            spread,
            cellModifier,

            formatCell,
            resetCellFormat,

            load,
            save
        }
    }
}
</script>

<style lang='postcss'>
.spreadsheet-page {
    @apply h-full;
}
.cell-service-name {
    @apply !bg-green-100;
}
.cell-service-count {
    @apply !bg-blue-100 text-center;
}
.cell-service-price {
    @apply !bg-yellow-100 text-right;
}
.cell-contract-number {
    @apply !bg-emerald-100;
}
.cell-contract-date {
    @apply !bg-indigo-100;
}
.cell-total {
    @apply !bg-rose-400;
}
.helper {
    @apply block w-4 h-4 border border-gray-300;
}
</style>
