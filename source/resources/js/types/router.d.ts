import type { RouteLocationNormalized, RouteRecordRaw } from 'vue-router';

import 'vue-router';

declare module 'vue-router' {
  interface RouteMeta {
    /** Model name */
    model?: string;
    /** Route title */
    title?: string;
    /** Order in menu */
    order?: number;
    /** Icon name */
    icon?: string;
    /** Permissions struct */
    permissions?: {
      /** Create records allowed */
      create: boolean;
      /** Update records allowed */
      update: boolean;
      /** Delete records allowed */
      delete: boolean;
    };
    /** View name. False to disable view */
    view?: string | boolean | null;
    /** Record view name. False to disable view */
    recordView?: string | boolean | null;
    /** Parent menu item name */
    menuParent?: string | null;

    /** Is reference route */
    isReference?: boolean;
    /** Is record route */
    isRecord?: boolean;
  }
}

export { };
