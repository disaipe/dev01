import { Model } from 'pinia-orm';

import { useApi } from '../../utils/axiosClient';
import { snake } from '../../utils/stringsUtils';

const axios = useApi();

export default class ApiModel extends Model {
    api() {
        return axios;
    }

    baseURL(): string {
        return snake(this.constructor.name);
    }
}
