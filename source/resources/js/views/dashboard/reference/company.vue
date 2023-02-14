<template lang='pug'>
.reference-page
    ItTable(
        reference='Company'
        :columns='columns'
    )
</template>

<script>
export default {
    name: 'CompanyReference',
    data: () => ({
        columns: [
            { field: 'code' },
            { field: 'name' },
            { field: 'identity' }
        ]
    })
}
</script>
