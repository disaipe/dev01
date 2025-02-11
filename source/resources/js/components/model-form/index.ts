import type { FormValidateCallback } from 'element-plus';

import ModelForm from './ModelForm.vue';

export interface IModelForm {
  validate: (callback: FormValidateCallback) => void;
}

export default ModelForm;
