import { h, ref } from 'vue';
import { ElMessageBox, ElResult } from 'element-plus';

const exceptionMessageVisible = ref(false);

/**
 * Show exception message box
 */
export function raiseErrorMessage(message: string, title: string | null = null) {
    if (exceptionMessageVisible.value) {
        return;
    }

    exceptionMessageVisible.value = true;

    ElMessageBox({
        message: h(ElResult, {
            icon: 'error',
            title: title || 'Ошибка',
            subTitle: 'Произошла непредвиденная ошибка, функционал портала может работать неправильно'
        }, {
            extra: () => h('div', {}, message)
        }),
        showCancelButton: false,
        confirmButtonText: 'OK'
    })
        .then(() => undefined)
        .catch(() => undefined)
        .finally(() => {
            exceptionMessageVisible.value = false;
        });
}
