import { h, ref } from 'vue';
import { ElMessageBox, ElResult } from 'element-plus';

const exceptionMessageVisible = ref(false);

/**
 * Show exception message box
 *
 * @param message
 * @param title
 */
export function raiseErrorMessage(message, title = null) {
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
        .then(() => {})
        .catch(() => {})
        .finally(() => {
            exceptionMessageVisible.value = false;
        });
}
