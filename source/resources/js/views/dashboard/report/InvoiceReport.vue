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

    el-button(
        type='primary'
        :loading='loading'
        :disabled='!company || !reportTemplate'
        @click='fetchReport'
    ) Сформировать
    el-button(v-if='loaded' @click='downloadReport') Скачать

.spread(class='h-[600px]')
    spreadsheet(
        v-show='loaded'
        ref='spread'
        :cell-modifier='cellModifier'
        :show-toolbar='false'
    )
</template>

<script>
import { ref, nextTick } from 'vue';
import { useRepos } from '../../../store/repository';
import { useApi } from '../../../utils/axiosClient';
import { isServiceCountCell, isServiceNameCell, isServicePriceCell } from '../../../components/spreadsheet/cellTypes';
import batchApi from '../../../utils/batchApi';

export default {
    name: 'InvoiceReport',
    setup() {
        const spread = ref();
        const loading = ref(false);
        const loaded = ref(false);

        const company = ref();
        const reportTemplate = ref();
        const providers = ref();

        const { ServiceProvider } = useRepos();

        const api = useApi();

        const companies = ref();

        let indicators = {};

        batchApi.batch('ServiceProvider,Company,ReportTemplate,Indicator').then((result) => {
            companies.value = result.Company;
            providers.value = result.ServiceProvider;

            ServiceProvider.with('reportTemplates').load(result.ServiceProvider);
        });

        const cellModifier = (cell) => {
            if (
                isServiceNameCell(cell.value)
                || isServiceCountCell(cell.value)
                || isServicePriceCell(cell.value)
            ) {
                if (indicators[cell.value] !== undefined) {
                    cell.value = indicators[cell.value];
                }
            }
        };

        const fetchReport = () => {
            const body = {
                company: company.value,
                template: reportTemplate.value
            };

            loading.value = true;

            api
                .post('report', body)
                .then((response) => {
                   if (response.ok) {
                       const { status, data } = response.data;

                       if (status) {
                           const { xlsx, values } = data;

                           indicators = values || {};

                           if (xlsx) {
                               spread.value.loadFromBase64(xlsx);
                               loaded.value = true;
                           }
                       }
                   }
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
