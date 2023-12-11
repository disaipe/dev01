<template lang='pug'>
el-dialog(
    title='Массовое изменение цен'
)
    .space-y-2
    el-form(label-width='200' label-position='left')
        el-form-item(label='Применить для')
            el-select(v-model='target')
                el-option(label='Все строки' value='whole')
                el-option(label='Выделенные строки' value='selected')

        el-form-item(label='Выберите тип операции')
            el-select(v-model='changeType')
                el-option(label='Изменить на процент' value='percent')
                el-option(label='Прибавить значение' value='add')

        el-form-item(label='Укажите значение')
            el-input-number(v-model='value')

        el-alert(type='info' :closable='false' show-icon)
            div Для изменения стоимости в меньшую сторону используйте значения с знаком минус.

    template(#footer)
        el-button(@click='close') Отменить
        el-button(
          type='primary'
          :disabled='!canApply'
          @click='apply'
        ) Применить
</template>

<script setup>
import { ref, computed } from 'vue';

const emit = defineEmits(['change', 'update:model-value']);

const target = ref('whole');
const changeType = ref('percent');
const value = ref();
const canApply = computed(() => changeType.value && value.value);

function close() {
    emit('update:model-value', false);
}

function apply() {
    emit('change', {
        target: target.value,
        type: changeType.value,
        value: value.value
    });

    close();
}
</script>
