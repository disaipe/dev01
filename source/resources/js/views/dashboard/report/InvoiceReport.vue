<template lang='pug'>
.options-bar.pb-4
    el-select(
        v-model='company'
        placeholder='Организация'
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
        type='month'
        placeholder='Период'
    )

    el-button(
        type='primary'
        :loading='loading'
        :disabled='!company || !reportTemplate'
        @click='fetchReport'
    ) Сформировать
    el-button(v-if='loaded' @click='downloadReport') Скачать

.errors.py-4
    el-alert(
        v-if='reportErrors && reportErrors.length'
        :closable='false'
        type='error'
        title='Внимание! При расчете отчета возникли ошибки'
        show-icon
    )
        ul.list-disc.list-inside
            li(v-for='error of reportErrors')
                span.font-bold {{ error.service_name }}
                div {{ error.message }}

.spread(class='h-[600px]')
    spreadsheet(
        v-show='loaded'
        ref='spread'
        :cell-modifier='cellModifier'
        :show-toolbar='false'
    )
</template>

<script>
import { ref, nextTick, watch } from 'vue';
import { ElMessageBox } from 'element-plus'
import dayjs from 'dayjs';
import { useReportSettingsStore } from '../../../store/modules';
import { useRepos } from '../../../store/repository';
import { useApi } from '../../../utils/axiosClient';
import batchApi from '../../../utils/batchApi';

export default {
    name: 'InvoiceReport',
    setup() {
        const spread = ref();
        const loading = ref(false);
        const loaded = ref(false);

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
            companies.value = result.Company;
            providers.value = ServiceProvider.query().whereHas('reportTemplates').with('reportTemplates').get();
        });

        const cellModifier = (cell) => {
            if (replacements[cell.value] !== undefined) {
                const replacement = replacements[cell.value];

                if (typeof(replacement) === 'number') {
                    cell.value = replacement.toFixed(2);
                } else {
                    cell.value = replacement;
                }
            }
        };

        const fetchReport = () => {
            const _period = period
                ? dayjs(period.value).tz('UTC', true).toISOString()
                : null;

            const body = {
                company: company.value,
                template: reportTemplate.value,
                period: _period
            };

            loading.value = true;

            api
                .post('report', body)
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

        return {
            spread,
            loaded,
            loading,

            company,
            companies,
            providers,
            reportTemplate,
            reportErrors,
            period,

            cellModifier,
            fetchReport,
            downloadReport
        }
    }
}
</script>

<style lang='postcss' scoped>
.options-bar {
    @apply flex items-center space-x-2;
}
</style>
