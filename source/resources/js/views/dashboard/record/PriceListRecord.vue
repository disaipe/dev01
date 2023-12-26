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
                    el-button(:type='isDirty ? "primary" : undefined' @click='save')
                        icon.mr-1(icon='material-symbols:save-outline')
                        span Сохранить

                el-dropdown(trigger='click')
                  el-button
                    icon.mr-1(icon='tabler:math-function')
                    span Операции
                  template(#dropdown)
                    el-dropdown-item(@click='massChangeDialogVisible = true')
                        icon.mr-1(icon='tabler:math-symbols')
                        span Массовое изменение цен

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
                            v-for='priceList of provider.price_lists'
                            :value='priceList.id'
                            :label='`${priceList.name} (${provider.name})`'
                        ) {{ priceList.name }}

                    template(#prefix)
                        icon.relative(class='top-[1px]' icon='mdi:table-edit' height='16')

    //- Mass change dialog
    mass-change-dialog(
      v-model='massChangeDialogVisible'
      @change='massChange'
    )
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessageBox } from 'element-plus'
import keyBy from 'lodash/keyBy';
import orderBy from 'lodash/orderBy';

import { useApi } from '../../../utils/axiosClient';
import { raiseErrorMessage } from '../../../utils/exceptions';
import { useRepos } from '../../../store/repository';
import { priceValueRenderer } from '../../../components/spreadsheet/cellRenderers';
import batchApi from '../../../utils/batchApi';

import MassChangeDialog from '../../../components/price-list/MassChangeDialog.vue';

const VALUE_SHORT_KEY = 'i';
const VALUE_SHORT_SERVICE = 's';
const VALUE_SHORT_VALUE = 'v';

const route = useRoute();
const router = useRouter();

const id = computed(() => parseInt(route.params.id));
const isDirty = computed(() => {
   if (priceListData.data) {
       for (const row of priceListData.data) {
           if (row.original !== row.value) {
               return true;
           }
       }
   }

   return false;
});

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
const massChangeDialogVisible = ref(false);

const providers = ref();

const { Service, ServiceProvider } = useRepos();

const load = () => {
    if (!id.value) {
        return;
    }

    priceListData.loading = true;

    batchApi.batch('PriceList,ServiceProvider').then(() => {
       providers.value = ServiceProvider.query().with('price_lists').get();
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
            dataObject.original = filled[VALUE_SHORT_VALUE];
        }

        rows.push(dataObject);
    }

    priceListData.data = rows;

  updateSpreadsheetData(priceListData.data);
};

const updateSpreadsheetData = (data) => {
  spread.value.instance.updateData(data);
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

const massChange = ({ target, type, value }) => {
    let rows = [];

    switch (target) {
        case 'whole':
            rows = Array.from(Array(priceListData.data.length).keys());
            break;
        case 'selected':
            const selection = spread.value?.instance.selection.getSelectedRange();

            if (!selection) {
                return;
            }

            for (const range of selection.ranges) {
                for (let idx = range.from.row; idx <= range.to.row; idx++) {
                    rows.push(idx);
                }
            }
            break;

        default:
            break;
    }

    const changeActions = {
        add: (currentValue) => currentValue + value,
        percent: (currentValue) => currentValue * (100 + value)/100,
    };

    if (Object.hasOwn(changeActions, type)) {
        for (const idx of rows) {
            const currentValue = priceListData.data[idx].value;

            if (currentValue) {
                priceListData.data[idx].value = (changeActions[type](currentValue)).toFixed(2);
            }
        }
    }

    updateSpreadsheetData(priceListData.data);
};

onMounted(() => {
    load();
});

const settings = {
  startRows: 1,
  startCols: 2,
  colHeaders: ['Услуга', 'Стоимость', 'Новая стоимость'],
  colWidths: [500, 100, 150],
  contextMenu: false,
  columns: [
    {
      data: 'service.name',
      readOnly: true
    },
    {
      data: 'original',
      readOnly: true,
      className: 'htRight'
    },
    {
      data: 'value',
      type: 'numeric',
      className: 'htRight',
      renderer: priceValueRenderer
    }
  ],

  filters: true,
  dropdownMenu: ['filter_by_value', 'filter_action_bar'],
};
</script>

<style lang='postcss'>
.price-list-record-page {
    @apply h-full;
}
</style>
