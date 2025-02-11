import { Icon } from '@iconify/vue';

import dayjs from 'dayjs';

import timezone from 'dayjs/plugin/timezone';

import utc from 'dayjs/plugin/utc';

import ElementPlus from 'element-plus';
/** @ts-ignore */
import elementLang from 'element-plus/dist/locale/ru.mjs';
import VXETable from 'vxe-table';

import { components } from './components';

import dynamicModels from './plugin/dynamic-models';

import dynamicRoutes from './plugin/dynamic-routes';
import filters from './plugin/filters';

import router from './router';
import pinia from './store';
import { createApp } from './utils/createApp';
import App from './views/App.vue';

import '../css/app.css';

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

app.mount('#app');
