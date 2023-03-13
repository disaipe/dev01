import { useRepos, defineModel, defineRepo } from '../../store/repository';

export default {
    install(app) {
        const models = app.config.globalProperties.$page.models;

        const repos = useRepos();

        for (const model of models) {

            const newModel = defineModel(model.name, {
                entity: model.entity,
                eagerLoad: model.eagerLoad,
                fields() {
                    const fields = {};

                    for (const [key, def] of Object.entries(model.fields)) {
                        if (Array.isArray(def)) {
                            const [method, ...args] = def;

                            switch (method) {
                                case 'belongsTo':
                                    const [related, foreignKey, ownerKey] = args;
                                    const relatedModel = repos[related]?.model;

                                    if (relatedModel) {
                                        fields[key] = this[method](relatedModel.constructor, foreignKey, ownerKey);
                                    }
                                    break;
                                default:
                                    fields[key] = this[method](...args);
                                    break;
                            }
                        } else {
                            console.warn(`Model "${model.name}" field "${key} definition is not valid, skipping`);
                        }
                    }

                    return fields;
                }
            });

            defineRepo(newModel);
        }
    }
}
