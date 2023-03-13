<template lang='pug'>
.reference-page
    ItTable(
        :reference='reference'
    )
        template(#columns-before)
            vxe-column(
                width='40px'
            )
                template(#default='{ row }')
                    el-link(:underline='false' @click='editRecord(row.$getKey())')
                        Icon(icon='mdi:table-edit' height='20')
</template>

<script>
import { useRouter } from 'vue-router';

export default {
    name: 'ReportTemplateReference',
    setup() {
        const router = useRouter();

        const editRecord = (id) => {
            router.push({ name: 'ReportTemplateRecord', params: { id } });
        };

        return {
            reference: 'ReportTemplate',
            editRecord
        };
    }
}
</script>
