import { ElForm, type FormProps } from 'element-plus';
import type CoreModel from '@/store/model';

export type ModelFormProps = FormProps & { modelValue: CoreModel };

export default {
    ...ElForm.props,

    modelValue: {
        type: Object,
        default: () => ({})
    }
} as ModelFormProps;