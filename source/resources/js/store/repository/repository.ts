import defaultsDeep from 'lodash/defaultsDeep';

import RepositoryApi from './api.js';
import { parseRules } from '../../utils/formUtils';
import type { ModelSchema } from '@/types';
import type CoreModel from '../model';

export default class Repository extends RepositoryApi {
    static fieldsSchema: { [key: string]: ModelSchema } = {};

    get name() {
        return this.model.$self().name;
    }

    $fieldsSchema(): ModelSchema {
        return (this.$self() as typeof Repository).fieldsSchema[this.name];
    }

    $modelSchema(): ModelSchema {
        return (this.model.$self() as typeof CoreModel).schema();
    }

    getEagerLoad(): string | undefined {
        return this.model.eagerLoad;
    }

    getFieldsSchema(): Promise<ModelSchema> {
        if (this.$fieldsSchema()) {
            return Promise.resolve(this.$fieldsSchema());
        }

        return this.schema().then((schema: ModelSchema) => {
            const result: ModelSchema = defaultsDeep(schema || {}, this.$modelSchema());

            for (const field of Object.values(result)) {
                if (field.rules && field.visible !== false) {
                    Object.assign(field, parseRules(field.rules));
                }
            }

            (this.$self() as typeof Repository).fieldsSchema[this.name] = result;

            return result;
        });
    }

    getRelatedModels(): Promise<string[]> {
        return this.getFieldsSchema().then((schema) => {
            return Object.values(schema).reduce((acc: string[], cur) => {
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

                defaultsDeep(schema || {}, this.$modelSchema());

                return acc;
            }, []);
        });
    }
}
