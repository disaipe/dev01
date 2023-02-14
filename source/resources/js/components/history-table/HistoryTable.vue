<template lang='pug'>
el-table(:data='records')
    el-table-column(type='expand')
        template(#default='{ row }')
            pre.text-xs {{ row.data }}
    el-table-column(prop='datetime' label='Дата изменения')
    el-table-column(prop='action' label='Событие')
    el-table-column(prop='user' label='Пользователь')
</template>

<script>
import { toRef } from 'vue';

import { useRepos } from '../../store/repository';

export default {
    name: 'HistoryTable',
    props: {
        reference: {
            type: String,
            required: true
        },
        id: {
            type: [String, Number],
            required: true
        }
    },
    setup(props) {
      const reference = toRef(props, 'reference');
      const repository = useRepos()[reference.value];

      return {
          repository
      };
    },
    data: () => ({
        records: []
    }),
    watch: {
      id() {
          this.load();
      }
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            this.repository.history(this.id).then((data) => {
                this.records = data;
            });
        }
    }
}
</script>
