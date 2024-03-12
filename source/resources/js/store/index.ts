import { createPinia, type PiniaPlugin } from 'pinia';
import { createORM } from 'pinia-orm';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';

const pinia = createPinia()
    .use(createORM() as PiniaPlugin)
    .use(piniaPluginPersistedState);

export default pinia;
