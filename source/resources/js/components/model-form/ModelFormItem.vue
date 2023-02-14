<template lang='pug'>
el-form-item(
    :label='field.label'
    :prop='prop'
)
    template(v-if='field.type === "numeric"')
        el-input-number(
            :model-value='modelValue'
            :disabled='field.readonly'
            :min='field.min'
            :max='field.max'
            @input='$emit("update:modelValue", $event)'
        )
    template(v-else-if='field.type === "relation"')
        el-select.w-full(
            :model-value='model[field.relation.key]'
            :disabled='field.readonly'
            :clearable='!field.required'
            filterable
            @change='model[field.relation.key] = $event'
        )
            el-option(
                v-for='option of relatedOptions'
                :label='option.label'
                :value='option.value'
            )
    template(v-else)
        el-input(
            :model-value='modelValue'
            :disabled='field.readonly'
            :min='field.min'
            :max='field.max'
            :maxlength='field.max'
            :show-word-limit='!!field.max'
            @input='$emit("update:modelValue", $event)'
        )
</template>

<script>
import { inject } from 'vue';
import { useRepos } from '../../store/repository';

export default {
    name: 'ModelFormItem',
    props: {
        modelValue: {
            type: [String, Number, Array, Object],
            default: null
        },
        field: {
            type: Object,
            required: true
        },
        prop: {
            type: String,
            default: null
        }
    },
    setup() {
        const modelForm = inject('modelForm');

        return {
            model: modelForm
        };
    },
    computed: {
        relatedOptions() {
            if (this.field.type !== 'relation') {
                return [];
            }

            const { model } = this.field.relation;

            return useRepos()[model].all().map((item) => {
                return {
                    value: item.$getKey(),
                    label: item.$getName()
                };
            });
        }
    }
}
</script>
