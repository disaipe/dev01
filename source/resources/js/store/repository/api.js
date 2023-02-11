import { Repository } from 'pinia-orm';

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
}
