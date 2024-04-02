import { Repository, type Model } from 'pinia-orm';

import type { ModelSchema, HistoryRecord, ResponseBase, FetchQueryParams, FetchModelResponse, ModelKey, RelatedModelsResponse, FetchModelResult } from '@/types';

import { useRepos } from './index.js';

export default class Api extends Repository {
    declare model: Model;

    api() {
        return this.model.api();
    }

    baseURL(): string {
        return this.model.baseURL();
    }

    fetch(params?: FetchQueryParams): Promise<FetchModelResult> {
        return this.api()
            .post(this.baseURL(), params)
            .then((response: FetchModelResponse) => {
                let items: Model[] | undefined;

                if (response.status === 200) {
                    const { status, data } = response.data;

                    if (status) {
                        items = this.save(data);
                    }
                }

                return {
                    response,
                    items
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
                            fetch(`data:${type};base64,${base64}`).then(res => res.blob())

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

    push(record: Model) {
        const body = record.$getAttributes();

        const key = record.$getKey();
        if (!Number.isInteger(key)) {
            const keyName = record.$getKeyName();
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
                return models;
            }

            return this.api()
                .post(`${this.baseURL()}/related`, { models })
                .then((response: RelatedModelsResponse) => {
                    if (response.status === 200) {
                        const { status, data } = response.data;

                        if (status) {
                            for (const [model, items] of Object.entries(data)) {
                                useRepos()[model].save(items);
                            }
                        }
                    }
                });
        });
    }
}