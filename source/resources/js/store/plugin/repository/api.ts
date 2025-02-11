import type { FetchModelResponse, FetchModelResult, FetchQueryParams, HistoryRecord, ModelKey, ModelSchema, RelatedModelsResponse, ResponseBase } from '@/types';
import type { Model } from '../model';

import { useRepos } from '../utils';

import Repository from './repository';

export class ReferenceRepository<M extends Model = Model> extends Repository<M> {
  api() {
    return this.model.api();
  }

  baseURL(): string {
    return this.model.baseURL();
  }

  fetch(params?: FetchQueryParams): Promise<FetchModelResult<M>> {
    return this.api()
      .post(this.baseURL(), params)
      .then((response: FetchModelResponse) => {
        let items: M[] | undefined;

        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            items = this.save(data);
          }
        }

        return {
          response,
          items,
        };
      });
  }

  load(id: ModelKey) {
    return this.fetch({ id });
  }

  export(params = {}) {
    return this.api()
      .post(`${this.baseURL()}/export`, params)
      .then((response: ResponseBase) => {
        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            const b64toBlob = (base64: string, type = 'application/octet-stream') =>
              fetch(`data:${type};base64,${base64}`).then(res => res.blob());

            b64toBlob(data.content).then((blob) => {
              const a = document.createElement('a');
              a.href = URL.createObjectURL(blob);
              a.download = data.name;
              a.click();
              a.remove();
            });
          }
        }
      });
  }

  push(record: M) {
    const body = record.$getAttributes();

    const key = record.$getKey();
    if (!Number.isInteger(key)) {
      const keyName = record.$getSingleKeyName();
      delete body[keyName];
    }

    return this.api()
      .post(`${this.baseURL()}/update`, body)
      .then((response: ResponseBase) => {
        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            return this.save(data);
          }
        }

        return record;
      });
  }

  remove(key: ModelKey): Promise<number[] | false> {
    return this.api()
      .post(`${this.baseURL()}/remove`, { key })
      .then((response: ResponseBase<number[]>) => {
        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            /* @ts-ignore */
            this.destroy(key);
          }

          return data;
        }

        return false;
      });
  }

  schema(): Promise<ModelSchema> {
    return this.api()
      .get(`${this.baseURL()}/schema`)
      .then((response: ResponseBase<ModelSchema>) => {
        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            return data;
          }
        }

        return <ModelSchema>{};
      });
  }

  history(key: ModelKey): Promise<HistoryRecord[]> {
    return this.api()
      .get(`${this.baseURL()}/history/${key}`)
      .then((response: ResponseBase<HistoryRecord[]>) => {
        if (response.status === 200) {
          const { status, data } = response.data;

          if (status) {
            return data;
          }
        }

        return [];
      });
  }

  fetchRelatedModels() {
    return this.getRelatedModels().then((models: string[]) => {
      if (!models.length) {
        return Promise.resolve();
      }

      return this.api()
        .post(`${this.baseURL()}/related`, { models })
        .then((response: RelatedModelsResponse) => {
          if (response.status === 200) {
            const { status, data } = response.data;

            if (status) {
              for (const [model, items] of Object.entries(data)) {
                // @ts-ignore
                useRepos()[model].save(items);
              }
            }
          }
        });
    });
  }
}

export default ReferenceRepository;
