import type { ModelSchema } from '@/types';

import type { Model } from '../model';
import defaultsDeep from 'lodash/defaultsDeep';

import { Repository as CoreRepository } from 'pinia-orm';
import { parseRules } from '../../../utils/formUtils';

export class Repository<M extends Model = Model> extends CoreRepository<M> {
  static fieldsSchema: Record<string, ModelSchema> = {};

  get name() {
    return this.model.$self().name;
  }

  $getSelf(): typeof Repository<M> {
    return this.constructor as typeof Repository<M>;
  }

  $fieldsSchema(): ModelSchema {
    return this.$getSelf().fieldsSchema[this.name];
  }

  $modelSchema(): ModelSchema {
    return this.model.$self().schema();
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

      this.$getSelf().fieldsSchema[this.name] = result;

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

export default Repository;
