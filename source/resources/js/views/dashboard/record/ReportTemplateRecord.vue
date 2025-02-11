<template lang="pug">
.spreadsheet-page
  .flex.items-center.pb-4.space-x-2
    icon(icon="icon-park-outline:page-template" height='32')
    div
      .font-bold {{ record?.name }}
      .text-sm {{ serviceProvider?.name }}

  spreadsheet(
    ref='spread'
    :settings='settings'
    :cell-modifier='cellModifier'
    default-sheet-name='Отчет'
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

            el-dropdown-item(@click='formatCell("serviceName")')
              .flex.items-center.space-x-2
                .helper.cell-service-name
                div Наименование услуги

            el-dropdown-item(@click='formatCell("serviceCount")')
              .flex.items-center.space-x-2
                .helper.cell-service-count
                div Количество оказанной услуги

            el-dropdown-item(@click='formatCell("servicePrice")')
              .flex.items-center.space-x-2
                .helper.cell-service-price
                div Стоимость услуги

            el-dropdown-item(divided disabled) Данные договора
            el-dropdown-item(@click='append("{CONTRACT_NUMBER}")') Номер договора
            el-dropdown-item(@click='append("{CONTRACT_DATE}")') Дата договора

            el-dropdown-item(divided disabled) Итоговые суммы
            el-dropdown-item(@click='append("{TOTAL}")') Итого
            el-dropdown-item(@click='append("{TOTAL_VAT}")') Итого НДС
            el-dropdown-item(@click='append("{TOTAL_WITH_VAT}")') Итого с НДС

            el-dropdown-item(divided disabled) Разное
            el-dropdown-item(@click='append("{PERIOD_YEAR}")') Год
            el-dropdown-item(@click='append("{PERIOD_MONTH}")') Месяц (номер)
            el-dropdown-item(@click='append("{PERIOD_MONTH_NAME}")') Месяц (наименование)
            el-dropdown-item(@click='append("{COMPANY_CODE}")') Код организации
            el-dropdown-item(@click='append("{COMPANY_NAME}")') Наименование организации
            el-dropdown-item(@click='append("{COMPANY_NAME_FULL}")') Наименование организации (полное)

            el-dropdown-item(divided @click='resetCellFormat')
              icon.mr-1(icon='tabler:circle-off')
              span Сбросить

    template(#toolbar-extra-actions)
      el-button(:loading='data.saving' @click='save')
        icon.text-lg(icon='material-symbols:save-outline')
        .ml-1 Сохранить
</template>

<script setup lang="ts">
import type { Model } from '@/store';
import type { SpreadSheetCell } from '@/types';

import { cellFormatter, setCellFormat } from '@/components/spreadsheet/cellFormatter';
import { loadFromBase64 } from '@/components/spreadsheet/xlsxUtils';

import { useRepos } from '@/store';
import { bufferToBase64 } from '@/utils/base64';
import orderBy from 'lodash/orderBy';
import { computed, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import batchApi from '../../../utils/batchApi';

const route = useRoute();
const { id } = route.params;

const { ReportTemplate } = useRepos();

const record = ref<Model>();
const spread = ref();
const instance = computed(() => spread.value.instance);
const data = reactive({
  loading: false,
  saving: false,
});

const services = ref();
const serviceProviders = ref();

const settings = {};

const serviceProvider = computed(() =>
  serviceProviders.value?.find((provider: Model) => provider.$getKey() === record.value?.service_provider_id),
);

function load() {
  data.loading = true;

  const _id = Array.isArray(id) ? id[0] : id;

  return ReportTemplate.load(_id).then(({ items }) => {
    if (!items) {
      return;
    }

    const [item] = items;

    record.value = item;

    if (item.content) {
      loadFromBase64(item.content).then(() => {
        data.loading = false;
      });
    }
  });
}

function save() {
  data.saving = true;

  spread.value.store.workbook.xlsx.writeBuffer().then((buffer: BlobPart) => {
    bufferToBase64(buffer).then((base64) => {
      if (!record.value) {
        return;
      }

      record.value.content = base64;

      if (record.value) {
        ReportTemplate.push(record.value);
      }

      data.saving = false;
    });
  });
}

function formatCell(cellType: string) {
  const [[row, col]] = instance.value.getSelected();

  setCellFormat(instance, row, col, cellType, { services });
}

function cellModifier(cell: SpreadSheetCell) {
  cellFormatter(instance, cell.value, cell.fullAddress.row - 1, cell.fullAddress.col - 1, { services });
}

function append(str: string) {
  const [[row, col]] = instance.value.getSelected();
  const data = instance.value.getDataAtCell(row, col) || '';
  instance.value.setDataAtCell(row, col, `${data}${str}`);
}

function resetCellFormat(render = true) {
  const ranges = instance.value.getSelectedRange();
  for (const range of ranges) {
    for (let row = range.from.row; row <= range.to.row; row++) {
      for (let col = range.from.col; col <= range.to.col; col++) {
        instance.value.setCellMetaObject(row, col, {
          renderer: undefined,
          editor: undefined,
          selectOptions: undefined,
          className: undefined,
        });

        instance.value.setDataAtCell(row, col, null);
      }
    }
  }

  if (render) {
    instance.value.render();
  }
}

load()
  .then(() => {
    return batchApi.batch('ServiceProvider,Service')
      .then((result) => {
        const resultServices = result.Service.filter((service: Model) => service.service_provider_id === record.value?.service_provider_id);

        services.value = orderBy(resultServices, 'name');
        serviceProviders.value = result.ServiceProvider;
      });
  })
  .then(() => {
    instance.value?.render();
  });
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

.helper {
  @apply block w-4 h-4 border border-gray-300;
}
</style>
