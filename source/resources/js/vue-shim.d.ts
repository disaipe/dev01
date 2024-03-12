import { ElAlert } from 'element-plus';

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $alert: typeof ElAlert
    }
}

export {}