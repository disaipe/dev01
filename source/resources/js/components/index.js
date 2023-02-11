const files = import.meta.glob('./**/index.js', { eager: true });

const modules = {};

for (const key in files) {
    const model = files[key].default;
    modules[model.name || model.constructor.name] = model;
}

export const components = {
    install(app) {
        for (const [key, component] of Object.entries(modules)) {
            app.component(key, component);
        }
    }
}

export default modules;
