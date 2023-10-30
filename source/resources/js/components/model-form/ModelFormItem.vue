<template lang='pug'>
el-form-item(
    :label='field.label'
    :prop='prop'
)
    //- LABEL
    template(#label)
        .inline-flex.items-center.space-x-1.w-full
            .text-red-500(v-if='field.required') *

            .flex-1 {{ field.label }}

            //- DESCRIPTION
            el-popover(
                v-if='!!field.description'
                :content='field.description'
                :persistent='false'
                width='250px'
                effect='dark'
            )
                template(#reference)
                    el-link(:underline='false')
                        icon(icon='tabler:question-circle')

    //- NUMBER INPUT
    template(v-if='field.type === "numeric"')
        el-input-number(
            :model-value='modelValue'
            :disabled='field.readonly'
            :min='field.min || undefined'
            :max='field.max || undefined'
            @input='$emit("update:modelValue", $event)'
        )

    //- BOOLEAN / SWITCH INPUT
    template(v-else-if='field.type === "boolean"')
        el-switch(
            :model-value='modelValue'
            :disabled='field.readonly'
            @input='$emit("update:modelValue", $event)'
        )

    //- DATETIME
    template(v-else-if='field.type === "datetime"')
        el-date-picker(
            class='!w-full'
            :model-value='modelValue'
            :disabled='field.readonly'
            type='datetime'
            value-format='YYYY-MM-DD HH:mm:ss'
            @update:model-value='$emit("update:modelValue", $event)'
        )

    //- DATE
    template(v-else-if='field.type === "date"')
        el-date-picker(
            class='!w-full'
            :model-value='modelValue'
            :disabled='field.readonly'
            type='date'
            value-format='YYYY-MM-DD'
            @update:model-value='$emit("update:modelValue", $event)'
        )

    //- RELATION INPUT
    template(v-else-if='field.type === "relation"')
        el-select.w-full(
            :model-value='model[field.relation.key]'
            :disabled='field.readonly'
            :clearable='!field.required'
            filterable
            @change='model[field.relation.key] = $event; $emit("update:modelValue", $event)'
        )
            el-option(
                v-for='option of relatedOptions'
                :label='option.label'
                :value='option.value'
            )

    //- SELECT / ENUM
    template(v-else-if='field.type === "select"')
        el-select.w-full(
            :model-value='modelValue'
            :disabled='field.readonly'
            :clearable='!field.required'
            filterable
            @change='$emit("update:modelValue", $event)'
        )
            el-option(
                v-for='(label, value) of field.options'
                :label='label'
                :value='value'
            )

    //- TEXTAREA
    template(v-else-if='field.type === "textarea"')
        el-input(
            type='textarea'
            :model-value='modelValue'
            :disabled='field.readonly'
            :maxlength='field.max'
            :show-word-limit='!!field.max'
            @input='$emit("update:modelValue", $event)'
        )

    //- OTHER / TEXT INPUT
    template(v-else)
        el-input(
            v-bind:type='field.type === "password" ? "password" : "text"'
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
            type: [String, Number, Array, Object, Boolean],
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

            const { ownerKey, model } = this.field.relation;

            const repo = useRepos()[model];
            const valueKey = ownerKey || repo.getModel().$getKeyName();

            return repo.all().map((item) => {
                return {
                    value: item[valueKey],
                    label: item.$getName()
                };
            });
        }
    }
}
</script>
