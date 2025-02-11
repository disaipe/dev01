<template lang='pug'>
.onec-info-base-record-page
    record-page-header

    it-table(v-bind='tableProps')
</template>

<script>
import { useRoute } from 'vue-router';

export default {
  name: 'OneCInfoBaseRecord',
  setup() {
    const route = useRoute();
    const recordId = Number.parseInt(route.params?.id, 10);

    return {
      tableProps: {
        reference: 'OneCInfoBaseUser',
        context: {
          one_c_info_base_id: { $eq: recordId },
        },
        canCreate: false,
        canUpdate: false,
        canDelete: false,
      },
    };
  },
};
</script>
