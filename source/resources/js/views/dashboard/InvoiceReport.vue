<template lang='pug'>
.relative.h-full
    .flex.items-center.space-x-2.pb-4
        el-select(
            v-model='company'
            placeholder='Организация'
            filterable
        )
            el-option(
                v-for='company of companies'
                :label='company.name'
                :value='company.code'
            )

        el-select(
            v-model='reportTemplate'
            placeholder='Шаблон отчета'
        )
            template(#prefix)
                icon(icon='tabler:template')

            el-option-group(
                v-for='provider of providers'
                :label='provider.name'
            )
                el-option(
                    v-for='reportTemplate of provider.reportTemplates'
                    :label='reportTemplate.name'
                    :value='reportTemplate.$getKey()'
                )

        el-date-picker(
            v-model='period'
            class='!w-32'
            type='month'
            placeholder='Период'
        )

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

    el-dialog(
        v-model='showDebugDialog'
        title='Отладка (BETA)'
        width='90%'
        destroy-on-close
        :close-on-click-modal='false'
    )
        it-table(
            :reference='debugReference'
            :columns='debugColumns'
            :items='debugData'
            :can-create='false'
            :can-update='false'
            :can-delete='false'
            :can-load='false'
        )

    .spread.h-full.pb-8
        spreadsheet(
            v-show='loaded'
            ref='spread'
            :cell-modifier='cellModifier'
            :show-toolbar='false'
            @debug='onDebug'
        )
</template>

<script>
import { ref, computed, nextTick, watch } from 'vue';
import { ElMessageBox } from 'element-plus'
import dayjs from 'dayjs';
import orderBy from 'lodash/orderBy';
import { useReportSettingsStore } from '../../store/modules';
import { useRepos } from '../../store/repository';
import { useApi } from '../../utils/axiosClient';
import batchApi from '../../utils/batchApi';
import ItTable from "../../components/table/Table.vue";

export default {
    name: 'InvoiceReport',
    components: {ItTable},
    setup() {
        const spread = ref();
        const loading = ref(false);
        const loaded = ref(false);
        const showErrorsDialog = ref(false);
        const showDebugDialog = ref(false);

        const debugColumns = ref(null);
        const debugReference = ref(null);
        const debugData = ref(null);

        const savedSettings = useReportSettingsStore();

        const company = ref(savedSettings.company);
        const reportTemplate = ref(savedSettings.reportTemplate);
        const period = ref(savedSettings.period);

        const companies = ref();
        const providers = ref();
        const reportErrors = ref();

        watch(company, () => savedSettings.company = company.value);
        watch(reportTemplate, () => savedSettings.reportTemplate = reportTemplate.value);
        watch(period, () => savedSettings.period = period.value);

        const { ServiceProvider } = useRepos();

        const api = useApi();

        let replacements = {};

        batchApi.batch('ServiceProvider,Company,ReportTemplate,Indicator').then((result) => {
            companies.value = orderBy(result.Company, 'name');
            providers.value = ServiceProvider.query().whereHas('reportTemplates').with('reportTemplates').get();
        });

        const cellModifier = (cell) => {
            if (replacements[cell.value] !== undefined) {
                spread.value.instance.setCellMeta(cell.row - 1, cell.col - 1, 'original', cell.value);

                const replacement = replacements[cell.value];

                if (typeof(replacement) === 'number') {
                    cell.value = replacement.toFixed(2);
                } else {
                    cell.value = replacement;
                }
            }
        };

        const reportBody = computed(() => {
            const _period = period.value
                ? dayjs(period.value).tz('UTC', true).toISOString()
                : null;

            return {
                company: company.value,
                template: reportTemplate.value,
                period: _period
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
                            const { xlsx, values, errors } = data;

                            replacements = values || {};
                            reportErrors.value = errors;

                            if (xlsx) {
                                spread.value.loadFromBase64(xlsx);
                                loaded.value = true;
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

        const onDebug = (service) => {
            api
                .post('report/debug', { ...reportBody.value, service })
                .then((response) => {
                    if (response.ok) {
                        const { status, data } = response.data;

                        if (status) {
                            showDebugDialog.value = true;

                            debugColumns.value = data.columns;
                            debugReference.value = data.reference;
                            debugData.value = data.data;
                        }
                    }
                });
        };

        return {
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

            cellModifier,
            fetchReport,
            downloadReport,

            onDebug,
            showDebugDialog,
            debugReference,
            debugColumns,
            debugData
        };
    }
}
</script>
