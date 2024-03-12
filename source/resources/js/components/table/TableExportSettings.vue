<template lang="pug">
el-dropdown(
    ref='dropdown'
    trigger='click'
    placement='bottom-end'
)
    el-button
        icon(icon='tabler:file-download' height='16')

    template(#dropdown)
        .flex.flex-col.gap-y-2.p-2
            .font-bold.text-gray-600 Формат
            el-radio-group(v-model='format' size='small')
                el-radio-button(label='xlsx') Excel (XLSX)
                el-radio-button(label='xls') Excel (XLS)
                el-radio-button(label='csv') CSV

            .font-bold.text-gray-600 Данные
            el-radio-group(v-model='type' size='small')
                el-radio-button(label='page') Текущая страница
                el-radio-button(label='all') Все страницы

            el-button(@click='makeExport')
                span Экспортировать
                template(#icon)
                    icon(icon='tabler:file-download')
</template>

<script setup lang="ts">
import { ref } from 'vue';

const dropdown = ref();
const format = ref('xlsx')
const type = ref('page');

const emit = defineEmits(['on-export']);

function makeExport() {
    emit('on-export', {
        format: format.value,
        one_page: type.value === 'page'
    });

    dropdown.value?.handleClose();
}
</script>
