import type { App } from 'vue';

import type { RouteMeta, RouteRecordRaw } from 'vue-router';
import { defineComponent } from 'vue';
import Record from '../../views/dashboard/record/BaseRecord.vue';
import Reference from '../../views/dashboard/reference/BaseReference.vue';

declare type Route = RouteRecordRaw & { meta: RouteMeta };

function resolveDefaultComponent(component: any, name: string) {
  return Promise.resolve(defineComponent({ ...component, name }));
}

function referenceComponent(route: Route) {
  return route.meta?.view
    ? import(`../../views/dashboard/reference/${route.meta.view}.vue`)
    : resolveDefaultComponent(Reference, `Reference${route.name?.toString()}`);
}

function recordComponent(route: Route) {
  return route.meta.view
    ? import(`../../views/dashboard/record/${route.meta.view}.vue`)
    : resolveDefaultComponent(Record, `Record${route.name?.toString()}`);
}

export default {
  install(app: App) {
    const router = app.config.globalProperties.$router;
    const routes = app.config.globalProperties.$page.routes;

    for (const route of routes) {
      if (Array.isArray(route.children)) {
        for (const childRoute of route.children) {
          if (childRoute.meta?.isReference) {
            childRoute.component = () => referenceComponent(childRoute as Route);
          }
          else if (childRoute.meta?.isRecord) {
            childRoute.component = () => recordComponent(childRoute as Route);
          }
          else if (childRoute.meta?.view) {
            childRoute.component = () => import(`../../views/dashboard/${(childRoute as Route).meta.view}.vue`);
          }
        }
      }

      if (route.meta?.view) {
        route.component = () => import(`../../views/dashboard/${route.meta.view}.vue`);
      }

      router.addRoute('dashboard-root', route);
    }

    // Manually resolve and replace location
    // Without it, you will see empty screen on reference routes
    const resolved = router.resolve(window.location.pathname);
    if (resolved.matched.length) {
      router.replace(resolved.href);
    }
    else {
      router.replace('/');
    }
  },
};
