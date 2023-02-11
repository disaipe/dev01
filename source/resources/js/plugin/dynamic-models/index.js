import { defineModel, defineRepo } from '../../store/repository';

export default {
    install(app) {
        const models = app.config.globalProperties.$page.models;

        for (const model of models) {
            const newModel = defineModel(model.name, {
                entity: model.entity,
                fields() {
                    const fields = {};

                    for (const [key, def] of Object.entries(model.fields)) {
                        const [method, ...args] = def;
                        fields[key] = this[method](...args);
                    }

                    return fields;
                }
            });

            defineRepo(newModel);
        }
    }
}
