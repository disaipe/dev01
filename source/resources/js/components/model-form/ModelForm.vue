<template lang="pug">
el-form(
    ref='form'
    v-bind='$props'
    :model='modelValue'
    :hide-required-asterisk='true'
)
    slot
</template>

<script setup lang="ts">
import { ref, toRef, provide } from 'vue';
import { type FormValidateCallback } from 'element-plus';
import { type ModelFormProps } from './modelFormProps';

const props = defineProps<ModelFormProps>();

const form = ref();
const modelForm = toRef(props, 'modelValue');

provide('modelForm', modelForm);

function validate(callback: FormValidateCallback) {
    form.value?.validate(callback);
}

defineExpose({
    validate
});
</script>

<script lang="ts">
export default {
    name: 'ModelForm'
}
</script>