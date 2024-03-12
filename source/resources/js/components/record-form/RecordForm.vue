<template lang="pug">
model-form(
    v-bind='$props'
    ref='form'
    v-if='modelValue'
    :disabled='!canUpdate'
    :rules='rules'
    label-position='top'
)
    model-form-item(
        v-for='(field, prop) of visibleFields'
        v-model='modelValue[prop]'
        :field='field'
        :prop='prop'
    )

    el-button(v-if='canUpdate' type='primary' :loading='saving' @click='save(form)') Сохранить

    el-popconfirm(
        v-if='canRemove'
        width='auto'
        title='Точно удалить?'
        confirm-button-text='Да'
        cancel-button-text='Подумаю ещё'
        @confirm='remove'
    )
        template(#reference)
            el-button(v-if='canDelete' type='danger' :loading='removing') Удалить
</template>

<script setup lang="ts">
import { computed, ref, toRef } from 'vue';
import pickBy from 'lodash/pickBy';

import { useRepos } from '@/store/repository';
import { validationRulesFromSchema } from '@/utils/formUtils';

import type CoreModel from '@/store/model';

import { type ModelForm } from '../model-form';
import { type ModelFormProps } from '../model-form/modelFormProps';

export type RecordFormPermissions = {
    canCreate: boolean,
    canUpdate: boolean,
    canDelete: boolean
};

const props = withDefaults(defineProps<ModelFormProps & RecordFormPermissions>(), {
    canCreate: true,
    canUpdate: true,
    canDelete: true
});

const emit = defineEmits(['saved', 'removed']);

const modelValue = toRef(props, 'modelValue');
const reference = modelValue.value.$self().name;

const repository = useRepos()[reference];

const form = ref();
const fields = ref();
const visibleFields = ref();

repository.getFieldsSchema().then((schema) => {
    fields.value = (schema || {});
    visibleFields.value = pickBy(schema, (value) => value.hidden !== true);
});

const rules = computed(() => validationRulesFromSchema(fields.value));
const canRemove = computed(() => modelValue.value && modelValue.value.$isSaved());

const saving = ref(false);
const removing = ref(false);

function save(form: ModelForm) {
    form.validate((valid: boolean) => {
        if (valid) {
            saving.value = true;

            repository
                .push(props.modelValue)
                .then((savedRecord: CoreModel) => {
                    emit('saved', {
                        original: props.modelValue,
                        saved: savedRecord
                    });

                    return savedRecord;
                })
                .finally(() => saving.value = false);
        }
    });
}

function remove() {
    removing.value = true;

    const key = modelValue.value.$getKey();

    if (key) {
        repository
            .remove(key)
            .then((removed: number[] | false) => {
                emit('removed', removed);

                return removed;
            })
            .finally(() => removing.value = false);
    }
}
</script>

<script lang="ts">
export default {
    name: 'RecordForm'
}
</script>
