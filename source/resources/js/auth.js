import { createApp } from 'vue';

import ElementPlus from 'element-plus';
import * as ElementPlusIcons from '@element-plus/icons-vue';
import elementLang from 'element-plus/dist/locale/ru.min';

import '../styles/auth.pcss';
import 'element-plus/dist/index.css';

import App from './views/Auth.vue';

const app = createApp(App);

app
    .use(ElementPlus, { locale: elementLang });

for (const [key, component] of Object.entries(ElementPlusIcons)) {
    app.component(key, component);
}

app.mount('#app');
