<template lang='pug'>
.price-list-record-page
    record-page-header

    spreadsheet(
        ref='spread'
        v-loading='priceListData.loading'
        :settings='settings'
    )
        template(#toolbar)
            .flex.items-center.gap-x-4.pb-4
                el-button-group
                    el-button(@click='load')
                        icon.mr-1(icon='tabler:refresh')
                        span Обновить
                    el-button(@click='save')
                        icon.mr-1(icon='material-symbols:save-outline')
                        span Сохранить

                el-select.flex-1(
                    :model-value='id'
                    filterable
                    @change='changePriceList'
                )
                    el-option-group(
                        v-for='provider of providers'
                        :key='provider.id'
                        :label='provider.name'
                    )
                        el-option(
                            v-for='priceList of provider.priceLists'
                            :value='priceList.id'
                            :label='`${priceList.name} (${provider.name})`'
                        ) {{ priceList.name }}

                    template(#prefix)
                        icon.relative(class='top-[1px]' icon='mdi:table-edit' height='16')
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessageBox } from 'element-plus'
import keyBy from 'lodash/keyBy';
import orderBy from 'lodash/orderBy';

import { useApi } from '../../../utils/axiosClient';
import { raiseErrorMessage } from '../../../utils/exceptions';
import { useRepos } from '../../../store/repository';
import { priceValueRenderer } from '../../../components/spreadsheet/cellRenderers';
import batchApi from '../../../utils/batchApi';

const VALUE_SHORT_KEY = 'i';
const VALUE_SHORT_SERVICE = 's';
const VALUE_SHORT_VALUE = 'v';

export default {
    name: 'PriceListRecord',
    setup() {
        const route = useRoute();
        const router = useRouter();

        const id = computed(() => parseInt(route.params.id));

        watch(id, () => load());

        const api = useApi();
        const spread = ref();
        const priceListData = reactive({
            data: [],
            loading: false,
            saving: false,
            services: [],
            values: []
        });

        const providers = ref();

        const { Service, ServiceProvider } = useRepos();

        const load = () => {
            if (!id.value) {
                return;
            }

            priceListData.loading = true;

            batchApi.batch('PriceList,ServiceProvider').then(() => {
               providers.value = ServiceProvider.query().with('priceLists').get();
            });

            return api.get(`price_list/${id.value}`)
                .then((response) => {
                    parseDataResponse(response);
                })
                .catch((response) => {
                    const message = `(${response.status}) ${response.statusText}`;
                    raiseErrorMessage(message, 'Ошибка загрузки данных прайс-листа');
                })
                .finally(() => {
                    priceListData.loading = false;
                });
        };

        const parseDataResponse = (response) => {
            if (response.ok) {
                const { status, data } = response.data;

                if (status) {
                    const { values, services } = data;

                    priceListData.values = Array.isArray(values) ? values : [];
                    priceListData.services = Array.isArray(services) ? Service.save(services) : [];

                    fillSpreadsheet();

                    return;
                }
            }

            priceListData.data = [];
            priceListData.values = [];
            priceListData.services = [];
        };

        const fillSpreadsheet = () => {
            const valueByService = keyBy(priceListData.values, VALUE_SHORT_SERVICE);

            const rows = [];

            const sortedServices = orderBy(priceListData.services, 'name');

            for (const service of sortedServices) {
                const dataObject = {
                    id: undefined,
                    price_list_id: id,
                    service_id: service.id,
                    service,
                    value: null
                };

                const filled = valueByService[service.id];

                if (filled) {
                    dataObject.id = filled[VALUE_SHORT_KEY];
                    dataObject.value = filled[VALUE_SHORT_VALUE];
                }

                rows.push(dataObject);
            }

            priceListData.data = rows;

            spread.value.instance.updateData(priceListData.data);
        };

        const save = () => {
            priceListData.loading = true;

            const sourceData = spread.value.instance.getSourceData();

            const data = [];

            for (const priceValue of sourceData) {
                data.push({
                    [VALUE_SHORT_KEY]: priceValue.id,
                    [VALUE_SHORT_SERVICE]: priceValue.service_id,
                    [VALUE_SHORT_VALUE]: priceValue.value
                });
            }

            return api.post(`price_list/${id.value}`, data).then((response) => {
                parseDataResponse(response);
            }).finally(() => {
                priceListData.loading = false;
            });
        };

        const changePriceList = (id) => {
            ElMessageBox.confirm(
                'Все несохраненные данные будут утеряны. Продолжить?',
                'Внимание!',
                {
                    confirmButtonText: 'Да',
                    cancelButtonText: 'Нет',
                    type: 'warning'
                }
            ).then(() => {
                router.replace({ name: 'PriceListRecord', params: { id }});
            }).catch(() => {
                // chill
            });
        };

        load();

        return {
            id,
            spread,

            providers,
            priceListData,

            load,
            save,
            changePriceList,

            settings: {
                startRows: 1,
                startCols: 2,
                colHeaders: ['Услуга', 'Стоимость'],
                colWidths: [500, 100],
                contextMenu: false,
                columns: [
                    {
                        data: 'service.name',
                        readOnly: true
                    },
                    {
                        data: 'value',
                        type: 'numeric',
                        renderer: priceValueRenderer
                    }
                ],

                filters: true,
                dropdownMenu: ['filter_by_value', 'filter_action_bar'],
            }
        };
    }
}
</script>

<style lang='postcss'>
.price-list-record-page {
    @apply h-full;
}
</style>
