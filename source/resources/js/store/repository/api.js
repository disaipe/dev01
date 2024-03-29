import { Repository } from 'pinia-orm';

import { useRepos } from './index';

export default class Api extends Repository {
    api() {
        return this.model.constructor.api();
    }

    baseURL() {
        return this.model.constructor.baseURL();
    }

    fetch(params = {}) {
        return this.api()
            .post(this.baseURL(), params)
            .then((response) => {
                let items;

                if (response.ok) {
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

    load(id) {
        return this.fetch({ id });
    }

    export(params = {}) {
        return this.api()
            .post(`${this.baseURL()}/export`, params)
            .then((response) => {
                if (response.ok) {
                    const { status, data } = response.data;

                    if (status) {
                        const b64toBlob = (base64, type = 'application/octet-stream') =>
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

    push(record) {
        const body = record.$getAttributes();

        const key = record.$getKey();
        if (isNaN(key)) {
            delete body[record.$getKeyName()];
        }

        return this.api()
            .post(`${this.baseURL()}/update`, body)
            .then((response) => {
                if (response.ok) {
                    const { status, data } = response.data;

                    if (status) {
                        return this.save(data);
                    }
                }

                return record;
            });
    }

    remove(key) {
        return this.api()
            .post(`${this.baseURL()}/remove`, { key })
            .then((response) => {
                if (response.ok) {
                    const { status, removed } = response.data;

                    if (status) {
                        this.destroy(key);
                    }

                    return removed;
                }

                return false;
            });
    }

    schema() {
        return this.api()
            .get(`${this.baseURL()}/schema`)
            .then((response) => {
                if (response.ok) {
                    const { status, data } = response.data;

                    if (status) {
                        return data;
                    }
                }

                return {};
            });
    }

    history(key) {
        return this.api()
            .get(`${this.baseURL()}/history/${key}`)
            .then((response) => {
                if (response.ok) {
                    const { status, data } = response.data;

                    if (status) {
                        return data;
                    }
                }

                return [];
            });
    }

    fetchRelatedModels() {
        return this.getRelatedModels().then((models) => {
            if (!models.length) {
                return models;
            }

            return this.api()
                .post(`${this.baseURL()}/related`, { models })
                .then((response) => {
                    if (response.ok) {
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

    flushRelatedPivots() {
        const fields = this.getModel().constructor.fields();

        for (const field of Object.values(fields)) {
            if (field.constructor.name === 'BelongsToMany') {
                const pivot = field.pivot.constructor.name;
                useRepos()[pivot]?.flush();
            }
        }
    }
}
