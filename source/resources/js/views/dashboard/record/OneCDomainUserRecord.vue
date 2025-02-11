<template lang='pug'>
.onec-domain-user-record-page
    record-page-header

    it-table(v-bind='tableProps')
        template(#columns-before)
            vxe-column(width='40px')
                template(#default='{ row }')
                    el-link(:underline='false' :disabled='!row.$getKey' @click='openRecord(row.$getKey())')
                        icon(icon='mdi:table-edit' height='20')
</template>

<script>
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'OneCInfoBaseRecord',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const recordId = Number.parseInt(route.params?.id, 10);

    const openRecord = (id) => {
      router.push({ name: 'OneCInfoBaseRecord', params: { id } });
    };

    return {
      tableProps: {
        reference: 'OneCInfoBase',
        context: {
          domain_users: {
            one_c_domain_user_id: { $eq: recordId },
          },
        },
        canCreate: false,
        canUpdate: false,
        canDelete: false,
      },
      openRecord,
    };
  },
};
</script>
