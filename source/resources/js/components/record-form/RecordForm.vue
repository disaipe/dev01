<template lang='pug'>
model-form(
    v-bind='$props'
    ref='form'
    v-if='modelValue'
    :disabled='!canUpdate'
    label-position='top'
)
    model-form-item(
        v-for='(field, prop) of fields'
        v-model='modelValue[prop]'
        :field='field'
        :prop='prop'
    )

    el-button(v-if='canUpdate' type='primary' :loading='saving' @click='save($refs.form)') Сохранить

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

<script>
import { computed, ref, toRef } from 'vue';

import { useRepos } from '../../store/repository';
import { validationRulesFromSchema } from '../../utils/formUtils';

import modelFormProps from '../model-form/modelFormProps';

export default {
    name: 'RecordForm',
    props: {
        ...modelFormProps,
        canCreate: {
            type: Boolean,
            default: true
        },
        canUpdate: {
            type: Boolean,
            default: true
        },
        canDelete: {
            type: Boolean,
            default: true
        }
    },
    emits: ['saved', 'removed'],
    setup(props) {
        const modelValue = toRef(props, 'modelValue');
        const reference = modelValue.value.$self().name;

        const repository = useRepos()[reference];

        const fields = ref();
        repository.getFieldsSchema().then((schema) => {
            fields.value = schema;
        });

        const rules =  computed(() => validationRulesFromSchema(fields.value));

        return {
            reference,
            repository,
            fields,
            rules
        };
    },
    data: () => ({
        saving: false,
        removing: false
    }),
    computed: {
        canRemove() {
            return this.modelValue && this.modelValue.$isSaved();
        }
    },
    methods: {
        save(form) {
            form.validate((valid) => {
                if (valid) {
                    this.saving = true;

                    this.repository.push(this.modelValue).then((savedRecord) => {
                        this.saving = false;

                        this.$emit('saved', {
                            original: this.modelValue,
                            saved: savedRecord
                        });

                        return savedRecord;
                    })
                }
            });
        },
        remove() {
            this.removing = true;

            this.repository.remove(this.modelValue.$getKey()).then((removed) => {
                this.removing = false;

                this.$emit('removed', removed);

                return removed;
            });
        }
    }
}
</script>
