import { Type, Relation } from 'pinia-orm';

import ApiModel from './api';

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

            // console.log(key, field.constructor.name, field);

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

    $isSaved() {
        return !isNaN(this.$getKey());
    }

    $getName() {
        return this.name;
    }
}
