import { ElForm } from 'element-plus';

export default {
    ...ElForm.props,
    modelValue: {
        type: Object,
        default: () => ({})
    }
}
