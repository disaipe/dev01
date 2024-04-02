import type { Model } from 'pinia-orm';
import { ElForm, type FormProps } from 'element-plus';

export type ModelFormProps = FormProps & { modelValue: Model };

export default {
    ...ElForm.props,

    modelValue: {
        type: Object,
        default: () => ({}),
    }
} as ModelFormProps;