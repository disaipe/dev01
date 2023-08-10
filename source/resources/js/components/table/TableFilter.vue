<template lang='pug'>
el-popover(
    :visible='isVisible'
    @show='onShow'
)
    template(#reference)
        el-link(
            :underline='false'
            :type='isSet ? "primary" : "default"'
            @click='toggleFilter'
        )
            icon(:icon='isSet ? "tabler:filter-cancel" : "tabler:filter"')

    .flex.flex-col.space-y-1(v-click-outside='{ handler: close, isActive: isVisible }')

        //- RELATED SELECTION
        el-select(
            v-if='schema && schema.relation'
            v-model='filterStore.inputs[remoteField]'
            filterable
            @visible-change='(visible) => visible && loadRelatedOptions()'
        )
            el-option(
                v-for='relatedOption of relatedOptions'
                :key='relatedOption.$getKey()'
                :label='relatedOption.$getName()'
                :value='relatedOption[relation.ownerKey] || relatedOption.$getKey()'
            )

        //- BOOLEAN INPUT
        el-switch(
            v-else-if='schema && schema.type === "boolean"'
            v-model='filterStore.inputs[remoteField]'
        )

        //- PLAIN INPUT
        el-input(
            v-else
            v-model='filterStore.inputs[remoteField]'
        )

        div
            el-button.w-full(
                size='small'
                :disabled='!isCanSet'
                @click.stop='applyFilter(filterStore.inputs[remoteField])'
            ) Применить

        div
            el-link.w-full(
                :underline='false'
                :disabled='filterStore.inputs[remoteField] === undefined'
                @click.stop='applyFilter()'
            )
                .text-xs Сбросить
</template>

<script>
import { ref, toRef, computed, inject } from 'vue';
import { useRepos } from '../../store/repository';

export default {
    name: 'TableFilter',
    functional: true,
    props: {
        field: {
            type: String,
            required: true
        },
        schema: {
            type: Object,
            default: () => ({})
        }
    },
    emits: ['filter-change'],
    setup(props, { emit }) {
        const field = toRef(props, 'field');
        const schema = toRef(props, 'schema');
        const { filterStore } = inject('TableInstance');

        const remoteField = computed(() => {
            if (schema.value) {
                if (schema.value.relation) {
                    return schema.value.relation.key || field.value;
                }
            }

            return field.value;
        });

        const value = toRef(filterStore.filters, remoteField.value);

        const applyFilter = (value = undefined) => {
            filterStore.visibility[remoteField.value] = false;
            filterStore.inputs[remoteField.value] = undefined;
            filterStore.filters[remoteField.value] = value;

            emit('filter-change', remoteField.value, value);
        };

        const relatedOptions = ref([]);

        const loadRelatedOptions = () => {
            if (schema.value?.relation?.model) {
                const relatedRepo = useRepos()[schema.value.relation.model];
                if (relatedRepo) {
                    relatedOptions.value = relatedRepo.query().all();
                }
            }
        };

        return {
            remoteField,

            relation: schema.value.relation,
            relatedOptions,
            loadRelatedOptions,

            value,
            filterStore,
            applyFilter,
            isSet: computed(() => filterStore.filters[remoteField.value]),
            isCanSet: computed(() => filterStore.inputs[remoteField.value] !== filterStore.filters[remoteField.value]),
            isVisible: computed(() => filterStore.visibility[remoteField.value] || false),
            onShow() {
                if (!filterStore.inputs[remoteField.value]) {
                    filterStore.inputs[remoteField.value] = filterStore.filters[remoteField.value];
                }
            },
            toggleFilter() {
                filterStore.visibility[remoteField.value] = !filterStore.visibility[remoteField.value];
            },
            close() {
                filterStore.visibility[remoteField.value] = false;
            }
        };
    }
}
</script>
