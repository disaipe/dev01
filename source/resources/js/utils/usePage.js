import { getCurrentInstance } from 'vue';

export default function usePage() {
    const instance = getCurrentInstance();

    if (instance) {
        return instance.appContext.config.globalProperties.$page;
    }

    return {};
}
