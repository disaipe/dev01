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
            el-icon
                Filter
    .flex.flex-col.space-y-1
        el-input(v-model='filterStore.inputs[field]')

        div
            el-button.w-full(
                size='small'
                :disabled='!isCanSet'
                @click.stop='applyFilter(filterStore.inputs[field])'
            ) Применить

        div
            el-button.w-full(
                type='text'
                size='small'
                :disabled='!filterStore.inputs[field]'
                @click.stop='applyFilter()'
            ) Сбросить
</template>

<script>
import { toRef, computed, inject } from 'vue';

export default {
    name: 'TableFilter',
    functional: true,
    props: {
        field: {
            type: String,
            required: true
        }
    },
    emits: ['filter-change'],
    setup(props, { emit }) {
        const field = toRef(props, 'field');
        const { filterStore } = inject('TableInstance');

        const value = toRef(filterStore.filters, field.value);

        const applyFilter = (value = undefined) => {
            filterStore.visibility[field] = false;
            filterStore.filters[field] = value;

            emit('filter-change', field, value);
        };

        return {
            value,
            filterStore,
            applyFilter,
            isSet: computed(() => filterStore.filters[field]),
            isCanSet: computed(() => filterStore.inputs[field] !== filterStore.filters[field]),
            isVisible: computed(() => filterStore.visibility[field] || false),
            onShow() {
                if (!filterStore.inputs[field]) {
                    filterStore.inputs[field] = filterStore.filters[field];
                }
            },
            toggleFilter() {
                filterStore.visibility[field] = !filterStore.visibility[field];
            }
        };
    }
}
</script>
