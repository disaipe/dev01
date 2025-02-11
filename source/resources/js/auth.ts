import ElementPlus from 'element-plus';

/* @ts-ignore */
import elementLang from 'element-plus/dist/locale/ru.mjs';
import { createApp } from 'vue';

import App from './views/Auth.vue';

import '../css/auth.css';

const app = createApp(App);

app
  .use(ElementPlus, { locale: elementLang });

app.mount('#app');
