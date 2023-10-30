<template lang='pug'>
el-form(
    ref='form'
    v-bind='$props'
    :model='modelValue'
    :hide-required-asterisk='true'
)
    slot
</template>

<script>
import { ref, toRef, provide } from 'vue';
import props from './modelFormProps';

export default {
    name: 'ModelForm',
    props,
    setup(props) {
        const form = ref();
        const modelForm = toRef(props, 'modelValue');

        provide('modelForm', modelForm);

        return {
            form,
            validate: (...params) => form.value.validate(...params)
        };
    }
}
</script>
