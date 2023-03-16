import keyBy from 'lodash/keyBy';
import predefinedModels from '../../store/models';
import { defineModel, defineRepo } from '../../store/repository';

export default {
    install(app) {
        const models = keyBy(app.config.globalProperties.$page.models, 'name');

        const modelsCache = { ...predefinedModels };

        // define dynamic models in cache
        // needs to make linking models easy (including relationships to yourself)
        for (const [name, model] of Object.entries(models)) {
            modelsCache[name] = defineModel(name, {
                entity: model.entity,
                eagerLoad: model.eagerLoad,
            });
        }

        // iterate models and make fields binding
        for (const [name, model] of Object.entries(modelsCache)) {
            // skip predefined models
            if (predefinedModels[name]) {
                continue;
            }

            // override 'fields' method to return new bindings
            model.fields = function() {
                const fields = {};

                // iterate models fields and bind it to model
                for (const [key, def] of Object.entries(models[name].fields)) {
                    if (!Array.isArray(def)) {
                        console.warn(`Model "${model.name}" field "${key} definition is not valid, skipping`);
                        continue;
                    }

                    const [method, ...args] = def;

                    switch (method) {
                        case 'belongsTo': {
                            const [related, foreignKey, ownerKey] = args;
                            const relatedModel = modelsCache[related];

                            if (relatedModel) {
                                fields[key] = this[method](relatedModel, foreignKey, ownerKey);
                            } else {
                                console.warn(`Related model "${related}" for "${model.name}" not found, field definition skipped`);
                            }
                            break;
                        }
                        case 'hasMany': {
                            const [related, foreignKey, localKey] = args;
                            const relatedModel = modelsCache[related];

                            if (relatedModel) {
                                fields[key] = this[method](relatedModel, foreignKey, localKey);
                            } else {
                                console.warn(`Related model "${related}" for "${model.name}" not found, field definition skipped`);
                            }
                            break;
                        }
                        default:
                            fields[key] = this[method](...args);
                            break;
                    }
                }

                return fields;
            }
        }

        // iterate models cache again to define they repositories
        // must be after all models defined
        for (const [name, model] of Object.entries(modelsCache)) {
            // skip predefined models, they defines manually
            if (predefinedModels[name]) {
                continue;
            }

            defineRepo(model);
        }
    }
}
