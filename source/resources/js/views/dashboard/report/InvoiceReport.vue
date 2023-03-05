<template lang='pug'>
.options-bar.pb-4
    el-select(
        v-model='company'
    )
        el-option(
            v-for='company of companies'
            :label='company.name'
            :value='company.code'
        )

    el-select(
        v-model='reportTemplate'
    )
        el-option(
            v-for='reportTemplate of reportTemplates'
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
import { isServiceCountCell, isServiceNameCell } from '../../../components/spreadsheet/cellTypes';

export default {
    name: 'InvoiceReport',
    setup() {
        const spread = ref();
        const loading = ref(false);
        const loaded = ref(false);

        const company = ref();
        const reportTemplate = ref();

        const { Company, ReportTemplate, Indicator } = useRepos();

        const api = useApi();

        const companies = ref();
        const reportTemplates = ref();

        let indicators = {};

        Company.fetch().then(({ items }) => {
           companies.value = items;
        });

        ReportTemplate.fetch().then(({ items }) => {
           reportTemplates.value = items;
        });

        Indicator.fetch();

        const cellModifier = (cell) => {
            if (isServiceNameCell(cell.value) || isServiceCountCell(cell.value)) {
                if (indicators[cell.value]) {
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

            api.post('report', body).then((response) => {
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

            cellModifier,

            reportTemplate,
            reportTemplates,

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
