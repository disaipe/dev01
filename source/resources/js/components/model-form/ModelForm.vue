<template lang='pug'>
el-form(
    ref='form'
    v-bind='$props'
    :model='modelValue'
)
    slot
</template>

<script>
import { ref, toRef, provide } from 'vue';
import { ElForm } from 'element-plus';

export default {
    name: 'ModelForm',
    props: {
        ...ElForm.props,
        modelValue: {
            type: Object,
            default: () => ({})
        }
    },
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
