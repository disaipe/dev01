import type { App, Component } from 'vue';

type Components = Record<string, Component>;

const files: Record<string, Components> = import.meta.glob('./*/index.[jt]s', { eager: true });
const modules: Components = {};

for (const key in files) {
  for (const [name, exported] of Object.entries(files[key])) {
    modules[exported.name || name] = exported;
  }
}

export const components = {
  install(app: App) {
    for (const [key, component] of Object.entries(modules)) {
      app.component(key, component);
    }
  },
};

export default modules;
