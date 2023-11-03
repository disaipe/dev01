import { useGroupBy } from 'pinia-orm/dist/helpers';
import defaultsDeep from 'lodash/defaultsDeep';

import RepositoryApi from './api';
import { parseRules } from '../../utils/formUtils';

export default class Repository extends RepositoryApi {
    static fieldsSchema = {};

    get name() {
        return this.model.$self().name;
    }

    getEagerLoad() {
        return this.model.constructor.eagerLoad;
    }

    getFieldsSchema() {
        if (this.constructor.fieldsSchema[this.name]) {
            return Promise.resolve(this.constructor.fieldsSchema[this.name]);
        }

        return this.schema().then((schema) => {
            const result = defaultsDeep(schema || {}, this.model.constructor.schema());

            for (const field of Object.values(result)) {
                if (field.rules && field.visible !== false) {
                    Object.assign(field, parseRules(field.rules));
                }
            }

            this.constructor.fieldsSchema[this.name] = result;

            return result;
        })
    }

    getRelatedModels() {
        return this.getFieldsSchema().then((schema) => {
            return Object.values(schema).reduce((acc, cur) => {
                if (cur.relation && !cur.lazy) {
                    const model = cur.relation.model;

                    if (
                        model
                        && model !== this.name
                        && !acc.includes(model)
                    ) {
                        acc.push(model);
                    }
                }

                return acc;
            }, []);
        });
    }

    groupTreeBy(parentKey = 'parent_id', childrenKey = 'children', items = null) {
        const groupItems = items || this.all();
        const grouped = useGroupBy(groupItems, parentKey);

        function childrenOf(parentId) {
            return (grouped[parentId] || []).map((record) => {
                record[childrenKey] = childrenOf(record.$getKey());
                return record;
            });
        }

        return childrenOf(null);
    }
}
