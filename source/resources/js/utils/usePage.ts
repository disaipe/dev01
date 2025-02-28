import type { RouteMeta, RouteRecordRaw } from 'vue-router';
import { getCurrentInstance } from 'vue';

export interface MenuItem {
  /** Menu item name */
  name: string;
  /** Icon name */
  icon: string;
  /** Menu item label */
  label: string;
  /** Order */
  order: string;
  /** Route options */
  route: {
    name: string;
  };
}

export type RouteItem = RouteRecordRaw & {
  /** Route name */
  name: string;
  /** Route path */
  path: string;
  /** Route meta */
  meta: RouteMeta;
  /** Redirect route options */
  redirect?: {
    name: string;
  };
};

export interface ModelItem {
  /** Model name */
  name: string;
  /** Model entity name */
  entity: string;
  /** Field to display user as model record title */
  displayField: string;
  /** Eager load fields */
  eagerLoad: string[];
  /** Fields definition */
  fields: Record<string, string[]>;
  /** API path prefix */
  apiPrefix: string;
}

export interface User {
  /** User name */
  name: string;
  /** Avatar url */
  avatar: string | null;
  /** User available companies list */
  companies: Record<number, string>;
  /** User has admin access */
  hasAdminAccess: boolean;
  /** User is client */
  isClient: boolean;
  /** Impersonating state */
  isImpersonating: false | string;
}

export interface PageStruct {
  menu: MenuItem[];
  user: User;
  routes: RouteItem[];
  models: ModelItem[];
}

export default function usePage(): PageStruct {
  const instance = getCurrentInstance();

  if (instance) {
    return instance.appContext.config.globalProperties.$page;
  }

  return {} as PageStruct;
}
