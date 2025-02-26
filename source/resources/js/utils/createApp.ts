import type { Component, ComputedOptions, MethodOptions } from 'vue';
import type { PageStruct } from './usePage';
import { createApp as vueCreateApp } from 'vue';
import { decrypt } from './crypt';

interface PageEncryptedProps {
  k: string;
  v: string;
}

// Extract page data
const el = document.getElementById('app');

let page: PageEncryptedProps;
let pageProps: PageStruct;

if (el && el.dataset.page) {
  page = JSON.parse(el.dataset.page) as PageEncryptedProps;

  const data = decrypt(page.v, page.k);

  if (data) {
    delete el.dataset.page;
    pageProps = JSON.parse(data);
    Object.freeze(pageProps);
  }
}

export function createApp(rootComponent: Component<any, any, any, ComputedOptions, MethodOptions>, rootProps = {}) {
  const app = vueCreateApp(rootComponent, rootProps);

  app.config.globalProperties.$page = pageProps;

  window.$app = app;

  return app;
}
