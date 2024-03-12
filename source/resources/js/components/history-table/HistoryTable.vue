<template lang="pug">
el-table(:data='records')
    el-table-column(type='expand')
        template(#default='{ row }')
            pre.text-xs {{ row.data }}
    el-table-column(prop='datetime' label='Дата изменения')
    el-table-column(prop='action' label='Событие')
    el-table-column(prop='user' label='Пользователь')
</template>

<script setup lang="ts">
import { ref, toRef, onMounted, watch } from 'vue';

import type { HistoryRecord } from '@/types';
import { useRepos } from '@/store/repository';

const props = defineProps({
    reference: {
        type: String,
        required: true
    },
    id: {
        type: [String, Number],
        required: true
    }
});

const records = ref<HistoryRecord[]>([]);
const id = toRef(props, 'id');
const reference = toRef(props, 'reference');
const repository = useRepos()[reference.value];

function load() {
    repository.history(id.value).then((data) => {
        records.value = data;
    });
}

watch(() => id.value, () => load());

onMounted(() => load());
</script>

<script lang="ts">
export default {
    name: 'HistoryTable'
}
</script>