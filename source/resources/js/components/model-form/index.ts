import type { FormValidateCallback } from 'element-plus';

import ModelForm from './ModelForm.vue';

export interface ModelForm {
    validate(callback: FormValidateCallback): void;
}

export default ModelForm;
