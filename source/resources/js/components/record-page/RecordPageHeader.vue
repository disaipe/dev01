<template lang='pug'>
.mb-4.text-gray-700(v-if='record')
  .text-xl.font-bold {{ record.$getName() }}
  .text-xs {{ referenceTitle }}
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { useRepos} from '../../store/repository';

const route = useRoute();
const recordId = parseInt(route.params?.id, 10);

const record = ref(null);
const referenceTitle = ref();

const { model, title } = route.meta;
referenceTitle.value = title;

console.log(route.meta);

if (model) {
  const reference = useRepos()[model];
  const storedRecord = reference.query().whereId(recordId).first();

  if (storedRecord) {
    record.value = storedRecord;
  } else {
    reference.load(recordId).then(({ items }) => {
      if (items && items.length) {
        record.value = items[0];
      }
    });
  }
}
</script>

<script>
export default {
  name: 'RecordPageHeader'
}
</script>
