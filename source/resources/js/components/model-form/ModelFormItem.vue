<template lang="pug">
el-form-item(
  v-if='model'
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
      @input='$emit("update:model-value", $event)'
    )

  //- BOOLEAN / SWITCH INPUT
  template(v-else-if='field.type === "boolean"')
    el-switch(
      :model-value='modelValue'
      :disabled='field.readonly'
      @input='$emit("update:model-value", $event)'
    )

  //- CHECKBOX
  template(v-else-if='field.type === "checkbox"')
    el-checkbox(
      :model-value='modelValue'
      :disabled='field.readonly'
      @input='$emit("update:model-value", $event)'
    )

  //- DATETIME
  template(v-else-if='field.type === "datetime"')
    el-date-picker(
      class='!w-full'
      :model-value='modelValue'
      :disabled='field.readonly'
      type='datetime'
      value-format='YYYY-MM-DD HH:mm:ss'
      @update:model-value='$emit("update:model-value", $event)'
    )

  //- DATE
  template(v-else-if='field.type === "date"')
    el-date-picker(
      class='!w-full'
      :model-value='modelValue'
      :disabled='field.readonly'
      type='date'
      value-format='YYYY-MM-DD'
      @update:model-value='$emit("update:model-value", $event)'
    )

  //- RELATION INPUT
  template(v-else-if='field.type === "relation" && field.relation?.key')
    el-select.w-full(
      :model-value='model[field.relation.key]'
      :disabled='field.readonly'
      :clearable='!field.required'
      :multiple='field.relation?.multiple'
      filterable
      @change='onRelatedChange'
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
      @change='$emit("update:model-value", $event)'
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
      @input='$emit("update:model-value", $event)'
    )

  //- OTHER / TEXT INPUT
  template(v-else)
    el-input(
      :type='field.type === "password" ? "password" : "text"'
      :model-value='modelValue'
      :disabled='field.readonly'
      :min='field.min'
      :max='field.max'
      :maxlength='field.max'
      :show-word-limit='!!field.max'
      @input='$emit("update:model-value", $event)'
    )
</template>

<script setup lang="ts">
import type { ModelFieldSchema } from '../../types';

import { computed, inject, toRef } from 'vue';
import { useRepos } from '../../store';

const props = defineProps<{
  modelValue?: string | number | object | boolean | Array<any>;
  field: ModelFieldSchema;
  prop?: string;
}>();

const emit = defineEmits(['update:model-value']);

const model = inject<Record<string, any>>('modelForm');

const field = toRef(props, 'field');

const relatedOptions = computed(() => {
  if (field.value.type !== 'relation' || !field.value.relation) {
    return [];
  }

  const { ownerKey, model } = field.value.relation;

  const repo = useRepos()[model];
  const valueKey = ownerKey || repo.getModel().$getSingleKeyName();

  return repo.all().map((item) => {
    return {
      value: item[valueKey] || item.$getKey(),
      label: item.$getName(),
    };
  });
});

function onRelatedChange(value: any) {
  if (model && field.value.relation?.key) {
    model.value[field.value.relation.key] = value;
    emit('update:model-value', value);
  }
}
</script>

<script lang="ts">
export default {
  name: 'ModelFormItem',
};
</script>
