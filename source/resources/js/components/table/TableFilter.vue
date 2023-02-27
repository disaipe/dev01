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
    .flex.flex-col.space-y-1(v-click-outside='close')
        el-input(v-model='filterStore.inputs[field]')

        div
            el-button.w-full(
                size='small'
                :disabled='!isCanSet'
                @click.stop='applyFilter(filterStore.inputs[field])'
            ) Применить

        div
            el-link.w-full(
                :underline='false'
                :disabled='!filterStore.inputs[field]'
                @click.stop='applyFilter()'
            )
                .text-xs Сбросить
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
            filterStore.visibility[field.value] = false;
            filterStore.filters[field.value] = value;

            emit('filter-change', field.value, value);
        };

        return {
            value,
            filterStore,
            applyFilter,
            isSet: computed(() => filterStore.filters[field.value]),
            isCanSet: computed(() => filterStore.inputs[field.value] !== filterStore.filters[field.value]),
            isVisible: computed(() => filterStore.visibility[field.value] || false),
            onShow() {
                if (!filterStore.inputs[field.value]) {
                    filterStore.inputs[field.value] = filterStore.filters[field.value];
                }
            },
            toggleFilter() {
                filterStore.visibility[field.value] = !filterStore.visibility[field.value];
            },
            close() {
                filterStore.visibility[field.value] = false;
            }
        };
    }
}
</script>
