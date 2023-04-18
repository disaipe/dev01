import { useApi } from './axiosClient';
import { useRepos } from '../store/repository';

const axios = useApi();

export default {
    /**
     * Batch model request
     *
     * @param {string} models
     */
    batch(models) {
        return axios.post('batch', { models }).then((response) => {
            const result = {};

            if (response.ok) {
                const { status, data } = response.data;

                if (status) {
                    for (const [key, value] of Object.entries(data)) {
                        const repo = useRepos()[key];

                        if (repo) {
                            result[key] = repo.save(value);
                        }
                    }
                }
            }

            return result;
        });
    }
};
