const files = import.meta.glob('./*.js', { eager: true });

const modules = {};

for (const key in files) {
    if (key === './index.js') {
        continue;
    }

    const model = files[key].default;
    modules[model.name || model.constructor.name] = model;
}

export default modules;
