import type { Model } from '@/store';
import type { ResponseBase } from '@/types';
import type { Element } from 'pinia-orm';

import { useRepos } from '@/store';
import { useApi } from './axiosClient';

const axios = useApi();

export default {
  /**
   * Batch model request
   *
   * @param {string} models
   */
  batch(models: string) {
    return axios
      .post('batch', { models })
      .then((response: ResponseBase<Record<string, Element[]>>) => {
        const result: Record<string, Model[]> = {};

        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            for (const [key, value] of Object.entries(data)) {
              const repo = useRepos()[key];

              if (repo) {
                // @ts-ignore
                result[key] = repo.save(value);
              }
            }
          }
        }

        return result;
      });
  },
};
