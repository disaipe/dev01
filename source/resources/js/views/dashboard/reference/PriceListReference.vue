<template lang='pug'>
.reference-page
    it-table(
        ref='table'
        :reference='reference'
    )
        template(#columns-before)
            vxe-column(width='40px')
                template(#default='{ row }')
                    el-link(:underline='false' @click='editRecord(row.$getKey())')
                        icon(icon='mdi:table-edit' height='20')

            vxe-column(width='40px')
              template(#default='{ row }')
                el-link(:underline='false' @click='copyRecord(row.$getKey())')
                  icon(icon='tabler:copy' height='20')
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessageBox } from 'element-plus';

import { useApi } from '../../../utils/axiosClient';
import { raiseErrorMessage } from "../../../utils/exceptions";

export default {
    name: 'PriceListReference',
    setup() {
        const router = useRouter();
        const table = ref();

        const editRecord = (id) => {
            router.push({ name: 'PriceListRecord', params: { id } });
        };

        const copyRecord = (id) => {
            ElMessageBox.confirm(
                'Прайс-лист и цены на услуги будут копированы в новую запись. Продолжить?',
                'Создать копию?',
                {
                    confirmButtonText: 'Да, копировать',
                    cancelButtonText: 'Нет',
                    type: 'warning'
                }
            ).then(() => {
                useApi()
                    .post(`price_list/${id}/copy`)
                    .then((response) => {
                      if (response.ok) {
                          table.value?.load();
                      } else {
                          raiseErrorMessage('При копировании прайс-листа произошла ошибка');
                      }
                    })
                    .catch(() => {
                      raiseErrorMessage('Ошибка выполнения запроса на копирование прайс-листа');
                    })
            }).catch(() => {
                // chill
            });
        };

        return {
            reference: 'PriceList',

            table,

            editRecord,
            copyRecord
        };
    }
}
</script>
