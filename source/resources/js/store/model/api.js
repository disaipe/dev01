import { Model } from 'pinia-orm';

import { useApi } from '../../utils/axiosClient';
import { snake } from '../../utils/stringsUtils';

const axios = useApi();

export default class ApiModel extends Model {
    static api() {
        return axios;
    }

    static baseURL() {
        return snake(this.name);
    }


}
