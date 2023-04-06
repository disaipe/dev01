import { createApp as vueCreateApp } from 'vue';

import { decrypt } from './crypt';

// Extract page data
const el = document.getElementById('app');
const page = JSON.parse(el.dataset.page);

const data = decrypt(page.v, page.k);

if (data) {
    delete el.dataset.page;
    delete page.k;
    delete page.v;
    Object.assign(page, JSON.parse(data));
    Object.freeze(page);
}

export function createApp(rootComponent, rootProps = {}) {
    const app = vueCreateApp(rootComponent, rootProps);

    app.config.globalProperties.$page = page;

    return app;
}
