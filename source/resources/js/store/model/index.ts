import type { BelongsTo, BelongsToMany, HasManyBy, Model, ModelFields } from 'pinia-orm';
import type { ModelFieldRelation, ModelFieldSchema, ModelKey, ModelSchema } from '@/types';
import ApiModel from './api';
import Date from './attributes/date';
import Datetime from './attributes/datetime';

function isAttribute(type: string): boolean {
    return ['uid', 'attr', 'string', 'number', 'boolean', 'datetime', 'date'].includes(type);
}

function isRelation(type: string): boolean {
    return ['belongsTo', 'belongsToMany', 'hasMany', 'hasManyBy'].includes(type);
}

export default class CoreModel extends ApiModel {
    static labels(): Record<string, string> {
        return {};
    }

    static rules(): Record<string, string> {
        return {};
    }

    static schema(): ModelSchema {
        const fields = this.fields() as ModelFields;
        const labels = this.labels() || {};
        const rules = this.rules() || {};

        const schema: ModelSchema = {};

        for (const [key, field] of Object.entries(fields)) {
            let type = 'string';
            let readonly = false;
            let relation: ModelFieldRelation | undefined;

            const fieldType = field.name ?? field.constructor.name;

            if (isAttribute(fieldType)) {
                switch (fieldType) {
                    case 'uid':
                        readonly = true;
                        break;
                    case 'attr':
                        break;
                    case 'string':
                        break;
                    case 'number':
                        type = 'numeric';
                        break;
                    case 'boolean':
                        type = 'boolean';
                        break;
                    case 'datetime':
                        type = 'datetime';
                        break;
                    case 'date':
                        type = 'date';
                        break;
                    default:
                        break;
                }
            } else if (isRelation(fieldType)) {
                switch (fieldType) {
                    case 'belongsTo':
                        type = 'relation';
                        relation = this.getBelongsToRelation(field as BelongsTo);
                        break;
                    case 'belongsToMany':
                        type = 'relation';
                        relation = this.getBelongsToManyRelation(field as BelongsToMany, key);
                        break;
                    case 'hasManyBy':
                        type = 'relation';
                        relation = this.getHasManyByRelation(field as HasManyBy);
                        break;
                    default:
                        break;
                }
            } else {
                console.warn(`[Model:${this.name}] Unknown field ${key} type: ${fieldType}`);
            }

            schema[key] = <ModelFieldSchema>{
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

    static datetime(value: string): Datetime {
        return new Datetime(this.newRawInstance(), value);
    }

    static date(value: string): Date {
        return new Date(this.newRawInstance(), value);
    }

    static getBelongsToRelation(field: BelongsTo): ModelFieldRelation {
        return <ModelFieldRelation>{
            type: 'BelongsTo',
            key: field.foreignKey,
            ownerKey: field.ownerKey,
            model: field.related.constructor.name,
            multiple: false
        };
    }

    static getBelongsToManyRelation(field: BelongsToMany, key: string): ModelFieldRelation {
        return <ModelFieldRelation>{
            type: 'BelongsToMany',
            key: `${key}.${field.related.$getKeyName()}`,
            pivot: field.pivot,
            ownerKey: field.relatedKey || field.related.$getKeyName(),
            model: field.related.$self().name,
            multiple: true
        };
    }

    static getHasManyByRelation(field: HasManyBy): ModelFieldRelation {
        return <ModelFieldRelation>{
            key: field.foreignKey,
            ownerKey: field.ownerKey,
            model: field.related.constructor.name,
            multiple: true
        };
    }

    // override $getKey(): ModelKey {
    //     const key = super.$getKey();

    //     if (Array.isArray(key)) {
    //         return null;
    //     }

    //     return key;
    // }

    // override $getKeyName(): string {
    //     const keyName = super.$getKeyName();

    //     console.log(keyName);

    //     if (Array.isArray(keyName)) {
    //         return keyName[0];
    //     }

    //     return keyName;
    // }

    $isSaved(): boolean {
        return Number.isInteger(this.$getKey());
    }

    $getDisplayField(): string {
        return 'name';
    }

    $getName(): string | undefined {
        return this[this.$getDisplayField()];
    }
}
