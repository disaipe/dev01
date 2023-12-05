<template lang='pug'>
.onec-domain-user-record-page
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
        const parentId = route.params?.id;

        const openRecord = (id) => {
          router.push({ name: 'OneCInfoBaseRecord', params: { id } });
        };

        return {
            tableProps: {
                reference: 'OneCInfoBase',
                context: { one_c_domain_user_id: parseInt(parentId, 10) },
                canCreate: false,
                canUpdate: false,
                canDelete: false
            },
            openRecord
        };
    }
}
</script>
