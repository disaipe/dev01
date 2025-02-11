import type { Model } from '@/store';
import type { FormProps } from 'element-plus';
import { ElForm } from 'element-plus';

export type ModelFormProps = FormProps & { modelValue: Model };

export default {
  ...ElForm.props,

  modelValue: {
    type: Object,
    default: () => ({}),
  },
} as ModelFormProps;
