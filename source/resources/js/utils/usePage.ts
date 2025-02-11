import { getCurrentInstance } from 'vue';

export interface MenuItem {
  icon: string;
  label: string;
  name: string;
  order: string;
  route: {
    name: string;
  };
}

export interface RouteItem {
  name: string;
  path: string;
  children: RouteItem[];
  meta: Record<string, any>;
  redirect: {
    name: string;
  };
}

export interface ModelItem {
  name: string;
  entity: string;
  displayField: string;
  eagerLoad: string[];
  fields: Record<string, string[]>;
}

export interface User {
  name: string;
  avatar: string;
  companies: string[];
  hasAdminAccess: boolean;
  isClient: boolean;
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
