import type { AxiosInstance } from 'axios';

import { Model as CoreModel } from 'pinia-orm';

import { useApi } from '../../../utils/axiosClient';
import { snake } from '../../../utils/stringsUtils';

const axios = useApi();

export class ModelApi extends CoreModel {
  api(): AxiosInstance {
    return axios;
  }

  baseURL(): string {
    return snake(this.constructor.name);
  }
}

export default ModelApi;
