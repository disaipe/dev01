<template lang='pug'>
.reference-page
    Table(
        reference='Service'
        :columns='columns'
        :tree='true'
    )
</template>

<script>
export default {
    name: 'ServiceReference',
    data: () => ({
        columns: [
            { field: 'name' }
        ]
    })
}
</script>
