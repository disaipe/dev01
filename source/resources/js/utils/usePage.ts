import { getCurrentInstance } from 'vue';

export type MenuItem = {
    icon: string;
    label: string;
    name: string;
    order: string;
    route: {
        name: string
    }
}

export type RouteItem = {
    name: string;
    path: string;
    children: RouteItem[],
    meta: { [key: string]: any },
    redirect: {
        name: string
    }
}

export type ModelItem = {
    name: string;
    entity: string;
    displayField: string;
    eagerLoad: string[];
    fields: { [key: string]: string[] }
}

export type User = {
    name: string;
    avatar: string;
    companies: string[];
    hasAdminAccess: boolean;
    isClient: boolean;
    isImpersonating: false | string;
}

export type PageStruct = {
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
