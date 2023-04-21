<template lang='pug'>
.database-server-record-page
    ItTable(
        :reference='reference'
        :filters='filters'
        :can-create='canCreate'
        :can-update='canUpdate'
        :can-delete='canDelete'
    )
</template>

<script>
import { useRoute } from 'vue-router';

export default {
    name: 'DatabaseServerRecord',
    setup() {
        const route = useRoute();
        const parentId = route.params?.id;

        return {
            reference: 'Database',
            filters: { database_server_id: parentId },
            canCreate: false,
            canUpdate: true,
            canDelete: false
        };
    }
}
</script>
