<template lang='pug'>
.onec-info-base-record-page
    it-table(v-bind='tableProps')
</template>

<script>
import { useRoute } from 'vue-router';

export default {
    name: 'OneCInfoBaseRecord',
    setup() {
        const route = useRoute();
        const parentId = route.params?.id;

        return {
            tableProps: {
                reference: 'OneCInfoBaseUser',
                context: { one_c_info_base_id: parseInt(parentId, 10) },
                canCreate: false,
                canUpdate: false,
                canDelete: false
            }
        };
    }
}
</script>
