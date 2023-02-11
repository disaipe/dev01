import { useGroupBy } from 'pinia-orm/dist/helpers';
import defaultsDeep from 'lodash/defaultsDeep';

import RepositoryApi from './api';
import { parseRules } from '../../utils/formUtils';

export default class Repository extends RepositoryApi {
    getEagerLoad() {
        return this.model.constructor.eagerLoad;
    }

    getFieldsSchema() {
        return this.schema().then((schema) => {
            const result = defaultsDeep(schema || {}, this.model.constructor.schema());

            for (const field of Object.values(result)) {
                if (field.rules) {
                    Object.assign(field, parseRules(field.rules));
                }
            }

            return result;
        })
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
