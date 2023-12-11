const files = import.meta.glob('./*/index.js', { eager: true });

const modules = {};

for (const key in files) {
    for (const [name, exported] of Object.entries(files[key])) {
        modules[exported.name || name] = exported;
    }
}

export const components = {
    install(app) {
        for (const [key, component] of Object.entries(modules)) {
            app.component(key, component);
        }
    }
}

export default modules;
