import type { IModelOptions } from '@/types';
import type { Attribute, Constructor, ModelFields } from 'pinia-orm';
import type { App } from 'vue';
import type { Model } from '../../store';
import keyBy from 'lodash/keyBy';
import { defineModel, defineRepo } from '../../store';
import predefinedModels from '../../store/models';

export default {
  install(app: App) {
    const models: Record<string, IModelOptions> = keyBy(app.config.globalProperties.$page.models, 'name');

    const modelsCache: Record<string, typeof Model> = { ...predefinedModels };

    // define dynamic models in cache
    // needs to make linking models easy (including relationships to yourself)
    for (const [name, model] of Object.entries(models)) {
      modelsCache[name] = defineModel(name, model);
    }

    // iterate models and make fields binding
    for (const [name, model] of Object.entries(modelsCache)) {
      // skip predefined models
      if (Object.hasOwn(predefinedModels, name)) {
        continue;
      }

      // override 'fields' method to return new bindings
      model.fields = function () {
        const fields: ModelFields = {};

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
                fields[key] = this[method](relatedModel, foreignKey, ownerKey) as Attribute;
              }
              else {
                console.warn(`Related model "${related}" for "${model.name}" not found, field definition skipped`);
              }
              break;
            }
            case 'belongsToMany': {
              const [related, pivot, foreignPivotKey, relatedPivotKey] = args;
              const relatedModel = modelsCache[related];

              if (relatedModel) {
                fields[key] = this[method](relatedModel, modelsCache[pivot], relatedPivotKey, foreignPivotKey);
              }
              else {
                console.warn(`Related model "${related}" for "${model.name}" not found, field definition skipped`);
              }

              break;
            }
            case 'hasMany':
            case 'hasManyBy': {
              const [related, foreignKey, localKey] = args;
              const relatedModel = modelsCache[related];

              if (relatedModel) {
                fields[key] = this[method](relatedModel, foreignKey, localKey);
              }
              else {
                console.warn(`Related model "${related}" for "${model.name}" not found, field definition skipped`);
              }
              break;
            }
            default:
              /* @ts-ignore */
              fields[key] = this[method](...args);
              break;
          }

          Object.defineProperty(fields[key], 'name', { value: method, writable: false });
        }

        return fields;
      };
    }

    // iterate models cache again to define they repositories
    // must be after all models defined
    for (const [name, model] of Object.entries(modelsCache)) {
      // skip predefined models, they defines manually
      if (Object.hasOwn(predefinedModels, name)) {
        continue;
      }

      defineRepo(model);
    }
  },
};
