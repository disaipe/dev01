import type { PageStruct } from './utils/usePage';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $page: PageStruct;
  }
}

export { };
