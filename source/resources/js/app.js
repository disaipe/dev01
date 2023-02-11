import ElementPlus from 'element-plus';
import * as ElementPlusIcons from '@element-plus/icons-vue';
import elementLang from 'element-plus/dist/locale/ru.min';

import VXETable from 'vxe-table';

import '../styles/dashboard.pcss';
import 'element-plus/dist/index.css'
import 'vxe-table/lib/style.css';

import { createApp } from './utils/createApp';

import router from './router';
import pinia from './store';

import dynamicRoutes from './plugin/dynamic-routes';
import dynamicModels from './plugin/dynamic-models';
import { components } from './components';

import App from './views/App.vue';

const app = createApp(App)
    .use(ElementPlus, { locale: elementLang })
    .use(VXETable)
    .use(pinia)
    .use(router)

    // app plugins
    .use(dynamicRoutes)
    .use(dynamicModels)
    .use(components);

for (const [key, component] of Object.entries(ElementPlusIcons)) {
    app.component(key, component);
}

app.mount('#app');
