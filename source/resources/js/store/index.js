import { createPinia } from 'pinia';
import { createORM } from 'pinia-orm';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';

const pinia = createPinia()
    .use(createORM())
    .use(piniaPluginPersistedState);

export default pinia;
