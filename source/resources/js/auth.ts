import { createApp } from 'vue';

import ElementPlus from 'element-plus';
/* @ts-ignore */
import elementLang from 'element-plus/dist/locale/ru.mjs';

import '../css/auth.css';

import App from './views/Auth.vue';

const app = createApp(App);

app
    .use(ElementPlus, { locale: elementLang });

app.mount('#app');
