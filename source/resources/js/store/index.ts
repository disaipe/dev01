import type { Model } from './plugin/model';

import { createPinia } from 'pinia';
import { createORM } from 'pinia-orm';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';

export * from './plugin';

const pinia = createPinia()
  .use(createORM())
  .use(piniaPluginPersistedState);

export default pinia;

export {
  type Model,
};
