<template lang='pug'>
.database-server-record-page
    record-page-header

    it-table(v-bind='tableProps')
</template>

<script>
import { useRoute } from 'vue-router';

export default {
    name: 'DatabaseServerRecord',
    setup() {
        const route = useRoute();
        const recordId = parseInt(route.params?.id, 10);

        return {
            tableProps: {
                reference: 'Database',
                context: { database_server_id: recordId },
                canCreate: false,
                canUpdate: true,
                canDelete: false
            }
        };
    }
}
</script>
