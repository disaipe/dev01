<template lang='pug'>
.reference-page
    Table(
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
