import { Type, Relation } from 'pinia-orm';

import ApiModel from './api';
import Date from './attributes/date';
import Datetime from './attributes/datetime';

export default class CoreModel extends ApiModel {

    static labels() {
        return {};
    }

    static rules() {
        return {};
    }

    static schema() {
        const fields = this.fields();
        const labels = this.labels() || {};
        const rules = this.rules() || {};

        const schema = {};

        for (const [key, field] of Object.entries(fields)) {
            let type = 'string';
            let readonly = false;
            let relation;

            if (field instanceof Type) {
                switch (field.constructor.name) {
                    case 'Uid':
                        readonly = true;
                        break;
                    case 'String':
                    case 'String2':
                        break;
                    case 'Number':
                        type = 'numeric';
                        break;
                    case 'Boolean':
                        type = 'boolean';
                        break;
                    case 'Datetime':
                        type = 'datetime';
                        break;
                    case 'Date':
                        type = 'date';
                        break;
                    default:
                        break;
                }
            }

            if (field instanceof Relation) {
                switch (field.constructor.name) {
                    case 'BelongsTo':
                        type = 'relation';
                        relation = {
                            key: field.foreignKey,
                            model: field.related.constructor.name
                        };
                        break;
                    default:
                        break;
                }
            }

            schema[key] = {
                type,
                relation,
                readonly,
                field: key,
                label: labels[key] || key,
                rules: rules[key]
            };
        }


        return schema;
    }

    static datetime(value) {
        return new Datetime(this.newRawInstance(), value);
    }

    static date(value) {
        return new Date(this.newRawInstance(), value);
    }

    $isSaved() {
        return !isNaN(this.$getKey());
    }

    $getName() {
        return this.name;
    }
}
