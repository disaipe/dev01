<template lang='pug'>
.relative.h-full(ref='report')
    .flex.items-center.space-x-2.pb-4(ref='toolbar')
        el-select.shrink-0(
            v-model='company'
            placeholder='Организация'
            style='width:180px'
            filterable
        )
            el-option(
                v-for='company of companies'
                :label='company.name'
                :value='company.code'
            )

        el-select.shrink-0(
            v-model='reportTemplate'
            placeholder='Шаблон отчета'
            style='width:180px'
        )
            template(#prefix)
                icon(icon='tabler:template')

            el-option-group(
                v-for='provider of providers'
                :label='provider.name'
            )
                el-option(
                    v-for='reportTemplate of provider.report_templates'
                    :label='reportTemplate.name'
                    :value='reportTemplate.$getKey()'
                )

        el-date-picker.shrink-0(
            v-model='period'
            style='width:120px'
            type='month'
            placeholder='Период'
        )

        el-badge(value='beta')
            el-checkbox(v-model='extended' label='Подробный' border)

        .flex-1

        el-button(
            type='primary'
            :loading='loading'
            :disabled='!company || !reportTemplate'
            @click='fetchReport'
        ) Сформировать

        el-button(
            v-if='loaded'
            @click='downloadReport'
        ) Скачать

        el-button(
            v-if='reportErrors && reportErrors.length'
            text
            @click='showErrorsDialog = true'
        )
            icon.text-red-500(
                icon='tabler:alert-triangle-filled'
                height='24'
            )

    el-dialog(
        v-model='showErrorsDialog'
        :closable='false'
        title='Внимание! При расчете отчета возникли ошибки'
    )
        el-alert(
            type='error'
            :closable='false'
        )
            ul.list-disc.list-inside
                li(v-for='error of reportErrors')
                    span.font-bold {{ error.service_name }}
                    div {{ error.message }}

    .spread.h-full.pb-8
        spreadsheet(
            v-show='loaded'
            ref='spread'
            :cell-modifier='cellModifier'
            :show-toolbar='false'
        )
</template>

<script>
import { ref, computed, nextTick, watch } from 'vue';
import { ElMessageBox } from 'element-plus'
import dayjs from 'dayjs';
import orderBy from 'lodash/orderBy';
import { useElementSize } from '@vueuse/core';
import { useReportSettingsStore } from '../../store/modules';
import { useRepos } from '../../store/repository';
import { useApi } from '../../utils/axiosClient';
import batchApi from '../../utils/batchApi';
import { applyBindings } from '../../components/spreadsheet/utils';
import ItTable from '../../components/table/Table.vue';

export default {
    name: 'InvoiceReport',
    components: { ItTable },
    setup() {
        const report = ref();
        const toolbar = ref();
        const spread = ref();
        const loading = ref(false);
        const loaded = ref(false);
        const showErrorsDialog = ref(false);

        const reportSize = useElementSize(report);
        const toolbarSize = useElementSize(toolbar);
        const spreadHeight = computed(() => reportSize.height.value - toolbarSize.height.value);

        const savedSettings = useReportSettingsStore();

        const company = ref(savedSettings.company);
        const reportTemplate = ref(savedSettings.reportTemplate);
        const period = ref(savedSettings.period);
        const extended = ref(savedSettings.extended);

        const companies = ref();
        const providers = ref();
        const reportErrors = ref();

        watch(company, () => savedSettings.company = company.value);
        watch(reportTemplate, () => savedSettings.reportTemplate = reportTemplate.value);
        watch(period, () => savedSettings.period = period.value);
        watch(extended, () => savedSettings.extended = extended.value);

        const { ServiceProvider } = useRepos();

        const api = useApi();

        let bindings = {};

        batchApi.batch('ServiceProvider,Company,ReportTemplate,Indicator').then((result) => {
            companies.value = orderBy(result.Company, 'name');
            providers.value = ServiceProvider.query().whereHas('report_templates').with('report_templates').get();
        });

        const cellModifier = (cell) => {
            if (cell.value === null) {
                return;
            }

            const newValue = applyBindings(cell.value, bindings);

            if (newValue !== cell.value) {
                spread.value.instance.setCellMeta(cell.row - 1, cell.col - 1, 'original', cell.value);
                cell.value = newValue;
            }
        };

        const reportBody = computed(() => {
            const _period = period.value
                ? dayjs(period.value).tz('UTC', true).toISOString()
                : null;

            return {
                company: company.value,
                template: reportTemplate.value,
                period: _period,
                extended: extended.value
            };
        });

        const fetchReport = () => {
            loading.value = true;

            api
                .post('report', reportBody.value)
                .then((response) => {
                    let message = 'Необработанная ошибка';

                    if (response.ok) {
                        const { status, data } = response.data;

                        if (status) {
                            const { xlsx, values, errors, debug } = data;

                            bindings = values || {};
                            reportErrors.value = errors;

                            if (xlsx) {
                                spread.value.loadFromBase64(xlsx)
                                    .then(() => {
                                        if (extended.value) {
                                            for (const serviceData of Object.values(debug)) {
                                                const { rows, columns } = serviceData || {};

                                                if (!rows || !columns) {
                                                    continue;
                                                }

                                                // make unique sheet name for each service with max allowed length
                                                const sheetName = `${serviceData.service.id}: ${serviceData.service.name}`.substring(0, 31);

                                                const ws = spread.value.createWorkSheet(sheetName)
                                                spread.value.setWorkSheetData(ws.id, [columns, ...rows]);
                                                spread.value.fitWorksheetColumnsWidthToContent(ws.id);

                                                ws.getRow(1).eachCell((cell) => {
                                                    cell.alignment = {
                                                        vertical: 'middle',
                                                        horizontal: 'center'
                                                    };
                                                })
                                            }
                                        }
                                    })
                                    .finally(() => loaded.value = true);

                                return;
                            }
                        } else if (data) {
                            message = data;
                        }
                   } else {
                        message = 'Ошибка при выполнении запроса к серверу';
                    }

                    ElMessageBox.alert(
                        message,
                        'Что-то пошло не так'
                    );
                })
                .finally(() => {
                    nextTick(() => loading.value = false);
                });
        };

        const downloadReport = () => {
            spread.value.download();
        };

        return {
            spreadHeight,

            report,
            toolbar,
            spread,
            loaded,
            loading,
            showErrorsDialog,

            company,
            companies,
            providers,
            reportTemplate,
            reportErrors,
            period,
            extended,

            cellModifier,
            fetchReport,
            downloadReport
        };
    }
}
</script>
