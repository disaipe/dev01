import ElementPlus from 'element-plus';
import * as ElementPlusIcons from '@element-plus/icons-vue';

/** @ts-ignore */
import elementLang from 'element-plus/dist/locale/ru.mjs';

import VXETable from 'vxe-table';

import { Icon } from '@iconify/vue';

import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

import '../css/app.css';

import { createApp } from './utils/createApp';

import router from './router';
import pinia from './store';

import dynamicRoutes from './plugin/dynamic-routes';
import dynamicModels from './plugin/dynamic-models';
import filters from './plugin/filters';
import { components } from './components';

import App from './views/App.vue';

dayjs.extend(timezone);
dayjs.extend(utc);
dayjs.tz.setDefault('UTC');

const app = createApp(App)
    .use(ElementPlus, { locale: elementLang })
    .use(VXETable)
    .use(pinia)
    .use(router)

    .component('Icon', Icon)

    // app plugins
    .use(dynamicRoutes)
    .use(dynamicModels)
    .use(filters)
    .use(components);

for (const [key, component] of Object.entries(ElementPlusIcons)) {
    app.component(key, component);
}

app.mount('#app');
