<template lang='pug'>
.database-server-record-page
    it-table(v-bind='tableProps')
</template>

<script>
import { useRoute } from 'vue-router';

export default {
    name: 'DatabaseServerRecord',
    setup() {
        const route = useRoute();
        const parentId = route.params?.id;

        return {
            tableProps: {
                reference: 'Database',
                context: { database_server_id: parseInt(parentId, 10) },
                canCreate: false,
                canUpdate: true,
                canDelete: false
            }
        };
    }
}
</script>
