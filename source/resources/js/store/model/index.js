import ApiModel from './api';
import Date from './attributes/date';
import Datetime from './attributes/datetime';

function isAttribute(type) {
    return ['Uid', 'Attr', 'String', 'String2', 'Number', 'Boolean', 'Datetime', 'Date'].includes(type);
}

function isRelation(type) {
    return ['BelongsTo', 'BelongsToMany', 'HasMany'].includes(type);
}

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

            const fieldType = field.name ?? field.constructor.name;

            if (isAttribute(fieldType)) {
                switch (fieldType) {
                    case 'Uid':
                        readonly = true;
                        break;
                    case 'Attr':
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
            } else if (isRelation(fieldType)) {
                switch (fieldType) {
                    case 'BelongsTo':
                        type = 'relation';
                        relation = {
                            type: fieldType,
                            key: field.foreignKey,
                            ownerKey: field.ownerKey,
                            model: field.related.constructor.name,
                            multiple: false
                        };
                        break;
                    case 'BelongsToMany':
                        type = 'relation';
                        relation = {
                            type: fieldType,
                            key: `${key}_keys`,
                            model: field.related.constructor.name,
                            multiple: true,
                        };
                        break;
                    case 'HasMany':
                        type = 'relation';
                        relation = {
                            key,
                            ownerKey: field.ownerKey,
                            model: field.related.constructor.name,
                            multiple: true
                        };
                        break;
                    default:
                        break;
                }
            } else {
                console.warn(`[Model:${this.name}] Unknown field ${key} type: ${fieldType}`);
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

    static attr(value= null) {
        const attr = super.attr(value);
        attr.name = 'Attr';
        return attr;
    }

    static string(value = null) {
        const attr = super.string(value);
        attr.name = 'String';
        return attr;
    }

    static number(value= null) {
        const attr = super.number(value);
        attr.name = 'Number';
        return attr;
    }

    static boolean(value= null) {
        const attr = super.boolean(value);
        attr.name = 'Boolean';
        return attr;
    }

    static uid(options= { alphabet: '0123456789abcdef', size: 7 }) {
        const attr = super.uid(options);
        attr.name = 'Uid';
        return attr;
    }

    static datetime(value) {
        return new Datetime(this.newRawInstance(), value);
    }

    static date(value) {
        return new Date(this.newRawInstance(), value);
    }

    static belongsTo(related, foreignKey, ownerKey = null)  {
        const attr = super.belongsTo(related, foreignKey, ownerKey);
        attr.name = 'BelongsTo';
        return attr;
    }

    static belongsToMany(related, pivot, foreignPivotKey, relatedPivotKey, parentKey = null, relatedKey= null) {
        const attr = super.belongsToMany(related, pivot, foreignPivotKey, relatedPivotKey, parentKey, relatedKey);
        attr.name = 'BelongsToMany';
        return attr;
    }

    static hasMany(related, foreignKey, localKey = null) {
        const attr = super.hasMany(related, foreignKey, localKey);
        attr.name = 'HasMany';
        return attr;
    }

    $isSaved() {
        return !isNaN(this.$getKey());
    }

    $getDisplayField() {
        return 'name';
    }

    $getName() {
        return this[this.$getDisplayField()];
    }


}
